#EXTM3U
@foreach($list as $Channel)
#EXTINF:-1 type="stream" @if($Channel->radio) radio=true @else tvg-id="{!! $attr($Channel->number) !!}" tvg-name="{!! $attr($Channel->name) !!}" @endif tvg-logo="{!! $attr(url($Channel->logo)) !!}" group-title="{!! $attr($Channel->group_name) !!}",{!! $line($Channel->name) !!}
{!! $line($Channel->url_stream) !!}
@endforeach

