@extends('IPTV::app')

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
					<form class="form-horizontal" role="form" method="POST" action="{{ url()->current()  }}" enctype="multipart/form-data">

						{{ csrf_field() }}

                        @foreach($config_list  as $config)

                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"  id="flexSwitchCheckDefault-{{$config['id']}}"  value='1'  name="{{$config['name']}}" @if(@$config['val']) checked @endif>
                                <label class="form-check-label" for="flexSwitchCheckDefault-{{$config['id']}}">{{ __($config['name']) }}<label>
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
@endsection
