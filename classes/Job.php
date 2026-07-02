<?php
// classes/Job.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/Database.php';

class Job {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get total number of jobs
     */
    public function getTotalJobs() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM jobs");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total jobs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get active jobs count
     */
    public function getActiveJobs() {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(*) 
                FROM jobs 
                WHERE status = 'active' 
                AND expiryDate > NOW()
            ");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting active jobs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Create a new job listing
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO jobs (
                companyId, postedBy, jobTitle, jobSlug, jobDescription, 
                jobRequirements, jobBenefits, jobType, experienceLevel, 
                location, isRemote, salaryMin, salaryMax, salaryCurrency, 
                salaryPeriod, isSalaryVisible, expiresAt, createdAt
            ) VALUES (
                :companyId, :postedBy, :jobTitle, :jobSlug, :jobDescription, 
                :jobRequirements, :jobBenefits, :jobType, :experienceLevel, 
                :location, :isRemote, :salaryMin, :salaryMax, :salaryCurrency, 
                :salaryPeriod, :isSalaryVisible, DATE_ADD(NOW(), INTERVAL 30 DAY), NOW()
            )
        ");
        
        $slug = $this->createSlug($data['jobTitle']);
        
        $stmt->execute([
            ':companyId' => $data['companyId'],
            ':postedBy' => $data['postedBy'],
            ':jobTitle' => $data['jobTitle'],
            ':jobSlug' => $slug,
            ':jobDescription' => $data['jobDescription'],
            ':jobRequirements' => $data['jobRequirements'],
            ':jobBenefits' => $data['jobBenefits'] ?? null,
            ':jobType' => $data['jobType'],
            ':experienceLevel' => $data['experienceLevel'],
            ':location' => $data['location'],
            ':isRemote' => $data['isRemote'] ?? 0,
            ':salaryMin' => $data['salaryMin'] ?? null,
            ':salaryMax' => $data['salaryMax'] ?? null,
            ':salaryCurrency' => $data['salaryCurrency'] ?? 'USD',
            ':salaryPeriod' => $data['salaryPeriod'] ?? 'yearly',
            ':isSalaryVisible' => $data['isSalaryVisible'] ?? 1
        ]);
        
        $jobId = $this->db->lastInsertId();
        
        // Add skills if provided
        if (!empty($data['skills'])) {
            $this->addSkills($jobId, $data['skills']);
        }
        
        return $jobId;
    }
    
    /**
     * Get job by ID
     */
    public function getById($jobId) {
        try {
            $stmt = $this->db->prepare("
                SELECT j.*, c.companyName, c.industry
                FROM jobs j
                JOIN companies c ON j.companyId = c.companyId
                WHERE j.jobId = ?
            ");
            $stmt->execute([$jobId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting job by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update job listing
     */
    public function update($jobId, $data) {
        try {
            $updates = [];
            $params = [':jobId' => $jobId];

            foreach ($data as $key => $value) {
                if ($value !== null && $key !== 'jobId') {
                    $updates[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            if (empty($updates)) {
                return false;
            }

            $sql = "UPDATE jobs SET " . implode(', ', $updates) . ", updatedAt = NOW() WHERE jobId = :jobId";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error updating job: " . $e->getMessage());
            throw new Exception("Failed to update job listing");
        }
    }
    
    /**
     * Delete job listing
     */
    public function delete($jobId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM jobs WHERE jobId = ?");
            return $stmt->execute([$jobId]);
        } catch (PDOException $e) {
            error_log("Error deleting job: " . $e->getMessage());
            throw new Exception("Failed to delete job listing");
        }
    }
    
    /**
     * Search jobs with filters
     */
    public function search($filters = [], $page = 1, $perPage = 10) {
        try {
            $where = ["status = 'active'", "expiryDate > NOW()"];
            $params = [];

            if (!empty($filters['keyword'])) {
                $where[] = "(title LIKE :keyword OR description LIKE :keyword)";
                $params[':keyword'] = '%' . $filters['keyword'] . '%';
            }

            if (!empty($filters['location'])) {
                $where[] = "location LIKE :location";
                $params[':location'] = '%' . $filters['location'] . '%';
            }

            if (!empty($filters['jobType'])) {
                $where[] = "jobType = :jobType";
                $params[':jobType'] = $filters['jobType'];
            }

            if (!empty($filters['experienceLevel'])) {
                $where[] = "experienceLevel = :experienceLevel";
                $params[':experienceLevel'] = $filters['experienceLevel'];
            }

            // Handle category filter (from category cards)
            if (!empty($filters['category'])) {
                $where[] = "j.category = :category";
                $params[':category'] = $filters['category'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            $offset = ($page - 1) * $perPage;

            $sql = "
                SELECT j.*, c.companyName, c.industry,
                       (SELECT COUNT(*) FROM applications a WHERE a.jobId = j.jobId) as applicationCount
                FROM jobs j
                JOIN companies c ON j.companyId = c.companyId
                $whereClause
                ORDER BY j.createdAt DESC
                LIMIT :offset, :limit
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching jobs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count total jobs matching filters
     */
    public function countSearchResults($filters = []) {
        try {
            $where = ["status = 'active'", "expiryDate > NOW()"];
            $params = [];

            if (!empty($filters['keyword'])) {
                $where[] = "(title LIKE :keyword OR description LIKE :keyword)";
                $params[':keyword'] = '%' . $filters['keyword'] . '%';
            }

            if (!empty($filters['location'])) {
                $where[] = "location LIKE :location";
                $params[':location'] = '%' . $filters['location'] . '%';
            }

            if (!empty($filters['jobType'])) {
                $where[] = "jobType = :jobType";
                $params[':jobType'] = $filters['jobType'];
            }

            if (!empty($filters['experienceLevel'])) {
                $where[] = "experienceLevel = :experienceLevel";
                $params[':experienceLevel'] = $filters['experienceLevel'];
            }

            // Handle category filter (from category cards)
            if (!empty($filters['category'])) {
                $where[] = "j.category = :category";
                $params[':category'] = $filters['category'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT COUNT(*) FROM jobs j JOIN companies c ON j.companyId = c.companyId $whereClause";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting jobs: " . $e->getMessage());
            return 0;
        }
    }
    
    private function createSlug($title) {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $title), '-'));
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM jobs WHERE jobSlug LIKE ?");
        $stmt->execute([$slug . '%']);
        $count = $stmt->fetchColumn();
        
        return $count > 0 ? $slug . '-' . ($count + 1) : $slug;
    }
    
    public function addSkills($jobId, $skills) {
        $skills = array_unique($skills);
        $stmt = $this->db->prepare("
            INSERT INTO jobSkills (jobId, skillName, createdAt)
            VALUES (:jobId, :skillName, NOW())
        ");
        
        foreach ($skills as $skill) {
            $stmt->execute([
                ':jobId' => $jobId,
                ':skillName' => trim($skill)
            ]);
        }
    }
    
    public function getFeaturedJobs($limit = 6) {
        $stmt = $this->db->prepare("
            SELECT j.*, c.companyName, c.logo as companyLogo 
            FROM jobs j
            JOIN companies c ON j.companyId = c.companyId
            WHERE j.isActive = 1 AND j.isFeatured = 1
            ORDER BY j.createdAt DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function searchJobs($query = '', $filters = [], $page = 1, $perPage = 10) {
        // Basic setup - ensure page is at least 1 and properly cast to int
        $page = max(1, (int)$page);
        $perPage = (int)$perPage;
        $offset = ($page - 1) * $perPage;
        
        $whereConditions = [];
        $params = [];
        
        // Always include active jobs
        $whereConditions[] = "j.isActive = 1";
        
        // Handle search query
        if (!empty($query)) {
            // If query contains AND/OR operators, use it directly
            if (strpos($query, ' AND ') !== false || strpos($query, ' OR ') !== false) {
                $whereConditions[] = "($query)";
            } else {
                // Search only by jobTitle
                $whereConditions[] = "j.jobTitle LIKE :query";
                $params[':query'] = '%' . $query . '%';
            }
        }
        
        // Handle location filter
        if (!empty($filters['location'])) {
            $whereConditions[] = "j.location LIKE :location";
            $params[':location'] = '%' . $filters['location'] . '%';
        }
        
        // Handle job type filter
        if (!empty($filters['jobType'])) {
            if ($filters['jobType'] === 'remote') {
                $whereConditions[] = "j.isRemote = 1";
            } else {
                $whereConditions[] = "j.jobType = :jobType";
                $params[':jobType'] = $filters['jobType'];
            }
        }
        
        // Handle experience level filter
        if (!empty($filters['experienceLevel'])) {
            $whereConditions[] = "j.experienceLevel = :experienceLevel";
            $params[':experienceLevel'] = $filters['experienceLevel'];
        }
        
        // Handle company filter
        if (!empty($filters['companyId'])) {
            $whereConditions[] = "j.companyId = :companyId";
            $params[':companyId'] = (int)$filters['companyId'];
        }
        
        // Build the main SQL query
        $sql = "
            SELECT j.*, c.companyName, c.logo as companyLogo 
            FROM jobs j
            JOIN companies c ON j.companyId = c.companyId
            WHERE " . implode(' AND ', $whereConditions) . "
            ORDER BY j.createdAt DESC
            LIMIT :limit OFFSET :offset
        ";
        
        try {
            // Prepare and execute the main query
            $stmt = $this->db->prepare($sql);
            
            // Bind all filter parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind pagination parameters
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $jobs = $stmt->fetchAll();
            
            // Build count query for pagination
            $countSql = "
                SELECT COUNT(*) 
                FROM jobs j
                JOIN companies c ON j.companyId = c.companyId
                WHERE " . implode(' AND ', $whereConditions);
            
            $countStmt = $this->db->prepare($countSql);
            
            // Bind all filter parameters to count query
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            
            $countStmt->execute();
            $total = (int)$countStmt->fetchColumn();
            
            return [
                'jobs' => $jobs,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ];
        }
        catch (PDOException $e) {
            error_log("Search jobs error: " . $e->getMessage());
            return [
                'jobs' => [],
                'total' => 0,
                'pages' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getJobBySlug($slug) {
        $stmt = $this->db->prepare("
            SELECT j.*, c.companyName, c.description as companyDescription, 
                   c.websiteUrl as companyWebsite, c.logo as companyLogo
            FROM jobs j
            JOIN companies c ON j.companyId = c.companyId
            WHERE j.jobSlug = :slug
        ");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch();
    }
    
    public function getJobSkills($jobId) {
        $stmt = $this->db->prepare("
            SELECT skillName FROM jobSkills
            WHERE jobId = :jobId
            ORDER BY createdAt
        ");
        $stmt->execute([':jobId' => $jobId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    public function getTopCompanies($limit = 6) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.companyId, c.companyName as name, c.logo, c.companySlug as slug,
                       COUNT(j.jobId) as job_count
                FROM companies c
                LEFT JOIN jobs j ON c.companyId = j.companyId AND j.isActive = 1
                GROUP BY c.companyId, c.companyName, c.logo, c.companySlug
                ORDER BY job_count DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getTopCompanies: " . $e->getMessage());
            return []; // Return empty array instead of null to avoid foreach errors
        }
    }
    
    public function getJobById($jobId) {        
        $stmt = $this->db->prepare("
            SELECT j.*, c.companyName, c.description as companyDescription, 
                   c.websiteUrl as companyWebsite, c.logo as companyLogo,
                   c.foundedYear as companyFounded, c.employeeCount as companySize,
                   c.industry as companySector, c.linkedinUrl as companyLinkedIn
            FROM jobs j
            JOIN companies c ON j.companyId = c.companyId
            WHERE j.jobId = :jobId
        ");
        $stmt->execute([':jobId' => $jobId]);
        return $stmt->fetch();
    }
    
    /**
     * Count active jobs for a company or system-wide
     * 
     * @param int|null $companyId Optional company ID, if null returns system-wide count
     * @return int Number of active jobs
     */
    public function countActiveJobs($companyId = null) {
        if ($companyId !== null) {
            // Count for specific company
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM jobs 
                WHERE companyId = :companyId AND isActive = 1
            ");
            $stmt->execute([':companyId' => $companyId]);
        } else {
            // System-wide count
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM jobs WHERE isActive = 1");
            $stmt->execute();
        }
        
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get jobs for a specific company with pagination
     * 
     * @param int $companyId Company ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Jobs and pagination info
     */
    public function getJobsByCompany($companyId, $page = 1, $perPage = 10) {
        // Calculate offset for pagination
        $offset = ($page - 1) * $perPage;
        
        // Get jobs
        $stmt = $this->db->prepare("
            SELECT j.*, c.companyName, c.logo as companyLogo 
            FROM jobs j
            JOIN companies c ON j.companyId = c.companyId
            WHERE j.companyId = :companyId AND j.isActive = 1
            ORDER BY j.createdAt DESC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $jobs = $stmt->fetchAll();
        
        // Get total count for pagination
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM jobs 
            WHERE companyId = :companyId AND isActive = 1
        ");
        
        $stmt->execute([':companyId' => $companyId]);
        $total = $stmt->fetchColumn();
        
        return [
            'jobs' => $jobs,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'currentPage' => $page
        ];
    }
    
    /**
     * Get job performance data for charts
     * 
     * @param int $companyId The company ID
     * @param int $months Number of months to look back
     * @return array Performance data with month names and counts
     */
    public function getJobPerformance($companyId, $months = 6) {
        try {
            // First check if the job_monthly_stats table exists and has data
            $checkStmt = $this->db->prepare("SHOW TABLES LIKE 'job_monthly_stats'");
            $checkStmt->execute();
            $tableExists = $checkStmt->rowCount() > 0;
            
            if ($tableExists) {
                // Use the stored procedure if available
                try {
                    $stmt = $this->db->prepare("CALL GetJobPerformanceByCompany(?, ?)");
                    $stmt->execute([$companyId, $months]);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($results)) {
                        return $results;
                    }
                } catch (Exception $e) {
                    // Fallback to direct table query if procedure fails
                    $stmt = $this->db->prepare("
                        SELECT 
                            month_name,
                            SUM(count) as count,
                            SUM(views) as views
                        FROM 
                            job_monthly_stats
                        WHERE 
                            companyId = :companyId
                        GROUP BY 
                            month_date, month_name
                        ORDER BY 
                            month_date DESC
                        LIMIT :months
                    ");
                    $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
                    $stmt->bindValue(':months', $months, PDO::PARAM_INT);
                    $stmt->execute();
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($results)) {
                        return $results;
                    }
                }
            }
            
            // Fallback to original implementation using applications table
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(a.createdAt, '%Y-%m') as month,
                    DATE_FORMAT(a.createdAt, '%b') as month_name,
                    COUNT(a.applicationId) as count
                FROM applications a
                JOIN jobs j ON a.jobId = j.jobId
                WHERE j.companyId = :companyId
                AND a.createdAt >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(a.createdAt, '%Y-%m'), DATE_FORMAT(a.createdAt, '%b')
                ORDER BY month ASC
            ");
            $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindValue(':months', $months, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no data, provide some placeholder data for the full time range
            if (empty($results)) {
                $results = [];
                for ($i = $months-1; $i >= 0; $i--) {
                    $monthDate = date('Y-m-d', strtotime("-$i months"));
                    $results[] = [
                        'month' => date('Y-m', strtotime($monthDate)),
                        'month_name' => date('M', strtotime($monthDate)),
                        'count' => 0,
                        'views' => 0
                    ];
                }
            } else {
                // Fill in missing months with zeros and add view data
                $filledResults = [];
                $existingMonths = array_column($results, 'month');
                
                for ($i = $months-1; $i >= 0; $i--) {
                    $monthDate = date('Y-m-d', strtotime("-$i months"));
                    $monthKey = date('Y-m', strtotime($monthDate));
                    
                    if (in_array($monthKey, $existingMonths)) {
                        $index = array_search($monthKey, $existingMonths);
                        $result = $results[$index];
                        // Add views (approximately double the applications as a placeholder)
                        $result['views'] = isset($result['views']) ? $result['views'] : $result['count'] * 2;
                        $filledResults[] = $result;
                    } else {
                        $filledResults[] = [
                            'month' => $monthKey,
                            'month_name' => date('M', strtotime($monthDate)),
                            'count' => 0,
                            'views' => 0
                        ];
                    }
                }
                
                $results = $filledResults;
                usort($results, function($a, $b) {
                    return strcmp($a['month'], $b['month']);
                });
            }
            
            return $results;
        } catch (Exception $e) {
            error_log("Error in getJobPerformance: " . $e->getMessage());
            
            // Return empty fallback data on error
            $results = [];
            for ($i = $months-1; $i >= 0; $i--) {
                $monthDate = date('Y-m-d', strtotime("-$i months"));
                $results[] = [
                    'month_name' => date('M', strtotime($monthDate)),
                    'count' => 0,
                    'views' => 0
                ];
            }
            return $results;
        }
    }
    
    /**
     * Get recommended jobs based on user profile and previous applications
     */
    public function getRecommendedJobs($userId, $limit = 6) {
        try {
            // Get jobs based on user's previous applications and skills
            $sql = "
                SELECT DISTINCT j.*, c.companyName, c.logo as companyLogo 
                FROM jobs j
                JOIN companies c ON j.companyId = c.companyId
                LEFT JOIN (
                    SELECT jobId FROM applications WHERE userId = :userId
                ) a ON j.jobId = a.jobId
                WHERE j.isActive = 1 
                AND a.jobId IS NULL  -- Don't recommend jobs user already applied to
                AND j.expiresAt > NOW()
                ORDER BY j.createdAt DESC
                LIMIT :limit
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getRecommendedJobs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get jobs posted by a specific employer
     */
    public function getPostedJobsForEmployer($companyId, $status = 'active', $searchQuery = '', $limit = 10, $offset = 0) {
        $where = ['j.companyId = :companyId'];
        $params = [':companyId' => $companyId];
        
        // Filter by status
        if ($status !== 'all') {
            if ($status === 'active') {
                $where[] = "j.isActive = 1 AND j.expiresAt > NOW()";
            } elseif ($status === 'closed') {
                $where[] = "(j.isActive = 0 OR j.expiresAt <= NOW())";
            }
        }
        
        // Search query
        if (!empty($searchQuery)) {
            $where[] = "(j.jobTitle LIKE :search_query OR j.jobDescription LIKE :search_query)";
            $params[':search_query'] = "%$searchQuery%";
        }
        
        $sql = "
            SELECT 
                j.jobId,
                j.jobTitle as title,
                j.location,
                j.isActive,
                j.applicationsCount as applicationCount,
                j.createdAt,
                CASE 
                    WHEN j.isActive = 0 THEN 'closed'
                    WHEN j.expiresAt <= NOW() THEN 'expired'
                    ELSE 'active'
                END as status
            FROM jobs j
            WHERE " . implode(' AND ', $where) . "
            ORDER BY j.createdAt DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count jobs posted by a specific employer
     */
    public function countPostedJobsForEmployer($companyId, $status = 'active', $searchQuery = '') {
        $where = ['j.companyId = :companyId'];
        $params = [':companyId' => $companyId];
        
        // Filter by status
        if ($status !== 'all') {
            if ($status === 'active') {
                $where[] = "j.isActive = 1 AND j.expiresAt > NOW()";
            } elseif ($status === 'closed') {
                $where[] = "(j.isActive = 0 OR j.expiresAt <= NOW())";
            }
        }
        
        // Search query
        if (!empty($searchQuery)) {
            $where[] = "(j.jobTitle LIKE :search_query OR j.jobDescription LIKE :search_query)";
            $params[':search_query'] = "%$searchQuery%";
        }
        
        $sql = "
            SELECT COUNT(*) 
            FROM jobs j
            WHERE " . implode(' AND ', $where);
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Count weekly job applications
     *
     * @return int Number of applications in the past week
     */
    public function countWeeklyApplications() {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM applications 
                WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting weekly applications: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get labels for job post trend chart
     *
     * @return array Array of date labels
     */
    public function getPostTrendLabels() {
        try {
            $stmt = $this->db->prepare("
                SELECT DATE(createdAt) as date
                FROM jobs
                WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(createdAt)
                ORDER BY DATE(createdAt)
            ");
            $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return array_map(function($date) {
                return date('M d', strtotime($date));
            }, $dates);
        } catch (Exception $e) {
            error_log("Error getting post trend labels: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get data points for job post trend chart
     *
     * @return array Array of job post counts
     */
    public function getPostTrendData() {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM jobs
                WHERE createdAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(createdAt)
                ORDER BY DATE(createdAt)
            ");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("Error getting post trend data: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all jobs with pagination, search, and status filtering for admin
     *
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @param string $statusFilter Status filter (all, active, pending, expired)
     * @param string $search Search query
     * @return array List of jobs
     */
    public function getAllJobs($page = 1, $perPage = 25, $statusFilter = 'all', $search = '', $dateFrom = '', $dateTo = '') {
        $where = [];
        
        // Add status filter if not 'all'
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'active') {
                $where[] = "j.isActive = 1 AND j.expiresAt > NOW()";
            } elseif ($statusFilter === 'pending') {
                $where[] = "j.isActive = 0 AND j.expiresAt > NOW()";
            } elseif ($statusFilter === 'expired') {
                $where[] = "j.expiresAt <= NOW()";
            }
        }
        
        // Add date range filter
        if (!empty($dateFrom)) {
            $where[] = "DATE(j.createdAt) >= :dateFrom";
        }
        if (!empty($dateTo)) {
            $where[] = "DATE(j.createdAt) <= :dateTo";
        }
        
        // Add search condition if search term provided
        if (!empty($search)) {
            $where[] = "(j.jobTitle LIKE :search OR j.location LIKE :search OR c.companyName LIKE :search)";
        }
        
        // Build WHERE clause
        $whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Prepare and execute query
        $sql = "
            SELECT 
                j.jobId as id,
                j.jobTitle as title,
                j.location,
                j.applicationsCount as application_count,
                j.isFeatured as is_featured,
                j.createdAt as created_at,
                c.companyId as company_id,
                c.companyName as company_name,
                c.logo as company_logo,
                CASE 
                    WHEN j.isActive = 0 THEN 'pending'
                    WHEN j.expiresAt <= NOW() THEN 'expired'
                    ELSE 'active'
                END as status
            FROM jobs j
            JOIN companies c ON j.companyId = c.companyId
            $whereClause
            ORDER BY j.createdAt DESC
            LIMIT :limit OFFSET :offset
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            // Bind search parameter if exists
            if (!empty($search)) {
                $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }
            
            // Bind date parameters if they exist
            if (!empty($dateFrom)) {
                $stmt->bindValue(':dateFrom', $dateFrom, PDO::PARAM_STR);
            }
            if (!empty($dateTo)) {
                $stmt->bindValue(':dateTo', $dateTo, PDO::PARAM_STR);
            }
            
            // Bind pagination parameters
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAllJobs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count all jobs with optional status filtering and search
     *
     * @param string $statusFilter Status filter (all, active, pending, expired)
     * @param string $search Search query
     * @return int Total number of matching jobs
     */
    public function countAllJobs($statusFilter = 'all', $search = '', $dateFrom = '', $dateTo = '') {
        $where = [];
        
        // Add status filter if not 'all'
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'active') {
                $where[] = "j.isActive = 1 AND j.expiresAt > NOW()";
            } elseif ($statusFilter === 'pending') {
                $where[] = "j.isActive = 0 AND j.expiresAt > NOW()";
            } elseif ($statusFilter === 'expired') {
                $where[] = "j.expiresAt <= NOW()";
            }
        }
        
        // Add date range filter
        if (!empty($dateFrom)) {
            $where[] = "DATE(j.createdAt) >= :dateFrom";
        }
        if (!empty($dateTo)) {
            $where[] = "DATE(j.createdAt) <= :dateTo";
        }
        
        // Add search condition if search term provided
        if (!empty($search)) {
            $where[] = "(j.jobTitle LIKE :search OR j.location LIKE :search OR c.companyName LIKE :search)";
        }
        
        // Build WHERE clause
        $whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
        
        // Prepare and execute count query
        $sql = "
            SELECT COUNT(*) 
            FROM jobs j
            JOIN companies c ON j.companyId = c.companyId
            $whereClause
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            // Bind search parameter if exists
            if (!empty($search)) {
                $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }
            
            // Bind date parameters if they exist
            if (!empty($dateFrom)) {
                $stmt->bindValue(':dateFrom', $dateFrom, PDO::PARAM_STR);
            }
            if (!empty($dateTo)) {
                $stmt->bindValue(':dateTo', $dateTo, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in countAllJobs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Save a job for a user
     * 
     * @param int $userId The user ID
     * @param int $jobId The job ID
     * @return bool Whether saving was successful
     */
    public function saveJob($userId, $jobId) {
        // Check if already saved
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM savedjobs 
            WHERE userId = ? AND jobId = ?
        ");
        $stmt->execute([$userId, $jobId]);
        $exists = $stmt->fetchColumn() > 0;
        
        // If already saved, return true
        if ($exists) {
            return true;
        }
        
        // Insert new saved job
        $stmt = $this->db->prepare("
            INSERT INTO savedjobs (userId, jobId, createdAt)
            VALUES (?, ?, NOW())
        ");
        
        return $stmt->execute([$userId, $jobId]);
    }
    
    /**
     * Get saved jobs for a user
     *
     * @param int $userId The user ID
     * @param int $limit Number of jobs to return
     * @param int $offset Offset for pagination
     * @return array List of saved jobs
     */
    public function getSavedJobs($userId, $limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT j.jobId, j.jobTitle, j.location, j.companyId, 
                   c.companyName, c.logo as companyLogo, sj.createdAt
            FROM savedjobs sj
            JOIN jobs j ON sj.jobId = j.jobId
            JOIN companies c ON j.companyId = c.companyId
            WHERE sj.userId = ?
            ORDER BY sj.createdAt DESC
            LIMIT ?, ?
        ");
        $stmt->execute([$userId, $offset, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Count saved jobs for a user
     *
     * @param int $userId The user ID
     * @return int Number of saved jobs
     */
    public function countSavedJobs($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM savedjobs
            WHERE userId = :userId
        ");
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get popular search terms from job titles, locations, and skills
     * 
     * @param int $limit Maximum number of terms to return
     * @return array List of popular search terms with their counts
     */
    public function getPopularSearchTerms($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    jobTitle as term,
                    COUNT(*) as count
                FROM jobs
                GROUP BY jobTitle
                ORDER BY count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $terms = [];
            foreach ($results as $row) {
                $terms[$row['term']] = $row['count'];
            }
            return $terms;
        } catch (Exception $e) {
            error_log("Error getting popular search terms: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get job types from the database
     * 
     * @return array List of job types
     */
    public function getJobTypes() {
        return ['fullTime', 'partTime', 'contract', 'freelance', 'internship'];
    }
    
    /**
     * Get experience levels from the database
     * 
     * @return array List of experience levels
     */
    public function getExperienceLevels() {
        try {
            // Get unique experience levels from the jobs table
            $stmt = $this->db->prepare("
                SELECT DISTINCT experienceLevel
                FROM jobs
                WHERE isActive = 1 AND experienceLevel IS NOT NULL AND experienceLevel != ''
                ORDER BY COUNT(*) DESC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (Exception $e) {
            error_log("Error in getExperienceLevels: " . $e->getMessage());
            return ['entryLevel', 'midLevel', 'senior', 'executive']; // Fallback to defaults
        }
    }
    
    /**
     * Get popular job locations
     * 
     * @param int $limit Number of locations to return
     * @return array List of popular locations
     */
    public function getPopularLocations($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT location, COUNT(*) as location_count
                FROM jobs
                WHERE isActive = 1 AND location IS NOT NULL AND location != ''
                GROUP BY location
                ORDER BY location_count DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (Exception $e) {
            error_log("Error in getPopularLocations: " . $e->getMessage());
            return []; // Return empty array
        }
    }
    
    /**
     * Get all companies for dropdown selection
     * 
     * @return array List of companies
     */
    public function getAllCompanies() {
        try {
            $stmt = $this->db->prepare("
                SELECT companyId, companyName 
                FROM companies 
                ORDER BY companyName ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting companies: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update job listing using data array (expects 'jobId' in $data)
     */
    public function updateJob($data) {
        if (!isset($data['jobId'])) {
            throw new Exception('jobId is required for updateJob');
        }
        $jobId = $data['jobId'];
        // Remove jobId from data to avoid updating the primary key
        $updateData = $data;
        unset($updateData['jobId']);
        // Remove skills from updateData so it is not used in the SQL update
        $skills = [];
        if (isset($updateData['skills'])) {
            $skills = $updateData['skills'];
            unset($updateData['skills']);
        }
        $result = $this->update($jobId, $updateData);
        // Now update skills if provided
        if (!empty($skills)) {
            // First delete existing skills
            $stmt = $this->db->prepare("DELETE FROM jobSkills WHERE jobId = ?");
            $stmt->execute([$jobId]);
            // Then add new skills
            $this->addSkills($jobId, $skills);
        }
        return $result;
    }
    
    /**
     * Check if a job is saved by a user
     */
    public function isJobSaved($userId, $jobId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM savedjobs WHERE userId = ? AND jobId = ?");
        $stmt->execute([$userId, $jobId]);
        return $stmt->fetchColumn() > 0;
    }
}