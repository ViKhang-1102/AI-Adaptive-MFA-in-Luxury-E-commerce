
<?php $__env->startSection('title', 'My Orders'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">My Orders</h1>
        <div class="flex gap-2">
            <select id="status-filter" class="px-4 py-2 border rounded">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <?php if($orders->isEmpty()): ?>
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            No orders found.
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Order ID</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left">Items</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold">#<?php echo e($order->id); ?></td>
                        <td class="px-6 py-3"><?php echo e($order->user->name); ?></td>
                        <td class="px-6 py-3"><?php echo e($order->items_count ?? count($order->orderItems)); ?></td>
                        <td class="px-6 py-3 text-right font-semibold">$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-full
                                <?php echo e($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                                <?php echo e($order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                <?php echo e($order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : ''); ?>

                                <?php echo e($order->status === 'delivered' ? 'bg-green-100 text-green-800' : ''); ?>

                                <?php echo e($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : ''); ?>

                            ">
                                <?php echo e(ucfirst($order->status)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3"><?php echo e($order->created_at->format('M d, Y')); ?></td>
                        <td class="px-6 py-3">
                            <a href="<?php echo e(route('seller.orders.show', $order)); ?>" class="text-blue-600 hover:underline text-sm">View</a>
                            <?php if($order->status === 'pending'): ?>
                                <form method="POST" action="<?php echo e(route('seller.orders.confirm', $order)); ?>" class="inline ml-2">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-green-600 hover:underline text-sm">Confirm</button>
                                </form>
                            <?php endif; ?>
                            <?php if($order->status === 'confirmed'): ?>
                                <form method="POST" action="<?php echo e(route('seller.orders.ship', $order)); ?>" class="inline ml-2">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-blue-600 hover:underline text-sm">Ship</button>
                                </form>
                            <?php endif; ?>
                            <?php if(in_array($order->status, ['pending', 'confirmed'])): ?>
                                <form method="POST" action="<?php echo e(route('seller.orders.cancel', $order)); ?>" class="inline ml-2" onsubmit="return confirm('Are you sure?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-red-600 hover:underline text-sm">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php echo e($orders->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/orders/index.blade.php ENDPATH**/ ?>