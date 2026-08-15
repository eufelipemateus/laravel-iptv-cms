@extends('app')

@section('style')
<style>
/*
.form-group input[type="checkbox"] {
    display: none;
}

.form-group input[type="checkbox"] + .btn-group > label span {
    width: 20px;
}

.form-group input[type="checkbox"] + .btn-group > label span:first-child {
    display: none;
}
.form-group input[type="checkbox"] + .btn-group > label span:last-child {
    display: inline-block;
}

.form-group input[type="checkbox"]:checked + .btn-group > label span:first-child {
    display: inline-block;
}
.form-group input[type="checkbox"]:checked + .btn-group > label span:last-child {
    display: none;
}*/

.row{
    margin: 1% 0;
}
</style>
@endsection

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Channels')   }}</h1>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-3"><b>{{ __('Channel') }} </b></div>
					</div>
                </div>

                <div class="card-body">
					<form class="form-horizontal" role="form" method="POST" action="" enctype="multipart/form-data">

						{{ csrf_field() }}

						<div class="form-group">
							<label for="number" class="col-md-4 control-label">{{ __('Number') }}</label>
							<div class="col-md-6">
								<input id="number" type="number" min="1"   class="form-control" name="number" value="@if(isset($Channel->number)){{ $Channel->number }}@endif" placeholder="" required autofocus>

                                @if ($errors->has('number'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('number') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>


						<div class="form-group">
							<label for="image" class="col-md-4 control-label">{{ __('Logo') }}</label>
							<div class="col-md-6">
								<input id="image" type="file"   class="form-control" name="image" @if(!isset($Channel->logo)) required @endif placeholder=""  >

								@if ($errors->has('image'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('image') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>


						<div class="form-group">
							<label for="name" class="col-md-4 control-label">{{ __('Name') }}</label>
							<div class="col-md-6">
								<input id="name" type="text"   class="form-control" name="name" value="@if(isset($Channel->name)){{ $Channel->name }}@endif" placeholder="" required >
								@if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>




						<div class="form-group">
							<label for="group_id" class="col-md-4 control-label">{{ __('Group') }}</label>
							<div class="col-md-6">
								<select id="group_id" class="form-control" name="group_id"   >
									@foreach($Groupslist as $group)
										<option @if(isset($Channel->group_id)) @if($Channel->group_id==$group->id)  selected @endif @endif value="{{ $group->id}}">{{$group->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
                        @if(config('modules.epg.enabled', true))
                        <hr>
                        <h5>EPG</h5>
                        <div class="form-group">
                            <label for="epg_source_id" class="col-md-4 control-label">EPG Source</label>
                            <div class="col-md-6"><select id="epg_source_id" class="form-control">
                                <option value="">No EPG</option>
                                @foreach($EpgSources as $source)
                                    <option value="{{ $source->id }}" @selected(optional($Channel->epgChannel ?? null)->epg_source_id === $source->id)>{{ $source->name }}</option>
                                @endforeach
                            </select></div>
                        </div>
                        <div class="form-group">
                            <label for="epg_search" class="col-md-4 control-label">Search EPG channel</label>
                            <div class="col-md-6"><input id="epg_search" class="form-control" placeholder="Type a channel name or external ID"></div>
                        </div>
                        <div class="form-group">
                            <label for="epg_channel_id" class="col-md-4 control-label">EPG Channel</label>
                            <div class="col-md-6"><select id="epg_channel_id" name="epg_channel_id" class="form-control">
                                <option value="">No EPG channel</option>
                                @if(isset($Channel) && $Channel->epgChannel)
                                    <option selected value="{{ $Channel->epgChannel->id }}">{{ $Channel->epgChannel->display_name }} ({{ $Channel->epgChannel->external_id }})</option>
                                @endif
                            </select></div>
                        </div>
                        @endif
                        @if($radio_stream )
						<div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"  id="flexSwitchCheckDefault"  value='1'  name="radio" @if(@$Channel->radio) checked @endif>
                                <label class="form-check-label" for="flexSwitchCheckDefault">{{ __('is Radio?') }}</label>
                            </div>
                        </div>
                        @endif
						<div class="row">
							<div class="col-md-6 col-md-offset-5">
								<button class="btn btn-primary">{{ __('Save')}}</button>
							</div>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>

    @if(isset($Channel))
    <div class="row">
        <div class="col-md-12 col-md-offset-2">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-7"><b>{{ __('URLs')}}</b></div>
                    </div>
                </div>
                <div class="card-body">
                    @foreach ($urls as $url)
                    <form class="form-vertical" role="form" method="POST" action="{{ route('update_channel_url',['id'=>$url->id], false)  }}" enctype="multipart/form-data">
                    <input type="hidden" id="channel_id_{{$url->id}}" name="iptv_channel_id" value="{{$url->iptv_channel_id}}">
                    {{ csrf_field() }}

                        <div class="row">

							<div class="col-md-5">
                                <label for="url_stream_{{$url->id}}" class="col-md-4 control-label">{{ __('URl Stream') }}</label>
								<input id="url_stream_{{$url->id}}" type="text"   class="form-control" name="url_stream" value="@if(isset($url->url_stream)){{ $url->url_stream }}@endif" placeholder="" required >
								@if ($errors->has('url_stream'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('url_stream') }}</strong>
                                    </span>
                                @endif
							</div>

							<div class="col-md-5">
                                <label for="cdn_id_{{$url->id}}" class="col-md-3  control-label">CDN</label>
								<select id="cdn_id_{{$url->id}}" class="form-control" name="iptv_cdn_id"   >
									@foreach($Cdnslist as $cdn)
										<option  @if( $url->iptv_cdn_id ==  $cdn->id)   selected @endif   value="{{ $cdn->id}}">{{$cdn->name }}</option>
									@endforeach
								</select>
							</div>

                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>

                            <div class="col-md-1">
                                <button type="submit" formaction="{{ route('delete_channel_url', $url->id) }}" formmethod="POST" class="btn btn-primary">{{ __('delete') }}</button>
                            </div>
                        </div>
                    </form>
                    @endforeach
                    <form class="form-vertical" role="form" method="POST" action="{{ route('create_channel_url',[], false)  }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" id="new_channel_id" name="iptv_channel_id" value="{{$Channel->id}}">
                        <div class="row">

                            <div class="col-md-5">
                                <label for="new_url_stream" class="col-md-4 control-label">{{ __('URl Stream') }}</label>
                                <input id="new_url_stream" type="text"   class="form-control" name="url_stream" value="" placeholder="" required >
                                @if ($errors->has('url_stream'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('url_stream') }}</strong>
                                    </span>
                                @endif
                            </div>


							<div class="col-md-5">
                                <label for="new_cdn_id" class="col-md-3 control-label">CDN</label>
								<select id="new_cdn_id" class="form-control" name="iptv_cdn_id"   >
									@foreach($Cdnslist as $cdn)
										<option value="{{ $cdn->id}}">{{$cdn->name }}</option>
									@endforeach
								</select>
							</div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">{{ __('Add')}}</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@if(config('modules.epg.enabled', true))
@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const source = document.getElementById('epg_source_id');
    const search = document.getElementById('epg_search');
    const channel = document.getElementById('epg_channel_id');
    let timer;
    async function loadChannels() {
        const current = channel.value;
        channel.innerHTML = '<option value="">No EPG channel</option>';
        if (!source.value) return;
        const url = new URL(@json(route('epg.channels.search')), window.location.origin);
        url.searchParams.set('source_id', source.value);
        if (search.value) url.searchParams.set('q', search.value);
        const response = await fetch(url, {headers: {'Accept': 'application/json'}});
        if (!response.ok) return;
        for (const item of await response.json()) {
            const option = new Option(`${item.display_name} (${item.external_id})`, item.id, false, String(item.id) === current);
            channel.add(option);
        }
    }
    source.addEventListener('change', loadChannels);
    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(loadChannels, 300); });
});
</script>
@endsection
@endif
