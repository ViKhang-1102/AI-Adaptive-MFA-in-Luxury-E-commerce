

<?php $__env->startSection('title', 'Contact Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto py-10">
    <div class="bg-white rounded-md-lg shadow-sm-md p-6">
        <h1 class="text-2xl font-bold mb-4">Contact Admin</h1>

        <?php if(session('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md mb-4">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-4">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <p class="text-sm text-neutral-600 mb-6">
            Need help? Use this form to send a message to our support team. We will prioritize orders that require manual verification.
        </p>

        <?php if($order): ?>
            <div class="bg-neutral-50 border border-neutral-200 rounded-md p-4 mb-6">
                <p class="font-semibold">Order #<?php echo e($order->id); ?> (<?php echo e(ucfirst($order->status)); ?>)</p>
                <p class="text-sm text-neutral-600">Total: $<?php echo e(number_format($order->total_amount, 2)); ?></p>
                <p class="text-sm text-neutral-600">Placed on <?php echo e($order->created_at->format('M d, Y H:i')); ?></p>
                <p class="text-sm mt-2 text-neutral-800">Please mention this order in your message so our team can verify it quickly.</p>
            </div>

            <?php if(isset($messages)): ?>
                <div id="support-messages" class="bg-white border border-neutral-200 rounded-md p-4 mb-6">
                    <h2 class="text-lg font-semibold mb-3">Recent Support Messages</h2>
                    <div id="support-messages-list" class="space-y-3">
                        <?php if($messages->isNotEmpty()): ?>
                            <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-3 rounded-lg <?php echo e($msg->sender_id === Auth::id() ? 'bg-primary/10' : 'bg-neutral-50'); ?> border border-neutral-100">
                                    <p class="text-xs text-neutral-500"><?php echo e($msg->created_at->format('M d, Y H:i')); ?> • <?php echo e($msg->sender->name); ?></p>
                                    <p class="mt-1 text-sm text-neutral-800"><?php echo e($msg->message); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <p class="text-sm text-neutral-500">No messages yet. Our support team will respond here shortly.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('support.contact.submit')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="order_id" value="<?php echo e($order?->id); ?>">
            <div id="support-data" data-order-id="<?php echo e($order?->id); ?>" data-current-user="<?php echo e(Auth::id()); ?>"></div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700">Subject</label>
                <input type="text" name="subject" value="<?php echo e(old('subject')); ?>" class="mt-1 w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold" required>
                <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-neutral-700">Message</label>
                <textarea name="message" rows="6" class="mt-1 w-full border border-neutral-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gold" required><?php echo e(old('message')); ?></textarea>
                <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-light transition">Send Message</button>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    (function() {
        const supportData = document.getElementById('support-data');
        const orderId = supportData?.dataset.orderId;
        const currentUserId = supportData?.dataset.currentUser;
        if (!orderId) return;

        const messagesContainer = document.getElementById('support-messages-list');
        if (!messagesContainer) return;

        async function fetchMessages() {
            try {
                const url = new URL('<?php echo e(route('support.messages')); ?>', window.location.origin);
                url.searchParams.set('order_id', orderId);

                const response = await fetch(url);
                if (!response.ok) return;
                const data = await response.json();
                if (!data.messages) return;

                const html = data.messages.map(msg => {
                    const isMe = String(msg.sender_id) === String(currentUserId);
                    const bgClass = isMe ? 'bg-primary/10' : 'bg-neutral-50';
                    const sender = msg.sender?.name || 'Support';
                    const time = new Date(msg.created_at).toLocaleString();
                    return `
                        <div class="p-3 rounded-lg ${bgClass} border border-neutral-100">
                            <p class="text-xs text-neutral-500">${time} • ${sender}</p>
                            <p class="mt-1 text-sm text-neutral-800">${msg.message}</p>
                        </div>
                    `;
                }).join('');

                messagesContainer.innerHTML = html || '<p class="text-sm text-neutral-500">No messages yet. Our support team will respond here shortly.</p>';
            } catch (error) {
                console.error('Unable to fetch latest messages', error);
            }
        }

        setInterval(fetchMessages, 5000);
    })();
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/support/contact.blade.php ENDPATH**/ ?>