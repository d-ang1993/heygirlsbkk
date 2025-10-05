@php
$show     = get_theme_mod('hero_enable', true);
$heading  = get_theme_mod('hero_heading', 'NEW ARRIVAL');
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

// Background customization options
$bgPosition = get_theme_mod('hero_bg_position', 'center center');
$bgSize = get_theme_mod('hero_bg_size', 'cover');
$bgRepeat = get_theme_mod('hero_bg_repeat', 'no-repeat');
$bgColor = get_theme_mod('hero_bg_color', '#f8f9fa');
$bgAttachment = get_theme_mod('hero_bg_attachment', 'scroll');

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
      <div class="hero__bg" style="
        background-image: url('{{ $bgUrl }}');
        background-position: {{ $bgPosition }};
        background-size: {{ $bgSize }};
        background-repeat: {{ $bgRepeat }};
        background-attachment: {{ $bgAttachment }};
        background-color: {{ $bgColor }};
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        z-index: 1;
      "></div>
    @else
      <!-- Fallback background when no image is set -->
      <div class="hero__bg hero__bg--fallback" style="background-color: {{ $bgColor }};"></div>
    @endif
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