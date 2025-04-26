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
    <!-- <h1 class="h3 mb-0 text-gray-800">{{ __('Invoce') }}</h1> -->
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-6"><b>{{ __('Invoce') }}</b></div>
					</div>
                </div>

                <div class="card-body">
					<form class="form-horizontal" role="form" method="POST" action="" enctype="multipart/form-data">

						{{ csrf_field() }}

						<div class="form-group">
							<label for="duedate_at" class="col-md-4 control-label">{{ __('Due date') }}</label>
							<div class="col-md-6">
                                <input id="duedate_at" type="text"   class="form-control" name="duedate_at" value="@if(isset($CustomerInvoce->duedate_at)){{ $CustomerInvoce->duedate_at }}@endif" placeholder="" required autofocus>
							</div>
						</div>

                        <div class="form-group">
							<label for="payeddate_at" class="col-md-4 control-label">{{ __('Payment') }}</label>
							<div class="col-md-6">
								<input id="payeddate_at" type="text"   class="form-control" name="payeddate_at" value="@if(isset($CustomerInvoce->payeddate_at)){{ $CustomerInvoce->payeddate_at }}@endif" placeholder="" disabled autofocus>
							</div>
						</div>

                        <div class="form-group">
							<label for="canceleddate_at" class="col-md-4 control-label">{{ __('Canceled') }}</label>
							<div class="col-md-6">
								<input id="canceleddate_at" type="text"   class="form-control" name="canceleddate_at" value="@if(isset($CustomerInvoce->canceleddate_at)){{ $CustomerInvoce->canceleddate_at }}@endif" placeholder="" disabled autofocus>
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

</div>
@endsection

