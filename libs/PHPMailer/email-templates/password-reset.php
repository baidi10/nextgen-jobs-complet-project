<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { 
            display: inline-block; padding: 10px 20px; 
            background-color: #4CAF50; color: white; 
            text-decoration: none; border-radius: 5px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hello <?= htmlspecialchars($name ?? 'User') ?>,</p>
        <p>We received a request to reset your password. Click the button below to reset it:</p>
        
        <p>
            <a href="<?= htmlspecialchars($resetUrl) ?>" class="button">
                Reset Password
            </a>
        </p>
        
        <p>This password reset link will expire in <?= $expiryHours ?> hours.</p>
        <p>If you didn't request a password reset, please ignore this email.</p>
        
        <p>Best regards,<br>Your App Team</p>
    </div>
</body>
</html>