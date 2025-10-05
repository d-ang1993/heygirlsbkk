@extends('layouts.app')

@section('content')
  @php do_action('woocommerce_before_main_content') @endphp

  @if (woocommerce_product_loop())
    @php do_action('woocommerce_before_shop_loop') @endphp

    <div class="product-grid-section">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">{{ woocommerce_page_title(false) }}</h2>
        </div>
        <div class="product-grid product-grid--4-cols">
          @while (have_posts())
            @php the_post() @endphp
            @include('woocommerce.content-product')
          @endwhile
        </div>
      </div>
    </div>

    @php do_action('woocommerce_after_shop_loop') @endphp
  @else
    @php do_action('woocommerce_no_products_found') @endphp
  @endif

  @php do_action('woocommerce_after_main_content') @endphp
@endsection
