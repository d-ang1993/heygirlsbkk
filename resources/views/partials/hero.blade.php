@php
$show     = get_theme_mod('hero_enable', true);
$headingRaw = get_theme_mod('hero_heading', 'NEW ARRIVAL');
$lineBreakMode = get_theme_mod('hero_heading_line_breaks', 'manual');

// Process heading based on line break mode
$heading = $headingRaw;
if ($lineBreakMode === 'each-word') {
    // Split by spaces and wrap each word in a span with display block
    $words = explode(' ', $headingRaw);
    $heading = '';
    foreach ($words as $index => $word) {
        if ($index > 0) {
            $heading .= '<br>';
        }
        $heading .= esc_html($word);
    }
} elseif ($lineBreakMode === 'preserve-spaces') {
    // Convert line breaks to <br> tags
    $heading = nl2br(esc_html($headingRaw));
} else {
    // Manual mode - allow HTML but escape if needed
    $heading = $headingRaw;
}

$sub      = get_theme_mod('hero_subheading', '');
$ctaText  = get_theme_mod('hero_cta_text', 'Shop Now');
$ctaUrl   = esc_url(get_theme_mod('hero_cta_url', '/shop'));
$bgId     = get_theme_mod('hero_bg_image');
$bgUrl    = '';


if ($bgId) {
    // Check if $bgId is already a URL (starts with http)
    if (strpos($bgId, 'http') === 0) {
        // It's already a URL, use it directly
        $bgUrl = $bgId;
    } else {
        // It's an attachment ID, get the URL
        $bgUrl = wp_get_attachment_image_url($bgId, 'full');
        
        // If wp_get_attachment_image_url fails, try alternative methods
        if (!$bgUrl) {
            $bgUrl = wp_get_attachment_url($bgId);
        }
        
        // If still no URL, try getting the attachment directly
        if (!$bgUrl) {
            $attachment = get_post($bgId);
            if ($attachment && $attachment->post_type === 'attachment') {
                $bgUrl = wp_get_attachment_url($bgId);
            }
        }
    }
}

// Background customization options - using fixed defaults
$bgColor = get_theme_mod('hero_bg_color', '#f8f9fa');
$bgAttachment = get_theme_mod('hero_bg_attachment', 'scroll');
$bgPosition = get_theme_mod('hero_bg_position', 'center center');
$bgPositionMobile = get_theme_mod('hero_bg_position_mobile', 'center center');

// Grid layout settings
$gridColumn = get_theme_mod('hero_grid_column', 'left');
$gridVertical = get_theme_mod('hero_grid_vertical', 'center');
$gridHorizontal = get_theme_mod('hero_grid_horizontal', 'center');
$headingFontFamily = get_theme_mod('hero_heading_font_family', 'Hiragino Sans GB');
$subheadingFontFamily = get_theme_mod('hero_subheading_font_family', 'Hiragino Sans GB');
$headingFontSize = get_theme_mod('hero_heading_font_size', 'clamp(32px, 6vw, 64px)');
$headingFontWeight = get_theme_mod('hero_heading_font_weight', '800');
$subheadingFontSize = get_theme_mod('hero_subheading_font_size', 'clamp(16px, 2.2vw, 20px)');
$subheadingFontWeight = get_theme_mod('hero_subheading_font_weight', '400');
$ctaFontFamily = get_theme_mod('hero_cta_font_family', 'Hiragino Sans GB');
$ctaFontSize = get_theme_mod('hero_cta_font_size', '1rem');
$ctaFontWeight = get_theme_mod('hero_cta_font_weight', '600');
$ctaColor = get_theme_mod('hero_cta_color', '#c4b5a8');

// Legacy alignment (for backward compatibility)
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
                 class="hero__carousel-image"
                 loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                 fetchpriority="{{ $index === 0 ? 'high' : 'auto' }}"
                 decoding="async" />
          </a>
        </div>
      @endforeach
      
      <!-- Navigation Arrows -->
      @if(count($carouselProducts) > 1)
        <div class="hero__carousel-arrows">
          <button class="hero__carousel-arrow hero__carousel-arrow--prev" onclick="changeHeroSlide(-1, event)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="15,18 9,12 15,6"></polyline>
            </svg>
          </button>
          <button class="hero__carousel-arrow hero__carousel-arrow--next" onclick="changeHeroSlide(1, event)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="9,18 15,12 9,6"></polyline>
            </svg>
          </button>
        </div>
      @endif
    </div>
  @else
    <!-- Static Background -->
    @if(!empty($bgUrl) && $bgUrl !== '')
      <div class="hero__bg" 
           style="background-image: url('{{ esc_url($bgUrl) }}'); 
                  background-attachment: {{ esc_attr($bgAttachment) }}; 
                  background-color: {{ esc_attr($bgColor) }}; 
                  background-position: {{ esc_attr($bgPosition) }};
                  --hero-bg-position-mobile: {{ esc_attr($bgPositionMobile) }};"></div>
    @else
      <!-- Fallback background when no image is set -->
      <div class="hero__bg hero__bg--fallback" style="background-color: {{ esc_attr($bgColor) }};"></div>
    @endif
  @endif
  
  <div class="hero__overlay" style="--overlay-opacity: {{ $overlay }}; opacity: var(--overlay-opacity);"></div>

  <div class="container">
    <div class="hero__grid hero__grid--column-{{ $gridColumn }}">
      <div class="hero__grid-left hero__grid--vertical-{{ $gridVertical }} hero__grid--horizontal-{{ $gridHorizontal }}">
        @if($gridColumn === 'left')
          <div class="hero__inner hero__inner--grid">
            @php
              // Process heading font
              $headingFont = str_replace(['Noto Serif Display Italic', 'Noto Serif Display Condensed'], 'Noto Serif Display', $headingFontFamily);
              $headingStyle = '';
              if ($headingFontFamily === 'Noto Serif Display Condensed') {
                $headingStyle = "font-family: 'Noto Serif Display', serif; font-stretch: 75%;";
              } elseif ($headingFontFamily === 'Noto Serif Display Italic') {
                $headingStyle = "font-family: 'Noto Serif Display', serif; font-style: italic;";
              } elseif ($headingFontFamily === 'Noto Serif Display') {
                $headingStyle = "font-family: 'Noto Serif Display', serif;";
              } else {
                $headingStyle = "font-family: '{$headingFont}', var(--font-family-primary);";
              }
              
              // Process subheading font
              $subheadingFont = str_replace(['Noto Serif Display Italic', 'Noto Serif Display Condensed'], 'Noto Serif Display', $subheadingFontFamily);
              $subheadingStyle = '';
              if ($subheadingFontFamily === 'Noto Serif Display Condensed') {
                $subheadingStyle = "font-family: 'Noto Serif Display', serif; font-stretch: 75%;";
              } elseif ($subheadingFontFamily === 'Noto Serif Display Italic') {
                $subheadingStyle = "font-family: 'Noto Serif Display', serif; font-style: italic;";
              } elseif ($subheadingFontFamily === 'Noto Serif Display') {
                $subheadingStyle = "font-family: 'Noto Serif Display', serif;";
              } else {
                $subheadingStyle = "font-family: '{$subheadingFont}', var(--font-family-primary);";
              }
              
              // Process button font
              $ctaFont = str_replace(['Noto Serif Display Italic', 'Noto Serif Display Condensed'], 'Noto Serif Display', $ctaFontFamily);
              $ctaFontStyle = '';
              if ($ctaFontFamily === 'Noto Serif Display Condensed') {
                $ctaFontStyle = "font-family: 'Noto Serif Display', serif; font-stretch: 75%;";
              } elseif ($ctaFontFamily === 'Noto Serif Display Italic') {
                $ctaFontStyle = "font-family: 'Noto Serif Display', serif; font-style: italic;";
              } elseif ($ctaFontFamily === 'Noto Serif Display') {
                $ctaFontStyle = "font-family: 'Noto Serif Display', serif;";
              } else {
                $ctaFontStyle = "font-family: '{$ctaFont}', var(--font-family-primary);";
              }
            @endphp
            <h1 class="hero__title" style="{{ $headingStyle }}; font-size: {{ $headingFontSize }}; font-weight: {{ $headingFontWeight }};">{!! $heading !!}</h1>
            @if($sub)
              <p class="hero__subtitle" style="{{ $subheadingStyle }}; font-size: {{ $subheadingFontSize }}; font-weight: {{ $subheadingFontWeight }};">{!! $sub !!}</p>
            @endif
            @if($ctaText)
              <a href="{{ $ctaUrl }}" class="btn btn-primary hero__cta" style="{{ $ctaFontStyle }}; font-size: {{ $ctaFontSize }}; font-weight: {{ $ctaFontWeight }}; background-color: {{ $ctaColor }};">{!! $ctaText !!}</a>
            @endif
          </div>
        @endif
      </div>
      <div class="hero__grid-right hero__grid--vertical-{{ $gridVertical }} hero__grid--horizontal-{{ $gridHorizontal }}">
        @if($gridColumn === 'right')
          <div class="hero__inner hero__inner--grid">
            @php
              // Process heading font
              $headingFont = str_replace(['Noto Serif Display Italic', 'Noto Serif Display Condensed'], 'Noto Serif Display', $headingFontFamily);
              $headingStyle = '';
              if ($headingFontFamily === 'Noto Serif Display Condensed') {
                $headingStyle = "font-family: 'Noto Serif Display', serif; font-stretch: 75%;";
              } elseif ($headingFontFamily === 'Noto Serif Display Italic') {
                $headingStyle = "font-family: 'Noto Serif Display', serif; font-style: italic;";
              } elseif ($headingFontFamily === 'Noto Serif Display') {
                $headingStyle = "font-family: 'Noto Serif Display', serif;";
              } else {
                $headingStyle = "font-family: '{$headingFont}', var(--font-family-primary);";
              }
              
              // Process subheading font
              $subheadingFont = str_replace(['Noto Serif Display Italic', 'Noto Serif Display Condensed'], 'Noto Serif Display', $subheadingFontFamily);
              $subheadingStyle = '';
              if ($subheadingFontFamily === 'Noto Serif Display Condensed') {
                $subheadingStyle = "font-family: 'Noto Serif Display', serif; font-stretch: 75%;";
              } elseif ($subheadingFontFamily === 'Noto Serif Display Italic') {
                $subheadingStyle = "font-family: 'Noto Serif Display', serif; font-style: italic;";
              } elseif ($subheadingFontFamily === 'Noto Serif Display') {
                $subheadingStyle = "font-family: 'Noto Serif Display', serif;";
              } else {
                $subheadingStyle = "font-family: '{$subheadingFont}', var(--font-family-primary);";
              }
              
              // Process button font
              $ctaFont = str_replace(['Noto Serif Display Italic', 'Noto Serif Display Condensed'], 'Noto Serif Display', $ctaFontFamily);
              $ctaFontStyle = '';
              if ($ctaFontFamily === 'Noto Serif Display Condensed') {
                $ctaFontStyle = "font-family: 'Noto Serif Display', serif; font-stretch: 75%;";
              } elseif ($ctaFontFamily === 'Noto Serif Display Italic') {
                $ctaFontStyle = "font-family: 'Noto Serif Display', serif; font-style: italic;";
              } elseif ($ctaFontFamily === 'Noto Serif Display') {
                $ctaFontStyle = "font-family: 'Noto Serif Display', serif;";
              } else {
                $ctaFontStyle = "font-family: '{$ctaFont}', var(--font-family-primary);";
              }
            @endphp
            <h1 class="hero__title" style="{{ $headingStyle }}; font-size: {{ $headingFontSize }}; font-weight: {{ $headingFontWeight }};">{!! $heading !!}</h1>
            @if($sub)
              <p class="hero__subtitle" style="{{ $subheadingStyle }}; font-size: {{ $subheadingFontSize }}; font-weight: {{ $subheadingFontWeight }};">{!! $sub !!}</p>
            @endif
            @if($ctaText)
              <a href="{{ $ctaUrl }}" class="btn btn-primary hero__cta" style="{{ $ctaFontStyle }}; font-size: {{ $ctaFontSize }}; font-weight: {{ $ctaFontWeight }}; background-color: {{ $ctaColor }};">{!! $ctaText !!}</a>
            @endif
          </div>
        @endif
      </div>
    </div>
  </div>

  @if($carouselEnable && !empty($carouselProducts) && count($carouselProducts) > 1)
    <!-- Carousel Navigation -->
    <div class="hero__carousel-nav">
      @foreach($carouselProducts as $index => $product)
        <button class="hero__carousel-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" onclick="selectHeroSlide({{ $index }})"></button>
      @endforeach
    </div>
  @endif

  @if($carouselEnable && !empty($carouselProducts) && count($carouselProducts) > 1)
    <!-- Carousel JavaScript -->
    <script>
    // Hero Carousel - Enhanced functionality from hero-banner.blade.php
    let currentHeroSlide = 0;
    const totalHeroSlides = {{ count($carouselProducts) }};
    let heroAutoPlayInterval;

    // Enhanced showSlide function (adapted from hero-banner.blade.php)
    function showHeroSlide(n) {
        if (n > totalHeroSlides) { currentHeroSlide = 0; }
        if (n < 0) { currentHeroSlide = totalHeroSlides - 1; }
        
        updateHeroSlide();
    }
    function changeHeroSlide(direction, event = null) {
    console.log('changeHeroSlide called with direction:', direction);
    
    if (event && event.target) {
        const button = event.target.closest('.hero__carousel-arrow');
        if (button) {
            button.style.background = '#ff0000';
            setTimeout(() => {
                button.style.background = '';
            }, 200);
        }
    }

    if (totalHeroSlides <= 1) return;

    currentHeroSlide += direction;
    if (currentHeroSlide >= totalHeroSlides) currentHeroSlide = 0;
    if (currentHeroSlide < 0) currentHeroSlide = totalHeroSlides - 1;

    updateHeroSlide();
}


    // Enhanced currentSlide function (adapted from hero-banner.blade.php)
    function goToHeroSlide(n) {
        if (n >= 0 && n < totalHeroSlides) {
            currentHeroSlide = n;
            updateHeroSlide();
        }
    }

    function selectHeroSlide(index) {
        goToHeroSlide(index);
    }

    function updateHeroSlide() {
        console.log('updateHeroSlide called, currentHeroSlide:', currentHeroSlide);
        
        // Hide all slides
        const carouselItems = document.querySelectorAll('.hero__carousel-item');
        console.log('Found carousel items:', carouselItems.length);
        
        carouselItems.forEach((item, index) => {
            item.classList.remove('active');
            console.log(`Item ${index} classes:`, item.className);
        });
        
        // Show current slide
        if (carouselItems[currentHeroSlide]) {
            carouselItems[currentHeroSlide].classList.add('active');
            console.log(`Activated slide ${currentHeroSlide}`);
        }
        
        // Update dots
        const dots = document.querySelectorAll('.hero__carousel-dot');
        console.log('Found dots:', dots.length);
        
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentHeroSlide);
        });
    }

    function startHeroAutoPlay() {
        if (heroAutoPlayInterval) {
            clearInterval(heroAutoPlayInterval);
        }
        heroAutoPlayInterval = setInterval(() => changeHeroSlide(1), 5000);
    }
    
    function stopHeroAutoPlay() {
        if (heroAutoPlayInterval) {
            clearInterval(heroAutoPlayInterval);
        }
    }

    // Initialize carousel functionality
    function initHeroCarousel() {
        console.log('Initializing hero carousel...');
        console.log('Total slides:', totalHeroSlides);
        
        // Add click event listeners to dots (enhanced from hero-banner.blade.php)
        const dots = document.querySelectorAll('.hero__carousel-dot');
        dots.forEach(dot => {
            dot.addEventListener('click', function(e) {
                e.preventDefault();
                const slideNum = parseInt(this.getAttribute('data-slide'));
                console.log('Dot clicked, slide number:', slideNum);
                if (slideNum >= 0 && slideNum < totalHeroSlides) {
                    currentHeroSlide = slideNum;
                    updateHeroSlide();
                }
            });
        });

        // Set up hover functionality
        const carousel = document.querySelector('.hero__carousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', stopHeroAutoPlay);
            carousel.addEventListener('mouseleave', startHeroAutoPlay);
        }

        // Start auto-play if multiple slides
        if (totalHeroSlides > 1) {
            startHeroAutoPlay();
        }
    }

    // Test functions are available
    console.log('Hero carousel functions loaded');
    console.log('changeHeroSlide function:', typeof changeHeroSlide);
    console.log('selectHeroSlide function:', typeof selectHeroSlide);
    console.log('goToHeroSlide function:', typeof goToHeroSlide);
    
    // Make functions globally available for onclick handlers
    window.changeHeroSlide = changeHeroSlide;
    window.selectHeroSlide = selectHeroSlide;
    window.goToHeroSlide = goToHeroSlide;

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up hero carousel');
        setTimeout(initHeroCarousel, 100); // Small delay to ensure elements are rendered
    });

    // Also initialize if DOM is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroCarousel);
    } else {
        setTimeout(initHeroCarousel, 100);
    }
    </script>
  @endif
</section>

@endif
