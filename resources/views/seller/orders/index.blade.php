@extends('layouts.app')
@section('title', 'My Orders')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">My Orders</h1>
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
                        <td class="px-6 py-3">{{ $order->user->name }}</td>
                        <td class="px-6 py-3">{{ $order->items_count ?? count($order->orderItems) }}</td>
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
                            <a href="{{ route('seller.orders.show', $order) }}" class="text-blue-600 hover:underline text-sm">View</a>
                            @if($order->status === 'pending')
                                <form method="POST" action="{{ route('seller.orders.confirm', $order) }}" class="inline ml-2">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:underline text-sm">Confirm</button>
                                </form>
                            @endif
                            @if($order->status === 'confirmed')
                                <form method="POST" action="{{ route('seller.orders.ship', $order) }}" class="inline ml-2">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:underline text-sm">Ship</button>
                                </form>
                            @endif
                            @if(in_array($order->status, ['pending', 'confirmed']))
                                <form method="POST" action="{{ route('seller.orders.cancel', $order) }}" class="inline ml-2" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:underline text-sm">Cancel</button>
                                </form>
                            @endif
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
