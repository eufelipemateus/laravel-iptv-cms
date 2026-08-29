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
    $xmltvId = !empty($Channel->epg_external_id)
        ? \App\Models\EpgChannel::makeXmltvId($Channel->epg_source_id, $Channel->epg_external_id)
        : $Channel->number;
@endphp
#EXTINF:-1 type="stream" @if($Channel->radio) radio=true @else tvg-id="{!! $attr($xmltvId) !!}" tvg-name="{!! $attr($Channel->name) !!}" @endif tvg-logo="{!! $attr(url($Channel->logo)) !!}" group-title="{!! $attr($Channel->group_name) !!}",{!! $line($Channel->name) !!}
{!! $line($Channel->url_stream) !!}
@endforeach
@foreach(($vods ?? []) as $vod)
@php
    $playback = route('api.vods.playback', ['id' => $vod->slug ?: $vod->id]);
@endphp
#EXTINF:-1 tvg-id="vod-{{ $attr($vod->id) }}" tvg-name="{!! $attr($vod->name) !!}" group-title="VOD",{!! $line($vod->name) !!}
{!! $line($playback) !!}
@endforeach
