<?php
  // Check if this is a product page
  $is_product = get_post_type() === 'product';
  
  // Initialize all variables with default values
  $product_id = null;
  $product_name = '';
  $product_description = '';
  $product_short_description = '';
  $product_sku = '';
  $product_price = '';
  $product_regular_price = '';
  $product_sale_price = '';
  $product_stock_status = '';
  $product_stock_quantity = 0;
  $product_type = 'simple';
  $product_in_stock = false;
  $product_purchasable = false;
  $product_on_sale = false;
  $product_featured = false;
  $color = '';
  $sizes = [];
  $variations_data = [];
  $main_image = '';
  $gallery_images = [];
  $related_products = [];

  if ($is_product) {
    global $product;
    if (!$product) {
      $product = wc_get_product(get_the_ID());
    }
    if ($product) {
      $product_id = $product->get_id();
      $product_name = $product->get_name();
      $product_description = $product->get_description();
      $product_short_description = $product->get_short_description();
      $product_sku = $product->get_sku();
      $product_price = $product->get_price_html();
      $product_regular_price = $product->get_regular_price();
      $product_sale_price = $product->get_sale_price();
      $product_stock_status = $product->get_stock_status();
      $product_stock_quantity = $product->get_stock_quantity();
      $product_type = $product->get_type();
      $product_in_stock = $product->is_in_stock();
      // For variable products, consider them purchasable if they have stock
      // For simple products, use the standard is_purchasable() method
      if ($product->is_type('variable')) {
        $product_purchasable = $product->is_in_stock();
      } else {
        $product_purchasable = $product->is_purchasable();
      }
      $product_on_sale = $product->is_on_sale();
      $product_featured = $product->get_featured();
      
      // Get color information
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
        $attributes = $product->get_attributes();
        if (isset($attributes['pa_color'])) {
          $color = $attributes['pa_color']->get_options()[0] ?? '';
        } elseif (isset($attributes['color'])) {
          $color = $attributes['color']->get_options()[0] ?? '';
        }
      }

      // Get size information
      $sizes = [];
      if ($product->is_type('variable')) {
        $variations = $product->get_available_variations();
        if (!empty($variations)) {
          foreach ($variations as $variation) {
            // Check all attributes for size-related ones
            foreach ($variation['attributes'] as $attr_key => $attr_value) {
              if (stripos($attr_key, 'size') !== false && !empty($attr_value)) {
                if (!in_array($attr_value, $sizes)) {
                  $sizes[] = $attr_value;
                }
              }
            }
          }
        }
        
        // If still no sizes found, try getting from variation attributes
        if (empty($sizes)) {
          $variation_attributes = $product->get_variation_attributes();
          foreach ($variation_attributes as $attribute_name => $options) {
            if (stripos($attribute_name, 'size') !== false) {
              $sizes = array_merge($sizes, $options);
            }
          }
          $sizes = array_unique($sizes);
        }
      } else {
        $attributes = $product->get_attributes();
        foreach ($attributes as $attribute_name => $attribute) {
          if (stripos($attribute_name, 'size') !== false) {
            if (method_exists($attribute, 'get_options')) {
              $sizes = array_merge($sizes, $attribute->get_options() ?? []);
            }
          }
        }
        $sizes = array_unique($sizes);
      }
      
      // Debug: Add some common sizes if none found (for testing)
      if (empty($sizes) && current_user_can('manage_options')) {
        $sizes = ['s', 'm', 'l', 'xl', 'xxl']; // Fallback for testing
      }

      // Get all variations data for JavaScript
      $variations_data = [];
      if ($product->is_type('variable')) {
        $variations = $product->get_available_variations();
        foreach ($variations as $variation) {
          $variation_obj = wc_get_product($variation['variation_id']);
          
          // Get color and size from all possible attribute formats
          $color = '';
          $size = '';
          
          foreach ($variation['attributes'] as $attr_key => $attr_value) {
            if (stripos($attr_key, 'color') !== false) {
              $color = $attr_value;
            }
            if (stripos($attr_key, 'size') !== false) {
              $size = $attr_value;
            }
          }
          
          $variations_data[] = [
            'id' => $variation['variation_id'],
            'color' => $color,
            'sizes' => $size,
            'price' => $variation_obj->get_price_html(),
            'regular_price' => $variation_obj->get_regular_price(),
            'sale_price' => $variation_obj->get_sale_price(),
            'in_stock' => $variation_obj->is_in_stock(),
            'stock_quantity' => $variation_obj->get_stock_quantity(),
            'image' => $variation['image']['src'] ?? ''
          ];
        }
      }
      
      // Clean up color name
      $color = str_replace(['-', '_'], ' ', $color);
      $color = ucwords($color);
      
      // Get product images
      $main_image = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_single');
      $gallery_images = [];
      foreach ($product->get_gallery_image_ids() as $image_id) {
        $gallery_images[] = wp_get_attachment_image_url($image_id, 'woocommerce_single');
      }
      
      // Get related products (change the number to show more/fewer)
      $related_products = wc_get_related_products($product_id, 6);
    }
  }
?>

<?php if($is_product): ?>
  <!-- Product Page Layout -->
  <div class="single-product-page">
    <div class="container">
      
      <!-- Product Header -->
      <div class="product-header">
        <?php if($product_featured): ?>
          <div class="product-badge featured">BEST SELLER</div>
        <?php endif; ?>
        
        <?php if($product_on_sale): ?>
          <?php
            $regular_price_num = (float) $product->get_regular_price();
            $sale_price_num = (float) $product->get_sale_price();
            $discount_percentage = $regular_price_num > 0 ? round((($regular_price_num - $sale_price_num) / $regular_price_num) * 100) : 0;
          ?>
          <div class="product-badge sale">
            <?php echo e($discount_percentage); ?>% OFF
          </div>
        <?php endif; ?>
      </div>

      <!-- Product Main Content -->
      <div class="product-main">
        <?php echo $__env->make('components.product.images', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('components.product.details', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      </div>

      <!-- Product Description -->
      <?php if($product_description): ?>
        <div class="product-description">
          <h2>Description</h2>
          <div class="description-content">
            <?php echo $product_description; ?>

          </div>
        </div>
      <?php endif; ?>

      <!-- Related Products Carousel -->
      <?php if(!empty($related_products)): ?>
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
                       aria-label="View <?php echo e($related_product->get_name()); ?>">
                      <img src="<?php echo e(wp_get_attachment_image_url($related_product->get_image_id(), 'woocommerce_single')); ?>" 
                           alt="<?php echo e($related_product->get_name()); ?>"
                           loading="lazy"
                           width="400" />
                      
                      
                      <!-- Heart Icon -->
                      <div class="heart-icon" onclick="event.preventDefault(); toggleWishlist(<?php echo e($related_product->get_id()); ?>);">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </div>
                    </a>
                    
                    <!-- Color Swatches -->
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
                    
                    <!-- Product Information Container -->
                    <div class="product-info-container">
                      <!-- Color Swatches Section -->
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
                      
                      <!-- Product Name Section -->
                      <div class="product-name-section">
                        <h3><?php echo e($related_product->get_name()); ?></h3>
                      </div>
                      
                      <!-- Product Price Section -->
                      <div class="product-price-section">
                        <div class="price"><?php echo $related_product->get_price_html(); ?></div>
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

    </div>
  </div>

<?php else: ?>
  <!-- Default Layout for Posts/Pages -->
  <article <?php (post_class('h-entry')); ?>>
    <header>
      <h1 class="p-name">
        <?php echo $title; ?>

      </h1>

      <?php echo $__env->make('partials.entry-meta', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </header>

    <div class="e-content">
      <?php (the_content()); ?>
    </div>

    <?php if($pagination()): ?>
      <footer>
        <nav class="page-nav" aria-label="Page">
          <?php echo $pagination; ?>

        </nav>
      </footer>
    <?php endif; ?>

    <?php (comments_template()); ?>
  </article>
<?php endif; ?>

<?php if($is_product): ?>
  <!-- Product Page JavaScript -->
  <script>
    window.productVariations = <?php echo json_encode($variations_data ?? [], 15, 512) ?>;
    window.productAddToCartUrl = '<?php echo e($product ? $product->add_to_cart_url() : ''); ?>';
  </script>
<?php endif; ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/partials/content-single.blade.php ENDPATH**/ ?>