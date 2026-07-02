<?php
namespace Libs\PHPMailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
    private $mailer;
    private $config;

    public function __construct(array $config = []) {
        $this->mailer = new PHPMailer(true);
        $this->config = array_merge([
            'host' => 'smtp.example.com',
            'username' => 'your@example.com',
            'password' => 'yourpassword',
            'port' => 587,
            'encryption' => 'tls',
            'from_email' => 'noreply@example.com',
            'from_name' => 'Your App Name'
        ], $config);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = $this->config['encryption'];
        $this->mailer->Port = $this->config['port'];
        
        // Sender info
        $this->mailer->setFrom(
            $this->config['from_email'], 
            $this->config['from_name']
        );
    }

    public function send(
        string $to, 
        string $subject, 
        string $template, 
        array $data = []
    ): bool {
        try {
            // Recipient
            $this->mailer->addAddress($to);
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $this->renderTemplate($template, $data);
            $this->mailer->AltBody = strip_tags($this->renderTemplate($template, $data));

            $this->mailer->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Mailer Error: ' . $this->mailer->ErrorInfo);
            return false;
        }
    }

    private function renderTemplate(string $template, array $data): string {
        $templatePath = __DIR__ . '/email-templates/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Email template {$template} not found");
        }

        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }
}