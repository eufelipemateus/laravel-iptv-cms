@extends('app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Plans') }}</h1>
    <a href="{{ route('add_customer_plan') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
         class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add Plan') }}</a>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
					<div class="row">
						<div class="col-md-4"><b>{{ __('Plans List') }}</b></div>
					</div>
				</div>

                <div class="card-body">
					@foreach($list as $plan)
					<div class="row">
						<div class="col-md-8">
						{{ $plan->name }}
						</div>
						<div class="col-md-2">
                            <form action="{{ route('delete_customer_plan', $plan->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 align-baseline">{{ __('delete')}}</button>
                            </form>
						</div>
						<div class="col-md-2">
						  <a href="{{ route('show_customer_plan',$plan->id)  }}">{{ __('edit') }}</a>
						</div>

					</div>
					@endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
