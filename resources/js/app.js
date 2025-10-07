import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

// Import live search functionality
import './live-search.js';

// Import cart functionality
import './cart.js';

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


