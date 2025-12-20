@php
    // Get the current WooCommerce product if none provided
    $product = $product ?? wc_get_product();
    
    if (!$product) {
        return;
    }
    
    $product_name = $product->get_name();
    
    // Get featured image ID
    $featured_id = $product->get_image_id();
    
    // Helper function to create image data structure with main and thumbnail
    // Use 'full' or 'large' size for better quality, browser will select appropriate from srcset
    $make = function($id) {
        // Use 'full' size for maximum quality, srcset will handle responsive sizing
        $full_url = wp_get_attachment_image_url($id, 'full');
        $large_url = wp_get_attachment_image_url($id, 'large');
        $single_url = wp_get_attachment_image_url($id, 'woocommerce_single');
        
        // Generate comprehensive srcset with multiple sizes for better browser selection
        $srcset = wp_get_attachment_image_srcset($id, 'full');
        
        // If srcset is empty, create a basic one
        if (empty($srcset) && $full_url) {
            $image_meta = wp_get_attachment_metadata($id);
            if ($image_meta && isset($image_meta['width'])) {
                $srcset = $full_url . ' ' . $image_meta['width'] . 'w';
            }
        }
        
        return [
            'main'  => $full_url ?: $single_url, // Use full size for best quality
            'main_srcset' => $srcset,
            // Accurate sizes attribute: desktop is ~60% of 1200px container = ~720px, but account for padding
            // Mobile is full viewport width
            'main_sizes'  => '(max-width: 768px) 100vw, (max-width: 1200px) 60vw, 720px',
            'thumb' => wp_get_attachment_image_url($id, 'woocommerce_thumbnail'),
            'thumb_srcset' => wp_get_attachment_image_srcset($id, 'woocommerce_thumbnail'),
            'thumb_sizes'  => '60px',
        ];
    };
    
    $main_images = [];
    
    // Add featured image
    if ($featured_id) {
        $main_images[] = $make($featured_id);
    }
    
    // Add gallery images
    foreach ($product->get_gallery_image_ids() as $gid) {
        $main_images[] = $make($gid);
    }
    
    // If no images, use placeholder
    if (empty($main_images)) {
        $main_images = [[
            'main' => wc_placeholder_img_src('woocommerce_single'),
            'main_srcset' => '',
            'main_sizes' => '',
            'thumb' => wc_placeholder_img_src('woocommerce_thumbnail'),
            'thumb_srcset' => '',
            'thumb_sizes' => '60px',
        ]];
    }
    
@endphp

<!-- Product Images Carousel -->
<div class="product-images-carousel">
    <!-- Preload main product image for faster initial load -->
    @if(!empty($main_images[0]['main']))
        <link rel="preload" as="image" href="{{ $main_images[0]['main'] }}" fetchpriority="high">
        @if(isset($main_images[1]['main']) && !empty($main_images[1]['main']))
            <link rel="preload" as="image" href="{{ $main_images[1]['main'] }}">
        @endif
    @endif
    
    <!-- Thumbnail Sidebar (if multiple images) -->
    @if(count($main_images) > 1)
        <div class="thumbnail-sidebar">
            @foreach($main_images as $index => $img)
                <div class="thumbnail-container {{ $index === 0 ? 'active' : '' }}" data-image-index="{{ $index }}">
                    <img 
                        src="{{ $img['thumb'] }}" 
                        @if(!empty($img['thumb_srcset']))
                            srcset="{{ $img['thumb_srcset'] }}"
                            sizes="{{ $img['thumb_sizes'] }}"
                        @endif
                        alt="{{ $product_name }} - Image {{ $index + 1 }}" 
                        class="thumbnail-image"
                        loading="{{ $index < 2 ? 'eager' : 'lazy' }}"
                        decoding="async"
                        width="60"
                        height="60"
                        fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}"
                    />
                </div>
            @endforeach
        </div>
    @endif
    
    <div class="main-image-container">
        <div class="main-image" id="main-image">
            @php
                $img = $main_images[0];
            @endphp
            <img 
                src="{{ $img['main'] }}"
                @if(!empty($img['main_srcset']))
                    srcset="{{ $img['main_srcset'] }}"
                    sizes="{{ $img['main_sizes'] }}"
                @endif
                alt="{{ $product_name }}" 
                class="product-main-image"
                id="main-product-image"
                loading="eager"
                decoding="async"
                fetchpriority="high"
            />
            
            <!-- Navigation Arrows (if multiple images) -->
            @if(count($main_images) > 1)
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
            @endif
        </div>
        
        <!-- Image Dots (for mobile) - inside main image container -->
        @if(count($main_images) > 1)
            <div class="image-dots">
                @foreach($main_images as $index => $img)
                    <span class="dot {{ $index === 0 ? 'active' : '' }}" data-image-index="{{ $index }}"></span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
// Product Images Carousel - Optimized for immediate response
let currentImageIndex = 0;
const imagesData = @json($main_images); // Full image data with srcset for updates

// Reusable prefetch hint element for next image
let nextHintEl = null;
function hintNext(url) {
    if (!url) return;
    if (!nextHintEl) {
        nextHintEl = document.createElement('link');
        nextHintEl.rel = 'prefetch'; // better than preload for "next"
        nextHintEl.as = 'image';
        document.head.appendChild(nextHintEl);
    }
    nextHintEl.href = url;
}

function changeImage(direction) {
    if (imagesData.length <= 1) return;
    
    currentImageIndex += direction;
    
    if (currentImageIndex >= imagesData.length) {
        currentImageIndex = 0;
    } else if (currentImageIndex < 0) {
        currentImageIndex = imagesData.length - 1;
    }
    
    updateImage();
}

function selectImage(index) {
    if (index >= 0 && index < imagesData.length) {
        currentImageIndex = index;
        updateImage();
    }
}

function updateImage() {
    const mainImg = document.getElementById('main-product-image');
    if (!mainImg || !imagesData[currentImageIndex]) return;
    
    const imageData = imagesData[currentImageIndex];
    const newImageUrl = imageData.main;
    
    // Hint next image for smoother transitions (reuses single prefetch element)
    const nextIndex = (currentImageIndex + 1) % imagesData.length;
    hintNext(imagesData[nextIndex]?.main);
    
    // Add a smooth fade effect
    mainImg.style.opacity = '0.7';
    mainImg.style.transition = 'opacity 0.2s ease';
    
    // Update src and srcset - rely on browser caching + prefetch hint for smooth loading
    // Important: Update srcset BEFORE src to prevent blurry intermediate state
    if (imageData.main_srcset) {
        mainImg.srcset = imageData.main_srcset;
    } else {
        mainImg.removeAttribute('srcset');
    }
    if (imageData.main_sizes) {
        mainImg.sizes = imageData.main_sizes;
    } else {
        mainImg.removeAttribute('sizes');
    }
    // Set src last to trigger load with correct srcset already in place
    mainImg.src = newImageUrl;
    
    // Restore opacity once image loads (or immediately if cached)
    if (mainImg.complete && mainImg.naturalHeight !== 0) {
        // Image already loaded/cached
        mainImg.style.opacity = '1';
    } else {
        // Wait for load
        mainImg.onload = function() {
            mainImg.style.opacity = '1';
        };
    }
    
    // Update thumbnail states immediately
    const thumbnails = document.querySelectorAll('.thumbnail-container');
    const thumbnailSidebar = document.querySelector('.thumbnail-sidebar');
    const activeThumbnail = thumbnails[currentImageIndex];
    
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.classList.toggle('active', index === currentImageIndex);
    });
    
    // Scroll active thumbnail into view
    if (activeThumbnail && thumbnailSidebar) {
        activeThumbnail.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'nearest'
        });
    }
    
    // Update dot states
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentImageIndex);
    });
}

// Initialize event listeners - only once
let initialized = false;

function initializeCarousel() {
    if (initialized) return;
    initialized = true;
    
    // Thumbnail click handlers
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
            e.stopPropagation();
            const direction = parseInt(this.getAttribute('data-direction'));
            changeImage(direction);
        });
    });

    // Dot click handlers
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot) => {
        dot.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const index = parseInt(this.getAttribute('data-image-index'));
            selectImage(index);
        });
    });
}

// Initialize immediately if DOM is ready, otherwise wait for DOMContentLoaded
if (document.readyState !== 'loading') {
    initializeCarousel();
} else {
    document.addEventListener('DOMContentLoaded', initializeCarousel);
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
</script>