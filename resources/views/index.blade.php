@extends('layouts.app')

@section('content')
@php
// Get the custom section order (removed footer since it's in the main layout)
$sectionOrder = get_theme_mod('homepage_section_order', 'hero,new_drops,featured_products,new_arrival');
$sections = explode(',', $sectionOrder);

// Define section mappings (removed footer)
$sectionMap = [
    'hero' => 'partials.hero',
    'new_drops' => 'partials.new-drops-carousel', 
    'featured_products' => 'partials.featured-products',
    'new_arrival' => 'partials.new-arrival'
];
@endphp

@foreach($sections as $section)
  @if(isset($sectionMap[$section]))
    @include($sectionMap[$section])
  @endif
@endforeach

  <!-- BEST SELLER Section -->
  @php
    $best_products = wc_get_products([
        'limit' => 8,
        'featured' => true,
        'status' => 'publish'
    ]);
  @endphp
  
  <x-product-grid 
    title="BEST SELLER" 
    :products="$best_products"
    :columns="4"
    viewAllUrl="{{ add_query_arg('orderby', 'popularity', wc_get_page_permalink('shop')) }}"
  />

  <!-- SALE Section -->
  @php
    $sale_products = wc_get_products([
        'limit' => 8,
        'on_sale' => true,
        'status' => 'publish'
    ]);
  @endphp
  
  <x-product-grid 
    title="SALE" 
    :products="$sale_products"
    :columns="4"
    viewAllUrl="{{ add_query_arg('on_sale', '1', wc_get_page_permalink('shop')) }}"
  />

  <!-- Category Navigation
  <section class="category-navigation">
    <div class="container">
      <div class="category-grid">
        <div class="category-item">
          <a href="#" class="category-link">
            <div class="category-image">
              <img src="/app/themes/heygirlsbkk/resources/images/category-tops.jpg" alt="상의" />
            </div>
            <h3 class="category-title">상의</h3>
          </a>
        </div>
        <div class="category-item">
          <a href="#" class="category-link">
            <div class="category-image">
              <img src="/app/themes/heygirlsbkk/resources/images/category-bottoms.jpg" alt="하의" />
            </div>
            <h3 class="category-title">하의</h3>
          </a>
        </div>
        <div class="category-item">
          <a href="#" class="category-link">
            <div class="category-image">
              <img src="/app/themes/heygirlsbkk/resources/images/category-outer.jpg" alt="아우터" />
            </div>
            <h3 class="category-title">아우터</h3>
          </a>
        </div>
        <div class="category-item">
          <a href="#" class="category-link">
            <div class="category-image">
              <img src="/app/themes/heygirlsbkk/resources/images/category-dress.jpg" alt="원피스" />
            </div>
            <h3 class="category-title">원피스</h3>
          </a>
        </div>
      </div>
    </div>
  </section> -->

  <!-- Newsletter Signup -->
  <section class="newsletter-section">
    <div class="container">
      <div class="newsletter-content">
        <h2 class="newsletter-title">Stay Updated</h2>
        <p class="newsletter-subtitle">Get the latest updates on new arrivals and exclusive offers</p>
        <form class="newsletter-form">
          <input type="email" placeholder="Enter your email address" class="newsletter-input" required>
          <button type="submit" class="newsletter-button">Subscribe</button>
        </form>
      </div>
    </div>
  </section>
@endsection
