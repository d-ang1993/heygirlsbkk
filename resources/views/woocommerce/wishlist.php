@extends('layouts.app')

@section('content')
<div class="wishlist-page">
  <div class="container">
    <div class="wishlist-header">
      <h1 class="wishlist-title">My Wishlist</h1>
      <p class="wishlist-subtitle">Save your favorite items for later</p>
    </div>
    
    <div class="wishlist-content">
      @php
        echo do_shortcode('[ti_wishlistsview]');
      @endphp
    </div>
  </div>
</div>
@endsection
