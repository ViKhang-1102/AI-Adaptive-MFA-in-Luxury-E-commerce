

<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <h1 class="text-3xl font-bold">Admin Dashboard</h1>

        <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="flex gap-2 items-center bg-white p-2 rounded-md shadow-sm">
            <select name="month" class="border rounded-md px-3 py-2 bg-white text-sm outline-none focus:border-primary">
                <option value="">All Months</option>
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo e($m); ?>" <?php echo e((isset($month) && $month == $m) ? 'selected' : ''); ?>>Month <?php echo e(sprintf('%02d', $m)); ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="border rounded-md px-3 py-2 bg-white text-sm outline-none focus:border-primary">
                <option value="">All Years</option>
                <?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e((isset($year) && $year == $y) ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-light transition-colors text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-blue-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-primary"><?php echo e($totalCustomers); ?></div>
            <p class="text-neutral-600">Customers</p>
        </div>
        <div class="bg-green-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-green-600"><?php echo e($totalSellers); ?></div>
            <p class="text-neutral-600">Sellers</p>
        </div>
        <div class="bg-cyan-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-cyan-600"><?php echo e($totalProducts); ?></div>
            <p class="text-neutral-600">Products</p>
        </div>
        <div class="bg-indigo-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-indigo-600"><?php echo e($totalCategories); ?></div>
            <p class="text-neutral-600">Categories</p>
        </div>
        <div class="bg-purple-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-purple-600"><?php echo e($totalOrders); ?></div>
            <p class="text-neutral-600">Total Orders</p>
        </div>
        <div class="bg-red-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-red-600"><?php echo e($todayOrders); ?></div>
            <p class="text-neutral-600">Today's Orders</p>
        </div>
        <div class="bg-orange-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-orange-600">$<?php echo e(number_format(($totalRevenue ?? 0), 2)); ?></div>
            <p class="text-neutral-600">Revenue</p>
        </div>

        <div class="bg-amber-100 p-6 rounded-md-lg shadow-sm">
            <div class="text-2xl font-bold text-amber-600">$<?php echo e(number_format($totalPlatformBalance, 2)); ?></div>
            <p class="text-neutral-600">Platform Fee Balance</p>
        </div>
    </div>


    <!-- Top & Bottom Products -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        <!-- Top Products -->
        <div class="bg-white p-6 rounded-md-lg shadow-sm">
            <h3 class="font-bold text-lg mb-4">🔥 Top 3 Selling Products (30 days)</h3>
            <?php if($topProducts->count() > 0): ?>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between pb-2 border-b">
                        <span class="text-neutral-700"><?php echo e($item->product->name ?? 'N/A'); ?></span>
                        <span class="font-bold text-green-600"><?php echo e($item->total_qty); ?> sold</span>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php else: ?>
                <p class="text-neutral-500">No sales in last 30 days</p>
            <?php endif; ?>
        </div>

        <!-- Bottom Products -->
        <div class="bg-white p-6 rounded-md-lg shadow-sm">
            <h3 class="font-bold text-lg mb-4">📉 Bottom 3 Selling Products (30 days)</h3>
            <?php if($bottomProducts->count() > 0): ?>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $bottomProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between pb-2 border-b">
                        <span class="text-neutral-700"><?php echo e($item->product->name ?? 'N/A'); ?></span>
                        <span class="font-bold text-red-600"><?php echo e($item->total_qty); ?> sold</span>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php else: ?>
                <p class="text-neutral-500">No sales in last 30 days</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Menu -->
    <div class="bg-white p-6 rounded-md-lg shadow-sm">
        <h3 class="font-bold text-lg mb-4">Admin Panel</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="<?php echo e(route('admin.customers.index')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-users text-primary mr-2"></i> Manage Customers
            </a>
            <a href="<?php echo e(route('admin.sellers.index')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-store text-primary mr-2"></i> Manage Sellers
            </a>
            <a href="<?php echo e(route('admin.categories.index')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-folder text-primary mr-2"></i> Categories
            </a>
            <a href="<?php echo e(route('admin.banners.index')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-image text-primary mr-2"></i> Banners
            </a>
            <a href="<?php echo e(route('admin.fees.index')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-percent text-primary mr-2"></i> Fees & Settings
            </a>
            <a href="<?php echo e(route('admin.wallet')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-wallet text-primary mr-2"></i> Platform Wallet
            </a>
            <a href="<?php echo e(route('admin.orders.index')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-receipt text-primary mr-2"></i> All Orders
            </a>
            <a href="<?php echo e(route('admin.orders.pending')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-exclamation-triangle text-primary mr-2"></i> Pending Verifications
            </a>
            <a href="<?php echo e(route('profile.show')); ?>" class="p-4 border rounded-md hover:bg-neutral-50">
                <i class="fas fa-user text-primary mr-2"></i> My Profile
            </a>
            <a href="<?php echo e(route('admin.security.index')); ?>" class="p-4 border border-gold bg-[#0A192F] text-white rounded-md hover:bg-[#112240] transition-colors shadow-soft">
                <i class="fas fa-shield-alt text-gold mr-2"></i> Security Insights 
                <span class="ml-2 bg-gold text-[#0A192F] text-xs font-bold px-2 py-1 rounded-full">AI</span>
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>