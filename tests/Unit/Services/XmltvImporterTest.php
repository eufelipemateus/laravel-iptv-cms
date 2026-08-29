<?php

namespace Tests\Unit\Services;

use App\Models\EpgChannel;
use App\Models\EpgProgramme;
use App\Models\EpgSource;
use App\Services\Epg\EpgImportException;
use App\Services\Epg\XmltvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XmltvImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_and_updates_channels_and_programmes_without_duplicates(): void
    {
        $source = $this->source();
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<tv><channel id="news.br"><display-name lang="pt">News</display-name><icon src="https://example.com/news.png" /></channel><programme start="20260814180000 -0300" stop="20260814190000 -0300" channel="news.br"><title lang="pt">Jornal</title><sub-title>Edição</sub-title><desc>Notícias.</desc><category>News</category></programme></tv>
XML;
        $path = $this->temporaryXml($xml);
        try {
            $importer = app(XmltvImporter::class);
            $importer->import($source, $path);
            $importer->import($source, $path);
        } finally {
            @unlink($path);
        }

        $this->assertDatabaseCount('epg_channels', 1);
        $this->assertDatabaseCount('epg_programmes', 1);
        $this->assertSame('News', EpgChannel::firstOrFail()->display_name);
        $programme = EpgProgramme::firstOrFail();
        $this->assertSame('Jornal', $programme->title);
        $this->assertSame('2026-08-14 21:00:00', $programme->start_at->format('Y-m-d H:i:s'));
    }

    public function test_rejects_doctypes_and_external_entities(): void
    {
        $path = $this->temporaryXml('<?xml version="1.0"?><!DOCTYPE tv [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><tv><channel id="x"><display-name>&xxe;</display-name></channel></tv>');
        try {
            $this->expectException(EpgImportException::class);
            app(XmltvImporter::class)->import($this->source(), $path);
        } finally {
            @unlink($path);
        }
    }

    public function test_imports_gzip_compressed_xmltv(): void
    {
        $xml = '<tv><channel id="sports.br"><display-name>Sports</display-name></channel><programme start="20260814180000 +0000" stop="20260814190000 +0000" channel="sports.br"><title>Live match</title></programme></tv>';
        $path = $this->temporaryXml((string) gzencode($xml, 9));
        try {
            $result = app(XmltvImporter::class)->import($this->source(), $path);
        } finally {
            @unlink($path);
        }

        $this->assertSame(['channels' => 1, 'programmes' => 1], $result);
        $this->assertDatabaseHas('epg_channels', ['external_id' => 'sports.br']);
        $this->assertDatabaseHas('epg_programmes', ['title' => 'Live match']);
    }

    public function test_rejects_gzip_that_exceeds_uncompressed_limit(): void
    {
        config(['modules.epg.max_uncompressed_bytes' => 32]);
        $path = $this->temporaryXml((string) gzencode('<tv>'.str_repeat(' ', 100).'</tv>'));
        try {
            $this->expectException(EpgImportException::class);
            $this->expectExceptionMessage('uncompressed XMLTV payload exceeds');
            app(XmltvImporter::class)->import($this->source(), $path);
        } finally {
            @unlink($path);
        }
    }

    public function test_rejects_malformed_xml_and_size_limit(): void
    {
        config(['modules.epg.max_download_bytes' => 8]);
        $path = $this->temporaryXml('<tv><channel></tv>');
        try {
            $this->expectException(EpgImportException::class);
            app(XmltvImporter::class)->import($this->source(), $path);
        } finally {
            @unlink($path);
        }
    }

    public function test_reconciles_changed_programmes_without_leaving_ghosts(): void
    {
        $source = $this->source();
        $this->importXml($source, '<tv><channel id="news"><display-name>News</display-name></channel><programme start="20260814180000 +0000" stop="20260814190000 +0000" channel="news"><title>Bulletin</title></programme></tv>');
        $firstGeneration = $source->fresh()->active_sync_generation;

        $this->importXml($source, '<tv><channel id="news"><display-name>News</display-name></channel><programme start="20260814183000 +0000" stop="20260814193000 +0000" channel="news"><title>Bulletin</title></programme></tv>');

        $this->assertDatabaseCount('epg_programmes', 1);
        $programme = EpgProgramme::firstOrFail();
        $this->assertSame('2026-08-14 18:30:00', $programme->start_at->format('Y-m-d H:i:s'));
        $this->assertNotSame($firstGeneration, $source->fresh()->active_sync_generation);
    }

    public function test_failed_import_preserves_the_previous_active_generation(): void
    {
        $source = $this->source();
        $this->importXml($source, '<tv><channel id="news"><display-name>News</display-name></channel><programme id="valid" start="20260814180000 +0000" stop="20260814190000 +0000" channel="news"><title>Valid</title></programme></tv>');
        $generation = $source->fresh()->active_sync_generation;

        config(['modules.epg.max_programmes_per_import' => 1]);
        try {
            $this->importXml($source, '<tv><channel id="news"><display-name>News</display-name></channel><programme id="new" start="20260814200000 +0000" stop="20260814210000 +0000" channel="news"><title>New</title></programme><programme id="overflow" start="20260814210000 +0000" stop="20260814220000 +0000" channel="news"><title>Overflow</title></programme></tv>');
            $this->fail('The import should have exceeded the programme limit.');
        } catch (EpgImportException) {
            $this->assertSame($generation, $source->fresh()->active_sync_generation);
            $this->assertDatabaseHas('epg_programmes', ['external_id' => 'valid', 'sync_generation' => $generation]);
            $this->assertDatabaseMissing('epg_programmes', ['external_id' => 'new']);
        }
    }

    public function test_rejects_empty_wrong_root_and_excess_programmes(): void
    {
        foreach (['', '<guide/>'] as $xml) {
            try {
                $this->importXml($this->source(), $xml);
                $this->fail('Invalid XMLTV should be rejected.');
            } catch (EpgImportException) {
                $this->addToAssertionCount(1);
            }
        }

        config(['modules.epg.max_programmes_per_import' => 0]);
        $this->expectException(EpgImportException::class);
        $this->importXml($this->source(), '<tv><channel id="x"><display-name>X</display-name></channel><programme start="20260814180000" stop="20260814190000" channel="x"><title>X</title></programme></tv>');
    }

    private function source(): EpgSource
    {
        return EpgSource::create(['name' => 'Guide', 'url' => 'https://example.com/guide.xml', 'enabled' => true, 'format' => 'xmltv', 'timezone' => 'UTC', 'refresh_interval' => 60]);
    }

    private function temporaryXml(string $xml): string
    {
        $path = tempnam(sys_get_temp_dir(), 'epg-test-');
        file_put_contents($path, $xml);

        return $path;
    }

    private function importXml(EpgSource $source, string $xml): array
    {
        $path = $this->temporaryXml($xml);
        try {
            return app(XmltvImporter::class)->import($source, $path);
        } finally {
            @unlink($path);
        }
    }
}
