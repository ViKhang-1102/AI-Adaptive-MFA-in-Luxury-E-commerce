@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Xác thực đa yếu tố</h3>
    <p>Risk score của bạn: <strong>{{ $riskScore }}</strong></p>
    <p>Vui lòng thực hiện bước xác thực MFA trước khi tiếp tục thanh toán cho đơn hàng #{{ $order->order_number }}.</p>
    <!-- In a real application you would display a form here to enter an MFA code -->
    <form method="post" action="{{ route('paypal.create', $order) }}">
        @csrf
        <input type="hidden" name="mfa_verified" value="1" />
        <button class="btn btn-primary">Xác thực và tiếp tục thanh toán</button>
    </form>
</div>
@endsection
