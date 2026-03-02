

<?php $__env->startSection('content'); ?>
<div class="container">
    <h3>Thanh toán bị hủy</h3>
    <p>Giao dịch PayPal đã bị hủy. Vui lòng thử lại.</p>
    <a href="<?php echo e(url('/')); ?>" class="btn btn-secondary">Trở lại </a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/paypal/cancel.blade.php ENDPATH**/ ?>