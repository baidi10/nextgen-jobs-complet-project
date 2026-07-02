// UI effects
/**
 * UI Animations and Effects
 */

class UIAnimations {
  constructor() {
    this.initScrollAnimations();
    this.initHoverEffects();
    this.initPageTransitions();
    this.initLoadingEffects();
  }
  
  initScrollAnimations() {
    // Animate elements when they come into view
    const animateOnScroll = () => {
      const elements = document.querySelectorAll('.animate-on-scroll');
      
      elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementPosition < windowHeight - 100) {
          element.classList.add('animated');
        }
      });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Run once on load
  }
  
  initHoverEffects() {
    // Job card hover effects
    document.querySelectorAll('.job-card').forEach(card => {
      card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-5px)';
        card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
      });
      
      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
        card.style.boxShadow = '';
      });
    });
    
    // Button hover effects
    document.querySelectorAll('.btn').forEach(btn => {
      btn.addEventListener('mouseenter', () => {
        btn.style.transform = 'translateY(-2px)';
      });
      
      btn.addEventListener('mouseleave', () => {
        btn.style.transform = '';
      });
    });
  }
  
  initPageTransitions() {
    // Smooth page transitions (requires PJAX for full effect)
    document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"])').forEach(link => {
      link.addEventListener('click', (e) => {
        if (link.href && !link.href.startsWith('javascript:')) {
          e.preventDefault();
          
          // Add loading animation
          document.body.classList.add('page-transition-out');
          
          setTimeout(() => {
            window.location.href = link.href;
          }, 300);
        }
      });
    });
  }
  
  initLoadingEffects() {
    // Skeleton loading for async content
    this.showSkeletonLoading = (containerId) => {
      const container = document.getElementById(containerId);
      if (container) {
        container.innerHTML = `
          <div class="skeleton-loading">
            ${Array(5).fill(`
              <div class="skeleton-item">
                <div class="skeleton-line" style="width: 80%"></div>
                <div class="skeleton-line" style="width: 60%"></div>
                <div class="skeleton-line" style="width: 40%"></div>
              </div>
            `).join('')}
          </div>
        `;
      }
    };
    
    // Example usage:
    // this.showSkeletonLoading('jobs-container');
  }
}

// Initialize animations
document.addEventListener('DOMContentLoaded', () => {
  new UIAnimations();
  
  // Add animated class to body to enable CSS transitions
  setTimeout(() => {
    document.body.classList.add('animated');
  }, 100);
});