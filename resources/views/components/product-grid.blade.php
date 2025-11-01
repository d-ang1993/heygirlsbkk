@props([
    'title' => 'Products',
    'products' => [],
    'columns' => 4,
    'showDiscount' => true,
    'showQuickView' => true,
    'viewAllUrl' => null,
    'priorityLoadCount' => 0  // Number of products to eagerly load
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
                         data-images='@json(array_map(function($id) { return wp_get_attachment_image_url($id, "woocommerce_single"); }, $image_ids))'>
                        <a href="{{ $product->get_permalink() }}">
                            <img class="product-main-image" 
                                 src="{{ wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_single') ?: wc_placeholder_img_src('woocommerce_single') }}" 
                                 alt="{{ $product->get_name() }}" 
                                 loading="{{ $loop->index < $priorityLoadCount ? 'eager' : 'lazy' }}"
                                 decoding="async"
                                 width="400"
                                 height="400"
                                 onload="this.style.opacity=1"
                                 onerror="this.src='{{ wc_placeholder_img_src('woocommerce_single') }}'" />
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
