<?php
// Maybe fetch maintenance mode status
// $db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT settingValue FROM settings WHERE settingKey = 'maintenance_mode'");
$maintenanceMode = $stmt->fetchColumn() ?: '0';
?>

<div class="card border-0 shadow-sm border-danger">
  <div class="card-header bg-white text-danger">
    <h3 class="h5 fw-bold mb-0">Danger Zone</h3>
  </div>
  <div class="card-body">
    <div class="alert alert-warning">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      Warning: Actions in this section can have serious consequences. Proceed with caution.
    </div>
    
    <!-- Maintenance Mode -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1">Maintenance Mode</h5>
            <p class="text-muted mb-0">Put the site in maintenance mode to perform updates</p>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="maintenance_toggle" 
                   <?= $maintenanceMode === '1' ? 'checked' : '' ?>>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Clear Cache -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1">Clear System Cache</h5>
            <p class="text-muted mb-0">Clear all cached data to refresh the system</p>
          </div>
          <button class="btn btn-outline-danger" id="clear_cache_btn">Clear Cache</button>
        </div>
      </div>
    </div>
    
    <!-- Reset Settings -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1">Reset to Default Settings</h5>
            <p class="text-muted mb-0">Reset all settings to their default values</p>
          </div>
          <button class="btn btn-outline-danger" id="reset_settings_btn" 
                  data-bs-toggle="modal" data-bs-target="#resetSettingsModal">Reset</button>
        </div>
      </div>
    </div>
    
    <!-- Database Backup/Restore -->
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1">Database Operations</h5>
            <p class="text-muted mb-0">Backup or restore the database</p>
          </div>
          <div>
            <button class="btn btn-outline-primary me-2" id="backup_db_btn">Backup</button>
            <button class="btn btn-outline-danger" id="restore_db_btn" 
                    data-bs-toggle="modal" data-bs-target="#restoreDbModal">Restore</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Reset Settings Modal -->
<div class="modal fade" id="resetSettingsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to reset all settings to their default values? This action cannot be undone.</p>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="reset_confirm">
          <label class="form-check-label" for="reset_confirm">
            I understand this action is irreversible
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirm_reset_btn" disabled>Reset Settings</button>
      </div>
    </div>
  </div>
</div>

<!-- Restore Database Modal -->
<div class="modal fade" id="restoreDbModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Restore Database</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-danger fw-bold">Warning: Restoring a database will overwrite all current data!</p>
        <div class="mb-3">
          <label for="backup_file" class="form-label">Select Backup File</label>
          <input class="form-control" type="file" id="backup_file">
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="restore_confirm">
          <label class="form-check-label" for="restore_confirm">
            I understand this will overwrite all current data
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirm_restore_btn" disabled>Restore Database</button>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle maintenance mode
document.getElementById('maintenance_toggle').addEventListener('change', function() {
  const isChecked = this.checked;
  if (confirm(`Are you sure you want to ${isChecked ? 'enable' : 'disable'} maintenance mode?`)) {
    fetch('/admin/toggle-maintenance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ maintenance_mode: isChecked ? 1 : 0 })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(`Maintenance mode ${isChecked ? 'enabled' : 'disabled'}`);
      } else {
        alert('Error: ' + data.message);
        this.checked = !isChecked; // Revert the toggle
      }
    });
  } else {
    this.checked = !isChecked; // Revert the toggle if canceled
  }
});

// Enable reset button when checkbox is checked
document.getElementById('reset_confirm').addEventListener('change', function() {
  document.getElementById('confirm_reset_btn').disabled = !this.checked;
});

// Enable restore button when checkbox is checked
document.getElementById('restore_confirm').addEventListener('change', function() {
  document.getElementById('confirm_restore_btn').disabled = !this.checked;
});

// Clear cache button
document.getElementById('clear_cache_btn').addEventListener('click', function() {
  if (confirm('Are you sure you want to clear the system cache?')) {
    fetch('/admin/clear-cache.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Cache cleared successfully');
        } else {
          alert('Error: ' + data.message);
        }
      });
  }
});

// Database backup
document.getElementById('backup_db_btn').addEventListener('click', function() {
  window.location.href = '/admin/backup-database.php';
});

// Confirm reset settings
document.getElementById('confirm_reset_btn').addEventListener('click', function() {
  fetch('/admin/reset-settings.php', { method: 'POST' })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Settings reset to defaults');
        window.location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    });
});
</script> 