

<?php $__env->startSection('title', 'My Messages Inbox'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Message Inbox</h1>

    <?php if(count($conversations) === 0): ?>
    <div class="bg-white p-6 rounded-md-lg shadow-sm text-center">
        <p class="text-neutral-600">You have no conversations yet.</p>
        <a href="<?php echo e(route('products.index')); ?>" class="bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 px-6 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5 mt-4 inline-block">
            Start Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('customer.messages.conversation', ['product' => $conv['product']->id, 'other' => $conv['seller']->id])); ?>"
           class="block bg-white p-4 rounded-md-lg shadow-sm hover:bg-neutral-50 flex justify-between items-center">
            <div>
                <div class="font-semibold"><?php echo e($conv['product']->name); ?></div>
                <div class="text-sm text-neutral-600">Seller: <?php echo e($conv['seller']->name); ?></div>
                <div class="text-sm text-neutral-500 truncate" style="max-width:500px;"><?php echo e($conv['last_message']); ?></div>
            </div>
            <?php if($conv['unread_count'] > 0): ?>
            <span class="bg-red-600 text-white rounded-md-full px-2 py-1 text-xs"><?php echo e($conv['unread_count']); ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/customer/messages/index.blade.php ENDPATH**/ ?>