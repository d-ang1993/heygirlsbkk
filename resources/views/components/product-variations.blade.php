<!-- Product Variations -->
@if($product_type === 'variable')
  @php
    $variations = $product->get_available_variations();
    $attributes = $product->get_variation_attributes();
    
    // Group variations by attribute
    $color_variations = [];
    $size_variations = [];
    
    foreach ($variations as $variation) {
      $variation_attributes = $variation['attributes'];
      
      // Check for color variations
      if (isset($variation_attributes['attribute_pa_color']) || isset($variation_attributes['attribute_color'])) {
        $color_key = $variation_attributes['attribute_pa_color'] ?? $variation_attributes['attribute_color'];
        if (!isset($color_variations[$color_key])) {
          $color_variations[$color_key] = $variation;
        }
      }
      
      // Check for size variations
      if (isset($variation_attributes['attribute_pa_size']) || isset($variation_attributes['attribute_size'])) {
        $size_key = $variation_attributes['attribute_pa_size'] ?? $variation_attributes['attribute_size'];
        if (!isset($size_variations[$size_key])) {
          $size_variations[$size_key] = $variation;
        }
      }
    }
    
    // Debug: Console log all variations and attributes
    echo '<script>';
    echo 'console.log("Product ID:", ' . $product->get_id() . ');';
    echo 'console.log("Product Type:", "' . $product_type . '");';
    echo 'console.log("All Variations:", ' . json_encode($variations) . ');';
    echo 'console.log("All Attributes:", ' . json_encode($attributes) . ');';
    echo 'console.log("Color Variations:", ' . json_encode($color_variations) . ');';
    echo 'console.log("Size Variations:", ' . json_encode($size_variations) . ');';
    echo 'console.log("Product is variable:", ' . ($product->is_type('variable') ? 'true' : 'false') . ');';
    echo 'console.log("Has attributes:", ' . ($product->has_attributes() ? 'true' : 'false') . ');';
    echo '</script>';
  @endphp
  
  <!-- Color Variations -->
  @if(!empty($color_variations))
    <div class="color-selection">
      <!-- <label class="variation-label">Color</label> -->
      <div class="selected-color-display" id="selected-color-display">
        <span class="selected-color-label">Color: <span id="selected-color-name">Select a color</span></span>
      </div>
      <div class="color-dots">
        @foreach($color_variations as $color_key => $variation)
          @php
            $color_name = str_replace(['-', '_'], ' ', $color_key);
            $color_name = ucwords($color_name);
            
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
            
            // Check if the color is already a hex code
            $dot_color = '#cccccc'; // Default fallback color
            
            if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color_key)) {
              // It's already a hex code, use it directly
              $dot_color = $color_key;
            } else {
              // Map friendly color names to hex codes
              $color_map = [
                'black' => '#000000',
                'white' => '#ffffff',
                'red' => '#ff0000',
                'blue' => '#0000ff',
                'green' => '#008000',
                'yellow' => '#ffff00',
                'pink' => '#ffc0cb',
                'purple' => '#800080',
                'orange' => '#ffa500',
                'brown' => '#a52a2a',
                'gray' => '#808080',
                'grey' => '#808080',
                'navy' => '#000080',
                'beige' => '#f5f5dc',
                'khaki' => '#f0e68c',
                'ivory' => '#fffff0',
                'cream' => '#fffdd0',
                'tan' => '#d2b48c',
                'maroon' => '#800000',
                'burgundy' => '#800020',
                'light blue' => '#add8e6',
                'dark blue' => '#00008b',
                'light green' => '#90ee90',
                'dark green' => '#006400',
                'light gray' => '#d3d3d3',
                'dark gray' => '#a9a9a9',
                'light grey' => '#d3d3d3',
                'dark grey' => '#a9a9a9',
                'gold' => '#ffd700',
                'silver' => '#c0c0c0',
                'bronze' => '#cd7f32',
                'copper' => '#b87333',
                'rose gold' => '#e8b4b8',
                'mint' => '#98fb98',
                'coral' => '#ff7f50',
                'turquoise' => '#40e0d0',
                'lavender' => '#e6e6fa',
                'sage' => '#9caf88',
                'olive' => '#808000',
                'forest' => '#228b22',
                'royal' => '#4169e1',
                'midnight' => '#191970',
                'charcoal' => '#36454f',
                'camel' => '#c19a6b',
                'taupe' => '#483c32',
                'mauve' => '#e0b0ff',
                'peach' => '#ffcba4',
                'salmon' => '#fa8072',
                'lime' => '#00ff00',
                'cyan' => '#00ffff',
                'magenta' => '#ff00ff',
                'indigo' => '#4b0082',
                'violet' => '#8a2be2',
                'teal' => '#008080',
                'aqua' => '#00ffff',
                'fuchsia' => '#ff00ff',
                'crimson' => '#dc143c',
                'scarlet' => '#ff2400',
                'emerald' => '#50c878',
                'jade' => '#00a86b',
                'ruby' => '#e0115f',
                'sapphire' => '#0f52ba',
                'amber' => '#ffbf00',
                'topaz' => '#ffc87c',
                'pearl' => '#f8f6f0',
                'platinum' => '#e5e4e2',
                'steel' => '#71797e',
                'gunmetal' => '#2a3439'
              ];
              
              $dot_color = $color_map[strtolower($color_name)] ?? '#cccccc';
            }
          @endphp
          <button class="color-dot {{ !$color_has_stock ? 'out-of-stock' : '' }}" 
                  style="background-color: {{ $dot_color }};"
                  title="{{ $color_name }}"
                  data-color="{{ $color_key }}"
                  data-color-name="{{ $color_name }}"
                  data-variation-id="{{ $variation['variation_id'] }}"
                  onclick="selectColor('{{ $color_key }}', '{{ $color_name }}', this)">
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
            $size_in_stock = true; // You can add stock checking logic here if needed
          @endphp
          <button class="size-button {{ !$size_in_stock ? 'out-of-stock' : '' }}"
                  data-size="{{ $size }}">
            {{ $size_name }}
          </button>
        @endforeach
      </div>
      <a href="#" class="clear-selection">Clear</a>
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
    
    if (selectedColorDot) {
        const colorName = selectedColorDot.getAttribute('data-color-name');
        const colorKey = selectedColorDot.getAttribute('data-color');
        selectColor(colorKey, colorName, selectedColorDot);
        
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