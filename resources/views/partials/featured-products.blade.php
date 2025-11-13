@php
$enable = get_theme_mod('featured_products_enable', false);
$title = get_theme_mod('featured_products_title', 'Featured Products');
$categoryId = get_theme_mod('featured_products_category', '');
$count = get_theme_mod('featured_products_count', 6);
$columns = get_theme_mod('featured_products_columns', 3);

// Get gradient settings for text
$gradientType = get_theme_mod('featured_products_gradient_type', 'none');
$gradientStart = get_theme_mod('featured_products_gradient_start', '#000000');
$gradientEnd = get_theme_mod('featured_products_gradient_end', '#666666');
$gradientDirection = get_theme_mod('featured_products_gradient_direction', 'to bottom');

// Build gradient CSS class name and custom CSS property
$gradientClass = '';
$gradientCSS = '';
if ($gradientType !== 'none' && $gradientStart && $gradientEnd) {
    $gradientClass = 'has-text-gradient';
    if ($gradientType === 'linear') {
        $gradientCSS = "linear-gradient({$gradientDirection}, {$gradientStart}, {$gradientEnd})";
    } elseif ($gradientType === 'radial') {
        $gradientCSS = "radial-gradient(circle, {$gradientStart}, {$gradientEnd})";
    }
}

// Get products for featured section
$featuredProducts = [];
if ($enable && $categoryId) {
    // Get category by ID
    $category = get_term($categoryId, 'product_cat');
    if ($category && !is_wp_error($category)) {
        $featuredProducts = wc_get_products([
            'limit' => $count,
            'category' => [$category->slug],
            'status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
    }
}
@endphp

@if($enable && !empty($featuredProducts))
@php
  // Generate the collection URL
  $collectionUrl = '#';
  if ($categoryId) {
    $category = get_term($categoryId, 'product_cat');
    if ($category && !is_wp_error($category)) {
      $collectionUrl = get_term_link($category);
    }
  }
@endphp
<div class="featured-products-section">
  <x-product-grid 
    title="{{ $title }}" 
    :products="$featuredProducts"
    :columns="$columns"
    :viewAllUrl="$collectionUrl"
    :gradientClass="$gradientClass"
    :gradientCSS="$gradientCSS"
  />
</div>
@else
<!-- Debug info for Featured Products -->
@if(current_user_can('manage_options'))
<div style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
  <h3>Featured Products Debug Info:</h3>
  <p><strong>Enabled:</strong> {{ $enable ? 'Yes' : 'No' }}</p>
  <p><strong>Category ID:</strong> {{ $categoryId ?: 'Not set' }}</p>
  <p><strong>Products found:</strong> {{ count($featuredProducts) }}</p>
  <p><strong>Title:</strong> {{ $title }}</p>
  @if($categoryId)
    @php $category = get_term($categoryId, 'product_cat'); @endphp
    <p><strong>Category:</strong> {{ $category && !is_wp_error($category) ? $category->name : 'Category not found' }}</p>
  @endif
</div>
@endif
@endif