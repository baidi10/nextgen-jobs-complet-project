<?php
// Get filter value
$filter = isset($_GET['user_filter']) ? trim($_GET['user_filter']) : '';
// Get user data for the report
$userData = $report->getUserReportData($startDate, $endDate);
// Apply filter if set
if ($filter !== '') {
    $userData = array_filter($userData, function($user) use ($filter) {
        // Support both naming conventions
        $firstName = $user['firstName'] ?? $user['first_name'] ?? '';
        $lastName = $user['lastName'] ?? $user['last_name'] ?? '';
        $email = strtolower($user['email']);
        $name = strtolower($firstName . ' ' . $lastName);
        $filterLower = strtolower($filter);
        return strpos($name, $filterLower) !== false || strpos($email, $filterLower) !== false;
    });
}
// Pagination setup
$perPage = 10;
$totalUsers = count($userData);
$userPage = isset($_GET['user_page']) ? max(1, intval($_GET['user_page'])) : 1;
$totalPages = max(1, ceil($totalUsers / $perPage));
$start = ($userPage - 1) * $perPage;
$usersToShow = array_slice($userData, $start, $perPage);
?>

<!-- Filter input -->
<form method="get" class="mb-3 d-flex gap-2 align-items-center">
  <input type="hidden" name="tab" value="users">
  <input type="hidden" name="start" value="<?= htmlspecialchars($startDate) ?>">
  <input type="hidden" name="end" value="<?= htmlspecialchars($endDate) ?>">
  <input type="text" name="user_filter" class="form-control" placeholder="Filter by Name or Email" value="<?= htmlspecialchars($filter) ?>" style="max-width:250px;">
  <button type="submit" class="btn btn-outline-primary">Filter</button>
</form>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="bg-light">
      <tr>
        <th>User ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Joined</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($usersToShow)): ?>
        <tr>
          <td colspan="6" class="text-center py-4">No user data found for the selected period</td>
        </tr>
      <?php else: ?>
        <?php foreach($usersToShow as $user): ?>
          <tr>
            <td><?= $user['userId'] ?? $user['user_id'] ?? 'N/A' ?></td>
            <td><?= htmlspecialchars(($user['firstName'] ?? $user['first_name'] ?? 'Unknown') . ' ' . ($user['lastName'] ?? $user['last_name'] ?? 'User')) ?></td>
            <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
            <td><span class="badge bg-<?= roleBadgeColor($user['userType'] ?? $user['user_type'] ?? 'unknown') ?>"><?= ucfirst($user['userType'] ?? $user['user_type'] ?? 'Unknown') ?></span></td>
            <td><span class="badge bg-<?= statusBadgeColor($user['status'] ?? 'unknown') ?>"><?= ucfirst($user['status'] ?? 'Unknown') ?></span></td>
            <td><?= date('M j, Y', strtotime($user['createdAt'] ?? $user['created_at'] ?? 'now')) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<nav aria-label="User pagination">
  <ul class="pagination justify-content-center">
    <li class="page-item<?= $userPage == 1 ? ' disabled' : '' ?>">
      <a class="page-link" href="<?= buildPaginationUrl('users', 'user_page', $userPage-1, ['user_filter' => $filter]) ?>" tabindex="-1">Previous</a>
    </li>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item<?= $i == $userPage ? ' active' : '' ?>">
        <a class="page-link" href="<?= buildPaginationUrl('users', 'user_page', $i, ['user_filter' => $filter]) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item<?= $userPage == $totalPages ? ' disabled' : '' ?>">
      <a class="page-link" href="<?= buildPaginationUrl('users', 'user_page', $userPage+1, ['user_filter' => $filter]) ?>">Next</a>
    </li>
  </ul>
</nav>
<?php endif; ?> 