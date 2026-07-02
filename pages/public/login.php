<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();

// If user is already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    redirectBasedOnUserType();
    exit;
}

// Store referrer URL if coming from a page within the site
if (!isset($_SESSION['referrer_url'])) {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
        // Only store internal referrers
        $_SESSION['referrer_url'] = $_SERVER['HTTP_REFERER'];
    } else {
        // If external referrer or no referrer, set to home page
        $_SESSION['referrer_url'] = Config::BASE_URL . '/pages/public/index.php';
    }
}

// Get referrer from GET parameter if provided (e.g., from registration page)
if (isset($_GET['referrer']) && !empty($_GET['referrer'])) {
    $_SESSION['referrer_url'] = urldecode($_GET['referrer']);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Add debug output
        error_log("Login attempt initiated for: " . $_POST['email']);
        
        // Validate inputs
        if (empty($_POST['email']) || empty($_POST['password'])) {
            throw new Exception("Please enter both email and password");
        }
        
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address");
        }
        
        // Attempt login
        $result = $auth->login($_POST['email'], $_POST['password']);
        
        error_log("Login successful for: " . $_POST['email']);
        
        // Get user type from database and from session
        $dbUserType = getUserType();
        $sessionUserType = $_SESSION['user_type'] ?? 'not_set';
        error_log("User type from database: " . ($dbUserType ?? 'undefined'));
        error_log("User type from session: " . $sessionUserType);
        
        // Direct redirect based on user type after successful login
        if ($sessionUserType === 'jobSeeker') {
            error_log("Directing job seeker to index page.");
            redirect(Config::BASE_URL . '/pages/user/index.php');
        } else {
            // For other user types or if user type is not set unexpectedly, fall back
            // to the general redirect logic.
        redirectBasedOnUserType();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Login error: " . $error);
    }
}

$pageTitle = "Login - JOBEST";

// Get the referrer URL for the "Back" link
$backUrl = $_SESSION['referrer_url'] ?? Config::BASE_URL . '/pages/public/index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Inter font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- CSS -->
    <link href="<?= Config::BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= Config::BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" href="<?= Config::BASE_URL ?>/assets/images/V5NR.png" type="image/png">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 0;
            margin: 0;
        }
        
        .auth-container {
            width: 100%;
            padding: 2rem 0;
        }
        
        .rounded-4 {
            border-radius: 0.5rem;
        }
        
        .toggle-password {
            border-top-right-radius: 0.375rem !important;
            border-bottom-right-radius: 0.375rem !important;
        }
        
        .back-to-home {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s;
        }
        
        .back-to-home:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <a href="<?= htmlspecialchars($backUrl) ?>" class="back-to-home">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <a href="<?= Config::BASE_URL ?>/pages/public/index.php" class="d-inline-block mb-3">
                                    <img src="<?= Config::BASE_URL ?>/assets/images/V5NR.png" alt="JOBEST" height="40">
                                </a>
                                <h1 class="h3 fw-bold mb-2">Welcome Back</h1>
                                <p class="text-muted">Sign in to continue to your account</p>
                            </div>
                            
                            <div class="login-form">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                        <div class="d-flex">
                                            <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                                            <div><?= htmlspecialchars($error) ?></div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                
                                
                                
                                <form method="POST" class="needs-validation mt-5" novalidate>
                                    <div class="mb-4">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email"
                                                name="email" 
                                                class="form-control rounded-3" 
                                                id="email"
                                                placeholder="Enter your email" 
                                                required 
                                                autofocus>
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group mb-2">
                                            <input type="password"
                                                name="password" 
                                                class="form-control rounded-start" 
                                                id="password"
                                                placeholder="Enter your password" 
                                                required>
                                            <button type="button" class="btn btn-outline-secondary toggle-password px-3">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <div class="invalid-feedback">
                                                Please enter your password.
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <a href="<?= Config::BASE_URL ?>/pages/public/forgot-password.php" class="text-decoration-none small text-muted fw-medium">
                                                Forgot password?
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-medium mb-4">
                                        Sign In
                                    </button>
                                    
                                    <div class="text-center text-muted">
                                        Don't have an account? 
                                        <a href="<?= Config::BASE_URL ?>/pages/public/register.php" class="text-decoration-none fw-medium">
                                            Register here
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Update the eye icon
                this.querySelector('i').className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
        });

        // Form validation
        (function () {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>