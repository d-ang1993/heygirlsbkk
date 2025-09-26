@php
  // Check if this is a product page
  $is_product = get_post_type() === 'product';
  
  if ($is_product) {
    global $product;
    $product_id = $product->get_id();
    $product_name = $product->get_name();
    $product_description = $product->get_description();
    $product_short_description = $product->get_short_description();
    $product_sku = $product->get_sku();
    $product_price = $product->get_price_html();
    $product_regular_price = $product->get_regular_price();
    $product_sale_price = $product->get_sale_price();
    $product_stock_status = $product->get_stock_status();
    $product_stock_quantity = $product->get_stock_quantity();
    $product_type = $product->get_type();
    $product_in_stock = $product->is_in_stock();
    $product_purchasable = $product->is_purchasable();
    $product_on_sale = $product->is_on_sale();
    $product_featured = $product->get_featured();
    
    // Get color information
    $color = '';
    if ($product->is_type('variable')) {
      $variations = $product->get_available_variations();
      if (!empty($variations)) {
        $first_variation = $variations[0];
        if (isset($first_variation['attributes']['attribute_pa_color'])) {
          $color = $first_variation['attributes']['attribute_pa_color'];
        } elseif (isset($first_variation['attributes']['attribute_color'])) {
          $color = $first_variation['attributes']['attribute_color'];
        }
      }
    } else {
      $attributes = $product->get_attributes();
      if (isset($attributes['pa_color'])) {
        $color = $attributes['pa_color']->get_options()[0] ?? '';
      } elseif (isset($attributes['color'])) {
        $color = $attributes['color']->get_options()[0] ?? '';
      }
    }

    // Get size information
    $sizes = [];
    if ($product->is_type('variable')) {
      $variations = $product->get_available_variations();
      if (!empty($variations)) {
        foreach ($variations as $variation) {
          // Check all attributes for size-related ones
          foreach ($variation['attributes'] as $attr_key => $attr_value) {
            if (stripos($attr_key, 'size') !== false && !empty($attr_value)) {
              if (!in_array($attr_value, $sizes)) {
                $sizes[] = $attr_value;
              }
            }
          }
        }
      }
      
      // If still no sizes found, try getting from variation attributes
      if (empty($sizes)) {
        $variation_attributes = $product->get_variation_attributes();
        foreach ($variation_attributes as $attribute_name => $options) {
          if (stripos($attribute_name, 'size') !== false) {
            $sizes = array_merge($sizes, $options);
          }
        }
        $sizes = array_unique($sizes);
      }
    } else {
      $attributes = $product->get_attributes();
      foreach ($attributes as $attribute_name => $attribute) {
        if (stripos($attribute_name, 'size') !== false) {
          if (method_exists($attribute, 'get_options')) {
            $sizes = array_merge($sizes, $attribute->get_options() ?? []);
          }
        }
      }
      $sizes = array_unique($sizes);
    }
    
    // Debug: Add some common sizes if none found (for testing)
    if (empty($sizes) && current_user_can('manage_options')) {
      $sizes = ['s', 'm', 'l', 'xl', 'xxl']; // Fallback for testing
    }

    // Get all variations data for JavaScript
    $variations_data = [];
    if ($product->is_type('variable')) {
      $variations = $product->get_available_variations();
      foreach ($variations as $variation) {
        $variation_obj = wc_get_product($variation['variation_id']);
        
        // Get color and size from all possible attribute formats
        $color = '';
        $size = '';
        
        foreach ($variation['attributes'] as $attr_key => $attr_value) {
          if (stripos($attr_key, 'color') !== false) {
            $color = $attr_value;
          }
          if (stripos($attr_key, 'size') !== false) {
            $size = $attr_value;
          }
        }
        
        $variations_data[] = [
          'id' => $variation['variation_id'],
          'color' => $color,
          'size' => $size,
          'price' => $variation_obj->get_price_html(),
          'regular_price' => $variation_obj->get_regular_price(),
          'sale_price' => $variation_obj->get_sale_price(),
          'in_stock' => $variation_obj->is_in_stock(),
          'stock_quantity' => $variation_obj->get_stock_quantity(),
          'image' => $variation['image']['src'] ?? ''
        ];
      }
    }
    
    // Clean up color name
    $color = str_replace(['-', '_'], ' ', $color);
    $color = ucwords($color);
    
    // Get product images
    $main_image = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_single');
    $gallery_images = [];
    foreach ($product->get_gallery_image_ids() as $image_id) {
      $gallery_images[] = wp_get_attachment_image_url($image_id, 'woocommerce_single');
    }
    
    // Get related products
    $related_products = wc_get_related_products($product_id, 4);
  }
@endphp