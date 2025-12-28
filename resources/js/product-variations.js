document.addEventListener('DOMContentLoaded', function() {
  // Prevent form submission to avoid page reload and resubmission issues
  const customCartForm = document.querySelector('.custom-add-cart-form');
  
  if (customCartForm) {
    customCartForm.addEventListener('submit', function(event) {
      event.preventDefault();
      event.stopPropagation();
    });
  }
  
  // Product variation selection functionality
  const addToCartBtn = document.querySelector('.add-to-cart-btn');
  const quantityInput = document.querySelector('.quantity-input');
  const minusBtn = document.querySelector('.quantity-btn.minus');
  const plusBtn = document.querySelector('.quantity-btn.plus');
  const priceDisplay = document.getElementById('product-price-display');
  const stockDisplay = document.getElementById('stock-status-display');
  
  
  let selectedAttributes = {};
  let selectedVariation = null;
  
  // Variations data from PHP
  const variations = window.productVariations || [];

  
  // Cache DOM elements for better performance
  let colorDotsCache = null;
  let sizeButtonsCache = null;
  
  function getColorDots() {
    if (!colorDotsCache) {
      colorDotsCache = Array.from(document.querySelectorAll('.color-dot'));
    }
    return colorDotsCache;
  }
  
  function getSizeButtons() {
    if (!sizeButtonsCache) {
      sizeButtonsCache = Array.from(document.querySelectorAll('.size-button'));
    }
    return sizeButtonsCache;
  }
  
  // Event delegation for all variation types
  document.addEventListener('click', function(e) {
    // Color selection
    if (e.target.classList.contains('color-dot')) {
      e.preventDefault();
      e.stopPropagation();
      
      const dot = e.target;
      if (dot.classList.contains('out-of-stock')) return;
      
      // Remove selected class from all color dots (using cache)
      getColorDots().forEach(d => d.classList.remove('selected'));
      
      // Add selected class to clicked dot
      dot.classList.add('selected');
      selectedAttributes.color = dot.getAttribute('data-color');
      
      updateQuantityDisplay(1);
      updateAllVariationAvailability();
      updateProductDisplay();
      enableQuantityButtons(); // Ensure buttons are enabled after variation selection
    }
  
    // Size selection
    if (e.target.classList.contains('size-button')) {
      const button = e.target;
      if (button.classList.contains('out-of-stock')) return;
      
      // Remove selected class from all size buttons (using cache)
      getSizeButtons().forEach(b => b.classList.remove('selected'));
      
      // Add selected class to clicked button
      button.classList.add('selected');
      selectedAttributes.sizes = button.getAttribute('data-size');
      
      updateQuantityDisplay(1);
      updateAllVariationAvailability();
      updateProductDisplay();
      enableQuantityButtons(); // Ensure buttons are enabled after variation selection
    }
  });
  
  
  // Quantity controls
  const quantityValue = document.querySelector('.quantity-value');
  const spinnerUp = document.querySelector('.spinner-btn.up');
  const spinnerDown = document.querySelector('.spinner-btn.down');
  
  function updateQuantityDisplay(value) {
    if (quantityValue) {
      quantityValue.textContent = value;
    }
    
    // Update hidden input fields (for both variable and simple products)
    const hiddenQuantityById = document.getElementById('quantity');
    const hiddenQuantityByClass = document.querySelector('.quantity-input');
    
    if (hiddenQuantityById) {
      hiddenQuantityById.value = value;
    }
    if (hiddenQuantityByClass) {
      hiddenQuantityByClass.value = value;
    }
  }
  
  function getCurrentQuantity() {
    return quantityValue ? parseInt(quantityValue.textContent) || 1 : 1;
  }
  
  function getMaxQuantity() {
    // Get max quantity based on selected variation stock
    if (window.selectedVariation) {
      // Check multiple possible properties for stock quantity
      const stockQty = window.selectedVariation.stock_quantity || 
                      window.selectedVariation.stockQuantity || 
                      window.selectedVariation.max_qty ||
                      window.selectedVariation.maxQty;
      
      // If stock quantity exists and is a valid number, use it
      if (stockQty !== null && stockQty !== undefined && stockQty !== '' && !isNaN(stockQty)) {
        return parseInt(stockQty);
      }
      
      // If in_stock is true but no stock quantity, assume unlimited
      if (window.selectedVariation.in_stock) {
        return 999;
      }
      
      // If out of stock, max is 0
      return 0;
    }
    
    // No variation selected, allow up to 999
    return 999;
  }
  
  function handleQuantityChange(delta) {
    const currentValue = getCurrentQuantity();
    const newValue = currentValue + delta;
    const maxValue = getMaxQuantity();
    
    // Validate the new value
    if (newValue < 1) {
      updateQuantityDisplay(1);
    } else if (maxValue > 0 && newValue > maxValue) {
      updateQuantityDisplay(maxValue);
    } else {
      updateQuantityDisplay(newValue);
    }
  }
  
  // Enable quantity buttons and ensure they work
  function enableQuantityButtons() {
    const currentValue = getCurrentQuantity();
    const maxValue = getMaxQuantity();
    
    if (minusBtn) {
      // Disable minus button only if quantity is already at minimum (1)
      const isAtMin = currentValue <= 1;
      minusBtn.disabled = isAtMin;
      minusBtn.style.pointerEvents = isAtMin ? 'none' : 'auto';
      minusBtn.style.opacity = isAtMin ? '0.5' : '1';
    }
    
    if (plusBtn) {
      // Disable plus button if max is 0 (out of stock) or if at max quantity
      const isAtMax = maxValue > 0 && currentValue >= maxValue;
      const isOutOfStock = maxValue <= 0;
      plusBtn.disabled = isAtMax || isOutOfStock;
      plusBtn.style.pointerEvents = (isAtMax || isOutOfStock) ? 'none' : 'auto';
      plusBtn.style.opacity = (isAtMax || isOutOfStock) ? '0.5' : '1';
    }
  }
  
  if (minusBtn) {
    minusBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      handleQuantityChange(-1);
      enableQuantityButtons(); // Update button states after change
    });
  }
    
  if (plusBtn) {
    plusBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      handleQuantityChange(1);
      enableQuantityButtons(); // Update button states after change
    });
  }
  
  if (spinnerUp) {
    spinnerUp.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      handleQuantityChange(1);
      enableQuantityButtons();
    });
  }
  
  if (spinnerDown) {
    spinnerDown.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      handleQuantityChange(-1);
      enableQuantityButtons();
    });
  }
  
  // Generic function to update availability for any variation type
  function updateVariationAvailability(attributeType) {
    // Get all buttons/dots for this attribute type
    const selector = `.${attributeType}-button, .${attributeType}-dot`;
    const elements = document.querySelectorAll(selector);
    
    if (!elements.length) return;
    
    // Check each element for availability
    
    elements.forEach(element => {
      const elementValue = element.getAttribute(`data-${attributeType}`);
      
      const matchingVariation = variations.find(v => {
        // Check if this variation matches all currently selected attributes
        // plus the element we're checking
        const testAttributes = { ...selectedAttributes, [attributeType]: elementValue };
        
        return Object.entries(testAttributes).every(([key, value]) => v[key] === value);
      });
      
      if (matchingVariation && matchingVariation.in_stock) {
        element.classList.remove('out-of-stock');
        element.disabled = false;
      } else {
        element.classList.add('out-of-stock');
        element.disabled = true;
      }
    });
  }
  
  // Update all variation availability when any attribute changes
  function updateAllVariationAvailability() {
    if (variations.length === 0) return;
    
    // Get dynamically detected selectable attributes
    const attributeTypes = getSelectableAttributes();
    
    // Update availability for each attribute type
    attributeTypes.forEach(attributeType => {
      updateVariationAvailability(attributeType);
    });
  }
  
  // Reset all availability states
  function resetAvailability() {
    if (variations.length === 0) return;
    
    // Get dynamically detected selectable attributes
    const attributeTypes = getSelectableAttributes();
    
    // Reset all variation types
    attributeTypes.forEach(attributeType => {
      const selector = `.${attributeType}-button, .${attributeType}-dot`;
      document.querySelectorAll(selector).forEach(element => {
        element.classList.remove('out-of-stock');
        element.disabled = false;
      });
    });
  }
  
  function getSelectableAttributes() {
    if (variations.length === 0) return [];
    
    // ✅ Use dynamic attributes from window.productAttributes
    const requiredAttributes = window.productAttributes || [];
    
    // If no dynamic attributes found, return empty array
    if (!requiredAttributes || requiredAttributes.length === 0) {
      return [];
    }
    
    // Get all possible attribute keys from all variations
    const allKeys = new Set();
    variations.forEach(variation => {
      Object.keys(variation).forEach(key => allKeys.add(key));
    });
    
    // Filter out metadata fields that are never user-selectable
    const metadataFields = [
      'id', 'price', 'regular_price', 'sale_price', 'image', 'price_html', 
      'in_stock', 'stock_quantity', 'variation_id', 'price_html'
    ];
    
    // Start with required attributes that exist in the variations
    const selectableAttributes = requiredAttributes.filter(attr => allKeys.has(attr));
    
    // Add any other attributes that have different values across variations
    const additionalAttributes = Array.from(allKeys).filter(key => {
      if (metadataFields.includes(key) || requiredAttributes.includes(key)) return false;
      
      // Check if this attribute has different values across variations
      const values = variations.map(v => v[key]).filter(v => v !== null && v !== undefined && v !== '');
      const uniqueValues = [...new Set(values)];
      
      // If there are multiple unique values, it's a selectable attribute
      return uniqueValues.length > 1;
    });
    
    const finalAttributes = [...selectableAttributes, ...additionalAttributes];
    return finalAttributes;
  }

  function checkRequiredAttributes() {
    if (variations.length === 0) {
      return false;
    }
    
    const requiredAttributes = getSelectableAttributes();
    
    // Check if we have all required attributes selected
    return requiredAttributes.every(attr => selectedAttributes[attr]);
  }
  
  function updateProductDisplay() {
    // Check if we have all required attributes selected
    const hasRequiredAttributes = checkRequiredAttributes();
  
    if (hasRequiredAttributes) {
      selectedVariation = variations.find(v => {
        return Object.entries(selectedAttributes).every(([key, value]) => {
          // Map selectedAttributes keys to variation data keys
          const variationKey = key === 'sizes' ? 'sizes' : key;
          return v[variationKey] === value;
        });
      });
      
      // ✅ Make selectedVariation available globally for quantity buttons
      window.selectedVariation = selectedVariation;
      
      // ✅ Update hidden variation_id input field in the form
      const variationInput = document.getElementById('variation_id');
      if (variationInput && selectedVariation?.id) {
        variationInput.value = selectedVariation.id;
      }
  
      if (selectedVariation) {
        // ✅ Display correct price and stock
        const priceHtml = selectedVariation.price_html || selectedVariation.price || 'Price not available';
        priceDisplay.innerHTML = priceHtml;
  
        stockDisplay.innerHTML = selectedVariation.in_stock
          ? '<span class="stock-status in-stock">IN STOCK</span>'
          : '<span class="stock-status out-of-stock">OUT OF STOCK</span>';
  
        // ✅ Handle Add to Cart button logic safely
        if (addToCartBtn) {
          addToCartBtn.disabled = !selectedVariation.in_stock;
          addToCartBtn.textContent = selectedVariation.in_stock ? 'ADD TO CART' : 'OUT OF STOCK';
          addToCartBtn.style.pointerEvents = selectedVariation.in_stock ? 'auto' : 'none';
  
          // ✅ Bind only once — prevent duplicate click handlers
          if (!addToCartBtn.dataset.bound) {
            addToCartBtn.dataset.bound = 'true';
            addToCartBtn.addEventListener('click', (event) => {
              event.preventDefault();
              event.stopPropagation();
              addToCart(event);
            });
          }
        }
        
        // ✅ Enable quantity buttons when variation is selected
        enableQuantityButtons();
  
        // ✅ Update wishlist button with current variation using the wishlist integration module
        const variationId = selectedVariation.id || selectedVariation.variation_id || selectedVariation.variationId;
        if (window.wishlistIntegration && window.wishlistIntegration.initializeWishlistForVariation) {
          window.wishlistIntegration.initializeWishlistForVariation(variationId);
        }
  
      } else {
        // ❌ Invalid combination
        priceDisplay.innerHTML = '<div class="price-current">Combination not available</div>';
        stockDisplay.innerHTML = '<span class="stock-status out-of-stock">NOT AVAILABLE</span>';
        if (addToCartBtn) {
          addToCartBtn.disabled = true;
          addToCartBtn.textContent = 'Combination Not Available';
          addToCartBtn.style.pointerEvents = 'none';
        }
      }
  
    } else {
      // ⏳ No attributes selected yet
      const defaultPrice = variations.length > 0 ? variations[0].price : 'Price not available';
      priceDisplay.innerHTML = defaultPrice;
      stockDisplay.innerHTML = '<span class="stock-status">Select options to see availability</span>';
      if (addToCartBtn) {
        addToCartBtn.disabled = true;
        addToCartBtn.textContent = 'Select Options';
        addToCartBtn.style.pointerEvents = 'none';
      }
    }
  }
  
  
// Add to cart function
// Add to cart function - use WooCommerce native form
function addToCart(event) {
  if (event) {
    event.preventDefault();
    event.stopImmediatePropagation();
  }

  if (!selectedVariation) {
    showCartMessage('Please select all required options', 'error');
    return;
  }

  // Find the native WooCommerce form
  const nativeForm = document.querySelector('.woocommerce-native-form form');
  if (!nativeForm) {
    showCartMessage('Form not available', 'error');
    return;
  }

  // Update the native form with our selected values
  updateNativeForm(nativeForm);
  
  // Submit the form via AJAX to avoid page reload
  submitFormViaAjax(nativeForm);
}

// Update the native WooCommerce form with our selected values
function updateNativeForm(form) {
  // Update variation_id
  const variationIdInput = form.querySelector('input[name="variation_id"]');
  if (variationIdInput && selectedVariation) {
    variationIdInput.value = selectedVariation.id;
  }

  // Update quantity
  const quantityInput = form.querySelector('input[name="quantity"]');
  if (quantityInput) {
    quantityInput.value = getCurrentQuantity();
  }

  // Update attribute inputs
  Object.entries(selectedAttributes).forEach(([key, value]) => {
    if (value) {
      // Format the attribute key
      let attrKey;
      if (key.startsWith('attribute_')) {
        attrKey = key;
      } else if (key.startsWith('pa_')) {
        attrKey = `attribute_${key}`;
      } else {
        attrKey = `attribute_pa_${key}`;
      }

      // Find and update the attribute input
      const attrInput = form.querySelector(`input[name="${attrKey}"]`);
      if (attrInput) {
        attrInput.value = value;
      }
    }
  });
}

// Submit the WooCommerce form via AJAX to avoid page reload
function submitFormViaAjax(form) {
  const formData = new FormData(form);
  
  // Add AJAX action for WooCommerce
  formData.append('wc-ajax', 'add_to_cart');
  
  // Get the form action URL
  const formAction = form.action || window.location.href;
  
  // Show loading state
  const button = document.querySelector('.add-to-cart-btn');
  const originalText = button ? button.textContent : 'ADD TO CART';
  const originalBackground = button ? button.style.background || '' : '';
  
  if (button) {
    // Store original width to prevent size changes
    if (!button.dataset.originalWidth) {
      button.dataset.originalWidth = button.offsetWidth + 'px';
    }
    button.style.minWidth = button.dataset.originalWidth;
    button.style.width = button.dataset.originalWidth;
    
    button.disabled = true;
    button.textContent = 'Adding...';
    button.style.pointerEvents = 'none';
  }
  
  // Submit via AJAX
  fetch(formAction, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  })
  .then(response => {
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    return response.text();
  })
  .then(text => {
    try {
      return JSON.parse(text);
    } catch (e) {
      return { success: true };
    }
  })
  .then(data => {
    
    if (data.success === false) {
      showCartMessage('Error: ' + (data.data || 'Unknown error'), 'error');
      reEnableAddToCartButton(originalText, originalBackground);
      return;
    }
    
    // Success! Update fragments and show success message
    if (data.fragments) {
      Object.entries(data.fragments).forEach(([selector, html]) => {
        const el = document.querySelector(selector);
        if (el) el.innerHTML = html;
      });
    }
    
    // Track Add to Cart event
    if (typeof window.mixpanel !== 'undefined' && selectedVariation) {
      const productId = selectedVariation.variation_id || selectedVariation.id;
      const productName = document.querySelector('h1')?.textContent?.trim() || 'Unknown Product';
      const category = document.querySelector('.product-category')?.textContent?.trim() || '';
      const quantityInput = form.querySelector('input[name="quantity"]');
      const quantity = quantityInput ? parseInt(quantityInput.value || '1', 10) : 1;
      const price = parseFloat(selectedVariation.price || selectedVariation.regular_price || 0);
      
      window.mixpanel.track('Add to Cart', {
        user_id: window.mixpanelUser?.id || null,
        Cart: [{
          product_id: productId,
          name: productName,
          quantity: quantity,
          price: price,
        }],
        Category: category,
      });
    }
    
    // Trigger WooCommerce events
    if (typeof jQuery !== 'undefined') {
      jQuery(document.body).trigger('added_to_cart', [
        data.fragments,
        data.cart_hash,
        jQuery('.add-to-cart-btn'),
      ]);
    }
    
    // Show success animation
    if (button) {
      button.innerHTML = '✅ Added!';
      button.style.background = '#10b981';
      setTimeout(() => {
        button.textContent = originalText;
        button.style.background = originalBackground;
        button.disabled = false;
        button.style.pointerEvents = 'auto';
        // Keep the fixed width
        if (button.dataset.originalWidth) {
          button.style.minWidth = button.dataset.originalWidth;
          button.style.width = button.dataset.originalWidth;
        }
      }, 2000);
    }
    
    // Open cart drawer or redirect (only on desktop, not mobile)
    setTimeout(() => {
      // Check if mobile - on mobile, don't open cart drawer (it doesn't exist)
      // User will navigate to cart page manually via cart icon
      if (window.innerWidth <= 768) {
        // On mobile, just update the bag count if available
        if (typeof window.updateBagCount === 'function') {
          setTimeout(() => window.updateBagCount(), 200);
        }
        return; // Don't try to open cart drawer on mobile
      }
      
      // Desktop: Open cart drawer
      if (typeof window.openCartDrawer === 'function') {
        window.openCartDrawer(true);
      } else {
        const trigger = document.querySelector('.cart-trigger, .navbar-bag, [data-cart-url]');
        if (trigger) trigger.click();
      }
      
      // Optional refresh hooks
      if (typeof window.loadCartContent === 'function') {
        setTimeout(() => window.loadCartContent(true), 100);
      }
      if (typeof window.updateBagCount === 'function') {
        setTimeout(() => window.updateBagCount(), 200);
      }
    }, 500);
  })
  .catch(err => {
    showCartMessage('Error adding product to cart', 'error');
    reEnableAddToCartButton(originalText, originalBackground);
  });
}


// Function to properly re-enable the add to cart button
function reEnableAddToCartButton(originalText = 'ADD TO CART', originalBackground = '') {
  const button = document.querySelector('.add-to-cart-btn');
  if (!button) return;

  // Restore fixed width if it was set
  if (button.dataset.originalWidth) {
    button.style.minWidth = button.dataset.originalWidth;
    button.style.width = button.dataset.originalWidth;
  }

  if (selectedVariation && selectedVariation.in_stock) {
    button.disabled = false;
    button.style.pointerEvents = 'auto';
    button.textContent = originalText || 'ADD TO CART';
    if (originalBackground) {
      button.style.background = originalBackground;
    } else {
      button.style.background = '';
    }
  } else {
    updateProductDisplay();
  }
}

  
  // Show cart message function
  function showCartMessage(message, type) {
    // Create or update cart message
    let messageEl = document.querySelector('.cart-message');
    if (!messageEl) {
      messageEl = document.createElement('div');
      messageEl.className = 'cart-message';
      document.body.appendChild(messageEl);
    }
    
    messageEl.textContent = message;
    messageEl.className = `cart-message ${type}`;
    messageEl.style.display = 'block';
    
    // Hide after 3 seconds
    setTimeout(() => {
      messageEl.style.display = 'none';
    }, 3000);
  }
  
  // Wishlist functionality has been moved to wishlist-integration.js
  
  // ✅ Initialize single color products by auto-selecting the color
  function initializeSingleColorProducts() {
    if (variations.length === 0) return;
    
    const colorDots = document.querySelectorAll('.color-dot');
    
    // If there's only one color, auto-select it
    if (colorDots.length === 1) {
      const singleColor = colorDots[0].getAttribute('data-color');
      colorDots[0].classList.add('selected');
      selectedAttributes.color = singleColor;
    }
  }
  
  // ✅ Auto-select single color products
  initializeSingleColorProducts();
  
  // Initialize display
  updateProductDisplay();
  
  // Check initial stock availability for single color products
  checkInitialStockAvailability();
  
  // Enable quantity buttons on page load
  enableQuantityButtons();
  
  // Handle simple product add to cart (non-variable products)
  // Only bind if this is a simple product (no variations)
  if (variations.length === 0 && addToCartBtn && !addToCartBtn.dataset.bound) {
    addToCartBtn.addEventListener('click', function(event) {
      event.preventDefault();
      
      // Ensure quantity is updated before form submission
      const currentQty = getCurrentQuantity();
      const qtyInput = document.querySelector('input[name="quantity"]');
      if (qtyInput) {
        qtyInput.value = currentQty;
      }
      
      // Submit the form
      const form = addToCartBtn.closest('form');
      if (form) {
        form.submit();
      }
    });
    addToCartBtn.dataset.bound = 'true';
  }
});

// Check initial stock availability for single color products
function checkInitialStockAvailability() {
  if (variations.length === 0) return;
  
  // Check if this is a single color product
  const colorDots = document.querySelectorAll('.color-dot');
  const sizeButtons = document.querySelectorAll('.size-button');
  
  // Only proceed if we have exactly one color and multiple sizes
  if (colorDots.length === 1 && sizeButtons.length > 1) {
    const singleColor = colorDots[0].getAttribute('data-color');
    
    // Check stock for each size with this color
    sizeButtons.forEach(sizeButton => {
      const size = sizeButton.getAttribute('data-size');
      const matchingVariation = variations.find(v => {
        return v.color === singleColor && v.sizes === size;
      });
      
      if (matchingVariation && !matchingVariation.in_stock) {
        sizeButton.classList.add('out-of-stock');
        sizeButton.disabled = true;
      } else if (matchingVariation && matchingVariation.in_stock) {
        sizeButton.classList.remove('out-of-stock');
        sizeButton.disabled = false;
      }
    });
  }
}

// TI Wishlist integration has been moved to wishlist-integration.js