
<?php $__env->startSection('title', 'Pending Verifications'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="text-center">
        <h1 class="text-3xl font-bold mb-4">Pending Verifications</h1>
        <p class="text-neutral-600 mb-6">No orders are currently awaiting manual review. Once an order requires verification, it will automatically appear here.</p>
        <a href="<?php echo e(route('admin.orders.index')); ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-md hover:bg-primary-light transition">
            <i data-lucide="list" class="w-4 h-4"></i>
            View All Orders
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026 - Copy (6)\resources\views/admin/orders/pending.blade.php ENDPATH**/ ?>