document.addEventListener('DOMContentLoaded', function () {
    // Initialize cart drawer
    initCartDrawer();
    
    // Initialize quantity controls - DISABLED: Let product-variations.js handle this
    // initQuantityControls();
    
    // Handle add to cart - DISABLED: Let product-variations.js handle this
    // const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    // 
    // addToCartButtons.forEach(button => {
    //   button.addEventListener('click', function (e) {
    //     // Prevent double submissions
    //     if (this.disabled || this.dataset.processing === 'true') {
    //       e.preventDefault();
    //       e.stopPropagation();
    //       return false;
    //     }
    //     
    //     // Update quantity in hidden input before submission
    //     updateQuantityInForm(this);
    //     
    //     // Set processing flag
    //     this.dataset.processing = 'true';
    //     
    //     // Show loading state
    //     const originalText = this.innerHTML;
    //     this.innerHTML = '<div class="spinner"></div> Adding...';
    //     this.disabled = true;
    //     
    //     // Re-enable after 5 seconds as fallback
    //     setTimeout(() => {
    //       this.innerHTML = originalText;
    //       this.disabled = false;
    //       this.dataset.processing = 'false';
    //     }, 5000);
    //   });
    // });
    
    function initQuantityControls() {
      console.log('Initializing quantity controls');
      
      // Handle quantity +/- buttons
      document.addEventListener('click', function(e) {
        if (e.target.classList.contains('quantity-btn')) {
          e.preventDefault();
          console.log('Quantity button clicked:', e.target.classList.contains('plus') ? 'plus' : 'minus');
          
          const isPlus = e.target.classList.contains('plus');
          const quantityControls = e.target.closest('.quantity-controls');
          const quantityValue = quantityControls.querySelector('.quantity-value');
          let currentQuantity = parseInt(quantityValue.textContent) || 1;
          
          if (isPlus) {
            currentQuantity++;
          } else {
            currentQuantity = Math.max(1, currentQuantity - 1);
          }
          
          console.log('New quantity:', currentQuantity);
          quantityValue.textContent = currentQuantity;
          
          // Update ALL hidden quantity inputs in the product form
          const productForm = e.target.closest('.product-form');
          const forms = productForm.querySelectorAll('form');
          
          forms.forEach(form => {
            const quantityInputs = form.querySelectorAll('input[name="quantity"]');
            quantityInputs.forEach(input => {
              input.value = currentQuantity;
              console.log('Updated form input:', input.name, 'to', currentQuantity);
            });
          });
          
          // Also update any input with id="quantity"
          const quantityById = document.getElementById('quantity');
          if (quantityById) {
            quantityById.value = currentQuantity;
            console.log('Updated input by ID to:', currentQuantity);
          }
        }
      });
    }
    
    function updateQuantityInForm(button) {
      const productForm = button.closest('.product-form');
      const quantityControls = productForm.querySelector('.quantity-controls');
      
      if (quantityControls) {
        const quantityValue = quantityControls.querySelector('.quantity-value');
        const currentQuantity = parseInt(quantityValue.textContent) || 1;
        
        console.log('Updating quantity to:', currentQuantity);
        
        // Update ALL forms in the product form
        const forms = productForm.querySelectorAll('form');
        forms.forEach(form => {
          const quantityInputs = form.querySelectorAll('input[name="quantity"]');
          quantityInputs.forEach(input => {
            input.value = currentQuantity;
            console.log('Updated form input:', input.name, 'to', currentQuantity);
          });
        });
        
        // Also update any input with id="quantity"
        const quantityById = document.getElementById('quantity');
        if (quantityById) {
          quantityById.value = currentQuantity;
          console.log('Updated input by ID to:', currentQuantity);
        }
      }
    }
  
    // Listen for WooCommerce add to cart events to update bag count
    document.addEventListener('added_to_cart', function(event) {
      console.log('🎉 WooCommerce added_to_cart event triggered');
      console.log('Event details:', event.detail);
      console.log('Event type:', event.type);
      console.log('Event target:', event.target);
      
      // Reset all add to cart buttons
      const allButtons = document.querySelectorAll('.add-to-cart-btn');
      allButtons.forEach(button => {
        button.disabled = false;
        button.dataset.processing = 'false';
        // Reset to original text if it still shows loading
        if (button.innerHTML.includes('Adding...')) {
          button.innerHTML = 'ADD TO CART';
        }
      });
      
      // Update bag count - try multiple methods to ensure it works
      console.log('updateBagCount function available:', typeof updateBagCount === 'function');
      
      // Method 1: Try fragments first (fastest)
      if (event.detail && event.detail.fragments) {
        console.log('Updating bag count using fragments...');
        const bagCountElement = document.querySelector('.bag-count');
        if (bagCountElement && event.detail.fragments['.bag-count']) {
          // Extract just the number from the fragment
          const fragmentContent = event.detail.fragments['.bag-count'];
          const match = fragmentContent.match(/>(\d+)</);
          if (match) {
            bagCountElement.textContent = match[1];
            console.log('✅ Updated bag count using fragments:', match[1]);
          }
        }
      }
      
      // Method 2: Fallback to updateBagCount function
      if (typeof updateBagCount === 'function') {
        console.log('Also calling updateBagCount as backup...');
        setTimeout(() => updateBagCount(), 100); // Small delay to ensure fragments processed first
      }
    });
    
    // Also listen for WooCommerce errors to reset buttons
    jQuery(document.body).on('wc_error', function(event, error) {
      console.log('WooCommerce error:', error);
      
      // Reset all add to cart buttons on error
      const allButtons = document.querySelectorAll('.add-to-cart-btn');
      allButtons.forEach(button => {
        button.disabled = false;
        button.dataset.processing = 'false';
        if (button.innerHTML.includes('Adding...')) {
          button.innerHTML = 'ADD TO CART';
        }
      });
    });
  });
  
  /**
   * Style (Injected Inline for Simplicity)
   */
  const style = document.createElement('style');
  style.textContent = `
    .cart-message {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 14px 22px;
      border-radius: 8px;
      font-size: 14px;
      color: white;
      font-weight: 600;
      z-index: 9999;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      display: none;
      animation: fadeSlide 0.3s ease-out;
    }
    .cart-message.success { background: linear-gradient(135deg, #10b981, #059669); }
    .cart-message.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
    @keyframes fadeSlide {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .add-to-cart-btn .spinner {
      width: 16px; height: 16px;
      border: 2px solid #fff;
      border-top: 2px solid transparent;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      display: inline-block;
      vertical-align: middle;
      margin-right: 6px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
  `;
  document.head.appendChild(style);

// Cart Drawer Functionality
function initCartDrawer() {
    const cartTrigger = document.querySelector('.cart-trigger');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartClose = document.querySelector('.cart-drawer-close');
    const cartOverlay = document.querySelector('.cart-drawer-overlay');
    
    if (!cartTrigger || !cartDrawer) return;
    
    // Check if mobile - if so, use regular cart page
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // Handle cart trigger click
    cartTrigger.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (isMobile()) {
            // Mobile: redirect to cart page
            const cartUrl = this.getAttribute('data-cart-url');
            if (cartUrl) {
                window.location.href = cartUrl;
            }
            return;
        }
        
        // Desktop: open cart drawer
        openCartDrawer();
    });
    
    // Handle close button
    if (cartClose) {
        cartClose.addEventListener('click', closeCartDrawer);
    }
    
    // Handle overlay click
    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCartDrawer);
    }
    
    // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && cartDrawer.classList.contains('active')) {
            closeCartDrawer();
        }
    });
    
    function openCartDrawer(forceRefresh = false) {
        cartDrawer.classList.add('active');
        document.body.style.overflow = 'hidden';
        loadCartContent(forceRefresh);
    }
    
    function closeCartDrawer() {
        cartDrawer.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Cache for cart data
    let cartCache = null;
    let cacheTimestamp = 0;
    const CACHE_DURATION = 30000; // 30 seconds
    
    function loadCartContent(forceRefresh = false) {
        const cartLoading = document.querySelector('.cart-loading');
        const cartContent = document.querySelector('.cart-content');
        const cartFooter = document.querySelector('.cart-drawer-footer');
        
        // Check cache first
        const now = Date.now();
        if (!forceRefresh && cartCache && (now - cacheTimestamp) < CACHE_DURATION) {
            console.log('Using cached cart data');
            renderCartContent(cartCache);
            return;
        }
        
        // Show loading
        cartLoading.style.display = 'flex';
        cartContent.style.display = 'none';
        cartFooter.style.display = 'none';
        
        // Get AJAX URL from WordPress
        const ajaxUrl = window.wc_cart_params?.ajax_url || '/wp-admin/admin-ajax.php';
        
        // Fetch cart content via AJAX
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=woocommerce_get_cart_contents&nonce=' + (window.wc_cart_params?.nonce || '')
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Cart data received:', data);
            if (data.success) {
                // Cache the data
                cartCache = data.data;
                cacheTimestamp = now;
                renderCartContent(data.data);
            } else {
                console.error('Cart data error:', data);
                showCartError();
            }
        })
        .catch(error => {
            console.error('Error loading cart:', error);
            showCartError();
        });
    }
    
    function renderCartContent(cartData) {
        const cartLoading = document.querySelector('.cart-loading');
        const cartContent = document.querySelector('.cart-content');
        const cartFooter = document.querySelector('.cart-drawer-footer');
        const cartItems = document.querySelector('.cart-items');
        const cartEmpty = document.querySelector('.cart-empty');
        
        cartLoading.style.display = 'none';
        cartContent.style.display = 'block';
        
        // Always hide footer first, then show if needed
        cartFooter.style.display = 'none';
        
        if (cartData && cartData.items && cartData.items.length > 0) {
            // Show cart items
            cartItems.style.display = 'block';
            cartEmpty.style.display = 'none';
            cartFooter.style.display = 'block';
            
            // Render cart items
            cartItems.innerHTML = cartData.items.map(item => `
                <div class="cart-item" data-cart-item-key="${item.cart_item_key}">
                    <div class="cart-item-image">
                        <img src="${item.image}" alt="${item.name}" loading="lazy">
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        ${item.variation ? `<div class="cart-item-variation">${item.variation}</div>` : ''}
                        <div class="cart-item-price">${item.price}</div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn minus" data-cart-item-key="${item.cart_item_key}">-</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" min="1" data-cart-item-key="${item.cart_item_key}">
                            <button class="quantity-btn plus" data-cart-item-key="${item.cart_item_key}">+</button>
                        </div>
                        <button class="cart-item-remove" data-cart-item-key="${item.cart_item_key}">Remove</button>
                    </div>
                </div>
            `).join('');
            
            // Update totals safely
            const subtotalElement = document.querySelector('.cart-subtotal-amount');
            const totalElement = document.querySelector('.cart-total-amount');
            
            if (subtotalElement && cartData.subtotal) {
                subtotalElement.textContent = cartData.subtotal;
            }
            if (totalElement && cartData.total) {
                totalElement.textContent = cartData.total;
            }
            
            // Set up event listeners for cart items
            setupCartItemListeners();
        } else {
            // Show empty cart
            cartItems.style.display = 'none';
            cartEmpty.style.display = 'block';
            cartFooter.style.display = 'none';
        }
    }
    
    function setupCartItemListeners() {
        // Quantity buttons
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const cartItemKey = this.getAttribute('data-cart-item-key');
                const isPlus = this.classList.contains('plus');
                const quantityInput = document.querySelector(`input[data-cart-item-key="${cartItemKey}"]`);
                let newQuantity = parseInt(quantityInput.value);
                
                if (isPlus) {
                    newQuantity++;
                } else {
                    newQuantity = Math.max(1, newQuantity - 1);
                }
                
                updateCartItemQuantity(cartItemKey, newQuantity);
            });
        });
        
        // Quantity input changes
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const cartItemKey = this.getAttribute('data-cart-item-key');
                const newQuantity = Math.max(1, parseInt(this.value) || 1);
                updateCartItemQuantity(cartItemKey, newQuantity);
            });
        });
        
        // Remove buttons
        document.querySelectorAll('.cart-item-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const cartItemKey = this.getAttribute('data-cart-item-key');
                removeCartItem(cartItemKey);
            });
        });
        
        // Continue shopping button
        document.querySelector('.continue-shopping')?.addEventListener('click', function() {
            closeCartDrawer();
        });
        
        // View cart button
        document.querySelector('.view-cart-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            const cartUrl = document.querySelector('.cart-trigger').getAttribute('data-cart-url');
            window.location.href = cartUrl;
        });
        
        // Checkout button
        document.querySelector('.checkout-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/checkout/';
        });
    }
    
    function updateCartItemQuantity(cartItemKey, quantity) {
        const ajaxUrl = window.wc_cart_params?.ajax_url || '/wp-admin/admin-ajax.php';
        
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=woocommerce_update_cart_item&cart_item_key=${cartItemKey}&quantity=${quantity}&nonce=${window.wc_cart_params?.nonce || ''}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear cache and reload
                cartCache = null;
                loadCartContent(true);
                updateBagCount();
            }
        })
        .catch(error => {
            console.error('Error updating cart item:', error);
        });
    }
    
    function removeCartItem(cartItemKey) {
        const ajaxUrl = window.wc_cart_params?.ajax_url || '/wp-admin/admin-ajax.php';
        
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=woocommerce_remove_cart_item&cart_item_key=${cartItemKey}&nonce=${window.wc_cart_params?.nonce || ''}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear cache and reload
                cartCache = null;
                loadCartContent(true);
                updateBagCount();
            }
        })
        .catch(error => {
            console.error('Error removing cart item:', error);
        });
    }
    
    function updateBagCount() {
        console.log('🔄 updateBagCount function called');
        const ajaxUrl = window.wc_cart_params?.ajax_url || '/wp-admin/admin-ajax.php';
        
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=woocommerce_get_cart_count&nonce=${window.wc_cart_params?.nonce || ''}`
        })
        .then(response => response.json())
        .then(data => {
            console.log('📊 Bag count response:', data);
            if (data.success) {
                const bagCount = document.querySelector('.bag-count');
                if (bagCount) {
                    const oldCount = bagCount.textContent;
                    bagCount.textContent = data.data;
                    console.log(`✅ Bag count updated: ${oldCount} → ${data.data}`);
                } else {
                    console.warn('⚠️ .bag-count element not found');
                }
            } else {
                console.error('❌ Bag count update failed:', data);
            }
        })
        .catch(error => {
            console.error('❌ Error updating bag count:', error);
        });
    }
    
    function showCartError() {
        const cartLoading = document.querySelector('.cart-loading');
        const cartContent = document.querySelector('.cart-content');
        const cartFooter = document.querySelector('.cart-drawer-footer');
        const cartItems = document.querySelector('.cart-items');
        const cartEmpty = document.querySelector('.cart-empty');
        
        cartLoading.style.display = 'none';
        cartContent.style.display = 'block';
        cartFooter.style.display = 'none';
        cartItems.style.display = 'none';
        cartEmpty.style.display = 'none';
        
        cartContent.innerHTML = '<div class="cart-error"><p>Error loading cart. Please try again.</p></div>';
    }
    
    // Make functions globally available
    window.openCartDrawer = openCartDrawer;
    window.closeCartDrawer = closeCartDrawer;
    window.updateBagCount = updateBagCount;
    window.loadCartContent = loadCartContent;
}
  