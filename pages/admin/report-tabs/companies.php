<?php
// Get company data for the report
$companyData = $report->getCompanyReportData($startDate, $endDate);

// Pagination setup
$perPage = 10;
$totalCompanies = count($companyData);
$companyPage = isset($_GET['company_page']) ? max(1, intval($_GET['company_page'])) : 1;
$totalPages = max(1, ceil($totalCompanies / $perPage));
$start = ($companyPage - 1) * $perPage;
$companiesToShow = array_slice($companyData, $start, $perPage);
?>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="bg-light">
      <tr>
        <th>Company ID</th>
        <th>Name</th>
        <th>Industry</th>
        <th>Size</th>
        <th>Location</th>
        <th>Contact</th>
        <th>Active Jobs</th>
        <th>Registered</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($companiesToShow)): ?>
        <tr>
          <td colspan="8" class="text-center py-4">No company data found for the selected period</td>
        </tr>
      <?php else: ?>
        <?php foreach($companiesToShow as $company): ?>
          <tr>
            <td><?= $company['companyId'] ?></td>
            <td><?= htmlspecialchars($company['companyName']) ?></td>
            <td><?= htmlspecialchars($company['industry'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($company['employeeCount'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($company['headquarters'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($company['contactEmail']) ?></td>
            <td><?= number_format($company['activeJobs']) ?></td>
            <td><?= date('M j, Y', strtotime($company['createdAt'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<nav aria-label="Company pagination">
  <ul class="pagination justify-content-center">
    <li class="page-item<?= $companyPage == 1 ? ' disabled' : '' ?>">
      <a class="page-link" href="<?= buildPaginationUrl('companies', 'company_page', $companyPage-1) ?>" tabindex="-1">Previous</a>
    </li>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item<?= $i == $companyPage ? ' active' : '' ?>">
        <a class="page-link" href="<?= buildPaginationUrl('companies', 'company_page', $i) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item<?= $companyPage == $totalPages ? ' disabled' : '' ?>">
      <a class="page-link" href="<?= buildPaginationUrl('companies', 'company_page', $companyPage+1) ?>">Next</a>
    </li>
  </ul>
</nav>
<?php endif; ?> 