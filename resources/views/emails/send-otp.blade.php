<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .otp-box { background: #f4f4f4; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px; }
        .otp-code { font-size: 32px; font-weight: bold; color: #333; letter-spacing: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello {{ $user->first_name }},</h2>
        <p>Your OTP code for HireRight is:</p>
        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
        </div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you didn't request this code, please ignore this email.</p>
        <br>
        <p>Best regards,<br>The HireRight Team</p>
    </div>
</body>
</html>