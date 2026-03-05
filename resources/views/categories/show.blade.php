@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">{{ $category->name }}</h1>

    @if($products->isEmpty())
    <div class="bg-white p-8 rounded-md-lg shadow-sm text-center">
        <p class="text-neutral-600">No products in this category yet.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach($products as $product)
        <a href="{{ route('products.show', $product) }}" class="block bg-white rounded-md-lg shadow-sm hover:shadow-sm-lg transition overflow-hidden text-decoration-none group">
            @if($product->images->first())
            <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-48 object-cover group-hover:opacity-90 transition" alt="{{ $product->name }}">
            @else
            <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
            @endif
            <div class="p-4">
                <h3 class="font-bold truncate group-hover:text-primary">{{ $product->name }}</h3>
                <p class="text-neutral-600 text-sm mb-2">{{ $product->seller->name }}</p>
                <div class="flex justify-between items-center">
                    <span class="font-bold">${{ number_format($product->getDiscountedPrice(), 2) }}</span>
                    <span class="text-primary group-hover:text-blue-800">
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{ $products->links() }}
    @endif

    @if($relatedCategories->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Related Categories</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @foreach($relatedCategories as $related)
            <a href="{{ route('categories.show', $related) }}" 
                class="bg-white p-6 rounded-md-lg shadow-sm hover:shadow-sm-lg transition text-center">
                <i class="fas fa-folder text-4xl text-primary mb-4"></i>
                <h3 class="font-bold">{{ $related->name }}</h3>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
