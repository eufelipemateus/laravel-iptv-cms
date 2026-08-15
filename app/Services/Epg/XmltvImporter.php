<?php

namespace App\Services\Epg;

use App\Models\EpgChannel;
use App\Models\EpgProgramme;
use App\Models\EpgSource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use XMLReader;

class XmltvImporter
{
    /** @return array{channels:int,programmes:int} */
    public function import(EpgSource $source, string $path): array
    {
        $size = filesize($path);
        if ($size === false || $size > (int) config('modules.epg.max_download_bytes', 52428800)) {
            throw new EpgImportException('The XMLTV payload exceeds the configured size limit.');
        }

        $xmlPath = $this->prepareXmlPath($path);
        $prefix = file_get_contents($xmlPath, false, null, 0, 4096);
        if (! is_string($prefix) || stripos($prefix, '<!DOCTYPE') !== false || stripos($prefix, '<!ENTITY') !== false) {
            if ($xmlPath !== $path) {
                @unlink($xmlPath);
            }
            throw new EpgImportException('XML entities and doctypes are not allowed.');
        }

        $reader = new XMLReader;
        libxml_use_internal_errors(true);
        if (! $reader->open($xmlPath, null, LIBXML_NONET | LIBXML_COMPACT | LIBXML_NOBLANKS)) {
            if ($xmlPath !== $path) {
                @unlink($xmlPath);
            }
            throw new EpgImportException('Unable to open the XMLTV document.');
        }

        $channels = 0;
        $programmes = 0;
        $limit = (int) config('modules.epg.max_programmes_per_import', 500000);
        $seenRoot = false;

        try {
            DB::transaction(function () use ($reader, $source, &$channels, &$programmes, $limit, &$seenRoot): void {
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
                    if ($reader->name === 'channel') {
                        $this->importChannel($source, $reader->readOuterXml());
                        $channels++;
                    } elseif ($reader->name === 'programme') {
                        if (++$programmes > $limit) {
                            throw new EpgImportException('The XMLTV programme limit was exceeded.');
                        }
                        $this->importProgramme($source, $reader->readOuterXml());
                    }
                }
                if (! $seenRoot) {
                    throw new EpgImportException('The XMLTV document is empty.');
                }
                foreach (libxml_get_errors() as $error) {
                    if ($error->level >= LIBXML_ERR_ERROR) {
                        throw new EpgImportException('The XMLTV document is malformed.');
                    }
                }
            });
        } finally {
            $reader->close();
            libxml_clear_errors();
            if ($xmlPath !== $path) {
                @unlink($xmlPath);
            }
        }

        return compact('channels', 'programmes');
    }

    private function prepareXmlPath(string $path): string
    {
        $signature = file_get_contents($path, false, null, 0, 2);
        if ($signature !== "\x1f\x8b") {
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
        } catch (\Throwable $exception) {
            @unlink($outputPath);
            throw $exception;
        } finally {
            gzclose($input);
            fclose($output);
        }

        return $outputPath;
    }

    private function importChannel(EpgSource $source, string $xml): void
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
            ],
        );
    }

    private function importProgramme(EpgSource $source, string $xml): void
    {
        $node = $this->element($xml);
        $externalChannel = trim((string) $node['channel']);
        $title = trim((string) ($node->title[0] ?? ''));
        if ($externalChannel === '' || $title === '') {
            return;
        }
        $channel = EpgChannel::where('epg_source_id', $source->id)->where('external_id', $externalChannel)->first();
        if (! $channel) {
            return;
        }
        $start = $this->parseDate((string) $node['start'], $source->timezone);
        $end = $this->parseDate((string) $node['stop'], $source->timezone);
        if ($start === null || $end === null || $end->lessThanOrEqualTo($start)) {
            return;
        }
        $externalId = trim((string) $node['id']);
        $externalId = $externalId !== '' ? $externalId : hash('sha256', implode('|', [$externalChannel, $start->timestamp, $end->timestamp, $title]));
        EpgProgramme::updateOrCreate(
            ['epg_channel_id' => $channel->id, 'external_id' => $externalId],
            [
                'title' => $title,
                'subtitle' => trim((string) ($node->{'sub-title'}[0] ?? '')) ?: null,
                'description' => trim((string) ($node->desc[0] ?? '')) ?: null,
                'category' => trim((string) ($node->category[0] ?? '')) ?: null,
                'icon_url' => isset($node->icon) ? trim((string) $node->icon['src']) ?: null : null,
                'language' => isset($node->title[0]) ? trim((string) $node->title[0]['lang']) ?: null : null,
                'start_at' => $start->utc(),
                'end_at' => $end->utc(),
            ],
        );
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
        } catch (\Throwable) {
            return null;
        }
    }
}
