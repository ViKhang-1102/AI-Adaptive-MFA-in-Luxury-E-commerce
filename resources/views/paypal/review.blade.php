@extends('layouts.app')

@section('title', 'Review Your Order')

@section('content')
<div class="min-h-screen bg-neutral-50 flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border-t-4 border-blue-600">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
                <i class="fas fa-file-invoice-dollar text-2xl text-blue-600"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">
                Review Your Order
            </h2>
            <p class="text-sm text-gray-500 mb-6">
                Please review your items and total before confirming the payment.
            </p>
        </div>

        <!-- Bill Details -->
        <div class="bg-gray-50 rounded-lg p-6 space-y-4 border border-gray-200">
            <div class="flex justify-between items-center border-b pb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Invoice #{{ $order->order_number }}</h3>
                    <p class="text-xs text-gray-400">Date: {{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full uppercase tracking-wide">
                        Pending Payment
                    </span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="space-y-3 mt-4">
                @foreach($order->items as $item)
                <div class="flex justify-between items-center text-sm">
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-900 font-medium">{{ $item->product_name }}</span>
                        <span class="text-gray-400">x{{ $item->quantity }}</span>
                    </div>
                    <span class="text-gray-700 font-semibold">${{ number_format($item->subtotal, 2) }}</span>
                </div>
                @endforeach
            </div>

            <div class="border-t pt-4 space-y-2 mt-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="text-gray-700">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Shipping Fee</span>
                    <span class="text-gray-700">${{ number_format($order->shipping_fee, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between items-center text-sm text-green-600">
                    <span>Discount</span>
                    <span>-${{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <span class="text-lg font-bold text-gray-900">Total Amount</span>
                    <span class="text-2xl font-black text-blue-600">
                        ${{ number_format($order->total_amount, 2) }} {{ env('PAYPAL_CURRENCY', 'USD') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="flex flex-col items-center justify-center p-6 bg-blue-50 rounded-lg border-2 border-dashed border-blue-200">
            <h4 class="text-sm font-bold text-blue-800 mb-4 uppercase tracking-widest">Scan to Pay</h4>
            <div class="bg-white p-4 rounded-xl shadow-inner border border-blue-100">
                <img src="{{ $qrCodeUrl }}" alt="Payment QR Code" class="w-48 h-48">
            </div>
            <p class="text-xs text-blue-600 mt-4 text-center px-4">
                You can scan this QR code with your mobile device to complete the transaction on the go.
            </p>
        </div>

        <!-- Actions -->
        <div class="mt-8 space-y-4">
            <form action="{{ route('paypal.capture', $order) }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="PayerID" value="{{ $payerID }}">
                
                <button type="submit" class="w-full flex justify-center items-center py-4 px-6 border border-transparent text-lg font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50 shadow-xl transform transition hover:-translate-y-1 active:scale-95 duration-200">
                    <i class="fas fa-check-circle mr-2"></i> Confirm and Pay Now
                </button>
            </form>

            <div class="flex justify-center">
                <a href="{{ route('paypal.cancel', ['order_id' => $order->id]) }}" class="text-sm text-gray-400 hover:text-red-500 transition-colors duration-200 underline">
                    Cancel and Return to Merchant
                </a>
            </div>
        </div>

        <div class="text-center pt-4 border-t border-gray-100">
            <p class="text-[10px] text-gray-400 uppercase tracking-widest">
                Secured by PayPal &bull; 256-bit SSL Encryption
            </p>
        </div>
    </div>
</div>
@endsection
