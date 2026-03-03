<header class="bg-white shadow-md">
    <nav class="max-w-7xl mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">
                <i class="fas fa-shopping-bag"></i> E-Shop
            </a>

            <!-- Search Bar -->
            <form action="{{ route('products.index') }}" method="GET" class="hidden md:flex w-1/2">
                <input type="text" name="search" placeholder="Search products..." 
                    class="flex-1 px-4 py-2 border rounded-l-lg focus:outline-none">
                <button class="px-6 py-2 bg-blue-600 text-white rounded-r-lg">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-blue-600">
                    Products
                </a>
                <a href="{{ route('categories.index') }}" class="text-gray-700 hover:text-blue-600">
                    Categories
                </a>

                @auth
                    <!-- Cart Icon -->
                    <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-blue-600">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        @php
                            $cartCount = auth()->user()->cart?->items()->count() ?? 0;
                        @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                    <!-- User Menu -->
                    <div class="relative" id="user-menu-container">
                        <button id="user-menu-button" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" 
                                     alt="{{ auth()->user()->name }}" 
                                     class="w-8 h-8 rounded-full object-cover border border-gray-300">
                            @else
                                <i class="fas fa-user-circle text-2xl"></i>
                            @endif
                            <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                        </button>
                        <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden z-10">
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                My Profile
                            </a>
                            <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                My Orders
                            </a>

                            @if(auth()->user()->isCustomer())
                                <a href="{{ route('wishlist') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Wishlist
                                </a>
                                <a href="{{ route('addresses.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Addresses
                                </a>
                                <a href="{{ route('customer.messages.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 flex justify-between">
                                    <span>Messages</span>
                                    @if(auth()->user()->unreadMessagesCount() > 0)
                                        <span data-message-badge class="bg-red-600 text-white rounded-full px-2 text-xs flex items-center">{{ auth()->user()->unreadMessagesCount() }}</span>
                                    @else
                                        <span data-message-badge class="bg-red-600 text-white rounded-full px-2 text-xs flex items-center hidden"></span>
                                    @endif
                                </a>
                            @endif

                            @if(auth()->user()->isSeller())
                                <a href="{{ route('seller.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Seller Dashboard
                                </a>
                                <a href="{{ route('seller.messages.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 flex justify-between">
                                    <span>Messages</span>
                                    @if(auth()->user()->unreadMessagesCount() > 0)
                                        <span data-message-badge class="bg-red-600 text-white rounded-full px-2 text-xs flex items-center">{{ auth()->user()->unreadMessagesCount() }}</span>
                                    @else
                                        <span data-message-badge class="bg-red-600 text-white rounded-full px-2 text-xs flex items-center hidden"></span>
                                    @endif
                                </a>
                            @endif

                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Admin Panel
                                </a>
                            @endif

                            <form action="{{ route('logout') }}" method="POST" class="border-t">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</header>
@auth
<script>
    // Update message badge count every 3 seconds
    function updateMessageBadge() {
        fetch('/messages/unread/count')
            .then(response => response.json())
            .then(data => {
                const badges = document.querySelectorAll('[data-message-badge]');
                badges.forEach(badge => {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.parentElement.style.display = 'flex';
                    } else {
                        badge.parentElement.style.display = 'none';
                    }
                });
            })
            .catch(error => console.error('Error updating message badge:', error));
    }

    // Update badge on page load and every 3 seconds
    updateMessageBadge();
    setInterval(updateMessageBadge, 3000);
</script>
@endauth