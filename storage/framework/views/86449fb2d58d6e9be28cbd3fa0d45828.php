
<?php $__env->startSection('title', 'Platform Wallet'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 font-semibold">
            ← Dashboard
        </a>
        <h1 class="text-3xl font-bold">Platform Wallet Management</h1>
    </div>

    <!-- Platform Wallet Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-600 text-sm mb-1">Total Platform Balance</p>
            <h2 class="text-3xl font-bold text-blue-600">₫<?php echo e(number_format($totalBalance, 0)); ?></h2>
            <p class="text-gray-500 text-xs mt-2">Current funds in platform</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-600 text-sm mb-1">Total Seller Wallets</p>
            <h2 class="text-3xl font-bold text-green-600">₫<?php echo e(number_format($totalSellerWallets, 0)); ?></h2>
            <p class="text-gray-500 text-xs mt-2">Owed to sellers</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-600 text-sm mb-1">Total Transactions</p>
            <h2 class="text-3xl font-bold text-purple-600"><?php echo e($totalTransactions); ?></h2>
            <p class="text-gray-500 text-xs mt-2">All time transactions</p>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="font-bold text-lg">Recent Transactions</h3>
        </div>

        <?php if($transactions->isEmpty()): ?>
            <div class="p-6 text-center text-gray-500">
                No transactions found.
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">User/Seller</th>
                        <th class="px-6 py-3 text-left">Type</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Description</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-3"><?php echo e($transaction->created_at->format('M d, Y H:i')); ?></td>
                        <td class="px-6 py-3"><?php echo e($transaction->wallet->user->name); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded <?php echo e($transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo e(ucfirst($transaction->type)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3 text-right font-semibold">
                            <span class="<?php echo e($transaction->type === 'credit' ? 'text-green-600' : 'text-red-600'); ?>">
                                <?php echo e($transaction->type === 'credit' ? '+' : '-'); ?>₫<?php echo e(number_format($transaction->amount, 0)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-full 
                                <?php if($transaction->status === 'completed'): ?>
                                    bg-green-100 text-green-800
                                <?php elseif($transaction->status === 'payout_approved'): ?>
                                    bg-blue-100 text-blue-800
                                <?php elseif($transaction->status === 'payout_rejected'): ?>
                                    bg-red-100 text-red-800
                                <?php else: ?>
                                    bg-yellow-100 text-yellow-800
                                <?php endif; ?>">
                                <?php echo e(ucfirst($transaction->status ?? 'pending')); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600"><?php echo e($transaction->description); ?></td>
                        <td class="px-6 py-3 text-center">
                            <?php if($transaction->type === 'credit' && $transaction->status === 'pending'): ?>
                                <div class="flex gap-2 justify-center">
                                    <form method="POST" action="<?php echo e(route('admin.transaction.approve', $transaction)); ?>" class="inline" onsubmit="return confirm('Approve this payout?')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                                            Approve
                                        </button>
                                    </form>
                                    <button type="button" data-transaction-id="<?php echo e($transaction->id); ?>" class="reject-btn px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Reject</button>
                                </div>
                            <?php elseif($transaction->status === 'payout_approved'): ?>
                                <span class="text-xs text-green-600 font-semibold">✓ Paid</span>
                            <?php elseif($transaction->status === 'payout_rejected'): ?>
                                <span class="text-xs text-red-600 font-semibold">✗ Rejected</span>
                            <?php else: ?>
                                <span class="text-xs text-gray-500">—</span>
                            <?php endif; ?>
                        </td>
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

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold mb-4">Reject Payout</h3>
            <form method="POST" id="rejectForm" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Rejection Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" 
                              class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:border-red-500"
                              rows="4" placeholder="Enter reason for rejection..." required></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Reject Payout
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openRejectModal(transactionId) {
        const form = document.getElementById('rejectForm');
        form.action = '/admin/transactions/' + transactionId + '/reject';
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejection_reason').value = '';
    }

    // Reject button click handler
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.reject-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const transactionId = this.dataset.transactionId;
                openRejectModal(transactionId);
            });
        });
    });

    // Close modal when clicking outside
    document.getElementById('rejectModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeRejectModal();
        }
    });
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/admin/wallet/index.blade.php ENDPATH**/ ?>