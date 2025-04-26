@extends('IPTV::app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Payment Gateway') }}</h1>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
					<div class="row">
						<div class="col-md-3"><b>{{ __('Gateways List') }}</b></div>
                    </div>
				</div>

                <div class="card-body">
					@foreach($list as $gateway)

						<div class="row">
							<div class="col-md-4">
								<b>{{ $gateway->code }}</b>
                            </div>
							<div class="col-md-2">
								{{ $gateway->name }}
							</div>

                            <div class="col-md-2">
							@if( $gateway->active)	{{ __('Actived') }}  @else   {{ __('Inactived') }}  @endif
							</div>
                            <!--
                            <div class="col-md-2">
                                @if( $gateway->active)
                                    <a href="{{ route('inactive_gateway',['gateway'=>$gateway->code]) }} ">{{ __('Inactive') }}</a>
                                @else
                                    <a href="{{ route('active_gateway',['gateway'=>$gateway->code]) }} ">{{ __('Active') }}</a>

                                @endif
                            </div> -->
						</div>
					@endforeach


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
