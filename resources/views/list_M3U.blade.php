@php
    $line = fn ($value) => str_replace(["\r", "\n"], '', (string) $value);
    $attr = fn ($value) => str_replace(['"', "\r", "\n"], ["'", '', ''], (string) $value);
@endphp
#EXTM3U{!! !empty($epg_url) ? ' url-tvg="'.$attr($epg_url).'"' : '' !!}
@foreach($list as $Channel)
@php
    $streamAttributes = $Channel->radio
        ? ' radio=true'
        : (!empty($Channel->epg_external_id) ? ' tvg-id="'.$attr($Channel->epg_external_id).'"' : '').' tvg-name="'.$attr($Channel->name).'"';
@endphp
#EXTINF:-1 type="stream"{!! $streamAttributes !!} tvg-logo="{!! $attr(url($Channel->logo)) !!}" group-title="{!! $attr($Channel->group_name) !!}",{!! $line($Channel->name) !!}
{!! $line($Channel->url_stream) !!}
@endforeach
@foreach(($vods ?? []) as $vod)
@php
    $playback = route('api.vods.playback', ['id' => $vod->slug ?: $vod->id]);
@endphp
#EXTINF:-1 tvg-id="vod-{{ $attr($vod->id) }}" tvg-name="{!! $attr($vod->name) !!}" group-title="VOD",{!! $line($vod->name) !!}
{!! $line($playback) !!}
@endforeach
