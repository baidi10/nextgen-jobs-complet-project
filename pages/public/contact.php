<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php'; // Add Database class
require_once __DIR__ . '/../../vendor/autoload.php'; // Add autoloader for Composer packages

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill all required fields";
    } else {
        // Send email using PHPMailer
        try {
            $mail = new PHPMailer(true);
            
            // Get email settings from database
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT settingKey, settingValue FROM settings WHERE settingGroup = 'email'");
            $emailSettings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $emailSettings[$row['settingKey']] = $row['settingValue'];
            }
            
            // Get general settings for admin email
            $stmt = $db->query("SELECT settingValue FROM settings WHERE settingGroup = 'general' AND settingKey = 'admin_email'");
            $adminEmail = $stmt->fetchColumn() ?: 'contact@jobest.com';
            
            // Try to use SMTP first
            $useSMTP = false;
            if (!empty($emailSettings['smtp_host']) && 
                !empty($emailSettings['smtp_username']) && 
                !empty($emailSettings['smtp_password'])) {
                $useSMTP = true;
                
                $mail->isSMTP();
                $mail->Host = $emailSettings['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $emailSettings['smtp_username'];
                $mail->Password = $emailSettings['smtp_password'];
                $mail->SMTPSecure = $emailSettings['smtp_encryption'] ?? 'tls';
                $mail->Port = (int)($emailSettings['smtp_port'] ?? 587);
            }
            
            // Set content type and sender/receiver info
            $mail->isHTML(true);
            $mail->setFrom($emailSettings['from_email'] ?? 'noreply@jobest.com', $emailSettings['from_name'] ?? 'JOBEST');
            $mail->addAddress($adminEmail);
            $mail->Subject = "New Contact Form Submission";
            $mail->Body = "
                <h3>New Contact Message</h3>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Message:</strong></p>
                <p>$message</p>
            ";
            $mail->AltBody = "New Contact Message\n\nName: $name\nEmail: $email\n\nMessage:\n$message";

            // Try to send using PHPMailer
            try {
                $mail->send();
                $success = "Your message has been sent successfully!";
            } catch (Exception $e) {
                // If SMTP fails, try using PHP's mail() function
                if ($useSMTP) {
                    $headers = "From: " . $emailSettings['from_email'] . "\r\n";
                    $headers .= "Reply-To: $email\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    
                    $mailContent = "
                        <h3>New Contact Message</h3>
                        <p><strong>Name:</strong> $name</p>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Message:</strong></p>
                        <p>$message</p>
                    ";
                    
                    if (mail($adminEmail, "New Contact Form Submission", $mailContent, $headers)) {
                        $success = "Your message has been sent successfully using PHP mail!";
                    } else {
                        // Store in database as fallback
                        $stmt = $db->prepare("
                            INSERT INTO contact_messages (name, email, message, created_at)
                            VALUES (?, ?, ?, NOW())
                        ");
                        
                        // Check if table exists and create it if not
                        $tableExists = $db->query("SHOW TABLES LIKE 'contact_messages'")->rowCount() > 0;
                        if (!$tableExists) {
                            $db->exec("
                                CREATE TABLE contact_messages (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    name VARCHAR(100) NOT NULL,
                                    email VARCHAR(255) NOT NULL,
                                    message TEXT NOT NULL,
                                    is_read TINYINT(1) DEFAULT 0,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                            ");
                        }
                        
                        $stmt->execute([$name, $email, $message]);
                        $success = "We've received your message and saved it for review! Our team will get back to you soon.";
                    }
                } else {
                    throw $e; // Re-throw if not SMTP error
                }
            }
        } catch (Exception $e) {
            $error = "Message could not be sent. Please try again later or contact us directly at contact@jobest.com";
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}

$pageTitle = "Contact Us - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-sm-5">
          <h1 class="h3 fw-bold mb-4">Contact Us</h1>
          
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          
          <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
          <?php else: ?>
            <form method="POST">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Name</label>
                  <input type="text" name="name" 
                         class="form-control" required
                         value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" 
                         class="form-control" required
                         value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" 
                          rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary btn-lg">
                Send Message
              </button>
            </form>
          <?php endif; ?>

          <hr class="my-5">

          <div class="row g-4">
            <div class="col-md-6">
              <h3 class="h5 fw-bold mb-3">Office</h3>
              <p class="text-muted mb-0">
                123 Errachidia<br>
               WH69+XPR Errachidia, CA 94107<br>
                MOROCCO
              </p>
            </div>
            
            <div class="col-md-6">
              <h3 class="h5 fw-bold mb-3">Contact Info</h3>
              <ul class="list-unstyled">
                <li class="mb-2">
                  <i class="bi bi-envelope me-2"></i>
                  youssef-baidi@jobest.com
                </li>
                <li class="mb-2">
                  <i class="bi bi-telephone me-2"></i>
                  +212 640520877
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>