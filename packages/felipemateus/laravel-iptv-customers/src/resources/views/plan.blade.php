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
    <h1 class="h3 mb-0 text-gray-800">{{ __('Plans') }}</h1>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-6"><b>{{ __('Plan') }}</b></div>
					</div>
                </div>

                <div class="card-body">
					<form class="form-horizontal" role="form" method="POST" action="" enctype="multipart/form-data">

						{{ csrf_field() }}

						<div class="form-group">
							<label for="name" class="col-md-4 control-label">{{ __('Name') }}</label>
							<div class="col-md-6">
								<input id="name" type="text"   class="form-control" name="name" value="@if(isset($Plan->name)){{ $Plan->name }}@endif" placeholder="" required autofocus>
							</div>
						</div>

                        <div class="form-group">
							<label for="name" class="col-md-4 control-label">{{ __('Price') }}</label>
							<div class="col-md-6">
								<input id="name" type="number"   class="form-control" name="price" value="@if(isset($Plan->price)){{ $Plan->price }}@endif" placeholder="" required autofocus>
							</div>
						</div>

                        <div class="form-group">
							<label for="tax_id" class="col-md-4 control-label">{{ __('Tax') }}</label>
							<div class="col-md-6">
								<select id="tax_id" class="form-control" name="iptv_tax_vat_id"   >
                                    <option value="null">0% </option>
									@foreach($TaxVatList as $tax)
										<option @if(isset($Plan->iptv_tax_vat_id)) @if($Plan->iptv_tax_vat_id==$tax->id)  selected @endif @endif value="{{ $tax->id}}">{{$tax->name }} - {{ $tax->porcent}}% </option>
									@endforeach
								</select>
							</div>
						</div>


                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"  id="active"  value='1'  name="active" @if(@$Plan->active) checked @endif>
                                <label class="form-check-label" for="active">{{ __('Plan Active?') }}<label>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"  id="additional"  value='1'  name="additional" @if(@$Plan->additional) checked @endif>
                                <label class="form-check-label" for="additional">{{ __('Is additional?') }}<label>
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

    @if(isset($Plan))
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-7"><b>{{ __('Channels Groups')}}</b></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list">
                        <ul class="list-group">
                        @foreach($GroupList as $group)
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>{{  $group->name}}</b>
                                    </div>
                                    <div class="col-md-6">
                                        <form id="form-group-{{$group->id}}" class="form-list-group"  action="{{ route('add_group_to_plan', ['plan_id' => $Plan->id]) }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" id="iptv-group-id" name="iptv_group_id" value="{{$group->id}}">
                                            <button  id="id-group-{{$group->id}}"type="submit" class="btn btn-link">add group</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                    <div class="list">
                    <ul class="list-group">
                        @foreach($PlanGroupList as $group)
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>{{  $group->name}}</b>
                                    </div>
                                    <div class="col-md-6">
                                        <form id="form-group-{{$group->id}}" class="form-list-group"  action="{{ route('delete_group_to_plan', ['plan_id' => $Plan->id]) }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" id="iptv-group-id" name="iptv_group_id" value="{{$group->id}}">
                                            <button  id="id-group-{{$group->id}}"type="submit" class="btn btn-link">delete group</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
