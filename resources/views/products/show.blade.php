@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Product Images Section -->
        <div class="md:col-span-1">
            @if($product->images->count() > 0)
                <!-- Main Image -->
                <div class="bg-gray-100 rounded-lg overflow-hidden mb-4">
                    <img id="main-image" src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-auto object-cover rounded-lg" alt="{{ $product->name }}">
                </div>
                
                <!-- Thumbnails Grid -->
                @if($product->images->count() > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->images as $key => $image)
                    <button class="image-thumbnail relative overflow-hidden rounded-lg border-2 aspect-square {{ $key === 0 ? 'border-blue-600' : 'border-gray-300' }} hover:border-blue-600 transition" data-index="{{ $key }}" data-src="{{ asset('storage/' . $image->image) }}">
                        <img src="{{ asset('storage/' . $image->image) }}" class="w-full h-full object-cover" alt="Thumbnail {{ $key + 1 }}">
                    </button>
                    @endforeach
                </div>
                @endif
            @else
                <div class="bg-gray-200 rounded-lg flex items-center justify-center aspect-square">
                    <div class="text-center text-gray-500">
                        <i class="fas fa-image text-4xl mb-2"></i>
                        <p>No images available</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Product Details -->
        <div class="md:col-span-2">
            <h1 class="text-3xl font-bold mb-2">{{ $product->name }}</h1>
            
            <div class="mb-4">
                <span class="text-gray-600">By <strong>{{ $product->seller->name }}</strong></span>
            </div>

            <!-- Rating -->
            <div class="mb-4 flex items-center">
                <div class="text-yellow-400">
                    @php
                    $rating = $product->getAverageRating();
                    for($i = 1; $i <= 5; $i++):
                        if($i <= $rating): echo '<i class="fas fa-star"></i>';
                        else: echo '<i class="far fa-star"></i>';
                        endif;
                    endfor;
                    @endphp
                </div>
                <span class="ml-2 text-gray-600">({{ $product->getReviewCount() }} reviews)</span>
            </div>

            <!-- Price -->
            <div class="mb-6 text-2xl font-bold">
                @if($product->hasDiscount())
                <span class="text-red-600">₫{{ number_format($product->getDiscountedPrice(), 0) }}</span>
                <span class="text-gray-400 line-through text-lg">₫{{ number_format($product->price, 0) }}</span>
                <span class="text-red-600 bg-red-100 px-2 py-1 rounded text-sm ml-2">Save {{ $product->discount_percent }}%</span>
                @else
                <span>₫{{ number_format($product->price, 0) }}</span>
                @endif
            </div>

            <!-- Stock -->
            <div class="mb-6">
                @if($product->stock > 0)
                <span class="text-green-600 font-bold">In Stock ({{ $product->stock }} available)</span>
                @else
                <span class="text-red-600 font-bold">Out of Stock</span>
                @endif
            </div>

            <!-- Actions -->
            @auth
            @if(auth()->user()->isCustomer())
            <div class="space-y-3 mb-6">
                <div class="flex gap-3 items-center mb-3">
                    <label for="quantity" class="font-semibold">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-20 px-3 py-2 border rounded">
                </div>

                <form action="{{ route('cart.add') }}" method="POST" class="flex space-x-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" id="cartQuantity" value="1">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </form>

                <form action="{{ route('checkout') }}" method="GET">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" id="buyNowQuantity" value="1">
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                        Buy Now
                    </button>
                </form>

                @if(auth()->user()->wishlist->where('product_id', $product->id)->first())
                <form action="{{ route('wishlist.remove', $product->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
                        <i class="fas fa-heart"></i> Remove from Wishlist
                    </button>
                </form>
                @else
                <form action="{{ route('wishlist.add', $product->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-gray-600 text-white py-2 rounded hover:bg-gray-700">
                        <i class="far fa-heart"></i> Add to Wishlist
                    </button>
                </form>
                @endif
            </div>
            @endif
            @endif

            <!-- Description -->
            <div class="border-t pt-6">
                <h3 class="font-bold text-lg mb-2">Description</h3>
                <p class="text-gray-700">{{ $product->description }}</p>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Related Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                @if($related->images->first())
                <img src="{{ asset('storage/' . $related->images->first()->image) }}" class="w-full h-48 object-cover" alt="{{ $related->name }}">
                @else
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                @endif
                <div class="p-4">
                    <h3 class="font-bold truncate">{{ $related->name }}</h3>
                    <p class="text-gray-600 text-sm mb-2">{{ $related->seller->name }}</p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold">₫{{ number_format($related->getDiscountedPrice(), 0) }}</span>
                        <a href="{{ route('products.show', $related) }}" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Reviews Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>
        <div class="bg-white p-6 rounded-lg shadow">
            @foreach($reviews as $review)
            <div class="border-b pb-4 mb-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <strong>{{ $review->customer->name }}</strong>
                        <div class="text-yellow-400 text-sm">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                    <span class="text-gray-600 text-sm">{{ $review->created_at->format('M d, Y') }}</span>
                </div>
                <p class="text-gray-700">{{ $review->comment }}</p>
            </div>
            @endforeach

            {{ $reviews->links() }}
        </div>
    </div>
</div>

<script>
    const thumbnails = document.querySelectorAll('.image-thumbnail');
    const mainImage = document.getElementById('main-image');
    const quantityInput = document.getElementById('quantity');
    const cartQuantityInput = document.getElementById('cartQuantity');
    const buyNowQuantityInput = document.getElementById('buyNowQuantity');
    
    if (quantityInput) {
        quantityInput.addEventListener('change', function() {
            if (cartQuantityInput) cartQuantityInput.value = this.value;
            if (buyNowQuantityInput) buyNowQuantityInput.value = this.value;
        });
    }
    
    if (thumbnails.length > 0 && mainImage) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function(e) {
                e.preventDefault();
                const newSrc = this.dataset.src;
                mainImage.src = newSrc;
                
                // Update border styling
                thumbnails.forEach(t => t.classList.remove('border-blue-600'));
                thumbnails.forEach(t => t.classList.add('border-gray-300'));
                this.classList.remove('border-gray-300');
                this.classList.add('border-blue-600');
            });
        });
    }
</script>
@endsection
