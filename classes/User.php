<?php
// classes/User.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (email, passwordHash, firstName, lastName, userType, createdAt)
            VALUES (:email, :passwordHash, :firstName, :lastName, :userType, NOW())
        ");
        
        $stmt->execute([
            ':email' => $data['email'],
            ':passwordHash' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':firstName' => $data['firstName'],
            ':lastName' => $data['lastName'],
            ':userType' => $data['userType'] ?? 'jobSeeker'
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function findById($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE userId = :userId
        ");
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetch();
    }
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT * FROM users WHERE email = :email
        ");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
    
    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET ";
        $params = [':userId' => $userId];
        $updates = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, ['firstName', 'lastName', 'phoneNumber', 'location', 'bio', 'websiteUrl', 'linkedinUrl', 'githubUrl'])) {
                $updates[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        if (empty($updates)) return false;
        
        $sql .= implode(', ', $updates) . " WHERE userId = :userId";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function updatePassword($userId, $newPassword) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET passwordHash = :passwordHash 
            WHERE userId = :userId
        ");
        return $stmt->execute([
            ':passwordHash' => password_hash($newPassword, PASSWORD_BCRYPT),
            ':userId' => $userId
        ]);
    }
    
    public function setVerificationToken($userId, $token) {
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
    
    public function verifyEmail($token) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET isEmailVerified = 1, 
                verificationToken = NULL,
                verificationTokenExpires = NULL
            WHERE verificationToken = :token 
            AND verificationTokenExpires > NOW()
        ");
        $stmt->execute([':token' => $token]);
        return $stmt->rowCount() > 0;
    }
    
    public function getApplicationStats($userId) {
        $stats = [
            'totalApplications' => 0,
            'pending' => 0,
            'hired' => 0,
            'rejected' => 0,
            'savedJobs' => 0
        ];
        
        // Get application statistics
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM applications 
            WHERE userId = :userId
        ");
        
        $stmt->execute([':userId' => $userId]);
        $result = $stmt->fetch();
        
        if ($result) {
            $stats['totalApplications'] = (int)$result['total'];
            $stats['pending'] = (int)$result['pending'];
            $stats['hired'] = (int)$result['hired'];
            $stats['rejected'] = (int)$result['rejected'];
        }
        
        // Get saved jobs count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as savedCount
            FROM savedjobs
            WHERE userId = :userId
        ");
        $stmt->execute([':userId' => $userId]);
        $savedResult = $stmt->fetch();
        $stats['savedJobs'] = $savedResult ? (int)$savedResult['savedCount'] : 0;
        
        return $stats;
    }
    
    /**
     * Get user by ID
     * @param int $userId User ID
     * @return array|null User data or null if not found
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE userId = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Count total users
     * @return int Total number of users
     */
    public function countUsers() {
        try {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting users: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count recent signups
     * @param int $days Number of days to look back
     * @return int Number of recent signups
     */
    public function countRecentSignups($days) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE createdAt >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$days]);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting recent signups: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get recent activities
     * @param int $limit Number of activities to return
     * @return array Recent activities
     */
    public function getRecentActivities($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.userId,
                    u.userType,
                    u.firstName,
                    u.lastName,
                    u.createdAt,
                    CONCAT(u.firstName, ' ', u.lastName, ' created an account') as description
                FROM users u
                ORDER BY u.createdAt DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get growth data for charts
     * @return array Growth data
     */
    public function getGrowthData() {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) as count
                FROM users
                WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(createdAt)
                ORDER BY DATE(createdAt)
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Error getting growth data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get growth labels for charts
     * @return array Growth labels
     */
    public function getGrowthLabels() {
        try {
            $stmt = $this->db->query("
                SELECT DATE(createdAt) as date
                FROM users
                WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(createdAt)
                ORDER BY DATE(createdAt)
            ");
            $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map(function($date) {
                return date('M d', strtotime($date));
            }, $dates);
        } catch (Exception $e) {
            error_log("Error getting growth labels: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all users with pagination, search, role filtering, and date filtering
     *
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @param string $search Search query
     * @param string $roleFilter Role filter (all, jobSeeker, employer, admin)
     * @param string $dateFrom Start date for filtering
     * @param string $dateTo End date for filtering
     * @return array List of users
     */
    public function getAllUsers($page = 1, $perPage = 25, $search = '', $roleFilter = 'all', $dateFrom = '', $dateTo = '') {
        try {
            $params = [];
            $where = [];
            
            // Add search condition if search term provided
            if (!empty($search)) {
                $where[] = "(email LIKE :search OR firstName LIKE :search OR lastName LIKE :search)";
                $params[':search'] = "%{$search}%";
            }
            
            // Add role filter if not 'all'
            if ($roleFilter !== 'all') {
                $where[] = "userType = :userType";
                $params[':userType'] = $roleFilter;
            }

            // Add date range filter if dates provided
            if (!empty($dateFrom)) {
                $where[] = "createdAt >= :dateFrom";
                $params[':dateFrom'] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $where[] = "createdAt <= :dateTo";
                $params[':dateTo'] = $dateTo . ' 23:59:59'; // Include the entire day
            }
            
            // Build WHERE clause
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Prepare and execute query
            $sql = "
                SELECT 
                    userId as user_id,
                    email,
                    firstName as first_name,
                    lastName as last_name,
                    userType as user_type,
                    CASE 
                        WHEN isEmailVerified = 1 THEN 'active'
                        ELSE 'pending'
                    END as status,
                    createdAt as created_at
                FROM users
                {$whereClause}
                ORDER BY createdAt DESC
                LIMIT :offset, :perPage
            ";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind all parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind pagination parameters
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getAllUsers: " . $e->getMessage());
            throw new Exception("Error retrieving users: " . $e->getMessage());
        }
    }
    
    /**
     * Count all users with optional search, role filtering, and date filtering
     *
     * @param string $search Search query
     * @param string $roleFilter Role filter (all, jobSeeker, employer, admin)
     * @param string $dateFrom Start date for filtering
     * @param string $dateTo End date for filtering
     * @return int Total number of matching users
     */
    public function countAllUsers($search = '', $roleFilter = 'all', $dateFrom = '', $dateTo = '') {
        try {
            $params = [];
            $where = [];
            
            // Add search condition if search term provided
            if (!empty($search)) {
                $where[] = "(email LIKE :search OR firstName LIKE :search OR lastName LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            // Add role filter if not 'all'
            if ($roleFilter !== 'all') {
                $where[] = "userType = :userType";
                $params[':userType'] = $roleFilter;
            }

            // Add date range filter if dates provided
            if (!empty($dateFrom)) {
                $where[] = "createdAt >= :dateFrom";
                $params[':dateFrom'] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $where[] = "createdAt <= :dateTo";
                $params[':dateTo'] = $dateTo . ' 23:59:59'; // Include the entire day
            }
            
            // Build WHERE clause
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            // Prepare and execute count query
            $sql = "SELECT COUNT(*) FROM users $whereClause";
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error in countAllUsers: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get profile views statistics
     * 
     * @param int $userId User ID
     * @return array Profile views statistics
     */
    public function getProfileViews($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as totalViews,
                    COUNT(DISTINCT viewerId) as uniqueViewers,
                    MAX(viewedAt) as lastViewed
                FROM profile_views 
                WHERE viewedUserId = :userId
            ");
            
            $stmt->execute([':userId' => $userId]);
            $result = $stmt->fetch();
            
            return [
                'totalViews' => $result ? (int)$result['totalViews'] : 0,
                'uniqueViewers' => $result ? (int)$result['uniqueViewers'] : 0,
                'lastViewed' => $result ? $result['lastViewed'] : null
            ];
        } catch (PDOException $e) {
            // If table doesn't exist or other error, return default values
            return [
                'totalViews' => 0,
                'uniqueViewers' => 0,
                'lastViewed' => null
            ];
        }
    }

    /**
     * Get job search insights
     * 
     * @param int $userId User ID
     * @return array Job search insights
     */
    public function getJobSearchInsights($userId) {
        // Get most applied industry
        $stmt = $this->db->prepare("
            SELECT c.industry, COUNT(*) as count
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            JOIN companies c ON j.companyId = c.companyId
            WHERE a.userId = :userId
            GROUP BY c.industry
            ORDER BY count DESC
            LIMIT 1
        ");
        $stmt->execute([':userId' => $userId]);
        $topIndustry = $stmt->fetch();
        
        // Get average response time
        $stmt = $this->db->prepare("
            SELECT AVG(DATEDIFF(updatedAt, createdAt)) as avgResponseTime
            FROM applications
            WHERE userId = :userId 
            AND status != 'pending'
        ");
        $stmt->execute([':userId' => $userId]);
        $avgResponse = $stmt->fetch();
        
        // Get success rate (interviews + offers / total applications)
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('interviewed', 'offered') THEN 1 ELSE 0 END) as successful
            FROM applications
            WHERE userId = :userId
        ");
        $stmt->execute([':userId' => $userId]);
        $success = $stmt->fetch();
        
        return [
            'topIndustry' => $topIndustry ? $topIndustry['industry'] : 'N/A',
            'avgResponseTime' => $avgResponse ? round($avgResponse['avgResponseTime']) : 0,
            'successRate' => $success && $success['total'] > 0 ? 
                round(($success['successful'] / $success['total']) * 100) : 0
        ];
    }

    /**
     * Get job seeker profile data by User ID.
     *
     * @param int $userId The ID of the user.
     * @return array|false Job seeker data or false if not found.
     */
    public function getJobSeekerProfile(int $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM job_seekers WHERE userId = :userId");
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetch();
    }

    /**
     * Update job seeker profile data.
     *
     * @param int $userId The ID of the user.
     * @param array $data An associative array of data to update.
     * @return bool True on success, false on failure.
     */
    public function updateJobSeekerProfile(int $userId, array $data): bool
    {
        $updates = [];
        $params = [':userId' => $userId];

        $allowedFields = [
            'headline', 'currentPosition', 'currentCompany', 'educationLevel', 'yearsOfExperience',
            'resumeUrl', 'portfolioUrl', 'openToWork', 'openToRemote', 'desiredSalary',
            'desiredJobTypes', 'desiredLocations', 'photo' // Added 'photo'
        ];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                 // Handle boolean fields specifically if needed, e.g., for checkboxes
                 if ($key === 'openToWork' || $key === 'openToRemote') {
                     $updates[] = "`$key` = :$key";
                     $params[":$key"] = (int) $value; // Cast boolean to int
                 } else if ($value === null) {
                      $updates[] = "`$key` = NULL"; // Set to NULL if value is null
                 } else {
                    $updates[] = "`$key` = :$key";
                    $params[":$key"] = $value;
                 }
            }
        }

        if (empty($updates)) {
            return false; // No fields to update
        }

        $sql = "UPDATE job_seekers SET " . implode(', ', $updates) . " WHERE userId = :userId";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Update the profile photo path for a job seeker.
     *
     * @param int $userId The ID of the user.
     * @param string|null $photoPath The path to the new photo, or null to clear.
     * @return bool True on success, false on failure.
     */
    public function updateJobSeekerPhoto(int $userId, ?string $photoPath): bool
    {
        $sql = "UPDATE job_seekers SET photo = :photoPath WHERE userId = :userId";
        $stmt = $this->db->prepare($sql);
        $params = [':userId' => $userId, ':photoPath' => $photoPath];
        return $stmt->execute($params);
    }

    /**
     * Get recent applications for a user
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of applications to return
     * @return array Recent applications
     */
    public function getRecentApplications($userId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                a.applicationId,
                a.jobId,
                a.status,
                a.createdAt,
                j.jobTitle as jobTitle,
                c.companyName as companyName
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            JOIN companies c ON j.companyId = c.companyId
            WHERE a.userId = :userId
            ORDER BY a.createdAt DESC
            LIMIT :limit
        ");
        
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get all skills for a user
     *
     * @param int $userId The ID of the user
     * @return array Array of skills
     */
    public function getSkills($userId) {
        $stmt = $this->db->prepare("
            SELECT s.skillName 
            FROM skills s
            INNER JOIN user_skills us ON s.skillId = us.skillId
            WHERE us.userId = :userId
            ORDER BY s.skillName ASC
        ");
        
        $stmt->execute([':userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get total number of users
     */
    public function getTotalUsers() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total users: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get recent users with limit
     */
    public function getRecentUsers($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT userId, firstName, lastName, email, userType, createdAt 
                FROM users 
                ORDER BY createdAt DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent users: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user type distribution
     */
    public function getUserTypeDistribution() {
        try {
            $stmt = $this->db->query("
                SELECT userType, COUNT(*) as count 
                FROM users 
                GROUP BY userType
            ");
            $distribution = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $distribution[$row['userType']] = (int)$row['count'];
            }
            return $distribution;
        } catch (PDOException $e) {
            error_log("Error getting user type distribution: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly statistics for new users
     */
    public function getMonthlyStatistics() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(createdAt, '%Y-%m') as month,
                    DATE_FORMAT(createdAt, '%b %Y') as month_name,
                    COUNT(*) as new_users,
                    (SELECT COUNT(*) 
                     FROM jobs 
                     WHERE DATE_FORMAT(createdAt, '%Y-%m') = DATE_FORMAT(u.createdAt, '%Y-%m')
                    ) as new_jobs,
                    (SELECT COUNT(*) 
                     FROM applications 
                     WHERE DATE_FORMAT(createdAt, '%Y-%m') = DATE_FORMAT(u.createdAt, '%Y-%m')
                    ) as new_applications
                FROM users u
                WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY month
                ORDER BY month ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting monthly statistics: " . $e->getMessage());
            return [];
        }
    }

    public function updateUser($data) {
        try {
            $this->db->beginTransaction();
            
            // Update users table
            $sql = "UPDATE users SET 
                    email = :email,
                    firstName = :firstName,
                    lastName = :lastName,
                    userType = :userType,
                    phoneNumber = :phoneNumber,
                    location = :location,
                    bio = :bio,
                    websiteUrl = :websiteUrl,
                    linkedinUrl = :linkedinUrl,
                    githubUrl = :githubUrl";
            
            // Add password update if provided
            if (!empty($data['password'])) {
                $sql .= ", passwordHash = :passwordHash";
            }
            
            $sql .= " WHERE userId = :userId";
            
            $stmt = $this->db->prepare($sql);
            
            $params = [
                ':email' => $data['email'],
                ':firstName' => $data['firstName'],
                ':lastName' => $data['lastName'],
                ':userType' => $data['userType'],
                ':phoneNumber' => $data['phoneNumber'],
                ':location' => $data['location'],
                ':bio' => $data['bio'],
                ':websiteUrl' => $data['websiteUrl'],
                ':linkedinUrl' => $data['linkedinUrl'],
                ':githubUrl' => $data['githubUrl'],
                ':userId' => $data['userId']
            ];
            
            if (!empty($data['password'])) {
                $params[':passwordHash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            
            $stmt->execute($params);
            
            $this->db->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception("Error updating user: " . $e->getMessage());
        }
    }

    /**
     * Get the database connection
     * @return PDO Database connection
     */
    public function getConnection() {
        return $this->db;
    }
}