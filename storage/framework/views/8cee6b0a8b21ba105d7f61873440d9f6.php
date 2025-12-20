<?php if(!empty($related_products ?? [])): ?>
  <div class="related-products">
    <h2>Recommended Products</h2>
    <div class="related-products-carousel">
      <!-- Navigation Buttons -->
      <button class="related-carousel-arrow related-carousel-arrow--prev" 
              aria-label="Previous products"
              type="button">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      
      <button class="related-carousel-arrow related-carousel-arrow--next" 
              aria-label="Next products"
              type="button">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      
      <!-- Products Track -->
      <div class="related-products-track">
        <?php $__currentLoopData = $related_products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related_id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $related_product = wc_get_product($related_id); ?>
          <?php if($related_product): ?>
            <div class="related-product-card">
              <a href="<?php echo e($related_product->get_permalink()); ?>" 
                 aria-label="View <?php echo e($related_product->get_name()); ?>"
                 class="related-product-image-link">
                <img src="<?php echo e(wp_get_attachment_image_url($related_product->get_image_id(), 'woocommerce_single')); ?>" 
                     alt="<?php echo e($related_product->get_name()); ?>"
                     loading="lazy"
                     width="400" />
                
                <!-- Color Swatches - Overlay on Image -->
                <?php
                // Get all colors from product variations (same logic as product-grid.blade.php)
                $colors = [];
                if ($related_product->is_type('variable')) {
                  $variations = $related_product->get_available_variations();
                  foreach ($variations as $variation) {
                    $variation_attributes = $variation['attributes'];
                    $color_key = $variation_attributes['attribute_pa_color'] ?? $variation_attributes['attribute_color'] ?? '';
                    if ($color_key && !in_array($color_key, $colors)) {
                      $colors[] = $color_key;
                    }
                  }
                } else {
                  // For simple products, check attributes
                  $attributes = $related_product->get_attributes();
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
                  <div class="product-colors-section">
                    <div class="product-colors">
                      <?php $__currentLoopData = $color_swatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $swatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="color-swatch <?php echo e($index === 0 ? 'selected' : ''); ?>" 
                             style="background-color: <?php echo e($swatch['color']); ?>;"
                             title="<?php echo e($swatch['name']); ?>"></div>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                  </div>
                <?php endif; ?>
              </a>
              
              <!-- Product Information Container -->
              <div class="product-info-container">
                  <!-- Product Name and Price Row -->
                <div class="product-name-price-row">
                  <div class="product-name-section">
                    <h3><a href="<?php echo e($related_product->get_permalink()); ?>"><?php echo e($related_product->get_name()); ?></a></h3>
                  </div>
                  
                  <div class="product-price-section">
                    <?php
                      // Get the minimum price (cheapest price if there's a range)
                      $min_price = $related_product->get_price();
                      if ($related_product->is_type('variable')) {
                        $prices = $related_product->get_variation_prices();
                        if (!empty($prices['price'])) {
                          $min_price = min($prices['price']);
                        }
                      }
                      $price_html = wc_price($min_price);
                    ?>
                    <div class="price"><?php echo $price_html; ?></div>
                  </div>
                </div>
              </div>
              
            </div>
          <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
    
    <!-- Carousel Dots -->
    <div class="related-carousel-dots"></div>
  </div>
<?php else: ?>
  <div class="related-products empty">
    <h2>Recommended Products</h2>
    <p>No related products found at the moment.</p>
  </div>
<?php endif; ?>

<?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/components/product/recommended.blade.php ENDPATH**/ ?>