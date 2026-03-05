
<?php $__env->startSection('title', 'My Wallet'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <a href="<?php echo e(route('seller.dashboard')); ?>" class="inline-flex items-center text-sm font-medium text-neutral-500 hover:text-primary transition-colors group">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform"></i>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-serif font-bold text-primary mt-4">My Wallet</h1>
            <p class="text-neutral-500 mt-2">Manage your earnings and request withdrawals.</p>
        </div>

        <form method="GET" action="<?php echo e(route('seller.wallet')); ?>" class="flex gap-2 items-center bg-white p-2 rounded-xl shadow-sm border border-neutral-100">
            <select name="month" class="border-none rounded-lg px-3 py-2 bg-neutral-50 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo e($m); ?>" <?php echo e($month == $m ? 'selected' : ''); ?>>Month <?php echo e(sprintf('%02d', $m)); ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="border-none rounded-lg px-3 py-2 bg-neutral-50 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                <?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e($year == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-primary text-white px-5 py-2 rounded-lg hover:bg-primary-light transition-colors text-sm font-medium shadow-soft">Filter</button>
        </form>
    </div>

    <!-- Wallet Balance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Gross Revenue -->
        <div class="bg-primary p-6 rounded-2xl shadow-hover flex flex-col relative overflow-hidden group border border-primary-light/20">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-gold duration-500 rounded-full opacity-10 group-hover:scale-150 transition-transform"></div>
            <div class="relative z-10 flex-1">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-neutral-300">Gross Revenue</p>
                    <i data-lucide="trending-up" class="w-5 h-5 text-gold"></i>
                </div>
                <h2 class="text-4xl font-bold text-white mb-2">$<?php echo e(number_format($totalGrossRevenue ?? 0, 2)); ?></h2>
                <p class="text-xs text-neutral-400">Total sales amount</p>
            </div>
        </div>

        <!-- Pending Balance -->
        <?php if(isset($pendingBalance) && $pendingBalance > 0): ?>
        <div class="bg-orange-50 p-6 rounded-2xl shadow-soft border border-orange-100 flex flex-col group hover:border-orange-200 transition-colors relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-orange-500 duration-500 rounded-full opacity-5 group-hover:scale-150 transition-transform"></div>
            <div class="relative z-10 flex-1">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-orange-800">Pending Balance</p>
                    <i data-lucide="clock" class="w-5 h-5 text-orange-500"></i>
                </div>
                <h2 class="text-4xl font-bold text-orange-600 mb-2">$<?php echo e(number_format($pendingBalance, 2)); ?></h2>
                <p class="text-xs text-orange-700">Awaiting clearance</p>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 flex flex-col group hover:border-gold/50 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-neutral-500">Pending Balance</p>
                <i data-lucide="clock" class="w-5 h-5 text-neutral-400 group-hover:text-gold transition-colors"></i>
            </div>
            <h2 class="text-4xl font-bold text-primary mb-2">$0.00</h2>
            <p class="text-xs text-neutral-400">Awaiting clearance</p>
        </div>
        <?php endif; ?>

        <!-- Total Withdrawn -->
        <div class="bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 flex flex-col group hover:border-gold/50 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-neutral-500">Total Withdrawn</p>
                <i data-lucide="arrow-down-right" class="w-5 h-5 text-purple-500"></i>
            </div>
            <h2 class="text-3xl font-bold text-primary mb-2">$<?php echo e(number_format($totalWithdrawn ?? 0, 2)); ?></h2>
            <p class="text-xs text-neutral-400">Funds transferred</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content: Transactions -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-soft border border-neutral-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-neutral-100 bg-neutral-50/50 flex justify-between items-center">
                    <h3 class="font-serif font-bold text-primary text-lg">Transaction History</h3>
                </div>

                <?php if($transactions->isEmpty()): ?>
                    <div class="p-12 text-center flex flex-col items-center">
                        <div class="w-16 h-16 bg-neutral-50 rounded-full flex items-center justify-center mb-4 text-neutral-400">
                            <i data-lucide="receipt" class="w-8 h-8"></i>
                        </div>
                        <p class="text-neutral-500 font-medium">No transactions yet.</p>
                        <p class="text-sm text-neutral-400">Your earnings and withdrawals will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-neutral-50 text-neutral-500 font-medium">
                                <tr>
                                    <th class="px-6 py-4 text-left whitespace-nowrap">Date</th>
                                    <th class="px-6 py-4 text-left">Description</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-neutral-50/50 transition-colors group">
                                    <td class="px-6 py-4 text-neutral-500 whitespace-nowrap">
                                        <?php echo e($transaction->created_at->format('M d, Y')); ?><br>
                                        <span class="text-xs text-neutral-400"><?php echo e($transaction->created_at->format('H:i')); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 
                                                <?php echo e($transaction->type === 'credit' ? 'bg-green-50 text-green-500' : 'bg-red-50 text-red-500'); ?>">
                                                <i data-lucide="<?php echo e($transaction->type === 'credit' ? 'arrow-down-left' : 'arrow-up-right'); ?>" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-primary"><?php echo e($transaction->description); ?></p>
                                                <p class="text-xs text-neutral-500"><?php echo e(ucfirst($transaction->type)); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                            $statusClass = [
                                                'completed' => 'bg-green-50 text-green-600 border-green-200',
                                                'pending' => 'bg-orange-50 text-orange-600 border-orange-200',
                                                'failed' => 'bg-red-50 text-red-600 border-red-200',
                                            ][$transaction->status] ?? 'bg-neutral-50 text-neutral-600 border-neutral-200';
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?php echo e($statusClass); ?>">
                                            <?php echo e($transaction->status); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="font-bold <?php echo e($transaction->type === 'credit' ? 'text-green-600' : 'text-primary'); ?>">
                                            <?php echo e($transaction->type === 'credit' ? '+' : '-'); ?>$<?php echo e(number_format($transaction->amount, 2)); ?>

                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if($transactions->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-neutral-100 bg-neutral-50/30">
                        <?php echo e($transactions->links()); ?>

                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar: Withdraw Form -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-2xl shadow-soft border border-neutral-100 sticky top-28">
                <div class="flex items-center gap-3 mb-6 relative">
                    <div class="w-10 h-10 bg-gold/10 text-gold-dark rounded-xl flex items-center justify-center shrink-0">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-serif font-bold text-primary text-lg">Withdraw Funds</h3>
                        <p class="text-xs text-neutral-500">Transfer to your bank account.</p>
                    </div>
                </div>

                <?php if($actualBalance > 0): ?>
                <form action="<?php echo e(route('seller.wallet.withdraw')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Amount to Withdraw</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-neutral-500 font-medium">$</span>
                            </div>
                            <input type="number" name="amount" step="0.01" max="<?php echo e($actualBalance); ?>" placeholder="0.00" 
                                class="w-full pl-8 pr-4 py-3 bg-neutral-50 border border-neutral-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gold/50 focus:border-gold outline-none transition-all shadow-sm font-medium text-primary text-lg" required>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-neutral-500">Max available: <span class="font-bold text-primary">$<?php echo e(number_format($actualBalance, 2)); ?></span></span>
                            <button type="button" onclick="document.querySelector('input[name=amount]').value = <?php echo e($actualBalance); ?>" class="text-xs font-medium text-gold-dark hover:text-gold transition-colors">Max</button>
                        </div>
                    </div>
                    
                    <div class="mb-6 p-4 bg-primary-light/5 rounded-xl border border-primary-light/10">
                        <div class="flex gap-3">
                            <i data-lucide="info" class="w-4 h-4 text-primary shrink-0 mt-0.5"></i>
                            <div class="text-xs text-neutral-600 leading-relaxed">
                                Withdrawals are processed within 1-2 business days. The minimum withdrawal amount is $10.00.
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3.5 bg-primary text-white font-medium rounded-xl hover:bg-primary-light focus:ring-4 focus:ring-primary/20 transition-all shadow-soft hover:shadow-hover hover:-translate-y-0.5">
                        Confirm Withdrawal <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>
                <?php else: ?>
                <div class="text-center py-8">
                    <div class="inline-flex w-16 h-16 bg-neutral-50 rounded-full items-center justify-center mb-4 text-neutral-400">
                        <i data-lucide="lock" class="w-8 h-8"></i>
                    </div>
                    <p class="text-primary font-medium mb-1">No Funds Available</p>
                    <p class="text-sm text-neutral-500">You do not have any available balance to withdraw right now.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/seller/wallet/index.blade.php ENDPATH**/ ?>