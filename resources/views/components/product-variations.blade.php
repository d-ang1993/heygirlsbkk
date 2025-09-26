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
  @endphp
  
  <!-- Color Variations -->
  @if(!empty($color_variations))
    <div class="color-selection">
      <label class="variation-label">Color</label>
      <div class="color-dots">
        @foreach($color_variations as $color_key => $variation)
          @php
            $variation_obj = wc_get_product($variation['variation_id']);
            $variation_in_stock = $variation_obj->is_in_stock();
            $color_name = str_replace(['-', '_'], ' ', $color_key);
            $color_name = ucwords($color_name);
            
            // Map color names to actual colors
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
              'burgundy' => '#800020'
            ];
            
            $dot_color = $color_map[strtolower($color_name)] ?? '#cccccc';
          @endphp
          <button class="color-dot {{ !$variation_in_stock ? 'out-of-stock' : '' }}" 
                  style="background-color: {{ $dot_color }};"
                  title="{{ $color_name }}"
                  data-color="{{ $color_key }}"
                  data-variation-id="{{ $variation['variation_id'] }}">
            @if(!$variation_in_stock)
              <span class="out-of-stock-x">×</span>
            @endif
          </button>
        @endforeach
      </div>
    </div>
  @endif
  
  <!-- Size Component -->
  @if(!empty($sizes))
    <div class="size-selection">
      <label class="variation-label">Size</label>
      <div class="size-buttons">
        @foreach($sizes as $size)
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