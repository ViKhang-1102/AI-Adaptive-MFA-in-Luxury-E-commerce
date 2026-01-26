@extends('layouts.app')

@section('title', 'Home - E-Commerce Platform')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <!-- Banners Carousel -->
    @if($banners->count() > 0)
    <div class="mt-4 mb-8">
        <div class="relative bg-gray-200 h-96 rounded-lg overflow-hidden group">
            <div id="banner-carousel" class="relative w-full h-full">
                @foreach($banners as $key => $banner)
                <div class="banner-slide absolute inset-0 transition-opacity duration-1000 {{ $key === 0 ? 'opacity-100' : 'opacity-0' }}" data-index="{{ $key }}">
                    <img src="{{ asset('storage/' . $banner->image) }}" class="w-full h-full object-cover" alt="{{ $banner->title }}">
                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-end pb-12">
                        <h2 class="text-white text-4xl font-bold text-center px-4 w-full">{{ $banner->title }}</h2>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Previous Button -->
            <button id="banner-prev" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 text-gray-800 rounded-full p-2 transition opacity-0 group-hover:opacity-100 z-10">
                <i class="fas fa-chevron-left text-xl"></i>
            </button>
            
            <!-- Next Button -->
            <button id="banner-next" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white bg-opacity-80 hover:bg-opacity-100 text-gray-800 rounded-full p-2 transition opacity-0 group-hover:opacity-100 z-10">
                <i class="fas fa-chevron-right text-xl"></i>
            </button>
            
            <!-- Banner navigation dots -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                @foreach($banners as $key => $banner)
                <button class="banner-dot w-3 h-3 rounded-full bg-white transition {{ $key === 0 ? 'opacity-100' : 'opacity-50' }}" data-index="{{ $key }}"></button>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        let currentBanner = 0;
        let autoSlideInterval;
        const banners = document.querySelectorAll('.banner-slide');
        const dots = document.querySelectorAll('.banner-dot');
        const prevBtn = document.getElementById('banner-prev');
        const nextBtn = document.getElementById('banner-next');
        const totalBanners = banners.length;

        function showBanner(index) {
            banners.forEach(el => el.classList.add('opacity-0'));
            banners.forEach(el => el.classList.remove('opacity-100'));
            dots.forEach(el => el.classList.add('opacity-50'));
            dots.forEach(el => el.classList.remove('opacity-100'));
            
            banners[index].classList.remove('opacity-0');
            banners[index].classList.add('opacity-100');
            dots[index].classList.remove('opacity-50');
            dots[index].classList.add('opacity-100');
        }

        function nextBanner() {
            currentBanner = (currentBanner + 1) % totalBanners;
            showBanner(currentBanner);
        }

        function prevBanner() {
            currentBanner = (currentBanner - 1 + totalBanners) % totalBanners;
            showBanner(currentBanner);
        }

        function startAutoSlide() {
            autoSlideInterval = setInterval(nextBanner, 5000);
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        function resetAutoSlide() {
            stopAutoSlide();
            startAutoSlide();
        }

        // Auto slide every 5 seconds
        if (totalBanners > 1) {
            startAutoSlide();
        }

        // Previous button
        prevBtn.addEventListener('click', () => {
            prevBanner();
            resetAutoSlide();
        });

        // Next button
        nextBtn.addEventListener('click', () => {
            nextBanner();
            resetAutoSlide();
        });

        // Click on dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentBanner = index;
                showBanner(currentBanner);
                resetAutoSlide();
            });
        });

        // Stop auto-slide on hover
        document.querySelector('.group')?.addEventListener('mouseenter', stopAutoSlide);
        document.querySelector('.group')?.addEventListener('mouseleave', startAutoSlide);
    </script>
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
