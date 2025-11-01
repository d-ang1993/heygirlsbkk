<?php
    // Get the current WooCommerce product if none provided
    $product = $product ?? wc_get_product();
    
    if (!$product) {
        return;
    }
    
    $product_name = $product->get_name();
    
    // Get featured image
    $featured_id = $product->get_image_id();
    $featured_url = wp_get_attachment_url($featured_id);
    
    // Get gallery images
    $gallery_ids = $product->get_gallery_image_ids();
    $gallery_urls = array_map('wp_get_attachment_url', $gallery_ids);
    
    // Merge into one array
    $all_images = array_filter(array_merge([$featured_url], $gallery_urls));
    
    // If no images, use placeholder
    if (empty($all_images)) {
        $all_images = [wc_placeholder_img_src('woocommerce_single')];
    }
?>

<!-- Product Images Carousel -->
<div class="product-images-carousel">
    <!-- Thumbnail Sidebar (if multiple images) -->
    <?php if(count($all_images) > 1): ?>
        <div class="thumbnail-sidebar">
            <?php $__currentLoopData = $all_images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="thumbnail-container <?php echo e($index === 0 ? 'active' : ''); ?>" data-image-index="<?php echo e($index); ?>">
                    <img 
                        src="<?php echo e($image); ?>" 
                        alt="<?php echo e($product_name); ?> - Image <?php echo e($index + 1); ?>" 
                        class="thumbnail-image"
                        loading="lazy"
                        width="60"
                        height="60"
                    />
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
    
    <div class="main-image-container">
        <div class="main-image" id="main-image">
            <img 
                src="<?php echo e($all_images[0]); ?>" 
                alt="<?php echo e($product_name); ?>" 
                class="product-main-image"
                id="main-product-image"
                loading="eager"
                decoding="sync"
                width="800"
                height="800"
            />
            
            <!-- Navigation Arrows (if multiple images) -->
            <?php if(count($all_images) > 1): ?>
                <button class="carousel-arrow carousel-arrow--prev" data-direction="-1">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                </button>
                <button class="carousel-arrow carousel-arrow--next" data-direction="1">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Image Dots (for mobile) - inside main image container -->
        <?php if(count($all_images) > 1): ?>
            <div class="image-dots">
                <?php $__currentLoopData = $all_images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="dot <?php echo e($index === 0 ? 'active' : ''); ?>" data-image-index="<?php echo e($index); ?>"></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Product Images Carousel - Optimized for immediate response
let currentImageIndex = 0;
const images = <?php echo json_encode($all_images, 15, 512) ?>;

function changeImage(direction) {
    if (images.length <= 1) return;
    
    currentImageIndex += direction;
    
    if (currentImageIndex >= images.length) {
        currentImageIndex = 0;
    } else if (currentImageIndex < 0) {
        currentImageIndex = images.length - 1;
    }
    
    updateImage();
}

function selectImage(index) {
    if (index >= 0 && index < images.length) {
        currentImageIndex = index;
        updateImage();
    }
}

function updateImage() {
    const mainImg = document.getElementById('main-product-image');
    if (mainImg && images[currentImageIndex]) {
        // Add a smooth fade effect
        mainImg.style.opacity = '0.7';
        setTimeout(() => {
            mainImg.src = images[currentImageIndex];
            mainImg.style.opacity = '1';
        }, 100);
    }
    
    // Update thumbnail states immediately
    const thumbnails = document.querySelectorAll('.thumbnail-container');
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.classList.toggle('active', index === currentImageIndex);
    });
    
    // Update dot states
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentImageIndex);
    });
}

// Initialize immediately - no waiting for DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Thumbnail click handlers with immediate response
    const thumbnails = document.querySelectorAll('.thumbnail-container');
    thumbnails.forEach((thumbnail) => {
        thumbnail.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const index = parseInt(this.getAttribute('data-image-index'));
            selectImage(index);
        });
    });

        // Arrow click handlers
        const arrows = document.querySelectorAll('.carousel-arrow');
        arrows.forEach((arrow) => {
            arrow.addEventListener('click', function(e) {
                e.preventDefault();
                const direction = parseInt(this.getAttribute('data-direction'));
                changeImage(direction);
            });
        });

        // Dot click handlers
        const dots = document.querySelectorAll('.dot');
        dots.forEach((dot) => {
            dot.addEventListener('click', function(e) {
                e.preventDefault();
                const index = parseInt(this.getAttribute('data-image-index'));
                selectImage(index);
            });
        });
});

// Also try to initialize immediately if DOM is already ready
if (document.readyState !== 'loading') {
    const thumbnails = document.querySelectorAll('.thumbnail-container');
    thumbnails.forEach((thumbnail) => {
        thumbnail.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const index = parseInt(this.getAttribute('data-image-index'));
            selectImage(index);
        });
    });

    const arrows = document.querySelectorAll('.carousel-arrow');
    arrows.forEach((arrow) => {
        arrow.addEventListener('click', function(e) {
            e.preventDefault();
            const direction = parseInt(this.getAttribute('data-direction'));
            changeImage(direction);
        });
    });

    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot) => {
        dot.addEventListener('click', function(e) {
            e.preventDefault();
            const index = parseInt(this.getAttribute('data-image-index'));
            selectImage(index);
        });
    });
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowLeft') {
        changeImage(-1);
    } else if (e.key === 'ArrowRight') {
        changeImage(1);
    }
});

// Touch/swipe support for mobile
let touchStartX = 0;
let touchEndX = 0;

document.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
});

document.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
});

function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;
    
    if (Math.abs(diff) > swipeThreshold) {
        if (diff > 0) {
            changeImage(1); // Swipe left - next image
        } else {
            changeImage(-1); // Swipe right - previous image
        }
    }
}
</script><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/components/product-images.blade.php ENDPATH**/ ?>