/**
 * Product Description Read More Toggle
 * Shows "Read more" link only if content is truncated
 */

document.addEventListener('DOMContentLoaded', function() {
  const descriptionContainers = document.querySelectorAll('.product-short-description');
  
  descriptionContainers.forEach(container => {
    const wrapper = container.querySelector('.description-content-wrapper');
    const toggleLink = container.querySelector('.description-toggle');
    
    if (!wrapper || !toggleLink) return;
    
    // Setup toggle handler first (needed regardless of truncation)
    toggleLink.addEventListener('click', function(e) {
      e.preventDefault();
      
      if (container.classList.contains('is-collapsed')) {
        container.classList.remove('is-collapsed');
        toggleLink.textContent = 'Read less';
      } else {
        container.classList.add('is-collapsed');
        toggleLink.textContent = 'Read more';
      }
    });
    
    // Check if content needs truncation by comparing heights
    // Use requestAnimationFrame to batch DOM reads/writes for better performance
    requestAnimationFrame(() => {
      // Temporarily remove collapsed class to get full height
      container.classList.remove('is-collapsed');
      const fullHeight = wrapper.scrollHeight;
      
      // Apply collapsed class and measure
      container.classList.add('is-collapsed');
      const collapsedHeight = wrapper.scrollHeight;
      
      const needsTruncation = fullHeight > collapsedHeight + 5;
      
      if (!needsTruncation) {
        // Content fits in collapsed view, hide toggle
        container.classList.remove('is-collapsed');
        toggleLink.style.display = 'none';
      } else {
        // Show toggle and keep collapsed state
        toggleLink.style.display = 'inline-flex';
        toggleLink.textContent = 'Read more';
      }
    });
  });
});
