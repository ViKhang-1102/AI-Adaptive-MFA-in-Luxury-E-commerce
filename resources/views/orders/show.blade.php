@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm font-medium text-neutral-500 hover:text-primary transition-colors group mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Orders
            </a>
            <div class="flex items-center gap-4">
                <h1 class="text-3xl font-serif font-bold text-primary">Order {{ $order->order_number }}</h1>
                @php
                    $statusStyles = [
                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'review' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'paid' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
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

                @if($order->status === 'review')
                    <div class="ml-4">
                        <a href="{{ route('support.contact', ['order_id' => $order->id]) }}" class="inline-flex items-center gap-2 px-3 py-1 bg-orange-600 text-white rounded-full text-xs font-semibold hover:bg-orange-700 transition">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            Contact Support to Verify
                        </a>
                    </div>
                @endif
            </div>
            <p class="text-neutral-500 mt-2">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
        </div>
        
        @if($order->status === 'pending' && $order->payment_method === 'online' && in_array($order->payment_status, ['unpaid', 'pending']))
        <div>
            <a href="{{ route('paypal.create', $order) }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gold text-primary font-bold rounded-xl hover:bg-gold-light transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                <i data-lucide="credit-card" class="w-5 h-5"></i> Pay Now (${{ number_format($order->total_amount, 2) }})
            </a>
        </div>
        @endif
    </div>

    @if(session('order_notifications'))
    <div class="mb-8 space-y-4">
        @foreach(session('order_notifications') as $note)
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl flex items-start gap-3 shadow-sm text-blue-800">
            <i data-lucide="info" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <p class="text-sm font-medium">{{ $note['message'] }}</p>
        </div>
        @endforeach
    </div>
    @endif

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
                            'pending' => ['icon' => 'clock', 'label' => 'Order Placed', 'desc' => 'We have received your order'],
                            'review' => ['icon' => 'shield-alert', 'label' => 'Under Review', 'desc' => 'Your order is being reviewed by our support team'],
                            'paid' => ['icon' => 'credit-card', 'label' => 'Payment Completed', 'desc' => 'Payment received successfully'],
                            'processing' => ['icon' => 'settings', 'label' => 'Processing', 'desc' => 'Seller is preparing your items'],
                            'shipped' => ['icon' => 'truck', 'label' => 'Shipped', 'desc' => 'Your item is on the way'],
                            'delivered' => ['icon' => 'package-check', 'label' => 'Delivered', 'desc' => 'Order successfully delivered'],
                        ];
                        
                        $completedStages = ['pending'];

                        switch ($order->status) {
                            case 'review':
                                $completedStages = ['pending', 'review'];
                                break;
                            case 'paid':
                                $completedStages = ['pending', 'paid'];
                                break;
                            case 'processing':
                                $completedStages = ['pending', 'paid', 'processing'];
                                break;
                            case 'shipped':
                                $completedStages = ['pending', 'paid', 'processing', 'shipped'];
                                break;
                            case 'delivered':
                                $completedStages = ['pending', 'paid', 'processing', 'shipped', 'delivered'];
                                break;
                            default:
                                $completedStages = ['pending'];
                                break;
                        }
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
                                    $isCompleted = in_array($key, $completedStages);
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
                                        class="w-20 h-20 object-cover rounded-xl border border-neutral-100 shadow-sm group-hover:border-gold/50 transition-colors" alt="{{ $item->product_name }}">
                                @else
                                    <div class="w-20 h-20 bg-neutral-50 rounded-xl border border-neutral-100 flex items-center justify-center group-hover:border-gold/50 transition-colors">
                                        <i data-lucide="image" class="w-6 h-6 text-neutral-300"></i>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-bold text-primary mb-1">{{ $item->product_name }}</h3>
                                    <p class="text-sm text-neutral-500">Qty: {{ $item->quantity }} x ${{ number_format($item->product_price, 2) }}</p>
                                </div>
                            </div>
                            
                            <div class="text-right flex flex-col items-end gap-2">
                                <span class="font-bold text-lg text-primary">${{ number_format($item->subtotal, 2) }}</span>
                                @if($item->product && $order->status === 'delivered')
                                    <a href="#" class="inline-flex items-center text-xs font-bold text-gold hover:text-gold-dark transition-colors buy-again-btn" 
                                        data-product-id="{{ $item->product_id }}" data-quantity="{{ $item->quantity }}">
                                        <i data-lucide="rotate-cw" class="w-3 h-3 mr-1"></i> Buy Again
                                    </a>
                                @endif
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
                        <span class="font-medium text-neutral-700">${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-neutral-500">Shipping</span>
                        <span class="font-medium text-neutral-700">${{ number_format($order->shipping_fee, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between items-center text-sm text-green-600">
                        <span>Discount</span>
                        <span class="font-bold">-${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    @endif
                </div>

                <div class="pt-6 border-t border-neutral-100 mb-8">
                    <div class="flex justify-between items-center">
                        <span class="text-lg text-primary font-bold">Total</span>
                        <span class="text-2xl font-bold text-gold">${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <p class="text-xs text-neutral-400 mt-2 text-right">Payment: {{ ucfirst($order->payment_method) }}</p>
                </div>

                <!-- Actions -->
                <div class="space-y-3">
                    @if($order->canBeCancelled())
                    <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-white border-2 border-red-100 text-red-600 font-bold rounded-xl hover:bg-red-50 hover:border-red-200 transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i> Cancel Order
                        </button>
                    </form>
                    @elseif($order->status === 'cancelled')
                    <form action="{{ route('orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Delete order permanently?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-neutral-100 text-neutral-600 font-bold rounded-xl hover:bg-neutral-200 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i> Delete Order
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100">
                <h3 class="text-lg font-serif font-bold text-primary mb-6 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-gold"></i> Delivery Address
                </h3>
                <div class="bg-neutral-50 p-4 rounded-xl border border-neutral-100">
                    <p class="font-bold text-primary mb-1">{{ $order->recipient_name }}</p>
                    <p class="text-sm flex items-center gap-2 text-neutral-600 mb-3 block">
                        <i data-lucide="phone" class="w-3 h-3"></i> {{ $order->recipient_phone }}
                    </p>
                    <p class="text-sm text-neutral-500 leading-relaxed">{{ $order->delivery_address }}</p>
                </div>
            </div>
            
            <!-- Seller Info -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100">
                <h3 class="text-lg font-serif font-bold text-primary mb-6 flex items-center gap-2">
                    <i data-lucide="store" class="w-5 h-5 text-gold"></i> Seller Details
                </h3>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-light/5 text-primary rounded-full flex items-center justify-center">
                        <i data-lucide="store" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="font-bold text-primary">{{ $order->seller->name }}</p>
                        <a href="{{ route('customer.messages.index') }}" class="text-xs text-gold hover:text-gold-dark font-medium transition-colors">Contact Seller</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.buy-again-btn').forEach(btn => {
    btn.addEventListener('click', function(event) {
        event.preventDefault();
        const productId = this.getAttribute('data-product-id');
        const quantity = this.getAttribute('data-quantity');
        
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
    });
});
</script>
@endsection
