<!-- Product Variations -->
@if($product_type === 'variable')
  @php
    // Use the variations_data passed from the parent component, or fallback to getting it directly
    $product = wc_get_product($product->get_id() ?? $product);
    $variations = $product->get_available_variations();
    $attributes = $product->get_variation_attributes();
    
    // ✅ Extract dynamic attribute names for JavaScript
    $availableAttributes = [];
    if (isset($attributes) && is_array($attributes)) {
      foreach ($attributes as $attrName => $attrValues) {
        // Remove 'pa_' prefix if it exists for cleaner attribute name
        $cleanName = str_replace('pa_', '', $attrName);
        $availableAttributes[] = $cleanName;
      }
    }
    
    // Group variations by attribute
    $color_variations = [];
    $size_variations = [];
    
    foreach ($variations as $variation) {
      // Handle both formats: variations_data format and get_available_variations format
      if (isset($variation['attributes'])) {
        $variation_attributes = $variation['attributes'];
      } else {
        // If it's from variations_data, we need to reconstruct the attributes format
        $variation_attributes = [];
        if (isset($variation['color']) && !empty($variation['color'])) {
          $variation_attributes['attribute_pa_color'] = $variation['color'];
        }
        if (isset($variation['sizes']) && !empty($variation['sizes'])) {
          $variation_attributes['attribute_pa_sizes'] = $variation['sizes'];
        }
      }
      
      // Check for color variations
      if (isset($variation_attributes['attribute_pa_color']) || isset($variation_attributes['attribute_color'])) {
        $color_key = $variation_attributes['attribute_pa_color'] ?? $variation_attributes['attribute_color'];
        if (!isset($color_variations[$color_key])) {
          $color_variations[$color_key] = $variation;
        }
      }
      
      // Check for size variations
      if (isset($variation_attributes['attribute_pa_sizes']) || isset($variation_attributes['attribute_size'])) {
        $size_key = $variation_attributes['attribute_pa_sizes'] ?? $variation_attributes['attribute_size'];
        if (!isset($size_variations[$size_key])) {
          $size_variations[$size_key] = $variation;
        }
      }
    }
    
  @endphp
  
  <!-- Debug Script -->
  <script>
    console.log("Product ID HELLO:", {!! $product->get_id() !!});
    console.log("Product Type:", "{!! $product_type !!}");
    console.log("All Variations:", {!! json_encode($variations) !!});
    console.log("All Attributes:", {!! json_encode($attributes) !!});
    console.log("Color Variations:", {!! json_encode($color_variations) !!});
    console.log("Size Variations:", {!! json_encode($size_variations) !!});
    console.log("Product is variable:", {!! $product->is_type('variable') ? 'true' : 'false' !!});
    console.log("Has attributes:", {!! $product->has_attributes() ? 'true' : 'false' !!});
    console.log("Variations data passed:", {!! json_encode($variations_data ?? []) !!});
  </script>
  
  <!-- Single Color Styling -->
  <style>
    .color-dots.single-color .color-dot.single-color-selected {
      border: 2px solid #000 !important;
      transform: scale(1.1);
      /* box-shadow: 0 0 10px rgba(0,0,0,0.3); */
    }
    .color-dots.single-color .color-dot {
      cursor: default;
    }
    .color-dots.single-color .color-dot:hover {
      transform: none;
    }
    
    /* Enhanced color text styling with gradient */
    #selected-color-name {
      font-weight: 700 !important;
      letter-spacing: 0.8px;
      font-size: 1.1em;
      transition: all 0.3s ease;
      background: linear-gradient(120deg, var(--text-color, #333), #000);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Ensure gradient works properly when class is applied */
    #selected-color-name.gradient-text {
      background: linear-gradient(120deg, var(--text-color, #333), #000);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Remove underline from the "Color:" label */
    .selected-color-label {
      text-decoration: none !important;
    }
  </style>
  
  <!-- Color Variations -->
  @if(!empty($color_variations))
    <div class="color-selection">
      <!-- <label class="variation-label">Color</label> -->
      <div class="selected-color-display" id="selected-color-display">
        <span class="selected-color-label">Color: <span id="selected-color-name" 
          @if(count($color_variations) === 1)
            @php
              $single_color_key = array_keys($color_variations)[0];
              $single_color_name = str_replace(['-', '_'], ' ', $single_color_key);
              $single_color_name = ucwords($single_color_name);
              
              // Get the actual color for the text
              $single_color_term = get_term_by('slug', $single_color_key, 'pa_color');
              $single_color_value = '#cccccc'; // fallback
              if ($single_color_term) {
                $color_meta = get_term_meta($single_color_term->term_id, 'product_attribute_color', true);
                if (!empty($color_meta)) {
                  $single_color_value = $color_meta;
                }
              }
            @endphp
            style="color: {{ $single_color_value }};"
            data-color="{{ $single_color_value }}"
          @endif
        >
          @if(count($color_variations) === 1)
            {{ $single_color_name }}
          @else
            Select a color
          @endif
        </span></span>
      </div>
      <div class="color-dots {{ count($color_variations) === 1 ? 'single-color' : '' }}">
        @foreach($color_variations as $color_key => $variation)
          @php
            $color_name = str_replace(['-', '_'], ' ', $color_key);
            $color_name = ucwords($color_name);
            
            // Get the term by slug from the 'pa_color' taxonomy
            $color_term = get_term_by('slug', $color_key, 'pa_color');
            
            // Default fallback color
            $dot_color = '#cccccc';
            
            if ($color_term) {
              // Get hex color from Variation Swatches for WooCommerce plugin
              $color_meta = get_term_meta($color_term->term_id, 'product_attribute_color', true);
              if (!empty($color_meta)) {
                $dot_color = $color_meta;
              } else {
                $dot_color = '#cccccc'; // fallback color
              }
            }
            
            // Check if ALL variations for this color are out of stock
            $color_has_stock = false;
            foreach ($variations as $check_variation) {
              $check_attributes = $check_variation['attributes'];
              $check_color_key = $check_attributes['attribute_pa_color'] ?? $check_attributes['attribute_color'] ?? '';
              
              if ($check_color_key === $color_key) {
                $check_variation_obj = wc_get_product($check_variation['variation_id']);
                if ($check_variation_obj && $check_variation_obj->is_in_stock()) {
                  $color_has_stock = true;
                  break;
                }
              }
            }
          @endphp
          <button class="color-dot {{ !$color_has_stock ? 'out-of-stock' : '' }} {{ count($color_variations) === 1 ? 'single-color-selected' : '' }}" 
                  style="background-color: {{ $dot_color }};"
                  title="{{ $color_name }}"
                  data-color="{{ $color_key }}"
                  data-color-name="{{ $color_name }}"
                  data-variation-id="{{ $variation['variation_id'] }}">
            @if(!$color_has_stock)
              <span class="out-of-stock-x">×</span>
            @endif
          </button>
        @endforeach
      </div>
    </div>
  @endif
  
  <!-- Size Component -->
  @if(!empty($sizes))
    @php
      // Define size order
      $size_order = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl'];
      
      // Sort sizes according to the defined order
      $sorted_sizes = [];
      foreach ($size_order as $ordered_size) {
        foreach ($sizes as $size) {
          $normalized_size = strtolower(str_replace(['-', '_'], '', $size));
          if ($normalized_size === $ordered_size) {
            $sorted_sizes[] = $size;
            break;
          }
        }
      }
      
      // Add any remaining sizes that weren't in the predefined order
      foreach ($sizes as $size) {
        if (!in_array($size, $sorted_sizes)) {
          $sorted_sizes[] = $size;
        }
      }
    @endphp
    <div class="size-selection">
      <label class="variation-label">Size</label>
      <div class="size-buttons">
        @foreach($sorted_sizes as $size)
          @php
            $size_name = str_replace(['-', '_'], ' ', $size);
            $size_name = strtoupper($size_name);
            
            // Check if this size is in stock for single color products
            $size_in_stock = true; // Default to available
            if (count($color_variations) === 1) {
              // For single color products, check if this size is available
              $single_color_key = array_keys($color_variations)[0];
              foreach ($variations as $variation) {
                if (isset($variation['attributes'])) {
                  $variation_attributes = $variation['attributes'];
                } else {
                  $variation_attributes = [];
                  if (isset($variation['color']) && $variation['color'] === $single_color_key) {
                    $variation_attributes['attribute_pa_color'] = $variation['color'];
                  }
                  if (isset($variation['sizes']) && $variation['sizes'] === $size) {
                    $variation_attributes['attribute_pa_sizes'] = $variation['sizes'];
                  }
                }
                
                $variation_color = $variation_attributes['attribute_pa_color'] ?? $variation_attributes['attribute_color'] ?? '';
                $variation_size = $variation_attributes['attribute_pa_sizes'] ?? $variation_attributes['attribute_size'] ?? '';
                
                if ($variation_color === $single_color_key && $variation_size === $size) {
                  // Found matching variation, check if it's in stock
                  $variation_obj = wc_get_product($variation['variation_id']);
                  $size_in_stock = $variation_obj && $variation_obj->is_in_stock();
                  break;
                }
              }
            }
          @endphp
          <button class="size-button {{ !$size_in_stock ? 'out-of-stock' : '' }}"
                  data-size="{{ $size }}">
            {{ $size_name }}
          </button>
        @endforeach
      </div>
    </div>
  @else
    <!-- Debug info for sizes -->
    @if(current_user_can('manage_options'))
      <div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc; font-size: 12px;">
        <strong>Size Debug Info:</strong><br>
        Sizes found: {{ count($sizes) }}<br>
        Sizes array: {{ json_encode($sizes) }}<br>
        Product type: {{ $product_type }}<br>
        @if($product_type === 'variable')
          Variations count: {{ count($variations_data) }}<br>
          @if(!empty($variations_data))
            All variations data: {{ json_encode($variations_data) }}<br>
          @endif
          @php
            $raw_variations = $product->get_available_variations();
          @endphp
          Raw variations count: {{ count($raw_variations) }}<br>
          @if(!empty($raw_variations))
            First raw variation: {{ json_encode($raw_variations[0] ?? []) }}<br>
          @endif
        @endif
      </div>
    @endif
  @endif
@endif

<script>
// Color Selection Functionality
function selectColor(colorKey, colorName, element) {
    // Remove active class from all color dots
    document.querySelectorAll('.color-dot').forEach(dot => {
        dot.classList.remove('active');
    });
    
    // Add active class to selected color dot
    element.classList.add('active');
    
    // Update the selected color display
    const selectedColorName = document.getElementById('selected-color-name');
    if (selectedColorName) {
        selectedColorName.textContent = colorName;
        
        // Update the text color to match the selected color
        const colorValue = element.style.backgroundColor;
        if (colorValue) {
            // Set CSS custom property for gradient
            selectedColorName.style.setProperty('--text-color', colorValue);
            selectedColorName.classList.add('gradient-text');
            selectedColorName.setAttribute('data-color', colorValue);
        }
    }
    
    // Add underline effect to the selected color display
    const selectedColorDisplay = document.getElementById('selected-color-display');
    if (selectedColorDisplay) {
        selectedColorDisplay.classList.add('color-selected');
    }
    
    // Update product price when variant is selected
    const variationId = element.getAttribute('data-variation-id');
    if (variationId && window.variationData) {
        const variation = window.variationData[variationId];
        if (variation) {
            updateProductPrice(variation);
        }
    }
    
    console.log('Selected color:', colorName, 'Key:', colorKey);
}

// Function to update product price display
function updateProductPrice(variation) {
    const priceDisplay = document.getElementById('product-price-display');
    if (!priceDisplay || !variation) return;
    
    const isOnSale = variation.display_regular_price && variation.display_regular_price != variation.display_price;
    
    if (isOnSale) {
        priceDisplay.innerHTML = `
            <div class="price-sale">${variation.price_html}</div>
            <div class="price-regular">${variation.regular_price_html}</div>
        `;
    } else {
        priceDisplay.innerHTML = `
            <div class="price-current">${variation.price_html}</div>
        `;
    }
}

// Initialize with default color or first available color on page load
document.addEventListener('DOMContentLoaded', function() {
    let selectedColorDot = null;
    const availableColorDots = document.querySelectorAll('.color-dot:not(.out-of-stock)');
    
    // If there's only one color available, auto-select it
    if (availableColorDots.length === 1) {
        selectedColorDot = availableColorDots[0];
        console.log('Auto-selecting single available color');
    } else {
        // First, check if there's a default color set via form values
        const defaultColorInput = document.querySelector('input[name="attribute_pa_color"]:checked, input[name="attribute_color"]:checked');
        if (defaultColorInput) {
            const defaultColorValue = defaultColorInput.value;
            selectedColorDot = document.querySelector(`.color-dot[data-color="${defaultColorValue}"]:not(.out-of-stock)`);
        }
        
        // If no default color found, use the first available color
        if (!selectedColorDot) {
            selectedColorDot = document.querySelector('.color-dot:not(.out-of-stock)');
        }
    }
    
    if (selectedColorDot) {
        const colorName = selectedColorDot.getAttribute('data-color-name');
        const colorKey = selectedColorDot.getAttribute('data-color');
        selectColor(colorKey, colorName, selectedColorDot);
        
        // Add the selected class immediately for single color products
        if (availableColorDots.length === 1) {
            selectedColorDot.classList.add('active');
            // Update the selected color display immediately
            const selectedColorName = document.getElementById('selected-color-name');
            if (selectedColorName) {
                selectedColorName.textContent = colorName;
                
                // Apply the color to the text
                const colorValue = selectedColorDot.style.backgroundColor;
                if (colorValue) {
                    selectedColorName.style.setProperty('--text-color', colorValue);
                    selectedColorName.classList.add('gradient-text');
                    selectedColorName.setAttribute('data-color', colorValue);
                }
            }
            const selectedColorDisplay = document.getElementById('selected-color-display');
            if (selectedColorDisplay) {
                selectedColorDisplay.classList.add('color-selected');
            }
        }
        
        // Also load the price for the selected color
        const variationId = selectedColorDot.getAttribute('data-variation-id');
        if (variationId && window.variationData) {
            const variation = window.variationData[variationId];
            if (variation) {
                updateProductPrice(variation);
            }
        }
    }
});

</script>