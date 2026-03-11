

<?php $__env->startSection('title', 'My Orders'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Orders</h1>

    <?php if(session('order_notifications')): ?>
    <div class="mb-6">
        <?php $__currentLoopData = session('order_notifications'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-2 auto-hide-alert rounded-md shadow-sm">
            <p class="text-blue-700"><?php echo e($note['message']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php if($orders->isEmpty()): ?>
    <div class="bg-white p-8 rounded-md-lg shadow-sm text-center">
        <p class="text-neutral-600 text-lg mb-4">You have no orders yet</p>
        <a href="<?php echo e(route('products.index')); ?>" class="bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 px-6 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5">
            Start Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-6">
        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-2xl shadow-soft border border-neutral-100 overflow-hidden transform transition-all duration-300 hover:shadow-hover hover:-translate-y-1">
            <div class="bg-neutral-50 px-6 py-4 flex flex-wrap justify-between items-center border-b border-neutral-100 gap-4">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-xs text-neutral-500 uppercase tracking-wider font-bold mb-1">Order Placed</p>
                        <p class="font-medium text-neutral-800"><?php echo e($order->created_at->format('M d, Y')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-neutral-500 uppercase tracking-wider font-bold mb-1">Total</p>
                        <p class="font-medium text-neutral-800">$<?php echo e(number_format($order->total_amount, 2)); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xs text-neutral-500 uppercase tracking-wider font-bold mb-1">Order #</p>
                        <p class="font-serif font-bold text-primary"><?php echo e($order->order_number); ?></p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex flex-col md:flex-row justify-between gap-6">
                    <!-- Left: Items -->
                    <div class="flex-1 space-y-4">
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-4">
                            <?php if($item->product && $item->product->images->first()): ?>
                                <img src="<?php echo e(asset('storage/' . $item->product->images->first()->image)); ?>" class="w-16 h-16 object-cover rounded-lg border border-neutral-200">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-neutral-100 rounded-lg flex items-center justify-center">
                                    <i data-lucide="image" class="w-6 h-6 text-neutral-300"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <h3 class="font-bold text-primary"><?php echo e($item->product_name); ?></h3>
                                <p class="text-sm text-neutral-500">Seller: <?php echo e($order->seller->name ?? 'Unknown'); ?> | Qty: <?php echo e($item->quantity); ?></p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <!-- Right: Status and Actions -->
                    <div class="md:w-64 flex flex-col justify-between items-end gap-6 border-t md:border-t-0 md:border-l border-neutral-100 pt-4 md:pt-0 pl-0 md:pl-6">
                        <div class="w-full">
                            <?php
                                $statusStyles = [
                                    'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'review' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'paid' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'shipped' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'delivered' => 'bg-green-50 text-green-700 border-green-200',
                                    'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                ];
                                $badgeClass = $statusStyles[$order->status] ?? 'bg-neutral-50 text-neutral-700 border-neutral-200';
                                $statusLabel = $order->status === 'review' ? 'On Hold - Security Review' : ucfirst($order->status);
                            ?>
                            <div class="text-right">
                                <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full border <?php echo e($badgeClass); ?>">
                                    <?php echo e($statusLabel); ?>

                                </span>
                            </div>
                        </div>

                        <div class="w-full space-y-2">
                             <?php if($order->status === 'pending' && $order->payment_method === 'online' && in_array($order->payment_status, ['unpaid', 'pending'])): ?>
                            <a href="<?php echo e(route('paypal.create', $order)); ?>" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg text-sm font-bold text-white bg-green-600 hover:bg-green-700 transition-colors shadow-sm text-center">
                                Pay Now
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('orders.show', $order)); ?>" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary-light transition-colors shadow-sm text-center">
                                View Order Details
                            </a>

                            <?php if($order->status === 'review'): ?>
                                <a href="<?php echo e(route('support.contact', ['order_id' => $order->id])); ?>" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 transition-colors shadow-sm text-center">
                                    View Status
                                </a>
                            <?php endif; ?>

                            <div class="flex gap-2">
                                <form action="<?php echo e(route('orders.buyAgain', $order)); ?>" method="POST" class="flex-1 border rounded-lg hover:bg-neutral-50 transition-colors text-center">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="w-full py-2 text-sm font-bold text-primary">Buy Again</button>
                                </form>

                                <?php if($order->canBeCancelled()): ?>
                                <form action="<?php echo e(route('orders.cancel', $order)); ?>" method="POST" class="flex-1 border border-red-200 rounded-lg hover:bg-red-50 transition-colors text-center" onsubmit="return confirm('Cancel this order?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="w-full py-2 text-sm font-bold text-red-600">Cancel</button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if($order->status === 'cancelled'): ?>
                                <form action="<?php echo e(route('orders.destroy', $order)); ?>" method="POST" class="flex-1 border border-neutral-200 rounded-lg hover:bg-neutral-50 transition-colors text-center" onsubmit="return confirm('Delete order permanently?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="w-full py-2 text-sm font-bold text-neutral-600">Delete</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php echo e($orders->links()); ?>

    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/orders/index.blade.php ENDPATH**/ ?>