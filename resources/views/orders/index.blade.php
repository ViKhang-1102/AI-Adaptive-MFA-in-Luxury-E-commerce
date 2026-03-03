@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Orders</h1>

    @if(session('order_notifications'))
    <div class="mb-6">
        @foreach(session('order_notifications') as $note)
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-2">
            <p class="text-blue-700">{{ $note['message'] }}</p>
        </div>
        @endforeach
    </div>
    @endif

    @if($orders->isEmpty())
    <div class="bg-white p-8 rounded-lg shadow text-center">
        <p class="text-gray-600 text-lg mb-4">You have no orders yet</p>
        <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Start Shopping
        </a>
    </div>
    @else
    <div class="space-y-4">
        @foreach($orders as $order)
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Order ID</p>
                    <strong>{{ $order->order_number }}</strong>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Date</p>
                    <strong>{{ $order->created_at->format('M d, Y') }}</strong>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <strong>${{ number_format($order->total_amount / env('VND_PER_USD', 23000), 2) }}</strong>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <span class="px-3 py-1 rounded text-sm font-bold
                        @if($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                        @elseif($order->status === 'shipped') bg-purple-100 text-purple-800
                        @elseif($order->status === 'delivered') bg-green-100 text-green-800
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                @if($order->status === 'pending' && $order->payment_method === 'online' && $order->payment_status === 'pending')
                <a href="{{ route('paypal.create', $order) }}" class="text-green-600 hover:underline font-bold">
                    Pay Now
                </a>
                @endif
                <a href="{{ route('orders.show', $order) }}" class="text-blue-600 hover:underline">
                    View Details
                </a>
                <form action="{{ route('orders.buyAgain', $order) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-indigo-600 hover:underline">Buy Again</button>
                </form>
                @if($order->canBeCancelled())
                <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Cancel this order?')">
                    @csrf
                    <button type="submit" class="text-red-600 hover:underline">Cancel Order</button>
                </form>
                @endif
                @if($order->status === 'cancelled')
                <form action="{{ route('orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Delete order permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-600 hover:underline">Delete</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{ $orders->links() }}
    @endif
</div>
@endsection
