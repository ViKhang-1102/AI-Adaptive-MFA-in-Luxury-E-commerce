<div style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #111;">
    <h2>New Support Request</h2>

    <p><strong>User:</strong> <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</p>
    <?php if($order): ?>
        <p><strong>Order:</strong> #<?php echo e($order->id); ?> (<?php echo e($order->order_number ?? 'N/A'); ?>)</p>
        <p><strong>Order Status:</strong> <?php echo e(ucfirst($order->status)); ?></p>
    <?php endif; ?>

    <p><strong>Subject:</strong> <?php echo e($subjectLine); ?></p>

    <p><strong>Message:</strong></p>
    <div style="border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fafafa;">
        <?php echo nl2br(e($messageBody)); ?>

    </div>

    <p style="margin-top: 16px; font-size: 12px; color: #666;">This support request was submitted through the customer contact form.</p>
</div>
<?php /**PATH C:\laragon\www\E-commerce2026\resources\views/emails/support-request.blade.php ENDPATH**/ ?>