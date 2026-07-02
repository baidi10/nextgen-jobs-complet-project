<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Database.php';
require_once __DIR__ . '/../../classes/User.php';

// Admin authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: /pages/public/login.php');
    exit;
}

// Initialize variables with proper type casting
$users = [];
$totalUsers = 0;
$perPage = 25;
$currentPage = 1;
$roleFilter = 'all';
$dateFrom = $_GET['dateFrom'] ?? '';
$dateTo = $_GET['dateTo'] ?? '';

// Get and validate user ID filter
$userIdFilter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Validate dates
if (!empty($dateFrom) && !empty($dateTo)) {
    if (strtotime($dateFrom) > strtotime($dateTo)) {
        $_SESSION['error_message'] = "From date cannot be later than To date";
        header('Location: manage-users.php');
        exit;
    }
}

// Get and validate page number
if (isset($_GET['page'])) {
    $page = filter_var($_GET['page'], FILTER_VALIDATE_INT);
    if ($page !== false && $page > 0) {
        $currentPage = $page;
    }
}

// Get and validate role filter
if (isset($_GET['role'])) {
    $roleFilter = $_GET['role'];
}

try {
    // Create a new User instance for data retrieval
    $userObj = new User();

    // Adjust user retrieval logic to filter by user ID if provided
    if ($userIdFilter) {
        $users = [$userObj->getUserById($userIdFilter)];
        $totalUsers = count($users);
    } else {
        $users = $userObj->getAllUsers($currentPage, $perPage, '', $roleFilter, $dateFrom, $dateTo);
        $totalUsers = (int)$userObj->countAllUsers('', $roleFilter, $dateFrom, $dateTo);
    }

    // Ensure all pagination variables are integers
    $currentPage = (int)$currentPage;
    $perPage = (int)$perPage;
    $totalUsers = (int)$totalUsers;

    // Debugging: Log user data
    error_log("User data: " . print_r($users, true));

    // Process impersonation if requested
    if (isset($_POST['impersonate_user']) && !empty($_POST['user_id'])) {
        $impersonateUserId = (int)$_POST['user_id'];
        $userData = $userObj->getUserById($impersonateUserId);
        
        if ($userData) {
            // Store original admin ID for returning later
            $_SESSION['admin_id'] = $_SESSION['user_id'];
            $_SESSION['admin_type'] = $_SESSION['user_type'];
            
            // Set session to impersonated user - handle both column name formats
            $_SESSION['user_id'] = $userData['userId'] ?? $userData['user_id'] ?? $impersonateUserId;
            $_SESSION['user_type'] = $userData['userType'] ?? $userData['user_type'] ?? 'jobSeeker';
            $_SESSION['is_impersonating'] = true;
            
            // Redirect to appropriate dashboard based on user type
            redirectBasedOnUserType();
        }
    }
} catch (Exception $e) {
    // Log error and set message for display
    error_log("Error in manage-users.php: " . $e->getMessage());
    $errorMessage = "An error occurred while retrieving user data. Please try again later.";
}

$pageTitle = "Manage Users - JOBEST";
include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
  <div class="container py-5">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h1 class="h3 fw-bold mb-1">User Management</h1>
            <p class="text-muted mb-0">Manage and monitor all user accounts</p>
          </div>
          <a href="create-user.php" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Add New User
          </a>
        </div>
      </div>
    </div>

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4 filter-card">
      <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="dateFrom" class="form-label">From Date</label>
            <input type="date" id="dateFrom" name="dateFrom" class="form-control" 
                   value="<?= htmlspecialchars($dateFrom) ?>">
          </div>
          
          <div class="col-md-3">
            <label for="dateTo" class="form-label">To Date</label>
            <input type="date" id="dateTo" name="dateTo" class="form-control" 
                   value="<?= htmlspecialchars($dateTo) ?>">
          </div>
          
          <div class="col-md-2">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select">
              <option value="all" <?= $roleFilter === 'all' ? 'selected' : '' ?>>All Roles</option>
              <option value="jobSeeker" <?= $roleFilter === 'jobSeeker' ? 'selected' : '' ?>>Job Seekers</option>
              <option value="employer" <?= $roleFilter === 'employer' ? 'selected' : '' ?>>Employers</option>
              <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admins</option>
            </select>
          </div>

          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 btn-filter">
              <i class="bi bi-filter"></i> Filter
            </button>
          </div>

          <div class="col-md-2">
            <a href="manage-users.php" class="btn btn-outline-secondary w-100 btn-reset">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </a>
          </div>
        </form>
      </div>
    </div>

    <!-- Results Summary -->
    <?php if (!empty($dateFrom) || !empty($dateTo) || $roleFilter !== 'all'): ?>
      <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle"></i>
        Showing <?= count($users) ?> of <?= $totalUsers ?> users
        <?php if (!empty($dateFrom) || !empty($dateTo)): ?>
          registered between 
          <?= !empty($dateFrom) ? date('M d, Y', strtotime($dateFrom)) : 'any time' ?>
          and
          <?= !empty($dateTo) ? date('M d, Y', strtotime($dateTo)) : 'now' ?>
        <?php endif; ?>
        <?php if ($roleFilter !== 'all'): ?>
          with role "<?= ucfirst($roleFilter) ?>"
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($errorMessage)): ?>
      <div class="alert alert-danger mb-4">
        <?= htmlspecialchars($errorMessage) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- User List Section -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="border-0 ps-4">
                  <input type="checkbox" class="form-check-input" id="selectAll">
                </th>
                <th class="border-0">Name</th>
                <th class="border-0">Email</th>
                <th class="border-0">Role</th>
                <th class="border-0">Joined</th>
                <th class="border-0 text-center">View</th>
                <th class="border-0 text-center">Edit</th>
                <th class="border-0 text-center pe-4">Delete</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                  <tr>
                    <td class="ps-4">
                      <input type="checkbox" class="form-check-input user-checkbox" value="<?= $user['userId'] ?? $user['user_id'] ?? 0 ?>">
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-wrapper" style="margin-right: 20px;">
                        <img src="<?= getUserAvatar($user['userId'] ?? $user['user_id'] ?? 0, 40) ?>" 
                               class="rounded-circle" alt="User avatar">
                        </div>
                        <div>
                          <h6 class="mb-0 fw-semibold"><?= htmlspecialchars(($user['firstName'] ?? $user['first_name'] ?? 'Unknown') . ' ' . ($user['lastName'] ?? $user['last_name'] ?? 'User')) ?></h6>
                          <small class="text-muted">ID: <?= $user['userId'] ?? $user['user_id'] ?? 'N/A' ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="text-dark"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span>
                    </td>
                    <td>
                      <span class="badge bg-<?= roleBadgeColor($user['userType'] ?? $user['user_type'] ?? 'unknown') ?> px-3 py-2">
                        <?= ucfirst($user['userType'] ?? $user['user_type'] ?? 'Unknown') ?>
                      </span>
                    </td>
                    <td>
                      <span class="text-muted">
                        <?= date('M j, Y', strtotime($user['createdAt'] ?? $user['created_at'] ?? 'now')) ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <a href="view-user.php?id=<?= $user['userId'] ?? $user['user_id'] ?? 0 ?>" 
                         class="btn btn-sm btn-outline-info" 
                         title="View User">
                        <i class="bi bi-eye"></i>
                      </a>
                    </td>
                    <td class="text-center">
                      <a href="edit-user.php?id=<?= $user['userId'] ?? $user['user_id'] ?? 0 ?>" 
                         class="btn btn-sm btn-outline-primary" 
                         title="Edit User">
                        <i class="bi bi-pencil"></i>
                      </a>
                    </td>
                    <td class="text-center pe-4">
                      <form method="post" action="delete-user.php" class="d-inline">
                        <input type="hidden" name="user_id" value="<?= $user['userId'] ?? $user['user_id'] ?? 0 ?>">
                        <button type="submit" 
                                class="btn btn-sm btn-outline-danger" 
                                onclick="return confirm('Are you sure you want to delete this user?');"
                                title="Delete User">
                          <i class="bi bi-trash"></i>
                        </button>
                              </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <div class="text-muted">
                      <i class="bi bi-people display-4 d-block mb-3"></i>
                      <p class="mb-0">No users found</p>
                      <?php if (!empty($search) || $roleFilter !== 'all'): ?>
                        <small>Try adjusting your search or filter criteria</small>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
        </div>

    <!-- Bulk Actions and Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
      <div class="bulk-actions d-none">
        <button class="btn btn-outline-danger" onclick="bulkDelete()">
          <i class="bi bi-trash me-2"></i>Delete Selected
        </button>
          </div>
      <div class="pagination-info text-muted">
        Showing <?= count($users) ?> of <?= $totalUsers ?> users
      </div>
    </div>

    <!-- Pagination -->
    <?php if ((int)$totalUsers > (int)$perPage && !empty($users)): ?>
      <div class="d-flex flex-column align-items-center mt-4">
        <?php
        // Ensure integer types for pagination variables
        $currentPage = isset($currentPage) ? (int)$currentPage : 1;
        $perPage = isset($perPage) ? (int)$perPage : 25;
        $totalUsers = isset($totalUsers) ? (int)$totalUsers : 0;
        ?>
        <nav aria-label="User pagination">
          <ul class="pagination pagination-lg mb-0">
            <?php
            $totalPages = (int)ceil($totalUsers / $perPage);
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            // Previous button
            if ($currentPage > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $currentPage - 1 ?>&role=<?= htmlspecialchars($roleFilter) ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
            <?php endif; ?>

            <?php
            // First page
            if ($startPage > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=1&role=<?= htmlspecialchars($roleFilter) ?>">1</a>
              </li>
              <?php if ($startPage > 2): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
              <?php endif; ?>
            <?php endif; ?>

            <?php
            // Page numbers
            for ($i = $startPage; $i <= $endPage; $i++): ?>
              <li class="page-item <?= $i === (int)$currentPage ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&role=<?= htmlspecialchars($roleFilter) ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>

            <?php
            // Last page
            if ($endPage < $totalPages): ?>
              <?php if ($endPage < $totalPages - 1): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
              <?php endif; ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $totalPages ?>&role=<?= htmlspecialchars($roleFilter) ?>"><?= $totalPages ?></a>
              </li>
            <?php endif; ?>

            <?php
            // Next button
            if ($currentPage < $totalPages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $currentPage + 1 ?>&role=<?= htmlspecialchars($roleFilter) ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
      </nav>
      </div>
    <?php endif; ?>

    <style>
      .table > :not(caption) > * > * {
        padding: 1rem 0.5rem;
      }
      .avatar-wrapper {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        overflow: hidden;
        background-color: #f8f9fa;
      }
      .avatar-wrapper img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
      }
      .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
      }
      .btn-sm {
        padding: 0.4rem 0.6rem;
      }
      .table-hover tbody tr:hover {
        background-color: #f8f9fa;
      }
      /* Custom Select Styling */
      .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        padding-right: 2.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
      }

      .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
      }

      .form-select:hover {
        border-color: #adb5bd;
      }

      /* Custom Date Input Styling */
      .form-control[type="date"] {
        padding-right: 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
      }

      .form-control[type="date"]:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(177, 179, 182, 0.25);
      }

      .form-control[type="date"]:hover {
        border-color: #adb5bd;
      }

      /* Form Label Styling */
      .form-label {
        font-weight: 500;
        color: rgb(0, 0, 0);
        margin-bottom: 0.5rem;
      }

      /* Filter Card Styling */
      .filter-card {
        background-color: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(255, 255, 255, 0.07);
      }

      .filter-card .card-body {
        padding: 1.5rem;
      }

      /* Button Styling */
      .btn-filter {
        background-color: rgb(0, 0, 0);
        border-color: rgb(0, 0, 0);
        color: #fff;
        transition: all 0.2s ease-in-out;
      }

      .btn-filter:hover {
        background-color: #333;
        border-color: #333;
        transform: translateY(-1px);
      }

      .btn-reset {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: rgb(0, 0, 0);
        transition: all 0.2s ease-in-out;
      }

      .btn-reset:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
        transform: translateY(-1px);
      }

      /* Form Controls */
      .form-select,
      .form-control[type="date"] {
        color: rgb(0, 0, 0);
      }

      /* Dark mode support */
      @media (prefers-color-scheme: dark) {
        .form-select,
        .form-control[type="date"] {
          background-color: rgb(255, 255, 255);
          border-color: #495057;
          color: rgb(0, 0, 0);
        }

        .form-label {
          color: rgb(0, 0, 0);
        }

        .btn-reset {
          background-color: rgb(255, 255, 255);
          border-color: #495057;
          color: rgb(0, 0, 0);
        }

        .btn-reset:hover {
          background-color: rgb(255, 255, 255);
          border-color: #6c757d;
        }
      }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle select all checkbox
        const selectAllCheckbox = document.getElementById('selectAll');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const bulkActions = document.querySelector('.bulk-actions');

        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsVisibility();
        });

        userCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionsVisibility);
        });

        function updateBulkActionsVisibility() {
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            bulkActions.classList.toggle('d-none', checkedBoxes.length === 0);
        }
    });

    // Bulk actions
    function bulkDelete() {
        const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedUsers.length === 0) return;
        
        if (confirm(`Are you sure you want to delete ${selectedUsers.length} selected users?`)) {
            fetch('bulk-delete-users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_ids: selectedUsers })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete users');
                }
            });
        }
    }
    </script>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>