<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $user = new User();
    $userId = $_SESSION['user_id'];
    
    try {
        $file = $_FILES['photo'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
        }
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File is too large. Maximum size is 5MB.');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $extension;
        
        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../assets/uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Move uploaded file
        $destination = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to save the uploaded file.');
        }
        
        // Update user's profile photo in database
        $user->updateJobSeekerPhoto($userId, $filename);
        
        // Redirect back to profile page with success message
        $_SESSION['success'] = 'Profile photo updated successfully';
        header('Location: ' . Config::BASE_URL . '/pages/user/profile.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ' . Config::BASE_URL . '/pages/user/profile.php');
        exit;
    }
}

// If we get here, something went wrong
header('Location: ' . Config::BASE_URL . '/pages/user/profile.php');
exit; 