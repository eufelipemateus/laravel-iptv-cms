
@foreach($dashs as $dash)
<div class="col-md-4">
    <div class="card dash">
        <div class="card-header">
            <div class="row">
                <div class="col-md-12">
                    {{ __($dash::$title) }}
                </div>
            </div>
        </div>

        <div class="card-body">

        {{ $dash::view(); }}
        </div>
    </div>
</div>
@endforeach
