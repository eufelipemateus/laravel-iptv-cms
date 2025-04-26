@extends('IPTV::app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('CDN')   }}</h1>
    <a href="{{ route('add_cdn') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
         class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add CDN')}}</a>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
					<div class="row">
						<div class="col-md-3"><h3>CDN List</h3></div>
					</div>
				</div>

                <div class="card-body">
					@foreach($list as $cdn)
						<div class="row">

                            <div class="col-md-3">
								{{ $cdn->slug }}
							</div>

							<div class="col-md-3">
								{{ $cdn->name }}
							</div>

                            @if($url_cdn && !$donwload)
                            <div class="col-md-2">
							    <a href="{{  route('cdn-playslit',$cdn->slug) }}" target="_blank">Playslit</a>
							</div>
                            @endif
                            @if ($url_cdn && $donwload)
                            <div class="col-md-2">
							  <a href="{{  route('cdn-playslit',$cdn->slug) }}">Donwload</a>
							</div>
                            @endif

							<div class="col-md-2">
							  <a href="{{  route('show_cdn',$cdn->id) }}">{{ __('edit') }}</a>
							</div>
                            @if ($cdn->canDelete())
							<div class="col-md-2">
							  <a href="{{  route('delete_cdn',$cdn->id) }}">{{ __('delete')}}</a>
							</div>
                            @endif

						</div>
					@endforeach


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
