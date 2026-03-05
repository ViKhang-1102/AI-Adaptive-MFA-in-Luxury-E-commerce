
<?php $__env->startSection('title', 'All Orders'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
                ← Dashboard
            </a>
            <h1 class="text-3xl font-bold">All Orders</h1>
        </div>
        <div class="flex gap-2">
            <select id="status-filter" class="px-4 py-2 border rounded-md">
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
        <div class="bg-white p-6 rounded-md-lg shadow-sm text-center text-neutral-500">
            No orders found.
        </div>
    <?php else: ?>
        <div class="bg-white rounded-md-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-neutral-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Order ID</th>
                        <th class="px-6 py-3 text-left">Customer</th>
                        <th class="px-6 py-3 text-left">Seller</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-left">Payment</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b hover:bg-neutral-50">
                        <td class="px-6 py-3 font-semibold">#<?php echo e($order->id); ?></td>
                        <td class="px-6 py-3">
                            <div class="text-sm">
                                <div class="font-medium"><?php echo e($order->user->name ?? 'Guest'); ?></div>
                                <div class="text-neutral-500"><?php echo e($order->user->email ?? 'N/A'); ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-3"><?php echo e($order->seller->name ?? 'Unknown'); ?></td>
                        <td class="px-6 py-3 text-right font-semibold">$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-md-full <?php echo e($order->payment_status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                <?php echo e(ucfirst($order->payment_status ?? 'pending')); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-md-full
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
                            <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="text-primary hover:underline text-sm">View</a>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>