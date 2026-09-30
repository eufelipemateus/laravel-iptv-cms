@php
    $line = fn ($value) => str_replace(["\r", "\n"], '', (string) $value);
    $attr = fn ($value) => str_replace(['"', "\r", "\n"], ["'", '', ''], (string) $value);
@endphp
@if(!empty($epg_url))
#EXTM3U url-tvg="{!! $attr($epg_url) !!}"
@else
#EXTM3U
@endif
@foreach($list as $Channel)
@php
    $hasMapping = !empty($Channel->epg_channel_id);
    $mappingIsPublished = !empty($epg_url)
        && $hasMapping
        && (bool) $Channel->epg_is_active
        && (bool) $Channel->epg_source_enabled
        && !empty($Channel->active_sync_generation);
    $xmltvId = $mappingIsPublished
        ? \App\Models\EpgChannel::makeXmltvId($Channel->epg_source_id, $Channel->epg_external_id)
        : ($hasMapping ? null : $Channel->number);
@endphp
#EXTINF:-1 type="stream" @if($Channel->radio) radio=true @else @if($xmltvId !== null)tvg-id="{!! $attr($xmltvId) !!}" @endif tvg-name="{!! $attr($Channel->name) !!}" @endif tvg-logo="{!! $attr(url($Channel->logo)) !!}" group-title="{!! $attr($Channel->group_name) !!}",{!! $line($Channel->name) !!}
{!! $line($Channel->url_stream) !!}
@endforeach
@foreach(($vods ?? []) as $vod)
@php
    $playback = route('api.vods.playback', ['id' => $vod->slug ?: $vod->id]);
@endphp
#EXTINF:-1 tvg-id="vod-{{ $attr($vod->id) }}" tvg-name="{!! $attr($vod->name) !!}" group-title="VOD",{!! $line($vod->name) !!}
{!! $line($playback) !!}
@endforeach
