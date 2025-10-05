document.addEventListener('DOMContentLoaded', function() {
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
        addToCartBtn.onclick = selectedVariation.in_stock ? () => addToCart() : null;
  
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
  function addToCart() {
    if (!selectedVariation) return;
    
    const quantity = getCurrentQuantity();
    
    // Create form data
    const formData = new FormData();
    formData.append('add-to-cart', selectedVariation.id);
    formData.append('quantity', quantity);
    
    // Add variation attributes
    if (selectedColor) {
      formData.append('attribute_pa_color', selectedColor);
    }
    if (selectedSize) {
      formData.append('attribute_pa_size', selectedSize);
    }
    
    // Submit to WooCommerce
    fetch(window.productAddToCartUrl, {
      method: 'POST',
      body: formData
    })
    .then(response => {
      if (response.ok) {
        // Show success message or redirect
        alert('Product added to cart!');
        // You can also trigger a cart update event here
        if (typeof wc_add_to_cart_params !== 'undefined') {
          $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $('.add-to-cart-btn')]);
        }
      } else {
        alert('Error adding product to cart');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error adding product to cart');
    });
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
jQuery(function($) {
  console.log('TI Wishlist integration starting...');
  
  // Wait for the page to fully load
  setTimeout(function() {
    var $form = $('form.variations_form, form[data-product_variations], .variations_form');
    var $wishlistBtn = $('.tinvwl_add_to_wishlist_button, [data-tinv-wl-list], .tinv-wl-button, .tinvwl-button, .tinvwl_add_to_wishlist');
    
    console.log('Form found:', $form.length);
    console.log('Wishlist button found:', $wishlistBtn.length);
    console.log('Wishlist button HTML:', $wishlistBtn[0]);
    
    // Check if it's a variable product by looking at the wishlist button data
    var isVariableProduct = $wishlistBtn.attr('data-tinv-wl-producttype') === 'variable';
    console.log('Is variable product:', isVariableProduct);
    
    // Check if we're on a variable product page
    if ((isVariableProduct || $form.length) && $wishlistBtn.length) {
      console.log('Setting up wishlist for variable product');
      
      // Disable by default
      $wishlistBtn.addClass('disabled').attr('data-variation-id', 0);
      $wishlistBtn.css('opacity', '0.5').css('pointer-events', 'none');

      // When a valid variation is found (WooCommerce event)
      $form.on('found_variation', function(event, variation) {
        console.log('WooCommerce variation found:', variation);
        if (variation && variation.variation_id) {
          $wishlistBtn.removeClass('disabled');
          $wishlistBtn.attr('data-variation-id', variation.variation_id);
          $wishlistBtn.attr('data-tinv-wl-productvariation', variation.variation_id);
          $wishlistBtn.css('opacity', '1').css('pointer-events', 'auto');
          console.log('Wishlist button enabled for variation:', variation.variation_id);
        }
      });
      
      // Also listen for custom variation selection (your existing logic)
      $(document).on('click', '.color-dot.selected, .size-button.selected', function() {
        console.log('Custom variation selection detected');
        // Check if we have a selected variation from your existing logic
        setTimeout(function() {
          var selectedVariation = window.selectedVariation;
          if (selectedVariation && selectedVariation.variation_id) {
            console.log('Custom variation found:', selectedVariation);
            $wishlistBtn.removeClass('disabled');
            $wishlistBtn.attr('data-variation-id', selectedVariation.variation_id);
            $wishlistBtn.attr('data-tinv-wl-productvariation', selectedVariation.variation_id);
            $wishlistBtn.css('opacity', '1').css('pointer-events', 'auto');
            console.log('Wishlist button enabled for custom variation:', selectedVariation.variation_id);
          }
        }, 100);
      });

      // Block wishlist add if no variation selected
      $wishlistBtn.on('click', function(e) {
        var vid = $(this).data('variation-id') || $(this).attr('data-variation-id');
        console.log('Wishlist clicked, variation ID:', vid);
        if (!vid || vid == 0) {
          e.preventDefault();
          e.stopPropagation();
          alert('Please select product options first.');
          return false;
        }
      });
    } else if ($wishlistBtn.length) {
      console.log('Simple product - wishlist enabled immediately');
    } else {
      console.log('No wishlist button found. Available buttons:', $('button, a').length);
      console.log('All buttons:', $('button, a').map(function() { return this.className; }).get());
    }
  }, 1000); // Wait 1 second for shortcode to render
});