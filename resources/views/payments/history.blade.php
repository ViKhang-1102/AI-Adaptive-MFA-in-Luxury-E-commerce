@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-serif font-bold text-primary">Payment History</h1>
        <p class="text-sm text-neutral-500">Only successfully paid online orders are shown here.</p>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white rounded-md-2xl shadow-sm-soft p-12 text-center border border-neutral-100">
            <div class="w-20 h-20 bg-neutral-50 rounded-md-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="receipt" class="w-10 h-10 text-neutral-300"></i>
            </div>
            <h2 class="text-xl font-medium text-neutral-800 mb-2">No payments found</h2>
            <p class="text-neutral-500 mb-6">You haven't made any online payments yet.</p>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-primary text-white font-medium rounded-md-full hover:bg-primary-light transition-all shadow-sm-sm hover:shadow-sm-hover hover:-translate-y-0.5">
                View All Orders
            </a>
        </div>
    @else
        <div class="bg-white rounded-md-2xl shadow-sm-soft border border-neutral-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50 border-b border-neutral-100 text-sm font-medium text-neutral-500">
                            <th class="py-4 px-6">Order ID</th>
                            <th class="py-4 px-6">Date</th>
                            <th class="py-4 px-6 text-right">Amount</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach($orders as $order)
                        <tr class="hover:bg-neutral-50/50 transition-colors group">
                            <td class="py-4 px-6">
                                <span class="font-medium text-primary">#{{ $order->order_number }}</span>
                                <div class="text-xs text-neutral-400 mt-1">
                                    {{ $order->items->count() }} item(s)
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-neutral-600">
                                {{ $order->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="py-4 px-6 text-right font-medium text-primary">
                                ${{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-md-full mr-1.5"></span>
                                    Success
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <a href="{{ route('paypal.success', ['order_id' => $order->id]) }}" class="text-sm font-medium text-gold hover:text-gold-dark transition-colors inline-block transform group-hover:scale-105">
                                    View Receipt
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($orders->hasPages())
                <div class="border-t border-neutral-100 p-6 bg-neutral-50">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
