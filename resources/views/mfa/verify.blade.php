@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Multi-Factor Authentication</h3>
    <p>Your Risk Score: <strong>{{ $riskScore }}</strong></p>
    <p>Please complete MFA verification before continuing the payment process for order #{{ $order->order_number }}.</p>
    <!-- In a real application you would display a form here to enter an MFA code -->
    <form method="post" action="{{ route('paypal.create', $order) }}">
        @csrf
        <input type="hidden" name="mfa_verified" value="1" />
        <button class="btn btn-primary">Verify and Continue Payment</button>
    </form>
</div>
@endsection
