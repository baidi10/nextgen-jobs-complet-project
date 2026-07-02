<?php
// ajax/apply.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../classes/Application.php';
require_once __DIR__ . '/../classes/User.php';

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'jobSeeker') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    $jobId = filter_var($data['jobId'], FILTER_VALIDATE_INT);
    $coverLetter = filter_var($data['coverLetter'], FILTER_SANITIZE_STRING);
    
    if (!$jobId) {
        throw new Exception('Invalid job ID');
    }

    $application = new Application();
    $applicationId = $application->create(
        $_SESSION['user_id'],
        $jobId,
        $coverLetter,
        $data['resumePath'] ?? null
    );

    echo json_encode([
        'success' => true,
        'applicationId' => $applicationId
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}