<?php
// includes/functions.php
require_once __DIR__ . '/../classes/Database.php';

function redirect($url) {
    error_log("Redirecting to: " . $url);
    
    // If URL doesn't start with http(s):// or /, prepend /
    if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
        $url = '/' . $url;
    }
    
    // For relative URLs (starting with /), add base URL
    if (strpos($url, '/') === 0 && strpos($url, 'http') !== 0) {
        if (defined('Config::BASE_URL')) {
            // Avoid double slashes
            if (substr(Config::BASE_URL, -1) === '/' && substr($url, 0, 1) === '/') {
                $url = Config::BASE_URL . substr($url, 1);
            } else {
                $url = Config::BASE_URL . $url;
            }
        } else {
            // Fallback to using HTTP_HOST if BASE_URL is not defined
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $url = $protocol . $_SERVER['HTTP_HOST'] . $url;
        }
    }
    
    error_log("Final redirect URL: " . $url);
    
    // Flush any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header("Location: $url");
    exit;
}

/**
 * Get the current user's information from the database
 *
 * @return array|null User information or null if not logged in
 */
function getCurrentUser() {
    static $currentUser = null;
    
    // Only fetch once per request
    if ($currentUser === null) {
        if (isset($_SESSION['user_id'])) {
            error_log("Fetching current user data for ID: " . $_SESSION['user_id']);
            
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT userId, email, firstName, lastName, userType FROM users WHERE userId = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($currentUser) {
                    error_log("User found: " . $currentUser['email'] . " | Type: " . $currentUser['userType']);
                } else {
                    error_log("No user found with ID: " . $_SESSION['user_id']);
                }
            } catch (Exception $e) {
                error_log("Error fetching user data: " . $e->getMessage());
                $currentUser = false;
            }
        } else {
            error_log("No user_id in session");
            $currentUser = false; // Not logged in
        }
    }
    
    return $currentUser ?: null;
}

/**
 * Update general settings in the database
 *
 * @param array $data Form data containing general settings
 * @return bool True on success
 * @throws Exception If an error occurs
 */
function updateGeneralSettings($data) {
    $db = Database::getInstance()->getConnection();
    
    // Validate inputs
    if (empty($data['site_name'])) {
        throw new Exception("Site name is required");
    }
    
    if (empty($data['admin_email']) || !filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Valid admin email is required");
    }
    
    $jobs_per_page = (int)$data['jobs_per_page'];
    if ($jobs_per_page < 5 || $jobs_per_page > 50) {
        throw new Exception("Jobs per page must be between 5 and 50");
    }
    
    // Process enable_registration checkbox
    $enable_registration = isset($data['enable_registration']) ? '1' : '0';
    
    // Settings to update
    $settings = [
        'site_name' => $data['site_name'],
        'site_description' => $data['site_description'],
        'admin_email' => $data['admin_email'],
        'jobs_per_page' => $jobs_per_page,
        'currency' => $data['currency'],
        'enable_registration' => $enable_registration
    ];
    
    // Update each setting
    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("
            INSERT INTO settings (settingGroup, settingKey, settingValue)
            VALUES ('general', ?, ?)
            ON DUPLICATE KEY UPDATE settingValue = VALUES(settingValue)
        ");
        $stmt->execute([$key, $value]);
    }
    
    return true;
}

/**
 * Update email settings in the database
 *
 * @param array $data Form data containing email settings
 * @return bool True on success
 * @throws Exception If an error occurs
 */
function updateEmailSettings($data) {
    $db = Database::getInstance()->getConnection();
    
    // Validate inputs
    if (empty($data['smtp_host'])) {
        throw new Exception("SMTP host is required");
    }
    
    $smtp_port = (int)$data['smtp_port'];
    if ($smtp_port < 1 || $smtp_port > 65535) {
        throw new Exception("SMTP port must be a valid port number");
    }
    
    if (empty($data['from_email']) || !filter_var($data['from_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Valid from email is required");
    }
    
    if (empty($data['from_name'])) {
        throw new Exception("From name is required");
    }
    
    // Process email_verification checkbox
    $email_verification = isset($data['email_verification']) ? '1' : '0';
    
    // Settings to update
    $settings = [
        'smtp_host' => $data['smtp_host'],
        'smtp_port' => $smtp_port,
        'smtp_username' => $data['smtp_username'],
        'smtp_password' => $data['smtp_password'],
        'smtp_encryption' => $data['smtp_encryption'],
        'from_email' => $data['from_email'],
        'from_name' => $data['from_name'],
        'email_verification' => $email_verification
    ];
    
    // Update each setting
    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("
            INSERT INTO settings (settingGroup, settingKey, settingValue)
            VALUES ('email', ?, ?)
            ON DUPLICATE KEY UPDATE settingValue = VALUES(settingValue)
        ");
        $stmt->execute([$key, $value]);
    }
    
    return true;
}

/**
 * Update security settings in the database
 *
 * @param array $data Form data containing security settings
 * @return bool True on success
 * @throws Exception If an error occurs
 */
function updateSecuritySettings($data) {
    $db = Database::getInstance()->getConnection();
    
    // Validate inputs
    $password_min_length = (int)$data['password_min_length'];
    if ($password_min_length < 6 || $password_min_length > 32) {
        throw new Exception("Password minimum length must be between 6 and 32");
    }
    
    $login_attempts = (int)$data['login_attempts'];
    if ($login_attempts < 1 || $login_attempts > 10) {
        throw new Exception("Login attempts must be between 1 and 10");
    }
    
    $lockout_time = (int)$data['lockout_time'];
    if ($lockout_time < 5 || $lockout_time > 1440) {
        throw new Exception("Lockout time must be between 5 and 1440 minutes");
    }
    
    // Process checkboxes
    $require_special_chars = isset($data['require_special_chars']) ? '1' : '0';
    $require_uppercase = isset($data['require_uppercase']) ? '1' : '0';
    $require_numbers = isset($data['require_numbers']) ? '1' : '0';
    $enable_recaptcha = isset($data['enable_recaptcha']) ? '1' : '0';
    
    // Settings to update
    $settings = [
        'password_min_length' => $password_min_length,
        'require_special_chars' => $require_special_chars,
        'require_uppercase' => $require_uppercase,
        'require_numbers' => $require_numbers,
        'login_attempts' => $login_attempts,
        'lockout_time' => $lockout_time,
        'enable_recaptcha' => $enable_recaptcha
    ];
    
    // Update each setting
    foreach ($settings as $key => $value) {
        $stmt = $db->prepare("
            INSERT INTO settings (settingGroup, settingKey, settingValue)
            VALUES ('security', ?, ?)
            ON DUPLICATE KEY UPDATE settingValue = VALUES(settingValue)
        ");
        $stmt->execute([$key, $value]);
    }
    
    return true;
}

/**
 * Get the current user's type from the database
 *
 * @return string|null User type or null if not logged in
 */
function getUserType() {
    // First check if user_type is in session
    if (isset($_SESSION['user_type'])) {
        error_log("Getting user type from session: " . $_SESSION['user_type']);
        return $_SESSION['user_type'];
    }
    
    // If not in session, get from database
    $user = getCurrentUser();
    
    if (!$user) {
        error_log("User not found in getCurrentUser()");
        return null;
    }
    
    if (isset($user['userType'])) {
        error_log("User type from database: " . $user['userType']);
        // Store in session for future use
        $_SESSION['user_type'] = $user['userType'];
        return $user['userType'];
    }
    
    error_log("No userType found in user data");
    return null;
}

/**
 * Check if user is logged in
 *
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && getCurrentUser() !== null;
}

/**
 * Check if current user is an admin
 *
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    $userType = getUserType();
    return $userType === 'admin';
}

/**
 * Check if current user is an employer
 *
 * @return bool True if employer, false otherwise
 */
function isEmployer() {
    $userType = getUserType();
    return $userType === 'employer';
}

/**
 * Check if current user is a job seeker
 *
 * @return bool True if job seeker, false otherwise
 */
function isJobSeeker() {
    $userType = getUserType();
    return $userType === 'jobSeeker';
}

/**
 * Get user full name
 *
 * @param int|null $userId User ID, defaults to current user
 * @return string|null User full name or null if not found
 */
function getUserName($userId = null) {
    if ($userId === null) {
        $user = getCurrentUser();
        if (!$user) return null;
        return $user['firstName'] . ' ' . $user['lastName'];
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT firstName, lastName FROM users WHERE userId = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user ? $user['firstName'] . ' ' . $user['lastName'] : null;
}

/**
 * Modified redirect based on user type that uses database
 */
function redirectBasedOnUserType() {
    // First check if user type is stored in session
    $userType = $_SESSION['user_type'] ?? null;
    
    // If not in session, try to get it from database
    if (!$userType) {
        $userType = getUserType();
    }
    
    error_log("Redirecting user with type: " . ($userType ?? 'unknown'));
    
    if (!$userType) {
        error_log("No user type found, redirecting to jobs page");
        redirect(Config::BASE_URL . '/pages/public/jobs.php');
    }
    
    switch ($userType) {
        case 'admin':
            error_log("Admin user detected, redirecting to admin dashboard");
            redirect(Config::BASE_URL . '/pages/admin/dashboard.php');
            break;
        case 'employer':
            error_log("Employer user detected, redirecting to employer dashboard");
            redirect(Config::BASE_URL . '/pages/employer/dashboard.php');
            break;
        case 'jobSeeker':
            error_log("Job seeker user detected, redirecting to user dashboard");
            redirect(Config::BASE_URL . '/pages/user/dashboard.php');
            break;
        default:
            error_log("Unknown user type: {$userType}, redirecting to user dashboard");
            redirect(Config::BASE_URL . '/pages/user/dashboard.php');
    }
}

function getUserAvatar($userId, $size = 40) {
    $db = Database::getInstance()->getConnection();
    
    // First check if user has a profile photo in job_seekers table
    $stmt = $db->prepare("
        SELECT js.photo 
        FROM job_seekers js 
        WHERE js.userId = ?
    ");
    $stmt->execute([$userId]);
    $photo = $stmt->fetchColumn();
    
    if ($photo && file_exists(__DIR__ . '/../assets/uploads/profiles/' . $photo)) {
        return Config::BASE_URL . '/assets/uploads/profiles/' . $photo;
    }
    
    // If no profile photo, get user email for default avatar
    $stmt = $db->prepare("SELECT email FROM users WHERE userId = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return Config::BASE_URL . '/assets/images/default-avatar.png';
    }
    
    // Generate avatar based on email
    require_once __DIR__ . '/../classes/Avatar.php';
    $avatar = new Avatar();
    return $avatar->generateDataURI($user['email'], $size);
}

/**
 * Get company avatar based on the owner's email
 * 
 * @param int $companyId Company ID
 * @param int $size Size of the avatar in pixels
 * @return string Data URI for the avatar
 */
function getCompanyAvatar($companyId, $size = 40) {
    // Initialize Avatar class if needed
    require_once __DIR__ . '/../classes/Avatar.php';
    
    // Get company owner's email
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT u.email 
        FROM companies c
        JOIN users u ON c.userId = u.userId
        WHERE c.companyId = ?
    ");
    $stmt->execute([$companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" style="border-radius:50%;"><rect width="' . $size . '" height="' . $size . '" fill="#757575"/><text x="50%" y="50%" dy=".1em" fill="white" text-anchor="middle" dominant-baseline="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="' . ($size * 0.5) . '">C</text></svg>');
    }
    
    // Generate avatar based on email
    $avatar = new Avatar();
    return $avatar->generateDataURI($company['email'], $size);
}

function displayInputValue($fieldName) {
    echo htmlspecialchars($_POST[$fieldName] ?? '');
}

function addAlert($type, $message) {
    $_SESSION['alerts'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function getAlerts() {
    $alerts = $_SESSION['alerts'] ?? [];
    unset($_SESSION['alerts']);
    return $alerts;
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime("@$datetime");
    $diff = $now->diff($ago);

    // Calculate weeks manually
    $weeks = floor($diff->d / 7);
    $days = $diff->d % 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        //'w' => 'week', // Not using this as it's not a native property
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    // Add weeks manually
    if ($weeks > 0) {
        $string['w'] = $weeks . ' week' . ($weeks > 1 ? 's' : '');
    }
    
    foreach ($string as $k => &$v) {
        if ($k !== 'w' && $diff->$k) { // Skip weeks as we handled it manually
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        }
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }
    
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function parsedown($text) {
    // Simple function to format text with basic HTML
    // Replace this with a proper Markdown parser if needed
    if (empty($text)) {
        return '';
    }
    
    // Convert markdown-style links [text](url)
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $text);
    
    // Convert markdown headings (##, ###)
    $text = preg_replace('/^## (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^### (.*?)$/m', '<h4>$1</h4>', $text);
    
    // Convert bullet points
    $text = preg_replace('/^\* (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/((?:<li>.*?<\/li>\s*)+)/', '<ul>$1</ul>', $text);
    
    // Convert line breaks to paragraphs
    $paragraphs = explode("\n\n", $text);
    foreach ($paragraphs as &$paragraph) {
        // Skip if already wrapped in HTML
        if (!preg_match('/^<(\w+)>/', $paragraph) && trim($paragraph) !== '') {
            $paragraph = '<p>' . nl2br($paragraph) . '</p>';
        }
    }
    
    return implode("\n", $paragraphs);
}

/**
 * Return Bootstrap color class based on application status
 */
function statusColor($status) {
    switch (strtolower($status)) {
        case 'pending':
            return 'warning';
        case 'reviewed':
        case 'viewed':
            return 'info';
        case 'interviewed':
        case 'interviewing':
            return 'primary';
        case 'offered':
        case 'hired':
            return 'success';
        case 'rejected':
            return 'danger';
        case 'active':
            return 'success';
        case 'expired':
            return 'warning';
        case 'closed':
            return 'secondary';
        default:
            return 'secondary';
    }
}

/**
 * Format time as a human-readable relative time (e.g., "2 hours ago")
 *
 * @param string $datetime Date/time string in MySQL format
 * @return string Human-readable relative time
 */
function timeElapsed($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Create a weeks property since DateInterval doesn't have one
    $weeks = floor($diff->days / 7);
    $days = $diff->days - ($weeks * 7);

    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    // Use special handling for weeks
    $values = [
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];

    foreach ($string as $k => &$v) {
        if ($values[$k]) {
            $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) : 'just now';
}

/**
 * Get Bootstrap color for user role badge
 *
 * @param string $role User role
 * @return string Bootstrap color class name
 */
function roleBadgeColor($role) {
    switch ($role) {
        case 'admin':
            return 'danger';
        case 'employer':
            return 'primary';
        case 'jobSeeker':
            return 'success';
        default:
            return 'secondary';
    }
}

/**
 * Get Bootstrap color for user status badge
 *
 * @param string $status User status
 * @return string Bootstrap color class name
 */
function statusBadgeColor($status) {
    switch ($status) {
        case 'active':
            return 'success';
        case 'pending':
            return 'warning';
        case 'suspended':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Generate a URL with proper base URL
 * 
 * @param string $path The path to append to the base URL
 * @return string The complete URL
 */
function siteUrl($path) {
    // Remove leading slash if present
    if (substr($path, 0, 1) === '/') {
        $path = substr($path, 1);
    }
    
    return Config::BASE_URL . '/' . $path;
}

// Update all URLs in existing HTML content
function updateUrlsInContent($content) {
    // Regular expression to match all href="/pages/..."
    $pattern = '/href=["\']\/pages\/([^"\']*)["\'](?!.*<?=)/i';
    
    // Replace with proper URLs
    return preg_replace_callback($pattern, function($matches) {
        return 'href="' . Config::BASE_URL . '/pages/' . $matches[1] . '"';
    }, $content);
}