
<?php $__env->startSection('title', 'Order #' . $order->id); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="<?php echo e(route('seller.orders.index')); ?>" class="text-blue-600 hover:underline">&larr; Back to Orders</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Order Header -->
        <div class="p-6 border-b">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold mb-2">Order #<?php echo e($order->id); ?></h1>
                    <p class="text-gray-600"><?php echo e($order->created_at->format('M d, Y \a\t h:i A')); ?></p>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 rounded-full text-white text-sm font-bold <?php echo e($order->status === 'pending' ? 'bg-yellow-600' : 
                        ($order->status === 'confirmed' ? 'bg-blue-600' : 
                        ($order->status === 'shipped' ? 'bg-purple-600' : 
                        ($order->status === 'delivered' ? 'bg-green-600' : 'bg-red-600')))); ?>">
                        <?php echo e(ucfirst($order->status)); ?>

                    </span>
                    <?php if($order->delivered_at): ?>
                    <p class="text-xs text-white mt-1">Delivered: <?php echo e($order->delivered_at->format('M d, Y H:i')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b">
            <div>
                <h3 class="font-bold text-lg mb-2">Customer</h3>
                <p class="text-gray-700"><?php echo e($order->customer->name ?? 'Guest Customer'); ?></p>
                <p class="text-gray-600"><?php echo e($order->customer->email ?? 'N/A'); ?></p>
                <p class="text-gray-600"><?php echo e($order->customer->phone ?? 'N/A'); ?></p>
            </div>
            <div>
                <h3 class="font-bold text-lg mb-2">Delivery Address</h3>
                <p class="text-gray-700"><?php echo e($order->delivery_address ?? 'N/A'); ?></p>
                <p class="text-gray-600"><?php echo e($order->recipient_name ?? ''); ?></p>
                <p class="text-gray-600"><?php echo e($order->recipient_phone ?? ''); ?></p>
            </div>
        </div>

        <!-- Order Items -->
        <div class="p-6 border-b">
            <h3 class="font-bold text-lg mb-4">Order Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left">Image</th>
                            <th class="px-4 py-2 text-left">Product</th>
                            <th class="px-4 py-2 text-center">Quantity</th>
                            <th class="px-4 py-2 text-right">Price</th>
                            <th class="px-4 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <?php if($item->product && $item->product->images->first()): ?>
                                    <img src="<?php echo e(asset('storage/' . $item->product->images->first()->image)); ?>" alt="<?php echo e($item->product->name); ?>" class="w-12 h-12 object-cover rounded">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 font-semibold"><?php echo e($item->product->name ?? 'Product Removed'); ?></td>
                            <td class="px-4 py-2 text-center"><?php echo e($item->quantity); ?></td>
                            <td class="px-4 py-2 text-right">$<?php echo e(number_format($item->price / env('VND_PER_USD', 23000), 2)); ?></td>
                            <td class="px-4 py-2 text-right font-semibold">$<?php echo e(number_format(($item->quantity * $item->price) / env('VND_PER_USD', 23000), 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-gray-500">No items in this order</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="p-6 border-b bg-gray-50">
            <div class="space-y-2 text-right">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>$<?php echo e(number_format($order->total_amount / env('VND_PER_USD', 23000), 2)); ?></span>
                </div>
                <div class="flex justify-between font-bold text-lg">
                    <span>Total:</span>
                    <span>$<?php echo e(number_format($order->total_amount / env('VND_PER_USD', 23000), 2)); ?></span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="p-6 flex gap-3">
            <?php if($order->status === 'pending'): ?>
                <form method="POST" action="<?php echo e(route('seller.orders.confirm', $order)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        <i class="fas fa-check mr-2"></i> Confirm Order
                    </button>
                </form>
            <?php endif; ?>

            <?php if($order->status === 'confirmed'): ?>
                <form method="POST" action="<?php echo e(route('seller.orders.ship', $order)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition">
                        <i class="fas fa-truck mr-2"></i> Ship Order
                    </button>
                </form>
            <?php endif; ?>

            <?php if($order->status === 'shipped'): ?>
                <form method="POST" action="<?php echo e(route('seller.orders.deliver', $order)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        <i class="fas fa-check-double mr-2"></i> Mark as Delivered
                    </button>
                </form>
            <?php endif; ?>

            <?php if(in_array($order->status, ['pending', 'confirmed'])): ?>
                <form method="POST" action="<?php echo e(route('seller.orders.cancel', $order)); ?>" class="inline" onsubmit="return confirm('Are you sure?')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        <i class="fas fa-times mr-2"></i> Cancel Order
                    </button>
                </form>
            <?php endif; ?>

            <a href="<?php echo e(route('seller.orders.index')); ?>" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/orders/show.blade.php ENDPATH**/ ?>