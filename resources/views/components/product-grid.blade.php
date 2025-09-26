@props([
    'title' => 'Products',
    'products' => [],
    'columns' => 4,
    'showDiscount' => true,
    'showQuickView' => true
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
            <a href="#" class="view-all-link">View All →</a>
        </div>
        
        <div class="product-grid product-grid--{{ $columns }}-cols">
            @foreach($products as $product)
                <div class="product-card">
                    <div class="product-image">
                        <a href="{{ $product->get_permalink() }}">
                            <img src="{{ wp_get_attachment_image_url($product->get_image_id(), 'medium') }}" 
                                 alt="{{ $product->get_name() }}" />
                        </a>
                        
                        @if($showDiscount && $product->is_on_sale())
                            <div class="product-badge sale">
                                {{ round((($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100) }}% OFF
                            </div>
                        @endif
                        
                        @if($product->is_featured())
                            <div class="product-badge featured">BEST</div>
                        @endif
                        
                        @if($showQuickView)
                            <div class="product-actions">
                                <button class="quick-view-btn" data-product-id="{{ $product->get_id() }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="add-to-cart-btn" data-product-id="{{ $product->get_id() }}">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    <div class="product-info">
                        @php
                            // Get color from product attributes or variations
                            $color = '';
                            if ($product->is_type('variable')) {
                                $variations = $product->get_available_variations();
                                if (!empty($variations)) {
                                    $first_variation = $variations[0];
                                    if (isset($first_variation['attributes']['attribute_pa_color'])) {
                                        $color = $first_variation['attributes']['attribute_pa_color'];
                                    } elseif (isset($first_variation['attributes']['attribute_color'])) {
                                        $color = $first_variation['attributes']['attribute_color'];
                                    }
                                }
                            } else {
                                // For simple products, check attributes
                                $attributes = $product->get_attributes();
                                if (isset($attributes['pa_color'])) {
                                    $color = $attributes['pa_color']->get_options()[0] ?? '';
                                } elseif (isset($attributes['color'])) {
                                    $color = $attributes['color']->get_options()[0] ?? '';
                                }
                            }
                            
                            // Clean up color name
                            $color = str_replace(['-', '_'], ' ', $color);
                            $color = ucwords($color);
                        @endphp
                        
                        @if($color)
                            <div class="product-color">
                                <span class="color-label">{{ $color }}</span>
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
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.product-image {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
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
}

.product-color {
    margin-bottom: 8px;
}

.color-label {
    display: inline-block;
    padding: 4px 8px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-title {
    margin: 0 0 10px 0;
    font-size: 1rem;
    font-weight: 500;
    line-height: 1.4;
}

.product-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.2s;
}

.product-title a:hover {
    color: #000;
}

.product-price {
    margin-bottom: 10px;
}

.price-current,
.price-sale {
    font-size: 1.1rem;
    font-weight: 600;
    color: #000;
}

.price-regular {
    font-size: 0.9rem;
    color: #999;
    text-decoration: line-through;
    margin-left: 8px;
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