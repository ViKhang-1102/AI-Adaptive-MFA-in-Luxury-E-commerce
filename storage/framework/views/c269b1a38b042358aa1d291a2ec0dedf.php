

<?php $__env->startSection('title', 'Categories'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center text-sm font-medium text-neutral-500 hover:text-primary transition-colors group">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
            <span>Back to Home</span>
        </a>
    </div>
    <div class="flex items-center gap-4 mb-8">
        <h1 class="text-3xl font-bold">Categories</h1>
    </div>

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026 - Copy\resources\views/categories/index.blade.php ENDPATH**/ ?>