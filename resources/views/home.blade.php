@extends('layouts.app')

@section('title', 'LuxGuard - Authentic Luxury')

@section('content')
<!-- Hero / Banners Carousel -->
@if($banners->count() > 0)
<div class="relative w-full h-[80vh] min-h-[500px] overflow-hidden group mb-16">
    <div id="banner-carousel" class="w-full h-full">
        @foreach($banners as $key => $banner)
        <div class="banner-slide absolute inset-0 transition-opacity duration-1000 ease-in-out {{ $key === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" data-index="{{ $key }}">
            <img src="{{ asset('storage/' . $banner->image) }}" class="w-full h-full object-cover" alt="{{ $banner->title }}">
            <div class="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/20 to-transparent flex items-center justify-center">
                <div class="text-center px-4 max-w-4xl mx-auto translate-y-8">
                    <h2 class="text-white text-5xl md:text-7xl font-serif font-bold tracking-tight mb-6 drop-shadow-lg">{{ $banner->title }}</h2>
                    <p class="text-neutral-200 text-lg md:text-xl font-medium mb-8">Discover meticulously curated authentic luxury goods.</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gold text-primary text-sm font-bold uppercase tracking-widest rounded-none hover:bg-white transition-colors duration-300">
                        Shop Collection <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Previous Button -->
    <button id="banner-prev" class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white border border-white/20 hover:border-white text-white hover:text-primary backdrop-blur-sm rounded-full flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-20">
        <i data-lucide="chevron-left" class="w-6 h-6"></i>
    </button>
    
    <!-- Next Button -->
    <button id="banner-next" class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white border border-white/20 hover:border-white text-white hover:text-primary backdrop-blur-sm rounded-full flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 z-20">
        <i data-lucide="chevron-right" class="w-6 h-6"></i>
    </button>
    
    <!-- Banner navigation dots -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-3 z-20">
        @foreach($banners as $key => $banner)
        <button class="banner-dot w-2.5 h-2.5 rounded-full transition-all duration-300 {{ $key === 0 ? 'bg-gold w-8' : 'bg-white/50 hover:bg-white' }}" data-index="{{ $key }}"></button>
        @endforeach
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
        banners.forEach(el => {
            el.classList.remove('opacity-100', 'z-10');
            el.classList.add('opacity-0', 'z-0');
        });
        dots.forEach(el => {
            el.className = 'banner-dot w-2.5 h-2.5 rounded-full transition-all duration-300 bg-white/50 hover:bg-white';
        });
        
        banners[index].classList.remove('opacity-0', 'z-0');
        banners[index].classList.add('opacity-100', 'z-10');
        dots[index].className = 'banner-dot h-2.5 rounded-full transition-all duration-300 bg-gold w-8';
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
        autoSlideInterval = setInterval(nextBanner, 6000);
    }

    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }

    function resetAutoSlide() {
        stopAutoSlide();
        startAutoSlide();
    }

    if (totalBanners > 1) {
        startAutoSlide();
        
        prevBtn.addEventListener('click', () => {
            prevBanner();
            resetAutoSlide();
        });

        nextBtn.addEventListener('click', () => {
            nextBanner();
            resetAutoSlide();
        });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                currentBanner = index;
                showBanner(currentBanner);
                resetAutoSlide();
            });
        });
    }
</script>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <!-- Categories Section -->
    @if($categories->count() > 0)
    <section class="mb-24">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h5 class="text-gold font-bold tracking-widest uppercase text-xs mb-2">Discover</h5>
                <h2 class="text-3xl font-serif font-bold text-primary">Curated Categories</h2>
            </div>
            <a href="{{ route('categories.index') }}" class="text-sm font-medium text-neutral-500 hover:text-primary transition-colors inline-flex items-center gap-2 group">
                View All <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach($categories as $category)
            <a href="{{ route('categories.show', $category) }}" 
                class="group flex flex-col items-center justify-center p-8 bg-white border border-neutral-100 rounded-2xl shadow-soft hover:shadow-hover hover:-translate-y-1 transition-all duration-300">
                <div class="w-16 h-16 rounded-full bg-neutral-50 flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-gold transition-colors text-neutral-400">
                    <i data-lucide="folder" class="w-6 h-6"></i>
                </div>
                <p class="font-bold text-sm text-primary group-hover:text-gold transition-colors">{{ $category->name }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Top Selling Products -->
    @if($topProducts->count() > 0)
    <section class="mb-24">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h5 class="text-gold font-bold tracking-widest uppercase text-xs mb-2">Trending Now</h5>
                <h2 class="text-3xl font-serif font-bold text-primary">Top Sellers</h2>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($topProducts as $product)
            <a href="{{ route('products.show', $product) }}" class="group flex flex-col bg-white overflow-hidden text-decoration-none">
                <div class="relative aspect-[4/5] bg-neutral-50 mb-4 overflow-hidden rounded-xl">
                    @if($product->images->first())
                    <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" alt="{{ $product->name }}">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i data-lucide="image" class="w-12 h-12 text-neutral-300"></i>
                    </div>
                    @endif
                    <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                
                <div>
                    <h3 class="font-bold text-primary truncate group-hover:text-gold transition-colors text-lg mb-1">{{ $product->name }}</h3>
                    <p class="text-neutral-500 text-xs uppercase tracking-wider mb-2">{{ $product->seller->name }}</p>
                    
                    <div class="flex items-center gap-3">
                        @if($product->hasDiscount())
                        <span class="text-primary font-bold">${{ number_format($product->getDiscountedPrice(), 2) }}</span>
                        <span class="text-neutral-400 line-through text-xs">${{ number_format($product->price, 2) }}</span>
                        @else
                        <span class="text-primary font-bold">${{ number_format($product->price, 2) }}</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Discounted Products -->
    @if($discountedProducts->count() > 0)
    <section class="mb-24">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h5 class="text-red-500 font-bold tracking-widest uppercase text-xs mb-2">Limited Time</h5>
                <h2 class="text-3xl font-serif font-bold text-primary">Special Offers</h2>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($discountedProducts as $product)
            <a href="{{ route('products.show', $product) }}" class="group flex flex-col bg-white overflow-hidden text-decoration-none">
                <div class="relative aspect-[4/5] bg-neutral-50 mb-4 overflow-hidden rounded-xl">
                    @if($product->discount_percent)
                    <div class="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 text-xs font-bold tracking-wider z-10 shadow-sm">
                        -{{ $product->discount_percent }}%
                    </div>
                    @endif
                    @if($product->images->first())
                    <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" alt="{{ $product->name }}">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i data-lucide="image" class="w-12 h-12 text-neutral-300"></i>
                    </div>
                    @endif
                </div>
                
                <div>
                    <h3 class="font-bold text-primary truncate group-hover:text-gold transition-colors text-lg mb-1">{{ $product->name }}</h3>
                    <p class="text-neutral-500 text-xs uppercase tracking-wider mb-2">{{ $product->seller->name }}</p>
                    <div class="flex items-center gap-3">
                        <span class="text-red-600 font-bold">${{ number_format($product->getDiscountedPrice(), 2) }}</span>
                        <span class="text-neutral-400 line-through text-xs">${{ number_format($product->price, 2) }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- All Products -->
    <section class="mb-24">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h5 class="text-gold font-bold tracking-widest uppercase text-xs mb-2">Full Collection</h5>
                <h2 class="text-3xl font-serif font-bold text-primary">The Boutique</h2>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            @foreach($products as $product)
            <a href="{{ route('products.show', $product) }}" class="group flex flex-col bg-white overflow-hidden text-decoration-none border border-transparent hover:border-neutral-100 p-3 -m-3 rounded-2xl transition-all">
                <div class="relative aspect-[4/5] bg-neutral-50 mb-4 overflow-hidden rounded-xl">
                    @if($product->images->first())
                    <img src="{{ asset('storage/' . $product->images->first()->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" alt="{{ $product->name }}">
                    @else
                    <div class="w-full h-full flex items-center justify-center">
                        <i data-lucide="image" class="w-12 h-12 text-neutral-300"></i>
                    </div>
                    @endif
                </div>
                
                <div class="px-2">
                    <h3 class="font-bold text-primary truncate group-hover:text-gold transition-colors text-lg mb-1">{{ $product->name }}</h3>
                    <div class="flex justify-between items-center mb-1">
                        <p class="text-neutral-500 text-xs uppercase tracking-wider">{{ $product->seller->name }}</p>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-neutral-300 group-hover:text-gold transition-colors -translate-x-2 opacity-0 group-hover:translate-x-0 group-hover:opacity-100"></i>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($product->hasDiscount())
                        <span class="text-primary font-bold">${{ number_format($product->getDiscountedPrice(), 2) }}</span>
                        <span class="text-neutral-400 line-through text-xs">${{ number_format($product->price, 2) }}</span>
                        @else
                        <span class="text-primary font-bold">${{ number_format($product->price, 2) }}</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $products->links() }}
        </div>
    </section>
</div>
@endsection
