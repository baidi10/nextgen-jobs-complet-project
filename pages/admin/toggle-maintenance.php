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

// Get JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

if (!isset($data['maintenance_mode'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing maintenance_mode parameter']);
    exit;
}

$maintenanceMode = (int) $data['maintenance_mode'];

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if setting exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE settingGroup = 'danger' AND settingKey = 'maintenance_mode'");
    $stmt->execute();
    $settingExists = (bool) $stmt->fetchColumn();
    
    if ($settingExists) {
        // Update existing setting
        $stmt = $db->prepare("UPDATE settings SET settingValue = ? WHERE settingGroup = 'danger' AND settingKey = 'maintenance_mode'");
        $stmt->execute([$maintenanceMode]);
    } else {
        // Insert new setting
        $stmt = $db->prepare("INSERT INTO settings (settingGroup, settingKey, settingValue) VALUES ('danger', 'maintenance_mode', ?)");
        $stmt->execute([$maintenanceMode]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 