@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Wishlist</h1>

    @if($wishlists->isEmpty())
    <div class="bg-white p-8 rounded-lg shadow text-center">
        <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
        <p class="text-gray-600 text-lg mb-4">Your wishlist is empty</p>
        <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Start Shopping
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($wishlists as $wishlist)
        <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
            @if($wishlist->product->images->first())
            <img src="{{ asset('storage/' . $wishlist->product->images->first()->image) }}" class="w-full h-48 object-cover" alt="{{ $wishlist->product->name }}">
            @else
            <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
            @endif
            <div class="p-4">
                <h3 class="font-bold truncate">{{ $wishlist->product->name }}</h3>
                <p class="text-gray-600 text-sm mb-2">{{ $wishlist->product->seller->name }}</p>
                <div class="flex justify-between items-center mb-3">
                    <span class="font-bold">${{ number_format($wishlist->product->getDiscountedPrice(), 2) }}</span>
                </div>
                <div class="flex space-x-2">
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $wishlist->product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">
                            Add to Cart
                        </button>
                    </form>
                    <form action="{{ route('wishlist.remove', $wishlist->product->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{ $wishlists->links() }}
    @endif
</div>
@endsection
