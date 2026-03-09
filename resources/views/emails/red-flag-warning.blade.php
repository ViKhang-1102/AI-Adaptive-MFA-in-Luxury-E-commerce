<div style="font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #111;">
    <h2>Security Alert</h2>

    <p>Dear {{ $user->name }},</p>

    <p>We have detected {{ $redFlagCount }} recent activities on your account that were flagged as high risk by our security engine.</p>

    <p>For your protection, we recommend that you do not proceed with any new transactions until a verification has been completed. If you believe this is a mistake, please contact our support team immediately.</p>

    <p>Thank you for your cooperation,<br>
    The Support Team</p>
</div>
