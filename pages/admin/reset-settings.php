<?php
require_once '../../includes/config.php';
require_once '../../classes/Database.php';
require_once '../../classes/Auth.php';

// Check if user is authenticated and is an admin
$auth = new Auth();
if (!$auth->isLoggedIn() || $auth->getUserType() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// This should be a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Get current settings for backup (in case we need to restore)
    $stmt = $db->query("SELECT * FROM settings");
    $currentSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Begin transaction
    $db->beginTransaction();
    
    // Clear all settings
    $db->exec("DELETE FROM settings");
    
    // Insert default general settings
    $generalSettings = [
        ['general', 'site_name', 'JOBEST'],
        ['general', 'site_description', 'Find your dream job'],
        ['general', 'admin_email', 'admin@nextgen-jobs.com'],
        ['general', 'jobs_per_page', '10'],
        ['general', 'currency', 'USD'],
        ['general', 'enable_registration', '1'],
    ];
    
    // Insert default email settings
    $emailSettings = [
        ['email', 'smtp_host', 'smtp.example.com'],
        ['email', 'smtp_port', '587'],
        ['email', 'smtp_username', ''],
        ['email', 'smtp_password', ''],
        ['email', 'smtp_encryption', 'tls'],
        ['email', 'from_email', 'noreply@jobest.com'],
        ['email', 'from_name', 'JOBEST'],
        ['email', 'email_verification', '1'],
    ];
    
    // Insert default security settings
    $securitySettings = [
        ['security', 'password_min_length', '8'],
        ['security', 'require_special_chars', '1'],
        ['security', 'require_uppercase', '1'],
        ['security', 'require_numbers', '1'],
        ['security', 'login_attempts', '5'],
        ['security', 'lockout_time', '30'],
        ['security', 'enable_recaptcha', '0'],
    ];
    
    // Insert danger zone settings
    $dangerSettings = [
        ['danger', 'maintenance_mode', '0']
    ];
    
    // Combine all settings
    $allSettings = array_merge($generalSettings, $emailSettings, $securitySettings, $dangerSettings);
    
    // Prepare the insert statement
    $stmt = $db->prepare("INSERT INTO settings (settingGroup, settingKey, settingValue) VALUES (?, ?, ?)");
    
    // Insert all settings
    foreach ($allSettings as $setting) {
        $stmt->execute($setting);
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 