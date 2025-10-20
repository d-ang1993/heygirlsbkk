@php
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
@endphp

<!-- Product Images Carousel -->
<div class="product-images-carousel">
    <!-- Thumbnail Sidebar (if multiple images) -->
    @if(count($all_images) > 1)
        <div class="thumbnail-sidebar">
            @foreach($all_images as $index => $image)
                <div class="thumbnail-container {{ $index === 0 ? 'active' : '' }}" onclick="selectImage({{ $index }})">
                    <img 
                        src="{{ $image }}" 
                        alt="{{ $product_name }} - Image {{ $index + 1 }}" 
                        class="thumbnail-image"
                        loading="lazy"
                        width="60"
                        height="60"
                    />
                </div>
            @endforeach
        </div>
    @endif
    
    <div class="main-image-container">
        <div class="main-image" id="main-image">
            <img 
                src="{{ $all_images[0] }}" 
                alt="{{ $product_name }}" 
                class="product-main-image"
                id="main-product-image"
                loading="eager"
                decoding="sync"
                width="800"
                height="800"
            />
            
            <!-- Navigation Arrows (if multiple images) -->
            @if(count($all_images) > 1)
                <button class="carousel-arrow carousel-arrow--prev" onclick="changeImage(-1)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                </button>
                <button class="carousel-arrow carousel-arrow--next" onclick="changeImage(1)">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>

<script>
// Simple Product Images Carousel
let currentImageIndex = 0;
const images = @json($all_images);

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
        mainImg.src = images[currentImageIndex];
    }
    
    // Update thumbnail states
    const thumbnails = document.querySelectorAll('.thumbnail-container');
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.classList.toggle('active', index === currentImageIndex);
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
</script>