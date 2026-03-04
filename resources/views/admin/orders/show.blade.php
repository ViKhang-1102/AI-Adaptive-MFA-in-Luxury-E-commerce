@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:underline">← Back to Orders</a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <!-- Order Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold mb-4">Order #{{ $order->id }}</h1>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-gray-600 text-sm">Order Date</p>
                    <p class="font-semibold">{{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Status</p>
                    @php
                        $statusClass = match($order->status) {
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            default => 'bg-blue-100 text-blue-800'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Amount</p>
                    <p class="font-semibold text-lg">${{ number_format($order->total_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Payment Status</p>
                    @php
                        $paymentStatus = ($order->payment && $order->payment->status === 'completed') ? 'Paid' : 'Unpaid';
                        $paymentClass = ($order->payment && $order->payment->status === 'completed') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $paymentClass }}">
                        {{ $paymentStatus }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="mb-6 pb-6 border-b">
            <h2 class="text-xl font-bold mb-4">Customer Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600 text-sm">Name</p>
                    <p class="font-semibold">{{ $order->customer->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Email</p>
                    <p class="font-semibold">{{ $order->customer->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Phone</p>
                    <p class="font-semibold">{{ $order->customer->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Address</p>
                    <p class="font-semibold">{{ $order->shipping_address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Seller Information -->
        @if($order->seller)
        <div class="mb-6 pb-6 border-b">
            <h2 class="text-xl font-bold mb-4">Seller Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600 text-sm">Seller Name</p>
                    <p class="font-semibold">{{ $order->seller->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Email</p>
                    <p class="font-semibold">{{ $order->seller->email ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Order Items -->
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">Order Items</h2>
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
                        @foreach($order->items as $item)
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
                            <td class="px-4 py-2">
                                <div>
                                    <p class="font-semibold">{{ $item->product->name ?? 'Product Removed' }}</p>
                                    <p class="text-gray-600 text-xs">SKU: {{ $item->product->sku ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right">${{ number_format($item->price, 2) }}</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format(($item->price * $item->quantity), 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex justify-between mb-2">
                <span>Subtotal:</span>
                <span>${{ number_format($order->items->sum(fn($item) => $item->price * $item->quantity), 2) }}</span>
            </div>
            @if($order->shipping_fee)
                <div class="flex justify-between mb-2">
                <span>Shipping Fee:</span>
                <span>${{ number_format($order->shipping_fee, 2) }}</span>
            </div>
            @endif
            @if($order->tax_amount)
            <div class="flex justify-between mb-2">
                <span>Tax:</span>
                <span>${{ number_format($order->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-lg font-bold border-t pt-2">
                <span>Total:</span>
                <span>${{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Print
            </button>
            <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Back to Orders
            </a>
        </div>
    </div>
</div>
@endsection
