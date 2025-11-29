<?php $__env->startSection('content'); ?>
  <div class="heygirlsbkk-cart-container">
    <?php do_action('woocommerce_before_cart'); ?>

    
    <div class="cart-header">
      <div class="cart-header-left">
        <h1 class="cart-title">My Bag (<?php echo e(WC()->cart->get_cart_contents_count()); ?>)</h1>
      </div>
      <div class="cart-header-right">
        <span class="cart-help">Need Help? +852 8009 06220</span>
      </div>
    </div>

    
    <?php if(WC()->cart->is_empty()): ?>
      <div class="cart-empty-state">
        <div class="empty-cart-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
        </div>
        <h2>Your bag is empty</h2>
        <p>Add some items to get started</p>
        <a href="<?php echo e(wc_get_page_permalink('shop')); ?>" class="btn btn-primary">
          Continue Shopping
        </a>
      </div>

    <?php else: ?>
      
      <div class="cart-main-layout">
        
        
        <div class="cart-items-section">
          <form class="woocommerce-cart-form" action="<?php echo e(wc_get_cart_url()); ?>" method="post">
            <?php do_action('woocommerce_before_cart_table'); ?>
            
            <div class="cart-items-list">
              <?php $__currentLoopData = WC()->cart->get_cart(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cart_item_key => $cart_item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                  $product_permalink = $_product && $_product->is_visible() ? $_product->get_permalink($cart_item) : '';
                  $variation_data = wc_get_formatted_cart_item_data($cart_item, true);
                ?>

                <?php if($_product && $_product->exists() && $cart_item['quantity'] > 0): ?>
                  <div class="cart-item-heygirlsbkk">
                    <div class="cart-item-image">
                      <img src="<?php echo e(wp_get_attachment_image_url($_product->get_image_id(), 'woocommerce_thumbnail')); ?>" 
                           alt="<?php echo e($_product->get_name()); ?>" 
                           loading="lazy" />
                    </div>
                    
                    <div class="cart-item-details">
                      <div class="cart-item-info">
                        <h3 class="cart-item-name">
                          <a href="<?php echo e(esc_url($product_permalink)); ?>">
                            <?php echo e($_product->get_name()); ?>

                          </a>
                        </h3>
                        <?php if($variation_data): ?>
                          <div class="cart-item-variation"><?php echo $variation_data; ?></div>
                        <?php endif; ?>
                        
                      </div>
                      
                      <div class="cart-item-controls">
                        <div class="quantity-controls">
                          <button type="button" class="quantity-btn minus" data-cart-item-key="<?php echo e($cart_item_key); ?>">-</button>
                          <input type="number" 
                                 class="quantity-input" 
                                 value="<?php echo e($cart_item['quantity']); ?>" 
                                 min="1" 
                                 data-cart-item-key="<?php echo e($cart_item_key); ?>"
                                 name="cart[<?php echo e($cart_item_key); ?>][qty]">
                          <button type="button" class="quantity-btn plus" data-cart-item-key="<?php echo e($cart_item_key); ?>">+</button>
                        </div>
                        
                        <div class="cart-item-price">
                          <?php echo WC()->cart->get_product_subtotal($_product, $cart_item['quantity']); ?>

                        </div>
                      </div>
                    </div>
                    
                    <button type="button" 
                            class="cart-item-remove" 
                            data-cart-item-key="<?php echo e($cart_item_key); ?>"
                            title="Remove item">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                <?php endif; ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            
            <?php 
              do_action('woocommerce_cart_actions');
              wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce');
              do_action('woocommerce_after_cart_table');
            ?>
          </form>
        </div>

        
        <div class="cart-summary-section">
          <div class="cart-summary-content">
            
            <div class="promo-code-section">
              <button class="promo-code-toggle" type="button">
                <span class="promo-code-icon">+</span>
                <span class="promo-code-text">Enter promo code</span>
              </button>
              <div class="promo-code-form" style="display: none;">
                <input type="text" placeholder="Enter promo code" class="promo-code-input">
                <button type="button" class="promo-code-apply">Apply</button>
              </div>
            </div>

            
            <div class="order-totals">
              <div class="total-line subtotal">
                <span class="total-label">Subtotal:</span>
                <span class="total-amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
              </div>
              
              <div class="total-line shipping">
                <span class="total-label">Shipping:</span>
                <span class="total-amount">฿ 180.00</span>
              </div>
              
              <div class="total-line estimated-total">
                <span class="total-label">Estimated Total:</span>
                <span class="total-amount"><?php echo WC()->cart->get_cart_total(); ?></span>
              </div>
            </div>

            
            <div class="checkout-section">
              <a href="<?php echo e(wc_get_checkout_url()); ?>" class="btn-checkout">
                CHECKOUT
              </a>
              <p class="checkout-disclaimer">
                By clicking "Checkout", you will be redirected to our secure checkout page where payment will be processed and your order will be fulfilled by HeyGirlsBKK.
              </p>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
    <?php do_action('woocommerce_after_cart'); ?>
  </div>

  
  <style>
    .heygirlsbkk-cart-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      font-family: 'Helvetica Neue', Arial, sans-serif;
    }

    .cart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e5e7eb;
    }

    .cart-title {
      font-size: 24px;
      font-weight: 600;
      color: #1f2937;
      margin: 0;
    }

    .cart-help {
      color: #6b7280;
      font-size: 14px;
    }

    .cart-empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #6b7280;
    }

    .empty-cart-icon {
      margin-bottom: 20px;
      color: #d1d5db;
    }

    .cart-empty-state h2 {
      font-size: 24px;
      font-weight: 600;
      color: #374151;
      margin: 0 0 10px 0;
    }

    .cart-empty-state p {
      margin: 0 0 30px 0;
      font-size: 16px;
    }

    .cart-main-layout {
      display: grid;
      grid-template-columns: 1fr 400px;
      gap: 40px;
      align-items: start;
    }

    .cart-items-section {
      background: #fff;
    }

    .cart-items-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .cart-item-heygirlsbkk {
      display: flex;
      gap: 30px;
      padding: 30px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      background: #fff;
      position: relative;
      margin-bottom: 20px;
    }

    .cart-item-image {
      width: 240px;
      height: 240px;
      border-radius: 6px;
      overflow: hidden;
      flex-shrink: 0;
    }

    .cart-item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .cart-item-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .cart-item-info {
      flex: 1;
    }

    .cart-item-name {
      margin: 0 0 8px 0;
      font-size: 16px;
      font-weight: 600;
      color: #1f2937;
    }

    .cart-item-name a {
      color: inherit;
      text-decoration: none;
    }

    .cart-item-name a:hover {
      text-decoration: underline;
    }

    .cart-item-variation {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 10px;
    }


    .cart-item-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .quantity-controls {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .quantity-btn {
      width: 28px; 
      height: 28px; 
      border: 1px solid #d1d5db; 
      background: #fff; 
      cursor: pointer; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      font-size: 16px; 
      font-weight: 500; 
      color: #374151;
      transition: all 0.2s ease;
      padding: 0;
      border-radius: 4px;
    }

    .quantity-btn:hover { 
      background: #f9fafb;
      border-color: #9ca3af;
    }

    .quantity-input {
      font-size: 14px;
      font-weight: 500;
      color: #374151;
      font-family: sans-serif;
      min-width: 20px;
      text-align: center;
      border: none;
      background: none;
      padding: 0 0.25rem;
    }

    .cart-item-price {
      font-size: 16px;
      font-weight: 600;
      color: #1f2937;
    }

    .cart-item-remove {
      position: absolute;
      top: 15px;
      right: 15px;
      background: none;
      border: none;
      cursor: pointer;
      color: #6b7280;
      padding: 8px;
      border-radius: 4px;
      transition: all 0.2s ease;
    }

    .cart-item-remove:hover {
      background: #f3f4f6;
      color: #374151;
    }

    .cart-summary-section {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 20px;
      position: sticky;
      top: 20px;
    }

    .promo-code-section {
      margin-bottom: 20px;
    }

    .promo-code-toggle {
      display: flex;
      align-items: center;
      gap: 8px;
      background: none;
      border: none;
      cursor: pointer;
      color: #6b7280;
      font-size: 14px;
      padding: 0;
    }

    .promo-code-toggle:hover {
      color: #374151;
    }

    .promo-code-icon {
      font-size: 16px;
      font-weight: 600;
    }

    .promo-code-form {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }

    .promo-code-input {
      flex: 1;
      padding: 8px 12px;
      border: 1px solid #d1d5db;
      border-radius: 4px;
      font-size: 14px;
    }

    .promo-code-apply {
      padding: 8px 16px;
      background: #f3f4f6;
      border: 1px solid #d1d5db;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      color: #374151;
    }

    .order-totals {
      margin-bottom: 20px;
    }

    .total-line {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .total-label {
      font-size: 14px;
      color: #6b7280;
    }

    .total-amount {
      font-size: 14px;
      font-weight: 600;
      color: #1f2937;
    }

    .estimated-total {
      border-top: 1px solid #e5e7eb;
      padding-top: 10px;
      margin-top: 10px;
    }

    .estimated-total .total-label,
    .estimated-total .total-amount {
      font-size: 16px;
      font-weight: 600;
    }

    .checkout-section {
      text-align: center;
    }

    .btn-checkout {
      display: block;
      width: 100%;
      background: #dc2626;
      color: #fff;
      text-decoration: none;
      padding: 16px 24px;
      border-radius: 4px;
      font-size: 16px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: background 0.2s ease;
      margin-bottom: 15px;
    }

    .btn-checkout:hover {
      background: #b91c1c;
      color: #fff;
    }

    .checkout-disclaimer {
      font-size: 12px;
      color: #6b7280;
      line-height: 1.4;
      margin: 0;
    }

    .btn {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.2s ease;
    }

    .btn-primary {
      background: #dc2626;
      color: #fff;
    }

    .btn-primary:hover {
      background: #b91c1c;
      color: #fff;
    }

    @media (max-width: 768px) {
      .cart-main-layout {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .cart-summary-section {
        position: static;
      }
      
      .cart-item-heygirlsbkk {
        flex-direction: column;
        gap: 15px;
      }
      
      .cart-item-image {
        width: 100%;
        height: 300px;
      }
    }
  </style>

  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Quantity controls - unified function for both + and - buttons
      function updateQuantity(cartItemKey, newQuantity) {
        const quantityInput = document.querySelector(`input[data-cart-item-key="${cartItemKey}"]`);
        if (quantityInput) {
          quantityInput.value = newQuantity;
          
          // Create a hidden form to submit the update
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '<?php echo e(wc_get_cart_url()); ?>';
          
          // Add nonce
          const nonceInput = document.createElement('input');
          nonceInput.type = 'hidden';
          nonceInput.name = 'woocommerce-cart-nonce';
          nonceInput.value = '<?php echo e(wp_create_nonce("woocommerce-cart")); ?>';
          form.appendChild(nonceInput);
          
          // Add cart item key and quantity
          const cartItemInput = document.createElement('input');
          cartItemInput.type = 'hidden';
          cartItemInput.name = `cart[${cartItemKey}][qty]`;
          cartItemInput.value = newQuantity;
          form.appendChild(cartItemInput);
          
          // Add update cart action
          const updateInput = document.createElement('input');
          updateInput.type = 'hidden';
          updateInput.name = 'update_cart';
          updateInput.value = 'Update Cart';
          form.appendChild(updateInput);
          
          // Submit the form
          document.body.appendChild(form);
          form.submit();
        }
      }

      // Plus button
      document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const cartItemKey = this.dataset.cartItemKey;
          const quantityInput = document.querySelector(`input[data-cart-item-key="${cartItemKey}"]`);
          let currentQuantity = parseInt(quantityInput.value) || 1;
          let newQuantity = currentQuantity + 1;
          updateQuantity(cartItemKey, newQuantity);
        });
      });

      // Minus button
      document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const cartItemKey = this.dataset.cartItemKey;
          const quantityInput = document.querySelector(`input[data-cart-item-key="${cartItemKey}"]`);
          let currentQuantity = parseInt(quantityInput.value) || 1;
          let newQuantity = Math.max(1, currentQuantity - 1);
          updateQuantity(cartItemKey, newQuantity);
        });
      });

      // Quantity input change
      document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
          const cartItemKey = this.dataset.cartItemKey;
          let newQuantity = Math.max(1, parseInt(this.value) || 1);
          updateQuantity(cartItemKey, newQuantity);
        });
      });

      // Remove item buttons
      document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const cartItemKey = this.dataset.cartItemKey;
          const removeUrl = '<?php echo e(wc_get_cart_url()); ?>?remove_item=' + cartItemKey + '&_wpnonce=' + '<?php echo e(wp_create_nonce("woocommerce-cart")); ?>';
          window.location.href = removeUrl;
        });
      });

      // Promo code toggle
      const promoCodeToggle = document.querySelector('.promo-code-toggle');
      const promoCodeForm = document.querySelector('.promo-code-form');
      
      if (promoCodeToggle && promoCodeForm) {
        promoCodeToggle.addEventListener('click', function() {
          if (promoCodeForm.style.display === 'none') {
            promoCodeForm.style.display = 'flex';
          } else {
            promoCodeForm.style.display = 'none';
          }
        });
      }
    });
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/woocommerce/cart.blade.php ENDPATH**/ ?>