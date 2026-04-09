<?php $__env->startSection('title', 'Payment Cancelled'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-neutral-50 flex flex-col justify-center items-center py-12 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border-t-4 border-red-500">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-6">
                <svg class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">
                Payment Cancelled
            </h2>
            <p class="text-base text-gray-600 mb-6">
                Your PayPal transaction was cancelled. No charges were made to your account.
            </p>
        </div>

        <div class="bg-blue-50 rounded-lg p-6 space-y-3 border border-blue-100">
            <div class="flex items-center space-x-3 text-blue-800">
                <i class="fas fa-shopping-cart text-xl"></i>
                <h3 class="text-lg font-semibold">Items Saved!</h3>
            </div>
            <p class="text-sm text-blue-600 leading-relaxed">
                Don't worry, all the items from your order have been automatically moved back to your cart. You can review them and try checking out again whenever you're ready.
            </p>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?php echo e(route('cart.index')); ?>" class="flex-1 flex justify-center items-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200">
                <i class="fas fa-arrow-left mr-2 text-xs"></i> Back to My Cart
            </a>
            <a href="<?php echo e(route('home')); ?>" class="flex-1 flex justify-center items-center py-3 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                Continue Shopping
            </a>
        </div>
        
        <div class="text-center pt-6">
            <p class="text-xs text-gray-400">
                Need help with your payment? <a href="<?php echo e(route('support.contact')); ?>" class="text-blue-500 hover:underline">Contact our support team</a>.
            </p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026 - Copy\resources\views/paypal/cancel.blade.php ENDPATH**/ ?>