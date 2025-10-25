document.addEventListener('DOMContentLoaded', function () {
    // Initialize cart drawer
    initCartDrawer();
  
    // Listen for WooCommerce add to cart events
    document.addEventListener('added_to_cart', function (event) {
      console.log('🎉 WooCommerce added_to_cart event triggered');
      const allButtons = document.querySelectorAll('.add-to-cart-btn');
      allButtons.forEach(button => {
        button.disabled = false;
        button.dataset.processing = 'false';
        if (button.innerHTML.includes('Adding...')) button.innerHTML = 'ADD TO CART';
      });
  
      if (typeof updateBagCount === 'function') setTimeout(() => updateBagCount(), 100);
  
      const cartDrawer = document.getElementById('cart-drawer');
      if (cartDrawer && cartDrawer.classList.contains('active')) {
        console.log('🛒 Cart drawer open — refreshing...');
        setTimeout(() => {
          if (typeof loadCartContent === 'function') loadCartContent(true, Date.now());
        }, 200);
      }
    });
  
    jQuery(document.body).on('wc_error', function () {
      const allButtons = document.querySelectorAll('.add-to-cart-btn');
      allButtons.forEach(button => {
        button.disabled = false;
        button.dataset.processing = 'false';
        if (button.innerHTML.includes('Adding...')) button.innerHTML = 'ADD TO CART';
      });
    });
  
    jQuery(document.body).on('updated_wc_div wc_fragment_refresh wc_cart_updated', function () {
      const cartDrawer = document.getElementById('cart-drawer');
      if (cartDrawer && cartDrawer.classList.contains('active')) {
        setTimeout(() => {
          if (typeof loadCartContent === 'function') loadCartContent(true, Date.now());
        }, 100);
      }
    });
  
    document.addEventListener('cartUpdated', function (event) {
      const cartDrawer = document.getElementById('cart-drawer');
      if (cartDrawer && cartDrawer.classList.contains('active')) {
        setTimeout(() => {
          if (typeof loadCartContent === 'function') loadCartContent(true, Date.now());
        }, 100);
      }
    });
  });
  
  /**
   * Inline CSS Styles
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
  
  // ======================================================
  // 🛒 CART DRAWER LOGIC
  // ======================================================
  function initCartDrawer() {
    const cartTrigger = document.querySelector('.cart-trigger');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartClose = document.querySelector('.cart-drawer-close');
    const cartOverlay = document.querySelector('.cart-drawer-overlay');
  
    if (!cartTrigger || !cartDrawer) return;
  
    function isMobile() {
      return window.innerWidth <= 768;
    }
  
    cartTrigger.addEventListener('click', function (e) {
      e.preventDefault();
      if (isMobile()) {
        const cartUrl = this.getAttribute('data-cart-url');
        if (cartUrl) window.location.href = cartUrl;
        return;
      }
      openCartDrawer();
    });
  
    cartClose?.addEventListener('click', closeCartDrawer);
    cartOverlay?.addEventListener('click', closeCartDrawer);
  
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && cartDrawer.classList.contains('active')) closeCartDrawer();
    });
  
    function openCartDrawer(forceRefresh = false) {
      console.log('🛒 Opening cart drawer...', forceRefresh ? '(force refresh)' : '');
      cartDrawer.classList.add('active');
      document.body.style.overflow = 'hidden';
      loadCartContent(forceRefresh);
    }
  
    function closeCartDrawer() {
      cartDrawer.classList.remove('active');
      document.body.style.overflow = '';
    }
  
    // Cache
    let cartCache = null;
    let cacheTimestamp = 0;
    const CACHE_DURATION = 5000;
  
    function loadCartContent(forceRefresh = false, timestamp = null) {
      const cartLoading = document.querySelector('.cart-loading');
      const cartContent = document.querySelector('.cart-content');
      const cartFooter = document.querySelector('.cart-drawer-footer');
  
      const now = Date.now();
      if (!forceRefresh && cartCache && (now - cacheTimestamp) < CACHE_DURATION) {
        console.log('Using cached cart data');
        renderCartContent(cartCache);
        return;
      }
  
      if (forceRefresh) {
        cartCache = null;
        cacheTimestamp = 0;
      }
  
      cartLoading.style.display = 'flex';
      cartContent.style.display = 'none';
      cartFooter.style.display = 'none';
  
      const ajaxUrl = window.wc_cart_params?.ajax_url || '/wp-admin/admin-ajax.php';
      const fetchUrl = timestamp ? `${ajaxUrl}?t=${timestamp}` : ajaxUrl;
  
      fetch(fetchUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=woocommerce_get_cart_contents&nonce=' + (window.wc_cart_params?.nonce || '')
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            cartCache = data.data;
            cacheTimestamp = now;
            renderCartContent(data.data);
          } else {
            showCartError();
          }
        })
        .catch(showCartError);
    }
  
    // ======================================================
    // 🎨 RENDER CART CONTENT + TOTALS FIX
    // ======================================================
    function renderCartContent(cartData) {
      const cartLoading = document.querySelector('.cart-loading');
      const cartContent = document.querySelector('.cart-content');
      const cartFooter = document.querySelector('.cart-drawer-footer');
      const cartItems = document.querySelector('.cart-items');
      const cartEmpty = document.querySelector('.cart-empty');
  
      cartLoading.style.display = 'none';
      cartContent.style.display = 'block';
      cartFooter.style.display = 'none';
  
      if (cartData && cartData.items && cartData.items.length > 0) {
        cartItems.style.display = 'block';
        cartEmpty.style.display = 'none';
        cartFooter.style.display = 'block';
  
        cartItems.innerHTML = cartData.items.map(item => {
          // Parse variation data to extract color and size
          let color = '';
          let size = '';
          
          if (item.variation) {
            // Try to extract color and size from variation string
            const variationMatch = item.variation.match(/Color:?\s*([^,<]+)/i);
            if (variationMatch) color = variationMatch[1].trim();
            
            const sizeMatch = item.variation.match(/Size:?\s*([^,<]+)/i);
            if (sizeMatch) size = sizeMatch[1].trim();
          }
          
          return `
          <div class="cart-item" data-cart-item-key="${item.cart_item_key}">
            <div class="cart-item-image">
              <img src="${item.image}" alt="${item.productName || item.name}" loading="lazy">
            </div>
            <div class="cart-item-details">
              <div class="cart-item-top">
                <div class="cart-item-info">
                  <div class="cart-item-name">${item.productName || item.name}</div>
                  ${color ? `<div class="cart-item-variation">${color}</div>` : ''}
                  ${size ? `<div class="cart-item-variation">${size}</div>` : ''}
                </div>
                <div class="cart-item-price">${item.price}</div>
              </div>
              <div class="cart-item-bottom">
                <div class="cart-item-quantity">
                  <button class="quantity-btn minus" data-cart-item-key="${item.cart_item_key}">-</button>
                  <input type="number" class="quantity-input" value="${item.quantity}" min="1" data-cart-item-key="${item.cart_item_key}">
                  <button class="quantity-btn plus" data-cart-item-key="${item.cart_item_key}">+</button>
                </div>
                <button class="cart-item-remove" data-cart-item-key="${item.cart_item_key}">Remove</button>
              </div>
            </div>
          </div>
        `}).join('');
  
        // ✅ FIXED: Reliable subtotal + total updates
        setTimeout(() => {
          const subtotalElement = document.querySelector('.cart-subtotal-amount');
          const totalElement = document.querySelector('.cart-total-amount');
  
          const subtotalValue = cartData?.subtotal || '';
          const totalValue = cartData?.total || cartData?.subtotal || '';
  
          console.log('💰 Totals Update Triggered');
          console.log('Subtotal Raw:', subtotalValue);
          console.log('Total Raw:', totalValue);
  
          if (subtotalElement) {
            subtotalElement.innerHTML = decodeHTMLEntities(subtotalValue);
            console.log('✅ Subtotal Updated:', subtotalElement.innerHTML);
          }
  
          if (totalElement) {
            totalElement.innerHTML = decodeHTMLEntities(totalValue);
            console.log('✅ Total Updated:', totalElement.innerHTML);
          } else {
            console.warn('⚠️ totalElement not found');
          }
        }, 50);
  
        function decodeHTMLEntities(str) {
          if (!str || typeof str !== 'string') return '';
          const textarea = document.createElement('textarea');
          textarea.innerHTML = str;
          return textarea.value;
        }
  
        setupCartItemListeners();
      } else {
        cartItems.style.display = 'none';
        cartEmpty.style.display = 'block';
        cartFooter.style.display = 'none';
      }
    }
  
    // ======================================================
    // ⚙️ HELPER FUNCTIONS
    // ======================================================
    function setupCartItemListeners() {
      document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          const cartItemKey = this.dataset.cartItemKey;
          const isPlus = this.classList.contains('plus');
          const quantityInput = document.querySelector(`input[data-cart-item-key="${cartItemKey}"]`);
          let newQuantity = parseInt(quantityInput.value);
          newQuantity = isPlus ? newQuantity + 1 : Math.max(1, newQuantity - 1);
          updateCartItemQuantity(cartItemKey, newQuantity);
        });
      });
  
      document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function () {
          const key = this.dataset.cartItemKey;
          const qty = Math.max(1, parseInt(this.value) || 1);
          updateCartItemQuantity(key, qty);
        });
      });
  
      document.querySelectorAll('.cart-item-remove').forEach(btn => {
        btn.addEventListener('click', function () {
          removeCartItem(this.dataset.cartItemKey);
        });
      });
    }
  
    function updateCartItemQuantity(cartItemKey, quantity) {
      const ajaxUrl = window.wc_cart_params?.ajax_url || '/wp-admin/admin-ajax.php';
      fetch(ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=woocommerce_update_cart_item&cart_item_key=${cartItemKey}&quantity=${quantity}&nonce=${window.wc_cart_params?.nonce || ''}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            cartCache = null;
            loadCartContent(true, Date.now());
            updateBagCount();
            jQuery(document.body).trigger('updated_wc_div');
          }
        });
    }
  
    function removeCartItem(cartItemKey) {
      const ajaxUrl = window.wc_cart_params?.ajax_url || '/wp-admin/admin-ajax.php';
      fetch(ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=woocommerce_remove_cart_item&cart_item_key=${cartItemKey}&nonce=${window.wc_cart_params?.nonce || ''}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            cartCache = null;
            loadCartContent(true, Date.now());
            updateBagCount();
            jQuery(document.body).trigger('updated_wc_div');
          }
        });
    }
  
    function updateBagCount() {
      const ajaxUrl = window.wc_cart_params?.ajax_url || '/wp-admin/admin-ajax.php';
      fetch(ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=woocommerce_get_cart_count&nonce=${window.wc_cart_params?.nonce || ''}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const bagCount = document.querySelector('.bag-count');
            if (bagCount) bagCount.textContent = data.data;
          }
        });
    }
  
    function showCartError() {
      const cartContent = document.querySelector('.cart-content');
      cartContent.innerHTML = '<div class="cart-error"><p>Error loading cart. Please try again.</p></div>';
    }
  
    // Set up cart drawer button event listeners
    function setupCartDrawerButtons() {
      // View Cart button
      const viewCartBtn = document.querySelector('.view-cart-btn');
      if (viewCartBtn) {
        viewCartBtn.addEventListener('click', function(e) {
          e.preventDefault();
          // Redirect to WooCommerce cart page
          window.location.href = '/cart/';
        });
      }
      
      // Checkout button
      const checkoutBtn = document.querySelector('.checkout-btn');
      if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
          e.preventDefault();
          // Redirect to WooCommerce checkout page
          window.location.href = '/checkout/';
        });
      }
    }
  
    // Initialize cart drawer buttons when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
      setupCartDrawerButtons();
    });
  
    // Expose
    window.openCartDrawer = openCartDrawer;
    window.closeCartDrawer = closeCartDrawer;
    window.updateBagCount = updateBagCount;
    window.loadCartContent = loadCartContent;
  }
  