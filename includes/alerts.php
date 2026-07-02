<?php
// includes/alerts.php
$alerts = getAlerts();
if (!empty($alerts)):
    foreach ($alerts as $alert): ?>
        <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($alert['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach;
endif;