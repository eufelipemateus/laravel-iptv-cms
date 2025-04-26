#EXTM3U

@foreach($list as $Channel)

#EXTINF:-1 type="stream"  @if($Channel->radio) radio=true @else tvg-id="{{$Channel->number}}" tvg-name="{{$Channel->name}}" @endif tvg-logo="{{ url($Channel->logo) }}" group-title="{{ $Channel->group_name }}",{{$Channel->name}}
{{$Channel->url_stream}}
@endforeach

