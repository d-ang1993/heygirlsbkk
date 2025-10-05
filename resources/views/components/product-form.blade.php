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
        <form class="variations_form cart" method="post" enctype="multipart/form-data" data-product_id="{{ $product_id }}" data-product_variations="{{ json_encode($variations_data ?? []) }}">
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
          
          <button type="submit" class="btn btn-primary add-to-cart-btn" disabled>
            ADD TO CART
          </button>
        </form>
      @else
        <form class="cart" action="{{ $product->add_to_cart_url() }}" method="post" enctype="multipart/form-data">
          <button type="submit" name="add-to-cart" value="{{ $product_id }}" class="btn btn-primary add-to-cart-btn">
            ADD TO CART
          </button>
        </form>
      @endif
      
      {{-- TI WooCommerce Wishlist button --}}
      {!! do_shortcode('[ti_wishlists_addtowishlist product_id="' . $product_id . '" variation_id="0"]') !!}
    </div>
  @else
    <div class="product-unavailable">
      <p>This product is currently unavailable.</p>
    </div>
  @endif
</div>
