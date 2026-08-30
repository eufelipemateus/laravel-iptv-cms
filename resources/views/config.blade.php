@extends('app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{  __("IPTV Config")  }}</h1>
    <!--<a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
         class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>-->
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-3">
                            Form
                        </div>
                    </div>
                </div>

                <div class="card-body">
					<form id="config-form" class="form-horizontal" role="form" method="POST" action="{{ url()->current()  }}" enctype="multipart/form-data">

						{{ csrf_field() }}

						<input type="hidden" name="confirm_mode_change" id="confirm_mode_change" value="0">

						<div class="form-group mb-4">
							<label class="col-md-6 control-label"><strong>{{ __('OPERATION_MODE_LABEL') }}</strong></label>
							<div class="col-md-8 mt-2">
								<div class="form-check">
									<input
										class="form-check-input"
										type="radio"
										name="mode"
										id="mode-m3u8"
										value="m3u8"
										@if($is_m3u8_mode) checked @endif
									>
									<label class="form-check-label" for="mode-m3u8">
										{{ __('OPERATION_MODE_M3U8') }}
									</label>
								</div>
								<div class="form-check">
									<input
										class="form-check-input"
										type="radio"
										name="mode"
										id="mode-dtv3"
										value="dtv3"
										@if($is_dtv3_mode) checked @endif
									>
									<label class="form-check-label" for="mode-dtv3">
										{{ __('OPERATION_MODE_DTV3') }}
									</label>
								</div>
								<small class="form-text text-muted mt-2 d-block">{{ __('OPERATION_MODE_DESCRIPTION') }}</small>

								@if ($errors->has('mode'))
									<span class="help-block text-danger">
										<strong>{{ $errors->first('mode') }}</strong>
									</span>
								@endif
							</div>
						</div>

                        @foreach($config_list  as $config)

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input class="custom-control-input" type="checkbox" id="flexSwitchCheckDefault-{{$config['id']}}" value="1" name="{{$config['name']}}" @if(@$config['val']) checked @endif>
                                <label class="custom-control-label" for="flexSwitchCheckDefault-{{$config['id']}}">{{ __($config['name']) }}</label>
                            </div>
                        </div>
                        @endforeach

                        <div class="form-group">
							<label for="group_id" class="col-md-4 control-label">{{ __('Locale') }}</label>
							<div class="col-md-6">
								<select id="group_id" class="form-control" name="CURRENT_LOCALE" >
									@foreach($locales as $key => $locale)
										<option @if($current_locate==$key)  selected @endif  value="{{ $key}}">{{$locale }}</option>
									@endforeach
								</select>
							</div>
						</div>

                        @foreach($inputs as $input)
                        <div class="form-group">
							<label for="name-{{$input['name']}}" class="col-md-4 control-label">{{ __($input['name']) }}:</label>
							<div class="col-md-6">
								<input id="name-{{$input['name']}}" type="text"   class="form-control" name="{{$input['name']}}" value="@if(isset($input['val'])){{ $input['val']}}@endif" placeholder="" required autofocus>
							</div>
						</div>
                        @endforeach

						<div class="row">
							<div class="col-md-6 col-md-offset-5">
								<button class="btn btn-primary"> {{ __('Save') }}</button>
							</div>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>

</div>

<script>
	(function () {
		const form = document.getElementById('config-form');
		const currentMode = @json($mode->value);

		if (!form) {
			return;
		}

		form.addEventListener('submit', function (event) {
			const selected = form.querySelector('input[name="mode"]:checked');
			const confirmInput = document.getElementById('confirm_mode_change');

			if (!selected || !confirmInput) {
				return;
			}

			const modeChanged = selected.value !== currentMode;
			if (!modeChanged) {
				confirmInput.value = '0';

				return;
			}

			const accepted = window.confirm(@json(__('OPERATION_MODE_CONFIRMATION')));
			if (!accepted) {
				event.preventDefault();

				return;
			}

			confirmInput.value = '1';
		});
	})();
</script>
@endsection
