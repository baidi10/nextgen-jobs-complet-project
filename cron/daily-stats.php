<?php
// Generate daily statistics for the platform

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../classes/Database.php';

// Connect to database
$db = Database::getInstance()->getConnection();

// Get date for yesterday
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Collect statistics
$stats = [];

// New user registrations
$userStmt = $db->prepare("
    SELECT COUNT(*) as count, userType
    FROM users
    WHERE DATE(createdAt) = :date
    GROUP BY userType
");
$userStmt->execute([':date' => $yesterday]);
$stats['newUsers'] = $userStmt->fetchAll();

// New job postings
$jobStmt = $db->prepare("
    SELECT COUNT(*) as count
    FROM jobs
    WHERE DATE(createdAt) = :date
");
$jobStmt->execute([':date' => $yesterday]);
$stats['newJobs'] = $jobStmt->fetchColumn();

// New applications
$appStmt = $db->prepare("
    SELECT COUNT(*) as count
    FROM applications
    WHERE DATE(createdAt) = :date
");
$appStmt->execute([':date' => $yesterday]);
$stats['newApplications'] = $appStmt->fetchColumn();

// Store statistics in database
$insertStmt = $db->prepare("
    INSERT INTO dailyStats (
        date, newJobSeekers, newEmployers, newJobs, newApplications
    ) VALUES (
        :date, :newJobSeekers, :newEmployers, :newJobs, :newApplications
    )
");

// Extract user type counts
$newJobSeekers = 0;
$newEmployers = 0;

foreach ($stats['newUsers'] as $userType) {
    if ($userType['userType'] === 'jobSeeker') {
        $newJobSeekers = $userType['count'];
    } elseif ($userType['userType'] === 'employer') {
        $newEmployers = $userType['count'];
    }
}

$insertStmt->execute([
    ':date' => $yesterday,
    ':newJobSeekers' => $newJobSeekers,
    ':newEmployers' => $newEmployers,
    ':newJobs' => $stats['newJobs'],
    ':newApplications' => $stats['newApplications']
]);

// Log execution
$logStmt = $db->prepare("
    INSERT INTO cronLogs (cronName, executionTime, affectedRows, message)
    VALUES (:cronName, NOW(), :affectedRows, :message)
");

$logStmt->execute([
    ':cronName' => 'daily-stats',
    ':affectedRows' => 1,
    ':message' => "Generated statistics for $yesterday"
]);

echo "Daily statistics generated for $yesterday\n";

// Exit with success
exit(0);
