<!-- Product Details -->
<div class="product-details">
  
  <!-- Breadcrumbs -->
  <div class="product-breadcrumbs">
    @php
      $categories = wp_get_post_terms($product_id, 'product_cat');
      $category_names = [];
      foreach ($categories as $category) {
        $category_names[] = $category->name;
      }
    @endphp
    @if(!empty($category_names))
      {{ implode(' > ', $category_names) }}
    @endif
  </div>

  <!-- Product Title -->
  <h1 class="product-title">{{ $product_name }}</h1>

<!-- Product Price -->
<div class="product-price-section">
  <div id="product-price-display">
    @if($product_type === 'variable')
      <!-- Variable products: Show price range -->
      <div class="price-current">{!! $product->get_price_html() !!}</div>
    @elseif($product_type === 'simple')
      <!-- Simple products: Show price with sale logic -->
      @if($product_on_sale)
        <div class="price-wrapper">
          <div class="price-sale">{!! wc_price($product->get_sale_price()) !!}</div>
          <div class="price-regular">{!! wc_price($product->get_regular_price()) !!}</div>
        </div>
      @else
        <div class="price-current">{!! wc_price($product->get_price()) !!}</div>
      @endif
    @else
      <!-- Fallback for other product types (grouped, external, etc.) -->
      <div class="price-wrapper">
        <div class="price-current">{!! $product->get_price_html() !!}</div>
      </div>
    @endif
  </div>
</div>

  <!-- Product Short Description -->
  @if($product_short_description)
    <div class="product-short-description">
      {!! wpautop($product_short_description) !!}
      <a href="#" class="description-toggle">Read more</a>
    </div>
  @endif

  <!-- Product Variations -->
  @include('components.product-variations')

  <!-- Stock Status -->
  <div class="stock-status-section">
    <div id="stock-status-display">
      @php
        // Get the current product if not already available
        $current_product = $product ?? wc_get_product();
        if ($current_product) {
          $stock_status = $current_product->get_stock_status();
          $is_in_stock = $current_product->is_in_stock();
          $stock_quantity = $current_product->get_stock_quantity();
        } else {
          $stock_status = 'outofstock';
          $is_in_stock = false;
          $stock_quantity = 0;
        }
      @endphp
      
      @if($current_product && $current_product->is_type('variable'))
        @if($is_in_stock)
          <span class="stock-status in-stock">IN STOCK</span>
        @else
          <span class="stock-status out-of-stock">OUT OF STOCK</span>
        @endif
      @else
        @if($is_in_stock)
          <span class="stock-status in-stock">IN STOCK</span>
        @else
          <span class="stock-status out-of-stock">OUT OF STOCK</span>
        @endif
      @endif
    </div>
  </div>

  <!-- Product Form -->
      
  @include('components.product-form')

  <!-- Product Features -->
  <div class="product-features">
    <div class="feature-item">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="20,6 9,17 4,12"></polyline>
      </svg>
      <span>Free shipping over $50 USD</span>
    </div>
    <div class="feature-item">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="20,6 9,17 4,12"></polyline>
      </svg>
      <span>Easy payments</span>
    </div>
    <div class="feature-item">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="20,6 9,17 4,12"></polyline>
      </svg>
      <span>100% Secure Checkout</span>
    </div>
    <div class="feature-item">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="20,6 9,17 4,12"></polyline>
      </svg>
      <span>Free returns worldwide</span>
    </div>
  </div>

  <!-- Size Guide -->
  <div class="size-guide-section">
    <a href="#" class="size-guide-link">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
        <path d="M2 17l10 5 10-5"></path>
        <path d="M2 12l10 5 10-5"></path>
      </svg>
      Size Guide
    </a>
  </div>

  <!-- Estimated Delivery -->
  <div class="estimated-delivery">
    <span>Estimated delivery: 3 days</span>
  </div>

</div>