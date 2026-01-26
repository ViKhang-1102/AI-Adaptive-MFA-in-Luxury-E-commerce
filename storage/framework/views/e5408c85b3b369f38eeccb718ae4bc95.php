

<?php $__env->startSection('title', 'Order Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Header -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h1 class="text-2xl font-bold mb-4"><?php echo e($order->order_number); ?></h1>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Order Date</p>
                        <strong><?php echo e($order->created_at->format('M d, Y')); ?></strong>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <span class="px-2 py-1 rounded text-sm font-bold
                            <?php if($order->status === 'pending'): ?> bg-yellow-100 text-yellow-800
                            <?php elseif($order->status === 'confirmed'): ?> bg-blue-100 text-blue-800
                            <?php elseif($order->status === 'shipped'): ?> bg-purple-100 text-purple-800
                            <?php elseif($order->status === 'delivered'): ?> bg-green-100 text-green-800
                            <?php elseif($order->status === 'cancelled'): ?> bg-red-100 text-red-800
                            <?php endif; ?>">
                            <?php echo e(ucfirst($order->status)); ?>

                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Payment</p>
                        <strong><?php echo e(ucfirst($order->payment_method)); ?></strong>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Seller</p>
                        <strong><?php echo e($order->seller->name); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Items</h2>
                <table class="w-full">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left pb-2">Product</th>
                            <th class="text-center pb-2">Quantity</th>
                            <th class="text-right pb-2">Price</th>
                            <th class="text-right pb-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b">
                            <td class="py-3"><?php echo e($item->product_name); ?></td>
                            <td class="text-center"><?php echo e($item->quantity); ?></td>
                            <td class="text-right">$<?php echo e(number_format($item->product_price, 2)); ?></td>
                            <td class="text-right">$<?php echo e(number_format($item->subtotal, 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Delivery Information -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Delivery Address</h2>
                <div>
                    <p class="font-bold"><?php echo e($order->recipient_name); ?></p>
                    <p><?php echo e($order->recipient_phone); ?></p>
                    <p class="text-gray-600"><?php echo e($order->delivery_address); ?></p>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="bg-white p-6 rounded-lg shadow h-fit">
            <h3 class="text-xl font-bold mb-4">Order Summary</h3>

            <div class="space-y-3 border-b pb-4 mb-4">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>$<?php echo e(number_format($order->subtotal, 2)); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping:</span>
                    <span>$<?php echo e(number_format($order->shipping_fee, 2)); ?></span>
                </div>
                <?php if($order->discount_amount > 0): ?>
                <div class="flex justify-between text-green-600">
                    <span>Discount:</span>
                    <span>-$<?php echo e(number_format($order->discount_amount, 2)); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex justify-between text-xl font-bold mb-6">
                <span>Total:</span>
                <span>$<?php echo e(number_format($order->total_amount, 2)); ?></span>
            </div>

            <?php if($order->canBeCancelled()): ?>
            <form action="<?php echo e(route('orders.cancel', $order)); ?>" method="POST" onsubmit="return confirm('Cancel this order?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
                    Cancel Order
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/orders/show.blade.php ENDPATH**/ ?>