

<?php $__env->startSection('title', 'Conversation about ' . $product->name); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-4">
        <a href="<?php echo e(route('customer.messages.index')); ?>" class="text-blue-600 hover:underline">&larr; Back to Inbox</a>
    </div>

    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-bold mb-4">Product: <a href="<?php echo e(route('products.show', $product)); ?>" class="text-blue-600 hover:underline"><?php echo e($product->name); ?></a></h2>
        <h3 class="text-lg mb-4">Seller: <?php echo e($other->name); ?></h3>
        <div id="messages-container" class="h-96 bg-gray-100 rounded-lg p-4 mb-4 overflow-y-auto flex flex-col">
            <!-- messages loaded by JS -->
        </div>
        <form id="message-form" class="space-y-3">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="receiver_id" value="<?php echo e($other->id); ?>">
            <textarea name="message" placeholder="Type your message..." 
                class="w-full px-3 py-2 border rounded resize-none h-20"
                maxlength="1000" required></textarea>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Send Message
            </button>
        </form>
    </div>
</div>

<script>
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const productId = <?php echo e($product->id); ?>;
    const otherId = <?php echo e($other->id); ?>;
    const userId = <?php echo e(auth()->check() ? auth()->id() : 'null'); ?>;

    async function loadMessages() {
        try {
            const response = await fetch(`/products/${productId}/messages?user_id=${otherId}`);
            
            // Note: If you face HTML responses instead of JSON due to auth middleware or errors
            // Use this check.
            if (!response.ok) {
                console.error("Failed to load messages:", response.status);
                return;
            }

            const messages = await response.json();
            messagesContainer.innerHTML = '';
            messages.forEach(msg => {
                const isOwn = msg.sender_id === userId;
                const div = document.createElement('div');
                div.className = `mb-3 ${isOwn ? 'text-right' : 'text-left'}`;
                div.innerHTML = `
                    <div class="${isOwn ? 'bg-blue-500 text-white' : 'bg-gray-300 text-black'} rounded-lg px-3 py-2 inline-block max-w-xs text-left">
                        ${msg.message}
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        ${new Date(msg.created_at).toLocaleTimeString()}
                    </div>
                `;
                messagesContainer.appendChild(div);
            });
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    loadMessages();
    setInterval(loadMessages, 2000);

    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(messageForm);
        try {
            const response = await fetch(`/products/${productId}/messages`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            });
            if (response.ok) {
                messageForm.reset();
                loadMessages();
            } else {
                const error = await response.json();
                alert('Error: ' + (error.error || 'Failed to send message'));
            }
        } catch (err) {
            console.error('Error sending message', err);
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/customer/messages/conversation.blade.php ENDPATH**/ ?>