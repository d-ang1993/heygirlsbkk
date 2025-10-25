/**
 * Wishlist Integration for Variable Products
 * Handles TI Wishlist integration for variable product variations
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('🎁 Wishlist integration starting...');
  
  // Wait for shortcodes to render and TI Wishlist plugin to initialize
  setTimeout(function() {
    const wishlistBtn = findWishlistButton();
    const isVariableProduct = checkIfVariableProduct();
    
    if (wishlistBtn) {
      // Check if item is already in wishlist by checking for existing classes/attributes
      const isInWishlist = wishlistBtn.classList.contains('added') || 
                           wishlistBtn.classList.contains('tinvwl-added') ||
                           wishlistBtn.getAttribute('aria-label')?.toLowerCase().includes('remove') ||
                           wishlistBtn.querySelector('.tinvwl-heart')?.classList.contains('icon_heartcheck');
      
      console.log('💖 Item in wishlist:', isInWishlist);
      console.log('📋 Wishlist button classes:', wishlistBtn.className);
      
      if (isVariableProduct) {
        setupVariableProductWishlist(wishlistBtn);
        
        // If item is already in wishlist, ensure it stays enabled
        if (isInWishlist) {
          // Don't disable it if it's already in wishlist
          console.log('💖 Item already in wishlist, keeping button enabled');
        }
      } else {
        console.log('✅ Simple product - wishlist enabled immediately');
      }
    } else {
      logMissingWishlistButton();
    }
  }, 1500);
});

/**
 * Find the wishlist button using various selectors
 * @returns {HTMLElement|null}
 */
function findWishlistButton() {
  return document.querySelector(
    '.tinvwl_add_to_wishlist_button, [data-tinv-wl-list], .tinv-wl-button, .tinvwl-button'
  );
}

/**
 * Check if current product is a variable product
 * @returns {boolean}
 */
function checkIfVariableProduct() {
  const wishlistBtn = findWishlistButton();
  const form = document.querySelector('form.variations_form, form[data-product_variations], .variations_form');
  
  const isVariable = wishlistBtn && 
                     wishlistBtn.getAttribute('data-tinv-wl-producttype') === 'variable';
  
  console.log('🔍 Is variable product:', isVariable);
  console.log('📋 Form found:', !!form);
  
  return isVariable || !!form;
}

/**
 * Setup wishlist for variable products
 * @param {HTMLElement} wishlistBtn - The wishlist button element
 */
function setupVariableProductWishlist(wishlistBtn) {
  console.log('⚙️ Setting up wishlist for variable product');
  
  // Check if already in wishlist (for page refresh/initialization)
  const isInWishlist = wishlistBtn.classList.contains('added') || 
                       wishlistBtn.classList.contains('tinvwl-added') ||
                       wishlistBtn.getAttribute('aria-label')?.toLowerCase().includes('remove');
  
  // Only disable if not already in wishlist and no variation is selected
  if (!isInWishlist) {
    // Check if a variation is already selected
    const selectedVariation = window.selectedVariation;
    if (!selectedVariation || !selectedVariation.id) {
      disableWishlistButton(wishlistBtn, 'Please select product options first.');
    }
  }
  
  // Listen for WooCommerce variation events
  setupWooCommerceVariationListener(wishlistBtn);
  
  // Listen for custom variation selection
  setupCustomVariationListener(wishlistBtn);
  
  // Prevent clicking when no variation selected (unless already in wishlist)
  if (!isInWishlist) {
    setupClickPrevention(wishlistBtn);
  }
}

/**
 * Disable wishlist button
 * @param {HTMLElement} btn - The wishlist button
 * @param {string} message - Alert message to show when clicked
 */
function disableWishlistButton(btn, message) {
  btn.classList.add('disabled');
  btn.classList.add('disabled-add-wishlist');
  btn.setAttribute('disabled', 'disabled');
  btn.setAttribute('data-variation-id', '0');
  btn.style.opacity = '0.5';
  btn.style.pointerEvents = 'none';
  btn.style.cursor = 'not-allowed';
  btn.setAttribute('data-disabled-message', message || '');
  
  console.log('🚫 Wishlist button disabled');
}

/**
 * Enable wishlist button for specific variation
 * @param {HTMLElement} btn - The wishlist button
 * @param {number} variationId - The variation ID
 */
function enableWishlistButton(btn, variationId) {
  // Remove all disabled classes and attributes
  btn.classList.remove('disabled');
  btn.classList.remove('disabled-add-wishlist'); // TI Wishlist specific class
  btn.classList.remove('tinv-disabled'); // Another TI Wishlist class
  btn.removeAttribute('disabled');
  btn.removeAttribute('aria-disabled');
  
  // Set variation ID attributes - THIS IS CRITICAL for TI Wishlist to know which variation to add
  btn.setAttribute('data-variation-id', variationId);
  btn.setAttribute('data-tinv-wl-productvariation', variationId);
  
  // Also update the href if it exists to include variation ID
  if (btn.hasAttribute('href')) {
    const href = btn.getAttribute('href');
    if (href && href.includes('variation_id=0')) {
      btn.setAttribute('href', href.replace('variation_id=0', 'variation_id=' + variationId));
    }
  }
  
  // Force enable styling
  btn.style.opacity = '1';
  btn.style.pointerEvents = 'auto';
  btn.style.cursor = 'pointer';
  
  console.log('✅ Wishlist button enabled for variation:', variationId);
  console.log('📋 Button data attributes:', {
    'data-variation-id': btn.getAttribute('data-variation-id'),
    'data-tinv-wl-productvariation': btn.getAttribute('data-tinv-wl-productvariation'),
    'href': btn.getAttribute('href')
  });
}

/**
 * Setup listener for WooCommerce native variation events
 * @param {HTMLElement} wishlistBtn - The wishlist button
 */
function setupWooCommerceVariationListener(wishlistBtn) {
  const form = document.querySelector('form.variations_form, form[data-product_variations], .variations_form');
  
  if (!form) {
    console.log('⚠️ No WooCommerce form found');
    return;
  }
  
  form.addEventListener('found_variation', function(event) {
    console.log('🔔 WooCommerce variation found:', event.detail);
    const variation = event.detail;
    
    if (variation && variation.variation_id) {
      enableWishlistButton(wishlistBtn, variation.variation_id);
    }
  });
  
  // Also handle reset
  form.addEventListener('reset_data', function() {
    console.log('🔄 Variation reset');
    disableWishlistButton(wishlistBtn);
  });
}

/**
 * Setup listener for custom variation selection
 * @param {HTMLElement} wishlistBtn - The wishlist button
 */
function setupCustomVariationListener(wishlistBtn) {
  document.addEventListener('click', function(e) {
    // Check for color selection
    if (e.target.classList.contains('color-dot') && e.target.classList.contains('selected')) {
      console.log('🎨 Custom color selection detected');
      updateWishlistFromSelectedVariation(wishlistBtn);
    }
    
    // Check for size selection
    if (e.target.classList.contains('size-button') && e.target.classList.contains('selected')) {
      console.log('📏 Custom size selection detected');
      updateWishlistFromSelectedVariation(wishlistBtn);
    }
  });
}

/**
 * Update wishlist button from globally selected variation
 * @param {HTMLElement} wishlistBtn - The wishlist button
 */
function updateWishlistFromSelectedVariation(wishlistBtn) {
  // Wait a bit for the variation to be set
  setTimeout(function() {
    const selectedVariation = window.selectedVariation;
    
    if (selectedVariation) {
      const variationId = selectedVariation.id || 
                          selectedVariation.variation_id || 
                          selectedVariation.variationId;
      
      if (variationId) {
        console.log('✅ Updating wishlist from custom variation:', selectedVariation);
        
        // Check if THIS specific variation is already in wishlist
        checkAndUpdateWishlistButton(wishlistBtn, variationId);
      } else {
        console.log('⚠️ Variation has no ID - partial selection (need both color and size)');
        // Don't disable, just show as not selected yet
        // Check if ANY variation for this product is in wishlist
        checkProductWishlistStatus(wishlistBtn);
      }
    } else {
      console.log('⚠️ No variation selected yet');
      // Check if product is in wishlist (for simple products or any variation)
      checkProductWishlistStatus(wishlistBtn);
    }
  }, 100);
}

/**
 * Check and update wishlist button state for a specific variation
 */
function checkAndUpdateWishlistButton(wishlistBtn, variationId) {
  // Update the button with the variation ID
  enableWishlistButton(wishlistBtn, variationId);
  
  // Check the data-tinv-wl-list attribute to see if this variation is in the wishlist
  const wishlistData = wishlistBtn.getAttribute('data-tinv-wl-list');
  
  if (wishlistData) {
    try {
      const parsed = JSON.parse(wishlistData);
      
      // Quick check: look for variation ID in the "in" array
      let isInWishlist = false;
      for (const listId in parsed) {
        if (parsed[listId]?.in?.includes(parseInt(variationId))) {
          isInWishlist = true;
          break;
        }
      }
      
      // Update class immediately without logs for speed
      wishlistBtn.classList.toggle('in-wishlist', isInWishlist);
    } catch (e) {
      wishlistBtn.classList.remove('in-wishlist');
    }
  } else {
    // No wishlist data found
    console.log('💔 No wishlist data - showing add button');
    wishlistBtn.classList.remove('in-wishlist');
  }
}

/**
 * Check wishlist status for the product (not specific variation)
 */
function checkProductWishlistStatus(wishlistBtn) {
  const isInWishlist = wishlistBtn.classList.contains('added') || 
                       wishlistBtn.classList.contains('tinvwl-added') ||
                       wishlistBtn.getAttribute('aria-label')?.toLowerCase().includes('remove');
  
  if (isInWishlist) {
    console.log('💖 Product has variations in wishlist, keeping button visible');
    // Keep visible but disabled until specific variation selected
    wishlistBtn.style.opacity = '1';
    wishlistBtn.style.pointerEvents = 'none';
    wishlistBtn.style.cursor = 'wait';
  } else {
    console.log('💔 No variations in wishlist yet');
    // Disable until full selection
    wishlistBtn.style.opacity = '0.5';
    wishlistBtn.style.pointerEvents = 'none';
  }
}

/**
 * Setup click prevention when no variation selected
 * @param {HTMLElement} wishlistBtn - The wishlist button
 */
function setupClickPrevention(wishlistBtn) {
  wishlistBtn.addEventListener('click', function(e) {
    const vid = this.getAttribute('data-variation-id');
    console.log('👆 Wishlist clicked, variation ID:', vid);
    
    if (!vid || vid == 0) {
      e.preventDefault();
      e.stopPropagation();
      
      const message = this.getAttribute('data-disabled-message') || 
                      'Please select product options first.';
      alert(message);
      
      return false;
    }
  });
}

/**
 * Initialize wishlist button for integration with product-variations.js
 * This is called from product-variations.js to enable wishlist for a variation
 * @param {number} variationId - The variation ID to enable
 */
function initializeWishlistForVariation(variationId) {
  const wishlistBtn = findWishlistButton();
  
  if (!wishlistBtn) {
    console.log('⚠️ Wishlist button not found');
    return;
  }
  
  if (variationId && variationId !== 0) {
    enableWishlistButton(wishlistBtn, variationId);
  } else {
    disableWishlistButton(wishlistBtn);
  }
}

/**
 * Log when wishlist button is not found
 */
function logMissingWishlistButton() {
  const allButtons = document.querySelectorAll('button, a');
  console.log('❌ No wishlist button found. Available buttons:', allButtons.length);
  console.log('📋 All button classes:', 
    Array.from(allButtons).map(btn => btn.className).filter(c => c.length > 0)
  );
}

// Export for use in other scripts
window.wishlistIntegration = {
  initializeWishlistForVariation,
  disableWishlistButton,
  enableWishlistButton,
  findWishlistButton,
};
