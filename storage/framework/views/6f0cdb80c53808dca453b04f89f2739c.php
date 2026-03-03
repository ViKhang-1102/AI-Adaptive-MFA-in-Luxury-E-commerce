

<?php $__env->startSection('title', 'Seller Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Seller Dashboard</h1>
        <a href="<?php echo e(route('home')); ?>" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back Home
        </a>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-blue-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-blue-600"><?php echo e($totalProducts); ?></div>
            <p class="text-gray-600">Products</p>
        </div>
        <div class="bg-green-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-green-600"><?php echo e($totalOrders); ?></div>
            <p class="text-gray-600">Total Orders</p>
        </div>
        <div class="bg-purple-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-purple-600">$<?php echo e(number_format($totalRevenue / env('VND_PER_USD', 23000), 2)); ?></div>
            <p class="text-gray-600">Total Revenue</p>
        </div>
        <div class="bg-orange-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-orange-600"><?php echo e($pendingOrders); ?></div>
            <p class="text-gray-600">Pending Orders</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <a href="<?php echo e(route('seller.products.create')); ?>" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <i class="fas fa-plus text-2xl text-blue-600 mb-2"></i>
            <h3 class="font-bold">Add Product</h3>
            <p class="text-sm text-gray-600">Create a new product</p>
        </a>
        <a href="<?php echo e(route('seller.orders.index')); ?>" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
            <i class="fas fa-shopping-cart text-2xl text-green-600 mb-2"></i>
            <h3 class="font-bold">Manage Orders</h3>
            <p class="text-sm text-gray-600">View and manage orders</p>
        </a>
        <a href="<?php echo e(route('seller.wallet')); ?>" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition relative">
            <i class="fas fa-wallet text-2xl text-purple-600 mb-2"></i>
            <h3 class="font-bold">My Wallet</h3>
            <p class="text-sm text-gray-600">Check wallet balance</p>
        </a>
        <a href="<?php echo e(route('seller.messages.index')); ?>" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition relative">
            <i class="fas fa-envelope text-2xl text-blue-600 mb-2"></i>
            <h3 class="font-bold">Messages</h3>
            <p class="text-sm text-gray-600">View inbox</p>
            <?php if(isset($unreadMessages) && $unreadMessages > 0): ?>
            <span class="absolute top-2 right-4 bg-red-600 text-white text-xs px-2 py-1 rounded-full"><?php echo e($unreadMessages); ?></span>
            <?php endif; ?>
        </a>
    </div>

    <!-- Menu Links -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-bold text-lg mb-4">Seller Menu</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="<?php echo e(route('seller.products.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-box text-blue-600 mr-2"></i> Manage Products
            </a>
            <a href="<?php echo e(route('seller.categories.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-folder text-blue-600 mr-2"></i> View Categories
            </a>
            <a href="<?php echo e(route('seller.orders.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-receipt text-blue-600 mr-2"></i> Orders
            </a>
            <a href="<?php echo e(route('seller.wallet')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-credit-card text-blue-600 mr-2"></i> Wallet
            </a>
            <a href="<?php echo e(route('profile.show')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-user text-blue-600 mr-2"></i> My Profile
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/dashboard.blade.php ENDPATH**/ ?>