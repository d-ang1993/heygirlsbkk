@extends('layouts.app')

@section('content')
  <div class="heygirlsbkk-cart-container">
    @php do_action('woocommerce_before_cart'); @endphp

    {{-- HeyGirlsBKK-style Header --}}
    <div class="cart-header">
      <div class="cart-header-left">
        <h1 class="cart-title">My Bag ({{ WC()->cart->get_cart_contents_count() }})</h1>
      </div>
      <div class="cart-header-right">
        <span class="cart-help">Need Help? +852 8009 06220</span>
      </div>
    </div>

    {{-- Empty Cart State --}}
    @if (WC()->cart->is_empty())
      <div class="cart-empty-state">
        <div class="empty-cart-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
        </div>
        <h2>Your bag is empty</h2>
        <p>Add some items to get started</p>
        <a href="{{ wc_get_page_permalink('shop') }}" class="btn btn-primary">
          Continue Shopping
        </a>
      </div>

    @else
      {{-- HeyGirlsBKK Cart Layout --}}
      <div class="cart-main-layout">
        
        {{-- Cart Items Section --}}
        <div class="cart-items-section">
          <form class="woocommerce-cart-form" action="{{ wc_get_cart_url() }}" method="post">
            @php do_action('woocommerce_before_cart_table'); @endphp
            
            <div class="cart-items-list">
              @foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
                @php
                  $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                  $product_permalink = $_product && $_product->is_visible() ? $_product->get_permalink($cart_item) : '';
                  $variation_data = wc_get_formatted_cart_item_data($cart_item, true);
                  
                  // Optimize image: use medium size for smaller cards with srcset
                  $image_id = $_product->get_image_id();
                  $image_url = wp_get_attachment_image_url($image_id, 'medium') ?: wc_placeholder_img_src('medium');
                  $image_srcset = wp_get_attachment_image_srcset($image_id, 'medium');
                  $image_sizes = '(max-width: 768px) 120px, 150px';
                @endphp

                @if ($_product && $_product->exists() && $cart_item['quantity'] > 0)
                  <div class="cart-page-item">
                    <div class="cart-page-item-image">
                      <img src="{{ $image_url }}" 
                           @if($image_srcset)
                             srcset="{{ $image_srcset }}"
                             sizes="{{ $image_sizes }}"
                           @endif
                           alt="{{ $_product->get_name() }}" 
                           loading="lazy"
                           decoding="async" />
                    </div>
                    
                    <div class="cart-page-item-details">
                      <div class="cart-page-item-info">
                        <h3 class="cart-page-item-name">
                          <a href="{{ esc_url($product_permalink) }}">
                            {{ $_product->get_name() }}
                          </a>
                        </h3>
                        @if($variation_data)
                          <div class="cart-page-item-variation">{!! $variation_data !!}</div>
                        @endif
                        
                      </div>
                      
                      <div class="cart-page-item-controls">
                        <div class="cart-page-quantity-controls">
                          <button type="button" class="cart-page-quantity-btn cart-page-quantity-minus" data-cart-item-key="{{ $cart_item_key }}">-</button>
                          <input type="number" 
                                 class="cart-page-quantity-input" 
                                 value="{{ $cart_item['quantity'] }}" 
                                 min="1" 
                                 data-cart-item-key="{{ $cart_item_key }}"
                                 name="cart[{{ $cart_item_key }}][qty]">
                          <button type="button" class="cart-page-quantity-btn cart-page-quantity-plus" data-cart-item-key="{{ $cart_item_key }}">+</button>
                        </div>
                        
                        <div class="cart-page-item-price">
                          {!! WC()->cart->get_product_subtotal($_product, $cart_item['quantity']) !!}
                        </div>
                      </div>
                    </div>
                    
                    <button type="button" 
                            class="cart-page-item-remove" 
                            data-cart-item-key="{{ $cart_item_key }}"
                            title="Remove item">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                @endif
              @endforeach
            </div>
            
            @php 
              do_action('woocommerce_cart_actions');
              wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce');
              do_action('woocommerce_after_cart_table');
            @endphp
          </form>
        </div>

        {{-- Order Summary Section --}}
        <div class="cart-summary-section">
          <div class="cart-summary-content">
            {{-- Order Totals --}}
            <div class="order-totals">
              <div class="total-line subtotal">
                <span class="total-label">Subtotal:</span>
                <span class="total-amount">{!! WC()->cart->get_cart_subtotal() !!}</span>
              </div>
              
              <div class="total-line shipping">
                <span class="total-label">Shipping:</span>
                <span class="total-amount">TBD</span>
              </div>
              
              <div class="total-line estimated-total">
                <span class="total-label">Estimated Total:</span>
                <span class="total-amount">{!! WC()->cart->get_cart_total() !!}</span>
              </div>
            </div>

            {{-- Checkout Button --}}
            <div class="checkout-section">
              <a href="{{ wc_get_checkout_url() }}" class="btn-checkout">
                CHECKOUT
              </a>
              <p class="checkout-disclaimer">
                By clicking "Checkout", you will be redirected to our secure checkout page where payment will be processed and your order will be fulfilled by HeyGirlsBKK.
              </p>
            </div>
          </div>
        </div>
      </div>
    @endif
    
    @php do_action('woocommerce_after_cart'); @endphp
  </div>

  {{-- HeyGirlsBKK Cart Styles --}}
  <style>
    .heygirlsbkk-cart-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      font-family: 'Helvetica Neue', Arial, sans-serif;
    }

    .heygirlsbkk-cart-container .cart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid var(--color-primary, #c4b5a8);
    }

    .heygirlsbkk-cart-container .cart-title {
      font-size: 24px;
      font-weight: 600;
      color: var(--color-primary-darker, #a89687);
      margin: 0;
    }

    .heygirlsbkk-cart-container .cart-help {
      color: var(--color-primary, #c4b5a8);
      font-size: 14px;
    }

    .heygirlsbkk-cart-container .cart-empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #6b7280;
    }

    .heygirlsbkk-cart-container .empty-cart-icon {
      margin-bottom: 20px;
      color: var(--color-primary, #c4b5a8);
    }

    .heygirlsbkk-cart-container .cart-empty-state h2 {
      font-size: 24px;
      font-weight: 600;
      color: var(--color-primary-darker, #a89687);
      margin: 0 0 10px 0;
    }

    .heygirlsbkk-cart-container .cart-empty-state p {
      margin: 0 0 30px 0;
      font-size: 16px;
    }

    .heygirlsbkk-cart-container .cart-main-layout {
      display: grid;
      grid-template-columns: 1fr 400px;
      gap: 40px;
      align-items: start;
    }

    .heygirlsbkk-cart-container .cart-items-section {
      background: #fff;
    }

    .heygirlsbkk-cart-container .cart-items-list {
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    /* Scoped to cart page only - won't affect other components */
    .heygirlsbkk-cart-container .cart-page-item {
      display: flex;
      gap: 1.5rem;
      padding: 1.5rem;
      border: 1px solid var(--color-primary, #c4b5a8);
      border-radius: 8px;
      background: #fff;
      position: relative;
      margin-bottom: 1.25rem;
    }

    .heygirlsbkk-cart-container .cart-page-item-image {
      width: 150px;
      height: 150px;
      border-radius: 6px;
      overflow: hidden;
      flex-shrink: 0;
    }

    .heygirlsbkk-cart-container .cart-page-item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .heygirlsbkk-cart-container .cart-page-item-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.625rem;
    }

    .heygirlsbkk-cart-container .cart-page-item-info {
      flex: 1;
    }

    .heygirlsbkk-cart-container .cart-page-item-name {
      margin: 0 0 0.5rem 0;
      font-size: 1rem;
      font-weight: 600;
      color: #1f2937;
    }

    .heygirlsbkk-cart-container .cart-page-item-name a {
      color: var(--color-primary-darker, #a89687);
      text-decoration: none;
    }

    .heygirlsbkk-cart-container .cart-page-item-name a:hover {
      color: var(--color-primary-dark, #b8a89a);
      text-decoration: underline;
    }

    .heygirlsbkk-cart-container .cart-page-item-variation {
      font-size: 0.875rem;
      color: #6b7280;
      margin-bottom: 0.625rem;
    }

    .heygirlsbkk-cart-container .cart-page-item-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .heygirlsbkk-cart-container .cart-page-quantity-controls {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .heygirlsbkk-cart-container .cart-page-quantity-btn {
      width: 28px; 
      height: 28px; 
      border: 1px solid var(--color-primary, #c4b5a8); 
      background: #fff; 
      cursor: pointer; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      font-size: 1rem; 
      font-weight: 500; 
      color: var(--color-primary-darker, #a89687);
      transition: all 0.2s ease;
      padding: 0;
      border-radius: 4px;
    }

    .heygirlsbkk-cart-container .cart-page-quantity-btn:hover { 
      background: var(--color-primary, #c4b5a8);
      border-color: var(--color-primary-dark, #b8a89a);
      color: #fff;
    }

    .heygirlsbkk-cart-container .cart-page-quantity-input {
      font-size: 0.875rem;
      font-weight: 500;
      color: #374151;
      font-family: sans-serif;
      min-width: 20px;
      text-align: center;
      border: none;
      background: none;
      padding: 0 0.25rem;
    }

    .heygirlsbkk-cart-container .cart-page-item-price {
      font-size: 1rem;
      font-weight: 600;
      color: #1f2937;
    }

    .heygirlsbkk-cart-container .cart-page-item-remove {
      position: absolute;
      top: 15px;
      right: 15px;
      background: none;
      border: none;
      cursor: pointer;
      color: var(--color-primary, #c4b5a8);
      padding: 8px;
      border-radius: 4px;
      transition: all 0.2s ease;
    }

    .heygirlsbkk-cart-container .cart-page-item-remove:hover {
      background: var(--color-primary, #c4b5a8);
      color: #fff;
    }

    .heygirlsbkk-cart-container .cart-summary-section {
      background: #fff;
      border: 2px solid var(--color-primary, #c4b5a8);
      border-radius: 8px;
      padding: 20px;
      position: sticky;
      top: 20px;
    }

    .heygirlsbkk-cart-container .order-totals {
      margin-bottom: 20px;
    }

    .heygirlsbkk-cart-container .total-line {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .heygirlsbkk-cart-container .total-label {
      font-size: 14px;
      color: #6b7280;
    }

    .heygirlsbkk-cart-container .total-amount {
      font-size: 14px;
      font-weight: 600;
      color: var(--color-primary-darker, #a89687);
    }

    .heygirlsbkk-cart-container .estimated-total {
      border-top: 2px solid var(--color-primary, #c4b5a8);
      padding-top: 10px;
      margin-top: 10px;
    }

    .estimated-total .total-label,
    .estimated-total .total-amount {
      font-size: 16px;
      font-weight: 600;
    }

    .heygirlsbkk-cart-container .checkout-section {
      text-align: center;
    }

    .heygirlsbkk-cart-container .btn-checkout {
      display: block;
      width: 100%;
      background: var(--color-primary-dark, #b8a89a);
      color: #fff;
      text-decoration: none;
      padding: 16px 24px;
      border-radius: 4px;
      font-size: 16px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: background 0.2s ease;
      margin-bottom: 15px;
      border: none;
    }

    .heygirlsbkk-cart-container .btn-checkout:hover {
      background: var(--color-primary-darker, #a89687);
      color: #fff;
    }

    .checkout-disclaimer {
      font-size: 12px;
      color: #6b7280;
      line-height: 1.4;
      margin: 0;
    }

    .btn {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 4px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.2s ease;
    }

    .heygirlsbkk-cart-container .btn-primary {
      background: var(--color-primary-dark, #b8a89a);
      color: #fff;
      border: none;
    }

    .heygirlsbkk-cart-container .btn-primary:hover {
      background: var(--color-primary-darker, #a89687);
      color: #fff;
    }

    @media (max-width: 768px) {
      .heygirlsbkk-cart-container .cart-main-layout {
        grid-template-columns: 1fr;
        gap: 1.25rem;
      }
      
      .heygirlsbkk-cart-container .cart-summary-section {
        position: static;
      }
      
      .heygirlsbkk-cart-container .cart-page-item {
        flex-direction: column;
        gap: 1rem;
      }
      
      .heygirlsbkk-cart-container .cart-page-item-image {
        width: 100%;
        height: 250px;
      }
    }
  </style>

  {{-- Cart JavaScript --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Quantity controls - unified function for both + and - buttons
      function updateQuantity(cartItemKey, newQuantity) {
        const quantityInput = document.querySelector(`input[data-cart-item-key="${cartItemKey}"]`);
        if (quantityInput) {
          quantityInput.value = newQuantity;
          
          // Create a hidden form to submit the update
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = '{{ wc_get_cart_url() }}';
          
          // Add nonce
          const nonceInput = document.createElement('input');
          nonceInput.type = 'hidden';
          nonceInput.name = 'woocommerce-cart-nonce';
          nonceInput.value = '{{ wp_create_nonce("woocommerce-cart") }}';
          form.appendChild(nonceInput);
          
          // Add cart item key and quantity
          const cartItemInput = document.createElement('input');
          cartItemInput.type = 'hidden';
          cartItemInput.name = `cart[${cartItemKey}][qty]`;
          cartItemInput.value = newQuantity;
          form.appendChild(cartItemInput);
          
          // Add update cart action
          const updateInput = document.createElement('input');
          updateInput.type = 'hidden';
          updateInput.name = 'update_cart';
          updateInput.value = 'Update Cart';
          form.appendChild(updateInput);
          
          // Submit the form
          document.body.appendChild(form);
          form.submit();
        }
      }

      // Plus button (scoped to cart page)
      document.querySelectorAll('.heygirlsbkk-cart-container .cart-page-quantity-plus').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const cartItemKey = this.dataset.cartItemKey;
          const quantityInput = document.querySelector(`.heygirlsbkk-cart-container input[data-cart-item-key="${cartItemKey}"]`);
          let currentQuantity = parseInt(quantityInput.value) || 1;
          let newQuantity = currentQuantity + 1;
          updateQuantity(cartItemKey, newQuantity);
        });
      });

      // Minus button (scoped to cart page)
      document.querySelectorAll('.heygirlsbkk-cart-container .cart-page-quantity-minus').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const cartItemKey = this.dataset.cartItemKey;
          const quantityInput = document.querySelector(`.heygirlsbkk-cart-container input[data-cart-item-key="${cartItemKey}"]`);
          let currentQuantity = parseInt(quantityInput.value) || 1;
          let newQuantity = Math.max(1, currentQuantity - 1);
          updateQuantity(cartItemKey, newQuantity);
        });
      });

      // Quantity input change (scoped to cart page)
      document.querySelectorAll('.heygirlsbkk-cart-container .cart-page-quantity-input').forEach(input => {
        input.addEventListener('change', function() {
          const cartItemKey = this.dataset.cartItemKey;
          let newQuantity = Math.max(1, parseInt(this.value) || 1);
          updateQuantity(cartItemKey, newQuantity);
        });
      });

      // Remove item buttons (scoped to cart page)
      document.querySelectorAll('.heygirlsbkk-cart-container .cart-page-item-remove').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const cartItemKey = this.dataset.cartItemKey;
          const removeUrl = '{{ wc_get_cart_url() }}?remove_item=' + cartItemKey + '&_wpnonce=' + '{{ wp_create_nonce("woocommerce-cart") }}';
          window.location.href = removeUrl;
        });
      });

    });
  </script>
@endsection
