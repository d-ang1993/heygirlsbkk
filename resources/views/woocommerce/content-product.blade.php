{{-- resources/views/woocommerce/content-product.blade.php --}}
@php
  global $product;
  $gallery = $product->get_gallery_image_ids();
  $primary = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'medium')[0] ?? '';
  $secondary = isset($gallery[0]) ? wp_get_attachment_image_url($gallery[0], 'medium') : $primary;
@endphp

<div class="group border p-3 hover:shadow-lg transition">
  <a href="{{ get_permalink() }}">
    <div class="relative overflow-hidden">
      <img 
        src="{{ $primary }}" 
        alt="{{ get_the_title() }}" 
        class="w-full h-auto transition-opacity duration-300 group-hover:opacity-0"
      >
      <img 
        src="{{ $secondary }}" 
        alt="{{ get_the_title() }}" 
        class="w-full h-auto absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
      >
    </div>
    <h2 class="mt-3 text-lg font-medium">{{ get_the_title() }}</h2>
    <span class="block text-red-600 font-bold">{!! $product->get_price_html() !!}</span>
  </a>
</div>
