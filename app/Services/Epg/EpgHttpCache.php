<?php

namespace App\Services\Epg;

use App\Models\Channel;
use App\Models\EpgChannel;
use App\Models\EpgSource;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EpgHttpCache
{
    /** @param array<int, int>|null $iptvChannelIds */
    public function apply(Request $request, Response $response, ?array $iptvChannelIds = null): Response
    {
        $sources = EpgSource::query()->orderBy('id')->get(['id', 'enabled', 'active_sync_generation', 'last_success_at', 'updated_at']);
        $epgChannels = EpgChannel::query()->where('is_active', true)->orderBy('id')
            ->get(['id', 'epg_source_id', 'external_id', 'display_name', 'icon_url', 'updated_at']);
        $mappings = Channel::query()->whereNotNull('epg_channel_id')->orderBy('id')->get(['id', 'epg_channel_id', 'updated_at']);
        $scope = $iptvChannelIds === null ? 'public' : hash('sha256', implode(',', $iptvChannelIds));
        $version = hash('sha256', json_encode([
            'epg-v2', $scope,
            $sources->toArray(), $epgChannels->toArray(), $mappings->toArray(),
        ], JSON_THROW_ON_ERROR));
        $lastModified = $sources->pluck('updated_at')->merge($epgChannels->pluck('updated_at'))->merge($mappings->pluck('updated_at'))
            ->filter()->map(fn ($value) => CarbonImmutable::parse($value))->max()
            ?? CarbonImmutable::createFromTimestamp((int) filemtime(__FILE__));

        $response->setEtag($version);
        $response->setLastModified($lastModified);
        $response->setPublic()->setMaxAge(max(0, (int) config('modules.epg.http_cache_seconds', 300)));

        if ($response->isNotModified($request)) {
            $response->setNotModified();
        }

        return $response;
    }
}
