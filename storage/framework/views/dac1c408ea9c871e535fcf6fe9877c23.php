

<?php $__env->startSection('title', 'Products'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Products</h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-4">Filters</h3>
                
                <form action="<?php echo e(route('products.index')); ?>" method="GET" class="space-y-4">
                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-bold mb-2">Category</label>
                        <select name="category" class="w-full px-3 py-2 border rounded">
                            <option value="">All Categories</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category') == $cat->id ? 'selected' : ''); ?>>
                                <?php echo e($cat->name); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div>
                        <label class="block text-sm font-bold mb-2">Sort By</label>
                        <select name="sort" class="w-full px-3 py-2 border rounded">
                            <option value="">Newest</option>
                            <option value="price_low" <?php echo e(request('sort') == 'price_low' ? 'selected' : ''); ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo e(request('sort') == 'price_high' ? 'selected' : ''); ?>>Price: High to Low</option>
                            <option value="popular" <?php echo e(request('sort') == 'popular' ? 'selected' : ''); ?>>Most Popular</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                        Apply Filters
                    </button>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('products.show', $product)); ?>" class="block bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden text-decoration-none group">
                    <?php if($product->images->first()): ?>
                    <img src="<?php echo e(asset('storage/' . $product->images->first()->image)); ?>" class="w-full h-48 object-cover group-hover:opacity-90 transition" alt="<?php echo e($product->name); ?>">
                    <?php else: ?>
                    <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
                    <?php endif; ?>
                    <div class="p-4">
                        <h3 class="font-bold truncate group-hover:text-blue-600"><?php echo e($product->name); ?></h3>
                        <p class="text-gray-600 text-sm mb-2"><?php echo e($product->seller->name); ?></p>
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <span class="font-bold">$<?php echo e(number_format($product->getDiscountedPrice(), 2)); ?></span>
                                <?php if($product->hasDiscount()): ?>
                                <span class="text-gray-400 line-through text-sm">$<?php echo e(number_format($product->price, 2)); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="text-blue-600 group-hover:text-blue-800">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <?php echo e($products->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/products/index.blade.php ENDPATH**/ ?>