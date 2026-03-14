@extends('layouts.app')

@section('title', 'Contact Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <!-- Breadcrumbs / Back Button -->
    <div class="mb-8">
        <a href="{{ url()->previous() == url()->current() ? route('home') : url()->previous() }}" class="inline-flex items-center text-sm font-medium text-neutral-500 hover:text-primary transition-colors group">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to previous page
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-soft border border-neutral-100 overflow-hidden">
        <div class="p-8 border-b border-neutral-50 bg-neutral-50/30">
            <h1 class="text-3xl font-serif font-bold text-primary mb-2">Contact Support</h1>
            <p class="text-neutral-500 text-sm">
                Need help with your order? Our elite support team is here to assist you with manual verifications and security inquiries.
            </p>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Left Column: Info & Context -->
                <div class="lg:col-span-1">
                    @if($order)
                        <div class="bg-primary rounded-xl p-6 text-white shadow-lg mb-8">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="p-2 bg-white/10 rounded-lg">
                                    <i data-lucide="package" class="w-5 h-5 text-gold"></i>
                                </div>
                                <h3 class="font-bold">Order Details</h3>
                            </div>
                            <div class="space-y-3 text-sm border-t border-white/10 pt-4">
                                <div class="flex justify-between">
                                    <span class="text-white/60">Order Number</span>
                                    <span class="font-mono">{{ $order->order_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white/60">Status</span>
                                    <span class="px-2 py-0.5 bg-gold/20 text-gold rounded text-[10px] font-bold uppercase tracking-wider">{{ $order->status }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-white/60">Total Amount</span>
                                    <span class="font-bold">${{ number_format($order->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center text-gold">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-primary">Response Time</h4>
                                <p class="text-xs text-neutral-500 mt-1">Typical response within 2-4 hours during business days.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center text-gold">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-primary">Secure Verification</h4>
                                <p class="text-xs text-neutral-500 mt-1">Manual verification ensures the highest level of security for your luxury purchases.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Form & Messages -->
                <div class="lg:col-span-2">
                    @if(session('success'))
                        <div class="mb-8 p-4 bg-green-50 border border-green-100 rounded-xl flex items-center gap-3 text-green-700 animate-fade-in">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            <p class="text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if($order && $order->status === 'review')
                        <div class="mb-8 p-6 bg-amber-50 border border-amber-100 rounded-xl">
                            <div class="flex items-center gap-3 text-amber-800 mb-3">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                                <h3 class="font-bold">Action Required</h3>
                            </div>
                            <p class="text-sm text-amber-700 leading-relaxed mb-4">
                                Your order is currently under manual review for security purposes. To expedite the process, please use the pre-filled template below to contact our verification team.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="useTemplate('verification')" class="text-xs font-bold px-4 py-2 bg-amber-100 text-amber-800 rounded-lg hover:bg-amber-200 transition-colors border border-amber-200">
                                    Use Verification Template
                                </button>
                                <button type="button" onclick="useTemplate('payment')" class="text-xs font-bold px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg hover:bg-neutral-200 transition-colors border border-neutral-200">
                                    Use Payment Issue Template
                                </button>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('support.contact.submit') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order?->id }}">
                        
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-primary flex items-center gap-2">
                                Subject
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="subject" name="subject" 
                                value="{{ old('subject', $order && $order->status === 'review' ? 'Verification Request: Order #' . $order->order_number : '') }}" 
                                class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gold/20 focus:border-gold outline-none transition-all text-sm" 
                                placeholder="Enter message subject..." required>
                            @error('subject')<p class="text-red-500 text-[10px] mt-1 font-bold uppercase tracking-wider">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-primary flex items-center gap-2">
                                Message
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea id="message" name="message" rows="8" 
                                class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gold/20 focus:border-gold outline-none transition-all text-sm resize-none" 
                                placeholder="Tell us how we can help..." required>{{ old('message', $order && $order->status === 'review' ? "Dear LuxGuard Verification Team,\n\nI am contacting you regarding my recent order #".$order->order_number." which is currently under manual review.\n\nI would like to confirm that this is a legitimate transaction made by me. Please let me know if any further documentation is required to verify my identity and process this order.\n\nThank you for your assistance." : '') }}</textarea>
                            @error('message')<p class="text-red-500 text-[10px] mt-1 font-bold uppercase tracking-wider">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="w-full sm:w-auto px-8 py-4 bg-primary text-white font-bold rounded-xl hover:bg-primary-light transition-all shadow-sm-soft hover:shadow-sm-hover hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Send Message
                        </button>
                    </form>

                    @if($order && isset($messages) && $messages->isNotEmpty())
                        <div class="mt-12 pt-12 border-t border-neutral-100">
                            <h3 class="text-lg font-bold text-primary mb-6 flex items-center gap-2">
                                <i data-lucide="messages-square" class="w-5 h-5 text-gold"></i>
                                Conversation History
                            </h3>
                            <div id="support-messages-list" class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($messages as $msg)
                                    <div class="flex {{ $msg->sender_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-[80%] {{ $msg->sender_id === Auth::id() ? 'bg-primary text-white rounded-l-2xl rounded-tr-2xl' : 'bg-neutral-100 text-neutral-800 rounded-r-2xl rounded-tl-2xl' }} p-4 shadow-sm">
                                            <p class="text-xs {{ $msg->sender_id === Auth::id() ? 'text-white/60' : 'text-neutral-500' }} mb-1">
                                                {{ $msg->sender->name }} • {{ $msg->created_at->format('H:i, M d') }}
                                            </p>
                                            <p class="text-sm leading-relaxed">{{ $msg->message }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function useTemplate(type) {
        const subject = document.getElementById('subject');
        const message = document.getElementById('message');
        const orderNum = "{{ $order->order_number ?? '' }}";

        if (type === 'verification') {
            subject.value = "Verification Request: Order #" + orderNum;
            message.value = "Dear LuxGuard Verification Team,\n\nI am contacting you regarding my recent order #" + orderNum + " which is currently under manual review.\n\nI would like to confirm that this is a legitimate transaction made by me. Please let me know if any further documentation is required to verify my identity and process this order.\n\nThank you for your assistance.";
        } else if (type === 'payment') {
            subject.value = "Payment Issue: Order #" + orderNum;
            message.value = "Dear LuxGuard Support,\n\nI encountered an issue while attempting to process the payment for my order #" + orderNum + ".\n\nCould you please check if the transaction was successful on your end or if I need to re-initiate the payment process? I am eager to complete this purchase.\n\nBest regards.";
        }
        
        message.focus();
    }

    (function() {
        const orderId = "{{ $order?->id }}";
        const currentUserId = "{{ Auth::id() }}";
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
                    const containerClass = isMe ? 'justify-end' : 'justify-start';
                    const bubbleClass = isMe ? 'bg-primary text-white rounded-l-2xl rounded-tr-2xl' : 'bg-neutral-100 text-neutral-800 rounded-r-2xl rounded-tl-2xl';
                    const textClass = isMe ? 'text-white/60' : 'text-neutral-500';
                    const sender = msg.sender?.name || 'Support';
                    const date = new Date(msg.created_at);
                    const timeStr = date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0') + ', ' + date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    
                    return `
                        <div class="flex ${containerClass}">
                            <div class="max-w-[80%] ${bubbleClass} p-4 shadow-sm">
                                <p class="text-xs ${textClass} mb-1">${sender} • ${timeStr}</p>
                                <p class="text-sm leading-relaxed">${msg.message}</p>
                            </div>
                        </div>
                    `;
                }).join('');

                messagesContainer.innerHTML = html || '<p class="text-sm text-neutral-500 text-center py-8">No messages yet. Our support team will respond here shortly.</p>';
                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            } catch (error) {
                console.error('Unable to fetch latest messages', error);
            }
        }

        setInterval(fetchMessages, 5000);
    })();
</script>
@endpush

@endsection
