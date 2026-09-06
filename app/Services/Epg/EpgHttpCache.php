<?php

namespace App\Services\Epg;

use App\Models\Channel;
use App\Models\Customer;
use App\Models\EpgChannel;
use App\Models\EpgSource;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EpgHttpCache
{
    public function applyPublic(Request $request, Response $response): Response
    {
        $response->setPublic();

        return $this->apply($request, $response, ['feed' => 'public']);
    }

    /** @param array<int, int> $iptvChannelIds */
    public function applyPrivate(
        Request $request,
        Response $response,
        Customer $customer,
        string $cdnSlug,
        array $iptvChannelIds,
    ): Response {
        sort($iptvChannelIds);
        $response->setPrivate();
        $response->setVary('Authorization');

        return $this->apply($request, $response, [
            'feed' => 'private',
            'customer' => $customer->getKey(),
            'cdn' => $cdnSlug,
            'channels' => $iptvChannelIds,
        ]);
    }

    /** @param array<string, mixed> $scope */
    private function apply(Request $request, Response $response, array $scope): Response
    {
        $seconds = max(1, (int) config('modules.epg.http_cache_seconds', 300));
        $sources = EpgSource::query()->orderBy('id')->get(['id', 'enabled', 'active_sync_generation', 'last_success_at', 'updated_at']);
        $epgChannels = EpgChannel::query()->where('is_active', true)->orderBy('id')
            ->get(['id', 'epg_source_id', 'external_id', 'display_name', 'icon_url', 'updated_at']);
        $mappings = Channel::query()->whereNotNull('epg_channel_id')->orderBy('id')->get(['id', 'epg_channel_id', 'updated_at']);
        $version = hash('sha256', json_encode([
            'epg-v1',
            'scope' => $scope,
            'time_bucket' => intdiv(now()->timestamp, $seconds),
            'retention_days' => (int) config('modules.epg.retention_days', 7),
            'default_timezone' => (string) config('modules.epg.default_timezone', 'UTC'),
            $sources->toArray(), $epgChannels->toArray(), $mappings->toArray(),
        ], JSON_THROW_ON_ERROR));

        $response->setEtag($version);
        $response->setMaxAge($seconds);

        if ($response->isNotModified($request)) {
            $response->setNotModified();
        }

        return $response;
    }
}
