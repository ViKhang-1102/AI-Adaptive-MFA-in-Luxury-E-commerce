@extends('layouts.app')
@section('title', 'Order #' . $order->id)
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('seller.orders.index') }}" class="text-blue-600 hover:underline">&larr; Back to Orders</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Order Header -->
        <div class="p-6 border-b">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold mb-2">Order #{{ $order->id }}</h1>
                    <p class="text-gray-600">{{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-white text-sm font-bold {{ 
                    $order->status === 'pending' ? 'bg-yellow-600' : 
                    ($order->status === 'confirmed' ? 'bg-blue-600' : 
                    ($order->status === 'shipped' ? 'bg-purple-600' : 
                    ($order->status === 'delivered' ? 'bg-green-600' : 'bg-red-600'))) 
                }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b">
            <div>
                <h3 class="font-bold text-lg mb-2">Customer</h3>
                <p class="text-gray-700">{{ $order->user->name }}</p>
                <p class="text-gray-600">{{ $order->user->email }}</p>
            </div>
            <div>
                <h3 class="font-bold text-lg mb-2">Delivery Address</h3>
                <p class="text-gray-700">{{ $order->address->address_line_1 ?? 'N/A' }}</p>
                <p class="text-gray-600">{{ $order->address->city ?? '' }}, {{ $order->address->state ?? '' }} {{ $order->address->zip_code ?? '' }}</p>
            </div>
        </div>

        <!-- Order Items -->
        <div class="p-6 border-b">
            <h3 class="font-bold text-lg mb-4">Order Items</h3>
            <div class="space-y-4">
                @foreach($order->orderItems as $item)
                <div class="flex justify-between items-center p-4 bg-gray-50 rounded">
                    <div>
                        <p class="font-semibold">{{ $item->product->name }}</p>
                        <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }} × ${{ number_format($item->price, 2) }}</p>
                    </div>
                    <p class="font-bold">${{ number_format($item->quantity * $item->price, 2) }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Order Summary -->
        <div class="p-6 border-b bg-gray-50">
            <div class="space-y-2 text-right">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>${{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-lg">
                    <span>Total:</span>
                    <span>${{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="p-6 flex gap-3">
            @if($order->status === 'pending')
                <form method="POST" action="{{ route('seller.orders.confirm', $order) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        <i class="fas fa-check mr-2"></i> Confirm Order
                    </button>
                </form>
            @endif

            @if($order->status === 'confirmed')
                <form method="POST" action="{{ route('seller.orders.ship', $order) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition">
                        <i class="fas fa-truck mr-2"></i> Ship Order
                    </button>
                </form>
            @endif

            @if(in_array($order->status, ['pending', 'confirmed']))
                <form method="POST" action="{{ route('seller.orders.cancel', $order) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        <i class="fas fa-times mr-2"></i> Cancel Order
                    </button>
                </form>
            @endif

            <a href="{{ route('seller.orders.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
</div>
@endsection
