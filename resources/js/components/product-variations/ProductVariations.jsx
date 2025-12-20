/** @jsxImportSource react */
import React, { useState, useEffect, useMemo } from "react";

export default function ProductVariations({
  productId,
  productType,
  variations = [],
  attributes = {},
  colorVariations = {},
  sizeVariations = {},
  sizes = [],
  availableAttributes = [],
}) {
  // Safety check - return null if not a variable product
  if (productType !== 'variable') {
    return null;
  }
  const [selectedColor, setSelectedColor] = useState(null);
  const [selectedSize, setSelectedSize] = useState(null);
  const [selectedVariation, setSelectedVariation] = useState(null);

  // Initialize with first available color
  useEffect(() => {
    if (colorVariations && Object.keys(colorVariations).length > 0 && !selectedColor) {
      const firstColor = Object.keys(colorVariations)[0];
      setSelectedColor(firstColor);
    }
  }, [colorVariations, selectedColor]);

  // Helper to get size from variation attributes (handles both singular and plural)
  const getVariationSize = (attrs) => {
    return attrs.attribute_pa_sizes || 
           attrs.attribute_pa_size || 
           attrs.attribute_sizes || 
           attrs.attribute_size || 
           '';
  };

  // Find matching variation when selections change
  useEffect(() => {
    if (variations.length === 0) return;

    const matchingVariation = variations.find((variation) => {
      const variationAttrs = variation.attributes || {};
      
      const colorMatch = !selectedColor || 
        variationAttrs.attribute_pa_color === selectedColor ||
        variationAttrs.attribute_color === selectedColor;
      
      const variationSize = getVariationSize(variationAttrs);
      const sizeMatch = !selectedSize || variationSize === selectedSize;

      return colorMatch && sizeMatch;
    });

    setSelectedVariation(matchingVariation || null);

    // Update price display (using the same function from variations.blade.php)
    if (matchingVariation) {
      const priceDisplay = document.getElementById('product-price-display');
      if (priceDisplay && matchingVariation.price_html) {
        const isOnSale = matchingVariation.display_regular_price && 
                        matchingVariation.display_regular_price != matchingVariation.display_price;
        
        if (isOnSale) {
          priceDisplay.innerHTML = `
            <div class="price-sale">${matchingVariation.price_html}</div>
            <div class="price-regular">${matchingVariation.regular_price_html || ''}</div>
          `;
        } else {
          priceDisplay.innerHTML = `
            <div class="price-current">${matchingVariation.price_html}</div>
          `;
        }
      }
      
      // Also try the window function if it exists
      if (window.updateProductPrice) {
        window.updateProductPrice(matchingVariation);
      }
    }

    // Update variation_id input
    const variationInput = document.getElementById('variation_id');
    if (variationInput && matchingVariation?.variation_id) {
      variationInput.value = matchingVariation.variation_id;
    }
    
    // Update selected attributes for add to cart functionality
    if (window.selectedAttributes !== undefined) {
      if (selectedColor) {
        window.selectedAttributes.color = selectedColor;
      }
      if (selectedSize) {
        window.selectedAttributes.sizes = selectedSize;
      }
      // Trigger update if function exists
      if (window.updateProductDisplay) {
        window.updateProductDisplay();
      }
    }
  }, [selectedColor, selectedSize, variations]);

  // Get color name from key
  const getColorName = (colorKey) => {
    return colorKey
      .replace(/[-_]/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  };

  // Get color hex value
  const getColorValue = (colorKey) => {
    // Try to get from color variations data (new format with color_hex)
    if (colorVariations[colorKey]?.color_hex) {
      return colorVariations[colorKey].color_hex;
    }
    // Try old format
    if (colorVariations[colorKey]?.color) {
      return colorVariations[colorKey].color;
    }
    // Fallback
    return '#cccccc';
  };

  // Helper function to get stock status from variation
  const getVariationStockStatus = (variation) => {
    // Check multiple possible stock status properties
    // WooCommerce variations can have different property names
    if (variation.is_in_stock !== undefined) {
      return variation.is_in_stock === true;
    }
    if (variation.in_stock !== undefined) {
      return variation.in_stock === true;
    }
    if (variation.stock_status !== undefined) {
      return variation.stock_status === 'instock' || variation.stock_status === 'in stock';
    }
    // Check if explicitly out of stock
    if (variation.is_in_stock === false || variation.in_stock === false) {
      return false;
    }
    if (variation.stock_status === 'outofstock' || variation.stock_status === 'out of stock') {
      return false;
    }
    // Default to true if stock status is unknown (assume available)
    // This is safer than disabling all sizes
    return true;
  };

  // Check if color has stock
  const colorHasStock = (colorKey) => {
    return variations.some((variation) => {
      const attrs = variation.attributes || {};
      const variationColor = attrs.attribute_pa_color || attrs.attribute_color || '';
      const inStock = getVariationStockStatus(variation);
      return variationColor === colorKey && inStock;
    });
  };

  // Check if size has stock for selected color
  const sizeHasStock = (size) => {
    // If no color selected, show all sizes that have at least one in-stock variation
    if (!selectedColor) {
      return variations.some((variation) => {
        const attrs = variation.attributes || {};
        const variationSize = getVariationSize(attrs);
        const inStock = getVariationStockStatus(variation);
        return variationSize === size && inStock;
      });
    }
    
    // If color is selected, check for matching color+size combination
    let hasStock = variations.some((variation) => {
      const attrs = variation.attributes || {};
      const variationColor = attrs.attribute_pa_color || attrs.attribute_color || '';
      const variationSize = getVariationSize(attrs);
      const inStock = getVariationStockStatus(variation);
      
      const colorMatches = variationColor === selectedColor;
      const sizeMatches = variationSize === size;
      const matches = colorMatches && sizeMatches && inStock;
      
      return matches;
    });
    
    // Fallback: If no exact match found but size exists in variations, 
    // and stock status is unknown, assume it's available
    if (!hasStock) {
      const sizeExists = variations.some((variation) => {
        const attrs = variation.attributes || {};
        const variationSize = getVariationSize(attrs);
        return variationSize === size;
      });
      
      // If size exists but we couldn't find a match, it might be a data structure issue
      // Default to available to avoid disabling all sizes
      if (sizeExists) {
        hasStock = true;
      }
    }
    
    return hasStock;
  };

  // Extract unique sizes from variations or use provided sizes array
  const allSizes = useMemo(() => {
    // If sizes array is provided, use it
    if (sizes && sizes.length > 0) {
      return sizes;
    }
    
    // Otherwise, extract from variations
    const sizeSet = new Set();
    
    variations.forEach((variation) => {
      const attrs = variation.attributes || {};
      const variationSize = getVariationSize(attrs);
      if (variationSize) {
        sizeSet.add(variationSize);
      }
    });
    
    // Also check sizeVariations object as fallback
    if (Object.keys(sizeVariations || {}).length > 0) {
      Object.keys(sizeVariations).forEach(size => sizeSet.add(size));
    }
    
    return Array.from(sizeSet);
  }, [variations, sizeVariations, sizes]);

  // Get sorted sizes
  const sortedSizes = useMemo(() => {
    const sizeOrder = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl'];
    const sizes = allSizes;
    
    const sorted = [];
    sizeOrder.forEach(orderedSize => {
      sizes.forEach(size => {
        const normalized = size.toLowerCase().replace(/[-_]/g, '');
        if (normalized === orderedSize && !sorted.includes(size)) {
          sorted.push(size);
        }
      });
    });
    
    sizes.forEach(size => {
      if (!sorted.includes(size)) {
        sorted.push(size);
      }
    });
    
    return sorted;
  }, [allSizes]);

  // Handle color selection
  const handleColorSelect = (colorKey, colorName, element) => {
    setSelectedColor(colorKey);
    
    // Update selected color display
    const selectedColorName = document.getElementById('selected-color-name-react');
    if (selectedColorName) {
      selectedColorName.textContent = colorName;
      
      const colorValue = getColorValue(colorKey);
      if (colorValue) {
        selectedColorName.style.setProperty('--text-color', colorValue);
        selectedColorName.classList.add('gradient-text');
        selectedColorName.setAttribute('data-color', colorValue);
      }
    }
    
    const selectedColorDisplay = document.getElementById('selected-color-display-react');
    if (selectedColorDisplay) {
      selectedColorDisplay.classList.add('color-selected');
    }
  };

  // Single color check
  const isSingleColor = Object.keys(colorVariations || {}).length === 1;
  const singleColorKey = isSingleColor ? Object.keys(colorVariations || {})[0] : null;
  const singleColorName = singleColorKey ? getColorName(singleColorKey) : null;
  const singleColorValue = singleColorKey ? getColorValue(singleColorKey) : null;

  return (
    <div className="product-variations-react">
      {/* Color Variations */}
      {colorVariations && Object.keys(colorVariations).length > 0 && (
        <div className="color-selection">
          <div className="selected-color-display" id="selected-color-display-react">
            <span className="selected-color-label">
              Color:{' '}
              <span
                id="selected-color-name-react"
                style={isSingleColor && singleColorValue ? { color: singleColorValue } : {}}
                data-color={isSingleColor && singleColorValue ? singleColorValue : ''}
              >
                {isSingleColor ? singleColorName : 'Select a color'}
              </span>
            </span>
          </div>
          <div className={`color-dots ${isSingleColor ? 'single-color' : ''}`}>
            {Object.entries(colorVariations).map(([colorKey, colorData]) => {
              // Handle both new format (with color_name, color_hex) and old format (direct variation)
              const variation = colorData.variation || colorData;
              const colorName = colorData.color_name || getColorName(colorKey);
              const colorValue = getColorValue(colorKey);
              const hasStock = colorHasStock(colorKey);
              const isSelected = selectedColor === colorKey;
              const variationId = variation.variation_id || colorData.variation_id;

              return (
                <button
                  key={colorKey}
                  className={`color-dot ${!hasStock ? 'out-of-stock' : ''} ${isSingleColor ? 'single-color-selected' : ''} ${isSelected ? 'active' : ''}`}
                  style={{ backgroundColor: colorValue }}
                  title={colorName}
                  data-color={colorKey}
                  data-color-name={colorName}
                  data-variation-id={variationId}
                  onClick={() => handleColorSelect(colorKey, colorName, null)}
                  disabled={!hasStock}
                >
                  {!hasStock && <span className="out-of-stock-x">×</span>}
                </button>
              );
            })}
          </div>
        </div>
      )}

      {/* Size Variations */}
      {sortedSizes.length > 0 && (
        <div className="size-selection">
          <label className="variation-label">Size</label>
          <div className="size-buttons">
            {sortedSizes.map((size) => {
              const sizeName = size.replace(/[-_]/g, ' ').toUpperCase();
              const hasStock = sizeHasStock(size);
              const isSelected = selectedSize === size;

              return (
                <button
                  key={size}
                  className={`size-button ${!hasStock ? 'out-of-stock' : ''} ${isSelected ? 'selected' : ''}`}
                  data-size={size}
                  onClick={() => setSelectedSize(size)}
                  disabled={!hasStock}
                >
                  {sizeName}
                </button>
              );
            })}
          </div>
        </div>
      )}

      {/* Debug Info (only in development) */}
      {process.env.NODE_ENV === 'development' && (
        <div style={{ marginTop: '20px', padding: '10px', background: '#f0f0f0', fontSize: '12px' }}>
          <strong>React Component Debug:</strong>
          <br />Selected Color: <strong>{selectedColor || 'None'}</strong>
          <br />Selected Size: {selectedSize || 'None'}
          <br />Selected Variation ID: {selectedVariation?.variation_id || 'None'}
          <br />Variations Count: {variations.length}
          <br />Sizes: {sortedSizes.join(', ')}
          <br />Size Stock Status: {sortedSizes.map(s => `${s}: ${sizeHasStock(s) ? '✅' : '❌'}`).join(', ')}
        </div>
      )}
    </div>
  );
}
