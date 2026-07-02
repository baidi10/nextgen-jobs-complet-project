<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/Auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    redirect('/pages/user/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $auth->sendPasswordReset($_POST['email']);
        $success = "Password reset instructions have been sent to your email";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = "Forgot Password - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="auth-container py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-5">
              <img src="<?= Config::BASE_URL ?>/assets/images/V5NR.png" alt="JOBEST" height="24" class="mb-4">
              <h1 class="h4 fw-bold">Reset your password</h1>
              <p class="text-muted small">Enter your email to receive reset instructions</p>
            </div>

            <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php else: ?>
              <form method="POST">
                <div class="mb-4">
                  <label class="form-label">Email address</label>
                  <input type="email" name="email" 
                         class="form-control form-control-lg" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                  Send Reset Instructions
                </button>

                <div class="text-center small mt-4">
                  Remember your password? 
                  <a href="login.php" class="text-decoration-none fw-bold">
                    Sign in
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>