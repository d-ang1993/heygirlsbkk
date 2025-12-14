<!-- Quantity and Add to Cart -->
<div class="product-form">
  @if($product_purchasable)
    <div class="add-to-cart-section">
      <div class="quantity-selector">
        <div class="quantity-label">QUANTITY</div>
        <div class="quantity-controls">
          <button type="button" class="quantity-btn minus">-</button>
          <span class="quantity-value">1</span>
          <button type="button" class="quantity-btn plus">+</button>
        </div>
      </div>
      
      @if($product_type === 'variable')
        {{-- Dynamic form for variable products --}}
        <form class="custom-add-cart-form" method="post" enctype="multipart/form-data" data-product_id="{{ $product_id }}">
          @csrf
          {{-- Hidden inputs for variation data --}}
          <input type="hidden" name="add-to-cart" value="{{ $product_id }}">
          <input type="hidden" name="product_id" value="{{ $product_id }}">
          <input type="hidden" name="variation_id" value="0" id="variation_id">
          <input type="hidden" name="quantity" value="1" id="quantity">
          
          {{-- Dynamic variation inputs based on available attributes --}}
          @if(isset($variations_data) && !empty($variations_data))
            @php
              $available_attributes = [];
              foreach($variations_data as $variation) {
                foreach($variation as $key => $value) {
                  if(!in_array($key, ['id', 'price', 'regular_price', 'sale_price', 'image', 'price_html', 'in_stock', 'stock_quantity', 'variation_id']) && !empty($value)) {
                    $available_attributes[$key] = $value;
                  }
                }
              }
            @endphp
            
            @foreach($available_attributes as $attribute => $value)
              <input type="hidden" name="attribute_{{ $attribute }}" value="" id="attribute_{{ $attribute }}">
            @endforeach
          @endif
          
          <button type="button" class="btn btn-primary add-to-cart-btn" data-product-id="{{ $product_id }}" disabled>
            ADD TO CART
          </button>
        </form>
      @else
        <form class="custom-add-cart-form" action="{{ wc_get_cart_url() }}" method="post" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="add-to-cart" value="{{ $product_id }}">
          <input type="hidden" name="quantity" value="1" class="quantity-input">
          <button type="button" name="add-to-cart" value="{{ $product_id }}" class="btn btn-primary add-to-cart-btn" data-product_id="{{ $product_id }}">
            ADD TO CART
          </button>
        </form>
      @endif
      
      
      {{-- TI WooCommerce Wishlist button --}}
      @if($product_type === 'variable')
        {{-- For variable products, initially disabled - will be enabled by JS when variation is selected --}}
        {!! do_shortcode('[ti_wishlists_addtowishlist product_id="' . $product_id . '" variation_id="0"]') !!}
      @else
        {{-- For simple products, enabled immediately --}}
        {!! do_shortcode('[ti_wishlists_addtowishlist product_id="' . $product_id . '"]') !!}
      @endif
    </div>
  @else
    <div class="product-unavailable">
      <p>This product is currently unavailable.</p>
    </div>
  @endif
</div>

