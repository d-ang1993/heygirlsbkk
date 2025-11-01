
<?php
  global $product;
  
  // Ensure we have a valid product object
  if (!$product) {
    $product = wc_get_product(get_the_ID());
  }
  
  // If still no product and we have a post ID, try to get it directly
  if (!$product && get_the_ID() > 0) {
    $product = wc_get_product(get_the_ID());
  }
  
  // Skip if still no valid product
  if (!$product || !is_a($product, 'WC_Product')) {
    echo '<!-- No valid product found for ID: ' . get_the_ID() . ' -->';
    return;
  }
?>

<div class="product-card">
  <div class="product-image">
    <a href="<?php echo e($product->get_permalink()); ?>">
      <img src="<?php echo e(wp_get_attachment_image_url($product->get_image_id(), 'product-grid')); ?>" 
           alt="<?php echo e($product->get_name()); ?>" 
           loading="lazy"
           decoding="async"
           width="500"
           height="500" />
    </a>
    
    <?php if($product->is_on_sale()): ?>
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
      <div class="product-badge featured fire-badge">BEST <span class="fire-emoji">🔥</span></div>
    <?php endif; ?>
  </div>
  
  <div class="product-info">
    <?php
      // Get product brand (collection name)
      $terms = get_the_terms($product->get_id(), 'product_brand');
      $product_brand = $terms && !is_wp_error($terms) ? $terms[0]->name : '';
    ?>
    
    <?php if($product_brand): ?>
      <div class="product-brand"><?php echo e($product_brand); ?></div>
    <?php endif; ?>
    
    <?php
      // Get available variations for variable products to display color dots
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
      }
    ?>
    
    <?php if(!empty($colors)): ?>
      <div class="product-colors">
        <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <span class="color-dot" style="background-color: <?php echo e(strtolower($color)); ?>;" title="<?php echo e($color); ?>"></span>
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
<?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/woocommerce/content-product.blade.php ENDPATH**/ ?>