@extends('IPTV::app')

@section('style')
<style>
.row{
    margin: 1% 0;
}
.list{
    width: 49%;
    display: inline-block;
}
.form-list-group{
    display: inline-block;
}
</style>
@endsection

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Tax') }}</h1>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-6"><b>{{ __('Add Tax') }}</b></div>
					</div>
                </div>

                <div class="card-body">
					<form class="form-horizontal" role="form" method="POST" action="" enctype="multipart/form-data">

						{{ csrf_field() }}

						<div class="form-group">
							<label for="name" class="col-md-4 control-label">{{ __('Name') }}</label>
							<div class="col-md-6">
								<input id="name" type="text"   class="form-control" name="name" value="@if(isset($Tax->name)){{ $Tax->name }}@endif" placeholder="" required autofocus>
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
						</div>

                        <div class="form-group">
							<label for="porcent" class="col-md-4 control-label">{{ __('Porcent') }}</label>
							<div class="col-md-6">
								<input id="porcent" type="number" min="0" max="100"  class="form-control" name="porcent" value="@if(isset($Tax->porcent)){{ $Tax->porcent }}@endif" placeholder="" required autofocus>
                                @if ($errors->has('porcent'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('porcent') }}</strong>
                                    </span>
                                @endif
                            </div>
						</div>


                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"  id="active"  value='1'  name="active" @if(@$Tax->active) checked @endif>
                                <label class="form-check-label" for="active">{{ __('Tax Active?') }}<label>
                            </div>
                        </div>


						<div class="row">
							<div class="col-md-6 col-md-offset-5">
								<button  class="btn btn-primary">{{ __('Save')  }}</button>
							</div>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>
@endsection
