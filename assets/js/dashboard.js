/**
 * User Dashboard Interactions
 */

class UserDashboard {
  constructor() {
    this.initEventListeners();
    this.loadSavedJobs();
    this.initApplicationStatusUpdates();
  }
  
  initEventListeners() {
    // Save job buttons
    document.querySelectorAll('.save-job-btn').forEach(btn => {
      btn.addEventListener('click', this.toggleJobSave.bind(this));
    });
    
    // Application status filters
    document.querySelectorAll('.application-filter').forEach(filter => {
      filter.addEventListener('change', this.filterApplications.bind(this));
    });
    
    // Profile edit toggle
    const editProfileBtn = document.getElementById('edit-profile-btn');
    if (editProfileBtn) {
      editProfileBtn.addEventListener('click', this.toggleProfileEdit.bind(this));
    }
  }
  
  toggleJobSave(e) {
    const button = e.currentTarget;
    const jobId = button.dataset.jobId;
    const isSaved = button.classList.contains('active');
    
    fetch('/ajax/save-job.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        job_id: jobId,
        action: isSaved ? 'unsave' : 'save'
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        button.classList.toggle('active');
        button.innerHTML = isSaved ? 
          '<i class="far fa-bookmark"></i> Save Job' : 
          '<i class="fas fa-bookmark"></i> Saved';
        
        showToast(
          isSaved ? 'Job Unsaved' : 'Job Saved',
          isSaved ? 'Removed from your saved jobs' : 'Added to your saved jobs',
          'success'
        );
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('Error', 'Failed to update saved jobs', 'error');
    });
  }
  
  loadSavedJobs() {
    const savedJobsContainer = document.getElementById('saved-jobs-container');
    if (!savedJobsContainer) return;
    
    fetch('/ajax/get-saved-jobs.php')
      .then(response => response.json())
      .then(data => {
        if (data.length > 0) {
          savedJobsContainer.innerHTML = this.generateSavedJobsHTML(data);
        } else {
          savedJobsContainer.innerHTML = `
            <div class="empty-state">
              <i class="far fa-bookmark fa-3x"></i>
              <h4>No saved jobs yet</h4>
              <p>Save interesting jobs to view them here later</p>
              <a href="/jobs" class="btn btn-primary">Browse Jobs</a>
            </div>
          `;
        }
      });
  }
  
  generateSavedJobsHTML(jobs) {
    return jobs.map(job => `
      <div class="card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5 class="card-title"><a href="/jobs/${job.slug}">${job.title}</a></h5>
              <h6 class="card-subtitle mb-2 text-muted">${job.company_name}</h6>
              <p class="card-text">
                <small class="text-muted">${job.location} • ${job.posted_date}</small>
              </p>
            </div>
            <div>
              <button class="btn btn-outline-primary save-job-btn active" data-job-id="${job.id}">
                <i class="fas fa-bookmark"></i> Saved
              </button>
            </div>
          </div>
        </div>
      </div>
    `).join('');
  }
  
  filterApplications() {
    const status = document.querySelector('input[name="application_status"]:checked').value;
    document.querySelectorAll('.application-card').forEach(card => {
      if (status === 'all' || card.dataset.status === status) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  }
  
  toggleProfileEdit() {
    const profileForm = document.getElementById('profile-form');
    const profileDisplay = document.getElementById('profile-display');
    const editProfileBtn = document.getElementById('edit-profile-btn');
    
    if (profileForm.style.display === 'none') {
      profileForm.style.display = 'block';
      profileDisplay.style.display = 'none';
      editProfileBtn.textContent = 'Cancel';
    } else {
      profileForm.style.display = 'none';
      profileDisplay.style.display = 'block';
      editProfileBtn.textContent = 'Edit Profile';
    }
  }
  
  initApplicationStatusUpdates() {
    document.querySelectorAll('.status-select').forEach(select => {
      select.addEventListener('change', function() {
        const applicationId = this.dataset.applicationId;
        const newStatus = this.value;
        
        fetch('/ajax/update-application-status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            application_id: applicationId,
            status: newStatus
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const statusBadge = document.querySelector(`.status-badge[data-application-id="${applicationId}"]`);
            if (statusBadge) {
              statusBadge.className = `status-badge badge bg-${this.getStatusColor(newStatus)}`;
              statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            }
            showToast('Success', 'Application status updated', 'success');
          }
        });
      });
    });
  }
  
  getStatusColor(status) {
    const statusColors = {
      applied: 'primary',
      reviewed: 'info',
      interview: 'warning',
      offered: 'success',
      rejected: 'danger'
    };
    return statusColors[status.toLowerCase()] || 'secondary';
  }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
  if (document.body.classList.contains('dashboard-page')) {
    new UserDashboard();
  }
});