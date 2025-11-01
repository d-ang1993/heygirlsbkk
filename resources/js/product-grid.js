/**
 * Product Grid Functionality
 * Handles image carousel on hover and lazy loading
 */

document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    
    // Optimize image loading with Intersection Observer
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            }
        });
    }, {
        rootMargin: '50px 0px',
        threshold: 0.1
    });
    
    // Observe all product images
    document.querySelectorAll('.product-main-image').forEach(img => {
        imageObserver.observe(img);
    });
    
    productCards.forEach(card => {
        const productImage = card.querySelector('.product-image.has-carousel');
        if (!productImage) return;
        
        const images = JSON.parse(productImage.getAttribute('data-images') || '[]');
        if (images.length <= 1) return;
        
        const img = productImage.querySelector('.product-main-image');
        const indicators = productImage.querySelectorAll('.indicator');
        let currentIndex = 0;
        let intervalId = null;
        const cycleDuration = 3000; // 3 seconds
        
        function showImage(index) {
            currentIndex = index;
            img.style.opacity = '0';
            
            setTimeout(() => {
                img.src = images[index];
                img.style.opacity = '1';
                
                // First, remove active from all indicators
                indicators.forEach(indicator => {
                    indicator.classList.remove('active');
                });
                
                // Force reflow
                void indicators[0].offsetWidth;
                
                // Then add active to the current one using requestAnimationFrame
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        if (indicators[index]) {
                            indicators[index].classList.add('active');
                        }
                    });
                });
            }, 250);
        }
        
        function startCarousel() {
            // Always clear any existing interval first
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
            
            // Reset to first image when starting
            if (currentIndex !== 0) {
                currentIndex = 0;
                img.src = images[0];
            }
            
            // Remove all active classes first.
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            // Use timeout to ensure animation resets
            setTimeout(() => {
                if (indicators[0]) {
                    indicators[0].classList.add('active');
                }
                
                // Start interval after initial setup
                intervalId = setInterval(() => {
                    const nextIndex = (currentIndex + 1) % images.length;
                    showImage(nextIndex);
                }, cycleDuration);
            }, 50);
        }
        
        function stopCarousel() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
            
            // Reset to first image
            if (currentIndex !== 0) {
                showImage(0);
            }
        }
        
        // Start carousel on hover
        card.addEventListener('mouseenter', startCarousel);
        card.addEventListener('mouseleave', stopCarousel);
        
        // Click on indicators to manually change image
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Clear existing interval and restart
                if (intervalId) clearInterval(intervalId);
                showImage(index);
                startCarousel();
            });
        });
    });
});

