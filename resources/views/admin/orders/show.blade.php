@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('admin.orders.index') }}" class="text-primary hover:underline">← Back to Orders</a>
    </div>

    <div class="bg-white rounded-md-lg shadow-sm-md p-6">
        <!-- Order Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold mb-4">Order #{{ $order->id }}</h1>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-neutral-600 text-sm">Order Date</p>
                    <p class="font-semibold">{{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-neutral-600 text-sm">Status</p>
                    @php
                        $statusClass = match($order->status) {
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            default => 'bg-blue-100 text-blue-800'
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-md-full text-sm font-semibold {{ $statusClass }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div>
                    <p class="text-neutral-600 text-sm">Total Amount</p>
                    <p class="font-semibold text-lg">${{ number_format($order->total_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-neutral-600 text-sm">Payment Status</p>
                    @php
                        $paymentStatus = ($order->payment && $order->payment->status === 'completed') ? 'Paid' : 'Unpaid';
                        $paymentClass = ($order->payment && $order->payment->status === 'completed') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    @endphp
                    <span class="px-3 py-1 rounded-md-full text-sm font-semibold {{ $paymentClass }}">
                        {{ $paymentStatus }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Security Review Summary -->
        @if($securityAudit)
            <div class="mb-6 pb-6 border-b">
                <h2 class="text-xl font-bold mb-4">Security Review</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                        <p class="text-sm text-neutral-600">Risk Score</p>
                        <p class="text-2xl font-bold text-primary">{{ number_format($securityAudit->risk_score, 1) }}</p>
                        <p class="text-xs text-neutral-500">Level: {{ ucfirst($securityAudit->level) }}</p>
                    </div>
                    <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                        <p class="text-sm text-neutral-600">AI Reason</p>
                        <div class="text-sm text-neutral-700 mt-2">
                            @if(is_array($securityAudit->metadata['risk_explanation']['score_breakdown'] ?? null))
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($securityAudit->metadata['risk_explanation']['score_breakdown'] as $line)
                                        <li>{{ $line }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p>{{ $securityAudit->metadata['risk_explanation']['score_breakdown'] ?? 'No details available.' }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                        <p class="text-sm text-neutral-600">Face Verify Cache</p>
                        <div class="text-sm text-neutral-700 mt-2">
                            @if($faceCacheInfo)
                                <p class="font-semibold">Landmark Count: {{ count($faceCacheInfo['landmarks'] ?? []) }}</p>
                                <p class="text-xs text-neutral-500">(Cached data found)</p>
                            @else
                                <p class="text-sm text-neutral-500">No local face cache data available.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-lg border border-neutral-200 bg-white p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-neutral-700">Identity Profile</h3>
                            <span class="text-xs text-neutral-500">Stored Image</span>
                        </div>
                        @if($order->customer && $order->customer->identity_image)
                            <img src="{{ asset('storage/' . $order->customer->identity_image) }}" class="w-full h-48 object-cover rounded-md" alt="Identity Image">
                        @else
                            <div class="h-48 rounded-md bg-neutral-100 flex items-center justify-center text-neutral-400">
                                <span>No identity image available</span>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-lg border border-neutral-200 bg-white p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-neutral-700">Last Face Scan (Live)</h3>
                            <span class="text-xs text-neutral-500">Captured at review</span>
                        </div>
                        @if($securityAudit && isset($securityAudit->metadata['face_scan_image']))
                            <img src="{{ asset('storage/' . $securityAudit->metadata['face_scan_image']) }}" class="w-full h-48 object-cover rounded-md" alt="Live Scan">
                        @else
                            <div class="h-48 rounded-md bg-neutral-100 flex items-center justify-center text-neutral-400">
                                <span>No live scan available</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Customer Information -->
        <div class="mb-6 pb-6 border-b">
            <h2 class="text-xl font-bold mb-4">Customer Information</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-neutral-600 text-sm">Name</p>
                    <p class="font-semibold">{{ $order->customer->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-neutral-600 text-sm">Email</p>
                    <p class="font-semibold">{{ $order->customer->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-neutral-600 text-sm">Phone</p>
                    <p class="font-semibold">{{ $order->customer->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-neutral-600 text-sm">Address</p>
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
                    <p class="text-neutral-600 text-sm">Seller Name</p>
                    <p class="font-semibold">{{ $order->seller->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-neutral-600 text-sm">Email</p>
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
                    <thead class="bg-neutral-100 border-b">
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
                        <tr class="border-b hover:bg-neutral-50">
                            <td class="px-4 py-2">
                                @if($item->product && $item->product->images->first())
                                    <img src="{{ asset('storage/' . $item->product->images->first()->image) }}" alt="{{ $item->product->name }}" class="w-12 h-12 object-cover rounded-md">
                                @else
                                    <div class="w-12 h-12 bg-neutral-200 rounded-md flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <div>
                                    <p class="font-semibold">{{ $item->product_name ?? $item->product->name ?? 'Product Removed' }}</p>
                                    <p class="text-neutral-600 text-xs">SKU: {{ $item->product->sku ?? 'N/A' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right">${{ number_format($item->product_price ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-right font-semibold">${{ number_format($item->subtotal ?? (($item->product_price ?? 0) * $item->quantity), 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-neutral-50 rounded-md-lg p-4">
            <div class="flex justify-between mb-2">
                <span>Subtotal:</span>
                <span>${{ number_format($order->items->sum(fn($item) => $item->subtotal ?? (($item->product_price ?? 0) * $item->quantity)), 2) }}</span>
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
        <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div class="flex flex-wrap gap-3">
                <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-md-lg hover:bg-gray-700">
                    Print
                </button>

                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md-lg hover:bg-primary-light hover:-translate-y-0.5">
                    Back to Orders
                </a>

                @if($order->status === 'review')
                    <form method="POST" action="{{ route('admin.orders.verify', $order) }}" class="inline" id="admin-approve-form">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md-lg hover:bg-emerald-700" id="approve-btn">
                            Approve & Unlock
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.reject', $order) }}" class="inline" id="admin-reject-form">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md-lg hover:bg-red-700" id="reject-btn">
                            Reject & Cancel
                        </button>
                    </form>
                @endif
            </div>

            <div class="w-full md:w-1/2 bg-neutral-50 rounded-lg border border-neutral-200 p-4">
                <h3 class="text-sm font-semibold text-neutral-700 mb-3">Quick Reply to Customer</h3>
                <form id="admin-quick-reply" class="space-y-3">
                    <textarea id="admin-reply-message" class="w-full border border-neutral-200 rounded-md p-3 text-sm" rows="3" placeholder="Type a quick response...">Your account has been verified successfully. Please complete the payment to proceed with order shipment.</textarea>
                    <button type="button" id="send-reply-btn" class="w-full py-2 bg-gold text-primary font-semibold rounded-md hover:bg-gold-light transition">Send to Customer</button>
                </form>
                <p class="text-xs text-neutral-500 mt-2">This will be delivered to the customer via the messaging system.</p>
            </div>
        </div>

        @push('scripts')
        <script>
            const sendReplyBtn = document.getElementById('send-reply-btn');
                const sendReplyUrl = "{{ route('admin.orders.message', $order) }}";
                const csrfToken = "{{ csrf_token() }}";

                if (sendReplyBtn) {
                    sendReplyBtn.addEventListener('click', async () => {
                        const message = document.getElementById('admin-reply-message').value.trim();
                        if (!message) return;

                        const response = await fetch(sendReplyUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                message,
                            }),
                        });

                        if (response.ok) {
                            sendReplyBtn.textContent = 'Sent!';
                            sendReplyBtn.disabled = true;
                            setTimeout(() => {
                                sendReplyBtn.textContent = 'Send to Customer';
                                sendReplyBtn.disabled = false;
                            }, 2000);
                        }
                    });
                }

            // AJAX actions for approve/reject
            const approveForm = document.getElementById('admin-approve-form');
            const rejectForm = document.getElementById('admin-reject-form');

            function ajaxSubmit(form, url) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const btn = form.querySelector('button');
                    const originalText = btn.textContent;
                    btn.textContent = 'Processing...';
                    btn.disabled = true;

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    if (response.ok) {
                        // Redirect to the next pending order (or empty state) after action completes
                        window.location.href = "{{ route('admin.orders.pending') }}";
                    } else {
                        const data = await response.json();
                        alert(data.error || 'Something went wrong.');
                        btn.textContent = originalText;
                        btn.disabled = false;
                    }
                });
            }

            const verifyUrl = "{{ route('admin.orders.verify', $order) }}";
            const rejectUrl = "{{ route('admin.orders.reject', $order) }}";

            if (approveForm) {
                ajaxSubmit(approveForm, verifyUrl);
            }
            if (rejectForm) {
                ajaxSubmit(rejectForm, rejectUrl);
            }
        </script>
        @endpush
    </div>
</div>
@endsection
