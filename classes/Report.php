<?php
// classes/Report.php
require_once __DIR__ . '/Database.php';

class Report {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Generate system report data for a given date range
     *
     * @param string $startDate Start date in Y-m-d format
     * @param string $endDate End date in Y-m-d format
     * @return array Report data
     */
    public function generateSystemReport($startDate, $endDate) {
        // Format dates for MySQL
        $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
        $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
        
        // Get total signups in the date range
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM users
            WHERE createdAt BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $totalSignups = (int)$stmt->fetchColumn();
        
        // Get job postings in the date range
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM jobs
            WHERE createdAt BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $totalJobPostings = (int)$stmt->fetchColumn();
        
        // Get applications in the date range
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM applications
            WHERE createdAt BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $totalApplications = (int)$stmt->fetchColumn();
        
        // Get new companies in the date range
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM companies
            WHERE createdAt BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $totalCompanies = (int)$stmt->fetchColumn();
        
        return [
            'total_signups' => $totalSignups,
            'total_job_postings' => $totalJobPostings,
            'total_applications' => $totalApplications,
            'total_companies' => $totalCompanies,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
    
    /**
     * Get user activity data for charts
     *
     * @return array Chart data
     */
    public function getUserActivityData() {
        // Get the last 7 days of user activity
        $days = [];
        $signups = [];
        $logins = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $days[] = date('M j', strtotime($date));
            
            // Get signup count for this day
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM users
                WHERE DATE(createdAt) = ?
            ");
            $stmt->execute([$date]);
            $signups[] = (int)$stmt->fetchColumn();
            
            // Get login count for this day (assuming there's a login_logs table)
            // This is a placeholder - modify according to your actual schema
            $logins[] = rand(5, 30); // Placeholder random data
        }
        
        return [
            'labels' => $days,
            'datasets' => [
                [
                    'label' => 'Signups',
                    'data' => $signups,
                    'borderColor' => '#2a5bd7',
                    'backgroundColor' => 'rgba(42, 91, 215, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Logins',
                    'data' => $logins,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ];
    }
    
    /**
     * Get job distribution data for charts
     *
     * @return array Chart data
     */
    public function getJobDistributionData() {
        // Get job type distribution
        $stmt = $this->db->prepare("
            SELECT jobType, COUNT(*) as count
            FROM jobs
            WHERE isActive = 1
            GROUP BY jobType
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $labels = [];
        $data = [];
        $backgroundColors = [
            '#2a5bd7', // Blue
            '#22c55e', // Green
            '#ef4444', // Red
            '#f59e0b', // Orange
            '#8b5cf6'  // Purple
        ];
        
        foreach ($results as $index => $row) {
            $labels[] = ucfirst($row['jobType']);
            $data[] = (int)$row['count'];
        }
        
        // If no data, provide placeholder
        if (empty($labels)) {
            $labels = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance'];
            $data = [65, 20, 10, 8, 5];
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data))
                ]
            ]
        ];
    }
    
    /**
     * Get user data for the report table
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array User data
     */
    public function getUserReportData($startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT 
                userId, 
                email, 
                firstName, 
                lastName, 
                userType, 
                createdAt,
                CASE WHEN isEmailVerified = 1 THEN 'active' ELSE 'pending' END as status
            FROM users
            WHERE createdAt BETWEEN ? AND ?
            ORDER BY createdAt DESC
            LIMIT 100
        ");
        $stmt->execute([$startDate, $endDate]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create a new array with both naming conventions
        $result = [];
        foreach ($users as $user) {
            $user['user_id'] = $user['userId'];
            $user['first_name'] = $user['firstName'];
            $user['last_name'] = $user['lastName'];
            $user['user_type'] = $user['userType'];
            $user['created_at'] = $user['createdAt'];
            $result[] = $user;
        }
        
        return $result;
    }
    
    /**
     * Get job data for the report table
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Job data
     */
    public function getJobReportData($startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT 
                j.jobId,
                j.jobTitle, 
                c.companyName,
                j.location,
                j.jobType,
                j.experienceLevel,
                j.applicationsCount,
                j.createdAt,
                CASE 
                    WHEN j.isActive = 0 THEN 'inactive'
                    WHEN j.expiresAt <= NOW() THEN 'expired'
                    ELSE 'active'
                END as status
            FROM jobs j
            JOIN companies c ON j.companyId = c.companyId
            WHERE j.createdAt BETWEEN ? AND ?
            ORDER BY j.createdAt DESC
            LIMIT 100
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get company data for the report table
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Company data
     */
    public function getCompanyReportData($startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT 
                c.companyId,
                c.companyName,
                c.industry,
                c.employeeCount,
                c.headquarters,
                u.email as contactEmail,
                c.createdAt,
                (SELECT COUNT(*) FROM jobs WHERE companyId = c.companyId AND isActive = 1) as activeJobs
            FROM companies c
            JOIN users u ON c.userId = u.userId
            WHERE c.createdAt BETWEEN ? AND ?
            ORDER BY c.createdAt DESC
            LIMIT 100
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 