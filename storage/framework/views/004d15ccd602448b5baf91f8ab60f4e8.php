
<?php $__env->startSection('title', 'Manage Customers'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
                ← Dashboard
            </a>
            <h1 class="text-3xl font-bold">Customers Management</h1>
        </div>
        <a href="<?php echo e(route('admin.customers.create')); ?>" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-semibold">
            <i class="fas fa-plus"></i> Add Customer
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-6 rounded-md-lg shadow-sm mb-6">
        <form method="GET" action="<?php echo e(route('admin.customers.index')); ?>" class="flex gap-4">
            <input type="text" name="search" placeholder="Search by name or email" class="flex-1 px-4 py-2 border rounded-md" value="<?php echo e(request('search')); ?>">
            <button type="submit" class="px-6 py-2 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md hover:bg-primary-light hover:-translate-y-0.5">Search</button>
        </form>
    </div>

    <?php if($customers->isEmpty()): ?>
        <div class="bg-white p-6 rounded-md-lg shadow-sm text-center text-neutral-500">
            No customers found.
        </div>
    <?php else: ?>
        <div class="bg-white rounded-md-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-neutral-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Name</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-left">Phone</th>
                        <th class="px-6 py-3 text-left">Joined</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b hover:bg-neutral-50">
                        <td class="px-6 py-3"><?php echo e($customer->name); ?></td>
                        <td class="px-6 py-3"><?php echo e($customer->email); ?></td>
                        <td class="px-6 py-3"><?php echo e($customer->phone ?? 'N/A'); ?></td>
                        <td class="px-6 py-3"><?php echo e($customer->created_at->format('M d, Y')); ?></td>
                        <td class="px-6 py-3 flex gap-2">
                            <a href="<?php echo e(route('admin.customers.edit', $customer)); ?>" class="px-3 py-1 bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 rounded-md hover:bg-primary-light hover:-translate-y-0.5 text-sm font-semibold">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" action="<?php echo e(route('admin.customers.destroy', $customer)); ?>" class="inline" onsubmit="return confirm('Delete this customer permanently?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-semibold">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php echo e($customers->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/admin/customers/index.blade.php ENDPATH**/ ?>