<?php

namespace App\Services\Epg;

use App\Models\EpgChannel;
use App\Models\EpgProgramme;
use App\Models\EpgSource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use XMLReader;

class XmltvImporter
{
    private const BATCH_SIZE = 500;

    /** @return array{channels:int,programmes:int} */
    public function import(EpgSource $source, string $path): array
    {
        $size = filesize($path);
        if ($size === false || $size > (int) config('modules.epg.max_download_bytes', 52428800)) {
            throw new EpgImportException('The XMLTV payload exceeds the configured size limit.');
        }
        $xmlPath = $this->prepareXmlPath($path);
        $generation = (string) Str::uuid();

        try {
            $channels = $this->importChannels($source, $xmlPath, $generation);
            $channelMap = EpgChannel::query()->where('epg_source_id', $source->id)->pluck('id', 'external_id')->all();
            $programmes = $this->importProgrammes($source, $xmlPath, $channelMap, $generation);

            DB::transaction(function () use ($source, $generation): void {
                EpgChannel::query()->where('epg_source_id', $source->id)->update(['is_active' => false]);
                EpgChannel::query()
                    ->where('epg_source_id', $source->id)
                    ->where('pending_sync_generation', $generation)
                    ->update(['is_active' => true]);
                EpgChannel::query()->where('epg_source_id', $source->id)->update(['pending_sync_generation' => null]);
                $source->forceFill(['active_sync_generation' => $generation])->save();
                EpgProgramme::query()
                    ->whereHas('channel', fn ($query) => $query->where('epg_source_id', $source->id))
                    ->where('sync_generation', '!=', $generation)
                    ->delete();
            });

            return compact('channels', 'programmes');
        } catch (Throwable $exception) {
            EpgProgramme::query()->where('sync_generation', $generation)->delete();
            EpgChannel::query()
                ->where('epg_source_id', $source->id)
                ->where('pending_sync_generation', $generation)
                ->update(['pending_sync_generation' => null]);
            throw $exception;
        } finally {
            if ($xmlPath !== $path) {
                @unlink($xmlPath);
            }
        }
    }

    private function importChannels(EpgSource $source, string $path, string $generation): int
    {
        $reader = $this->openReader($path);
        $count = 0;
        try {
            $this->walk($reader, function (XMLReader $reader) use ($source, $generation, &$count): void {
                if ($reader->name === 'channel') {
                    $this->importChannel($source, $reader->readOuterXml(), $generation);
                    $count++;
                }
            });
        } finally {
            $reader->close();
            libxml_clear_errors();
        }

        return $count;
    }

    /** @param array<string, int> $channelMap */
    private function importProgrammes(EpgSource $source, string $path, array $channelMap, string $generation): int
    {
        $reader = $this->openReader($path);
        $limit = (int) config('modules.epg.max_programmes_per_import', 500000);
        $count = 0;
        $buffer = [];
        try {
            $this->walk($reader, function (XMLReader $reader) use ($source, $channelMap, $generation, $limit, &$count, &$buffer): void {
                if ($reader->name !== 'programme') {
                    return;
                }
                if (++$count > $limit) {
                    throw new EpgImportException('The XMLTV programme limit was exceeded.');
                }
                $data = $this->programmeData($source, $reader->readOuterXml(), $channelMap, $generation);
                if ($data !== null) {
                    $buffer[] = $data;
                }
                if (count($buffer) >= self::BATCH_SIZE) {
                    $this->upsertProgrammes($buffer);
                    $buffer = [];
                }
            });
            $this->upsertProgrammes($buffer);
        } finally {
            $reader->close();
            libxml_clear_errors();
        }

        return $count;
    }

    private function openReader(string $path): XMLReader
    {
        $prefix = file_get_contents($path, false, null, 0, 4096);
        if (! is_string($prefix) || stripos($prefix, '<!DOCTYPE') !== false || stripos($prefix, '<!ENTITY') !== false) {
            throw new EpgImportException('XML entities and doctypes are not allowed.');
        }
        libxml_use_internal_errors(true);
        $reader = new XMLReader;
        if (! $reader->open($path, null, LIBXML_NONET | LIBXML_COMPACT | LIBXML_NOBLANKS)) {
            throw new EpgImportException('Unable to open the XMLTV document.');
        }

        return $reader;
    }

    private function walk(XMLReader $reader, callable $callback): void
    {
        $seenRoot = false;
        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT) {
                continue;
            }
            if (! $seenRoot) {
                if ($reader->name !== 'tv') {
                    throw new EpgImportException('Invalid XMLTV root element.');
                }
                $seenRoot = true;

                continue;
            }
            $callback($reader);
        }
        if (! $seenRoot) {
            throw new EpgImportException('The XMLTV document is empty.');
        }
        foreach (libxml_get_errors() as $error) {
            if ($error->level >= LIBXML_ERR_ERROR) {
                throw new EpgImportException('The XMLTV document is malformed.');
            }
        }
    }

    private function importChannel(EpgSource $source, string $xml, string $generation): void
    {
        $node = $this->element($xml);
        $externalId = trim((string) $node['id']);
        if ($externalId === '') {
            return;
        }
        $names = $node->{'display-name'};
        $displayName = trim((string) ($names[0] ?? '')) ?: $externalId;
        EpgChannel::updateOrCreate(
            ['epg_source_id' => $source->id, 'external_id' => $externalId],
            [
                'name' => $displayName,
                'display_name' => $displayName,
                'icon_url' => isset($node->icon) ? trim((string) $node->icon['src']) ?: null : null,
                'language' => isset($names[0]) ? trim((string) $names[0]['lang']) ?: null : null,
                'metadata' => ['display_names' => array_values(array_filter(array_map('trim', array_map('strval', iterator_to_array($names)))))],
                'is_active' => EpgChannel::query()
                    ->where('epg_source_id', $source->id)
                    ->where('external_id', $externalId)
                    ->value('is_active') ?? false,
                'pending_sync_generation' => $generation,
            ],
        );
    }

    /** @param array<string, int> $channelMap
     * @return array<string, mixed>|null
     */
    private function programmeData(EpgSource $source, string $xml, array $channelMap, string $generation): ?array
    {
        $node = $this->element($xml);
        $externalChannel = trim((string) $node['channel']);
        $title = trim((string) ($node->title[0] ?? ''));
        $channelId = $channelMap[$externalChannel] ?? null;
        if ($externalChannel === '' || $title === '' || $channelId === null) {
            return null;
        }
        $start = $this->parseDate((string) $node['start'], $source->timezone);
        $end = $this->parseDate((string) $node['stop'], $source->timezone);
        if ($start === null || $end === null || $end->lessThanOrEqualTo($start)) {
            return null;
        }
        $externalId = trim((string) $node['id']);
        $externalId = $externalId !== '' ? $externalId : hash('sha256', implode('|', [$externalChannel, $start->timestamp, $end->timestamp, $title]));
        $now = now();

        return [
            'epg_channel_id' => $channelId,
            'external_id' => $externalId,
            'title' => $title,
            'subtitle' => trim((string) ($node->{'sub-title'}[0] ?? '')) ?: null,
            'description' => trim((string) ($node->desc[0] ?? '')) ?: null,
            'category' => trim((string) ($node->category[0] ?? '')) ?: null,
            'icon_url' => isset($node->icon) ? trim((string) $node->icon['src']) ?: null : null,
            'language' => isset($node->title[0]) ? trim((string) $node->title[0]['lang']) ?: null : null,
            'start_at' => $start->utc(),
            'end_at' => $end->utc(),
            'sync_generation' => $generation,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /** @param list<array<string, mixed>> $programmes */
    private function upsertProgrammes(array $programmes): void
    {
        if ($programmes !== []) {
            EpgProgramme::query()->upsert(
                $programmes,
                ['epg_channel_id', 'external_id', 'sync_generation'],
                ['title', 'subtitle', 'description', 'category', 'icon_url', 'language', 'start_at', 'end_at', 'updated_at'],
            );
        }
    }

    private function prepareXmlPath(string $path): string
    {
        if (file_get_contents($path, false, null, 0, 2) !== "\x1f\x8b") {
            return $path;
        }
        $input = gzopen($path, 'rb');
        $outputPath = tempnam(sys_get_temp_dir(), 'xmltv-uncompressed-');
        $output = $outputPath !== false ? fopen($outputPath, 'wb') : false;
        if ($input === false || $outputPath === false || $output === false) {
            if (is_resource($input)) {
                gzclose($input);
            }
            if (is_resource($output)) {
                fclose($output);
            }
            if (is_string($outputPath)) {
                @unlink($outputPath);
            }
            throw new EpgImportException('Unable to decompress the XMLTV gzip payload.');
        }
        $maximum = (int) config('modules.epg.max_uncompressed_bytes', 52428800);
        $total = 0;
        try {
            while (! gzeof($input)) {
                $chunk = gzread($input, 8192);
                if ($chunk === false) {
                    throw new EpgImportException('The XMLTV gzip payload is corrupted.');
                }
                $total += strlen($chunk);
                if ($total > $maximum) {
                    throw new EpgImportException('The uncompressed XMLTV payload exceeds the configured size limit.');
                }
                if ($chunk !== '' && fwrite($output, $chunk) === false) {
                    throw new EpgImportException('Unable to write the decompressed XMLTV payload.');
                }
            }
        } catch (Throwable $exception) {
            @unlink($outputPath);
            throw $exception;
        } finally {
            gzclose($input);
            fclose($output);
        }

        return $outputPath;
    }

    private function element(string $xml): \SimpleXMLElement
    {
        $node = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        if (! $node) {
            throw new EpgImportException('Malformed XMLTV element.');
        }

        return $node;
    }

    private function parseDate(string $value, string $timezone): ?CarbonImmutable
    {
        if (! preg_match('/^(\d{14})(?:\s*([+\-]\d{4}))?/', trim($value), $matches)) {
            return null;
        }
        try {
            return isset($matches[2])
                ? CarbonImmutable::createFromFormat('YmdHis O', $matches[1].' '.$matches[2])
                : CarbonImmutable::createFromFormat('YmdHis', $matches[1], $timezone ?: config('modules.epg.default_timezone'));
        } catch (Throwable) {
            return null;
        }
    }
}
