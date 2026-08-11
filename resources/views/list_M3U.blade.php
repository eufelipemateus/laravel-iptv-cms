@php
    $line = fn ($value) => str_replace(["\r", "\n"], '', (string) $value);
    $attr = fn ($value) => str_replace(['"', "\r", "\n"], ["'", '', ''], (string) $value);
@endphp
#EXTM3U
@foreach($list as $Channel)
#EXTINF:-1 type="stream" @if($Channel->radio) radio=true @else tvg-id="{!! $attr($Channel->number) !!}" tvg-name="{!! $attr($Channel->name) !!}" @endif tvg-logo="{!! $attr(url($Channel->logo)) !!}" group-title="{!! $attr($Channel->group_name) !!}",{!! $line($Channel->name) !!}
{!! $line($Channel->url_stream) !!}
@endforeach
@foreach(($vods ?? []) as $vod)
@php
    $playback = route('api.v1.vods.playback', ['id' => $vod->slug ?: $vod->id]);
@endphp
#EXTINF:-1 tvg-id="vod-{{ $attr($vod->id) }}" tvg-name="{!! $attr($vod->name) !!}" group-title="VOD",{!! $line($vod->name) !!}
{!! $line($playback) !!}
@endforeach
