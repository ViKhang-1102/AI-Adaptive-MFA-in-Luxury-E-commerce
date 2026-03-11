
<?php $__env->startSection('title', 'Platform Wallet'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2 bg-neutral-500 text-white rounded-md hover:bg-gray-600 font-semibold">
                &larr; Dashboard
            </a>
            <h1 class="text-3xl font-bold">Platform Wallet Management</h1>
        </div>

        <form method="GET" action="<?php echo e(route('admin.wallet')); ?>" class="flex gap-2 items-center bg-white p-2 rounded-md shadow-sm">
            <select name="month" class="border rounded-md px-3 py-2 bg-white text-sm outline-none focus:border-primary">
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>Month <?php echo e(sprintf('%02d', $m)); ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="border rounded-md px-3 py-2 bg-white text-sm outline-none focus:border-primary">
                <?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-light transition-colors text-sm font-medium">Filter</button>
        </form>
    </div>

    <!-- Platform Wallet Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded-md-lg shadow-sm">
            <p class="text-neutral-600 text-sm mb-1">Total Platform Balance</p>
            <h2 class="text-3xl font-bold text-primary">$<?php echo e(number_format($totalBalance, 2)); ?></h2>
            <p class="text-neutral-500 text-xs mt-2">Current funds in platform</p>
        </div>
        <div class="bg-white p-6 rounded-md-lg shadow-sm relative overflow-hidden group hover:shadow-md transition-all">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-purple-500 duration-500 rounded-full opacity-5 group-hover:scale-150 transition-transform"></div>
            <div class="relative z-10">
                <p class="text-neutral-600 text-sm mb-1">Total Transactions</p>
                <div class="flex items-center gap-3">
                    <i data-lucide="receipt" class="w-6 h-6 text-purple-600"></i>
                    <h2 class="text-3xl font-bold text-purple-600"><?php echo e($totalTransactions); ?></h2>
                </div>
                <p class="text-neutral-500 text-xs mt-2">In selected timeframe</p>
            </div>
        </div>
    </div>

    <!-- Seller Funds Overview -->
    <div class="bg-white rounded-md-lg shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b bg-neutral-50 flex justify-between items-center">
            <h3 class="font-bold text-lg text-primary">Seller Funds Overview</h3>
            <span class="text-sm text-neutral-500 font-medium bg-white px-3 py-1 rounded-full border">
                Filtering: Month <?php echo e(sprintf('%02d', $month)); ?> / <?php echo e($year); ?>

            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-neutral-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left">Seller Name</th>
                        <th class="px-6 py-3 text-right">Total Gross Revenue</th>
                        <th class="px-6 py-3 text-right">Total Net Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $sellers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seller): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-b hover:bg-neutral-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-primary flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gold/20 text-gold-dark flex items-center justify-center font-bold text-xs uppercase">
                                <?php echo e(substr($seller->name, 0, 2)); ?>

                            </div>
                            <?php echo e($seller->name); ?>

                        </td>
                        <td class="px-6 py-4 text-right text-neutral-600 font-semibold text-lg">$<?php echo e(number_format($seller->total_gross_revenue, 2)); ?></td>
                        <td class="px-6 py-4 text-right text-green-600 font-bold text-lg">$<?php echo e(number_format($seller->total_net_revenue, 2)); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-neutral-500">No seller data found for this timeframe.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($totalBalance > 0): ?>
    <div class="bg-white p-6 rounded-md-lg shadow-sm border border-gold/20 mb-8 max-w-xl">
        <h3 class="font-bold text-lg mb-2 flex items-center gap-2">
            <i data-lucide="banknote" class="w-5 h-5 text-gold"></i> Withdraw Platform Fees
        </h3>
        <p class="text-sm text-neutral-600 mb-4">Transfer platform earnings to your configured Admin PayPal account. (<?php echo e(auth()->user()->paypal_email ?? 'Not Configured'); ?>)</p>
        
        <form action="<?php echo e(route('admin.wallet.withdraw')); ?>" method="POST" class="flex gap-3">
            <?php echo csrf_field(); ?>
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-neutral-500 font-medium">$</span>
                </div>
                <input type="number" name="amount" step="0.01" max="<?php echo e($totalBalance); ?>" placeholder="0.00" 
                    class="w-full pl-7 pr-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-primary outline-none" required>
            </div>
            <button type="submit" class="bg-primary text-white px-6 py-2 rounded-md hover:bg-primary-light transition-colors shadow-sm <?php echo e(!auth()->user()->paypal_email ? 'opacity-50 cursor-not-allowed' : ''); ?>" <?php echo e(!auth()->user()->paypal_email ? 'disabled title="Configure PayPal Email first"' : ''); ?>>
                Withdraw
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-md-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-neutral-50">
            <h3 class="font-bold text-lg">Recent Transactions</h3>
        </div>

        <?php if($transactions->isEmpty()): ?>
            <div class="p-6 text-center text-neutral-500">
                No transactions found.
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-neutral-100 border-b">
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
                    <tr class="border-b hover:bg-neutral-50">
                        <td class="px-6 py-3"><?php echo e($transaction->created_at->format('M d, Y H:i')); ?></td>
                        <td class="px-6 py-3"><?php echo e($transaction->wallet->user->name); ?></td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-md <?php echo e($transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo e(ucfirst($transaction->type)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3 text-right font-semibold">
                            <span class="<?php echo e($transaction->type === 'credit' ? 'text-green-600' : 'text-red-600'); ?>">
                                <?php echo e($transaction->type === 'credit' ? '+' : '-'); ?>$<?php echo e(number_format($transaction->amount, 2)); ?>

                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 text-sm rounded-md-full 
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
                        <td class="px-6 py-3 text-sm text-neutral-600"><?php echo e($transaction->description); ?></td>
                        <td class="px-6 py-3 text-center">
                            <?php if($transaction->type === 'credit' && $transaction->status === 'pending'): ?>
                                <div class="flex gap-2 justify-center">
                                    <form method="POST" action="<?php echo e(route('admin.transaction.approve', $transaction)); ?>" class="inline" onsubmit="return confirm('Approve this payout?')">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded-md hover:bg-green-700">
                                            Approve
                                        </button>
                                    </form>
                                    <button type="button" data-transaction-id="<?php echo e($transaction->id); ?>" class="reject-btn px-3 py-1 bg-red-600 text-white text-xs rounded-md hover:bg-red-700">Reject</button>
                                </div>
                            <?php elseif($transaction->status === 'payout_approved'): ?>
                                <span class="text-xs text-green-600 font-semibold">✓ Paid</span>
                            <?php elseif($transaction->status === 'payout_rejected'): ?>
                                <span class="text-xs text-red-600 font-semibold">✗ Rejected</span>
                            <?php else: ?>
                                <span class="text-xs text-neutral-500">—</span>
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
        <div class="bg-white rounded-md-lg shadow-sm-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold mb-4">Reject Payout</h3>
            <form method="POST" id="rejectForm" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="rejection_reason" class="block text-sm font-medium text-neutral-700 mb-2">
                        Rejection Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejection_reason" name="rejection_reason" 
                              class="w-full px-3 py-2 border border-neutral-200 rounded-md shadow-sm-sm focus:outline-none focus:border-red-500"
                              rows="4" placeholder="Enter reason for rejection..." required></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeRejectModal()" 
                            class="flex-1 px-4 py-2 border border-neutral-200 rounded-md hover:bg-neutral-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
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