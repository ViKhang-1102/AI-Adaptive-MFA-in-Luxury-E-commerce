

<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-blue-600"><?php echo e($totalCustomers); ?></div>
            <p class="text-gray-600">Customers</p>
        </div>
        <div class="bg-green-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-green-600"><?php echo e($totalSellers); ?></div>
            <p class="text-gray-600">Sellers</p>
        </div>
        <div class="bg-cyan-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-cyan-600"><?php echo e($totalProducts); ?></div>
            <p class="text-gray-600">Products</p>
        </div>
        <div class="bg-indigo-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-indigo-600"><?php echo e($totalCategories); ?></div>
            <p class="text-gray-600">Categories</p>
        </div>
        <div class="bg-purple-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-purple-600"><?php echo e($totalOrders); ?></div>
            <p class="text-gray-600">Total Orders</p>
        </div>
        <div class="bg-orange-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-orange-600">$<?php echo e(number_format(($totalRevenue ?? 0) / env('VND_PER_USD', 23000), 2)); ?></div>
            <p class="text-gray-600">Revenue</p>
        </div>
        <div class="bg-red-100 p-6 rounded-lg shadow">
            <div class="text-2xl font-bold text-red-600"><?php echo e($todayOrders); ?></div>
            <p class="text-gray-600">Today's Orders</p>
        </div>
    </div>

    <!-- Top & Bottom Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        <!-- Top Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4">🔥 Top 3 Selling Products (30 days)</h3>
            <?php if($topProducts->count() > 0): ?>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between pb-2 border-b">
                        <span class="text-gray-700"><?php echo e($item->product->name ?? 'N/A'); ?></span>
                        <span class="font-bold text-green-600"><?php echo e($item->total_qty); ?> sold</span>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No sales in last 30 days</p>
            <?php endif; ?>
        </div>

        <!-- Bottom Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="font-bold text-lg mb-4">📉 Bottom 3 Selling Products (30 days)</h3>
            <?php if($bottomProducts->count() > 0): ?>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $bottomProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between pb-2 border-b">
                        <span class="text-gray-700"><?php echo e($item->product->name ?? 'N/A'); ?></span>
                        <span class="font-bold text-red-600"><?php echo e($item->total_qty); ?> sold</span>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-500">No sales in last 30 days</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Menu -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="font-bold text-lg mb-4">Admin Panel</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="<?php echo e(route('admin.customers.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-users text-blue-600 mr-2"></i> Manage Customers
            </a>
            <a href="<?php echo e(route('admin.sellers.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-store text-blue-600 mr-2"></i> Manage Sellers
            </a>
            <a href="<?php echo e(route('admin.categories.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-folder text-blue-600 mr-2"></i> Categories
            </a>
            <a href="<?php echo e(route('admin.banners.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-image text-blue-600 mr-2"></i> Banners
            </a>
            <a href="<?php echo e(route('admin.fees.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-percent text-blue-600 mr-2"></i> Fees & Settings
            </a>
            <a href="<?php echo e(route('admin.wallet')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-wallet text-blue-600 mr-2"></i> Platform Wallet
            </a>
            <a href="<?php echo e(route('admin.orders.index')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-receipt text-blue-600 mr-2"></i> All Orders
            </a>
            <a href="<?php echo e(route('profile.show')); ?>" class="p-4 border rounded hover:bg-gray-50">
                <i class="fas fa-user text-blue-600 mr-2"></i> My Profile
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>