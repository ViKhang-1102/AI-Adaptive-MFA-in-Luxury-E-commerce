

<?php $__env->startSection('title', 'Home - E-Commerce Platform'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4">
    <!-- Banners Carousel -->
    <?php if($banners->count() > 0): ?>
    <div class="mt-4 mb-8">
        <div class="relative bg-gray-200 h-96 rounded-lg overflow-hidden">
            <?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="absolute inset-0 opacity-0 hover:opacity-100 transition-opacity">
                <img src="<?php echo e(asset('storage/' . $banner->image)); ?>" class="w-full h-full object-cover" alt="<?php echo e($banner->title); ?>">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                    <h2 class="text-white text-4xl font-bold"><?php echo e($banner->title); ?></h2>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <img src="https://via.placeholder.com/1200x400?text=Welcome+to+E-Shop" class="w-full h-full object-cover" alt="Banner">
        </div>
    </div>
    <?php endif; ?>

    <!-- Categories Section -->
    <?php if($categories->count() > 0): ?>
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Categories</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('categories.show', $category)); ?>" 
                class="text-center p-4 bg-white rounded-lg shadow hover:shadow-lg transition">
                <i class="fas fa-folder text-3xl text-blue-600 mb-2"></i>
                <p class="font-semibold text-sm"><?php echo e($category->name); ?></p>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Top Selling Products -->
    <?php if($topProducts->count() > 0): ?>
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">🔥 Top Selling</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                <?php if($product->images->first()): ?>
                <img src="<?php echo e(asset('storage/' . $product->images->first()->image)); ?>" class="w-full h-48 object-cover" alt="<?php echo e($product->name); ?>">
                <?php else: ?>
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                <?php endif; ?>
                <div class="p-4">
                    <h3 class="font-bold truncate"><?php echo e($product->name); ?></h3>
                    <p class="text-gray-600 text-sm mb-2"><?php echo e($product->seller->name); ?></p>
                    <div class="flex justify-between items-center">
                        <div>
                            <?php if($product->hasDiscount()): ?>
                            <span class="text-red-600 font-bold">$<?php echo e(number_format($product->getDiscountedPrice(), 2)); ?></span>
                            <span class="text-gray-400 line-through text-sm">$<?php echo e(number_format($product->price, 2)); ?></span>
                            <?php else: ?>
                            <span class="font-bold">$<?php echo e(number_format($product->price, 2)); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo e(route('products.show', $product)); ?>" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Discounted Products -->
    <?php if($discountedProducts->count() > 0): ?>
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">💰 Special Discounts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php $__currentLoopData = $discountedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden relative">
                <?php if($product->discount_percent): ?>
                <div class="absolute top-2 right-2 bg-red-600 text-white px-2 py-1 rounded text-sm font-bold">
                    -<?php echo e($product->discount_percent); ?>%
                </div>
                <?php endif; ?>
                <?php if($product->images->first()): ?>
                <img src="<?php echo e(asset('storage/' . $product->images->first()->image)); ?>" class="w-full h-48 object-cover" alt="<?php echo e($product->name); ?>">
                <?php else: ?>
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                <?php endif; ?>
                <div class="p-4">
                    <h3 class="font-bold truncate"><?php echo e($product->name); ?></h3>
                    <p class="text-gray-600 text-sm mb-2"><?php echo e($product->seller->name); ?></p>
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-red-600 font-bold">$<?php echo e(number_format($product->getDiscountedPrice(), 2)); ?></span>
                            <span class="text-gray-400 line-through text-sm">$<?php echo e(number_format($product->price, 2)); ?></span>
                        </div>
                        <a href="<?php echo e(route('products.show', $product)); ?>" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- All Products -->
    <section class="mb-12">
        <h2 class="text-2xl font-bold mb-6">All Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                <?php if($product->images->first()): ?>
                <img src="<?php echo e(asset('storage/' . $product->images->first()->image)); ?>" class="w-full h-48 object-cover" alt="<?php echo e($product->name); ?>">
                <?php else: ?>
                <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                <?php endif; ?>
                <div class="p-4">
                    <h3 class="font-bold truncate"><?php echo e($product->name); ?></h3>
                    <p class="text-gray-600 text-sm mb-2"><?php echo e($product->seller->name); ?></p>
                    <div class="flex justify-between items-center">
                        <span class="font-bold">$<?php echo e(number_format($product->getDiscountedPrice(), 2)); ?></span>
                        <a href="<?php echo e(route('products.show', $product)); ?>" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            <?php echo e($products->links()); ?>

        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/home.blade.php ENDPATH**/ ?>