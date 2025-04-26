

<div>
    @foreach($list as $gateway)

        <div>
        {{ $gateway->name }}
        </div>

    @endforeach
    <a href='{{ route("list_gateway") }}'>{{ __('Show All Gateways') }}</a>
</div>
