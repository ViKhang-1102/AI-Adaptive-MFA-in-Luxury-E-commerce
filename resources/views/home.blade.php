@extends('layouts.app')

@section('title', 'Home - E-Commerce Platform')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <!-- Banners Carousel -->
    @if($banners->count() > 0)
    <div class="mt-4 mb-8">
        <div class="relative bg-gray-200 h-96 rounded-lg overflow-hidden">
            @foreach($banners as $banner)
            <div class="absolute inset-0 opacity-0 hover:opacity-100 transition-opacity">
                <img src="{{ asset('storage/' . $banner->image) }}" class="w-full h-full object-cover" alt="{{ $banner->title }}">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                    <h2 class="text-white text-4xl font-bold">{{ $banner->title }}</h2>
                </div>
            </div>
            @endforeach
            <img src="https://via.placeholder.com/1200x400?text=Welcome+to+E-Shop" class="w-full h-full object-cover" alt="Banner">
        </div>
    </div>
    @endif

    <!-- Categories Section -->
    @if($categories->count() > 0)
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Categories</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($categories as $category)
            <a href="{{ route('categories.show', $category) }}" 
                class="text-center p-4 bg-white rounded-lg shadow hover:shadow-lg transition">
                <i class="fas fa-folder text-3xl text-blue-600 mb-2"></i>
                <p class="font-semibold text-sm">{{ $category->name }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Top Selling Products -->
    @if($topProducts->count() > 0)
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">🔥 Top Selling</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($topProducts as $product)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                @if($product->images->first())
                <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-48 object-cover" alt="{{ $product->name }}">
                @else
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                @endif
                <div class="p-4">
                    <h3 class="font-bold truncate">{{ $product->name }}</h3>
                    <p class="text-gray-600 text-sm mb-2">{{ $product->seller->name }}</p>
                    <div class="flex justify-between items-center">
                        <div>
                            @if($product->hasDiscount())
                            <span class="text-red-600 font-bold">${{ number_format($product->getDiscountedPrice(), 2) }}</span>
                            <span class="text-gray-400 line-through text-sm">${{ number_format($product->price, 2) }}</span>
                            @else
                            <span class="font-bold">${{ number_format($product->price, 2) }}</span>
                            @endif
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Discounted Products -->
    @if($discountedProducts->count() > 0)
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">💰 Special Discounts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($discountedProducts as $product)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden relative">
                @if($product->discount_percent)
                <div class="absolute top-2 right-2 bg-red-600 text-white px-2 py-1 rounded text-sm font-bold">
                    -{{ $product->discount_percent }}%
                </div>
                @endif
                @if($product->images->first())
                <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-48 object-cover" alt="{{ $product->name }}">
                @else
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                @endif
                <div class="p-4">
                    <h3 class="font-bold truncate">{{ $product->name }}</h3>
                    <p class="text-gray-600 text-sm mb-2">{{ $product->seller->name }}</p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-red-600 font-bold">${{ number_format($product->getDiscountedPrice(), 2) }}</span>
                            <span class="text-gray-400 line-through text-sm">${{ number_format($product->price, 2) }}</span>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- All Products -->
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">All Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                @if($product->images->first())
                <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-48 object-cover" alt="{{ $product->name }}">
                @else
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                @endif
                <div class="p-4">
                    <h3 class="font-bold truncate">{{ $product->name }}</h3>
                    <p class="text-gray-600 text-sm mb-2">{{ $product->seller->name }}</p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold">${{ number_format($product->getDiscountedPrice(), 2) }}</span>
                        <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </section>
</div>
@endsection
