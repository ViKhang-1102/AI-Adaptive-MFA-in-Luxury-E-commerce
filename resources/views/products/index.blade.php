@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Products</h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-4">Filters</h3>
                
                <form action="{{ route('products.index') }}" method="GET" class="space-y-4">
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-bold mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border rounded">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-bold mb-2">Sort By</label>
                        <select name="sort" class="w-full px-3 py-2 border rounded">
                            <option value="">Newest</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                        Apply Filters
                    </button>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <span class="font-bold">₫{{ number_format($product->getDiscountedPrice(), 0) }}</span>
                                @if($product->hasDiscount())
                                <span class="text-gray-400 line-through text-sm">₫{{ number_format($product->price, 0) }}</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="block text-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                            View Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
