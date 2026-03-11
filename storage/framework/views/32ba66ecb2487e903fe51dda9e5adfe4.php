

<?php $__env->startSection('title', 'My Wishlist'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Wishlist</h1>

    <?php if($wishlists->isEmpty()): ?>
    <div class="bg-white p-8 rounded-md-lg shadow-sm text-center">
        <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
        <p class="text-neutral-600 text-lg mb-4">Your wishlist is empty</p>
        <a href="<?php echo e(route('products.index')); ?>" class="bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 px-6 py-2 rounded-md hover:bg-primary-light hover:-translate-y-0.5">
            Start Shopping
        </a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php $__currentLoopData = $wishlists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wishlist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-md-lg shadow-sm hover:shadow-sm-lg transition overflow-hidden">
            <?php if($wishlist->product->images->first()): ?>
            <img src="<?php echo e(asset('storage/' . $wishlist->product->images->first()->image)); ?>" class="w-full h-48 object-cover" alt="<?php echo e($wishlist->product->name); ?>">
            <?php else: ?>
            <img src="https://via.placeholder.com/300x200?text=No+Image" class="w-full h-48 object-cover" alt="No image">
            <?php endif; ?>
            <div class="p-4">
                <h3 class="font-bold truncate"><?php echo e($wishlist->product->name); ?></h3>
                <p class="text-neutral-600 text-sm mb-2"><?php echo e($wishlist->product->seller->name); ?></p>
                <div class="flex justify-between items-center mb-3">
                    <span class="font-bold">$<?php echo e(number_format($wishlist->product->getDiscountedPrice(), 2)); ?></span>
                </div>
                <div class="flex space-x-2">
                    <form action="<?php echo e(route('cart.add')); ?>" method="POST" class="flex-1">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" value="<?php echo e($wishlist->product->id); ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="w-full bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 py-2 rounded-md text-sm hover:bg-primary-light hover:-translate-y-0.5">
                            Add to Cart
                        </button>
                    </form>
                    <form action="<?php echo e(route('wishlist.remove', $wishlist->product->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php echo e($wishlists->links()); ?>

    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/products/wishlist.blade.php ENDPATH**/ ?>