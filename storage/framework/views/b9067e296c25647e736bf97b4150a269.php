
<?php $__env->startSection('title', 'Manage Order #' . $order->order_number); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="<?php echo e(route('seller.orders.index')); ?>" class="inline-flex items-center text-sm font-medium text-neutral-500 hover:text-primary transition-colors group mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Orders
            </a>
            <div class="flex items-center gap-4">
                <h1 class="text-3xl font-serif font-bold text-primary">Order #<?php echo e($order->id); ?></h1>
                <?php
                    $statusStyles = [
                        'pending' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'shipped' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'delivered' => 'bg-green-50 text-green-700 border-green-200',
                        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
                    ];
                    $badgeClass = $statusStyles[$order->status] ?? 'bg-neutral-50 text-neutral-700 border-neutral-200';
                ?>
                <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full border <?php echo e($badgeClass); ?>">
                    <?php echo e($order->status); ?>

                </span>
            </div>
            <p class="text-neutral-500 mt-2">Placed on <?php echo e($order->created_at->format('F d, Y \a\t h:i A')); ?></p>
        </div>
        
        <div>
             <?php if(in_array($order->status, ['pending', 'processing'])): ?>
                <form method="POST" action="<?php echo e(route('seller.orders.confirm', $order)); ?>" class="inline-block">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-light transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                        <i data-lucide="check-circle" class="w-5 h-5"></i> Confirm Order
                    </button>
                </form>
            <?php endif; ?>

            <?php if($order->status === 'confirmed'): ?>
                <form method="POST" action="<?php echo e(route('seller.orders.ship', $order)); ?>" class="inline-block">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-light transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                        <i data-lucide="truck" class="w-5 h-5"></i> Ship Order
                    </button>
                </form>
            <?php endif; ?>

            <?php if($order->status === 'shipped'): ?>
                <form method="POST" action="<?php echo e(route('seller.orders.deliver', $order)); ?>" class="inline-block">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                        <i data-lucide="package-check" class="w-5 h-5"></i> Mark Delivered
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Timeline -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100 overflow-hidden">
                <h2 class="text-xl font-serif font-bold text-primary mb-6">Order Status</h2>
                
                <div class="relative py-4">
                    <!-- Progress Bar Background (hidden on very small screens, shown vertically otherwise) -->
                    <div class="absolute left-8 sm:left-1/2 top-4 bottom-4 w-px bg-neutral-100 sm:-translate-x-1/2"></div>
                    
                    <?php
                        $stages = [
                            'pending' => ['icon' => 'clock', 'label' => 'Order Placed', 'desc' => 'Awaiting your confirmation'],
                            'processing' => ['icon' => 'credit-card', 'label' => 'Payment Processed', 'desc' => 'Payment has been cleared'],
                            'confirmed' => ['icon' => 'check-circle', 'label' => 'Confirmed', 'desc' => 'Order accepted by you'],
                            'shipped' => ['icon' => 'truck', 'label' => 'Shipped', 'desc' => 'Item is out for delivery'],
                            'delivered' => ['icon' => 'package-check', 'label' => 'Delivered', 'desc' => 'Order fulfilled successfully'],
                        ];
                        
                        // If it's a COD order, we don't show the processing stage in the timeline.
                        if ($order->payment_method === 'cod') {
                            unset($stages['processing']);
                        }
                        
                        $statusKeys = array_keys($stages);
                        $currentIndex = array_search($order->status, $statusKeys);
                        if ($currentIndex === false) $currentIndex = -1;
                        if ($order->status === 'cancelled') $currentIndex = -1;
                    ?>
                    
                    <div class="space-y-8 relative">
                        <?php if($order->status === 'cancelled'): ?>
                             <div class="flex flex-col sm:flex-row items-start sm:items-center relative z-10 w-full pl-20 sm:pl-0">
                                <div class="absolute left-0 sm:left-1/2 w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center border-4 border-white shadow-soft sm:-translate-x-1/2">
                                    <i data-lucide="x-circle" class="w-6 h-6"></i>
                                </div>
                                <div class="sm:w-1/2 sm:pr-16 sm:text-right w-full min-h-[4rem] flex flex-col justify-center">
                                    <h4 class="font-bold text-red-600 sm:text-right text-left">Order Cancelled</h4>
                                </div>
                                <div class="sm:w-1/2 sm:pl-16 w-full hidden sm:block"></div>
                            </div>
                        <?php else: ?>
                            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $isCompleted = array_search($key, $statusKeys) <= $currentIndex;
                                    $isCurrent = $key === $order->status;
                                    $iconBg = $isCompleted ? 'bg-primary text-gold' : 'bg-neutral-50 text-neutral-400';
                                    if ($isCurrent) $iconBg = 'bg-gold text-primary';
                                    $iconBorder = $isCurrent ? 'ring-4 ring-gold/20' : '';
                                    $textColor = $isCompleted ? 'text-primary' : 'text-neutral-400';
                                ?>
                                <div class="flex flex-col sm:flex-row items-start sm:items-center relative z-10 w-full pl-20 sm:pl-0">
                                    <div class="absolute left-0 sm:left-1/2 top-0 sm:top-1/2 sm:-translate-y-1/2 w-16 h-16 <?php echo e($iconBg); ?> <?php echo e($iconBorder); ?> rounded-full flex items-center justify-center border-4 border-white shadow-soft transition-all sm:-translate-x-1/2">
                                        <i data-lucide="<?php echo e($stage['icon']); ?>" class="w-6 h-6"></i>
                                    </div>
                                    
                                    <div class="sm:w-1/2 sm:pr-16 sm:text-right w-full min-h-[4rem] flex flex-col justify-center">
                                        <h4 class="font-bold <?php echo e($textColor); ?> sm:text-right text-left"><?php echo e($stage['label']); ?></h4>
                                        <p class="text-sm text-neutral-500 sm:text-right text-left"><?php echo e($stage['desc']); ?></p>
                                    </div>
                                    <div class="sm:w-1/2 sm:pl-16 w-full hidden sm:block">
                                        <?php if($isCompleted && $key === 'delivered' && $order->delivered_at): ?>
                                            <p class="text-sm font-medium text-neutral-500"><?php echo e($order->delivered_at->format('M d, Y')); ?></p>
                                            <p class="text-xs text-neutral-400"><?php echo e($order->delivered_at->format('H:i')); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white rounded-2xl shadow-soft border border-neutral-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-neutral-100 bg-neutral-50/50">
                    <h2 class="text-xl font-serif font-bold text-primary">Order Items</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-4 flex-1">
                                <?php if($item->product && $item->product->images->first()): ?>
                                    <img src="<?php echo e(asset('storage/' . $item->product->images->first()->image)); ?>" 
                                        class="w-20 h-20 object-cover rounded-xl border border-neutral-100 shadow-sm group-hover:border-gold/50 transition-colors" alt="<?php echo e($item->product->name ?? 'Product'); ?>">
                                <?php else: ?>
                                    <div class="w-20 h-20 bg-neutral-50 rounded-xl border border-neutral-100 flex items-center justify-center group-hover:border-gold/50 transition-colors">
                                        <i data-lucide="image" class="w-6 h-6 text-neutral-300"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3 class="font-bold text-primary mb-1"><?php echo e($item->product->name ?? 'Product Removed'); ?></h3>
                                    <p class="text-sm text-neutral-500">Qty: <?php echo e($item->quantity); ?> x $<?php echo e(number_format($item->price, 2)); ?></p>
                                </div>
                            </div>
                            
                            <div class="text-right flex flex-col items-end gap-2">
                                <span class="font-bold text-lg text-primary">$<?php echo e(number_format($item->price * $item->quantity, 2)); ?></span>
                            </div>
                        </div>
                        <?php if(!$loop->last): ?>
                            <hr class="border-neutral-100">
                        <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="space-y-6">
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100">
                <h3 class="text-xl font-serif font-bold text-primary mb-6">Order Summary</h3>

                <div class="space-y-4 mb-6">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-neutral-500">Subtotal</span>
                        <span class="font-medium text-neutral-700">$<?php echo e(number_format($order->total_amount, 2)); ?></span>
                    </div>
                </div>

                <div class="pt-6 border-t border-neutral-100 mb-8">
                    <div class="flex justify-between items-center">
                        <span class="text-lg text-primary font-bold">Total earnings</span>
                        <span class="text-2xl font-bold text-gold">$<?php echo e(number_format($order->total_amount, 2)); ?></span>
                    </div>
                </div>

                <!-- Actions -->
                <?php if(in_array($order->status, ['pending', 'processing', 'confirmed'])): ?>
                <div class="space-y-3">
                    <form method="POST" action="<?php echo e(route('seller.orders.cancel', $order)); ?>" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                        <?php echo csrf_field(); ?>
                        <input type="text" name="reason" placeholder="Reason for cancellation..." required class="w-full px-4 py-3 mb-3 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all shadow-sm text-sm">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 bg-white border-2 border-red-100 text-red-600 font-bold rounded-xl hover:bg-red-50 hover:border-red-200 transition-colors">
                            <i data-lucide="x-circle" class="w-4 h-4"></i> Cancel Order
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- Customer Info -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100">
                <h3 class="text-lg font-serif font-bold text-primary mb-6 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-gold"></i> Customer Details
                </h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-primary-light/5 text-primary rounded-full flex items-center justify-center">
                        <span class="font-serif font-bold text-lg"><?php echo e(substr($order->customer->name ?? 'G', 0, 1)); ?></span>
                    </div>
                    <div>
                        <p class="font-bold text-primary"><?php echo e($order->customer->name ?? 'Guest Customer'); ?></p>
                        <a href="<?php echo e(route('seller.messages.index')); ?>" class="text-xs text-gold hover:text-gold-dark font-medium transition-colors">Message Customer</a>
                    </div>
                </div>
                <div class="space-y-2 text-sm text-neutral-600">
                    <p class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-neutral-400"></i> <?php echo e($order->customer->email ?? 'N/A'); ?></p>
                    <p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-neutral-400"></i> <?php echo e($order->customer->phone ?? 'N/A'); ?></p>
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-neutral-100">
                <h3 class="text-lg font-serif font-bold text-primary mb-6 flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-gold"></i> Delivery Address
                </h3>
                <div class="bg-neutral-50 p-4 rounded-xl border border-neutral-100">
                    <p class="font-bold text-primary mb-1"><?php echo e($order->recipient_name ?? ''); ?></p>
                    <p class="text-sm flex items-center gap-2 text-neutral-600 mb-3 block">
                        <i data-lucide="phone" class="w-3 h-3"></i> <?php echo e($order->recipient_phone ?? ''); ?>

                    </p>
                    <p class="text-sm text-neutral-500 leading-relaxed"><?php echo e($order->delivery_address ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/orders/show.blade.php ENDPATH**/ ?>