
<?php $__env->startSection('title', 'My Categories'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="<?php echo e(route('seller.dashboard')); ?>" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Back to Dashboard</a>
        <h1 class="text-3xl font-bold">Available Categories</h1>
    </div>

    <?php if($categories->isEmpty()): ?>
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            No categories available yet. Please contact admin to create categories.
        </div>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Category Name</th>
                        <th class="px-6 py-3 text-left">Your Products</th>
                        <th class="px-6 py-3 text-left">Created</th>
                        <th class="px-6 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3 font-semibold"><?php echo e($category->name); ?></td>
                        <td class="px-6 py-3"><?php echo e($category->products_count ?? 0); ?></td>
                        <td class="px-6 py-3"><?php echo e($category->created_at->format('M d, Y')); ?></td>
                        <td class="px-6 py-3">
                            <a href="<?php echo e(route('seller.categories.show', $category)); ?>" class="text-blue-600 hover:underline text-sm">View Products</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php echo e($categories->links()); ?>

        </div>

        <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-blue-800">
                <strong>Note:</strong> Categories are managed by the admin. 
                To use more categories for your products, please contact the admin to create new ones.
            </p>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/categories/index.blade.php ENDPATH**/ ?>