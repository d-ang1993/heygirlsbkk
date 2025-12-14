<!-- Quantity and Add to Cart -->
<div class="product-form">
  <?php if($product_purchasable): ?>
    <div class="add-to-cart-section">
      <div class="quantity-selector">
        <div class="quantity-label">QUANTITY</div>
        <div class="quantity-controls">
          <button type="button" class="quantity-btn minus">-</button>
          <span class="quantity-value">1</span>
          <button type="button" class="quantity-btn plus">+</button>
        </div>
      </div>
      
      <?php if($product_type === 'variable'): ?>
        
        <form class="custom-add-cart-form" method="post" enctype="multipart/form-data" data-product_id="<?php echo e($product_id); ?>">
          <?php echo csrf_field(); ?>
          
          <input type="hidden" name="add-to-cart" value="<?php echo e($product_id); ?>">
          <input type="hidden" name="product_id" value="<?php echo e($product_id); ?>">
          <input type="hidden" name="variation_id" value="0" id="variation_id">
          <input type="hidden" name="quantity" value="1" id="quantity">
          
          
          <?php if(isset($variations_data) && !empty($variations_data)): ?>
            <?php
              $available_attributes = [];
              foreach($variations_data as $variation) {
                foreach($variation as $key => $value) {
                  if(!in_array($key, ['id', 'price', 'regular_price', 'sale_price', 'image', 'price_html', 'in_stock', 'stock_quantity', 'variation_id']) && !empty($value)) {
                    $available_attributes[$key] = $value;
                  }
                }
              }
            ?>
            
            <?php $__currentLoopData = $available_attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <input type="hidden" name="attribute_<?php echo e($attribute); ?>" value="" id="attribute_<?php echo e($attribute); ?>">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endif; ?>
          
          <button type="button" class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo e($product_id); ?>" disabled>
            ADD TO CART
          </button>
        </form>
      <?php else: ?>
        <form class="custom-add-cart-form" action="<?php echo e(wc_get_cart_url()); ?>" method="post" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="add-to-cart" value="<?php echo e($product_id); ?>">
          <input type="hidden" name="quantity" value="1" class="quantity-input">
          <button type="button" name="add-to-cart" value="<?php echo e($product_id); ?>" class="btn btn-primary add-to-cart-btn" data-product_id="<?php echo e($product_id); ?>">
            ADD TO CART
          </button>
        </form>
      <?php endif; ?>
      
      
      
      <?php if($product_type === 'variable'): ?>
        
        <?php echo do_shortcode('[ti_wishlists_addtowishlist product_id="' . $product_id . '" variation_id="0"]'); ?>

      <?php else: ?>
        
        <?php echo do_shortcode('[ti_wishlists_addtowishlist product_id="' . $product_id . '"]'); ?>

      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="product-unavailable">
      <p>This product is currently unavailable.</p>
    </div>
  <?php endif; ?>
</div>

<?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/components/product/form.blade.php ENDPATH**/ ?>