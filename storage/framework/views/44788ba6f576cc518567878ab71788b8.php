<header class="bg-white shadow-md">
    <nav class="max-w-7xl mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="<?php echo e(route('home')); ?>" class="text-2xl font-bold text-blue-600">
                <i class="fas fa-shopping-bag"></i> E-Shop
            </a>

            <!-- Search Bar -->
            <form action="<?php echo e(route('products.index')); ?>" method="GET" class="hidden md:flex w-1/2">
                <input type="text" name="search" placeholder="Search products..." 
                    class="flex-1 px-4 py-2 border rounded-l-lg focus:outline-none">
                <button class="px-6 py-2 bg-blue-600 text-white rounded-r-lg">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <!-- Navigation Links -->
            <div class="flex items-center space-x-4">
                <a href="<?php echo e(route('products.index')); ?>" class="text-gray-700 hover:text-blue-600">
                    Products
                </a>
                <a href="<?php echo e(route('categories.index')); ?>" class="text-gray-700 hover:text-blue-600">
                    Categories
                </a>

                <?php if(auth()->guard()->check()): ?>
                    <!-- Cart Icon -->
                    <a href="<?php echo e(route('cart.index')); ?>" class="relative text-gray-700 hover:text-blue-600">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <?php
                            $cartCount = auth()->user()->cart?->items()->count() ?? 0;
                        ?>
                        <?php if($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                <?php echo e($cartCount); ?>

                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Menu -->
                    <div class="relative" id="user-menu-container">
                        <button id="user-menu-button" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                            <?php if(auth()->user()->avatar): ?>
                                <img src="<?php echo e(asset('storage/' . auth()->user()->avatar)); ?>" 
                                     alt="<?php echo e(auth()->user()->name); ?>" 
                                     class="w-8 h-8 rounded-full object-cover border border-gray-300">
                            <?php else: ?>
                                <i class="fas fa-user-circle text-2xl"></i>
                            <?php endif; ?>
                            <span class="hidden sm:inline"><?php echo e(auth()->user()->name); ?></span>
                        </button>
                        <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden z-10">
                            <a href="<?php echo e(route('profile.show')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                My Profile
                            </a>
                            <a href="<?php echo e(route('orders.index')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                My Orders
                            </a>

                            <?php if(auth()->user()->isCustomer()): ?>
                                <a href="<?php echo e(route('wishlist')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Wishlist
                                </a>
                                <a href="<?php echo e(route('addresses.index')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Addresses
                                </a>
                                <a href="<?php echo e(route('customer.messages.index')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 flex justify-between">
                                    <span>Messages</span>
                                    <?php if(auth()->user()->unreadMessagesCount() > 0): ?>
                                        <span data-message-badge class="bg-red-600 text-white rounded-full px-2 text-xs flex items-center"><?php echo e(auth()->user()->unreadMessagesCount()); ?></span>
                                    <?php else: ?>
                                        <span data-message-badge class="bg-red-600 text-white rounded-full px-2 text-xs flex items-center hidden"></span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>

                            <?php if(auth()->user()->isSeller()): ?>
                                <a href="<?php echo e(route('seller.dashboard')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Seller Dashboard
                                </a>
                                <a href="<?php echo e(route('seller.messages.index')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 flex justify-between">
                                    <span>Messages</span>
                                    <?php if(auth()->user()->unreadMessagesCount() > 0): ?>
                                        <span data-message-badge class="bg-red-600 text-white rounded-full px-2 text-xs flex items-center"><?php echo e(auth()->user()->unreadMessagesCount()); ?></span>
                                    <?php else: ?>
                                        <span data-message-badge class="bg-red-600 text-white rounded-full px-2 text-xs flex items-center hidden"></span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>

                            <?php if(auth()->user()->isAdmin()): ?>
                                <a href="<?php echo e(route('admin.dashboard')); ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Admin Panel
                                </a>
                            <?php endif; ?>

                            <form action="<?php echo e(route('logout')); ?>" method="POST" class="border-t">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="text-gray-700 hover:text-blue-600">
                        Login
                    </a>
                    <a href="<?php echo e(route('register')); ?>" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
<?php if(auth()->guard()->check()): ?>
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
<?php endif; ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/layouts/header.blade.php ENDPATH**/ ?>