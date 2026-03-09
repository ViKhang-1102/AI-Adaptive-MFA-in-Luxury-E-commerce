<div style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #111;">
    <h2>New Support Request</h2>

    <p><strong>User:</strong> {{ $user->name }} ({{ $user->email }})</p>
    @if($order)
        <p><strong>Order:</strong> #{{ $order->id }} ({{ $order->order_number ?? 'N/A' }})</p>
        <p><strong>Order Status:</strong> {{ ucfirst($order->status) }}</p>
    @endif

    <p><strong>Subject:</strong> {{ $subjectLine }}</p>

    <p><strong>Message:</strong></p>
    <div style="border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fafafa;">
        {!! nl2br(e($messageBody)) !!}
    </div>

    <p style="margin-top: 16px; font-size: 12px; color: #666;">This support request was submitted through the customer contact form.</p>
</div>
