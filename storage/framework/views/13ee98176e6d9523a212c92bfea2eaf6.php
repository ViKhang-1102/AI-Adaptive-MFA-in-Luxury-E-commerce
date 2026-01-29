
<?php $__env->startSection('title', 'My Wallet'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="<?php echo e(route('seller.dashboard')); ?>" class="text-blue-600 hover:underline">&larr; Back to Dashboard</a>
    </div>

    <h1 class="text-3xl font-bold mb-8">My Wallet</h1>

    <!-- Wallet Balance -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow-lg">
            <p class="text-blue-100 text-sm mb-1">Available Balance</p>
            <h2 class="text-4xl font-bold">$<?php echo e(number_format($balance, 2)); ?></h2>
            <p class="text-blue-100 text-xs mt-2">Ready to withdraw</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-600 text-sm mb-1">Total Earned</p>
            <h2 class="text-3xl font-bold text-green-600">$<?php echo e(number_format($totalEarned, 2)); ?></h2>
            <p class="text-gray-500 text-xs mt-2">All time earnings</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-600 text-sm mb-1">Total Withdrawn</p>
            <h2 class="text-3xl font-bold text-purple-600">$<?php echo e(number_format($totalWithdrawn, 2)); ?></h2>
            <p class="text-gray-500 text-xs mt-2">Funds transferred</p>
        </div>
    </div>

    <!-- Withdraw Request -->
    <?php if($balance > 0): ?>
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h3 class="font-bold text-lg mb-4">Request Withdrawal</h3>
        <form action="<?php echo e(route('seller.wallet.withdraw')); ?>" method="POST" class="max-w-md">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Withdrawal Amount</label>
                <div class="flex gap-2">
                    <input type="number" name="amount" step="0.01" max="<?php echo e($balance); ?>" placeholder="Enter amount" class="flex-1 px-4 py-2 border rounded" required>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">Request</button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Maximum: $<?php echo e(number_format($balance, 2)); ?></p>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Transaction History -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="font-bold text-lg">Transaction History</h3>
        </div>

        <?php if($transactions->isEmpty()): ?>
            <div class="p-6 text-center text-gray-500">
                No transactions yet.
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Type</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3"><?php echo e($transaction->created_at->format('M d, Y H:i')); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded <?php echo e($transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo e(ucfirst($transaction->type)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3 text-right font-semibold">
                            <span class="<?php echo e($transaction->type === 'credit' ? 'text-green-600' : 'text-red-600'); ?>">
                                <?php echo e($transaction->type === 'credit' ? '+' : '-'); ?>$<?php echo e(number_format($transaction->amount, 2)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-full 
                                <?php echo e($transaction->status === 'completed' ? 'bg-green-100 text-green-800' : ''); ?>

                                <?php echo e($transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                                <?php echo e($transaction->status === 'failed' ? 'bg-red-100 text-red-800' : ''); ?>

                            ">
                                <?php echo e(ucfirst($transaction->status)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600"><?php echo e($transaction->description); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t">
                <?php echo e($transactions->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/wallet/index.blade.php ENDPATH**/ ?>