{{-- resources/views/woocommerce/content-product.blade.php --}}
@php
  global $product;
  
  // Ensure we have a valid product object
  if (!$product) {
    $product = wc_get_product(get_the_ID());
  }
  
  // If still no product and we have a post ID, try to get it directly
  if (!$product && get_the_ID() > 0) {
    $product = wc_get_product(get_the_ID());
  }
  
  // Skip if still no valid product
  if (!$product || !is_a($product, 'WC_Product')) {
    echo '<!-- No valid product found for ID: ' . get_the_ID() . ' -->';
    return;
  }
@endphp

<div class="product-card">
  <div class="product-image">
    <a href="{{ $product->get_permalink() }}">
      <img src="{{ wp_get_attachment_image_url($product->get_image_id(), 'product-grid') }}" 
           alt="{{ $product->get_name() }}" 
           loading="lazy"
           decoding="async"
           width="500"
           height="500" />
    </a>
    
    @if($product->is_on_sale())
      @php
        $regular_price_num = (float) $product->get_regular_price();
        $sale_price_num = (float) $product->get_sale_price();
        $discount_percentage = $regular_price_num > 0 ? round((($regular_price_num - $sale_price_num) / $regular_price_num) * 100) : 0;
      @endphp
      <div class="product-badge sale">
        {{ $discount_percentage }}% OFF
      </div>
    @endif
    
    @if($product->is_featured())
      <div class="product-badge featured fire-badge">BEST <span class="fire-emoji">🔥</span></div>
    @endif
  </div>
  
  <div class="product-info">
    @php
      // Get product brand (collection name)
      $terms = get_the_terms($product->get_id(), 'product_brand');
      $product_brand = $terms && !is_wp_error($terms) ? $terms[0]->name : '';
    @endphp
    
    @if($product_brand)
      <div class="product-brand">{{ $product_brand }}</div>
    @endif
    
    @php
      // Get available variations for variable products to display color dots
      $colors = [];
      if ($product->is_type('variable')) {
          $variations = $product->get_available_variations();
          foreach ($variations as $variation) {
              $variation_attributes = $variation['attributes'];
              $color_key = $variation_attributes['attribute_pa_color'] ?? $variation_attributes['attribute_color'] ?? '';
              if ($color_key && !in_array($color_key, $colors)) {
                  $colors[] = $color_key;
              }
          }
      }
    @endphp
    
    @if(!empty($colors))
      <div class="product-colors">
        @foreach($colors as $color)
          <span class="color-dot" style="background-color: {{ strtolower($color) }};" title="{{ $color }}"></span>
        @endforeach
      </div>
    @endif
    
    <h3 class="product-title">
      <a href="{{ $product->get_permalink() }}">{{ $product->get_name() }}</a>
    </h3>
    
    <div class="product-price">
      @if($product->is_on_sale())
        <span class="price-sale">{!! $product->get_price_html() !!}</span>
        <span class="price-regular">{!! wc_price($product->get_regular_price()) !!}</span>
      @else
        <span class="price-current">{!! $product->get_price_html() !!}</span>
      @endif
    </div>
  </div>
</div>
