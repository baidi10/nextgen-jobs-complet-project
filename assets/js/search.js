// Live search functionality
/**
 * Live Job Search Implementation
 */

class JobSearch {
  constructor() {
    this.searchInput = document.getElementById('job-search-input');
    this.searchResults = document.getElementById('search-results');
    this.searchTimer = null;
    this.debounceTime = 300;
    
    if (this.searchInput) {
      this.init();
    }
  }
  
  init() {
    this.searchInput.addEventListener('input', this.handleSearch.bind(this));
    this.searchInput.addEventListener('focus', this.showResultsContainer.bind(this));
    document.addEventListener('click', this.handleClickOutside.bind(this));
  }
  
  handleSearch(e) {
    clearTimeout(this.searchTimer);
    
    const query = e.target.value.trim();
    if (query.length < 2) {
      this.clearResults();
      return;
    }
    
    this.searchTimer = setTimeout(() => {
      this.fetchResults(query);
    }, this.debounceTime);
  }
  
  fetchResults(query) {
    fetch(`/ajax/search.php?q=${encodeURIComponent(query)}`)
      .then(response => response.json())
      .then(data => this.displayResults(data))
      .catch(error => {
        console.error('Search error:', error);
      });
  }
  
  displayResults(results) {
    this.clearResults();
    
    if (results.length === 0) {
      this.searchResults.innerHTML = '<div class="p-3 text-muted">No jobs found matching your criteria</div>';
      return;
    }
    
    const fragment = document.createDocumentFragment();
    
    results.forEach(job => {
      const item = document.createElement('a');
      item.className = 'dropdown-item search-result-item';
      item.href = `/jobs/${job.slug}`;
      
      item.innerHTML = `
        <div class="d-flex justify-content-between">
          <strong>${job.title}</strong>
          <small class="text-muted">${job.company}</small>
        </div>
        <small class="text-muted">${job.location} • ${job.salary_range}</small>
      `;
      
      fragment.appendChild(item);
    });
    
    this.searchResults.appendChild(fragment);
    this.showResultsContainer();
  }
  
  clearResults() {
    this.searchResults.innerHTML = '';
  }
  
  showResultsContainer() {
    if (this.searchInput.value.trim().length > 1) {
      this.searchResults.style.display = 'block';
    }
  }
  
  hideResultsContainer() {
    this.searchResults.style.display = 'none';
  }
  
  handleClickOutside(e) {
    if (!this.searchInput.contains(e.target) && !this.searchResults.contains(e.target)) {
      this.hideResultsContainer();
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  new JobSearch();
});