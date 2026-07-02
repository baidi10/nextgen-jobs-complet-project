<?php
// ajax/search.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../classes/Job.php';

header('Content-Type: application/json');

try {
    // Validate input
    $query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
    $filters = [
        'location' => filter_input(INPUT_GET, 'location', FILTER_SANITIZE_STRING),
        'jobType' => filter_input(INPUT_GET, 'jobType', FILTER_SANITIZE_STRING),
        'experience' => filter_input(INPUT_GET, 'experience', FILTER_SANITIZE_STRING)
    ];

    $job = new Job();
    $result = $job->searchJobs($query, $filters, 1, 10);

    echo json_encode([
        'success' => true,
        'results' => $result['jobs'],
        'total' => $result['total']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Search failed: ' . $e->getMessage()
    ]);
}