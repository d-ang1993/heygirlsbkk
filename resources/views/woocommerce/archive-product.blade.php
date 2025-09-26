{{-- resources/views/woocommerce/archive-product.blade.php --}}
@extends('layouts.app')

@section('content')
  <div class="container mx-auto px-4 py-10">
    @if (woocommerce_product_loop())
      <h1 class="text-3xl font-bold mb-6">
        {{ woocommerce_page_title(false) }}
      </h1>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @while (have_posts()) @php the_post() @endphp
          @include('woocommerce.content-product')
        @endwhile
      </div>

      {!! woocommerce_pagination() !!}
    @else
      {!! wc_get_template('loop/no-products-found.php') !!}
    @endif
  </div>
@endsection
