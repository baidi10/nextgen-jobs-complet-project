<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();

// if ($auth->isLoggedIn()) {
//     header('Location: /pages/user/dashboard.php');
//     exit();
// }

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

// Get referrer from GET parameter if provided
if (isset($_GET['referrer']) && !empty($_GET['referrer'])) {
    $_SESSION['referrer_url'] = urldecode($_GET['referrer']);
}

// Get the referrer URL for the "Back" link and for after registration
$backUrl = $_SESSION['referrer_url'] ?? Config::BASE_URL . '/pages/public/index.php';

$error = '';
$success = '';
$formData = [
    'firstName' => '',
    'lastName' => '',
    'email' => '',
    'userType' => 'jobSeeker'
];

// Check if type parameter is set in URL
if (isset($_GET['type']) && $_GET['type'] === 'employer') {
    $formData['userType'] = 'employer';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $formData = [
            'firstName' => htmlspecialchars(trim($_POST['firstName'] ?? '')),
            'lastName' => htmlspecialchars(trim($_POST['lastName'] ?? '')),
            'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
            'userType' => in_array($_POST['userType'] ?? '', ['jobSeeker', 'employer']) 
                          ? $_POST['userType'] 
                          : 'jobSeeker'
        ];

        $password = $_POST['password'] ?? '';
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }

        $auth->register(
            $formData['email'],
            $password,
            [
                'firstName' => $formData['firstName'],
                'lastName' => $formData['lastName'],
                'userType' => $formData['userType']
            ]
        );
        
        $success = "Registration successful! Please check your email to verify your account.";
        $formData = [];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = "Register - JOBEST";
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
        
        .custom-option {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .custom-option:hover {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }
        
        .custom-option .form-check-input {
            margin-top: 0.3rem;
        }
        
        .custom-option .form-check-label {
            cursor: pointer;
        }
        
        .form-check-input:checked + .form-check-label {
            font-weight: 500;
        }
        
        .custom-option:has(.form-check-input:checked) {
            border-color: #000;
            background-color: #f8f9fa;
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
                                <h1 class="h3 fw-bold mb-2">Create Your Account</h1>
                                <p class="text-muted">Join thousands of tech professionals and companies</p>
                            </div>
                            
                            <div class="register-form">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                        <div class="d-flex">
                                            <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                                            <div><?= htmlspecialchars($error) ?></div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <?php if ($success): ?>
                                    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                                        <div class="d-flex">
                                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                                            <div><?= htmlspecialchars($success) ?></div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                    
                                    <div class="text-center my-5">
                                        <a href="<?= Config::BASE_URL ?>/pages/public/login.php?referrer=<?= urlencode($backUrl) ?>" class="btn btn-dark rounded-pill px-5 py-3 fw-medium">
                                            <i class="bi bi-arrow-right-circle me-2"></i> Continue to Login
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" class="needs-validation" novalidate>
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-6">
                                                <label for="firstName" class="form-label">First Name</label>
                                                <input type="text" 
                                                    class="form-control rounded-3" 
                                                    id="firstName" 
                                                    name="firstName" 
                                                    value="<?= htmlspecialchars($formData['firstName']) ?>" 
                                                    required
                                                    autofocus>
                                                <div class="invalid-feedback">
                                                    Please enter your first name.
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="lastName" class="form-label">Last Name</label>
                                                <input type="text" 
                                                    class="form-control rounded-3" 
                                                    id="lastName" 
                                                    name="lastName" 
                                                    value="<?= htmlspecialchars($formData['lastName']) ?>" 
                                                    required>
                                                <div class="invalid-feedback">
                                                    Please enter your last name.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" 
                                                class="form-control rounded-3" 
                                                id="email" 
                                                name="email" 
                                                value="<?= htmlspecialchars($formData['email']) ?>" 
                                                required>
                                            <div class="invalid-feedback">
                                                Please enter a valid email address.
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="password" class="form-label">Password</label>
                                            <div class="input-group mb-2">
                                                <input type="password" 
                                                    class="form-control rounded-start" 
                                                    id="password" 
                                                    name="password" 
                                                    required
                                                    minlength="8">
                                                <button type="button" class="btn btn-outline-secondary toggle-password px-3">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <div class="invalid-feedback">
                                                    Password must be at least 8 characters.
                                                </div>
                                            </div>
                                            <div class="form-text small text-muted">
                                                <i class="bi bi-info-circle me-1"></i> Minimum 8 characters with at least one number or special character
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label d-block mb-3">I'm a...</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-check custom-option border rounded-3 p-3">
                                                        <input class="form-check-input" type="radio" 
                                                            name="userType" 
                                                            id="jobSeeker" 
                                                            value="jobSeeker" 
                                                            <?= $formData['userType'] === 'jobSeeker' ? 'checked' : '' ?>>
                                                        <label class="form-check-label w-100" for="jobSeeker">
                                                            <i class="bi bi-person me-2"></i> Job Seeker
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-check custom-option border rounded-3 p-3">
                                                        <input class="form-check-input" type="radio" 
                                                            name="userType" 
                                                            id="employer" 
                                                            value="employer" 
                                                            <?= $formData['userType'] === 'employer' ? 'checked' : '' ?>>
                                                        <label class="form-check-label w-100" for="employer">
                                                            <i class="bi bi-briefcase me-2"></i> Employer
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-medium mb-4">
                                            Create Account
                                        </button>

                                        <div class="small text-center text-muted mb-4">
                                            By registering, you agree to our 
                                            <a href="<?= Config::BASE_URL ?>/pages/public/terms.php" class="text-decoration-none fw-medium">Terms of Service</a> and 
                                            <a href="<?= Config::BASE_URL ?>/pages/public/privacy.php" class="text-decoration-none fw-medium">Privacy Policy</a>
                                        </div>

                                    
                                        
                                    

                                        <div class="text-center text-muted">
                                            Already have an account? 
                                            <a href="<?= Config::BASE_URL ?>/pages/public/login.php?referrer=<?= urlencode($backUrl) ?>" class="text-decoration-none fw-medium">
                                                Sign in here
                                            </a>
                                        </div>
                                    </form>
                                <?php endif; ?>
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
        (function() {
            'use strict';
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation');
            
            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>