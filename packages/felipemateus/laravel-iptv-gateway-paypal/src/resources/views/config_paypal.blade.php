@extends('IPTV::app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('PayPal') }}</h1>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
					<div class="row">
						<div class="col-md-3"><b>{{ __('Config Paypal') }}</b></div>
                    </div>
				</div>

                <div class="card-body">
                    <form class="form-horizontal" role="form" method="POST" action="" enctype="multipart/form-data">
                        {{ csrf_field() }}


                        <div class="form-group">
							<label for="client_id" class="col-md-4 control-label">{{ __('Client ID') }}</label>
							<div class="col-md-6">
								<input id="client_id" type="text" class="form-control" name="client_id" value="@if(isset($config_paypal->client_id)){{ $config_paypal->client_id }}@endif" placeholder="" required autofocus>
                                @if ($errors->has('client_id'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('client_id') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>


                        <div class="form-group">
							<label for="client_secret" class="col-md-4 control-label">{{ __('Client Secret') }}</label>
							<div class="col-md-6">
								<input id="client_secret" type="text" class="form-control" name="client_secret" value="@if(isset($config_paypal->client_secret)){{ $config_paypal->client_secret }}@endif" placeholder="" required autofocus>
                                @if ($errors->has('client_secret'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('client_secret') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>


                        <div class="form-group">
							<label for="enviroment" class="col-md-4 control-label">{{ __('Enviroment') }}</label>
                            <div class="col-md-6">
								<select id="enviroment" class="form-control" name="enviroment"   >
									<option @if(isset($config_paypal->enviroment)) @if($config_paypal->enviroment == 'sandbox') selected   @endif @endif value="sandbox">Sandbox</option>
                                    <option @if(isset($config_paypal->enviroment)) @if($config_paypal->enviroment == 'production') selected   @endif @endif value="production">Production</option>
                                </select>
							</div>
						</div>

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
</div>
@endsection
