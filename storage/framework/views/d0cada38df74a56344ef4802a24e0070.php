

<?php $__env->startSection('title', 'My Orders'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Orders</h1>

    <?php if($orders->isEmpty()): ?>
    <div class="bg-white p-8 rounded-lg shadow text-center">
        <p class="text-gray-600 text-lg mb-4">You have no orders yet</p>
        <a href="<?php echo e(route('products.index')); ?>" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Start Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Order ID</p>
                    <strong><?php echo e($order->order_number); ?></strong>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Date</p>
                    <strong><?php echo e($order->created_at->format('M d, Y')); ?></strong>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <strong>₫<?php echo e(number_format($order->total_amount, 0)); ?></strong>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <span class="px-3 py-1 rounded text-sm font-bold
                        <?php if($order->status === 'pending'): ?> bg-yellow-100 text-yellow-800
                        <?php elseif($order->status === 'confirmed'): ?> bg-blue-100 text-blue-800
                        <?php elseif($order->status === 'shipped'): ?> bg-purple-100 text-purple-800
                        <?php elseif($order->status === 'delivered'): ?> bg-green-100 text-green-800
                        <?php elseif($order->status === 'cancelled'): ?> bg-red-100 text-red-800
                        <?php endif; ?>">
                        <?php echo e(ucfirst($order->status)); ?>

                    </span>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <?php if($order->status === 'pending' && $order->payment_method === 'online' && $order->payment_status === 'pending'): ?>
                <a href="<?php echo e(route('paypal.create', $order)); ?>" class="text-green-600 hover:underline font-bold">
                    Pay Now
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('orders.show', $order)); ?>" class="text-blue-600 hover:underline">
                    View Details
                </a>
                <?php if($order->canBeCancelled()): ?>
                <form action="<?php echo e(route('orders.cancel', $order)); ?>" method="POST" onsubmit="return confirm('Cancel this order?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-red-600 hover:underline">Cancel Order</button>
                </form>
                <?php endif; ?>
                <?php if($order->status === 'cancelled'): ?>
                <form action="<?php echo e(route('orders.destroy', $order)); ?>" method="POST" onsubmit="return confirm('Delete order permanently?')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="text-gray-600 hover:underline">Delete</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php echo e($orders->links()); ?>

    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/orders/index.blade.php ENDPATH**/ ?>