@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <h1 class="text-3xl font-bold">Admin Dashboard</h1>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-2 items-center bg-white p-2 rounded-md shadow-sm">
            <select name="month" class="border rounded-md px-3 py-2 bg-white text-sm outline-none focus:border-primary">
                <option value="">All Months</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ (isset($month) && $month == $m) ? 'selected' : '' }}>Month {{ sprintf('%02d', $m) }}</option>
                @endfor
            </select>
            <select name="year" class="border rounded-md px-3 py-2 bg-white text-sm outline-none focus:border-primary">
                <option value="">All Years</option>
                @for($y = date('Y') - 5; $y <= date('Y'); $y++)
                    <option value="{{ $y }}" {{ (isset($year) && $year == $y) ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-light transition-colors text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-primary">{{ $totalCustomers }}</div>
            <p class="text-neutral-600">Customers</p>
        </div>
        <div class="bg-green-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-green-600">{{ $totalSellers }}</div>
            <p class="text-neutral-600">Sellers</p>
        </div>
        <div class="bg-cyan-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-cyan-600">{{ $totalProducts }}</div>
            <p class="text-neutral-600">Products</p>
        </div>
        <div class="bg-indigo-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-indigo-600">{{ $totalCategories }}</div>
            <p class="text-neutral-600">Categories</p>
        </div>
        <div class="bg-purple-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-purple-600">{{ $totalOrders }}</div>
            <p class="text-neutral-600">Total Orders</p>
        </div>
        <div class="bg-red-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-red-600">{{ $todayOrders }}</div>
            <p class="text-neutral-600">Today's Orders</p>
        </div>
        <div class="bg-orange-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-orange-600">${{ number_format(($totalRevenue ?? 0), 2) }}</div>
            <p class="text-neutral-600">Revenue</p>
        </div>

        <div class="bg-amber-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-amber-600">${{ number_format($totalPlatformBalance, 2) }}</div>
            <p class="text-neutral-600">Platform Fee Balance</p>
        </div>
    </div>


    <!-- Top & Bottom Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        <!-- Top Products -->
        <div class="bg-white p-6 rounded-md-lg shadow-sm">
            <h3 class="font-bold text-lg mb-4">🔥 Top 3 Selling Products (30 days)</h3>
            @if($topProducts->count() > 0)
                <ul class="space-y-2">
                    @foreach($topProducts as $item)
                    <li class="flex justify-between pb-2 border-b">
                        <span class="text-neutral-700">{{ $item->product->name ?? 'N/A' }}</span>
                        <span class="font-bold text-green-600">{{ $item->total_qty }} sold</span>
                    </li>
                    @endforeach
                </ul>
            @else
                <p class="text-neutral-500">No sales in last 30 days</p>
            @endif
        </div>

        <!-- Bottom Products -->
        <div class="bg-white p-6 rounded-md-lg shadow-sm">
            <h3 class="font-bold text-lg mb-4">📉 Bottom 3 Selling Products (30 days)</h3>
            @if($bottomProducts->count() > 0)
                <ul class="space-y-2">
                    @foreach($bottomProducts as $item)
                    <li class="flex justify-between pb-2 border-b">
                        <span class="text-neutral-700">{{ $item->product->name ?? 'N/A' }}</span>
                        <span class="font-bold text-red-600">{{ $item->total_qty }} sold</span>
                    </li>
                    @endforeach
                </ul>
            @else
                <p class="text-neutral-500">No sales in last 30 days</p>
            @endif
        </div>
    </div>

    <!-- Admin Menu -->
    <div class="bg-white p-6 rounded-md-lg shadow-sm">
        <h3 class="font-bold text-lg mb-4">Admin Panel</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('admin.customers.index') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-users text-primary mr-2"></i> Manage Customers
            </a>
            <a href="{{ route('admin.sellers.index') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-store text-primary mr-2"></i> Manage Sellers
            </a>
            <a href="{{ route('admin.categories.index') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-folder text-primary mr-2"></i> Categories
            </a>
            <a href="{{ route('admin.banners.index') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-image text-primary mr-2"></i> Banners
            </a>
            <a href="{{ route('admin.fees.index') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-percent text-primary mr-2"></i> Fees & Settings
            </a>
            <a href="{{ route('admin.wallet') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-wallet text-primary mr-2"></i> Platform Wallet
            </a>
            <a href="{{ route('admin.orders.index') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-receipt text-primary mr-2"></i> All Orders
            </a>
            <a href="{{ route('admin.orders.pending') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-exclamation-triangle text-primary mr-2"></i> Pending Verifications
            </a>
            <a href="{{ route('profile.show') }}" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-user text-primary mr-2"></i> My Profile
            </a>
            <a href="{{ route('admin.security.index') }}" class="p-4 border border-gold bg-[#0A192F] text-white rounded-md hover:bg-[#112240] transition-colors shadow-soft">
                <i class="fas fa-shield-alt text-gold mr-2"></i> Security Insights 
                <span class="ml-2 bg-gold text-[#0A192F] text-xs font-bold px-2 py-1 rounded-full">AI</span>
            </a>
        </div>
    </div>
</div>
@endsection
