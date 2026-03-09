@extends('layouts.app')
@section('title', 'All Orders')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
                ← Dashboard
            </a>
            <h1 class="text-3xl font-bold">Orders</h1>
            <a id="pending-verifications-btn" href="{{ route('admin.orders.pending') }}" class="px-4 py-2 bg-gold text-primary font-semibold rounded-md hover:bg-gold-light transition">
                Pending Verifications
            </a>
        </div>
        <div class="flex gap-2 items-center">
            <label for="status-filter" class="text-sm text-neutral-600">Filter:</label>
            <select id="status-filter" class="px-4 py-2 border rounded-md">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>On Hold</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white p-6 rounded-md-lg shadow-sm text-center text-neutral-500">
            No orders found.
        </div>
    @else
        <div class="bg-white rounded-md-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-neutral-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Order ID</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left">Seller</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-left">Payment</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr class="border-b hover:bg-neutral-50">
                        <td class="px-6 py-3 font-semibold">#{{ $order->id }}</td>
                        <td class="px-6 py-3">
                            <div class="text-sm">
                                <div class="font-medium">{{ $order->customer->name ?? 'Guest' }}</div>
                                <div class="text-neutral-500">{{ $order->customer->email ?? 'N/A' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-3">{{ $order->seller->name ?? 'Unknown' }}</td>
                        <td class="px-6 py-3 text-right font-semibold">${{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-md-full {{ $order->payment_status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($order->payment_status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'review' => 'bg-amber-100 text-amber-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'shipped' => 'bg-purple-100 text-purple-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                $statusLabel = $order->status === 'review' ? 'On Hold' : ucfirst($order->status);
                            @endphp

                            <span class="px-2 py-1 text-sm rounded-md-full {{ $statusClasses[$order->status] ?? 'bg-neutral-100 text-neutral-800' }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-3">{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-primary hover:underline text-sm">View</a>
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

@push('scripts')
<script>
    document.getElementById('status-filter')?.addEventListener('change', function () {
        const status = this.value;
        const url = new URL(window.location.href);
        if (status) {
            url.searchParams.set('status', status);
            url.searchParams.delete('filter');
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    });

    // Ensure the Pending Verifications button always navigates properly
    document.getElementById('pending-verifications-btn')?.addEventListener('click', function (event) {
        event.preventDefault();
        window.location.href = this.href;
    });
</script>
@endpush

