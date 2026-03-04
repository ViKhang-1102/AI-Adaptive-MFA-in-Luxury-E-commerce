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
                <div class="text-right">
                    <span class="px-3 py-1 rounded-full text-white text-sm font-bold {{ 
                        $order->status === 'pending' ? 'bg-yellow-600' : 
                        ($order->status === 'confirmed' ? 'bg-blue-600' : 
                        ($order->status === 'shipped' ? 'bg-purple-600' : 
                        ($order->status === 'delivered' ? 'bg-green-600' : 'bg-red-600'))) 
                    }}">
                        {{ ucfirst($order->status) }}
                    </span>
                    @if($order->delivered_at)
                    <p class="text-xs text-white mt-1">Delivered: {{ $order->delivered_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b">
            <div>
                <h3 class="font-bold text-lg mb-2">Customer</h3>
                <p class="text-gray-700">{{ $order->customer->name ?? 'Guest Customer' }}</p>
                <p class="text-gray-600">{{ $order->customer->email ?? 'N/A' }}</p>
                <p class="text-gray-600">{{ $order->customer->phone ?? 'N/A' }}</p>
            </div>
            <div>
                <h3 class="font-bold text-lg mb-2">Delivery Address</h3>
                <p class="text-gray-700">{{ $order->delivery_address ?? 'N/A' }}</p>
                <p class="text-gray-600">{{ $order->recipient_name ?? '' }}</p>
                <p class="text-gray-600">{{ $order->recipient_phone ?? '' }}</p>
            </div>
        </div>

        <!-- Order Items -->
        <div class="p-6 border-b">
            <h3 class="font-bold text-lg mb-4">Order Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left">Image</th>
                            <th class="px-4 py-2 text-left">Product</th>
                            <th class="px-4 py-2 text-center">Quantity</th>
                            <th class="px-4 py-2 text-right">Price</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->items as $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">
                                @if($item->product && $item->product->images->first())
                                    <img src="{{ asset('storage/' . $item->product->images->first()->image) }}" alt="{{ $item->product->name }}" class="w-12 h-12 object-cover rounded">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2 font-semibold">{{ $item->product->name ?? 'Product Removed' }}</td>
                            <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right">${{ number_format($item->price, 2) }}</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format(($item->quantity * $item->price), 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-gray-500">No items in this order</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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

            @if($order->status === 'shipped')
                <form method="POST" action="{{ route('seller.orders.deliver', $order) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        <i class="fas fa-check-double mr-2"></i> Mark as Delivered
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
