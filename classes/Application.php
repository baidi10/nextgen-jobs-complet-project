<?php
// classes/Application.php
class Application {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Count all applications for a company
     */
    public function countCompanyApplications($companyId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(a.applicationId) 
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            WHERE j.companyId = :companyId
        ");
        $stmt->execute([':companyId' => $companyId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Count hired applicants for a company
     */
    public function countHired($companyId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(a.applicationId)
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            WHERE j.companyId = :companyId AND a.status = 'hired'
        ");
        $stmt->execute([':companyId' => $companyId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get application counts by status for a company
     * 
     * @param int $companyId Company ID
     * @return array Array of status counts indexed by status name
     */
    public function getApplicationStatusCounts($companyId) {
        $statusCounts = [
            'pending' => 0,
            'viewed' => 0,
            'interviewing' => 0,
            'hired' => 0,
            'rejected' => 0
        ];
        
        $stmt = $this->db->prepare("
            SELECT 
                a.status, 
                COUNT(a.applicationId) as count
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            WHERE j.companyId = :companyId
            GROUP BY a.status
        ");
        $stmt->execute([':companyId' => $companyId]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fill in the status counts array with actual data
        foreach ($results as $result) {
            $statusCounts[$result['status']] = (int)$result['count'];
        }
        
        return $statusCounts;
    }
    
    /**
     * Get monthly application counts for a company
     * 
     * @param int $companyId Company ID
     * @param int $months Number of months to get data for
     * @return array Monthly application counts
     */
    public function getMonthlyApplicationCounts($companyId, $months = 6) {
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(a.createdAt, '%Y-%m') as month,
                DATE_FORMAT(a.createdAt, '%b %Y') as month_name,
                COUNT(a.applicationId) as count
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            WHERE j.companyId = :companyId
            AND a.createdAt >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(a.createdAt, '%Y-%m'), DATE_FORMAT(a.createdAt, '%b %Y')
            ORDER BY month ASC
        ");
        
        $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent applicants for a company
     */
    public function getRecentApplicants($companyId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT 
                a.applicationId, a.userId, a.jobId, a.status, a.createdAt,
                j.jobTitle, u.firstName, u.lastName
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            JOIN users u ON a.userId = u.userId
            WHERE j.companyId = :companyId
            ORDER BY a.createdAt DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get total number of applications
     */
    public function getTotalApplications() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM applications");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total applications: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Create a new application
     */
    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO applications (
                    userId, jobId, coverLetter, resumePath,
                    status, createdAt, updatedAt
                ) VALUES (
                    :userId, :jobId, :coverLetter, :resumePath,
                    'pending', NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                ':userId' => $data['userId'],
                ':jobId' => $data['jobId'],
                ':coverLetter' => $data['coverLetter'],
                ':resumePath' => $data['resumePath']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating application: " . $e->getMessage());
            throw new Exception("Failed to submit application");
        }
    }
    
    public function getApplicationsForUser($userId, $status = null, $limit = null, $offset = 0) {
        $sql = "
            SELECT a.*, j.jobTitle, j.jobSlug, c.companyName
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            JOIN companies c ON j.companyId = c.companyId
            WHERE a.userId = :userId
        ";
        
        if ($status !== null) {
            $sql .= " AND a.status = :status";
        }
        
        $sql .= " ORDER BY a.createdAt DESC";
        
        // Add LIMIT and OFFSET for pagination
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        if ($status !== null) {
            $stmt->bindValue(':status', $status);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getApplicationsForJob($jobId, $status = null) {
        $sql = "
            SELECT a.*, u.firstName, u.lastName, u.email,
            FROM applications a
            JOIN users u ON a.userId = u.userId
            WHERE a.jobId = :jobId
        ";
        
        $params = [':jobId' => $jobId];
        
        if ($status !== null) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " ORDER BY a.createdAt DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Update application status
     */
    public function updateStatus($applicationId, $status) {
        try {
        $stmt = $this->db->prepare("
                UPDATE applications 
                SET status = ?, updatedAt = NOW() 
                WHERE applicationId = ?
        ");
            return $stmt->execute([$status, $applicationId]);
        } catch (PDOException $e) {
            error_log("Error updating application status: " . $e->getMessage());
            throw new Exception("Failed to update application status");
        }
    }
    
    /**
     * Get applicants for a specific job
     * 
     * @param int $jobId The ID of the job
     * @param string|null $statusFilter Filter applications by status
     * @return array Array of applicants
     */
    public function getApplicantsForJob($jobId, $statusFilter = 'all') {
        $sql = "
            SELECT 
                a.applicationId, a.userId, a.jobId, a.status, a.createdAt as appliedAt,
                a.resumeUrl as resumePath, 
                u.firstName, u.lastName, u.email
            FROM applications a
            JOIN users u ON a.userId = u.userId
            WHERE a.jobId = :jobId
        ";
        
        $params = [':jobId' => $jobId];
        
        if ($statusFilter !== 'all') {
            $sql .= " AND a.status = :status";
            $params[':status'] = $statusFilter;
        }
        
        $sql .= " ORDER BY a.createdAt DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debugging: Log the results to verify resumeUrl
        error_log('Applicants: ' . print_r($results, true));
        
        return $results;
    }
    
    /**
     * Apply for a job
     * 
     * @param int $userId ID of the user applying
     * @param int $jobId ID of the job being applied for
     * @param string $coverLetter Optional cover letter
     * @param string $resumePath Path to uploaded resume file
     * @return bool True on success, false on failure
     */
    public function apply($userId, $jobId, $coverLetter = '', $resumePath = '') {
        // Check if user already applied for this job
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM applications 
            WHERE userId = ? AND jobId = ?
        ");
        $stmt->execute([$userId, $jobId]);
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            throw new Exception('You have already applied for this job');
        }
        
        // Insert application
        $stmt = $this->db->prepare("
            INSERT INTO applications (
                userId, jobId, coverLetter, resumePath, applicationDate, status
            ) VALUES (
                ?, ?, ?, ?, NOW(), 'pending'
            )
        ");
        
        return $stmt->execute([
            $userId, 
            $jobId, 
            $coverLetter, 
            $resumePath
        ]);
    }

    /**
     * Get a single application by ID
     */
    public function getApplicationById($applicationId) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, j.jobTitle, c.companyName,
                       u.firstName, u.lastName, u.email
                FROM applications a
                JOIN jobs j ON a.jobId = j.jobId
                JOIN companies c ON j.companyId = c.companyId
                JOIN users u ON a.userId = u.userId
                WHERE a.applicationId = ?
            ");
            $stmt->execute([$applicationId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting application: " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param int $userId The user ID of the employer.
     * @return bool True if the application belongs to the employer's company, false otherwise.
     */
    public function isApplicationBelongToEmployer(int $applicationId, int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM applications a
                JOIN jobs j ON a.jobId = j.jobId
                JOIN companies c ON j.companyId = c.companyId
                WHERE a.applicationId = ? AND c.userId = ?
            ");
            $stmt->execute([$applicationId, $userId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking application ownership: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get application status breakdown for a user
     * 
     * @param int $userId User ID
     * @return array Array of status counts indexed by status name
     */
    public function getStatusBreakdown($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                status,
                COUNT(*) as count
            FROM applications
            WHERE userId = :userId
            GROUP BY status
        ");
        
        $stmt->execute([':userId' => $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Ensure all statuses are represented
        $statuses = [
            'pending' => 0,
            'hired' => 0,
            'rejected' => 0
        ];
        
        return array_merge($statuses, $results);
    }

    /**
     * Count applications for a user with optional status filter
     *
     * @param int $userId User ID
     * @param string|null $statusFilter Optional status to filter by
     * @return int Total count of applications
     */
    public function countUserApplications(int $userId, ?string $statusFilter = null): int
    {
        $sql = "SELECT COUNT(*) FROM applications WHERE userId = :userId";
        $params = [':userId' => $userId];

        if ($statusFilter !== null && $statusFilter !== '') {
            $sql .= " AND status = :statusFilter";
            $params[':statusFilter'] = $statusFilter;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Delete an application by ID
     *
     * @param int $applicationId The ID of the application to delete
     * @param int $userId The ID of the user performing the deletion (for security check)
     * @return bool True on success, false on failure or if the application doesn't belong to the user
     */
    public function deleteApplication(int $applicationId, int $userId): bool
    {
        try {
            // Optional: Add a check to ensure the application belongs to the user
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM applications WHERE applicationId = :applicationId AND userId = :userId");
            $stmtCheck->execute([':applicationId' => $applicationId, ':userId' => $userId]);
            if ($stmtCheck->fetchColumn() === 0) {
                return false; // Application doesn't belong to this user
            }

            $stmt = $this->db->prepare("DELETE FROM applications WHERE applicationId = :applicationId");
            return $stmt->execute([':applicationId' => $applicationId]);
        } catch (PDOException $e) {
            error_log("Error deleting application: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get applications for a job
     */
    public function getByJobId($jobId) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.firstName, u.lastName, u.email
                FROM applications a
                JOIN users u ON a.userId = u.userId
                WHERE a.jobId = ?
                ORDER BY a.createdAt DESC
            ");
            $stmt->execute([$jobId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting job applications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get applications for a user
     */
    public function getByUserId($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, j.title as jobTitle, c.companyName
                FROM applications a
                JOIN jobs j ON a.jobId = j.jobId
                JOIN companies c ON j.companyId = c.companyId
                WHERE a.userId = ?
                ORDER BY a.createdAt DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user applications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user has already applied to a job
     */
    public function hasApplied($userId, $jobId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM applications 
                WHERE userId = ? AND jobId = ?
            ");
            $stmt->execute([$userId, $jobId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking application: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get application statistics for a company
     */
    public function getCompanyStats($companyId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
                    SUM(CASE WHEN status = 'interviewing' THEN 1 ELSE 0 END) as interviewing,
                    SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM applications a
                JOIN jobs j ON a.jobId = j.jobId
                WHERE j.companyId = ?
            ");
            $stmt->execute([$companyId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting company stats: " . $e->getMessage());
            return null;
        }
    }
}