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
  />

  <!-- Category Navigation -->
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
  </section>

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

<style>
/* Category Navigation */
.category-navigation {
  padding: 60px 0;
  background: #f8f9fa;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 30px;
}

.category-item {
  text-align: center;
}

.category-link {
  text-decoration: none;
  color: inherit;
  display: block;
  transition: transform 0.3s ease;
}

.category-link:hover {
  transform: translateY(-5px);
}

.category-image {
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 15px;
}

.category-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.category-link:hover .category-image img {
  transform: scale(1.05);
}

.category-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

/* Newsletter Section */
.newsletter-section {
  padding: 60px 0;
  background: #000;
  color: #fff;
}

.newsletter-content {
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
}

.newsletter-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 15px 0;
}

.newsletter-subtitle {
  font-size: 1.1rem;
  margin: 0 0 30px 0;
  opacity: 0.8;
}

.newsletter-form {
  display: flex;
  gap: 15px;
  max-width: 400px;
  margin: 0 auto;
}

.newsletter-input {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 25px;
  font-size: 1rem;
  outline: none;
}

.newsletter-button {
  padding: 12px 30px;
  background: #fff;
  color: #000;
  border: none;
  border-radius: 25px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.newsletter-button:hover {
  background: #f0f0f0;
  transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
  .category-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
  
  .newsletter-form {
    flex-direction: column;
  }
  
  .newsletter-title {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .category-grid {
    grid-template-columns: 1fr;
  }
}
</style>
