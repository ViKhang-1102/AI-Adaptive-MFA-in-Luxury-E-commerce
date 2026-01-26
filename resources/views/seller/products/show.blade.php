@extends('layouts.app')
@section('title', $product->name)
@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <a href="{{ route('seller.products.index') }}" class="text-blue-600 hover:underline mb-6 inline-block">&larr; Back to Products</a>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
            <!-- Product Images -->
            <div>
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
                <h1 class="text-3xl font-bold mb-4">{{ $product->name }}</h1>
                
                <div class="mb-4">
                    <p class="text-gray-600">Category: <span class="font-semibold">{{ $product->category->name }}</span></p>
                </div>

                <!-- Price Section -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    @if($product->hasDiscount())
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-bold text-red-600">${{ number_format($product->getDiscountedPrice(), 2) }}</span>
                            <span class="text-xl text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                            <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold">-{{ $product->discount_percent }}%</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">
                            Valid from {{ $product->discount_start_date->format('M d, Y') }} to {{ $product->discount_end_date->format('M d, Y') }}
                        </p>
                    @else
                        <div class="text-3xl font-bold">${{ number_format($product->price, 2) }}</div>
                    @endif
                </div>

                <!-- Stock Status -->
                <div class="mb-6">
                    <p class="text-lg">
                        <span class="font-semibold">Stock:</span>
                        <span class="ml-2 {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $product->stock > 0 ? $product->stock . ' units available' : 'Out of stock' }}
                        </span>
                    </p>
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold mb-3">Description</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                </div>

                <!-- Status -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm">
                        <span class="font-semibold">Status:</span>
                        <span class="ml-2 {{ $product->is_active ? 'text-green-600' : 'text-gray-600' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <a href="{{ route('seller.products.edit', $product) }}" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center font-semibold">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                    <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                            <i class="fas fa-trash mr-2"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const thumbnails = document.querySelectorAll('.image-thumbnail');
    const mainImage = document.getElementById('main-image');
    
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
