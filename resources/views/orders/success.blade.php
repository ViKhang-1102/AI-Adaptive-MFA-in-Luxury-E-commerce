@extends('layouts.app')

@section('title', 'Order Placed Successfully')

@section('content')
<div class="min-h-screen bg-neutral-50 flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg text-center transform transition-all duration-500 hover:scale-105">
        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-6 relative">
            <svg class="h-12 w-12 text-green-600 absolute animate-ping" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="opacity: 0.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <svg class="h-12 w-12 text-green-600 relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h2 class="text-center text-3xl font-extrabold text-gray-900 tracking-tight mb-2">
            Order Confirmed
        </h2>
        
        <p class="text-center text-sm text-gray-600 mb-8">
            Thank you for shopping with LuxGuard. Your order has been placed successfully and is being processed. 
            You will receive a notification once your package is shipped.
        </p>

        <div class="space-y-4">
            <a href="{{ route('orders.index') }}" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary shadow-sm-soft hover:shadow-sm-hover transition-all duration-300">
                View My Orders
            </a>
            
            <a href="{{ route('home') }}" class="group relative w-full flex justify-center py-3 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300">
                Continue Shopping
            </a>
        </div>
    </div>
</div>
@endsection
