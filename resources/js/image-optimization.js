/**
 * Image optimization utilities
 */

// Detect device pixel ratio and set cookie for server-side optimization
function detectDevicePixelRatio() {
    const pixelRatio = window.devicePixelRatio || 1;
    document.cookie = `devicePixelRatio=${pixelRatio}; path=/; max-age=31536000`; // 1 year
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    detectDevicePixelRatio();
    
    // Add intersection observer for lazy loading enhancement
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Add fade-in effect when image loads
                    img.addEventListener('load', function() {
                        img.style.opacity = '1';
                        img.style.transition = 'opacity 0.3s ease';
                    });
                    
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        // Observe all lazy-loaded images
        document.querySelectorAll('img[loading="lazy"]').forEach(img => {
            img.style.opacity = '0';
            imageObserver.observe(img);
        });
    }
});

// Preload critical images
function preloadImage(src) {
    const link = document.createElement('link');
    link.rel = 'preload';
    link.as = 'image';
    link.href = src;
    document.head.appendChild(link);
}

// Export for use in other scripts
window.ImageOptimization = {
    detectDevicePixelRatio,
    preloadImage
};