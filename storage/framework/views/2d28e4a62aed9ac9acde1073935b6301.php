

<?php $__env->startSection('content'); ?>
<div class="container">
    <h3>Xác thực đa yếu tố</h3>
    <p>Risk score của bạn: <strong><?php echo e($riskScore); ?></strong></p>
    <p>Vui lòng thực hiện bước xác thực MFA trước khi tiếp tục thanh toán cho đơn hàng #<?php echo e($order->order_number); ?>.</p>
    <!-- In a real application you would display a form here to enter an MFA code -->
    <form method="post" action="<?php echo e(route('paypal.create', $order)); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="mfa_verified" value="1" />
        <button class="btn btn-primary">Xác thực và tiếp tục thanh toán</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/mfa/verify.blade.php ENDPATH**/ ?>