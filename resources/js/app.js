import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

// Import live search functionality
import './live-search.js';

// Import product description accordion
import './product-description-accordion.js';

// Import reusable ProgressCarousel component
import { ProgressCarousel } from './progress-carousel.js';

// Hero carousel functionality is now handled by hero.blade.php

// Navbar dropdown functionality
function initNavbar() {
  const shopLink = document.querySelector('.navbar-shop');
  const dropdown = document.querySelector('.navbar-dropdown');
  
  if (!shopLink || !dropdown) return;
  
  let isOpen = false;
  let hoverTimeout = null;
  
  // Function to open dropdown
  function openDropdown() {
    clearTimeout(hoverTimeout);
    dropdown.style.display = 'block';
    dropdown.classList.add('active');
    shopLink.classList.add('active');
    isOpen = true;
  }
  
  // Function to close dropdown
  function closeDropdown() {
    clearTimeout(hoverTimeout);
    hoverTimeout = setTimeout(() => {
      dropdown.style.display = 'none';
      dropdown.classList.remove('active');
      shopLink.classList.remove('active');
      isOpen = false;
    }, 200); // Increased delay to 200ms
  }
  
  // Click functionality
  shopLink.addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    if (isOpen) {
      closeDropdown();
    } else {
      openDropdown();
    }
  });
  
  // Hover functionality - open on hover
  shopLink.addEventListener('mouseenter', function() {
    openDropdown();
  });
  
  dropdown.addEventListener('mouseenter', function() {
    openDropdown();
  });
  
  // Close on mouse leave with delay
  shopLink.addEventListener('mouseleave', function() {
    closeDropdown();
  });
  
  dropdown.addEventListener('mouseleave', function() {
    closeDropdown();
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (!shopLink.contains(e.target) && !dropdown.contains(e.target)) {
      closeDropdown();
    }
  });
  
  // Close dropdown on escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && isOpen) {
      closeDropdown();
    }
  });
}

// Initialize navbar when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(initNavbar, 100);
});

// Import Product Variations JavaScript
import './product-variations.js';

// Import Product Description Toggle
import './product-description-toggle.js';

// Import Wishlist Integration
import './wishlist-integration.js';

// Import Related Products Carousel
import './related-products-carousel.js';

// Import Product Grid functionality
import './product-grid.js';

// Import Quick View functionality
import './quick-view.js';

// New Drops Carousel using reusable ProgressCarousel component
class NewDropsCarousel extends ProgressCarousel {
  constructor(options) {
    super(options);
    this.slides = options.slides || [];
    this.dots = options.indicators || [];
    this.autoplay = options.autoplay === true; // Only true if explicitly true
    this.autoplaySpeed = options.duration || 5000;
    this.currentSlide = 0;
    this.isUserInteracting = false;
    this.interactionTimeout = null;
    
    this.setupNewDropsFeatures();
  }
  
  setupNewDropsFeatures() {
    const carousel = this.container;
    if (!carousel) return;
    
    // Event listeners for navigation arrows
    const prevArrow = carousel.querySelector('.new-drops-arrow--prev');
    const nextArrow = carousel.querySelector('.new-drops-arrow--next');
    
    if (prevArrow) {
      prevArrow.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.prev();
        if (this.autoplay) this.startAutoplay();
      });
    }
    
    if (nextArrow) {
      nextArrow.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.next();
        if (this.autoplay) this.startAutoplay();
      });
    }

    // Keyboard navigation
    carousel.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        this.prev();
        if (this.autoplay) this.startAutoplay();
      } else if (e.key === 'ArrowRight') {
        e.preventDefault();
        this.next();
        if (this.autoplay) this.startAutoplay();
      }
    });

    // Touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    carousel.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    });

    carousel.addEventListener('touchend', (e) => {
      touchEndX = e.changedTouches[0].screenX;
      const diff = touchStartX - touchEndX;
      const swipeThreshold = 50;
      
      if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
          this.next();
        } else {
          this.prev();
        }
      }
      if (this.autoplay) this.startAutoplay();
    });

    // Pause autoplay on hover
    carousel.addEventListener('mouseenter', () => this.stopAutoplay());
    carousel.addEventListener('mouseleave', () => {
      if (!this.isUserInteracting && this.autoplay) {
        this.startAutoplay();
      }
    });
    
    // Initialize autoplay
    if (this.autoplay && this.slides.length > 1) {
      this.initializeAutoplay();
    }
  }
  
  showSlide(index) {
    // Show the slide
    this.slides.forEach(slide => slide.classList.remove('active'));
    if (this.slides[index]) {
      this.slides[index].classList.add('active');
    }
    
    // Update indicators using parent class method
    this.currentSlide = index;
    this.currentIndex = index;
    this.updateIndicators(index);
  }
  
  updateIndicators(index) {
    // Remove active from all dots
    this.dots.forEach(dot => dot.classList.remove('active'));
    
    // Force reflow
    if (this.dots.length > 0) {
      void this.dots[0].offsetWidth;
    }
    
    // Add active to current dot with animation reset
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        if (this.dots[index]) {
          this.dots[index].style.setProperty('--carousel-duration', this.autoplaySpeed + 'ms');
          this.dots[index].classList.add('active');
        }
      });
    });
  }
  
  next() {
    const nextIndex = (this.currentSlide + 1) % this.slides.length;
    this.showSlide(nextIndex);
  }
  
  prev() {
    const prevIndex = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
    this.showSlide(prevIndex);
  }
  
  startAutoplay() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
    
    if (this.autoplay && this.slides.length > 1) {
      this.intervalId = setInterval(() => this.next(), this.autoplaySpeed);
    }
  }
  
  stopAutoplay() {
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
  }
  
  initializeAutoplay() {
    // Show first slide
    if (this.slides[0]) {
      this.slides[0].classList.add('active');
    }
    
    // Remove all active dots
    this.dots.forEach(dot => dot.classList.remove('active'));
    
    // Use 50ms timeout for animation reset
    setTimeout(() => {
      if (this.dots[0]) {
        this.dots[0].style.setProperty('--carousel-duration', this.autoplaySpeed + 'ms');
        this.dots[0].classList.add('active');
      }
      
      // Start autoplay
      this.startAutoplay();
    }, 50);
  }
}

// Initialize New Drops carousel when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.querySelector('.new-drops-carousel-container');
  if (!carousel) return;

  const slides = carousel.querySelectorAll('.new-drops-slide');
  const dots = carousel.querySelectorAll('.new-drops-dot');
  
  if (slides.length === 0) return;
  
  const autoplay = carousel.dataset.autoplay === 'true';
  const autoplaySpeed = parseInt(carousel.dataset.autoplaySpeed) || 5000;
  
  console.log('New Drops Init:', { autoplay, autoplaySpeed, slidesCount: slides.length });
  
  new NewDropsCarousel({
    container: carousel,
    slides: Array.from(slides),
    indicators: Array.from(dots),
    autoplay: autoplay,
    duration: autoplaySpeed,
    autoStart: false // We handle initialization in the class
  });
});


