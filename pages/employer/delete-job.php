<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Company.php';
require_once __DIR__ . '/../../classes/Job.php';

// Authentication check using helper functions
if (!isLoggedIn() || !isEmployer()) {
    header('Location: /pages/public/login.php');
    exit;
}

// Check if job ID is provided (handle both GET and POST)
$jobId = isset($_POST['id']) ? $_POST['id'] : (isset($_GET['id']) ? $_GET['id'] : null);

if (!$jobId || !is_numeric($jobId)) {
    $_SESSION['error_message'] = "No job ID specified for deletion.";
    header('Location: manage-jobs.php');
    exit;
}

try {
    $jobId = (int)$jobId;
    $db = Database::getInstance()->getConnection();
    $company = new Company();
    
    // Get current user's company ID
    $userId = $_SESSION['user_id'];
    $companyId = $company->getCompanyIdByUser($userId);
    
    // First, verify the job belongs to this company and get its details
    $stmt = $db->prepare("SELECT jobId, jobTitle, companyId FROM jobs WHERE jobId = :jobId");
    $stmt->execute([':jobId' => $jobId]);
    $jobDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$jobDetails) {
        throw new Exception("Job with ID $jobId not found.");
    }
    
    if ($jobDetails['companyId'] != $companyId) {
        throw new Exception("You don't have permission to delete this job. It belongs to another company.");
    }
    
    // Begin transaction for deletion to ensure all related data is deleted
    $db->beginTransaction();
    
    try {
        // Delete related job skills
        $stmt = $db->prepare("DELETE FROM jobSkills WHERE jobId = :jobId");
        $stmt->execute([':jobId' => $jobId]);
        
        // Delete any applications for this job
        $stmt = $db->prepare("DELETE FROM applications WHERE jobId = :jobId");
        $stmt->execute([':jobId' => $jobId]);
        
        // Delete saved jobs entries
        // Saved jobs table check removed as it doesn't exist in the database
        
        // Finally delete the job itself
        $stmt = $db->prepare("DELETE FROM jobs WHERE jobId = :jobId");
        $stmt->execute([':jobId' => $jobId]);
        
        // Commit transaction
        $db->commit();
        
        $_SESSION['success_message'] = "Job '{$jobDetails['jobTitle']}' has been deleted successfully.";
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        throw new Exception("Failed to delete the job: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}

// Redirect back to manage jobs page
header('Location: manage-jobs.php');
exit; 