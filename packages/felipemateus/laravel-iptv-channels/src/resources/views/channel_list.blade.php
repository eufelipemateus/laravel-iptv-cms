@extends('IPTV::app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Channels')   }}</h1>
    <a href="{{ route('add_channel') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
         class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add Channel') }}</a>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
					<div class="row">
						<div class="col-md-3"><b>{{ __('Channels List') }}</b></div>
                    </div>
				</div>

                <div class="card-body">
					@foreach($list as $channel)
						<div class="row">
							<div class="col-md-1">
								<b>{{ $channel->number }}.</b>
							</div>
							<div class="col-md-2">
								<img src="{{ url($channel->logo) }}"  width="50"  />
							</div>
							<div class="col-md-3">
								{{ $channel->name }}
							</div>
							<div class="col-md-2">
								{{ $channel->group->name }}
							</div>

							<div class="col-md-2">
							  <a href="{{  route('show_channel',$channel->id) }}">{{ __('edit') }}</a>
							</div>
							<div class="col-md-2">
							  <a href="{{  route('delete_channel',$channel->id) }}">{{ __('delete')}}</a>
							</div>
						</div>
					@endforeach


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
