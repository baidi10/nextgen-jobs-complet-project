<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'jobSeeker') {
    header('Location: ' . Config::BASE_URL . '/pages/public/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Get job ID
$jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : (isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0);
if ($jobId <= 0) {
    die('Invalid job ID.');
}

// Get DB connection
$db = Database::getInstance()->getConnection();

// Fetch job details for display
$jobStmt = $db->prepare('SELECT jobTitle, companyId FROM jobs WHERE jobId = ?');
$jobStmt->bindParam(1, $jobId, PDO::PARAM_INT);
$jobStmt->execute();
$job = $jobStmt->fetch(PDO::FETCH_ASSOC);
if (!$job) {
    die('Job not found.');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $coverLetter = trim($_POST['cover_letter'] ?? '');
    $resumePath = '';

    // Handle resume upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($_FILES['resume']['type'], $allowedTypes)) {
            $error = 'Invalid file type. Only PDF, DOC, and DOCX allowed.';
        } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
            $error = 'File too large. Max 5MB.';
        } else {
            $ext = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
            $resumePath = '/assets/uploads/resumes/' . uniqid('resume_') . '.' . $ext;
            $targetPath = __DIR__ . '/../../assets/uploads/resumes/';
            
            // Create directory if it doesn't exist
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }
            
            $targetFile = $targetPath . basename($resumePath);
            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $targetFile)) {
                $error = 'Failed to upload resume.';
            } else {
                // echo 'Resume uploaded successfully to ' . $targetFile;
            }
        }
    } else {
        echo 'No file uploaded or upload error.';
    }

    if (!$error) {
        // Insert application
        $insertStmt = $db->prepare('INSERT INTO applications (userId, jobId, coverLetter, resumeUrl, status, createdAt) VALUES (?, ?, ?, ?, "pending", NOW())');
        $insertStmt->bindParam(1, $userId, PDO::PARAM_INT);
        $insertStmt->bindParam(2, $jobId, PDO::PARAM_INT);
        $insertStmt->bindParam(3, $coverLetter, PDO::PARAM_STR);
        $insertStmt->bindParam(4, $resumePath, PDO::PARAM_STR);
        if ($insertStmt->execute()) {
            $success = 'Application submitted successfully!';
            header('Location: ' . Config::BASE_URL . '/pages/user/jobs.php?status=success&message=' . urlencode($success));
            exit;
        } else {
            $error = 'Failed to submit application.';
        }
    }
}

$pageTitle = 'Apply for ' . htmlspecialchars($job['jobTitle']);
include __DIR__ . '/../../includes/header.php';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4">Apply for <span class="fw-bold"><?php echo $pageTitle; ?></span></h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="job_id" value="<?php echo $jobId; ?>">
                        
                        <button type="submit" class="btn btn-dark w-100">Submit Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?> 