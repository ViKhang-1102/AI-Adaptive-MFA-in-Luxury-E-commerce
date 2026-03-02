
<?php $__env->startSection('title', 'System Fees & Settings'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 font-semibold">
            ← Dashboard
        </a>
        <h1 class="text-3xl font-bold">System Fees & Settings</h1>
    </div>

    <?php if($errors->any()): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Platform Commission Configuration -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">Platform Commission Configuration</h2>
        <form method="POST" action="<?php echo e(route('admin.fees.commission.update')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label for="platform_commission" class="block text-sm font-medium text-gray-700 mb-2">
                    Admin Commission Percentage (%)
                </label>
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <input type="number" id="platform_commission" name="platform_commission" 
                               value="<?php echo e($platformFee?->fee_value ?? 10); ?>"
                               min="0" max="100" step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:blue-500"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Enter the percentage (0-100) that Admin receives from each order</p>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
                        Update Commission
                    </button>
                </div>
            </div>
            <div class="bg-blue-100 p-3 rounded text-sm text-blue-800">
                <strong>Current Split:</strong>
                <ul class="list-none mt-2">
                    <li>✓ Admin receives: <strong><?php echo e($platformFee?->fee_value ?? 10); ?>%</strong></li>
                    <li>✓ Seller receives: <strong><?php echo e(100 - ($platformFee?->fee_value ?? 10)); ?>%</strong></li>
                </ul>
            </div>
        </form>
    </div>

    <?php if($fees->isEmpty()): ?>
        <div class="bg-white p-6 rounded-lg shadow text-center text-gray-500">
            <p>No additional fees configured yet.</p>
            <a href="<?php echo e(route('admin.fees.create')); ?>" class="mt-4 inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">+ Add Fee</a>
        </div>
    <?php else: ?>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Other Fees</h2>
                <a href="<?php echo e(route('admin.fees.create')); ?>" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">+ Add Fee</a>
            </div>

            <div class="space-y-4">
                <?php $__currentLoopData = $fees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-lg"><?php echo e($fee->name); ?></h3>
                            <p class="text-gray-600"><?php echo e($fee->description); ?></p>
                            <div class="mt-2 text-sm">
                                <span class="font-bold text-xl text-blue-600">
                                    <?php if($fee->fee_type === 'percentage'): ?>
                                        <?php echo e($fee->fee_value); ?>%
                                    <?php else: ?>
                                        ₫<?php echo e(number_format($fee->fee_value, 0)); ?>

                                    <?php endif; ?>
                                </span>
                                <span class="text-gray-500 ml-2">(<?php echo e(ucfirst($fee->fee_type)); ?>)</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="<?php echo e(route('admin.fees.edit', $fee)); ?>" class="px-3 py-1 text-blue-600 hover:bg-blue-50 rounded">Edit</a>
                            <form method="POST" action="<?php echo e(route('admin.fees.destroy', $fee)); ?>" class="inline" onsubmit="return confirm('Are you sure?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <?php echo e($fees->links()); ?>

            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/admin/fees/index.blade.php ENDPATH**/ ?>