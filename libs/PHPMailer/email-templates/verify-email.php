<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
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
        <h2>Verify Your Email Address</h2>
        <p>Hello <?= htmlspecialchars($name ?? 'User') ?>,</p>
        <p>Thank you for registering with us. Please click the button below to verify your email address:</p>
        
        <p>
            <a href="<?= htmlspecialchars($verificationUrl) ?>" class="button">
                Verify Email Address
            </a>
        </p>
        
        <p>If you did not create an account, no further action is required.</p>
        
        <p>Best regards,<br>Your App Team</p>
    </div>
</body>
</html>