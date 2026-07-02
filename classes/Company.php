<?php
// classes/Company.php
require_once __DIR__ . '/Database.php';

class Company {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getCompanyIdByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT companyId FROM companies WHERE userId = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return $result ? $result['companyId'] : 0;
    }
    
    public function getName($companyId) {
        $stmt = $this->db->prepare("
            SELECT companyName FROM companies WHERE companyId = ?
        ");
        $stmt->execute([$companyId]);
        $result = $stmt->fetch();
        
        return $result ? $result['companyName'] : 'Your Company';
    }
    
    public function getProfileViews($companyId) {
        $stmt = $this->db->prepare("
            SELECT viewCount FROM companies WHERE companyId = ?
        ");
        $stmt->execute([$companyId]);
        $result = $stmt->fetch();
        
        return $result ? (int)$result['viewCount'] : 0;
    }
    
    /**
     * Increment the profile view count for a company
     * 
     * @param int $companyId Company ID
     * @return bool Success status
     */
    public function incrementProfileViews($companyId) {
        $stmt = $this->db->prepare("
            UPDATE companies 
            SET viewCount = viewCount + 1 
            WHERE companyId = ?
        ");
        return $stmt->execute([$companyId]);
    }
    
    public function getCompanyProfile($companyId) {
        $stmt = $this->db->prepare("
            SELECT 
                companyId, 
                companyName as name, 
                description, 
                industry, 
                employeeCount as size, 
                foundedYear, 
                headquarters as location, 
                websiteUrl as website, 
                logo 
            FROM companies 
            WHERE companyId = ?
        ");
        $stmt->execute([$companyId]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return [
                'name' => 'Your Company',
                'description' => '',
                'industry' => 'Technology',
                'size' => '',
                'foundedYear' => '',
                'location' => '',
                'website' => '',
                'logo' => 'default.png'
            ];
        }
        
        return $result;
    }
    
    public function create($userId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO companies (
                userId, companyName, companySlug, industry, employeeCount, foundedYear,
                description, headquarters, logo, websiteUrl, createdAt
            ) VALUES (
                :userId, :companyName, :companySlug, :industry, :employeeCount, :foundedYear,
                :description, :headquarters, :logo, :websiteUrl, NOW()
            )
        ");
        
        $slug = $this->createSlug($data['companyName']);
        
        $stmt->execute([
            ':userId' => $userId,
            ':companyName' => $data['companyName'],
            ':companySlug' => $slug,
            ':industry' => $data['industry'] ?? null,
            ':employeeCount' => $data['size'] ?? null,
            ':foundedYear' => $data['foundedYear'] ?? null,
            ':description' => $data['description'] ?? null,
            ':headquarters' => $data['location'] ?? null,
            ':logo' => $data['logo'] ?? null,
            ':websiteUrl' => $data['websiteUrl'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function update($companyId, $data) {
        $fields = [];
        $params = [':companyId' => $companyId];
        
        foreach ($data as $key => $value) {
            if ($key !== 'companyId' && $key !== 'userId') {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE companies SET " . implode(', ', $fields) . " WHERE companyId = :companyId";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    public function getDetails($companyId) {
        $stmt = $this->db->prepare("
            SELECT * FROM companies WHERE companyId = ?
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetch();
    }
    
    public function updateCompanyProfile($companyId, $data) {
        $sql = "UPDATE companies SET ";
        $updates = [];
        $params = [':companyId' => $companyId];
        
        if (isset($data['name'])) {
            $updates[] = "companyName = :name";
            $params[':name'] = $data['name'];
        }
        
        if (isset($data['description'])) {
            $updates[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        
        if (isset($data['industry'])) {
            $updates[] = "industry = :industry";
            $params[':industry'] = $data['industry'];
        }
        
        if (isset($data['size'])) {
            $updates[] = "employeeCount = :size";
            $params[':size'] = $data['size'];
        }
        
        if (isset($data['foundedYear'])) {
            $updates[] = "foundedYear = :foundedYear";
            $params[':foundedYear'] = $data['foundedYear'];
        }
        
        if (isset($data['location'])) {
            $updates[] = "headquarters = :location";
            $params[':location'] = $data['location'];
        }
        
        if (isset($data['website'])) {
            $updates[] = "websiteUrl = :website";
            $params[':website'] = $data['website'];
        }
        
        if (isset($data['logo'])) {
            $updates[] = "logo = :logo";
            $params[':logo'] = $data['logo'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql .= implode(', ', $updates) . " WHERE companyId = :companyId";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    public function uploadLogo($file) {
        // Use the correct server path for uploads (no BASE_URL)
        $uploadsDir = __DIR__ . '/../assets/uploads/company_logos';
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadsDir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload logo");
        }
        
        return $filename;
    }
    
    private function createSlug($name) {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $name), '-'));
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM companies WHERE companySlug LIKE ?");
        $stmt->execute([$slug . '%']);
        $count = $stmt->fetchColumn();
        
        return $count > 0 ? $slug . '-' . ($count + 1) : $slug;
    }
    
    /**
     * Search for companies with filtering and pagination
     * 
     * @param string $searchQuery The search query
     * @param string $industry Industry filter
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @return array Result with companies, pagination info
     */
    public function searchCompanies($searchQuery = '', $industry = '', $page = 1, $perPage = 12) {
        $params = [];
        $conditions = [];
        
        // Build search conditions
        if (!empty($searchQuery)) {
            $conditions[] = "(companyName LIKE ? OR description LIKE ?)";
            $searchTerm = "%$searchQuery%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($industry)) {
            $conditions[] = "industry = ?";
            $params[] = $industry;
        }
        
        // Build the WHERE clause
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        // Count total rows for pagination
        $countSql = "SELECT COUNT(*) FROM companies $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Calculate pagination
        $offset = ($page - 1) * $perPage;
        $pages = ceil($total / $perPage);
        
        // Get companies
        $companiesSql = "
            SELECT 
                companyId, 
                userId,
                companyName, 
                logo, 
                description, 
                industry, 
                employeeCount, 
                foundedYear,
                headquarters,
                websiteUrl,
                0 as isVerified
            FROM companies 
            $whereClause
            ORDER BY companyName ASC
            LIMIT $offset, $perPage
        ";
        
        $companiesStmt = $this->db->prepare($companiesSql);
        $companiesStmt->execute($params);
        $companies = $companiesStmt->fetchAll();
        
        return [
            'companies' => $companies,
            'total' => $total,
            'pages' => $pages,
            'currentPage' => $page
        ];
    }
    
    /**
     * Count total number of companies in the system
     *
     * @return int Number of companies
     */
    public function countCompanies() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM companies");
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get all unique industries from the database
     * 
     * @return array List of unique industries
     */
    public function getAllIndustries() {
        $stmt = $this->db->query("SELECT DISTINCT industry FROM companies WHERE industry IS NOT NULL AND industry != '' ORDER BY industry ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getActiveJobsCount($companyId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM jobs 
            WHERE companyId = ? AND isActive = 1
        ");
        $stmt->execute([$companyId]);
        return (int)$stmt->fetchColumn();
    }

    public function getTotalApplications($companyId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(a.applicationId) 
            FROM applications a
            JOIN jobs j ON a.jobId = j.jobId
            WHERE j.companyId = ?
        ");
        $stmt->execute([$companyId]);
        return (int)$stmt->fetchColumn();
    }
} 