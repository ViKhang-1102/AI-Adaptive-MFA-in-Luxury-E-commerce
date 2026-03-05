
<?php $__env->startSection('title', 'My Orders'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <a href="<?php echo e(route('seller.dashboard')); ?>" class="text-primary hover:underline mb-4 inline-block">&larr; Back to Dashboard</a>
            <h1 class="text-3xl font-bold">My Orders</h1>
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
                        <th class="px-6 py-3 text-left">Items</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b hover:bg-neutral-50">
                        <td class="px-6 py-3 font-semibold">#<?php echo e($order->id); ?></td>
                        <td class="px-6 py-3"><?php echo e($order->customer->name ?? 'N/A'); ?></td>
                        <td class="px-6 py-3"><?php echo e($order->items->count() ?? 0); ?></td>
                        <td class="px-6 py-3 text-right font-semibold">$<?php echo e(number_format($order->total_amount, 2)); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-md-full
                                <?php echo e($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                                <?php echo e($order->status === 'processing' ? 'bg-orange-100 text-orange-800' : ''); ?>

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
                            <div class="flex gap-2 flex-wrap">
                                <a href="<?php echo e(route('seller.orders.show', $order)); ?>" class="inline-block px-3 py-1 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 text-sm rounded-md hover:bg-primary-light hover:-translate-y-0.5 transition">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <?php if($order->status === 'pending'): ?>
                                    <form method="POST" action="<?php echo e(route('seller.orders.confirm', $order)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition">
                                            <i class="fas fa-check mr-1"></i> Confirm
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if($order->status === 'confirmed'): ?>
                                    <form method="POST" action="<?php echo e(route('seller.orders.ship', $order)); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="px-3 py-1 bg-purple-600 text-white text-sm rounded-md hover:bg-purple-700 transition">
                                            <i class="fas fa-truck mr-1"></i> Ship
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if(in_array($order->status, ['pending', 'confirmed'])): ?>
                                    <form method="POST" action="<?php echo e(route('seller.orders.cancel', $order)); ?>" class="inline" onsubmit="
                                        event.preventDefault(); 
                                        let reason = prompt('Please enter a reason for cancelling this order:'); 
                                        if(reason) { 
                                            let input = document.createElement('input'); 
                                            input.type = 'hidden'; 
                                            input.name = 'reason'; 
                                            input.value = reason; 
                                            this.appendChild(input); 
                                            this.submit(); 
                                        }
                                    ">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 transition">
                                            <i class="fas fa-times mr-1"></i> Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if($order->status === 'cancelled'): ?>
                                    <form method="POST" action="<?php echo e(route('seller.orders.destroy', $order)); ?>" class="inline" onsubmit="return confirm('Remove order permanently?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="px-3 py-1 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 transition">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
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