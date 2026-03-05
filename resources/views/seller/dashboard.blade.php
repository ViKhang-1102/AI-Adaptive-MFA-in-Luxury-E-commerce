@extends('layouts.app')

@section('title', 'Seller Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
        <div>
            <h1 class="text-3xl font-serif font-bold text-primary mb-2">Seller Dashboard</h1>
            <p class="text-neutral-500">Welcome back, {{ auth()->user()->name }}. Here is what's happening today.</p>
        </div>
        <div class="flex items-center gap-4">
            <form method="GET" action="{{ route('seller.dashboard') }}" class="flex gap-2 items-center bg-white p-2 rounded-xl border border-neutral-100 shadow-sm">
                <select name="month" class="border-none rounded-lg px-3 py-2 bg-neutral-50 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (isset($month) && $month == $m) ? 'selected' : '' }}>Month {{ sprintf('%02d', $m) }}</option>
                    @endfor
                </select>
                <select name="year" class="border-none rounded-lg px-3 py-2 bg-neutral-50 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">All Years</option>
                    @for($y = date('Y') - 5; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ (isset($year) && $year == $y) ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-light transition-colors text-sm font-medium">Filter</button>
            </form>

            <a href="{{ route('home') }}" class="inline-flex items-center px-5 py-2.5 bg-white border border-neutral-200 text-neutral-700 rounded-full hover:border-gold hover:text-primary transition-all shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Home
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- Products -->
        <div class="bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 flex items-center justify-between group hover:border-gold/50 transition-colors">
            <div>
                <p class="text-sm font-medium text-neutral-500 mb-1">Total Products</p>
                <div class="text-3xl font-bold text-primary group-hover:text-gold transition-colors">{{ $totalProducts }}</div>
            </div>
            <div class="w-12 h-12 bg-primary-light/5 text-primary rounded-xl flex items-center justify-center group-hover:bg-gold/10 group-hover:text-gold transition-colors">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Orders -->
        <div class="bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 flex items-center justify-between group hover:border-gold/50 transition-colors">
            <div>
                <p class="text-sm font-medium text-neutral-500 mb-1">Total Orders</p>
                <div class="text-3xl font-bold text-primary group-hover:text-gold transition-colors">{{ $totalOrders }}</div>
            </div>
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center group-hover:bg-green-100 transition-colors">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Revenue -->
        <div class="bg-primary p-6 rounded-2xl shadow-hover flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-gold rounded-full opacity-10 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <p class="text-sm font-medium text-neutral-300 mb-1">Total Revenue</p>
                <div class="text-3xl font-bold text-gold">${{ number_format($totalRevenue, 2) }}</div>
            </div>
            <div class="relative z-10 w-12 h-12 bg-white/10 text-gold rounded-xl flex items-center justify-center border border-white/10">
                <i data-lucide="dollar-sign" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 flex items-center justify-between group hover:border-gold/50 transition-colors">
            <div>
                <p class="text-sm font-medium text-neutral-500 mb-1">Pending Orders</p>
                <div class="text-3xl font-bold text-orange-500">{{ $pendingOrders }}</div>
            </div>
            <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="text-xl font-serif font-bold text-primary mb-6">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <a href="{{ route('seller.products.create') }}" class="bg-white p-6 rounded-2xl border border-neutral-100 shadow-soft hover:shadow-hover hover:-translate-y-1 transition-all group flex flex-col items-center text-center">
            <div class="w-14 h-14 bg-primary-light/5 text-primary rounded-full flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
                <i data-lucide="plus" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-primary mb-1">Add Product</h3>
            <p class="text-xs text-neutral-500">List a new luxury item</p>
        </a>
        
        <a href="{{ route('seller.orders.index') }}" class="bg-white p-6 rounded-2xl border border-neutral-100 shadow-soft hover:shadow-hover hover:-translate-y-1 transition-all group flex flex-col items-center text-center">
            <div class="w-14 h-14 bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-green-600 group-hover:text-white transition-colors">
                <i data-lucide="clipboard-list" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-primary mb-1">Manage Orders</h3>
            <p class="text-xs text-neutral-500">View and fulfill orders</p>
        </a>
        
        <a href="{{ route('seller.wallet') }}" class="bg-white p-6 rounded-2xl border border-neutral-100 shadow-soft hover:shadow-hover hover:-translate-y-1 transition-all group flex flex-col items-center text-center">
            <div class="w-14 h-14 bg-gold/10 text-gold-dark rounded-full flex items-center justify-center mb-4 group-hover:bg-gold-dark group-hover:text-white transition-colors">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-primary mb-1">My Wallet</h3>
            <p class="text-xs text-neutral-500">Withdraw funds</p>
        </a>
        
        <a href="{{ route('seller.messages.index') }}" class="bg-white p-6 rounded-2xl border border-neutral-100 shadow-soft hover:shadow-hover hover:-translate-y-1 transition-all group flex flex-col items-center text-center relative">
            <div class="w-14 h-14 bg-primary-light/5 text-primary rounded-full flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
                <i data-lucide="mail" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-primary mb-1">Messages</h3>
            <p class="text-xs text-neutral-500">Customer inquiries</p>
            @if(isset($unreadMessages) && $unreadMessages > 0)
                <span class="absolute top-4 right-4 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">{{ $unreadMessages }} new</span>
            @endif
        </a>
    </div>

    <!-- Menu Links -->
    <h2 class="text-xl font-serif font-bold text-primary mb-6">Seller Menu</h2>
    <div class="bg-white p-8 rounded-2xl shadow-soft border border-neutral-100">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('seller.products.index') }}" class="flex items-center p-4 border border-neutral-100 rounded-xl hover:bg-neutral-50 hover:border-gold/50 transition-colors group">
                <i data-lucide="box" class="text-neutral-400 group-hover:text-gold w-5 h-5 mr-4 transition-colors"></i> 
                <span class="text-sm font-medium text-neutral-700 group-hover:text-primary transition-colors">Manage Products</span>
            </a>
            <a href="{{ route('orders.index') }}" class="flex items-center p-4 border border-neutral-100 rounded-xl bg-primary/5 hover:bg-primary/10 hover:border-gold/50 transition-colors group">
                <i data-lucide="shopping-bag" class="text-primary group-hover:text-gold w-5 h-5 mr-4 transition-colors"></i> 
                <span class="text-sm font-bold text-primary transition-colors">My Purchases</span>
            </a>
            <a href="{{ route('seller.categories.index') }}" class="flex items-center p-4 border border-neutral-100 rounded-xl hover:bg-neutral-50 hover:border-gold/50 transition-colors group">
                <i data-lucide="folder" class="text-neutral-400 group-hover:text-gold w-5 h-5 mr-4 transition-colors"></i> 
                <span class="text-sm font-medium text-neutral-700 group-hover:text-primary transition-colors">View Categories</span>
            </a>
            <a href="{{ route('seller.orders.index') }}" class="flex items-center p-4 border border-neutral-100 rounded-xl hover:bg-neutral-50 hover:border-gold/50 transition-colors group">
                <i data-lucide="receipt" class="text-neutral-400 group-hover:text-gold w-5 h-5 mr-4 transition-colors"></i> 
                <span class="text-sm font-medium text-neutral-700 group-hover:text-primary transition-colors">Orders History</span>
            </a>
            <a href="{{ route('seller.wallet') }}" class="flex items-center p-4 border border-neutral-100 rounded-xl hover:bg-neutral-50 hover:border-gold/50 transition-colors group">
                <i data-lucide="credit-card" class="text-neutral-400 group-hover:text-gold w-5 h-5 mr-4 transition-colors"></i> 
                <span class="text-sm font-medium text-neutral-700 group-hover:text-primary transition-colors">Wallet & Withdrawals</span>
            </a>
            <a href="{{ route('profile.show') }}" class="flex items-center p-4 border border-neutral-100 rounded-xl hover:bg-neutral-50 hover:border-gold/50 transition-colors group">
                <i data-lucide="user" class="text-neutral-400 group-hover:text-gold w-5 h-5 mr-4 transition-colors"></i> 
                <span class="text-sm font-medium text-neutral-700 group-hover:text-primary transition-colors">My Profile</span>
            </a>
        </div>
    </div>
</div>
@endsection
