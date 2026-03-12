<!-- snippet: show PayPal payment button for an order -->
<form action="{{ route('paypal.create', $order) }}" method="get">
    @csrf
    <button type="submit" class="btn btn-primary">
        <i class="fab fa-paypal"></i> Pay with PayPal
    </button>
</form>
