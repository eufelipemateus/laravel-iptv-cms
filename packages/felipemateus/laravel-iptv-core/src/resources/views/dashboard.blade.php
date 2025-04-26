@extends('IPTV::app')


@section('style')
<style>
.card.dash{
    margin: 5% 0;
}
</style>
@endsection

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{  __("Dashboard")  }}</h1>
</div>

<div class="container">
    <div class="row">

        {{  FelipeMateus\IPTVCore\Facades\IPTVDashboard::view();  }}

    </div>
</div>
@endsection
