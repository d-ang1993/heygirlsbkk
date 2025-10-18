@extends('layouts.app')

@section('content')
  @php 
    // Remove WooCommerce default breadcrumbs and controls
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    do_action('woocommerce_before_main_content') 
  @endphp

  @if (woocommerce_product_loop())
    @php do_action('woocommerce_before_shop_loop') @endphp

    @php
      // Collect all products from the current query
      $archive_products = [];
      while (have_posts()) {
        the_post();
        global $product;
        if ($product && is_a($product, 'WC_Product')) {
          $archive_products[] = $product;
        }
      }
      wp_reset_postdata();
      
      // Get current query info
      global $wp_query;
      $total_products = $wp_query->found_posts;
    @endphp

    <!-- Custom Archive Header with Styled Elements -->
    <div class="archive-header">
      <div class="container">
        <!-- Breadcrumbs -->
        <nav class="woocommerce-breadcrumb" aria-label="Breadcrumb">
          <a href="{{ home_url() }}">Home</a>
          <span class="breadcrumb-separator">/</span>
          <a href="{{ wc_get_page_permalink('shop') }}">Shop</a>
          <span class="breadcrumb-separator">/</span>
          <a href="{{ wc_get_page_permalink('shop') }}/category/collections/">Collections</a>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-current">{{ woocommerce_page_title(false) }}</span>
        </nav>

        <!-- Results and Sorting -->
        <div class="archive-controls">
          <div class="woocommerce-result-count">
            Showing all {{ $total_products }} {{ $total_products === 1 ? 'result' : 'results' }}
          </div>
          
          <div class="woocommerce-ordering">
            <form method="get">
              <select name="orderby" class="orderby" aria-label="Shop order">
                <option value="menu_order" {{ selected('menu_order', $_GET['orderby'] ?? 'menu_order') }}>Default sorting</option>
                <option value="popularity" {{ selected('popularity', $_GET['orderby'] ?? '') }}>Sort by popularity</option>
                <option value="rating" {{ selected('rating', $_GET['orderby'] ?? '') }}>Sort by average rating</option>
                <option value="date" {{ selected('date', $_GET['orderby'] ?? '') }}>Sort by latest</option>
                <option value="price" {{ selected('price', $_GET['orderby'] ?? '') }}>Sort by price: low to high</option>
                <option value="price-desc" {{ selected('price-desc', $_GET['orderby'] ?? '') }}>Sort by price: high to low</option>
              </select>
              <input type="hidden" name="paged" value="1" />
              @foreach($_GET as $key => $value)
                @if($key !== 'orderby' && $key !== 'paged')
                  <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
                @endif
              @endforeach
            </form>
          </div>
        </div>
      </div>
    </div>

    @include('components.product-grid', [
      'title' => woocommerce_page_title(false),
      'products' => $archive_products,
      'columns' => 4,
      'showDiscount' => true,
      'showQuickView' => true
    ])

    @php do_action('woocommerce_after_shop_loop') @endphp
  @else
    @php do_action('woocommerce_no_products_found') @endphp
  @endif

  @php do_action('woocommerce_after_main_content') @endphp

  <!-- Archive Page JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-submit sorting form when selection changes
      const orderbySelect = document.querySelector('.orderby');
      if (orderbySelect) {
        orderbySelect.addEventListener('change', function() {
          this.form.submit();
        });
      }
    });
  </script>
@endsection
