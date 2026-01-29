@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Header -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h1 class="text-2xl font-bold mb-4">{{ $order->order_number }}</h1>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Order Date</p>
                        <strong>{{ $order->created_at->format('M d, Y') }}</strong>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <span class="px-2 py-1 rounded text-sm font-bold
                            @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                            @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                            @elseif($order->status === 'delivered') bg-green-100 text-green-800
                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Payment</p>
                        <strong>{{ ucfirst($order->payment_method) }}</strong>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Seller</p>
                        <strong>{{ $order->seller->name }}</strong>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Items</h2>
                <table class="w-full">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left pb-2">Image</th>
                            <th class="text-left pb-2">Product</th>
                            <th class="text-center pb-2">Quantity</th>
                            <th class="text-right pb-2">Price</th>
                            <th class="text-right pb-2">Total</th>
                            <th class="text-center pb-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3">
                                @if($item->product && $item->product->images->first())
                                    <img src="{{ asset('storage/' . $item->product->images->first()->image) }}" 
                                        class="w-16 h-16 object-cover rounded" alt="{{ $item->product_name }}">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3">{{ $item->product_name }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">₫{{ number_format($item->product_price, 0) }}</td>
                            <td class="text-right">₫{{ number_format($item->subtotal, 0) }}</td>
                            <td class="text-center">
                                @if($item->product)
                                    <a href="#" class="text-blue-600 hover:underline text-sm font-bold buy-again-btn" 
                                        data-product-id="{{ $item->product_id }}" data-quantity="{{ $item->quantity }}">
                                        Buy Again
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Delivery Information -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Delivery Address</h2>
                <div>
                    <p class="font-bold">{{ $order->recipient_name }}</p>
                    <p>{{ $order->recipient_phone }}</p>
                    <p class="text-gray-600">{{ $order->delivery_address }}</p>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="bg-white p-6 rounded-lg shadow h-fit">
            <h3 class="text-xl font-bold mb-4">Order Summary</h3>

            <div class="space-y-3 border-b pb-4 mb-4">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>₫{{ number_format($order->subtotal, 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping:</span>
                    <span>₫{{ number_format($order->shipping_fee, 0) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Discount:</span>
                    <span>-₫{{ number_format($order->discount_amount, 0) }}</span>
                </div>
                @endif
            </div>

            <div class="flex justify-between text-xl font-bold mb-6">
                <span>Total:</span>
                <span>₫{{ number_format($order->total_amount, 0) }}</span>
            </div>

            @if($order->canBeCancelled())
            <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Cancel this order?')">
                @csrf
                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
                    Cancel Order
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.buy-again-btn').forEach(btn => {
    btn.addEventListener('click', function(event) {
        event.preventDefault();
        
        const productId = this.getAttribute('data-product-id');
        const quantity = this.getAttribute('data-quantity');
        
        // Create and submit a form to add to cart
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("cart.add") }}';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="product_id" value="${productId}">
            <input type="hidden" name="quantity" value="${quantity}">
        `;
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    });
});
</script>
@endsection
