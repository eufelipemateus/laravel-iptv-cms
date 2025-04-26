@extends('IPTV::app')


@section('style')
<style>
.row-line{
    margin: 5% 0;
}
.list{
    width: 49%;
    display: inline-block;
}
.form-list-group{
    display: inline-block;
}

.tooltipCopy {
    position: relative;
    display: inline-block;
}

.tooltipCopy .tooltiptext {
    visibility: hidden;
    width: 140px;
    background-color: #555;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px;
    position: absolute;
    z-index: 1;
    bottom: 150%;
    left: 50%;
    margin-left: -75px;
    opacity: 0;
    transition: opacity 0.3s;
}

.tooltipCopy .tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

.tooltipCopy:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
</style>
@endsection

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Customers') }}</h1>
</div>
<div class="container">
    <div class="row row-line">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-3"><b>{{ __('Customer') }} </b></div>
					</div>
                </div>

                <div class="card-body">
					<form class="form-horizontal" role="form" method="POST" action="" enctype="multipart/form-data">

						{{ csrf_field() }}

						<div class="form-group">
							<label for="name" class="col-md-4 control-label">{{ __('Name') }}</label>
							<div class="col-md-6">
								<input id="name" type="text" class="form-control" name="name" value="@if(isset($Customer->name)){{ $Customer->name }}@endif" placeholder="" required autofocus>
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>

                        <div class="form-group">
							<label for="username" class="col-md-4 control-label">{{ __('Username') }}</label>
							<div class="col-md-6">
								<input id="username" type="text" class="form-control" name="username" value="@if(isset($Customer->username)){{ $Customer->username }}@endif" placeholder="" required autofocus>
                                @if ($errors->has('username'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>

                        <div class="form-group">
							<label for="hash_acess" class="col-md-4 control-label">{{ __('Hash') }}</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input id="hash_acess" type="text" class="form-control" readonly value="@if(isset($Customer->hash_acess)){{ $Customer->hash_acess }}@endif" placeholder="" required autofocus>
                                    @if ($errors->has('hash_acess'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('hash_acess') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <button  name="regenerate" value="ok"  class="btn btn-success">{{ __('Regenerate')}}</button>
                                </div>
                            </div>
						</div>

                        <div class="form-group">
							<label for="industry" class="col-md-4 control-label">{{ __('Industry') }}</label>
							<div class="col-md-6">
								<input id="industry" type="text" class="form-control" name="industry" value="@if(isset($Customer->industry)){{ $Customer->industry }}@endif" placeholder=""  autofocus>
                                @if ($errors->has('industry'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('industry') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>
                        <div class="form-group">
							<label for="address" class="col-md-4 control-label">{{ __('Address') }}</label>
							<div class="col-md-6">
								<input id="address" type="text" class="form-control" name="address" value="@if(isset($Customer->address)){{ $Customer->address }}@endif" placeholder=""  autofocus>
                                @if ($errors->has('address'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>
                        <div class="form-group">
							<label for="phone" class="col-md-4 control-label">{{ __('Phone') }}</label>
							<div class="col-md-6">
								<input id="phone" type="text" class="form-control" name="phone" value="@if(isset($Customer->phone)){{ $Customer->phone }}@endif" placeholder=""  autofocus>
                                @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>
                        <div class="form-group">
							<label for="email" class="col-md-4 control-label">{{ __('E-mail') }}</label>
							<div class="col-md-6">
								<input id="email" type="text" class="form-control" name="email" value="@if(isset($Customer->email)){{ $Customer->email }}@endif" placeholder=""  autofocus>
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>
                        <div class="form-group">
							<label for="tax_no" class="col-md-4 control-label">{{ __('Tax Number') }}</label>
							<div class="col-md-6">
								<input id="tax_no" type="text" class="form-control" name="tax_no" value="@if(isset($Customer->tax_no)){{ $Customer->tax_no }}@endif" placeholder=""  autofocus>
                                @if ($errors->has('tax_no'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('tax_no') }}</strong>
                                    </span>
                                @endif
							</div>
						</div>

                        <div class="form-group">
							<label for="plan_id" class="col-md-4 control-label">{{ __('Plan') }}</label>
							<div class="col-md-6">
								<select id="plan_id" class="form-control" name="iptv_plan_id"   >
									@foreach($Planslist as $plan)
										<option @if(isset($Customer->plan_id)) @if($Customer->plan_id==$plan->id)  selected @endif @endif value="{{ $plan->id}}">{{$plan->name }}</option>
									@endforeach
								</select>
							</div>
						</div>

                        <div class="form-group">
							<label for="cdn_id" class="col-md-4 control-label">{{ __('CDN') }}</label>
							<div class="col-md-6">
								<select id="cdn_id" class="form-control" name="iptv_cdn_id"   >
									@foreach($Cdnslist as $cdn)
										<option @if(isset($Customer->iptv_cdn_id)) @if($Customer->iptv_cdn_id==$cdn->id)  selected @endif @endif value="{{ $cdn->id}}">{{$cdn->name }}</option>
									@endforeach
								</select>
							</div>
						</div>

                        <div class="form-group">
							<label for="cdn_id" class="col-md-4 control-label">{{ __('Due Day') }}</label>
                            <div class="col-md-6">
								<select id="cdn_id" class="form-control" name="due_day"   >
                                    @for ($i = 5; $i < 30; $i=$i+5)
										<option @if(isset($Customer->due_day)) @if($Customer->due_day == $i) selected   @endif @endif value="{{$i}}">{{$i}}</option>
                                    @endfor
                                </select>
							</div>
						</div>

                        @if(@$Customer)
                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                type="checkbox"
                                id="active-switch"
                                value='1'
                                name="active"
                                @if(@$Customer->active) checked @endif>
                                <label class="form-check-label"
                                for="active-switch">{{ __('Customer Active') }}<label>
                            </div>
                        </div>
                        @endif
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

    @if(isset($Customer))
    <div class="row row-line">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-3"><b>{{ __('Personal URL') }} </b></div>
					</div>
                </div>

                <div class="card-body">

                    <div class="form-group">
                        <label for="name" class="col-md-4 control-label">{{ __('Url') }}</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" readonly value="@if(isset($Customer->personal_url)){{ $Customer->personal_url }} @endif" id="myInput">
                            </div>
                            <div class="col-md-3">
                                <div class="tooltipCopy">
                                    <button  class="btn btn-info" onclick="myFunction()" onmouseout="outFunc()">
                                        <span class="tooltiptext" id="myTooltip">Copy to clipboard</span>
                                        Copy text
                                    </button>
                                </div>
                            </div>
                        </div>
					</div>

                </div>
            </div>
        </div>
	</div>

    <div class="row row-line">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-7"><b>{{ __('Plans Additional')}}</b></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list">
                        <ul class="list-group">
                        @foreach($PlansAdditionallist as $plan)
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>{{  $plan->name}}</b>
                                    </div>
                                    <div class="col-md-6">
                                        <form id="form-plan-{{$plan->id}}" class="form-list-group"  action="{{ route('add_additional', ['customer_id' => $Customer->id], false) }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" id="iptv-plan-id" name="iptv_plan_id" value="{{$plan->id}}">
                                            <button  id="id-plan-{{$plan->id}}"type="submit" class="btn btn-link">add plan</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        </ul>
                    </div>
                    <div class="list">
                    <ul class="list-group">
                        @foreach($CustomerPlansAddionalList as $plan)

                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>{{  $plan->name}}</b>
                                    </div>
                                    <div class="col-md-6">
                                        <form id="form-plan-{{$plan->id}}" class="form-list-group"  action="{{ route('del_additional', ['customer_id' => $Customer->id], false) }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" id="iptv-plan-id" name="iptv_plan_id" value="{{$plan->id}}">
                                            <button  id="id-plan-{{$plan->id}}"type="submit" class="btn btn-link">delete plan</button>
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

    <div class="row row-line">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
						<div class="col-md-10"><b>{{ __('Invoces')}}</b></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="">
                        <ul class="list-group">
                        @foreach($CustomerInvoceList as $invoce)
                            <li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-9">
                                        <b> {{ __('Due date')}}:  {{  $invoce->duedate_at }}</b>
                                    </div>

                                    @if($invoce->is_payed)
                                        <div class="col-md-3">
                                        {{ __('Paid')}}
                                        </div>
                                    @elseif($invoce->is_canceled)
                                        <div class="col-md-3">
                                        {{ __('Canceled')}}
                                        </div>
                                    @else
                                        <div class="col-md-3">


                                            <form id="form-invoce-{{$invoce->id}}" class="form-list-group"  action="{{ route('pay_customer', ['customer_id' => $Customer->id, 'id'=> $invoce->id], false) }}" method="POST">
                                                {{ csrf_field() }}
                                                <input type="hidden" id="iptv-invoce-id" name="iptv_invoce_id" value="{{$invoce->id}}">
                                                <button  id="id-invoce-{{$invoce->id}}"type="submit" class="btn btn-link">Pagar</button>
                                            </form>



                                            <form id="form-invoce-cancel-{{$invoce->id}}" class="form-list-group"  action="{{ route('cancel_customer', ['customer_id' => $Customer->id, 'id'=> $invoce->id], false) }}" method="POST">
                                                {{ csrf_field() }}
                                                <input type="hidden" id="iptv-invoce-id" name="iptv_invoce_id" value="{{$invoce->id}}">
                                                <button  id="id-invoce-cancel-{{$invoce->id}}"type="submit" class="btn btn-link">Cancelar</button>
                                            </form>
                                        </div>
                                    @endif
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

<script>
    function myFunction() {
        var copyText = document.getElementById("myInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);

        var tooltip = document.getElementById("myTooltip");
        tooltip.innerHTML = "Copied: " + copyText.value;
    }

    function outFunc() {
        var tooltip = document.getElementById("myTooltip");
        tooltip.innerHTML = "Copy to clipboard";
    }
</script>
@endsection
