

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
        <div>
            <h2 class="text-3xl font-bold font-serif text-primary mb-1">Security Insights</h2>
            <p class="text-neutral-500 text-sm">Monitor and manage the AI-Driven Adaptive MFA System</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Export Button -->
            <a href="<?php echo e(route('admin.security.export-blocked')); ?>" class="bg-primary hover:bg-primary-light text-white px-5 py-2.5 rounded-lg flex items-center gap-2 transition-colors font-medium shadow-sm">
                <i class="fas fa-download text-sm"></i>
                Export Report
            </a>

            <!-- A/B Testing Toggle -->
            <div class="bg-white px-4 py-2.5 rounded-lg shadow-sm border flex items-center gap-4">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="mfaToggle" class="sr-only peer" <?php echo e($aiEnabled ? 'checked' : ''); ?>>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                </label>
                <div>
                    <span class="block text-sm font-bold text-neutral-800">Adaptive AI Engine</span>
                    <span class="text-xs text-neutral-500" id="mfaStatusText"><?php echo e($aiEnabled ? 'Active' : 'Disabled'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total -->
        <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6 flex flex-col justify-center items-center h-full">
            <span class="text-neutral-500 text-xs font-bold uppercase tracking-wider mb-3">Analyzed Transactions</span>
            <span class="text-5xl font-bold text-primary"><?php echo e(number_format($totalTransactions)); ?></span>
        </div>
        <!-- Allow -->
        <div class="bg-white rounded-xl shadow-sm border border-neutral-100 border-b-4 border-b-green-500 p-6 flex flex-col justify-center items-center h-full">
            <span class="text-neutral-500 text-xs font-bold uppercase tracking-wider mb-3">Allowed Flow</span>
            <span class="text-5xl font-bold text-green-500 mb-1"><?php echo e($allowPercentage); ?>%</span>
            <span class="text-neutral-400 text-xs">User Friction Nullified</span>
        </div>
        <!-- MFA -->
        <div class="bg-white rounded-xl shadow-sm border border-neutral-100 border-b-4 border-b-amber-500 p-6 flex flex-col justify-center items-center h-full">
            <span class="text-neutral-500 text-xs font-bold uppercase tracking-wider mb-3">MFA Enforced</span>
            <span class="text-5xl font-bold text-amber-500 mb-1"><?php echo e($mfaPercentage); ?>%</span>
            <span class="text-neutral-400 text-xs">Medium Risk Mitigated</span>
        </div>
        <!-- Block -->
        <div class="bg-white rounded-xl shadow-sm border border-neutral-100 border-b-4 border-b-red-500 p-6 flex flex-col justify-center items-center h-full">
            <span class="text-neutral-500 text-xs font-bold uppercase tracking-wider mb-3">Auto-Blocked</span>
            <span class="text-5xl font-bold text-red-500 mb-1"><?php echo e($blockPercentage); ?>%</span>
            <span class="text-neutral-400 text-xs">High Risk Prevented</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <!-- Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-neutral-100 p-6">
            <h3 class="text-lg font-bold text-primary mb-6">7-Day Global Risk Trend</h3>
            <div class="h-72 w-full relative">
                <canvas id="riskChart"></canvas>
            </div>
        </div>
        
        <!-- Top Risky Users -->
        <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6 flex flex-col h-full">
            <div class="flex items-center gap-3 mb-6">
                <h3 class="text-lg font-bold text-primary">Top Risky Users</h3>
                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded">Alert</span>
            </div>
            
            <div class="flex-1 overflow-y-auto">
                <ul class="space-y-4">
                    <?php $__empty_1 = true; $__currentLoopData = $topRiskyUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $riskyUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="flex items-center justify-between pb-4 border-b border-neutral-50 last:border-0 last:pb-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-neutral-100 flex items-center justify-center text-primary font-bold">
                                <?php echo e(substr($riskyUser->user->name ?? 'U', 0, 1)); ?>

                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-neutral-800"><?php echo e($riskyUser->user->name ?? 'Unknown'); ?></h4>
                                <span class="text-xs text-neutral-500"><?php echo e($riskyUser->user->email ?? 'No email'); ?></span>
                            </div>
                        </div>
                        <span class="bg-red-50 text-red-600 border border-red-100 text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                            <?php echo e($riskyUser->incident_count); ?> Flags
                        </span>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="text-center text-neutral-400 py-8 text-sm">
                        No high-risk users detected.
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Audits Table -->
    <div class="bg-white rounded-xl shadow-sm border border-neutral-100 p-6 mb-10 overflow-hidden">
        <h3 class="text-lg font-bold text-primary mb-6">Recent Audit Decisions</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs font-semibold text-neutral-400 uppercase tracking-wider border-b border-neutral-100">
                        <th class="pb-3 pr-4">DateTime</th>
                        <th class="pb-3 px-4">Customer</th>
                        <th class="pb-3 px-4">Amount</th>
                        <th class="pb-3 px-4">Engine Input</th>
                        <th class="pb-3 px-4 whitespace-nowrap">Risk Score</th>
                        <th class="pb-3 px-4">Level</th>
                        <th class="pb-3 px-4">Suggestion</th>
                        <th class="pb-3 px-4">Details</th>
                        <th class="pb-3 pl-4">Final Result</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-neutral-50">
                    <?php $__empty_1 = true; $__currentLoopData = $audits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="py-4 pr-4 text-neutral-500 whitespace-nowrap"><?php echo e($audit->created_at->format('M d, H:i')); ?></td>
                        <td class="py-4 px-4 font-bold text-neutral-700 whitespace-nowrap"><?php echo e($audit->user->name ?? 'Unknown'); ?></td>
                        <td class="py-4 px-4 text-neutral-600 whitespace-nowrap">$<?php echo e(number_format($audit->amount, 2)); ?></td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            <?php if(isset($audit->metadata['ai_enabled'])): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border border-neutral-200 bg-neutral-50 text-neutral-600">
                                    <?php echo e($audit->metadata['ai_enabled'] ? 'AI Core' : 'Static Mode'); ?>

                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border border-neutral-200 bg-neutral-50 text-neutral-600">
                                    Legacy Output
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 font-bold text-neutral-800"><?php echo e(number_format($audit->risk_score, 1)); ?></td>
                        <td class="py-4 px-4">
                            <?php if($audit->level == 'low'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">Low</span>
                            <?php elseif($audit->level == 'medium'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">Medium</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">High</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 text-xs font-medium uppercase text-neutral-500">
                            <button type="button" class="details-toggle text-primary hover:text-primary-dark" data-audit-id="<?php echo e($audit->id); ?>">View</button>
                        </td>
                        <td class="py-4 px-4 text-xs font-medium uppercase text-neutral-500"><?php echo e($audit->suggestion); ?></td>
                        <td class="py-4 pl-4 whitespace-nowrap">
                            <?php if($audit->result == 'success'): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-500 text-white shadow-sm">Verified/Paid</span>
                            <?php elseif($audit->result == 'failed'): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-500 text-white shadow-sm">MFA Failed</span>
                            <?php elseif($audit->result == 'blocked'): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-neutral-800 text-white shadow-sm">Blocked</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-neutral-200 text-neutral-700 shadow-sm">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="hidden bg-neutral-50" id="audit-details-<?php echo e($audit->id); ?>">
                        <td colspan="9" class="px-4 py-4">
                            <?php
                                $explanation = data_get($audit->metadata, 'risk_explanation.score_breakdown');
                                $engineInput = data_get($audit->metadata, 'engine_input') ?? data_get($audit->metadata, 'risk_explanation.input');
                            ?>
                            <div class="text-xs text-neutral-600">
                                <?php if($explanation): ?>
                                    <div class="font-semibold text-neutral-800 mb-2">Risk Breakdown</div>
                                    <ul class="list-disc list-inside space-y-1 mb-3">
                                        <?php $__currentLoopData = $explanation; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($line); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                <?php endif; ?>

                                <?php if($engineInput): ?>
                                    <div class="font-semibold text-neutral-800 mb-2">Engine Input</div>
                                    <ul class="list-disc list-inside space-y-1">
                                        <?php $__currentLoopData = $engineInput; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><strong><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?>:</strong> <?php echo e(is_bool($val) ? ($val ? 'Yes' : 'No') : $val); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                <?php endif; ?>

                                <?php if(!$explanation && !$engineInput): ?>
                                    <span class="text-neutral-500">No detailed breakdown is available for this audit.</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="py-8 text-center text-neutral-400 text-sm">No security audits recorded yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            <?php echo e($audits->links()); ?> 
        </div> <!-- Needs to check if pagination uses Tailwind -->
    </div>

    <!-- Thesis Evaluation Metrics Module -->
    <div class="mb-4">
        <h3 class="text-2xl font-bold font-serif text-primary mb-2">Thesis Evaluation Metrics</h3>
        <p class="text-neutral-500 text-sm mb-6">Empirical comparison between the legacy Static perimeter and the current AI-Driven Dynamic perimeter.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Interruption Rate Comparison -->
            <div class="bg-gradient-to-br from-primary to-primary-light rounded-xl shadow-lg p-6 text-white flex flex-col justify-center border border-primary-dark">
                <span class="text-blue-200 text-center text-xs font-bold uppercase tracking-wider mb-6 opacity-70">User Interruption Rate</span>
                <div class="flex justify-between items-center px-4">
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-gray-300"><?php echo e($staticInterruptionRate); ?>%</span>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-widest mt-1">Static</span>
                    </div>
                    <div class="text-gray-500">
                        <i class="fas fa-arrow-right text-lg"></i>
                    </div>
                    <div class="text-center">
                        <span class="block text-3xl font-bold text-gold"><?php echo e($dynamicInterruptionRate); ?>%</span>
                        <span class="text-xs font-medium text-gold-light opacity-80 uppercase tracking-widest mt-1">Dynamic</span>
                    </div>
                </div>
            </div>

            <!-- Checkout Time Comparison -->
            <div class="bg-gradient-to-br from-primary to-primary-light rounded-xl shadow-lg p-6 text-white flex flex-col justify-center border border-primary-dark">
                <span class="text-blue-200 text-center text-xs font-bold uppercase tracking-wider mb-6 opacity-70">Avg. Execution Time</span>
                <div class="flex justify-between items-center px-4">
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-gray-300"><?php echo e($staticAvgTime); ?>s</span>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-widest mt-1">Static</span>
                    </div>
                    <div class="text-gray-500">
                        <i class="fas fa-arrow-right text-lg"></i>
                    </div>
                    <div class="text-center">
                        <span class="block text-3xl font-bold text-gold"><?php echo e($dynamicAvgTime); ?>s</span>
                        <span class="text-xs font-medium text-gold-light opacity-80 uppercase tracking-widest mt-1">Dynamic</span>
                    </div>
                </div>
            </div>

            <!-- Conclusion Block -->
            <div class="bg-amber-50 rounded-xl shadow-sm p-6 flex flex-col justify-center border border-gold-dark relative overflow-hidden">
                <div class="absolute -top-4 -right-4 text-gold opacity-10">
                    <i class="fas fa-quote-right" style="font-size: 8rem;"></i>
                </div>
                <div class="text-gold mb-3 relative z-10">
                    <i class="fas fa-quote-left text-2xl"></i>
                </div>
                <p class="font-serif text-lg font-bold text-primary leading-snug relative z-10">
                    <?php echo e($conclusionText); ?>

                </p>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <div id="security-dashboard"
        data-chart-labels='<?php echo json_encode($chartLabels, 15, 512) ?>'
        data-chart-data='<?php echo json_encode($chartData, 15, 512) ?>'
        data-toggle-url="<?php echo e(route('admin.security.toggle-mfa')); ?>"
        data-csrf="<?php echo e(csrf_token()); ?>">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo e(asset('js/admin-security.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/admin/security/index.blade.php ENDPATH**/ ?>