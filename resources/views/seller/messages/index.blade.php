@extends('layouts.app')

@section('title', 'Messages Inbox')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-4xl font-serif font-bold text-primary">Message Inbox</h1>
            <p class="text-neutral-500 mt-2">Manage your conversations with customers</p>
        </div>
        <div class="bg-gold/10 px-4 py-2 rounded-full border border-gold/20">
            <span class="text-gold-dark font-bold text-sm">{{ count($conversations) }} Conversations</span>
        </div>
    </div>

    @if(count($conversations) === 0)
    <div class="bg-white p-16 rounded-3xl shadow-soft border border-neutral-100 text-center">
        <div class="w-20 h-20 bg-neutral-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="message-square-off" class="w-10 h-10 text-neutral-300"></i>
        </div>
        <h2 class="text-2xl font-serif font-bold text-primary mb-3">No messages yet</h2>
        <p class="text-neutral-500 max-w-md mx-auto">When customers message you about your products, they will appear here.</p>
    </div>
    @else
    <div class="grid gap-4">
        @foreach($conversations as $conv)
        <a href="{{ route('seller.messages.conversation', ['product' => $conv['product']->id, 'other' => $conv['other']->id]) }}"
           class="group block bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 hover:border-gold/30 hover:shadow-hover transition-all relative overflow-hidden">
            
            <div class="flex items-center gap-6">
                <!-- Product Image or Placeholder -->
                <div class="relative shrink-0">
                    @if($conv['product']->images->first())
                        <img src="{{ asset('storage/' . $conv['product']->images->first()->image) }}" 
                             class="w-16 h-16 rounded-xl object-cover border border-neutral-100 shadow-sm group-hover:scale-105 transition-transform duration-500" 
                             alt="{{ $conv['product']->name }}">
                    @else
                        <div class="w-16 h-16 bg-neutral-50 rounded-xl flex items-center justify-center border border-neutral-100">
                            <i data-lucide="package" class="w-8 h-8 text-neutral-300"></i>
                        </div>
                    @endif
                    
                    @if($conv['unread_count'] > 0)
                        <span class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-4 ring-white shadow-sm">
                            {{ $conv['unread_count'] }}
                        </span>
                    @endif
                </div>

                <!-- Conversation Details -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-bold text-primary text-lg truncate group-hover:text-gold transition-colors">
                            {{ $conv['product']->name }}
                        </h3>
                    </div>
                    
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-neutral-400">Customer:</span>
                        <span class="text-xs font-bold text-primary/70">{{ $conv['other']->name }}</span>
                    </div>

                    <p class="text-sm text-neutral-500 truncate pr-8 leading-relaxed italic">
                        "{{ $conv['last_message'] }}"
                    </p>
                </div>

                <!-- Arrow Icon -->
                <div class="shrink-0 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 -translate-x-4 transition-all duration-300">
                    <div class="w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center text-gold">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <!-- New Message Indicator Dot -->
            @if($conv['unread_count'] > 0)
                <div class="absolute top-0 right-0 w-2 h-full bg-red-600"></div>
            @endif
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
