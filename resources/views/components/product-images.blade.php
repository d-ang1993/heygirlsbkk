<!-- Product Images -->
<div class="product-images">
  <div class="main-image">
    @if($main_image)
      <img src="{{ $main_image }}" alt="{{ $product_name }}" class="product-main-image" />
    @else
      <img src="{{ wc_placeholder_img_src('woocommerce_single') }}" alt="{{ $product_name }}" class="product-main-image" />
    @endif
    <div class="image-zoom">+</div>
  </div>
</div>