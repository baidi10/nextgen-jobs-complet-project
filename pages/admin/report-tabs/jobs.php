<?php
// Get job data for the report
$jobData = $report->getJobReportData($startDate, $endDate);

// Pagination setup
$perPage = 10;
$totalJobs = count($jobData);
$jobPage = isset($_GET['job_page']) ? max(1, intval($_GET['job_page'])) : 1;
$totalPages = max(1, ceil($totalJobs / $perPage));
$start = ($jobPage - 1) * $perPage;
$jobsToShow = array_slice($jobData, $start, $perPage);
?>

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="bg-light">
      <tr>
        <th>Job ID</th>
        <th>Title</th>
        <th>Company</th>
        <th>Type</th>
        <th>Location</th>
        <th>Applications</th>
        <th>Status</th>
        <th>Posted</th>
      </tr>
    </thead>
    <tbody>
      <?php if(empty($jobsToShow)): ?>
        <tr>
          <td colspan="8" class="text-center py-4">No job data found for the selected period</td>
        </tr>
      <?php else: ?>
        <?php foreach($jobsToShow as $job): ?>
          <tr>
            <td><?= $job['jobId'] ?></td>
            <td><?= htmlspecialchars($job['jobTitle']) ?></td>
            <td><?= htmlspecialchars($job['companyName']) ?></td>
            <td><?= htmlspecialchars(ucfirst($job['jobType'])) ?></td>
            <td><?= htmlspecialchars($job['location']) ?></td>
            <td><?= number_format($job['applicationsCount']) ?></td>
            <td><span class="badge bg-<?= statusColor($job['status']) ?>"><?= ucfirst($job['status']) ?></span></td>
            <td><?= date('M j, Y', strtotime($job['createdAt'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<nav aria-label="Job pagination">
  <ul class="pagination justify-content-center">
    <li class="page-item<?= $jobPage == 1 ? ' disabled' : '' ?>">
      <a class="page-link" href="<?= buildPaginationUrl('jobs', 'job_page', $jobPage-1) ?>" tabindex="-1">Previous</a>
    </li>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item<?= $i == $jobPage ? ' active' : '' ?>">
        <a class="page-link" href="<?= buildPaginationUrl('jobs', 'job_page', $i) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item<?= $jobPage == $totalPages ? ' disabled' : '' ?>">
      <a class="page-link" href="<?= buildPaginationUrl('jobs', 'job_page', $jobPage+1) ?>">Next</a>
    </li>
  </ul>
</nav>
<?php endif; ?> 