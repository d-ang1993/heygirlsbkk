@props([
    'heading' => null,
    'subheading' => null,
    'ctaText' => null,
    'ctaUrl' => null,
    'images' => []
])

@php
    // Get theme mods if props not provided
    $heading = $heading ?? get_theme_mod('hero_new_heading', 'Summer styles are finally here');
    $subheading = $subheading ?? get_theme_mod('hero_new_subheading', 'This year, our new summer collection will shelter you from the harsh elements of a world that doesn\'t care if you live or die.');
    $ctaText = $ctaText ?? get_theme_mod('hero_new_cta_text', 'Shop Collection');
    $ctaUrl = $ctaUrl ?? esc_url(get_theme_mod('hero_new_cta_url', '/shop'));
    
    // Get images - if not provided, get from products or use defaults
    if (empty($images)) {
        // Try to get products for images
        $heroProducts = wc_get_products([
            'limit' => 7,
            'status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        $images = [];
        foreach ($heroProducts as $product) {
            $imageUrl = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_single');
            if ($imageUrl) {
                $images[] = [
                    'url' => $imageUrl,
                    'alt' => $product->get_name(),
                    'link' => $product->get_permalink()
                ];
            }
        }
        
        // Fill with placeholders if needed
        while (count($images) < 7) {
            $images[] = [
                'url' => wc_placeholder_img_src('woocommerce_single'),
                'alt' => 'Product image',
                'link' => '#'
            ];
        }
    }
    
    // Organize images into 3 columns: [2, 3, 2]
    $columns = [
        array_slice($images, 0, 2),
        array_slice($images, 2, 3),
        array_slice($images, 5, 2)
    ];
@endphp

<!-- Hero section - New Image Grid Layout -->
<div class="hero-new">
  <div class="hero-new__container">
    <div class="hero-new__content">
      <div class="hero-new__text">
        <h1 class="hero-new__title">{{ $heading }}</h1>
        @if($subheading)
          <p class="hero-new__subtitle">{{ $subheading }}</p>
        @endif
        @if($ctaText)
          <a href="{{ $ctaUrl }}" class="hero-new__cta">{{ $ctaText }}</a>
        @endif
      </div>
      
      <div class="hero-new__action">
        <div class="hero-new__image-grid">
          <!-- Decorative image grid -->
          <div class="hero-new__grid-wrapper" aria-hidden="true">
            <div class="hero-new__grid-container">
              <div class="hero-new__grid">
                @foreach($columns as $colIndex => $column)
                  <div class="hero-new__grid-column">
                    @foreach($column as $image)
                      <div class="hero-new__grid-item">
                        <a href="{{ $image['link'] ?? '#' }}" class="hero-new__grid-link">
                          <img 
                            src="{{ $image['url'] }}" 
                            alt="{{ $image['alt'] ?? 'Product image' }}" 
                            class="hero-new__grid-image"
                            loading="{{ $colIndex === 0 && $loop->index === 0 ? 'eager' : 'lazy' }}"
                            decoding="async" />
                        </a>
                      </div>
                    @endforeach
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

