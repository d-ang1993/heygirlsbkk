@php
$show     = get_theme_mod('hero_enable', true);
$heading  = get_theme_mod('hero_heading', 'NEW ARRIVAL');
$sub      = get_theme_mod('hero_subheading', '');
$ctaText  = get_theme_mod('hero_cta_text', 'Shop Now');
$ctaUrl   = esc_url(get_theme_mod('hero_cta_url', '/shop'));
$bgId     = get_theme_mod('hero_bg_image');
$bgUrl    = $bgId ? wp_get_attachment_image_url($bgId, 'full') : '';
$align    = get_theme_mod('hero_align', 'center');
$height   = get_theme_mod('hero_height', '60vh');
$overlay  = (float) get_theme_mod('hero_overlay', 0.35);

// Carousel settings
$carouselEnable = get_theme_mod('hero_carousel_enable', false);
$carouselCategory = get_theme_mod('hero_product_category', '');
$carouselCount = get_theme_mod('hero_carousel_count', 6);

// Get products for carousel
$carouselProducts = [];
if ($carouselEnable && $carouselCategory) {
    // Get category by ID
    $category = get_term($carouselCategory, 'product_cat');
    if ($category && !is_wp_error($category)) {
        $carouselProducts = wc_get_products([
            'limit' => $carouselCount,
            'category' => [$category->slug],
            'status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
    }
}
@endphp

@if($show)
<section class="hero relative overflow-hidden" style="--hero-height: {{ $height }};">
  @if($carouselEnable && !empty($carouselProducts))
    <!-- Product Carousel Background -->
    <div class="hero__carousel">
      @foreach($carouselProducts as $index => $product)
        <div class="hero__carousel-item {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
          <a href="{{ $product->get_permalink() }}" class="hero__carousel-link" style="z-index: 5;">
            <img src="{{ wp_get_attachment_image_url($product->get_image_id(), 'full') }}" 
                 alt="{!! $product->get_name() !!}" 
                 class="hero__carousel-image" />
          </a>
        </div>
      @endforeach
      
      <!-- Navigation Arrows -->
      @if(count($carouselProducts) > 1)
        <div class="hero__carousel-arrows">
          <button class="hero__carousel-arrow hero__carousel-arrow--prev" onclick="if(typeof changeSlide === 'function') changeSlide(-1);" style="z-index: 15;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="15,18 9,12 15,6"></polyline>
            </svg>
          </button>
          <button class="hero__carousel-arrow hero__carousel-arrow--next" onclick="if(typeof changeSlide === 'function') changeSlide(1);" style="z-index: 15;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="9,18 15,12 9,6"></polyline>
            </svg>
          </button>
        </div>
      @endif
    </div>
  @else
    <!-- Static Background -->
    <div class="hero__bg" style="background-image:url('{{ $bgUrl }}');"></div>
  @endif
  
  <div class="hero__overlay" style="--overlay-opacity: {{ $overlay }}; opacity: var(--overlay-opacity);"></div>

  <div class="container">
    <div class="hero__inner hero__inner--{{ $align }}">
      <h1 class="hero__title">{!! $heading !!}</h1>

      @if($sub)
        <p class="hero__subtitle">{!! $sub !!}</p>
      @endif

      @if($ctaText)
        <a href="{{ $ctaUrl }}" class="btn btn-primary hero__cta">{!! $ctaText !!}</a>
      @endif
    </div>
  </div>

  @if($carouselEnable && !empty($carouselProducts) && count($carouselProducts) > 1)
    <!-- Carousel Navigation -->
    <div class="hero__carousel-nav">
      @foreach($carouselProducts as $index => $product)
        <button class="hero__carousel-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}"></button>
      @endforeach
    </div>
  @endif


</section>

@endif