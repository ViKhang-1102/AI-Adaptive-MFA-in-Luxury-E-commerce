@extends('layouts.app')

@section('title', 'Contact Admin')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <div class="bg-white rounded-md-lg shadow-sm-md p-6">
        <h1 class="text-2xl font-bold mb-4">Contact Admin</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4">
                {{ session('error') }}
            </div>
        @endif

        <p class="text-sm text-neutral-600 mb-6">
            Need help? Use this form to send a message to our support team. We will prioritize orders that require manual verification.
        </p>

        @if($order)
            <div class="bg-neutral-50 border border-neutral-200 rounded-md p-4 mb-6">
                <p class="font-semibold">Order #{{ $order->id }} ({{ ucfirst($order->status) }})</p>
                <p class="text-sm text-neutral-600">Total: ${{ number_format($order->total_amount, 2) }}</p>
                <p class="text-sm text-neutral-600">Placed on {{ $order->created_at->format('M d, Y H:i') }}</p>
                <p class="text-sm mt-2 text-neutral-800">Please mention this order in your message so our team can verify it quickly.</p>
            </div>

            @if(isset($messages))
                <div id="support-messages" class="bg-white border border-neutral-200 rounded-md p-4 mb-6">
                    <h2 class="text-lg font-semibold mb-3">Recent Support Messages</h2>
                    <div id="support-messages-list" class="space-y-3">
                        @if($messages->isNotEmpty())
                            @foreach($messages as $msg)
                                <div class="p-3 rounded-lg {{ $msg->sender_id === Auth::id() ? 'bg-primary/10' : 'bg-neutral-50' }} border border-neutral-100">
                                    <p class="text-xs text-neutral-500">{{ $msg->created_at->format('M d, Y H:i') }} • {{ $msg->sender->name }}</p>
                                    <p class="mt-1 text-sm text-neutral-800">{{ $msg->message }}</p>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-neutral-500">No messages yet. Our support team will respond here shortly.</p>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        <form method="POST" action="{{ route('support.contact.submit') }}">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order?->id }}">
            <div id="support-data" data-order-id="{{ $order?->id }}" data-current-user="{{ Auth::id() }}"></div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700">Subject</label>
                <input type="text" name="subject" value="{{ old('subject', $order && $order->status === 'review' ? 'Order verification request for Order #' . $order->order_number : '') }}" class="mt-1 w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold" required>
                @error('subject')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700">Message</label>
                <textarea name="message" rows="6" class="mt-1 w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold" required>{{ old('message', $order && $order->status === 'review' ? 'Please review and confirm my order so I can proceed with the payment.' : '') }}</textarea>
                @error('message')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-light transition">Send Message</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const supportData = document.getElementById('support-data');
        const orderId = supportData?.dataset.orderId;
        const currentUserId = supportData?.dataset.currentUser;
        if (!orderId) return;

        const messagesContainer = document.getElementById('support-messages-list');
        if (!messagesContainer) return;

        async function fetchMessages() {
            try {
                const url = new URL('{{ route('support.messages') }}', window.location.origin);
                url.searchParams.set('order_id', orderId);

                const response = await fetch(url);
                if (!response.ok) return;
                const data = await response.json();
                if (!data.messages) return;

                const html = data.messages.map(msg => {
                    const isMe = String(msg.sender_id) === String(currentUserId);
                    const bgClass = isMe ? 'bg-primary/10' : 'bg-neutral-50';
                    const sender = msg.sender?.name || 'Support';
                    const time = new Date(msg.created_at).toLocaleString();
                    return `
                        <div class="p-3 rounded-lg ${bgClass} border border-neutral-100">
                            <p class="text-xs text-neutral-500">${time} • ${sender}</p>
                            <p class="mt-1 text-sm text-neutral-800">${msg.message}</p>
                        </div>
                    `;
                }).join('');

                messagesContainer.innerHTML = html || '<p class="text-sm text-neutral-500">No messages yet. Our support team will respond here shortly.</p>';
            } catch (error) {
                console.error('Unable to fetch latest messages', error);
            }
        }

        setInterval(fetchMessages, 5000);
    })();
</script>
@endpush

@endsection
