<?php $__env->startSection('title', 'My Messages Inbox'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-10">
        <div>
            <h1 class="text-4xl font-serif font-bold text-primary">Message Inbox</h1>
            <p class="text-neutral-500 mt-2">Manage your conversations with sellers and customers</p>
        </div>
        <div class="bg-primary/5 px-4 py-2 rounded-full border border-primary/10">
            <span class="text-primary font-bold text-sm"><?php echo e(count($conversations)); ?> Conversations</span>
        </div>
    </div>

    <?php if(count($conversations) === 0): ?>
    <div class="bg-white p-16 rounded-3xl shadow-soft border border-neutral-100 text-center">
        <div class="w-20 h-20 bg-neutral-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="message-square-off" class="w-10 h-10 text-neutral-300"></i>
        </div>
        <h2 class="text-2xl font-serif font-bold text-primary mb-3">No conversations yet</h2>
        <p class="text-neutral-500 max-w-md mx-auto mb-8">When you message a seller about a product, your conversation will appear here.</p>
        <a href="<?php echo e(route('products.index')); ?>" class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3 rounded-xl font-bold hover:bg-primary-light transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            Start Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="grid gap-4">
        <?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('customer.messages.conversation', ['product' => $conv['product']->id, 'other' => $conv['seller']->id])); ?>"
           class="group block bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 hover:border-gold/30 hover:shadow-hover transition-all relative overflow-hidden">
            
            <div class="flex items-center gap-6">
                <!-- Product Image or Placeholder -->
                <div class="relative shrink-0">
                    <?php if($conv['product']->images->first()): ?>
                        <img src="<?php echo e(asset('storage/' . $conv['product']->images->first()->image)); ?>" 
                             class="w-16 h-16 rounded-xl object-cover border border-neutral-100 shadow-sm group-hover:scale-105 transition-transform duration-500" 
                             alt="<?php echo e($conv['product']->name); ?>">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-neutral-50 rounded-xl flex items-center justify-center border border-neutral-100">
                            <i data-lucide="package" class="w-8 h-8 text-neutral-300"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($conv['unread_count'] > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-4 ring-white shadow-sm">
                            <?php echo e($conv['unread_count']); ?>

                        </span>
                    <?php endif; ?>
                </div>

                <!-- Conversation Details -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-bold text-primary text-lg truncate group-hover:text-gold transition-colors">
                            <?php echo e($conv['product']->name); ?>

                        </h3>
                        <span class="text-xs text-neutral-400 font-medium">
                            
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-neutral-400">Seller:</span>
                        <span class="text-xs font-bold text-primary/70"><?php echo e($conv['seller']->name); ?></span>
                    </div>

                    <p class="text-sm text-neutral-500 truncate pr-8 leading-relaxed italic">
                        "<?php echo e($conv['last_message']); ?>"
                    </p>
                </div>

                <!-- Arrow Icon -->
                <div class="shrink-0 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 -translate-x-4 transition-all duration-300">
                    <div class="w-10 h-10 rounded-full bg-gold/10 flex items-center justify-center text-gold">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </div>
                </div>
            </div>

            <!-- New Message Indicator Dot -->
            <?php if($conv['unread_count'] > 0): ?>
                <div class="absolute top-0 right-0 w-2 h-full bg-red-600"></div>
            <?php endif; ?>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/customer/messages/index.blade.php ENDPATH**/ ?>