/**
 * Reusable Progress Carousel Component
 * Handles image/slide carousel with progress bar indicators
 */

export class ProgressCarousel {
  constructor(options) {
    this.container = options.container;
    this.images = options.images || [];
    this.imageElement = options.imageElement;
    this.indicators = options.indicators || [];
    this.duration = options.duration || 3000;
    this.onSlideChange = options.onSlideChange || (() => {});
    this.autoStart = options.autoStart !== false;
    
    this.currentIndex = 0;
    this.intervalId = null;
    
    if (this.autoStart && this.images.length > 1) {
      this.init();
    }
  }
  
  init() {
    // Set up event listeners if container is provided
    if (this.container) {
      this.container.addEventListener('mouseenter', () => this.start());
      this.container.addEventListener('mouseleave', () => this.stop());
    }
    
    // Set up indicator click handlers
    this.indicators.forEach((indicator, index) => {
      indicator.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.goToSlide(index);
      });
    });
  }
  
  showSlide(index) {
    this.currentIndex = index;
    
    // Update image if provided
    if (this.imageElement && this.images[index]) {
      this.imageElement.style.opacity = '0';
      
      setTimeout(() => {
        this.imageElement.src = this.images[index];
        this.imageElement.style.opacity = '1';
        
        // Update indicators with animation reset
        this.updateIndicators(index);
      }, 250);
    } else {
      // Just update indicators
      this.updateIndicators(index);
    }
    
    // Call callback
    this.onSlideChange(index);
  }
  
  updateIndicators(index) {
    // Remove active from all indicators
    this.indicators.forEach(indicator => {
      indicator.classList.remove('active');
    });
    
    // Force reflow
    if (this.indicators.length > 0) {
      void this.indicators[0].offsetWidth;
    }
    
    // Add active to current indicator using double requestAnimationFrame
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        if (this.indicators[index]) {
          this.indicators[index].classList.add('active');
        }
      });
    });
  }
  
  start() {
    // Clear any existing interval
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
    
    // Reset to first slide
    if (this.currentIndex !== 0) {
      this.currentIndex = 0;
      if (this.imageElement && this.images[0]) {
        this.imageElement.src = this.images[0];
      }
    }
    
    // Remove all active classes
    this.indicators.forEach(indicator => indicator.classList.remove('active'));
    
    // Use timeout to ensure animation resets
    setTimeout(() => {
      if (this.indicators[0]) {
        this.indicators[0].classList.add('active');
      }
      
      // Start interval
      this.intervalId = setInterval(() => {
        const nextIndex = (this.currentIndex + 1) % this.images.length;
        this.showSlide(nextIndex);
      }, this.duration);
    }, 50);
  }
  
  stop() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
    
    // Reset to first slide
    if (this.currentIndex !== 0) {
      this.showSlide(0);
    }
  }
  
  goToSlide(index) {
    // Clear existing interval and restart
    if (this.intervalId) {
      clearInterval(this.intervalId);
    }
    this.showSlide(index);
    this.start();
  }
  
  destroy() {
    this.stop();
    // Remove event listeners if needed
    if (this.container) {
      this.container.replaceWith(this.container.cloneNode(true));
    }
  }
}

