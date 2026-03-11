<header class="bg-white/80 backdrop-blur-md border-b border-neutral-100 shadow-sm-sm sticky top-0 z-50">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center gap-8">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-2 group">
                <i data-lucide="gem" class="w-8 h-8 text-gold group-hover:scale-110 transition-transform duration-300"></i>
                <span class="text-2xl font-serif font-bold text-primary tracking-tight">LuxGuard</span>
            </a>

            <!-- Search Bar -->
            <form action="{{ route('products.index') }}" method="GET" class="hidden md:block flex-1 max-w-xl relative group">
                <input type="text" name="search" placeholder="Search luxury collections..." 
                    class="w-full pl-11 pr-4 py-2.5 bg-neutral-50 border border-transparent rounded-md-full focus:bg-white focus:ring-2 focus:ring-gold/50 focus:border-gold outline-none transition-all shadow-sm-soft text-sm">
                <i data-lucide="search" class="absolute left-4 top-3 w-4 h-4 text-neutral-400 group-focus-within:text-gold transition-colors"></i>
            </form>

            <!-- Navigation Links -->
            <div class="flex items-center space-x-6 sm:space-x-8">
                <a href="{{ route('products.index') }}" class="hidden sm:block text-sm font-medium text-neutral-600 hover:text-primary transition-colors">
                    Collections
                </a>
                <a href="{{ route('categories.index') }}" class="hidden xl:block text-sm font-medium text-neutral-600 hover:text-primary transition-colors">
                    Categories
                </a>

                @auth
                    <!-- Cart Icon -->
                    <a href="{{ route('cart.index') }}" class="relative text-neutral-600 hover:text-primary transition-colors group">
                        <i data-lucide="shopping-bag" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                        @php
                            $cartCount = auth()->user()->cart?->items()->count() ?? 0;
                        @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-2 -right-2 bg-gold text-primary text-[10px] font-bold rounded-md-full w-4 h-4 flex items-center justify-center shadow-sm-sm border border-white">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                    @if(auth()->user()->isSeller() || auth()->user()->isCustomer())
                        <!-- Message Icon (Global) -->
                        <a href="{{ auth()->user()->isSeller() ? route('seller.messages.index') : route('customer.messages.index') }}" class="relative text-neutral-600 hover:text-primary transition-colors group">
                            @if(auth()->user()->isSeller())
                                <i data-lucide="message-circle" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                            @else
                                <i data-lucide="message-square" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                            @endif
                            
                            @if(auth()->user()->unreadMessagesCount() > 0)
                                <span data-message-badge class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold rounded-md-full w-4 h-4 flex items-center justify-center shadow-sm-sm border border-white">
                                    {{ auth()->user()->unreadMessagesCount() }}
                                </span>
                            @else
                                <span data-message-badge class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold rounded-md-full w-4 h-4 flex items-center justify-center shadow-sm-sm border border-white hidden">
                                    0
                                </span>
                            @endif
                        </a>
                    @endif

                    <!-- User Menu -->
                    <div class="relative" id="user-menu-container">
                        <button id="user-menu-button" class="text-neutral-600 hover:text-primary flex items-center gap-2 transition-colors focus:outline-none">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" 
                                     alt="{{ auth()->user()->name }}" 
                                     class="w-8 h-8 rounded-md-full object-cover border-2 border-transparent hover:border-gold transition-colors">
                            @else
                                <div class="w-8 h-8 rounded-md-full bg-neutral-100 flex items-center justify-center border border-neutral-200 text-primary">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                            @endif
                            <span class="hidden md:inline text-sm font-medium">{{ auth()->user()->name }}</span>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-neutral-400"></i>
                        </button>
                        
                        <div id="user-menu-dropdown" class="absolute right-0 mt-3 w-64 bg-white rounded-md-2xl shadow-sm-hover border border-neutral-100 hidden z-50 overflow-hidden transform opacity-0 scale-95 transition-all duration-200 origin-top-right">
                            <div class="p-4 border-b border-neutral-50 bg-neutral-50/50">
                                <p class="text-sm font-medium text-primary truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-neutral-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            
                            <div class="p-2">
                                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 hover:text-primary rounded-md-xl transition-colors">
                                    <i data-lucide="user-circle" class="w-4 h-4 text-neutral-400"></i> My Profile
                                </a>
                                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 hover:text-primary rounded-md-xl transition-colors">
                                    <i data-lucide="package" class="w-4 h-4 text-neutral-400"></i> My Orders
                                </a>
                                <a href="{{ route('payments.history') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 hover:text-primary rounded-md-xl transition-colors">
                                    <i data-lucide="receipt" class="w-4 h-4 text-neutral-400"></i> Payment History
                                </a>

                                @if(auth()->user()->isCustomer() || auth()->user()->isSeller())
                                    <a href="{{ route('wishlist') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 hover:text-primary rounded-md-xl transition-colors">
                                        <i data-lucide="heart" class="w-4 h-4 text-neutral-400"></i> Wishlist
                                    </a>
                                    <a href="{{ route('addresses.index') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 hover:text-primary rounded-md-xl transition-colors">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-neutral-400"></i> Addresses
                                    </a>
                                    <a href="{{ route('customer.messages.index') }}" class="flex items-center justify-between px-3 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 hover:text-primary rounded-md-xl transition-colors">
                                        <div class="flex items-center gap-3">
                                            <i data-lucide="message-square" class="w-4 h-4 text-neutral-400"></i> Customer Messages
                                        </div>
                                    </a>
                                @endif

                                @if(auth()->user()->isSeller())
                                    <div class="h-px bg-neutral-100 my-2"></div>
                                    <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-gold-dark hover:bg-gold-light/20 hover:text-gold-dark rounded-md-xl transition-colors">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Seller Dashboard
                                    </a>
                                    <a href="{{ route('seller.messages.index') }}" class="flex items-center justify-between px-3 py-2.5 text-sm text-neutral-700 hover:bg-neutral-50 hover:text-primary rounded-md-xl transition-colors">
                                        <div class="flex items-center gap-3">
                                            <i data-lucide="message-circle" class="w-4 h-4 text-neutral-400"></i> Seller Messages
                                        </div>
                                        @if(auth()->user()->unreadMessagesCount() > 0)
                                            <span data-message-badge class="bg-red-500 text-white rounded-md-full px-2 py-0.5 text-[10px] font-bold shadow-sm-sm">{{ auth()->user()->unreadMessagesCount() }}</span>
                                        @else
                                            <span data-message-badge class="bg-red-500 text-white rounded-md-full px-2 py-0.5 text-[10px] font-bold shadow-sm-sm hidden">0</span>
                                        @endif
                                    </a>
                                @endif

                                @if(auth()->user()->isAdmin())
                                    <div class="h-px bg-neutral-100 my-2"></div>
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 rounded-md-xl transition-colors">
                                        <i data-lucide="shield" class="w-4 h-4"></i> Admin Panel
                                    </a>
                                @endif
                            </div>

                            <form action="{{ route('logout') }}" method="POST" class="p-2 border-t border-neutral-100 bg-neutral-50/50">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-neutral-600 hover:text-primary hover:bg-white rounded-md-xl transition-colors">
                                    <i data-lucide="log-out" class="w-4 h-4 text-neutral-400"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-neutral-600 hover:text-primary transition-colors">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="text-sm font-medium px-5 py-2.5 bg-primary text-white rounded-md-full hover:bg-primary-light transition-all shadow-sm-soft hover:shadow-sm-hover hover:-translate-y-0.5">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</header>
@auth
@if(auth()->user()->isSeller() || auth()->user()->isCustomer())
<script>
    // Determine correct route for unread count
    const unreadCountUrl = "{{ auth()->user()->isSeller() ? route('seller.messages.unread-count') : route('messages.unread-count') }}";

    function updateMessageBadge() {
        fetch(unreadCountUrl)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                const badges = document.querySelectorAll('[data-message-badge]');
                badges.forEach(badge => {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                });
            })
            .catch(error => console.error('Error updating message badge:', error));
    }

    updateMessageBadge();
    setInterval(updateMessageBadge, 5000);
</script>
@endif
@endauth