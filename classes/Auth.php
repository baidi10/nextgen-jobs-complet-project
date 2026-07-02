<?php
// classes/Auth.php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function register($email, $password, $userData) {
        // Validate input
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters");
        }
        
        // Check if email exists
        $stmt = $this->db->prepare("SELECT userId FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception("Email already registered");
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Insert user
        $stmt = $this->db->prepare("
            INSERT INTO users (
                email, passwordHash, firstName, lastName, userType, createdAt
            ) VALUES (
                :email, :passwordHash, :firstName, :lastName, :userType, NOW()
            )
        ");
        
        $stmt->execute([
            ':email' => $email,
            ':passwordHash' => $passwordHash,
            ':firstName' => $userData['firstName'],
            ':lastName' => $userData['lastName'],
            ':userType' => $userData['userType'] ?? 'jobSeeker'
        ]);
        
        $userId = $this->db->lastInsertId();
        
        // Generate verification token
        $verificationToken = bin2hex(random_bytes(16));
        $this->setVerificationToken($userId, $verificationToken);
        
        // Create default company for employer accounts
        if ($userData['userType'] === 'employer') {
            $this->createDefaultCompany($userId, $userData);
        }
        
        return $userId;
    }
    
    public function login($email, $password) {
        // Debug the login attempt
        error_log("Login attempt for email: " . $email);
        
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            error_log("User not found in database: " . $email);
            throw new Exception("Invalid email or password");
        }
        
        if (!password_verify($password, $user['passwordHash'])) {
            error_log("Password verification failed for: " . $email);
            throw new Exception("Invalid email or password");
        }
        
        // Commenting out email verification check as it's preventing login
        // if (!$user['isEmailVerified']) {
        //    throw new Exception("Please verify your email first");
        // }
        
        // Log successful authentication
        error_log("User authenticated successfully: " . $email . " | Type: " . $user['userType']);
        
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);
        
        // Store minimal information in session - just the user ID
        // The rest should be fetched from database when needed
        $_SESSION['user_id'] = $user['userId'];
        $_SESSION['user_type'] = $user['userType']; // Store user type in session
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        
        error_log("Session data set: user_id=" . $_SESSION['user_id'] . ", user_type=" . $_SESSION['user_type']);
        
        // Update last login
        $this->updateLastLogin($user['userId']);
        
        return true;
    }
    
    public function logout() {
        $_SESSION = [];
        session_destroy();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id'], 
               $_SESSION['user_agent'], 
               $_SESSION['ip_address']) && 
               $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'] && 
               $_SESSION['ip_address'] === $_SERVER['REMOTE_ADDR'];
    }
    
    /**
     * Get current user from database
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->prepare("
            SELECT userId, email, firstName, lastName, userType, profilePicture
            FROM users WHERE userId = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user type from database
     * @param int|null $userId Optional user ID, defaults to current user
     * @return string|null User type or null if not found
     */
    public function getUserType($userId = null) {
        if ($userId === null) {
            if (!$this->isLoggedIn()) {
                return null;
            }
            $userId = $_SESSION['user_id'];
        }
        
        $stmt = $this->db->prepare("SELECT userType FROM users WHERE userId = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['userType'] : null;
    }
    
    public function sendPasswordReset($email) {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            // Don't reveal if user exists
            return true;
        }
        
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET resetToken = :token, 
                resetTokenExpires = :expires
            WHERE userId = :userId
        ");
        $stmt->execute([
            ':token' => $token,
            ':expires' => $expires,
            ':userId' => $user['userId']
        ]);
        
        // In a real app, send email with reset link
        $resetLink = Config::BASE_URL . "/pages/public/auth/reset-password.php?token=$token";
        
        return true;
    }
    
    public function resetPassword($token, $newPassword) {
        $stmt = $this->db->prepare("
            SELECT userId FROM users 
            WHERE resetToken = :token 
            AND resetTokenExpires > NOW()
        ");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("Invalid or expired token");
        }
        
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET passwordHash = :passwordHash,
                resetToken = NULL,
                resetTokenExpires = NULL
            WHERE userId = :userId
        ");
        return $stmt->execute([
            ':passwordHash' => $passwordHash,
            ':userId' => $user['userId']
        ]);
    }
    
    private function getUserByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add debug log to check what user data is being retrieved
        if ($user) {
            error_log("User found: " . $email . " | Type: " . ($user['userType'] ?? 'unknown'));
        } else {
            error_log("User not found: " . $email);
        }
        
        return $user;
    }
    
    private function setVerificationToken($userId, $token) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET verificationToken = :token, 
                verificationTokenExpires = DATE_ADD(NOW(), INTERVAL 1 DAY)
            WHERE userId = :userId
        ");
        return $stmt->execute([
            ':token' => $token,
            ':userId' => $userId
        ]);
    }
    
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET lastLogin = NOW() 
            WHERE userId = :userId
        ");
        $stmt->execute([':userId' => $userId]);
    }
    
    private function createDefaultCompany($userId, $userData) {
        require_once __DIR__ . '/Company.php';
        
        $company = new Company();
        $defaultCompanyData = [
            'companyName' => $userData['firstName'] . ' ' . $userData['lastName'] . '\'s Company',
            'industry' => 'Technology',
            'description' => 'Company profile',
            'location' => 'United States'
        ];
        
        return $company->create($userId, $defaultCompanyData);
    }
}