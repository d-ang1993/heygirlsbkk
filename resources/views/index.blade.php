@extends('layouts.app')

@section('content')
  @include('partials.hero')

  <!-- Featured Products Section -->
  @include('partials.featured-products')

  <!-- New Arrival Section -->
  @include('partials.new-arrival')

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
