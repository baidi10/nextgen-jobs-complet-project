<?php
// includes/config.php
session_start();

class Config {
    // Local XAMPP Database Configuration
    // If you need to use a remote database, update these values accordingly
    const DB_HOST = 'localhost';
    const DB_NAME = 'jobest';
    const DB_USER = 'root';
    const DB_PASS = '';
    const BASE_URL = 'http://localhost/nextgen-jobs';
    
    // Remote Database Configuration (commented out - uncomment if needed)
    // const DB_HOST = 'sql107.infinityfree.com';
    // const DB_NAME = 'if0_40610729_jobest';
    // const DB_USER = 'if0_40610729';
    // const DB_PASS = 'Oussamaba2004';
    
    // Wellfound-inspired color scheme
    const THEME_COLORS = [
        'primary' => '#2a5bd7',
        'primary-hover' => '#1e4bc2',
        'text' => '#2d3748',
        'text-light' => '#718096',
        'bg' => '#ffffff',
        'card-bg' => '#f8fafc',
        'border' => '#e2e8f0',
        'success' => '#22c55e',
        'danger' => '#ef4444'
    ];
    
    public static function getDB() {
        static $db = null;
        if ($db === null) {
            $dsn = 'mysql:host=' . self::DB_HOST . ';dbname=' . self::DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            try {
                $db = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('Database connection failed: ' . $e->getMessage());
                // Show more helpful error message for debugging
                $errorMsg = 'Database connection error. Please try again later.';
                if (defined('DEBUG') && DEBUG) {
                    $errorMsg .= '<br><small>Error: ' . htmlspecialchars($e->getMessage()) . '</small>';
                }
                die($errorMsg);
            }
        }
        return $db;
    }
}

// Email Configuration
const SMTP_HOST = 'smtp.example.com';
const SMTP_USER = 'your@email.com';
const SMTP_PASS = 'your_password';
const SMTP_PORT = 587;
const EMAIL_FROM = 'no-reply@jobest.com';
const APP_NAME = 'JOBEST';