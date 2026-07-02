<?php
// includes/auth.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        try {
        $auth = new Auth();
        $auth->logout();
        session_write_close(); // Ensure session is properly closed
        header('Location: ' . Config::BASE_URL . '/pages/public/index.php');
        exit;
    } catch (Exception $e) {
        error_log('Logout error: ' . $e->getMessage());
        $_SESSION['error'] = 'An error occurred during logout. Please try again.';
        header('Location: ' . Config::BASE_URL . '/pages/public/index.php');
        exit;
    }
}

// For other auth actions, initialize Auth class
$auth = new Auth();

// Handle password reset
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    if ($auth->$token) {
        header('Location: ' . Config::BASE_URL . '/pages/public/auth/reset-password.php?token=' . urlencode($token));
        exit;
    } else {
        $_SESSION['error'] = 'Invalid or expired reset token';
        header('Location: ' . Config::BASE_URL . '/pages/public/auth/forgot-password.php');
        exit;
    }
}