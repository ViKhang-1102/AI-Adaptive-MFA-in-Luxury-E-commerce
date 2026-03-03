@extends('layouts.app')

@section('title', 'Messages Inbox')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Message Inbox</h1>

    @if(count($conversations) === 0)
    <div class="bg-white p-6 rounded-lg shadow text-center">
        <p class="text-gray-600">You have no conversations yet.</p>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-screen">
        <!-- Left Sidebar: Customers List -->
        <div class="lg:col-span-1 bg-white rounded-lg shadow overflow-hidden flex flex-col">
            <div class="p-4 border-b bg-gray-50">
                <h2 class="font-bold text-lg">Customers</h2>
                <p class="text-sm text-gray-600">Click to view messages</p>
            </div>
            <div id="customers-list" class="overflow-y-auto flex-1">
                <!-- Customers will load here via JS -->
            </div>
        </div>

        <!-- Right Panel: Products & Messages -->
        <div class="lg:col-span-3 bg-white rounded-lg shadow overflow-hidden flex flex-col">
            <div id="products-panel" class="h-full overflow-y-auto flex flex-col">
                <div class="p-8 text-center text-gray-500">
                    <p class="text-lg">Select a customer to view products and messages</p>
                </div>
            </div>

            <!-- Conversation View (hidden by default) -->
            <div id="conversation-panel" class="hidden h-full overflow-hidden flex flex-col">
                <div id="conv-header" class="p-4 border-b bg-gray-50">
                    <button id="back-to-products" class="text-blue-600 hover:underline mb-2">&larr; Back to Products</button>
                    <h2 id="conv-product-name" class="font-bold text-lg">Loading...</h2>
                    <p id="conv-customer-name" class="text-sm text-gray-600">Loading...</p>
                </div>
                
                <div id="messages-container" class="flex-1 overflow-y-auto p-4 bg-gray-100">
                    <!-- Messages will load here -->
                </div>

                <form id="message-form" class="p-4 border-t bg-white space-y-2">
                    @csrf
                    <input type="hidden" id="receiver-id" name="receiver_id">
                    <textarea name="message" placeholder="Type your message..." 
                        class="w-full px-3 py-2 border rounded resize-none h-20"
                        maxlength="1000" required></textarea>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    const sellerId = {{ auth()->id() }};
    let selectedCustomerId = null;
    let selectedProductId = null;

    // Load customers list
    async function loadCustomers() {
        try {
            const response = await fetch('/seller/messages/api/customers');
            const customers = await response.json();
            
            const list = document.getElementById('customers-list');
            list.innerHTML = '';

            if (customers.length === 0) {
                list.innerHTML = '<p class="p-4 text-gray-600 text-sm">No customers yet</p>';
                return;
            }

            customers.forEach(customer => {
                const div = document.createElement('div');
                div.className = 'p-3 border-b hover:bg-blue-50 cursor-pointer transition';
                if (selectedCustomerId === customer.id) {
                    div.classList.add('bg-blue-100', 'border-l-4', 'border-l-blue-600');
                }
                
                const badge = customer.unread_count > 0 
                    ? `<span class="ml-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full">${customer.unread_count}</span>`
                    : '';
                
                div.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <p class="font-semibold text-sm">${escapeHtml(customer.name)}</p>
                            <p class="text-xs text-gray-600 truncate">${escapeHtml(customer.last_message)}</p>
                            <p class="text-xs text-gray-400 mt-1">${formatTime(customer.last_message_at)}</p>
                        </div>
                        ${badge}
                    </div>
                `;

                div.addEventListener('click', () => selectCustomer(customer.id, customer.name));
                list.appendChild(div);
            });
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    // Load products for selected customer
    async function loadProducts(customerId) {
        try {
            const response = await fetch(`/seller/messages/api/customers/${customerId}/products`);
            const products = await response.json();

            const panel = document.getElementById('products-panel');
            panel.innerHTML = '';

            if (products.length === 0) {
                panel.innerHTML = '<div class="p-8 text-center text-gray-500"><p>No products with messages from this customer</p></div>';
                return;
            }

            const title = document.createElement('div');
            title.className = 'p-4 border-b bg-gray-50 sticky top-0';
            title.innerHTML = '<h3 class="font-bold text-lg">Products with Messages</h3>';
            panel.appendChild(title);

            products.forEach(product => {
                const div = document.createElement('div');
                div.className = 'p-3 border-b hover:bg-blue-50 cursor-pointer transition';
                if (selectedProductId === product.id) {
                    div.classList.add('bg-blue-100', 'border-l-4', 'border-l-blue-600');
                }

                const badge = product.unread_count > 0 
                    ? `<span class="ml-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full">${product.unread_count}</span>`
                    : '';

                div.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div class="flex-1 flex items-center gap-3">
                            ${product.image ? `<img src="${product.image}" class="w-10 h-10 object-cover rounded">` : '<div class="w-10 h-10 bg-gray-300 rounded"></div>'}
                            <div class="flex-1">
                                <p class="font-semibold text-sm">${escapeHtml(product.name)}</p>
                                <p class="text-xs text-gray-600 truncate">${escapeHtml(product.last_message)}</p>
                                <p class="text-xs text-gray-400 mt-1">${formatTime(product.last_message_at)}</p>
                            </div>
                        </div>
                        ${badge}
                    </div>
                `;

                div.addEventListener('click', () => selectProduct(product.id, product.name, customerId));
                panel.appendChild(div);
            });
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    // Select customer
    function selectCustomer(customerId, customerName) {
        selectedCustomerId = customerId;
        selectedProductId = null;
        
        document.getElementById('conversation-panel').classList.add('hidden');
        document.getElementById('products-panel').classList.remove('hidden');
        
        loadProducts(customerId);
        loadCustomers();
    }

    // Select product
    function selectProduct(productId, productName, customerId) {
        selectedProductId = productId;
        
        document.getElementById('conversation-panel').classList.remove('hidden');
        document.getElementById('products-panel').classList.add('hidden');
        
        document.getElementById('conv-product-name').textContent = productName;
        document.getElementById('receiver-id').value = customerId;
        
        loadMessages(productId, customerId);
    }

    // Load messages for product & customer
    async function loadMessages(productId, customerId) {
        try {
            const response = await fetch(`/products/${productId}/messages?user_id=${customerId}`);
            const messages = await response.json();

            localStorage.setItem('last_message_scroll', messages.length);

            const container = document.getElementById('messages-container');
            container.innerHTML = '';

            if (messages.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-600 py-8">No messages yet. Start the conversation!</p>';
                return;
            }

            messages.forEach(msg => {
                const isOwn = msg.sender_id === sellerId;
                const div = document.createElement('div');
                div.className = `mb-3 ${isOwn ? 'text-right' : 'text-left'}`;
                div.innerHTML = `
                    <div class="${isOwn ? 'bg-blue-500 text-white' : 'bg-gray-300 text-black'} rounded-lg px-3 py-2 inline-block max-w-xs break-words">
                        ${escapeHtml(msg.message)}
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        ${isOwn ? 'You' : 'Customer'} | ${formatTime(msg.created_at)}
                    </div>
                `;
                container.appendChild(div);
            });

            container.scrollTop = container.scrollHeight;
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    // Send message
    document.getElementById('message-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!selectedProductId || !selectedCustomerId) {
            alert('Please select a product first');
            return;
        }

        const formData = new FormData(document.getElementById('message-form'));
        
        try {
            const response = await fetch(`/products/${selectedProductId}/messages`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            });

            if (response.ok) {
                document.getElementById('message-form').reset();
                loadMessages(selectedProductId, selectedCustomerId);
                loadCustomers();
                loadProducts(selectedCustomerId);
            } else {
                const error = await response.json();
                alert('Error: ' + (error.error || 'Failed to send message'));
            }
        } catch (err) {
            console.error('Error sending message:', err);
        }
    });

    // Back to products
    document.getElementById('back-to-products').addEventListener('click', () => {
        selectedProductId = null;
        document.getElementById('conversation-panel').classList.add('hidden');
        document.getElementById('products-panel').classList.remove('hidden');
        loadProducts(selectedCustomerId);
    });

    // Auto-refresh messages every 2 seconds
    setInterval(() => {
        if (selectedProductId && selectedCustomerId) {
            loadMessages(selectedProductId, selectedCustomerId);
        }
    }, 2000);

    // Helper functions
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function formatTime(dateOrTime) {
        if (!dateOrTime) return '';
        const date = new Date(dateOrTime);
        const now = new Date();
        const diff = now - date;

        // Less than 1 minute
        if (diff < 60000) return 'just now';

        // Less than 1 hour
        if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';

        // Less than 1 day
        if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';

        // Same day
        if (date.toDateString() === now.toDateString()) return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        // Yesterday
        const yesterday = new Date(now);
        yesterday.setDate(yesterday.getDate() - 1);
        if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';

        // Other dates
        return date.toLocaleDateString();
    }

    // Initial load
    loadCustomers();
</script>

<style>
    #customers-list, #products-panel, #conversation-panel {
        -webkit-overflow-scrolling: touch;
    }
</style>
@endsection
