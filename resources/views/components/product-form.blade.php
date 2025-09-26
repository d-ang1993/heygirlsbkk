<!-- Quantity and Add to Cart -->
<div class="product-form">
  @if($product_purchasable)
    <div class="quantity-selector">
      <label class="quantity-label">Quantity</label>
      <div class="quantity-controls">
        <button type="button" class="quantity-btn minus">-</button>
        <input type="number" name="quantity" value="1" min="1" max="{{ $product->get_max_purchase_quantity() }}" class="quantity-input" />
        <button type="button" class="quantity-btn plus">+</button>
      </div>
    </div>
    
    <div class="add-to-cart-section">
      @if($product_type === 'variable')
        <button type="button" class="btn btn-primary add-to-cart-btn" disabled>
          Select Options
        </button>
      @else
        <form class="cart" action="{{ $product->add_to_cart_url() }}" method="post" enctype="multipart/form-data">
          <button type="submit" name="add-to-cart" value="{{ $product_id }}" class="btn btn-primary add-to-cart-btn">
            ADD TO CART
          </button>
        </form>
      @endif
      
      <button type="button" class="wishlist-btn" title="Add to Wishlist">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
      </button>
    </div>
  @else
    <div class="product-unavailable">
      <p>This product is currently unavailable.</p>
    </div>
  @endif
</div>