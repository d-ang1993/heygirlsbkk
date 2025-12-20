/**
 * Product Description Accordion
 * Handles chevron dropdown interactions for product specs
 */
document.addEventListener('DOMContentLoaded', function() {
  // Set background images from data attributes
  const imageItems = document.querySelectorAll('.spread-left-image-item[data-bg-image]');
  imageItems.forEach(item => {
    const bgImage = item.getAttribute('data-bg-image');
    if (bgImage) {
      item.style.backgroundImage = `url(${bgImage})`;
    }
  });
  
  // Accordion functionality
  const accordionHeaders = document.querySelectorAll('.spec-accordion-header');
  
  accordionHeaders.forEach(header => {
    header.addEventListener('click', function() {
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      const content = this.nextElementSibling;
      
      // Close all other accordions (optional - remove if you want multiple open)
      // accordionHeaders.forEach(otherHeader => {
      //   if (otherHeader !== this) {
      //     otherHeader.setAttribute('aria-expanded', 'false');
      //     otherHeader.nextElementSibling.style.maxHeight = '0';
      //     otherHeader.nextElementSibling.style.padding = '0';
      //   }
      // });
      
      // Toggle current accordion
      this.setAttribute('aria-expanded', !isExpanded);
      
      if (!isExpanded) {
        // Opening
        content.style.maxHeight = content.scrollHeight + 'px';
        content.style.padding = '0 0 16px 0';
      } else {
        // Closing
        content.style.maxHeight = '0';
        content.style.padding = '0';
      }
    });
  });
});

