@extends('IPTV::app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Tax') }}</h1>
    <a href="{{ route('add_tax') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
         class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add Tax Vat') }}</a>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
					<div class="row">
						<div class="col-md-3"><b>{{ __('Tax List') }}</b></div>
                    </div>
				</div>

                <div class="card-body">
					@foreach($list as $tax)

						<div class="row">
							<div class="col-md-4">
								<b>{{ $tax->name }}</b>
                            </div>
							<div class="col-md-2">
								{{ $tax->porcent }}%
							</div>

                            <div class="col-md-2">
							@if( $tax->active)	{{ __('Active') }}  @else   {{ __('Inactive') }}  @endif
							</div>

							<div class="col-md-2">
							  <a href="{{ route('show_tax',$tax->id) }} ">{{ __('edit') }}</a>
							</div>
							<div class="col-md-2">
							  <a href="{{  route('delete_tax',$tax->id) }}">{{ __('delete')}}</a>
							</div>
						</div>
					@endforeach


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
