@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Wishlist</h1>

    @if($wishlists->isEmpty())
    <div class="bg-white p-8 rounded-md-lg shadow-sm text-center">
        <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
        <p class="text-neutral-600 text-lg mb-4">Your wishlist is empty</p>
        <a href="{{ route('products.index') }}" class="bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 px-6 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5">
            Start Shopping
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($wishlists as $wishlist)
        <div class="bg-white rounded-md-lg shadow-sm hover:shadow-sm-lg transition overflow-hidden">
            @if($wishlist->product->images->first())
            <img src="{{ asset('storage/' . $wishlist->product->images->first()->image) }}" class="w-full h-48 object-cover" alt="{{ $wishlist->product->name }}">
            @else
            <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
            @endif
            <div class="p-4">
                <h3 class="font-bold truncate">{{ $wishlist->product->name }}</h3>
                <p class="text-neutral-600 text-sm mb-2">{{ $wishlist->product->seller->name }}</p>
                <div class="flex justify-between items-center mb-3">
                    <span class="font-bold">${{ number_format($wishlist->product->getDiscountedPrice(), 2) }}</span>
                </div>
                <div class="flex space-x-2">
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $wishlist->product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="w-full bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 py-2 rounded-md text-sm hover:bg-primary-light hover:-translate-y-0.5">
                            Add to Cart
                        </button>
                    </form>
                    <form action="{{ route('wishlist.remove', $wishlist->product->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">
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
