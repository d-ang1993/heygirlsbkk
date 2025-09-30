@php
$new_arrival_enable = get_theme_mod('new_arrival_enable', true);
$new_arrival_title = get_theme_mod('new_arrival_title', 'NEW ARRIVAL');
$new_arrival_count = get_theme_mod('new_arrival_count', 4);
$new_arrival_columns = get_theme_mod('new_arrival_columns', 4);

// Get the most recently added products
$recent_products = wc_get_products([
    'limit' => $new_arrival_count,
    'orderby' => 'date',
    'order' => 'DESC',
    'status' => 'publish',
    'visibility' => 'visible',
]);
@endphp

@if($new_arrival_enable && !empty($recent_products))
<x-product-grid 
  title="{{ $new_arrival_title }}" 
  :products="$recent_products"
  :columns="$new_arrival_columns"
/>
@endif