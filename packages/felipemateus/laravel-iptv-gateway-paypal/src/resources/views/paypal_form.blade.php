<form target="paypal" action="{{ route('checkout_paypal') }}" method="post">
    {{ csrf_field() }}
    <input type="hidden" name="invoce_id" value="{{ $invoce_id }}">
    <input type="hidden" name="invoce_price" value="{{ $invoce_price }}">
    <button type="submit" >Pay with PayPal</button>

</form>
