<?php
// Expire jobs past their expiration date

// Load dependencies
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../classes/Database.php';

// Connect to database
$db = Database::getInstance()->getConnection();

// Update jobs that have passed their expiration date
$stmt = $db->prepare("
    UPDATE jobs 
    SET isActive = 0 
    WHERE expiresAt < NOW() 
    AND isActive = 1
");

$stmt->execute();

$jobsExpired = $stmt->rowCount();
echo "Expired jobs updated: $jobsExpired\n";

// Log the execution
$logStmt = $db->prepare("
    INSERT INTO cronLogs (cronName, executionTime, affectedRows, message)
    VALUES (:cronName, NOW(), :affectedRows, :message)
");

$logStmt->execute([
    ':cronName' => 'expire-jobs',
    ':affectedRows' => $jobsExpired,
    ':message' => "Updated $jobsExpired expired job listings"
]);

// Exit with success
exit(0);
