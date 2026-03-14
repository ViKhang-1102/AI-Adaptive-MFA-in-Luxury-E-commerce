

<?php $__env->startSection('title', 'My Orders'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-10">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-neutral-200 text-neutral-500 hover:text-primary hover:border-primary transition-all group shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5 group-hover:-translate-x-1 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-3xl font-serif font-bold text-primary tracking-tight">Order History</h1>
                <p class="text-neutral-500 text-sm mt-1 uppercase tracking-widest font-bold">Track Your Luxury Acquisitions</p>
            </div>
        </div>
        <div class="hidden md:flex items-center gap-2 text-neutral-400">
            <i data-lucide="package-check" class="w-4 h-4 text-gold"></i>
            <span class="text-[10px] font-bold uppercase tracking-widest"><?php echo e($orders->count()); ?> Total Transactions</span>
        </div>
    </div>

    <?php if(session('order_notifications')): ?>
    <div class="mb-8 space-y-3">
        <?php $__currentLoopData = session('order_notifications'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-2xl flex items-center gap-3 auto-hide-alert shadow-sm">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                <i data-lucide="info" class="w-4 h-4"></i>
            </div>
            <p class="text-sm text-blue-700 font-medium"><?php echo e($note['message']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php if($orders->isEmpty()): ?>
    <div class="bg-white rounded-3xl shadow-soft border border-neutral-100 p-20 text-center">
        <div class="w-24 h-24 rounded-full bg-neutral-50 flex items-center justify-center mx-auto mb-6 text-neutral-300">
            <i data-lucide="package-open" class="w-12 h-12"></i>
        </div>
        <h2 class="text-2xl font-serif font-bold text-primary mb-2">No orders found</h2>
        <p class="text-neutral-500 mb-8 max-w-xs mx-auto text-sm">Your luxury journey hasn't started yet. Explore our curated collections today.</p>
        <a href="<?php echo e(route('products.index')); ?>" class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3.5 rounded-xl font-bold hover:bg-primary-light transition-all shadow-soft group">
            Start Shopping
            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
        </a>
    </div>
    <?php else: ?>
    <div class="space-y-8">
        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-3xl shadow-soft border border-neutral-100 overflow-hidden group hover:border-gold/30 transition-all duration-500">
            <!-- Order Header Card -->
            <div class="bg-neutral-50/50 px-8 py-5 flex flex-wrap justify-between items-center border-b border-neutral-100 gap-6">
                <div class="flex items-center gap-10">
                    <div>
                        <p class="text-[10px] text-neutral-400 uppercase tracking-widest font-bold mb-1">Acquisition Date</p>
                        <p class="text-sm font-bold text-primary"><?php echo e($order->created_at->format('M d, Y')); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-neutral-400 uppercase tracking-widest font-bold mb-1">Total Valuation</p>
                        <p class="text-sm font-serif font-bold text-primary">$<?php echo e(number_format($order->total_amount, 2)); ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] text-neutral-400 uppercase tracking-widest font-bold mb-1">Payment</p>
                        <p class="text-[10px] font-bold uppercase tracking-tight <?php echo e($order->payment_status === 'completed' ? 'text-emerald-600' : 'text-amber-600'); ?>">
                            <?php echo e($order->payment_status); ?>

                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-[10px] text-neutral-400 uppercase tracking-widest font-bold mb-1">Order Identifier</p>
                        <p class="text-sm font-serif font-bold text-gold">#<?php echo e($order->order_number); ?></p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="flex flex-col lg:flex-row justify-between gap-10">
                    <!-- Items Showcase -->
                    <div class="flex-1 space-y-6">
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-5 group/item">
                            <div class="relative w-16 h-16 flex-shrink-0">
                                <?php if($item->product && $item->product->images->first()): ?>
                                    <img src="<?php echo e(asset('storage/' . $item->product->images->first()->image)); ?>" class="w-full h-full object-cover rounded-xl shadow-sm border border-neutral-100">
                                <?php else: ?>
                                    <div class="w-full h-full bg-neutral-50 rounded-xl flex items-center justify-center border border-neutral-100">
                                        <i data-lucide="image" class="w-6 h-6 text-neutral-200"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-bold text-primary group-hover/item:text-gold transition-colors"><?php echo e($item->product_name); ?></h3>
                                <div class="flex items-center gap-3 mt-1.5">
                                    <span class="text-[10px] text-neutral-400 font-bold uppercase tracking-tighter">Qty: <?php echo e($item->quantity); ?></span>
                                    <span class="w-1 h-1 rounded-full bg-neutral-200"></span>
                                    <span class="text-[10px] text-neutral-500 italic">Seller: <?php echo e($order->seller->name ?? 'LuxGuard Official'); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <!-- Status and Intelligence -->
                    <div class="lg:w-72 flex flex-col justify-between items-end gap-8 border-t lg:border-t-0 lg:border-l border-neutral-100 pt-8 lg:pt-0 pl-0 lg:pl-10">
                        <div class="w-full text-right">
                            <?php
                                $statusStyles = [
                                    'pending' => 'bg-neutral-100 text-neutral-600 border-neutral-200',
                                    'review' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'paid' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'shipped' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    'cancelled' => 'bg-red-50 text-red-700 border-red-100',
                                ];
                                $badgeClass = $statusStyles[$order->status] ?? 'bg-neutral-50 text-neutral-700 border-neutral-200';
                                $statusLabel = $order->status === 'review' ? 'Security Hold' : ucfirst($order->status);
                            ?>
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest border <?php echo e($badgeClass); ?>">
                                <?php echo e($statusLabel); ?>

                            </span>
                        </div>

                        <div class="w-full space-y-3">
                             <?php if($order->status === 'pending' && $order->payment_method === 'online' && in_array($order->payment_status, ['unpaid', 'pending'])): ?>
                            <a href="<?php echo e(route('paypal.create', $order)); ?>" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-sm">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                Secure Payment
                            </a>
                            <?php endif; ?>
                            
                            <a href="<?php echo e(route('orders.show', $order)); ?>" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-xs font-bold text-white bg-primary hover:bg-primary-light transition-all shadow-soft group">
                                View Details
                                <i data-lucide="external-link" class="w-4 h-4 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform"></i>
                            </a>

                            <?php if($order->status === 'review'): ?>
                            <a href="<?php echo e(route('support.contact', ['order_id' => $order->id])); ?>" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 hover:bg-amber-100 transition-all">
                                <i data-lucide="help-circle" class="w-4 h-4"></i>
                                Contact Support
                            </a>
                            <?php endif; ?>

                            <div class="grid grid-cols-2 gap-2">
                                <form action="<?php echo e(route('orders.buyAgain', $order)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="w-full py-2.5 rounded-xl text-[10px] font-bold text-primary border border-neutral-200 hover:bg-neutral-50 transition-all uppercase tracking-widest">
                                        Buy Again
                                    </button>
                                </form>

                                <?php if($order->canBeCancelled()): ?>
                                <form action="<?php echo e(route('orders.cancel', $order)); ?>" method="POST" onsubmit="return confirm('Cancel this order?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="w-full py-2.5 rounded-xl text-[10px] font-bold text-red-600 border border-red-100 hover:bg-red-50 transition-all uppercase tracking-widest">
                                        Cancel
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if($order->status === 'cancelled'): ?>
                                <form action="<?php echo e(route('orders.destroy', $order)); ?>" method="POST" onsubmit="return confirm('Delete order permanently?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="w-full py-2.5 rounded-xl text-[10px] font-bold text-neutral-400 border border-neutral-100 hover:bg-neutral-50 transition-all uppercase tracking-widest">
                                        Delete
                                    </button>
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

    <div class="mt-10">
        <?php echo e($orders->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/orders/index.blade.php ENDPATH**/ ?>