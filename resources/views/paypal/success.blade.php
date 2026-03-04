@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="alert alert-success">
        <h3>✅ Thanh toán thành công</h3>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Chi tiết đơn hàng #{{ $order->order_number }}</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <tr>
                    <td><strong>Tổng tiền khách trả:</strong></td>
                    <td>
                            {{ number_format($order->total_amount, 0) }} USD
                            ({{ number_format($order->total_amount, 2) }} {{ env('PAYPAL_CURRENCY', 'USD') }})
                        </td>
                </tr>
                <tr class="table-info">
                    <td><strong>💼 Phí Platform (Admin 10%):</strong></td>
                    <td>
                        {{ number_format($adminFee, 2) }} {{ env('PAYPAL_CURRENCY', 'USD') }}
                    </td>
                </tr>
                <tr class="table-warning">
                    <td><strong>👤 Số tiền Seller nhận (90%):</strong></td>
                    <td>
                        {{ number_format($sellerAmount, 2) }} {{ env('PAYPAL_CURRENCY', 'USD') }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Email PayPal Seller:</strong></td>
                    <td>
                        {{ $sellerPayPalEmail ?? 'Chưa cập nhật' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ url('/') }}" class="btn btn-primary">Quay về trang chủ</a>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">Xem chi tiết đơn hàng</a>
    </div>
</div>
@endsection
