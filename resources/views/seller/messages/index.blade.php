@extends('layouts.app')

@section('title', 'Messages Inbox')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('seller.dashboard') }}" class="w-10 h-10 bg-white border border-neutral-100 rounded-full flex items-center justify-center text-neutral-400 hover:text-primary hover:border-gold transition-all shadow-sm group">
                    <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <h1 class="text-3xl font-serif font-bold text-primary">Message Center</h1>
            </div>
            <p class="text-neutral-500">Manage your inquiries and build relationships with your customers.</p>
        </div>
        
        <div class="flex items-center gap-4 bg-white p-2 rounded-2xl border border-neutral-100 shadow-sm">
            <div class="px-4 py-2 bg-gold/10 rounded-xl border border-gold/20 flex items-center gap-2">
                <i data-lucide="messages-square" class="w-4 h-4 text-gold-dark"></i>
                <span class="text-gold-dark font-bold text-sm">{{ count($conversations) }} Total Conversations</span>
            </div>
            @php
                $totalUnread = collect($conversations)->sum('unread_count');
            @endphp
            @if($totalUnread > 0)
            <div class="px-4 py-2 bg-red-50 rounded-xl border border-red-100 flex items-center gap-2 animate-pulse">
                <i data-lucide="bell" class="w-4 h-4 text-red-600"></i>
                <span class="text-red-600 font-bold text-sm">{{ $totalUnread }} Unread</span>
            </div>
            @endif
        </div>
    </div>

    @if(count($conversations) === 0)
    <div class="bg-white p-20 rounded-3xl shadow-soft border border-neutral-100 text-center max-w-2xl mx-auto">
        <div class="w-24 h-24 bg-neutral-50 rounded-full flex items-center justify-center mx-auto mb-8 relative">
            <i data-lucide="message-circle" class="w-12 h-12 text-neutral-200"></i>
            <div class="absolute -top-1 -right-1 w-6 h-6 bg-gold rounded-full border-4 border-white"></div>
        </div>
        <h2 class="text-2xl font-serif font-bold text-primary mb-4">Your inbox is quiet</h2>
        <p class="text-neutral-500 mb-8 leading-relaxed">When customers have questions about your luxury items, their messages will appear here. Exceptional service leads to exceptional sales.</p>
        <a href="{{ route('seller.products.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white font-bold rounded-xl hover:bg-primary-light transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
            View My Products <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($conversations as $conv)
        <a href="{{ route('seller.messages.conversation', ['product' => $conv['product']->id, 'other' => $conv['other']->id]) }}"
           class="group bg-white rounded-3xl shadow-soft border border-neutral-100 hover:border-gold/30 hover:shadow-hover transition-all duration-300 flex flex-col h-full relative overflow-hidden">
            
            <!-- Unread Ribbon -->
            @if($conv['unread_count'] > 0)
            <div class="absolute top-0 right-0 w-16 h-16 overflow-hidden pointer-events-none z-10">
                <div class="absolute top-[-10px] right-[-30px] w-20 h-8 bg-red-600 text-white text-[10px] font-bold flex items-center justify-center rotate-45 shadow-sm uppercase tracking-tighter">New</div>
            </div>
            @endif

            <!-- Product Header -->
            <div class="p-6 pb-4 border-b border-neutral-50 bg-neutral-50/30">
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        @if($conv['product']->images->first())
                            <img src="{{ asset('storage/' . $conv['product']->images->first()->image) }}" 
                                 class="w-14 h-14 rounded-2xl object-cover border border-white shadow-sm group-hover:scale-110 transition-transform duration-500" 
                                 alt="{{ $conv['product']->name }}">
                        @else
                            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center border border-neutral-100 shadow-sm">
                                <i data-lucide="package" class="w-6 h-6 text-neutral-300"></i>
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-primary truncate leading-tight mb-1 group-hover:text-gold transition-colors">{{ $conv['product']->name }}</h3>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-neutral-400">Ref: #{{ $conv['product']->id }}</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-6 flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary-light/5 text-primary rounded-full flex items-center justify-center border border-primary/5">
                            <span class="text-xs font-serif font-bold">{{ substr($conv['other']->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-primary leading-none">{{ $conv['other']->name }}</p>
                            <p class="text-[10px] text-neutral-400 mt-1">Customer</p>
                        </div>
                    </div>
                    @if($conv['unread_count'] > 0)
                        <div class="px-2 py-1 bg-red-600 text-white text-[10px] font-bold rounded-lg shadow-sm">
                            {{ $conv['unread_count'] }} new
                        </div>
                    @endif
                </div>

                <div class="relative bg-neutral-50 rounded-2xl p-4 flex-1 mb-4">
                    <i data-lucide="quote" class="absolute top-2 right-3 w-4 h-4 text-neutral-100"></i>
                    <p class="text-sm text-neutral-600 line-clamp-2 leading-relaxed italic">
                        "{{ $conv['last_message'] }}"
                    </p>
                </div>

                <div class="mt-auto pt-4 flex items-center justify-between border-t border-neutral-50">
                    <span class="text-[10px] font-medium text-neutral-400 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3"></i> 
                        {{ \Carbon\Carbon::parse($conv['last_message_time'])->diffForHumans() }}
                    </span>
                    <div class="text-gold font-bold text-xs flex items-center gap-1 group-hover:gap-2 transition-all">
                        Open Chat <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
