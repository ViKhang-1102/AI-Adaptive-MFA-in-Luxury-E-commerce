@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-blue-600">{{ $totalCustomers }}</div>
            <p class="text-gray-600">Customers</p>
        </div>
        <div class="bg-green-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-green-600">{{ $totalSellers }}</div>
            <p class="text-gray-600">Sellers</p>
        </div>
        <div class="bg-cyan-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-cyan-600">{{ $totalProducts }}</div>
            <p class="text-gray-600">Products</p>
        </div>
        <div class="bg-indigo-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-indigo-600">{{ $totalCategories }}</div>
            <p class="text-gray-600">Categories</p>
        </div>
        <div class="bg-purple-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-purple-600">{{ $totalOrders }}</div>
            <p class="text-gray-600">Total Orders</p>
        </div>
        <div class="bg-orange-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-orange-600">${{ number_format($totalRevenue ?? 0, 2) }}</div>
            <p class="text-gray-600">Revenue</p>
        </div>
        <div class="bg-red-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-red-600">{{ $todayOrders }}</div>
            <p class="text-gray-600">Today's Orders</p>
        </div>
    </div>

    <!-- Top & Bottom Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        <!-- Top Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4">🔥 Top 3 Selling Products (30 days)</h3>
            @if($topProducts->count() > 0)
                <ul class="space-y-2">
                    @foreach($topProducts as $item)
                    <li class="flex justify-between pb-2 border-b">
                        <span class="text-gray-700">{{ $item->product->name ?? 'N/A' }}</span>
                        <span class="font-bold text-green-600">{{ $item->total_qty }} sold</span>
                    </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">No sales in last 30 days</p>
            @endif
        </div>

        <!-- Bottom Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4">📉 Bottom 3 Selling Products (30 days)</h3>
            @if($bottomProducts->count() > 0)
                <ul class="space-y-2">
                    @foreach($bottomProducts as $item)
                    <li class="flex justify-between pb-2 border-b">
                        <span class="text-gray-700">{{ $item->product->name ?? 'N/A' }}</span>
                        <span class="font-bold text-red-600">{{ $item->total_qty }} sold</span>
                    </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">No sales in last 30 days</p>
            @endif
        </div>
    </div>

    <!-- Admin Menu -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-bold text-lg mb-4">Admin Panel</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('admin.customers.index') }}" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-users text-blue-600 mr-2"></i> Manage Customers
            </a>
            <a href="{{ route('admin.sellers.index') }}" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-store text-blue-600 mr-2"></i> Manage Sellers
            </a>
            <a href="{{ route('admin.categories.index') }}" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-folder text-blue-600 mr-2"></i> Categories
            </a>
            <a href="{{ route('admin.banners.index') }}" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-image text-blue-600 mr-2"></i> Banners
            </a>
            <a href="{{ route('admin.fees.index') }}" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-percent text-blue-600 mr-2"></i> Fees & Settings
            </a>
            <a href="{{ route('admin.wallet') }}" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-wallet text-blue-600 mr-2"></i> Platform Wallet
            </a>
            <a href="{{ route('admin.orders.index') }}" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-receipt text-blue-600 mr-2"></i> All Orders
            </a>
            <a href="{{ route('profile.show') }}" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-user text-blue-600 mr-2"></i> My Profile
            </a>
        </div>
    </div>
</div>
@endsection
