<!-- Seller Products List Stub -->

<?php $__env->startSection('title', 'My Products'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <a href="<?php echo e(route('seller.dashboard')); ?>" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Dashboard</a>
            <h1 class="text-3xl font-bold">My Products</h1>
        </div>
        <a href="<?php echo e(route('seller.products.create')); ?>" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            <i class="fas fa-plus mr-2"></i> Add Product
        </a>
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left">Image</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Category</th>
                    <th class="px-6 py-3 text-center">Stock</th>
                    <th class="px-6 py-3 text-right">Price</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-3">
                        <?php if($product->images->first()): ?>
                        <img src="<?php echo e(asset('storage/' . $product->images->first()->image)); ?>" class="w-16 h-16 object-cover rounded" alt="<?php echo e($product->name); ?>">
                        <?php else: ?>
                        <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-3"><?php echo e($product->name); ?></td>
                    <td class="px-6 py-3"><?php echo e($product->category->name); ?></td>
                    <td class="px-6 py-3 text-center"><?php echo e($product->stock); ?></td>
                    <td class="px-6 py-3 text-right">$<?php echo e(number_format($product->price, 2)); ?></td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="<?php echo e(route('seller.products.edit', $product)); ?>" class="inline-block px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <form action="<?php echo e(route('seller.products.destroy', $product)); ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php echo e($products->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/products/index.blade.php ENDPATH**/ ?>