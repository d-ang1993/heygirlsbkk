import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

// Hero Carousel Functionality
let currentSlide = 0;
let slides = [];
let dots = [];
let autoAdvanceInterval = null;

function initCarousel() {
  slides = document.querySelectorAll('.hero__carousel-item');
  dots = document.querySelectorAll('.hero__carousel-dot');
  
  if (slides.length === 0) {
    return;
  }
  
  // Clear any existing interval
  if (autoAdvanceInterval) {
    clearInterval(autoAdvanceInterval);
  }
  
  // Auto-advance carousel every 3 seconds
  if (slides.length > 1) {
    autoAdvanceInterval = setInterval(() => {
      changeSlide(1);
    }, 3000);
  }
}

function changeSlide(direction) {
  if (slides.length === 0) {
    return;
  }
  
  // Remove active class from current slide
  if (slides[currentSlide]) {
    slides[currentSlide].classList.remove('active');
  }
  if (dots[currentSlide]) {
    dots[currentSlide].classList.remove('active');
  }
  
  // Calculate new slide index
  currentSlide += direction;
  
  // Handle wraparound
  if (currentSlide >= slides.length) {
    currentSlide = 0;
  } else if (currentSlide < 0) {
    currentSlide = slides.length - 1;
  }
  
  // Add active class to new slide
  if (slides[currentSlide]) {
    slides[currentSlide].classList.add('active');
  }
  if (dots[currentSlide]) {
    dots[currentSlide].classList.add('active');
  }
}

// Dot navigation
function goToSlide(slideIndex) {
  if (slides.length === 0) return;
  
  // Remove active class from current slide
  slides[currentSlide].classList.remove('active');
  if (dots[currentSlide]) {
    dots[currentSlide].classList.remove('active');
  }
  
  // Set new slide
  currentSlide = slideIndex;
  
  // Add active class to new slide
  slides[currentSlide].classList.add('active');
  if (dots[currentSlide]) {
    dots[currentSlide].classList.add('active');
  }
}

// Make functions globally available immediately - BEFORE DOM is ready
window.changeSlide = changeSlide;
window.goToSlide = goToSlide;
window.initCarousel = initCarousel;

// Also make them available on window load as backup
window.addEventListener('load', function() {
  window.changeSlide = changeSlide;
  window.goToSlide = goToSlide;
  window.initCarousel = initCarousel;
});

// Also add fallback functions in case of timing issues
window.addEventListener('load', function() {
  if (!window.changeSlide) {
    window.changeSlide = changeSlide;
  }
  if (!window.goToSlide) {
    window.goToSlide = goToSlide;
  }
});

// Initialize carousel when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(initCarousel, 100); // Small delay to ensure all elements are rendered
  
  // Add click event listeners to dots
  const dots = document.querySelectorAll('.hero__carousel-dot');
  dots.forEach(dot => {
    dot.addEventListener('click', function() {
      const slideIndex = parseInt(this.getAttribute('data-slide'));
      if (typeof goToSlide === 'function') {
        goToSlide(slideIndex);
      }
    });
  });
});

// Also initialize if DOM is already loaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initCarousel, 100);
  });
} else {
  setTimeout(initCarousel, 100);
}

// Import Product Variations JavaScript
import './product-variations.js';
