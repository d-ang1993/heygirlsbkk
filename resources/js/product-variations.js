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
  
  // Debug: Check if button is found
  console.log('Add to cart button found:', addToCartBtn);
  
  let selectedAttributes = {};
  let selectedVariation = null;
  
  // Variations data from PHP
  const variations = window.productVariations || [];
  console.log('Available variations:', variations);
  if (variations.length > 0) {
    console.log('First variation structure:', variations[0]);
    console.log('First variation keys:', Object.keys(variations[0]));
  }
  
  // Event delegation for all variation types
  document.addEventListener('click', function(e) {
  // Color selection
    if (e.target.classList.contains('color-dot')) {
      console.log('Color dot clicked:', e.target);
      const dot = e.target;
      if (dot.classList.contains('out-of-stock')) return;
      
      // Remove selected class from all color dots
      document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('selected'));
      
      // Add selected class to clicked dot
      dot.classList.add('selected');
      selectedAttributes.color = dot.getAttribute('data-color');
      
      console.log('Color selected:', selectedAttributes.color);
      
      // Update all variation availability
      updateAllVariationAvailability();
      
      // Update display and button
      updateProductDisplay();
    }
  
  // Size selection
    if (e.target.classList.contains('size-button')) {
      console.log('Size button clicked:', e.target);
      const button = e.target;
      if (button.classList.contains('out-of-stock')) return;
      
      // Remove selected class from all size buttons
      document.querySelectorAll('.size-button').forEach(b => b.classList.remove('selected'));
      
      // Add selected class to clicked button
      button.classList.add('selected');
      selectedAttributes.size = button.getAttribute('data-size');
      
      console.log('Size selected:', selectedAttributes.size);
      
      // Update all variation availability
      updateAllVariationAvailability();
      
      // Update display and button
      updateProductDisplay();
    }
  });
  
  // Clear selection functionality
  const clearSelection = document.querySelector('.clear-selection');
  if (clearSelection) {
    clearSelection.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Clear size selection
      sizeButtons.forEach(b => b.classList.remove('selected'));
      selectedSize = null;
      selectedVariation = null;
      
      // Reset all availability states
      resetAvailability();
      
      // Reset displays
      updateProductDisplay();
    });
  }
  
  // Quantity controls
  const quantityValue = document.querySelector('.quantity-value');
  const spinnerUp = document.querySelector('.spinner-btn.up');
  const spinnerDown = document.querySelector('.spinner-btn.down');
  
  function updateQuantityDisplay(value) {
    if (quantityValue) {
      quantityValue.textContent = value;
    }
  }
  
  function getCurrentQuantity() {
    return quantityValue ? parseInt(quantityValue.textContent) || 1 : 1;
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
      const maxValue = selectedVariation ? selectedVariation.stock_quantity : 999;
      if (currentValue < maxValue) {
        updateQuantityDisplay(currentValue + 1);
      }
    });
  }
  
  if (spinnerUp) {
    spinnerUp.addEventListener('click', function() {
      const currentValue = getCurrentQuantity();
      const maxValue = selectedVariation ? selectedVariation.stock_quantity : 999;
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
    
    // Get only the attributes that have different values across variations
    // (meaning they are actual selectable attributes, not metadata)
    const selectableAttributes = Array.from(allKeys).filter(key => {
      if (metadataFields.includes(key)) return false;
      
      // Check if this attribute has different values across variations
      const values = variations.map(v => v[key]).filter(v => v !== null && v !== undefined && v !== '');
      const uniqueValues = [...new Set(values)];
      
      // If there are multiple unique values, it's a selectable attribute
      return uniqueValues.length > 1;
    });
    
    console.log('Detected selectable attributes:', selectableAttributes);
    return selectableAttributes;
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
        return Object.entries(selectedAttributes).every(([key, value]) => v[key] === value);
      });
      
      // Debug: Log the selected variation
      console.log('Selected variation:', selectedVariation);
      console.log('Selected attributes:', selectedAttributes);
  
      if (selectedVariation) {
        // Use WooCommerce's formatted HTML with fallback
        console.log('Price data:', {
          price: selectedVariation.price,
          price_html: selectedVariation.price_html,
          regular_price: selectedVariation.regular_price,
          sale_price: selectedVariation.sale_price
        });
        
        const priceHtml = selectedVariation.price || selectedVariation.price_html || 'Price not available';
        priceDisplay.innerHTML = priceHtml;
  
        stockDisplay.innerHTML = selectedVariation.in_stock
          ? '<span class="stock-status in-stock">IN STOCK</span>'
          : '<span class="stock-status out-of-stock">OUT OF STOCK</span>';
  
        if (addToCartBtn) {
        addToCartBtn.disabled = !selectedVariation.in_stock;
        addToCartBtn.textContent = selectedVariation.in_stock ? 'ADD TO CART' : 'OUT OF STOCK';
        
        // Remove any existing onclick handlers to prevent duplicates
        addToCartBtn.onclick = null;
        
        // Only add onclick if in stock and not already added
        if (selectedVariation.in_stock && !addToCartBtn.dataset.handlerAdded) {
          addToCartBtn.onclick = (event) => {
            event.preventDefault();
            event.stopPropagation();
            addToCart(event);
          };
          addToCartBtn.dataset.handlerAdded = 'true';
        }
  
          // Debug: Log the button state
          console.log('Button disabled:', addToCartBtn.disabled, 'Stock:', selectedVariation.in_stock);
        }
  
        // Update wishlist button for variable products
        console.log('About to update wishlist button with variation:', selectedVariation.variation_id);
        console.log('Full selectedVariation object:', selectedVariation);
        console.log('Available properties:', Object.keys(selectedVariation));
        
        // Try different possible property names for variation ID
        const variationId = selectedVariation.variation_id || selectedVariation.id || selectedVariation.variationId;
        console.log('Extracted variation ID:', variationId);
        
        updateWishlistButton(variationId);
        
        // Stock quantity is now handled in the getCurrentQuantity function
      } else {
        priceDisplay.innerHTML = '<div class="price-current">Combination not available</div>';
        stockDisplay.innerHTML = '<span class="stock-status out-of-stock">NOT AVAILABLE</span>';
        if (addToCartBtn) {
        addToCartBtn.disabled = true;
        addToCartBtn.textContent = 'Combination Not Available';
        addToCartBtn.onclick = null;
        }
      }
    } else {
      // Reset to default range price until a selection is made
      // Get the default price from the first variation or use a fallback
      const defaultPrice = variations.length > 0 ? variations[0].price : 'Price not available';
      priceDisplay.innerHTML = defaultPrice;
      stockDisplay.innerHTML = '<span class="stock-status">Select options to see availability</span>';
      if (addToCartBtn) {
      addToCartBtn.disabled = true;
      addToCartBtn.textContent = 'Select Options';
      addToCartBtn.onclick = null;
      }
    }
  }
  
// Add to cart function
function addToCart(event) {
  // Prevent default form submission & duplicate triggers
  if (event) {
    event.preventDefault();
    event.stopImmediatePropagation();
  }

  // Prevent multiple simultaneous requests
  if (window.addToCartProcessing) {
    console.log('⚠️ Add to cart already processing, ignoring duplicate call');
    return;
  }
  window.addToCartProcessing = true;

  console.log('=== CUSTOM ADD TO CART CALLED ===');
  console.log('selectedVariation:', selectedVariation);
  console.log('selectedAttributes:', selectedAttributes);

  if (!selectedVariation) {
    showCartMessage('Please select all required options', 'error');
    window.addToCartProcessing = false;
    return;
  }

  const quantity = getCurrentQuantity();
  const formData = new FormData();

  // ✅ Required WooCommerce fields
  const productId =
    window.item?.product_id || selectedVariation?.product_id || selectedVariation?.id;

  formData.append('product_id', productId);
  formData.append('add-to-cart', productId);
  formData.append('variation_id', selectedVariation.id);
  formData.append('quantity', quantity);

// ✅ Append correct attribute keys (handles pa_ and custom)
const allAttributes = {
  ...selectedVariation.attributes,
  ...selectedAttributes,
};

console.log('All attributes:', allAttributes);

  Object.entries(allAttributes).forEach(([key, value]) => {
    if (!value) return;

    // Normalize key — ensure correct WooCommerce naming convention
    let attrKey;
    if (key.startsWith('attribute_')) {
      attrKey = key; // already correct
    } else if (key.startsWith('pa_')) {
      attrKey = `attribute_${key}`;
    } else {
      attrKey = `attribute_pa_${key.replace(/^pa_/, '')}`;
    }

    // Normalize value to WooCommerce-friendly slug (lowercase, no spaces)
    const normalizedValue = value.toString().trim().toLowerCase().replace(/\s+/g, '-');

    // ✅ Append both for WooCommerce + CartFlows compatibility
    formData.append(attrKey, normalizedValue);
    formData.append(`variation[${attrKey}]`, normalizedValue);

    console.log(`Added attribute: ${attrKey} = ${normalizedValue}`);
  });

  // ✅ Determine AJAX endpoint (WooCommerce core-safe)
  let ajaxUrl = '/?wc-ajax=add_to_cart';
  if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.wc_ajax_url) {
    ajaxUrl = wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart');
  } else if (typeof window.productAddToCartUrl !== 'undefined') {
    ajaxUrl = window.productAddToCartUrl;
  } else {
    formData.append('action', 'woocommerce_add_to_cart');
    ajaxUrl = '/wp-admin/admin-ajax.php';
  }

  console.log('🧾 AJAX URL:', ajaxUrl);
  console.log('📦 Form Data:', Object.fromEntries([...formData.entries()]));

  // ✅ Perform WooCommerce AJAX Add to Cart
  fetch(ajaxUrl, {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  })
    .then((response) => {
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      return response.text();
    })
    .then((text) => {
      try {
        return JSON.parse(text);
      } catch (e) {
        console.warn('Non-JSON response — treating as success');
        return { success: true };
      }
    })
    .then((data) => {
      console.log('📦 WooCommerce Response:', data);

      if (data.success === false) {
        showCartMessage('Error: ' + (data.data || 'Unknown error'), 'error');
        window.addToCartProcessing = false;
        return;
      }

      // ✅ Update cart fragments if available
      if (data.fragments) {
        Object.entries(data.fragments).forEach(([selector, html]) => {
          const el = document.querySelector(selector);
          if (el) el.innerHTML = html;
        });
      }

      // ✅ Fire WooCommerce-compatible event
      const evt = new CustomEvent('added_to_cart', {
        detail: {
          fragments: data.fragments,
          cart_hash: data.cart_hash,
          button: document.querySelector('.add-to-cart-btn'),
        },
      });
      document.body.dispatchEvent(evt);

      if (typeof jQuery !== 'undefined') {
        jQuery(document.body).trigger('added_to_cart', [
          data.fragments,
          data.cart_hash,
          jQuery('.add-to-cart-btn'),
        ]);
      }

      // ✅ Flash success UI
      const button = document.querySelector('.add-to-cart-btn');
      if (button) {
        const original = button.innerHTML;
        button.innerHTML = '✅ Added!';
        button.style.background = '#10b981';
        setTimeout(() => {
          button.innerHTML = original;
          button.style.background = '';
        }, 2000);
      }

      // ✅ Open Cart Drawer (no redirect)
      setTimeout(() => {
        if (typeof window.openCartDrawer === 'function') {
          window.openCartDrawer(true); // force reload
        } else {
          const trigger =
            document.querySelector('.cart-trigger, .navbar-bag, [data-cart-url]');
          if (trigger) trigger.click();
        }

        // Optional UI refresh helpers
        if (typeof window.loadCartContent === 'function') {
          setTimeout(() => window.loadCartContent(true), 100);
        }
        if (typeof window.updateBagCount === 'function') {
          setTimeout(() => window.updateBagCount(), 200);
        }

        // ✅ Reset processing flag
        window.addToCartProcessing = false;
      }, 500);
    })
    .catch((err) => {
      console.error('❌ Add to cart error:', err);
      showCartMessage('❌ Error adding product to cart', 'error');
      window.addToCartProcessing = false;
    });
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
  
  // Update wishlist button for variable products
  function updateWishlistButton(variationId) {
    console.log('updateWishlistButton called with variation ID:', variationId);
    
    // Try multiple selectors to find the wishlist button
    const wishlistButton = document.querySelector('.tinvwl_add_to_wishlist_button, [data-tinv-wl-list], .tinv-wl-button, .tinvwl-button');
    console.log('Wishlist button found:', wishlistButton);
    
    if (wishlistButton && variationId) {
      // Enable the button
      wishlistButton.classList.remove('disabled');
      wishlistButton.setAttribute('data-variation-id', variationId);
      wishlistButton.setAttribute('data-tinv-wl-productvariation', variationId);
      wishlistButton.style.opacity = '1';
      wishlistButton.style.pointerEvents = 'auto';
      
      console.log('Wishlist button enabled for variation:', variationId);
      console.log('Wishlist button attributes:', {
        'data-variation-id': wishlistButton.getAttribute('data-variation-id'),
        'data-tinv-wl-productvariation': wishlistButton.getAttribute('data-tinv-wl-productvariation'),
        'class': wishlistButton.className,
        'opacity': wishlistButton.style.opacity
      });
    } else {
      console.log('Wishlist button not found or no variation ID');
      console.log('Available buttons:', document.querySelectorAll('button, a').length);
      console.log('All button classes:', Array.from(document.querySelectorAll('button, a')).map(btn => btn.className));
    }
  }

  // Initialize wishlist button state
  function initializeWishlistButton() {
    const wishlistButton = document.querySelector('.tinvwl_add_to_wishlist_button');
    if (wishlistButton) {
      // Disable initially for variable products
      wishlistButton.classList.add('disabled');
      wishlistButton.setAttribute('data-variation-id', '0');
      wishlistButton.style.opacity = '0.5';
      wishlistButton.style.pointerEvents = 'none';
      
      // Add click prevention
      wishlistButton.addEventListener('click', function(e) {
        const vid = this.getAttribute('data-variation-id');
        if (!vid || vid === '0') {
          e.preventDefault();
          e.stopPropagation();
          alert('Please select product options first.');
          return false;
        }
      });
      
      console.log('Wishlist button initialized and disabled');
    }
  }
  
  // Initialize display
  updateProductDisplay();
  
  // Initialize wishlist button
  setTimeout(initializeWishlistButton, 500);
});

// TI Wishlist integration for variable products
document.addEventListener('DOMContentLoaded', function() {
  console.log('TI Wishlist integration starting...');
  
  // Wait for the page to fully load
  setTimeout(function() {
    var form = document.querySelector('form.variations_form, form[data-product_variations], .variations_form');
    var wishlistBtn = document.querySelector('.tinvwl_add_to_wishlist_button, [data-tinv-wl-list], .tinv-wl-button, .tinvwl-button, .tinvwl_add_to_wishlist');
    
    console.log('Form found:', form ? 1 : 0);
    console.log('Wishlist button found:', wishlistBtn ? 1 : 0);
    console.log('Wishlist button HTML:', wishlistBtn);
    
    // Check if it's a variable product by looking at the wishlist button data
    var isVariableProduct = wishlistBtn && wishlistBtn.getAttribute('data-tinv-wl-producttype') === 'variable';
    console.log('Is variable product:', isVariableProduct);
    
    // Check if we're on a variable product page
    if ((isVariableProduct || form) && wishlistBtn) {
      console.log('Setting up wishlist for variable product');
      
      // Disable by default
      wishlistBtn.classList.add('disabled');
      wishlistBtn.setAttribute('data-variation-id', 0);
      wishlistBtn.style.opacity = '0.5';
      wishlistBtn.style.pointerEvents = 'none';

      // When a valid variation is found (WooCommerce event)
      if (form) {
        form.addEventListener('found_variation', function(event) {
          console.log('WooCommerce variation found:', event.detail);
          var variation = event.detail;
          if (variation && variation.variation_id) {
            wishlistBtn.classList.remove('disabled');
            wishlistBtn.setAttribute('data-variation-id', variation.variation_id);
            wishlistBtn.setAttribute('data-tinv-wl-productvariation', variation.variation_id);
            wishlistBtn.style.opacity = '1';
            wishlistBtn.style.pointerEvents = 'auto';
            console.log('Wishlist button enabled for variation:', variation.variation_id);
          }
        });
      }
      
      // Also listen for custom variation selection (your existing logic)
      document.addEventListener('click', function(e) {
        if ((e.target.classList.contains('color-dot') && e.target.classList.contains('selected')) ||
            (e.target.classList.contains('size-button') && e.target.classList.contains('selected'))) {
          console.log('Custom variation selection detected');
          // Check if we have a selected variation from your existing logic
          setTimeout(function() {
            var selectedVariation = window.selectedVariation;
            if (selectedVariation && selectedVariation.variation_id) {
              console.log('Custom variation found:', selectedVariation);
              wishlistBtn.classList.remove('disabled');
              wishlistBtn.setAttribute('data-variation-id', selectedVariation.variation_id);
              wishlistBtn.setAttribute('data-tinv-wl-productvariation', selectedVariation.variation_id);
              wishlistBtn.style.opacity = '1';
              wishlistBtn.style.pointerEvents = 'auto';
              console.log('Wishlist button enabled for custom variation:', selectedVariation.variation_id);
            }
          }, 100);
        }
      });

      // Block wishlist add if no variation selected
      wishlistBtn.addEventListener('click', function(e) {
        var vid = this.getAttribute('data-variation-id');
        console.log('Wishlist clicked, variation ID:', vid);
        if (!vid || vid == 0) {
          e.preventDefault();
          e.stopPropagation();
          alert('Please select product options first.');
          return false;
        }
      });
    } else if (wishlistBtn) {
      console.log('Simple product - wishlist enabled immediately');
    } else {
      var allButtons = document.querySelectorAll('button, a');
      console.log('No wishlist button found. Available buttons:', allButtons.length);
      console.log('All buttons:', Array.from(allButtons).map(function(btn) { return btn.className; }));
    }
  }, 1000); // Wait 1 second for shortcode to render
});