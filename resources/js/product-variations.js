document.addEventListener('DOMContentLoaded', function() {
  // Product variation selection functionality
  const colorDots = document.querySelectorAll('.color-dot');
  const sizeButtons = document.querySelectorAll('.size-button');
  const addToCartBtn = document.querySelector('.add-to-cart-btn');
  const quantityInput = document.querySelector('.quantity-input');
  const minusBtn = document.querySelector('.quantity-btn.minus');
  const plusBtn = document.querySelector('.quantity-btn.plus');
  const priceDisplay = document.getElementById('product-price-display');
  const stockDisplay = document.getElementById('stock-status-display');
  
  let selectedColor = null;
  let selectedSize = null;
  let selectedVariation = null;
  
  // Variations data from PHP
  const variations = window.productVariations || [];
  
  // Color selection
  colorDots.forEach(dot => {
    dot.addEventListener('click', function() {
      if (this.classList.contains('out-of-stock')) return;
      
      // Remove selected class from all color dots
      colorDots.forEach(d => d.classList.remove('selected'));
      
      // Add selected class to clicked dot
      this.classList.add('selected');
      selectedColor = this.getAttribute('data-color');
      
      // Update size availability based on selected color
      updateSizeAvailability();
      
      // Update display and button
      updateProductDisplay();
    });
  });
  
  // Size selection
  sizeButtons.forEach(button => {
    button.addEventListener('click', function() {
      if (this.classList.contains('out-of-stock')) return;
      
      // Remove selected class from all size buttons
      sizeButtons.forEach(b => b.classList.remove('selected'));
      
      // Add selected class to clicked button
      this.classList.add('selected');
      selectedSize = this.getAttribute('data-size');
      
      // Update color availability based on selected size
      updateColorAvailability();
      
      // Update display and button
      updateProductDisplay();
    });
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
  if (minusBtn && plusBtn && quantityInput) {
    minusBtn.addEventListener('click', function() {
      const currentValue = parseInt(quantityInput.value);
      if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
      }
    });
    
    plusBtn.addEventListener('click', function() {
      const currentValue = parseInt(quantityInput.value);
      const maxValue = selectedVariation ? selectedVariation.stock_quantity : 999;
      if (currentValue < maxValue) {
        quantityInput.value = currentValue + 1;
      }
    });
  }
  
  // Update size availability based on selected color
  function updateSizeAvailability() {
    if (!selectedColor) {
      // Reset all sizes to available
      sizeButtons.forEach(button => {
        button.classList.remove('out-of-stock');
        button.disabled = false;
      });
      return;
    }
    
    // Check each size for availability with selected color
    sizeButtons.forEach(button => {
      const size = button.getAttribute('data-size');
      const matchingVariation = variations.find(v => 
        v.color === selectedColor && v.size === size
      );
      
      if (matchingVariation && matchingVariation.in_stock) {
        button.classList.remove('out-of-stock');
        button.disabled = false;
      } else {
        button.classList.add('out-of-stock');
        button.disabled = true;
      }
    });
  }
  
  // Update color availability based on selected size
  function updateColorAvailability() {
    if (!selectedSize) {
      // Reset all colors to available
      colorDots.forEach(dot => {
        dot.classList.remove('out-of-stock');
        dot.style.opacity = '1';
        dot.style.cursor = 'pointer';
      });
      return;
    }
    
    // Check each color for availability with selected size
    colorDots.forEach(dot => {
      const color = dot.getAttribute('data-color');
      const matchingVariation = variations.find(v => 
        v.color === color && v.size === selectedSize
      );
      
      if (matchingVariation && matchingVariation.in_stock) {
        dot.classList.remove('out-of-stock');
        dot.style.opacity = '1';
        dot.style.cursor = 'pointer';
      } else {
        dot.classList.add('out-of-stock');
        dot.style.opacity = '0.5';
        dot.style.cursor = 'not-allowed';
      }
    });
  }
  
  // Reset all availability states
  function resetAvailability() {
    // Reset sizes
    sizeButtons.forEach(button => {
      button.classList.remove('out-of-stock');
      button.disabled = false;
    });
    
    // Reset colors
    colorDots.forEach(dot => {
      dot.classList.remove('out-of-stock');
      dot.style.opacity = '1';
      dot.style.cursor = 'pointer';
    });
  }
  
  // Update product display based on selections
  function updateProductDisplay() {
    if (selectedColor && selectedSize) {
      // Find matching variation
      selectedVariation = variations.find(v => 
        v.color === selectedColor && v.size === selectedSize
      );
      
      if (selectedVariation) {
        // Update price display
        if (selectedVariation.sale_price && selectedVariation.sale_price < selectedVariation.regular_price) {
          priceDisplay.innerHTML = `
            <div class="price-sale">${selectedVariation.price}</div>
            <div class="price-regular">${selectedVariation.regular_price}</div>
          `;
        } else {
          priceDisplay.innerHTML = `<div class="price-current">${selectedVariation.price}</div>`;
        }
        
        // Update stock display
        if (selectedVariation.in_stock) {
          stockDisplay.innerHTML = '<span class="stock-status in-stock">IN STOCK</span>';
        } else {
          stockDisplay.innerHTML = '<span class="stock-status out-of-stock">OUT OF STOCK</span>';
        }
        
        // Update add to cart button
        if (selectedVariation.in_stock) {
          addToCartBtn.disabled = false;
          addToCartBtn.textContent = 'ADD TO CART';
          addToCartBtn.onclick = function() {
            addToCart();
          };
        } else {
          addToCartBtn.disabled = true;
          addToCartBtn.textContent = 'OUT OF STOCK';
          addToCartBtn.onclick = null;
        }
        
        // Update quantity max
        if (quantityInput && selectedVariation.stock_quantity) {
          quantityInput.setAttribute('max', selectedVariation.stock_quantity);
        }
      } else {
        // No matching variation found
        priceDisplay.innerHTML = '<div class="price-current">Combination not available</div>';
        stockDisplay.innerHTML = '<span class="stock-status out-of-stock">NOT AVAILABLE</span>';
        addToCartBtn.disabled = true;
        addToCartBtn.textContent = 'Combination Not Available';
        addToCartBtn.onclick = null;
      }
    } else {
      // Reset to default state
      priceDisplay.innerHTML = '<div class="price-current">Select options to see price</div>';
      stockDisplay.innerHTML = '<span class="stock-status">Select options to see availability</span>';
      addToCartBtn.disabled = true;
      addToCartBtn.textContent = 'Select Options';
      addToCartBtn.onclick = null;
    }
  }
  
  // Add to cart function
  function addToCart() {
    if (!selectedVariation || !quantityInput) return;
    
    const quantity = parseInt(quantityInput.value) || 1;
    
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
  
  // Initialize display
  updateProductDisplay();
});