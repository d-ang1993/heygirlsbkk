<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Products',
    'products' => [],
    'columns' => 4,
    'showDiscount' => true,
    'showQuickView' => true,
    'viewAllUrl' => null,
    'priorityLoadCount' => 0  // Number of products to eagerly load
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => 'Products',
    'products' => [],
    'columns' => 4,
    'showDiscount' => true,
    'showQuickView' => true,
    'viewAllUrl' => null,
    'priorityLoadCount' => 0  // Number of products to eagerly load
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Get products if none provided
    if (empty($products)) {
        $products = wc_get_products([
            'limit' => 8,
            'status' => 'publish'
        ]);
    }
?>

<section class="product-grid-section">
    <div class="container">
        <?php if(!empty($title)): ?>
            <div class="section-header">
                <h2 class="section-title"><?php echo e($title); ?></h2>
                <?php if($viewAllUrl): ?>
                    <a href="<?php echo e($viewAllUrl); ?>" class="view-all-link">View All →</a>
                <?php else: ?>
                    <a href="<?php echo e(wc_get_page_permalink('shop')); ?>" class="view-all-link">View All →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="product-grid product-grid--<?php echo e($columns); ?>-cols">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
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
                ?>
                
                <div class="product-card" data-product-id="<?php echo e($product->get_id()); ?>">
                    <div class="product-image <?php echo e($has_multiple_images ? 'has-carousel' : ''); ?>" 
                         data-images='<?php echo json_encode(array_map(function($id) { return wp_get_attachment_image_url($id, "woocommerce_single"); }, $image_ids)) ?>'>
                        <a href="<?php echo e($product->get_permalink()); ?>">
                            <img class="product-main-image" 
                                 src="<?php echo e(wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_single') ?: wc_placeholder_img_src('woocommerce_single')); ?>" 
                                 alt="<?php echo e($product->get_name()); ?>" 
                                 loading="<?php echo e($loop->index < $priorityLoadCount ? 'eager' : 'lazy'); ?>"
                                 decoding="async"
                                 width="400"
                                 height="400"
                                 onload="this.style.opacity=1"
                                 onerror="this.src='<?php echo e(wc_placeholder_img_src('woocommerce_single')); ?>'" />
                        </a>
                        
                        <?php if($has_multiple_images): ?>
                            <div class="image-indicators">
                                <?php $__currentLoopData = $image_ids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $img_id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="indicator <?php echo e($index === 0 ? 'active' : ''); ?>" data-index="<?php echo e($index); ?>"></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($showDiscount && $product->is_on_sale()): ?>
                            <?php
                                $regular_price_num = (float) $product->get_regular_price();
                                $sale_price_num = (float) $product->get_sale_price();
                                $discount_percentage = $regular_price_num > 0 ? round((($regular_price_num - $sale_price_num) / $regular_price_num) * 100) : 0;
                            ?>
                            <div class="product-badge sale">
                                <?php echo e($discount_percentage); ?>% OFF
                            </div>
                        <?php endif; ?>
                        
                        <?php if($product->is_featured()): ?>
                            <div class="product-badge featured fire-badge">BEST 🔥</div>
                        <?php endif; ?>
                        
                        <?php if($showQuickView): ?>
                            <div class="product-actions">
                                <button class="quick-view-btn" data-product-id="<?php echo e($product->get_id()); ?>">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <!-- <button class="add-to-cart-btn" data-product-id="<?php echo e($product->get_id()); ?>">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                </button> -->
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <?php
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
                        ?>
                        
                        <?php if(!empty($color_swatches)): ?>
                            <div class="product-colors">
                                <?php $__currentLoopData = $color_swatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $swatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="color-swatch" 
                                         style="background-color: <?php echo e($swatch['color']); ?>;"
                                         title="<?php echo e($swatch['name']); ?>"></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="product-title">
                            <a href="<?php echo e($product->get_permalink()); ?>"><?php echo e($product->get_name()); ?></a>
                        </h3>
                        
                        <div class="product-price">
                            <?php if($product->is_on_sale()): ?>
                                <span class="price-sale"><?php echo $product->get_price_html(); ?></span>
                                <span class="price-regular"><?php echo wc_price($product->get_regular_price()); ?></span>
                            <?php else: ?>
                                <span class="price-current"><?php echo $product->get_price_html(); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/components/product-grid.blade.php ENDPATH**/ ?>