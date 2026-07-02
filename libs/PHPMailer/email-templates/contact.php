<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Submission</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .message { 
            background-color: #f9f9f9; 
            padding: 15px; 
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Contact Form Submission</h2>
        <p>You've received a new message from <?= htmlspecialchars($name) ?> (<?= htmlspecialchars($email) ?>):</p>
        
        <div class="message">
            <?= nl2br(htmlspecialchars($message)) ?>
        </div>
        
        <p>Submitted on: <?= date('F j, Y \a\t g:i a') ?></p>
    </div>
</body>
</html>