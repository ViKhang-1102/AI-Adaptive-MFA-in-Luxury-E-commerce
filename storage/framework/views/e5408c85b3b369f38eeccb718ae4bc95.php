

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
                            <th class="text-left pb-2">Image</th>
                            <th class="text-left pb-2">Product</th>
                            <th class="text-center pb-2">Quantity</th>
                            <th class="text-right pb-2">Price</th>
                            <th class="text-right pb-2">Total</th>
                            <th class="text-center pb-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3">
                                <?php if($item->product && $item->product->images->first()): ?>
                                    <img src="<?php echo e(asset('storage/' . $item->product->images->first()->image)); ?>" 
                                        class="w-16 h-16 object-cover rounded" alt="<?php echo e($item->product_name); ?>">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3"><?php echo e($item->product_name); ?></td>
                            <td class="text-center"><?php echo e($item->quantity); ?></td>
                            <td class="text-right">$<?php echo e(number_format($item->product_price / env('VND_PER_USD', 23000), 2)); ?></td>
                            <td class="text-right">$<?php echo e(number_format($item->subtotal / env('VND_PER_USD', 23000), 2)); ?></td>
                            <td class="text-center">
                                <?php if($item->product): ?>
                                    <a href="#" class="text-blue-600 hover:underline text-sm font-bold buy-again-btn" 
                                        data-product-id="<?php echo e($item->product_id); ?>" data-quantity="<?php echo e($item->quantity); ?>">
                                        Buy Again
                                    </a>
                                <?php endif; ?>
                            </td>
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
                    <span>$<?php echo e(number_format($order->subtotal / env('VND_PER_USD', 23000), 2)); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping:</span>
                    <span>$<?php echo e(number_format($order->shipping_fee / env('VND_PER_USD', 23000), 2)); ?></span>
                </div>
                <?php if($order->discount_amount > 0): ?>
                <div class="flex justify-between text-green-600">
                    <span>Discount:</span>
                    <span>-$<?php echo e(number_format($order->discount_amount / env('VND_PER_USD', 23000), 2)); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex justify-between text-xl font-bold mb-6">
                <span>Total:</span>
                <span>$<?php echo e(number_format($order->total_amount / env('VND_PER_USD', 23000), 2)); ?></span>
            </div>

            <?php if($order->status === 'pending' && $order->payment_method === 'online' && $order->payment_status === 'pending'): ?>
            <div class="mb-4">
                <a href="<?php echo e(route('paypal.create', $order)); ?>" class="block w-full text-center bg-green-600 text-white py-2 rounded hover:bg-green-700 font-bold">
                    Pay Now
                </a>
            </div>
            <?php endif; ?>

            <?php if($order->canBeCancelled()): ?>
            <form action="<?php echo e(route('orders.cancel', $order)); ?>" method="POST" onsubmit="return confirm('Cancel this order?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
                    Cancel Order
                </button>
            </form>
            <?php elseif($order->status === 'cancelled'): ?>
            <form action="<?php echo e(route('orders.destroy', $order)); ?>" method="POST" onsubmit="return confirm('Delete order permanently?')">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="w-full bg-gray-600 text-white py-2 rounded hover:bg-gray-700">
                    Delete Order
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.buy-again-btn').forEach(btn => {
    btn.addEventListener('click', function(event) {
        event.preventDefault();
        
        const productId = this.getAttribute('data-product-id');
        const quantity = this.getAttribute('data-quantity');
        
        // Create and submit a form to add to cart
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo e(route("cart.add")); ?>';
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="product_id" value="${productId}">
            <input type="hidden" name="quantity" value="${quantity}">
        `;
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/orders/show.blade.php ENDPATH**/ ?>