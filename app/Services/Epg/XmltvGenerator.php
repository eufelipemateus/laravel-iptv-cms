<?php

namespace App\Services\Epg;

use App\Models\EpgChannel;
use App\Models\EpgProgramme;
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

        $query = EpgChannel::query()
            ->where('is_active', true)
            ->whereHas('channels')
            ->whereHas('source', fn ($query) => $query->where('enabled', true)->whereNotNull('active_sync_generation'));
        if ($iptvChannelIds !== null) {
            $query->whereHas('channels', fn ($query) => $query->whereIn('iptv_channels.id', $iptvChannelIds));
        }

        $query->orderBy('id')->chunkById(250, function ($channels) use ($writer): void {
            foreach ($channels as $channel) {
                $writer->startElement('channel');
                $writer->writeAttribute('id', $channel->xmltvId());
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

        $programmeQuery = EpgProgramme::query()
            ->select('epg_programmes.*', 'epg_channels.epg_source_id', 'epg_channels.external_id as channel_external_id')
            ->join('epg_channels', 'epg_channels.id', '=', 'epg_programmes.epg_channel_id')
            ->join('epg_sources', 'epg_sources.id', '=', 'epg_channels.epg_source_id')
            ->where('epg_channels.is_active', true)
            ->where('epg_sources.enabled', true)
            ->whereColumn('epg_programmes.sync_generation', 'epg_sources.active_sync_generation')
            ->where('epg_programmes.end_at', '>=', now()->subDays((int) config('modules.epg.retention_days', 7)))
            ->whereHas('channel.channels');
        if ($iptvChannelIds !== null) {
            $programmeQuery->whereHas('channel.channels', fn ($query) => $query->whereIn('iptv_channels.id', $iptvChannelIds));
        }
        $programmeQuery->orderBy('epg_programmes.id')->chunkById(250, function ($programmes) use ($writer): void {
            foreach ($programmes as $programme) {
                $writer->startElement('programme');
                $writer->writeAttribute('start', $this->formatDate($programme->start_at));
                $writer->writeAttribute('stop', $this->formatDate($programme->end_at));
                $writer->writeAttribute('channel', EpgChannel::makeXmltvId($programme->epg_source_id, $programme->channel_external_id));
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
            $writer->flush();
        }, 'epg_programmes.id', 'id');

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
