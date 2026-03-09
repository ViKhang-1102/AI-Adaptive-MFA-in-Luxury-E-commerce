@extends('layouts.app')
@section('title', 'Manage Order #' . $order->order_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('seller.orders.index') }}" class="inline-flex items-center text-sm font-medium text-neutral-500 hover:text-primary transition-colors group mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Orders
            </a>
            <div class="flex items-center gap-4">
                <h1 class="text-3xl font-serif font-bold text-primary">Order #{{ $order->id }}</h1>
                @php
                    $statusStyles = [
                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'shipped' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'delivered' => 'bg-green-50 text-green-700 border-green-200',
                        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                    ];
                    $badgeClass = $statusStyles[$order->status] ?? 'bg-neutral-50 text-neutral-700 border-neutral-200';
                @endphp
                <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full border {{ $badgeClass }}">
                    {{ $order->status }}
                </span>
            </div>
            <p class="text-neutral-500 mt-2">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        
        <div>
             @if(in_array($order->status, ['pending', 'processing']))
                <form method="POST" action="{{ route('seller.orders.confirm', $order) }}" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-light transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                        <i data-lucide="check-circle" class="w-5 h-5"></i> Confirm Order
                    </button>
                </form>
            @endif

            @if($order->status === 'confirmed')
                <form method="POST" action="{{ route('seller.orders.ship', $order) }}" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-light transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                        <i data-lucide="truck" class="w-5 h-5"></i> Ship Order
                    </button>
                </form>
            @endif

            @if($order->status === 'shipped')
                <form method="POST" action="{{ route('seller.orders.deliver', $order) }}" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                        <i data-lucide="package-check" class="w-5 h-5"></i> Mark Delivered
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Timeline -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100 overflow-hidden">
                <h2 class="text-xl font-serif font-bold text-primary mb-6">Order Status</h2>
                
                <div class="relative py-4">
                    <!-- Progress Bar Background (hidden on very small screens, shown vertically otherwise) -->
                    <div class="absolute left-8 sm:left-1/2 top-4 bottom-4 w-px bg-neutral-100 sm:-translate-x-1/2"></div>
                    
                    @php
                        $stages = [
                            'pending' => ['icon' => 'clock', 'label' => 'Order Placed', 'desc' => 'Awaiting your confirmation'],
                            'processing' => ['icon' => 'credit-card', 'label' => 'Payment Processed', 'desc' => 'Payment has been cleared'],
                            'confirmed' => ['icon' => 'check-circle', 'label' => 'Confirmed', 'desc' => 'Order accepted by you'],
                            'shipped' => ['icon' => 'truck', 'label' => 'Shipped', 'desc' => 'Item is out for delivery'],
                            'delivered' => ['icon' => 'package-check', 'label' => 'Delivered', 'desc' => 'Order fulfilled successfully'],
                        ];
                        
                        // If it's a COD order, we don't show the processing stage in the timeline.
                        if ($order->payment_method === 'cod') {
                            unset($stages['processing']);
                        }
                        
                        $statusKeys = array_keys($stages);
                        $currentIndex = array_search($order->status, $statusKeys);
                        if ($currentIndex === false) $currentIndex = -1;
                        if ($order->status === 'cancelled') $currentIndex = -1;
                    @endphp
                    
                    <div class="space-y-8 relative">
                        @if($order->status === 'cancelled')
                             <div class="flex flex-col sm:flex-row items-start sm:items-center relative z-10 w-full pl-20 sm:pl-0">
                                <div class="absolute left-0 sm:left-1/2 w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center border-4 border-white shadow-soft sm:-translate-x-1/2">
                                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                                </div>
                                <div class="sm:w-1/2 sm:pr-16 sm:text-right w-full min-h-[4rem] flex flex-col justify-center">
                                    <h4 class="font-bold text-red-600 sm:text-right text-left">Order Cancelled</h4>
                                </div>
                                <div class="sm:w-1/2 sm:pl-16 w-full hidden sm:block"></div>
                            </div>
                        @else
                            @foreach($stages as $key => $stage)
                                @php
                                    $isCompleted = array_search($key, $statusKeys) <= $currentIndex;
                                    $isCurrent = $key === $order->status;
                                    $iconBg = $isCompleted ? 'bg-primary text-gold' : 'bg-neutral-50 text-neutral-400';
                                    if ($isCurrent) $iconBg = 'bg-gold text-primary';
                                    $iconBorder = $isCurrent ? 'ring-4 ring-gold/20' : '';
                                    $textColor = $isCompleted ? 'text-primary' : 'text-neutral-400';
                                @endphp
                                <div class="flex flex-col sm:flex-row items-start sm:items-center relative z-10 w-full pl-20 sm:pl-0">
                                    <div class="absolute left-0 sm:left-1/2 top-0 sm:top-1/2 sm:-translate-y-1/2 w-16 h-16 {{ $iconBg }} {{ $iconBorder }} rounded-full flex items-center justify-center border-4 border-white shadow-soft transition-all sm:-translate-x-1/2">
                                        <i data-lucide="{{ $stage['icon'] }}" class="w-6 h-6"></i>
                                    </div>
                                    
                                    <div class="sm:w-1/2 sm:pr-16 sm:text-right w-full min-h-[4rem] flex flex-col justify-center">
                                        <h4 class="font-bold {{ $textColor }} sm:text-right text-left">{{ $stage['label'] }}</h4>
                                        <p class="text-sm text-neutral-500 sm:text-right text-left">{{ $stage['desc'] }}</p>
                                    </div>
                                    <div class="sm:w-1/2 sm:pl-16 w-full hidden sm:block">
                                        @if($isCompleted && $key === 'delivered' && $order->delivered_at)
                                            <p class="text-sm font-medium text-neutral-500">{{ $order->delivered_at->format('M d, Y') }}</p>
                                            <p class="text-xs text-neutral-400">{{ $order->delivered_at->format('H:i') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-2xl shadow-soft border border-neutral-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-neutral-100 bg-neutral-50/50">
                    <h2 class="text-xl font-serif font-bold text-primary">Order Items</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        @foreach($order->items as $item)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-4 flex-1">
                                @if($item->product && $item->product->images->first())
                                    <img src="{{ asset('storage/' . $item->product->images->first()->image) }}" 
                                        class="w-20 h-20 object-cover rounded-xl border border-neutral-100 shadow-sm group-hover:border-gold/50 transition-colors" alt="{{ $item->product->name ?? 'Product' }}">
                                @else
                                    <div class="w-20 h-20 bg-neutral-50 rounded-xl border border-neutral-100 flex items-center justify-center group-hover:border-gold/50 transition-colors">
                                        <i data-lucide="image" class="w-6 h-6 text-neutral-300"></i>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-bold text-primary mb-1">{{ $item->product->name ?? 'Product Removed' }}</h3>
                                    <p class="text-sm text-neutral-500">Qty: {{ $item->quantity }} x ${{ number_format($item->product_price ?? 0, 2) }}</p>
                                </div>
                            </div>
                            
                            <div class="text-right flex flex-col items-end gap-2">
                                <span class="font-bold text-lg text-primary">${{ number_format(($item->subtotal ?? (($item->product_price ?? 0) * $item->quantity)), 2) }}</span>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="border-neutral-100">
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="space-y-6">
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100">
                <h3 class="text-xl font-serif font-bold text-primary mb-6">Order Summary</h3>

                <div class="space-y-4 mb-6">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-neutral-500">Subtotal</span>
                        <span class="font-medium text-neutral-700">${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>

                <div class="pt-6 border-t border-neutral-100 mb-8">
                    <div class="flex justify-between items-center">
                        <span class="text-lg text-primary font-bold">Total earnings</span>
                        <span class="text-2xl font-bold text-gold">${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                @if(in_array($order->status, ['pending', 'processing', 'confirmed']))
                <div class="space-y-3">
                    <form method="POST" action="{{ route('seller.orders.cancel', $order) }}" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                        @csrf
                        <input type="text" name="reason" placeholder="Reason for cancellation..." required class="w-full px-4 py-3 mb-3 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all shadow-sm text-sm">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-white border-2 border-red-100 text-red-600 font-bold rounded-xl hover:bg-red-50 hover:border-red-200 transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i> Cancel Order
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Customer Info -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100">
                <h3 class="text-lg font-serif font-bold text-primary mb-6 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-gold"></i> Customer Details
                </h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-primary-light/5 text-primary rounded-full flex items-center justify-center">
                        <span class="font-serif font-bold text-lg">{{ substr($order->customer->name ?? 'G', 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-bold text-primary">{{ $order->customer->name ?? 'Guest Customer' }}</p>
                        <a href="{{ route('seller.messages.index') }}" class="text-xs text-gold hover:text-gold-dark font-medium transition-colors">Message Customer</a>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-neutral-600">
                    <p class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-neutral-400"></i> {{ $order->customer->email ?? 'N/A' }}</p>
                    <p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-neutral-400"></i> {{ $order->customer->phone ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100">
                <h3 class="text-lg font-serif font-bold text-primary mb-6 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-gold"></i> Delivery Address
                </h3>
                <div class="bg-neutral-50 p-4 rounded-xl border border-neutral-100">
                    <p class="font-bold text-primary mb-1">{{ $order->recipient_name ?? '' }}</p>
                    <p class="text-sm flex items-center gap-2 text-neutral-600 mb-3 block">
                        <i data-lucide="phone" class="w-3 h-3"></i> {{ $order->recipient_phone ?? '' }}
                    </p>
                    <p class="text-sm text-neutral-500 leading-relaxed">{{ $order->delivery_address ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
