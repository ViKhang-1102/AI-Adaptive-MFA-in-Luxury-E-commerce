

<?php $__env->startSection('title', $category->name); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8"><?php echo e($category->name); ?></h1>

    <?php if($products->isEmpty()): ?>
    <div class="bg-white p-8 rounded-md-lg shadow-sm text-center">
        <p class="text-neutral-600">No products in this category yet.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('products.show', $product)); ?>" class="block bg-white rounded-md-lg shadow-sm hover:shadow-sm-lg transition overflow-hidden text-decoration-none group">
            <?php if($product->images->first()): ?>
            <img src="<?php echo e(asset('storage/' . $product->images->first()->image)); ?>" class="w-full h-48 object-cover group-hover:opacity-90 transition" alt="<?php echo e($product->name); ?>">
            <?php else: ?>
            <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
            <?php endif; ?>
            <div class="p-4">
                <h3 class="font-bold truncate group-hover:text-primary"><?php echo e($product->name); ?></h3>
                <p class="text-neutral-600 text-sm mb-2"><?php echo e($product->seller->name); ?></p>
                <div class="flex justify-between items-center">
                    <span class="font-bold">$<?php echo e(number_format($product->getDiscountedPrice(), 2)); ?></span>
                    <span class="text-primary group-hover:text-blue-800">
                        <i class="fas fa-arrow-right"></i>
                    </span>
                </div>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php echo e($products->links()); ?>

    <?php endif; ?>

    <?php if($relatedCategories->count() > 0): ?>
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Related Categories</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <?php $__currentLoopData = $relatedCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('categories.show', $related)); ?>" 
                class="bg-white p-6 rounded-md-lg shadow-sm hover:shadow-sm-lg transition text-center">
                <i class="fas fa-folder text-4xl text-primary mb-4"></i>
                <h3 class="font-bold"><?php echo e($related->name); ?></h3>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/categories/show.blade.php ENDPATH**/ ?>