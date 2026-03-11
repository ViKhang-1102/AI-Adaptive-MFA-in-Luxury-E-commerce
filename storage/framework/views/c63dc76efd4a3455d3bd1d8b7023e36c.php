

<?php $__env->startSection('title', 'Categories'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Categories</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('categories.show', $category)); ?>" 
            class="bg-white p-6 rounded-md-lg shadow-sm hover:shadow-sm-lg transition text-center">
            <i class="fas fa-folder text-6xl text-primary mb-4"></i>
            <h3 class="font-bold text-lg"><?php echo e($category->name); ?></h3>
            <?php if($category->children->count() > 0): ?>
            <p class="text-sm text-neutral-600"><?php echo e($category->children->count()); ?> subcategories</p>
            <?php endif; ?>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/categories/index.blade.php ENDPATH**/ ?>