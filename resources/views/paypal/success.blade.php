@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="min-h-screen bg-neutral-50 flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border-t-4 border-blue-500">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-blue-100 mb-6">
                <svg class="h-10 w-10 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">
                Payment Successful
            </h2>
            <p class="text-sm text-gray-600 mb-6">
                Your payment for order <span class="font-bold text-gray-900">#{{ $order->order_number }}</span> has been processed securely.
            </p>
        </div>

        <div class="bg-gray-50 rounded-lg p-6 space-y-4 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Transaction Details</h3>
            
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Total Amount Paid</span>
                <span class="font-bold text-gray-900 text-lg">
                    ${{ number_format($order->total_amount, 2) }} {{ env('PAYPAL_CURRENCY', 'USD') }}
                </span>
            </div>
            
            <div class="flex justify-between items-center text-sm pt-2">
                <span class="text-gray-500"><i class="fas fa-building mr-2"></i>Platform Fee ({{ $adminPercentage ?? '10' }}%)</span>
                <span class="text-gray-700">${{ number_format($adminFee, 2) }} {{ env('PAYPAL_CURRENCY', 'USD') }}</span>
            </div>
            
            <div class="flex justify-between items-center text-sm pt-2 border-t mt-2">
                <span class="text-gray-500"><i class="fas fa-store mr-2"></i>Seller Payment</span>
                <span class="text-gray-700">${{ number_format($sellerAmount, 2) }} {{ env('PAYPAL_CURRENCY', 'USD') }}</span>
            </div>
            
            <div class="flex justify-between items-center text-xs text-gray-400 pt-4 mt-2">
                <span>Seller PayPal</span>
                <span>{{ $sellerPayPalEmail ?? 'Not Provided' }}</span>
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('orders.show', $order) }}" class="flex-1 flex justify-center items-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors duration-200">
                View Order Details
            </a>
            <a href="{{ route('home') }}" class="flex-1 flex justify-center items-center py-3 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
