document.addEventListener('DOMContentLoaded', function() {
  // Prevent form submission to avoid page reload and resubmission issues
  const customCartForm = document.querySelector('.custom-add-cart-form');
  
  if (customCartForm) {
    customCartForm.addEventListener('submit', function(event) {
      event.preventDefault();
      event.stopPropagation();
      console.log('Custom cart form submission prevented, using AJAX instead');
    });
  }
  
  // Debug: Log any other form submissions that might be happening
  document.addEventListener('submit', function(event) {
    if (event.target.classList.contains('cart') || event.target.classList.contains('variations_form') || event.target.classList.contains('custom-add-cart-form')) {
      console.log('=== FORM SUBMISSION DETECTED ===');
      console.log('Form class:', event.target.className);
      console.log('Form action:', event.target.action);
      console.log('Event target:', event.target);
    }
  });
  
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

  
  // Event delegation for all variation types
  document.addEventListener('click', function(e) {
  // Color selection
    if (e.target.classList.contains('color-dot')) {
      e.preventDefault(); // Prevent any inline onclick from running
      e.stopPropagation();
      
      const dot = e.target;
      if (dot.classList.contains('out-of-stock')) return;
      
      // Remove selected class from all color dots
      document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
      
      // Add selected class to clicked dot
      dot.classList.add('selected');
      selectedAttributes.color = dot.getAttribute('data-color');
      
      // ✅ Reset quantity to 1 when color changes
      updateQuantityDisplay(1);
      
      // Update all variation availability
      updateAllVariationAvailability();
      
      // Update display and button
      updateProductDisplay();
    }
  
  // Size selection
    if (e.target.classList.contains('size-button')) {
      const button = e.target;
      if (button.classList.contains('out-of-stock')) return;
      
      // Remove selected class from all size buttons
      document.querySelectorAll('.size-button').forEach(b => b.classList.remove('selected'));
      
      // Add selected class to clicked button
      button.classList.add('selected');
      selectedAttributes.sizes = button.getAttribute('data-size');
      
      // ✅ Reset quantity to 1 when size changes
      updateQuantityDisplay(1);
      
      // Update all variation availability
      updateAllVariationAvailability();
      
      // Update display and button
      updateProductDisplay();
    }
  });
  
  
  // Quantity controls
  const quantityValue = document.querySelector('.quantity-value');
  const spinnerUp = document.querySelector('.spinner-btn.up');
  const spinnerDown = document.querySelector('.spinner-btn.down');
  
  // Debug: Check if quantity elements are found
  console.log('🔍 Quantity elements found:');
  console.log('  quantityValue:', quantityValue);
  console.log('  minusBtn:', minusBtn);
  console.log('  plusBtn:', plusBtn);
  console.log('  spinnerUp:', spinnerUp);
  console.log('  spinnerDown:', spinnerDown);
  
  function updateQuantityDisplay(value) {
    console.log('🔄 updateQuantityDisplay called with:', value);
    console.log('🔄 quantityValue element:', quantityValue);
    if (quantityValue) {
      quantityValue.textContent = value;
      console.log('✅ Updated quantity display to:', value);
    } else {
      console.error('❌ quantityValue element not found!');
    }
  }
  
  function getCurrentQuantity() {
    const current = quantityValue ? parseInt(quantityValue.textContent) || 1 : 1;
    console.log('📊 getCurrentQuantity:', current, 'element:', quantityValue, 'textContent:', quantityValue?.textContent);
    return current;
  }
  
  if (minusBtn) {
    minusBtn.addEventListener('click', function() {
      const currentValue = getCurrentQuantity();
      if (currentValue > 1) {
        updateQuantityDisplay(currentValue - 1);
      }
    });
  }
    
  if (plusBtn) {
    plusBtn.addEventListener('click', function() {
      const currentValue = getCurrentQuantity();
      // ✅ Get current selectedVariation dynamically (not from closure)
      const maxValue = window.selectedVariation ? window.selectedVariation.stock_quantity : 999;
      console.log('➕ Plus clicked - current:', currentValue, 'max:', maxValue, 'selectedVariation:', window.selectedVariation);
      if (currentValue < maxValue) {
        updateQuantityDisplay(currentValue + 1);
      }
    });
  }
  
  if (spinnerUp) {
    spinnerUp.addEventListener('click', function() {
      const currentValue = getCurrentQuantity();
      // ✅ Get current selectedVariation dynamically (not from closure)
      const maxValue = window.selectedVariation ? window.selectedVariation.stock_quantity : 999;
      console.log('⬆️ SpinnerUp clicked - current:', currentValue, 'max:', maxValue, 'selectedVariation:', window.selectedVariation);
      if (currentValue < maxValue) {
        updateQuantityDisplay(currentValue + 1);
      }
    });
  }
  
  if (spinnerDown) {
    spinnerDown.addEventListener('click', function() {
      const currentValue = getCurrentQuantity();
      if (currentValue > 1) {
        updateQuantityDisplay(currentValue - 1);
      }
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
      
      console.log(matchingVariation)
      if (matchingVariation && matchingVariation.in_stock) {
        element.classList.remove('out-of-stock');
        element.disabled = false;
        if (element.style) {
          element.style.opacity = '1';
          element.style.cursor = 'pointer';
        }
      } else {
        element.classList.add('out-of-stock');
        element.disabled = true;
        if (element.style) {
          element.style.opacity = '0.5';
          element.style.cursor = 'not-allowed';
        }
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
        if (element.style) {
          element.style.opacity = '1';
          element.style.cursor = 'pointer';
        }
      });
    });
  }
  
  function getSelectableAttributes() {
    if (variations.length === 0) return [];
    
    // ✅ Use dynamic attributes from window.productAttributes
    const requiredAttributes = window.productAttributes || [];
    console.log('🎯 Dynamic attributes from product:', window.productAttributes);
    console.log('🎯 Using required attributes:', requiredAttributes);
    
    // If no dynamic attributes found, return empty array
    if (!requiredAttributes || requiredAttributes.length === 0) {
      console.log('⚠️ No dynamic attributes found, returning empty array');
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
    console.log('🎯 Final selectable attributes:', finalAttributes);
    
    return finalAttributes;
  }

  function checkRequiredAttributes() {
    if (variations.length === 0) {
      console.log('No variations found');
      return false;
    }
    
    const requiredAttributes = getSelectableAttributes();
    console.log('Required attributes:', requiredAttributes);
    console.log('Selected attributes:', selectedAttributes);
    
    // Check if we have all required attributes selected
    const hasAll = requiredAttributes.every(attr => selectedAttributes[attr]);
    console.log('Has all required attributes:', hasAll);
    return hasAll;
  }
  
  function updateProductDisplay() {
    console.log('updateProductDisplay called');
  
    // Check if we have all required attributes selected
    const hasRequiredAttributes = checkRequiredAttributes();
    console.log('Has required attributes:', hasRequiredAttributes);
  
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
  
      console.log('Selected variation:', selectedVariation);
      console.log('Selected attributes:', selectedAttributes);
      
      // ✅ Update hidden variation_id input field in the form
      const variationInput = document.getElementById('variation_id');
      if (variationInput && selectedVariation?.id) {
        variationInput.value = selectedVariation.id;
        console.log('🔧 Updated variation_id field to:', selectedVariation.id);
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
            console.log('🧩 Add to Cart bound once.');
          } else {
            console.log('⚠️ Add to Cart already bound, skipping rebind.');
          }
        }
  
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

  console.log('=== ADD TO CART CALLED ===');
  console.log('selectedVariation:', selectedVariation);
  console.log('selectedAttributes:', selectedAttributes);

  if (!selectedVariation) {
    showCartMessage('Please select all required options', 'error');
    return;
  }

  // Find the native WooCommerce form
  const nativeForm = document.querySelector('.woocommerce-native-form form');
  if (!nativeForm) {
    console.error('❌ Native WooCommerce form not found');
    showCartMessage('Form not available', 'error');
    return;
  }

  // Update the native form with our selected values
  updateNativeForm(nativeForm);
  
  // Submit the form via AJAX to avoid page reload
  console.log('✅ Submitting native WooCommerce form via AJAX');
  submitFormViaAjax(nativeForm);
}

// Update the native WooCommerce form with our selected values
function updateNativeForm(form) {
  // Update variation_id
  const variationIdInput = form.querySelector('input[name="variation_id"]');
  if (variationIdInput && selectedVariation) {
    variationIdInput.value = selectedVariation.id;
    console.log('🔧 Updated variation_id to:', selectedVariation.id);
  }

  // Update quantity
  const quantityInput = form.querySelector('input[name="quantity"]');
  if (quantityInput) {
    const quantity = getCurrentQuantity();
    quantityInput.value = quantity;
    console.log('🔧 Updated quantity to:', quantity);
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
        // Use the key as-is for the attribute key
        attrKey = `attribute_pa_${key}`;
      }

      // Find and update the attribute input
      const attrInput = form.querySelector(`input[name="${attrKey}"]`);
      if (attrInput) {
        attrInput.value = value;
        console.log(`🔧 Updated ${attrKey} to:`, value);
      } else {
        console.warn(`⚠️ Attribute input not found: ${attrKey}`);
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
  
  console.log('🚀 Submitting AJAX request to:', formAction);
  console.log('📦 Form Data:', Object.fromEntries([...formData.entries()]));
  
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
      console.warn('Non-JSON response, assuming success');
      return { success: true };
    }
  })
  .then(data => {
    console.log('📦 WooCommerce AJAX Response:', data);
    
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
    
    // Open cart drawer or redirect
    setTimeout(() => {
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
    console.error('❌ AJAX Add to cart error:', err);
    showCartMessage('❌ Error adding product to cart', 'error');
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
    console.log('✅ Add to cart button re-enabled (no rebind)');
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
      console.log('🎨 Auto-selecting single color:', singleColor);
      
      // Add selected class to the single color dot
      colorDots[0].classList.add('selected');
      
      // Set in selectedAttributes
      selectedAttributes.color = singleColor;
      
      console.log('✅ Single color auto-selected:', selectedAttributes);
    }
  }
  
  // ✅ Auto-select single color products
  initializeSingleColorProducts();
  
  // Initialize display
  updateProductDisplay();
  
  // Check initial stock availability for single color products
  checkInitialStockAvailability();
});

// Check initial stock availability for single color products
function checkInitialStockAvailability() {
  if (variations.length === 0) return;
  
  // Check if this is a single color product
  const colorDots = document.querySelectorAll('.color-dot');
  const sizeButtons = document.querySelectorAll('.size-button');
  
  // Only proceed if we have exactly one color and multiple sizes
  if (colorDots.length === 1 && sizeButtons.length > 1) {
    console.log('Single color product detected, checking initial stock availability');
    
    // Get the single color
    const singleColor = colorDots[0].getAttribute('data-color');
    console.log('Single color:', singleColor);
    
    // Check stock for each size with this color
    sizeButtons.forEach(sizeButton => {
      const size = sizeButton.getAttribute('data-size');
      console.log('Checking stock for size:', size);
      
      // Find variation that matches this color and size
      const matchingVariation = variations.find(v => {
        return v.color === singleColor && v.sizes === size;
      });
      
      console.log('Matching variation for', size + ':', matchingVariation);
      
      if (matchingVariation && !matchingVariation.in_stock) {
        // Mark this size as out of stock
        sizeButton.classList.add('out-of-stock');
        sizeButton.disabled = true;
        sizeButton.style.opacity = '0.3';
        sizeButton.style.cursor = 'not-allowed';
        console.log('Size', size, 'marked as out of stock');
      } else if (matchingVariation && matchingVariation.in_stock) {
        // Ensure size is available
        sizeButton.classList.remove('out-of-stock');
        sizeButton.disabled = false;
        sizeButton.style.opacity = '1';
        sizeButton.style.cursor = 'pointer';
        console.log('Size', size, 'confirmed as in stock');
      }
    });
  }
}

// TI Wishlist integration has been moved to wishlist-integration.js