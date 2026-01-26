
<?php $__env->startSection('title', $category->name . ' - My Products'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="<?php echo e(route('seller.categories.index')); ?>" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Categories</a>
        <h1 class="text-3xl font-bold"><?php echo e($category->name); ?></h1>
    </div>

    <?php if($products->isEmpty()): ?>
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            <p>You don't have any products in this category yet.</p>
            <a href="<?php echo e(route('seller.products.create')); ?>" class="text-blue-600 hover:underline">Create a product</a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Product Name</th>
                        <th class="px-6 py-3 text-center">Stock</th>
                        <th class="px-6 py-3 text-right">Price</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3"><?php echo e($product->name); ?></td>
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

        <!-- Pagination -->
        <div class="mt-6">
            <?php echo e($products->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/categories/show.blade.php ENDPATH**/ ?>