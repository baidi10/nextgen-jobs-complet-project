<?php
require_once __DIR__ . "/../../../includes/config.php";
require_once __DIR__ . "/../../../includes/functions.php";
require_once __DIR__ . "/../../../classes/Database.php";
require_once __DIR__ . "/../../../classes/Auth.php";

$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    redirect('/pages/public/forgot-password.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match";
    } else {
        try {
            $auth = new Auth();
            $auth->resetPassword($token, $password);
            $success = "Your password has been reset successfully. You can now login.";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = "Reset Password - JOBEST";
include __DIR__ . "/../../../includes/header.php";
?>

<div class="auth-container py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-5">
              <img src="/assets/images/logo.svg" alt="JOBEST" width="120" class="mb-4">
              <h1 class="h4 fw-bold">Reset Your Password</h1>
              <p class="text-muted small">Enter your new password below</p>
            </div>

            <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
              <div class="text-center mt-4">
                <a href="/pages/public/login.php" class="btn btn-primary">Go to Login</a>
              </div>
            <?php else: ?>
              <form method="POST">
                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <input type="password" name="password" 
                         class="form-control form-control-lg" required>
                </div>

                <div class="mb-4">
                  <label class="form-label">Confirm Password</label>
                  <input type="password" name="confirm_password" 
                         class="form-control form-control-lg" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                  Reset Password
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . "/../../../includes/footer.php"; ?>