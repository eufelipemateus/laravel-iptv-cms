<?php

namespace App\Services\Epg;

use App\Models\EpgChannel;
use Carbon\CarbonInterface;
use XMLWriter;

class XmltvGenerator
{
    /** @param array<int, int>|null $iptvChannelIds */
    public function stream(?array $iptvChannelIds = null): void
    {
        $writer = new XMLWriter;
        $writer->openUri('php://output');
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('tv');
        $writer->writeAttribute('generator-info-name', 'Laravel IPTV CMS');

        $query = EpgChannel::query()->whereHas('channels');
        if ($iptvChannelIds !== null) {
            $query->whereHas('channels', fn ($query) => $query->whereIn('iptv_channels.id', $iptvChannelIds));
        }

        $query->orderBy('id')->chunkById(250, function ($channels) use ($writer): void {
            foreach ($channels as $channel) {
                $writer->startElement('channel');
                $writer->writeAttribute('id', $channel->external_id);
                $writer->writeElement('display-name', $channel->display_name);
                if ($channel->icon_url) {
                    $writer->startElement('icon');
                    $writer->writeAttribute('src', $channel->icon_url);
                    $writer->endElement();
                }
                $writer->endElement();
            }
            $writer->flush();
        });

        $programmeQuery = EpgChannel::query()->whereHas('channels')->with(['programmes' => fn ($query) => $query->where('end_at', '>=', now()->subDays((int) config('modules.epg.retention_days', 7)))->orderBy('start_at'),
        ]);
        if ($iptvChannelIds !== null) {
            $programmeQuery->whereHas('channels', fn ($query) => $query->whereIn('iptv_channels.id', $iptvChannelIds));
        }
        $programmeQuery->orderBy('id')->chunkById(50, function ($channels) use ($writer): void {
            foreach ($channels as $channel) {
                foreach ($channel->programmes as $programme) {
                    $writer->startElement('programme');
                    $writer->writeAttribute('start', $this->formatDate($programme->start_at));
                    $writer->writeAttribute('stop', $this->formatDate($programme->end_at));
                    $writer->writeAttribute('channel', $channel->external_id);
                    $this->writeText($writer, 'title', $programme->title, $programme->language);
                    $this->writeText($writer, 'sub-title', $programme->subtitle, $programme->language);
                    $this->writeText($writer, 'desc', $programme->description, $programme->language);
                    $this->writeText($writer, 'category', $programme->category, null);
                    if ($programme->icon_url) {
                        $writer->startElement('icon');
                        $writer->writeAttribute('src', $programme->icon_url);
                        $writer->endElement();
                    }
                    $writer->endElement();
                }
                unset($channel->programmes);
            }
            $writer->flush();
        });

        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
    }

    private function formatDate(CarbonInterface $date): string
    {
        return $date->copy()->setTimezone((string) config('modules.epg.default_timezone', 'UTC'))->format('YmdHis O');
    }

    private function writeText(XMLWriter $writer, string $element, ?string $value, ?string $language): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $writer->startElement($element);
        if ($language) {
            $writer->writeAttribute('lang', $language);
        }
        $writer->text($value);
        $writer->endElement();
    }
}
