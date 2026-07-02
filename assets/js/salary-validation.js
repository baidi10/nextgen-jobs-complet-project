// Salary validation - ensure max salary is greater than min salary
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('jobForm').addEventListener('submit', function(e) {
    const minSalary = document.querySelector('input[name="salary_min"]').value.trim();
    const maxSalary = document.querySelector('input[name="salary_max"]').value.trim();
    
    // Only validate if both values are provided
    if (minSalary && maxSalary) {
      if (parseFloat(maxSalary) <= parseFloat(minSalary)) {
        e.preventDefault();
        alert('Maximum salary must be greater than minimum salary.');
      }
    }
  });
}); 