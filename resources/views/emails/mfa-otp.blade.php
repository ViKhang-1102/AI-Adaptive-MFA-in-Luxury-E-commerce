<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8f9fa; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #1a1a1a; letter-spacing: 2px; text-transform: uppercase; }
        .title { font-size: 20px; font-weight: 600; margin-bottom: 20px; text-align: center; }
        .otp-box { text-align: center; background: #f4f4f4; padding: 20px; border-radius: 8px; margin: 30px 0; font-size: 32px; font-weight: bold; letter-spacing: 10px; color: #000; }
        .footer { text-align: center; font-size: 13px; color: #888; margin-top: 40px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">💎 LuxGuard</div>
        </div>
        <div class="title">Security Verification Code</div>
        <p>Dear Customer,</p>
        <p>We noticed an unusual activity and need to verify your identity to proceed safely. Please use the following One-Time Password (OTP) to complete your transaction:</p>
        
        <div class="otp-box">
            {{ $otp }}
        </div>
        
        <p>This code will expire shortly. If you did not request this, please contact support immediately or ignore this email.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} LuxGuard. All rights reserved.<br>
            Premium Luxury E-commerce Security Engine
        </div>
    </div>
</body>
</html>
