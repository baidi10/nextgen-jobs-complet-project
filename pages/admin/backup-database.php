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

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Get database connection details
$dbConfig = Database::getInstance()->getConfig();

// Generate file name with timestamp
$timestamp = date('Y-m-d_H-i-s');
$filename = "jobest_backup_{$timestamp}.sql";

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// On Windows, use the MySQL executable directly
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // Path to mysqldump.exe - this might need to be adjusted based on your XAMPP installation
    $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    
    // Build the command
    $command = sprintf(
        '%s --host=%s --user=%s --password=%s %s',
        escapeshellarg($mysqldumpPath),
        escapeshellarg($dbConfig['host']),
        escapeshellarg($dbConfig['username']),
        escapeshellarg($dbConfig['password']),
        escapeshellarg($dbConfig['database'])
    );
} else {
    // For Linux/macOS
    $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s',
        escapeshellarg($dbConfig['host']),
        escapeshellarg($dbConfig['username']),
        escapeshellarg($dbConfig['password']),
        escapeshellarg($dbConfig['database'])
    );
}

// Execute the command and output directly to browser
passthru($command);

// Exit to prevent any other output
exit;
?> 