@extends('layouts.app')

@section('title', 'Conversation with ' . $other->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header Navigation -->
    <div class="mb-8 flex items-center justify-between">
        <a href="{{ route('seller.messages.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-neutral-100 text-neutral-500 font-medium rounded-xl hover:text-primary hover:border-gold hover:shadow-sm transition-all group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
            <span>Back to Inbox</span>
        </a>
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-neutral-400">
            <span>Messages</span>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-primary">{{ $other->name }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar: Product & Customer Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Product Card -->
            <div class="bg-white rounded-3xl shadow-soft border border-neutral-100 overflow-hidden">
                <div class="aspect-square relative">
                    @if($product->images->first())
                        <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-full object-cover" alt="{{ $product->name }}">
                    @else
                        <div class="w-full h-full bg-neutral-50 flex items-center justify-center">
                            <i data-lucide="image" class="w-12 h-12 text-neutral-200"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-4">
                        <span class="text-white font-bold text-sm line-clamp-2 leading-tight">{{ $product->name }}</span>
                    </div>
                </div>
                <div class="p-4 bg-neutral-50/50 flex items-center justify-between">
                    <span class="text-xs font-bold text-primary">${{ number_format($product->price, 2) }}</span>
                    <a href="{{ route('products.show', $product) }}" target="_blank" class="text-[10px] font-bold text-gold hover:underline">View Product</a>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="bg-white p-6 rounded-3xl shadow-soft border border-neutral-100">
                <h3 class="text-xs font-bold uppercase tracking-widest text-neutral-400 mb-4">Customer</h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-gold/10 text-gold-dark rounded-full flex items-center justify-center border border-gold/20">
                        <span class="font-serif font-bold text-lg">{{ substr($other->name, 0, 1) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-primary truncate">{{ $other->name }}</p>
                        <p class="text-[10px] text-neutral-400">Since {{ $other->created_at->format('M Y') }}</p>
                    </div>
                </div>
                <div class="space-y-3 pt-4 border-t border-neutral-50">
                    <div class="flex items-center gap-2 text-xs text-neutral-500">
                        <i data-lucide="mail" class="w-3 h-3"></i>
                        <span class="truncate">{{ $other->email }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="lg:col-span-3 flex flex-col h-[700px] bg-white rounded-3xl shadow-soft border border-neutral-100 overflow-hidden">
            <!-- Chat Header -->
            <div class="px-6 py-4 border-b border-neutral-50 flex items-center justify-between bg-neutral-50/30">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="font-bold text-primary">Live Chat</span>
                </div>
                <div class="flex items-center gap-4 text-xs text-neutral-400">
                    <span id="status-text">Last active: just now</span>
                </div>
            </div>

            <!-- Messages Container -->
            <div id="messages-container" class="flex-1 p-6 overflow-y-auto space-y-4 bg-neutral-50/30">
                <!-- messages loaded by JS -->
                <div class="flex justify-center py-10">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gold"></div>
                </div>
            </div>

            <!-- Message Input -->
            <div class="p-6 bg-white border-t border-neutral-50">
                <form id="message-form" class="relative">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $other->id }}">
                    <textarea name="message" id="message-input" placeholder="Compose your response..." 
                        class="w-full pl-6 pr-32 py-4 bg-neutral-50 border border-neutral-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-gold/10 focus:border-gold/50 outline-none transition-all shadow-sm text-sm resize-none h-16 min-h-[64px] max-h-32"
                        maxlength="1000" required></textarea>
                    
                    <div class="absolute right-3 bottom-3 flex items-center gap-2">
                        <span id="char-count" class="text-[10px] font-bold text-neutral-300 mr-2">0/1000</span>
                        <button type="submit" id="send-btn" class="flex items-center justify-center w-10 h-10 bg-primary text-gold rounded-xl hover:bg-primary-light transition-all shadow-soft disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Scrollbar for messages */
    #messages-container::-webkit-scrollbar {
        width: 6px;
    }
    #messages-container::-webkit-scrollbar-track {
        background: transparent;
    }
    #messages-container::-webkit-scrollbar-thumb {
        background: #f1f1f1;
        border-radius: 10px;
    }
    #messages-container::-webkit-scrollbar-thumb:hover {
        background: #e5e5e5;
    }
    
    .message-bubble {
        max-width: 80%;
        position: relative;
    }
    .message-bubble-own {
        border-bottom-right-radius: 4px !important;
    }
    .message-bubble-other {
        border-bottom-left-radius: 4px !important;
    }
</style>

<script>
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const charCount = document.getElementById('char-count');
    const sendBtn = document.getElementById('send-btn');
    const productId = {{ $product->id }};
    const otherId = {{ $other->id }};
    const userId = {{ auth()->check() ? auth()->id() : 'null' }};

    // Character counter
    messageInput.addEventListener('input', () => {
        const length = messageInput.value.length;
        charCount.textContent = `${length}/1000`;
        charCount.className = length > 900 ? 'text-[10px] font-bold text-red-400 mr-2' : 'text-[10px] font-bold text-neutral-300 mr-2';
    });

    async function loadMessages() {
        try {
            const response = await fetch(`/products/${productId}/messages?user_id=${otherId}`);
            const messages = await response.json();
            
            // Only update if there's a change to avoid flickering
            const currentContent = messagesContainer.innerHTML;
            let newHtml = '';
            
            if (messages.length === 0) {
                newHtml = `
                    <div class="flex flex-col items-center justify-center h-full text-center p-10">
                        <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="message-square" class="w-8 h-8 text-neutral-300"></i>
                        </div>
                        <p class="text-neutral-400 text-sm italic">Start of your luxury conversation</p>
                    </div>
                `;
            } else {
                messages.forEach((msg, index) => {
                    const isOwn = msg.sender_id === userId;
                    const prevMsg = index > 0 ? messages[index-1] : null;
                    const showTime = !prevMsg || (new Date(msg.created_at) - new Date(prevMsg.created_at) > 300000); // 5 mins gap
                    
                    if (showTime) {
                        newHtml += `
                            <div class="flex justify-center my-6">
                                <span class="px-3 py-1 bg-neutral-100 text-neutral-400 text-[10px] font-bold uppercase tracking-widest rounded-full">
                                    ${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                </span>
                            </div>
                        `;
                    }

                    newHtml += `
                        <div class="flex ${isOwn ? 'justify-end' : 'justify-start'} mb-2">
                            <div class="flex flex-col ${isOwn ? 'items-end' : 'items-start'} max-w-[80%]">
                                <div class="message-bubble ${isOwn ? 'message-bubble-own bg-primary text-white rounded-3xl rounded-br-none' : 'message-bubble-other bg-white border border-neutral-100 text-primary rounded-3xl rounded-bl-none'} px-5 py-3 shadow-sm">
                                    <p class="text-sm leading-relaxed">${msg.message}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            // Simple diff check to prevent re-rendering if nothing changed
            if (newHtml !== currentContent) {
                const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop <= messagesContainer.clientHeight + 100;
                messagesContainer.innerHTML = newHtml;
                if (wasAtBottom) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
                // Refresh lucide icons for new content
                if (window.lucide) lucide.createIcons();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    // Initial load
    loadMessages().then(() => {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    });
    
    // Polling
    const pollInterval = setInterval(loadMessages, 3000);

    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const message = messageInput.value.trim();
        if (!message) return;

        sendBtn.disabled = true;
        const formData = new FormData(messageForm);
        
        try {
            const response = await fetch(`/products/${productId}/messages`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            });
            
            if (response.ok) {
                messageForm.reset();
                charCount.textContent = '0/1000';
                await loadMessages();
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            } else {
                const error = await response.json();
                alert('Error: ' + (error.error || 'Failed to send message'));
            }
        } catch (err) {
            console.error('Error sending message', err);
        } finally {
            sendBtn.disabled = false;
        }
    });

    // Cleanup on page leave
    window.addEventListener('beforeunload', () => clearInterval(pollInterval));
</script>
@endsection
