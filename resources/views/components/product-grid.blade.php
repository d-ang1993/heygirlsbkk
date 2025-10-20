@props([
    'title' => 'Products',
    'products' => [],
    'columns' => 4,
    'showDiscount' => true,
    'showQuickView' => true,
    'viewAllUrl' => null
])

@php
    // Get products if none provided
    if (empty($products)) {
        $products = wc_get_products([
            'limit' => 8,
            'status' => 'publish'
        ]);
    }
@endphp

<section class="product-grid-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">{{ $title }}</h2>
            @if($viewAllUrl)
                <a href="{{ $viewAllUrl }}" class="view-all-link">View All →</a>
            @else
                <a href="{{ wc_get_page_permalink('shop') }}" class="view-all-link">View All →</a>
            @endif
        </div>
        
        <div class="product-grid product-grid--{{ $columns }}-cols">
            @foreach($products as $product)
                @php
                    // Get all product images (gallery + featured)
                    $image_ids = [];
                    $featured_image_id = $product->get_image_id();
                    if ($featured_image_id) {
                        $image_ids[] = $featured_image_id;
                    }
                    $gallery_ids = $product->get_gallery_image_ids();
                    if (!empty($gallery_ids)) {
                        $image_ids = array_merge($image_ids, $gallery_ids);
                    }
                    $has_multiple_images = count($image_ids) > 1;
                @endphp
                
                <div class="product-card" data-product-id="{{ $product->get_id() }}">
                    <div class="product-image {{ $has_multiple_images ? 'has-carousel' : '' }}" 
                         data-images='@json(array_map(function($id) { return wp_get_attachment_image_url($id, "product-grid"); }, $image_ids))'>
                        <a href="{{ $product->get_permalink() }}">
                            <img class="product-main-image" 
                                 src="{{ wp_get_attachment_image_url($product->get_image_id(), 'product-grid') }}" 
                                 alt="{{ $product->get_name() }}" 
                                 loading="lazy"
                                 decoding="async"
                                 width="500"
                                 height="500" />
                        </a>
                        
                        @if($has_multiple_images)
                            <div class="image-indicators">
                                @foreach($image_ids as $index => $img_id)
                                    <span class="indicator {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}"></span>
                                @endforeach
                            </div>
                        @endif
                        
                        @if($showDiscount && $product->is_on_sale())
                            @php
                                $regular_price_num = (float) $product->get_regular_price();
                                $sale_price_num = (float) $product->get_sale_price();
                                $discount_percentage = $regular_price_num > 0 ? round((($regular_price_num - $sale_price_num) / $regular_price_num) * 100) : 0;
                            @endphp
                            <div class="product-badge sale">
                                {{ $discount_percentage }}% OFF
                            </div>
                        @endif
                        
                        @if($product->is_featured())
                            <div class="product-badge featured fire-badge">BEST 🔥</div>
                        @endif
                        
                        @if($showQuickView)
                            <div class="product-actions">
                                <button class="quick-view-btn" data-product-id="{{ $product->get_id() }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <!-- <button class="add-to-cart-btn" data-product-id="{{ $product->get_id() }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                </button> -->
                            </div>
                        @endif
                    </div>
                    
                    <div class="product-info">
                        @php
                            // Get all colors from product variations
                            $colors = [];
                            if ($product->is_type('variable')) {
                                $variations = $product->get_available_variations();
                                foreach ($variations as $variation) {
                                    $variation_attributes = $variation['attributes'];
                                    $color_key = $variation_attributes['attribute_pa_color'] ?? $variation_attributes['attribute_color'] ?? '';
                                    if ($color_key && !in_array($color_key, $colors)) {
                                        $colors[] = $color_key;
                                    }
                                }
                            } else {
                                // For simple products, check attributes
                                $attributes = $product->get_attributes();
                                if (isset($attributes['pa_color'])) {
                                    $color_options = $attributes['pa_color']->get_options() ?? [];
                                    $colors = array_merge($colors, $color_options);
                                } elseif (isset($attributes['color'])) {
                                    $color_options = $attributes['color']->get_options() ?? [];
                                    $colors = array_merge($colors, $color_options);
                                }
                            }
                            
                            // Get color swatches from plugin
                            $color_swatches = [];
                            foreach ($colors as $color_key) {
                                $color_name = str_replace(['-', '_'], ' ', $color_key);
                                $color_name = ucwords($color_name);
                                
                                // Get the term by slug from the 'pa_color' taxonomy
                                $color_term = get_term_by('slug', $color_key, 'pa_color');
                                
                                // Default fallback color
                                $dot_color = '#cccccc';
                                
                                if ($color_term) {
                                    // Get hex color from Variation Swatches for WooCommerce plugin
                                    $color_meta = get_term_meta($color_term->term_id, 'product_attribute_color', true);
                                    if (!empty($color_meta)) {
                                        $dot_color = $color_meta;
                                    }
                                }
                                
                                $color_swatches[] = [
                                    'name' => $color_name,
                                    'key' => $color_key,
                                    'color' => $dot_color
                                ];
                            }
                        @endphp
                        
                        @if(!empty($color_swatches))
                            <div class="product-colors">
                                @foreach($color_swatches as $swatch)
                                    <div class="color-swatch" 
                                         style="background-color: {{ $swatch['color'] }};"
                                         title="{{ $swatch['name'] }}"></div>
                                @endforeach
                            </div>
                        @endif
                        
                        <h3 class="product-title">
                            <a href="{{ $product->get_permalink() }}">{{ $product->get_name() }}</a>
                        </h3>
                        
                        <div class="product-price">
                            @if($product->is_on_sale())
                                <span class="price-sale">{!! $product->get_price_html() !!}</span>
                                <span class="price-regular">{!! wc_price($product->get_regular_price()) !!}</span>
                            @else
                                <span class="price-current">{!! $product->get_price_html() !!}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<style>
.product-grid-section {
    padding: 60px 0;
    background: #fff;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: #000;
    margin: 0;
}

.view-all-link {
    color: #666;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.view-all-link:hover {
    color: #000;
}

.product-grid {
    display: grid;
    gap: 30px;
}

.product-grid--2-cols {
    grid-template-columns: repeat(2, 1fr);
}

.product-grid--3-cols {
    grid-template-columns: repeat(3, 1fr);
}

.product-grid--4-cols {
    grid-template-columns: repeat(4, 1fr);
}

.product-grid--5-cols {
    grid-template-columns: repeat(5, 1fr);
}

.product-card {
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%; /* Ensure all cards have same height */
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.product-image {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1;
    flex-shrink: 0; /* Don't shrink the image */
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease, opacity 0.5s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.image-indicators {
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    z-index: 3;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .image-indicators {
    opacity: 1;
}

.indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.indicator.active {
    background: #fff;
    width: 20px;
    border-radius: 3px;
}

.indicator.active::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: rgba(255, 192, 203, 0.8); /* Pink color */
    width: 100%;
    border-radius: inherit;
    animation: progressFill 3s linear forwards;
}

@keyframes progressFill {
    0% {
        width: 0%;
    }
    100% {
        width: 100%;
    }
}

.product-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    z-index: 2;
}

.product-badge.sale {
    background: #ff4757;
}

.product-badge.featured {
    background: #2ed573;
}

.product-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 2;
}

.product-card:hover .product-actions {
    opacity: 1;
}

.quick-view-btn,
.add-to-cart-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.9);
    color: #333;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    backdrop-filter: blur(4px);
}

.quick-view-btn:hover,
.add-to-cart-btn:hover {
    background: #000;
    color: #fff;
    transform: scale(1.1);
}

.product-info {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 120px; /* Ensure consistent card height */
}

/* Color swatches section - fixed height */
.product-colors {
    min-height: 24px; /* Reserve space for color swatches */
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}

.color-swatch {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 1px solid rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

/* Product title section - fixed height */
.product-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 500;
    line-height: 1.4;
    min-height: 28px; /* Reserve space for title (2 lines max) */
    display: flex;
}

.product-title a {
    color: #333;
    text-decoration: none;
    transition: all 0.4s ease;
    display: block;
    width: 100%;
    text-align: center;
}

.product-title a:hover {
    background: linear-gradient(45deg, #ff6b9d, #ff8fab,rgb(159, 136, 144));
    background-size: 200% 200%;
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: gradientShift 1.5s ease-in-out infinite;
    transform: scale(1.05);
}

@keyframes gradientShift {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

/* Price section - fixed height */
.product-price {
    min-height: 48px; /* Reserve space for price + sale price */
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 2px;
}

.price-current,
.price-sale {
    font-size: 1.1rem;
    font-weight: 600;
    color: #ff6b9d; /* Pink color */
}

.price-regular {
    font-size: 0.9rem;
    color: #999;
    text-decoration: line-through;
    margin: 0;
}

/* Handle empty sections gracefully */
.product-colors:empty {
    display: block; /* Still show the reserved space */
}

.product-price:empty {
    display: block; /* Still show the reserved space */
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stars {
    display: flex;
    gap: 2px;
}

.star {
    color: #ddd;
    font-size: 14px;
}

.star.filled {
    color: #ffd700;
}

.review-count {
    font-size: 12px;
    color: #666;
}

/* Responsive */
@media (max-width: 1024px) {
    .product-grid {
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 15px;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    .product-info {
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productCards = document.querySelectorAll('.product-card');
    
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
            
            // Remove all active classes first
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
</script>