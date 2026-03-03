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
                <span class="text-red-600">${{ number_format($product->getDiscountedPrice() / env('VND_PER_USD', 23000), 2) }}</span>
                <span class="text-gray-400 line-through text-lg">${{ number_format($product->price / env('VND_PER_USD', 23000), 2) }}</span>
                <span class="text-red-600 bg-red-100 px-2 py-1 rounded text-sm ml-2">Save {{ $product->discount_percent }}%</span>
                @else
                <span>${{ number_format($product->price / env('VND_PER_USD', 23000), 2) }}</span>
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

    <!-- Reviews & Messages Section -->
    <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chat Section -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-4">Message Seller</h3>
                @auth
                    @if(auth()->user()->id !== $product->seller_id)
                    <div id="messages-container" class="h-96 bg-gray-100 rounded-lg p-4 mb-4 overflow-y-auto flex flex-col">
                        <!-- Messages will load here -->
                    </div>
                    <form id="message-form" class="space-y-3">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $product->seller_id }}">
                        <textarea name="message" placeholder="Type your message..." 
                            class="w-full px-3 py-2 border rounded resize-none h-20"
                            maxlength="1000" required></textarea>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                            Send Message
                        </button>
                    </form>
                    @else
                    <p class="text-gray-600 text-sm">You are the seller of this product</p>
                    @endif
                @else
                <p class="text-gray-600 text-sm"><a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a> to message the seller</p>
                @endauth
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>

                <!-- Review Form -->
                @auth
                    @if(auth()->user()->isCustomer())
                    @if($canReview)
                    <div class="mb-8 p-4 bg-blue-50 rounded-lg">
                        <h3 class="font-bold mb-4">Leave a Review</h3>
                        <form action="{{ route('reviews.store', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block font-semibold mb-2">Rating</label>
                                <div class="flex gap-2">
                                    @for($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $i }}" class="hidden rating-input" data-rating="{{ $i }}">
                                        <i class="fas fa-star text-2xl text-gray-300 rating-star" data-rating="{{ $i }}"></i>
                                    </label>
                                    @endfor
                                </div>
                                @error('rating')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                            </div>

                            <div>
                                <label class="block font-semibold mb-2">Comment (Optional)</label>
                                <textarea name="comment" placeholder="Share your experience with this product..."
                                    class="w-full px-3 py-2 border rounded h-24 resize-none" maxlength="1000"></textarea>
                                @error('comment')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                            </div>

                            <div>
                                <label class="block font-semibold mb-2">Upload Images (Optional)</label>
                                <input type="file" name="images[]" multiple accept="image/*" 
                                    class="w-full px-3 py-2 border rounded">
                                <p class="text-sm text-gray-600 mt-1">Max 2MB per image, up to 5 images</p>
                                @error('images.*')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                            </div>

                            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
                                Submit Review
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="mb-8 p-4 bg-yellow-50 rounded-lg">
                        <p class="text-yellow-800">Reviews are available only after delivery and one review per order.</p>
                    </div>
                    @endif
                    @endif
                @endauth

                <!-- Reviews List -->
                <div id="reviews-list" class="space-y-4">
                    @forelse($reviews->take(5) as $review)
                    <div class="border-b pb-4 last:border-b-0">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <strong class="block">{{ $review->customer->name }}</strong>
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
                        @if($review->comment)
                        <p class="text-gray-700 mb-2">{{ $review->comment }}</p>
                        @endif
                        
                        @if($review->images->count() > 0)
                        <div class="flex gap-2 mb-2 flex-wrap">
                            @foreach($review->images as $img)
                            <img src="{{ asset('storage/' . $img->image) }}" class="h-20 w-20 object-cover rounded cursor-pointer hover:opacity-80" data-image-url="{{ asset('storage/' . $img->image) }}" alt="Review image">
                            @endforeach
                        </div>
                        @endif

                        @auth
                            @if(auth()->id() === $review->customer_id)
                            <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete this review?')">
                                    Delete Review
                                </button>
                            </form>
                            @endif
                        @endauth
                    </div>
                    @empty
                    <p class="text-gray-600 text-center">No reviews yet. Be the first to review!</p>
                    @endforelse
                </div>

                <!-- Load More Reviews -->
                @if($reviews->count() > 5)
                <button id="load-more-reviews" class="w-full mt-4 py-2 border-2 border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Load More Reviews
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Related Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
            <a href="{{ route('products.show', $related) }}" class="block bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden text-decoration-none group">
                @if($related->images->first())
                <img src="{{ asset('storage/' . $related->images->first()->image) }}" class="w-full h-48 object-cover group-hover:opacity-90 transition" alt="{{ $related->name }}">
                @else
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                @endif
                <div class="p-4">
                    <h3 class="font-bold truncate group-hover:text-blue-600">{{ $related->name }}</h3>
                    <p class="text-gray-600 text-sm mb-2">{{ $related->seller->name }}</p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold">${{ number_format($related->getDiscountedPrice() / env('VND_PER_USD', 23000), 2) }}</span>
                        <span class="text-blue-600 group-hover:text-blue-800">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Image Modal -->
    <div id="image-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
        <div class="relative max-w-2xl w-full mx-4">
            <img id="modal-image" src="" class="w-full h-auto">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 bg-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-200">
                <i class="fas fa-times"></i>
            </button>
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

    // Star rating selector
    const ratingInputs = document.querySelectorAll('.rating-input');
    const ratingStars = document.querySelectorAll('.rating-star');

    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            ratingStars.forEach(star => {
                if (parseInt(star.dataset.rating) <= parseInt(rating)) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            });
        });
    });

    // Messages
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const productId = parseInt('{{ $product->id ?? 0 }}');
    const userId = parseInt('{{ auth()->check() ? auth()->id() : 0 }}');
    const sellerId = parseInt('{{ $product->seller_id ?? 0 }}');

    if (messagesContainer && userId && userId != sellerId) {
        // Load messages
        async function loadMessages() {
            try {
                const response = await fetch(`/products/${productId}/messages?user_id=${sellerId}`);
                const messages = await response.json();
                
                messagesContainer.innerHTML = '';
                messages.forEach(msg => {
                    const isOwn = msg.sender_id === userId;
                    const div = document.createElement('div');
                    div.className = `mb-3 ${isOwn ? 'text-right' : 'text-left'}`;
                    div.innerHTML = `
                        <div class="${isOwn ? 'bg-blue-500 text-white' : 'bg-gray-300 text-black'} rounded-lg px-3 py-2 inline-block max-w-xs">
                            ${msg.message}
                        </div>
                        <div class="text-xs text-gray-600 mt-1">
                            ${new Date(msg.created_at).toLocaleTimeString()}
                        </div>
                    `;
                    messagesContainer.appendChild(div);
                });
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // Load initial messages
        loadMessages();

        // Auto-refresh messages every 2 seconds
        setInterval(loadMessages, 2000);

        // Send message
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(messageForm);
            const submitBtn = messageForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            try {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
                
                const response = await fetch(`/products/${productId}/messages`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData
                });

                if (response.ok) {
                    messageForm.reset();
                    submitBtn.textContent = 'Message Sent!';
                    setTimeout(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    }, 2000);
                    loadMessages();
                } else {
                    const error = await response.json();
                    alert('Error: ' + (error.error || 'Failed to send message'));
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Error sending message. Please try again.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    }

    // Image modal
    function openImageModal(src) {
        document.getElementById('image-modal').classList.remove('hidden');
        document.getElementById('modal-image').src = src;
    }

    function closeImageModal() {
        document.getElementById('image-modal').classList.add('hidden');
    }

    // Review image click handlers
    document.querySelectorAll('img[data-image-url]').forEach(img => {
        img.addEventListener('click', function() {
            openImageModal(this.dataset.imageUrl);
        });
    });

    // Load more reviews
    const loadMoreBtn = document.getElementById('load-more-reviews');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // This would require additional backend route
            // For now, just show all reviews by scrolling
            window.location.hash = '#reviews-list';
        });
    }
</script>
@endsection
