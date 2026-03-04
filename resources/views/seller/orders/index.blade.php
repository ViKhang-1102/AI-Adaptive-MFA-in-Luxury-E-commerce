@extends('layouts.app')
@section('title', 'My Orders')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <a href="{{ route('seller.dashboard') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Dashboard</a>
            <h1 class="text-3xl font-bold">My Orders</h1>
        </div>
        <div class="flex gap-2">
            <select id="status-filter" class="px-4 py-2 border rounded">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            No orders found.
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Order ID</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left">Items</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">#{{ $order->id }}</td>
                        <td class="px-6 py-3">{{ $order->customer->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3">{{ $order->items->count() ?? 0 }}</td>
                        <td class="px-6 py-3 text-right font-semibold">${{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-full
                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                            ">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3">{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3">
                            <div class="flex gap-2 flex-wrap">
                                <a href="{{ route('seller.orders.show', $order) }}" class="inline-block px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                @if($order->status === 'pending')
                                    <form method="POST" action="{{ route('seller.orders.confirm', $order) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
                                            <i class="fas fa-check mr-1"></i> Confirm
                                        </button>
                                    </form>
                                @endif
                                @if($order->status === 'confirmed')
                                    <form method="POST" action="{{ route('seller.orders.ship', $order) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-purple-600 text-white text-sm rounded hover:bg-purple-700 transition">
                                            <i class="fas fa-truck mr-1"></i> Ship
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($order->status, ['pending', 'confirmed']))
                                    <form method="POST" action="{{ route('seller.orders.cancel', $order) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition">
                                            <i class="fas fa-times mr-1"></i> Cancel
                                        </button>
                                    </form>
                                @endif

                                @if($order->status === 'cancelled')
                                    <form method="POST" action="{{ route('seller.orders.destroy', $order) }}" class="inline" onsubmit="return confirm('Remove order permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
