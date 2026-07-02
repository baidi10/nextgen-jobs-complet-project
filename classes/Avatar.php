<?php
// classes/Avatar.php
require_once __DIR__ . '/Database.php';

class Avatar {
    private $db;
    private $colors = [
        '#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#34495e',
        '#16a085', '#27ae60', '#2980b9', '#8e44ad', '#2c3e50',
        '#f1c40f', '#e67e22', '#e74c3c', '#00bcd4', '#95a5a6',
        '#f39c12', '#d35400', '#c0392b', '#bdc3c7', '#7f8c8d'
    ];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Generate an avatar based on email
     * 
     * @param string $email The email address
     * @param int $size Size of the avatar in pixels
     * @return string SVG code for the avatar
     */
    public function generateSVG($email, $size = 40) {
        // Get the first character of the email
        $firstChar = strtoupper(substr($email, 0, 1));
        
        // Generate a consistent color based on the email
        $color = $this->getColorFromEmail($email);
        
        // Create SVG with the character
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" style="border-radius:50%;">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="' . $color . '"/>';
        $svg .= '<text x="50%" y="50%" dy=".1em" fill="white" text-anchor="middle" dominant-baseline="middle" font-family="Arial, sans-serif" font-weight="bold" font-size="' . ($size * 0.5) . '">' . $firstChar . '</text>';
        $svg .= '</svg>';
        
        return $svg;
    }
    
    /**
     * Get a color based on the email
     * 
     * @param string $email The email address
     * @return string Hex color code
     */
    private function getColorFromEmail($email) {
        // Use a hash of the email to get a consistent color
        $hash = md5($email);
        $colorIndex = hexdec(substr($hash, 0, 8)) % count($this->colors);
        return $this->colors[$colorIndex];
    }
    
    /**
     * Generate a data URI for direct embedding in HTML
     * 
     * @param string $email The email address
     * @param int $size Size of the avatar in pixels
     * @return string Data URI for the avatar
     */
    public function generateDataURI($email, $size = 40) {
        $svg = $this->generateSVG($email, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Update all user avatars in the database with SVG data
     * 
     * @return int Number of updated users
     */
    public function updateAllUserAvatars() {
        // Get all users
        $stmt = $this->db->prepare("SELECT userId, email FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updateCount = 0;
        
        foreach ($users as $user) {
            $svg = $this->generateSVG($user['email']);
            
            // Update the profilePhoto field with the SVG code
            $updateStmt = $this->db->prepare("UPDATE users SET profilePhoto = ? WHERE userId = ?");
            $updateStmt->execute([$svg, $user['userId']]);
            
            $updateCount += $updateStmt->rowCount();
        }
        
        return $updateCount;
    }
    
    /**
     * Update all company avatars in the database with SVG data
     * 
     * @return int Number of updated companies
     */
    public function updateAllCompanyAvatars() {
        // Get all companies with their owner's email
        $stmt = $this->db->prepare("
            SELECT c.companyId, u.email 
            FROM companies c
            JOIN users u ON c.userId = u.userId
        ");
        $stmt->execute();
        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updateCount = 0;
        
        foreach ($companies as $company) {
            $svg = $this->generateSVG($company['email']);
            
            // Update the logo field with the SVG code
            $updateStmt = $this->db->prepare("UPDATE companies SET logo = ? WHERE companyId = ?");
            $updateStmt->execute([$svg, $company['companyId']]);
            
            $updateCount += $updateStmt->rowCount();
        }
        
        return $updateCount;
    }
} 