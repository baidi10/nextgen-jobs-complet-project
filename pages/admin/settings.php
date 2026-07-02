<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: /pages/public/login.php');
    exit;
}

$error = '';
$success = '';

// Get the current tab from the URL, default to 'general'
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle different setting groups
        switch ($_POST['settings_group']) {
            case 'general':
                updateGeneralSettings($_POST);
                $success = "General settings updated";
                break;
                
            case 'email':
                updateEmailSettings($_POST);
                $success = "Email settings updated";
                break;
                
            case 'security':
                updateSecuritySettings($_POST);
                $success = "Security settings updated";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = "System Settings - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="container py-5">
    <div class="row">
      <!-- Settings Navigation -->
      <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:120px; z-index:1020;">
          <div class="card-body">
          <nav class="nav flex-column">
  <a class="nav-link <?= ($tab == 'general') ? 'active fw-bold' : '' ?>" href="?tab=general">
    <i class="bi bi-gear me-2"></i>General
  </a>
  <a class="nav-link <?= ($tab == 'email') ? 'active fw-bold' : '' ?>" href="?tab=email">
    <i class="bi bi-envelope me-2"></i>Email
  </a>
  <a class="nav-link <?= ($tab == 'security') ? 'active fw-bold' : '' ?>" href="?tab=security">
    <i class="bi bi-shield-lock me-2"></i>Security
  </a>
  <a class="nav-link <?= ($tab == 'danger-zone') ? 'active fw-bold text-danger' : 'text-danger' ?>" href="?tab=danger-zone">
    <i class="bi bi-exclamation-octagon me-2"></i>Danger Zone
  </a>
</nav>
          </div>
        </div>
      </div>

      <!-- Settings Content -->
      <div class="col-lg-9">
        <div class="tab-content">
          <!-- General Settings -->
          <div class="tab-pane fade<?= ($tab == 'general') ? ' show active' : '' ?>" id="general">
            <?php if ($tab == 'general') include __DIR__ . '/settings-tabs/general.php'; ?>
          </div>

          <!-- Email Settings -->
          <div class="tab-pane fade<?= ($tab == 'email') ? ' show active' : '' ?>" id="email">
            <?php if ($tab == 'email') include __DIR__ . '/settings-tabs/email.php'; ?>
          </div>

          <!-- Security Settings -->
          <div class="tab-pane fade<?= ($tab == 'security') ? ' show active' : '' ?>" id="security">
            <?php if ($tab == 'security') include __DIR__ . '/settings-tabs/security.php'; ?>
          </div>

          <!-- Danger Zone -->
          <div class="tab-pane fade<?= ($tab == 'danger-zone') ? ' show active' : '' ?>" id="danger-zone">
            <?php if ($tab == 'danger-zone') include __DIR__ . '/settings-tabs/danger-zone.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>