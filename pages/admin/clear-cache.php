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

// Define path to cache directory
$cachePath = '../../cache/';

try {
    // Check if cache directory exists
    if (is_dir($cachePath)) {
        $clearedFiles = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cachePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        // Delete all files in the cache directory except .gitkeep
        foreach ($files as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getFilename() !== '.gitkeep') {
                unlink($fileinfo->getRealPath());
                $clearedFiles++;
            }
        }
        
        // Clear database query cache if any
        $db = Database::getInstance()->getConnection();
        $db->exec("RESET QUERY CACHE");
        
        echo json_encode([
            'success' => true, 
            'message' => "Cache cleared successfully. Removed {$clearedFiles} files."
        ]);
    } else {
        // Create cache directory if it doesn't exist
        mkdir($cachePath, 0755, true);
        echo json_encode(['success' => true, 'message' => 'Cache directory created']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 