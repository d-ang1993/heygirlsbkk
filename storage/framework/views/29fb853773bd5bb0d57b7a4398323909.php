<?php
  // Get theme mods with fallbacks
  $heading = get_theme_mod('hero_react_heading', get_theme_mod('hero_heading', 'NEW ARRIVAL'));
  $subheading = get_theme_mod('hero_react_subheading', get_theme_mod('hero_subheading', ''));
  $ctaText = get_theme_mod('hero_react_cta_text', get_theme_mod('hero_cta_text', 'Shop Now'));
  $ctaUrl = esc_url(get_theme_mod('hero_react_cta_url', get_theme_mod('hero_cta_url', '/shop')));
  
  // Background settings
  $bgId = get_theme_mod('hero_react_bg_image', get_theme_mod('hero_bg_image'));
  $bgUrl = '';
  if ($bgId) {
    if (strpos($bgId, 'http') === 0) {
      $bgUrl = $bgId;
    } else {
      $bgUrl = wp_get_attachment_image_url($bgId, 'full') ?: wp_get_attachment_url($bgId);
    }
  }
  $bgColor = get_theme_mod('hero_react_bg_color', get_theme_mod('hero_bg_color', '#f8f9fa'));
  
  // Layout settings
  $alignment = get_theme_mod('hero_react_alignment', get_theme_mod('hero_align', 'center'));
  $height = get_theme_mod('hero_react_height', get_theme_mod('hero_height', '60vh'));
  $overlay = (float) get_theme_mod('hero_react_overlay', get_theme_mod('hero_overlay', 0.35));
  
  // Carousel settings
  $carouselEnabled = get_theme_mod('hero_react_carousel_enable', false);
  $carouselCategory = get_theme_mod('hero_react_product_category', get_theme_mod('hero_product_category', ''));
  $carouselCount = get_theme_mod('hero_react_carousel_count', get_theme_mod('hero_carousel_count', 6));
  $autoplay = get_theme_mod('hero_react_autoplay', true);
  $autoplaySpeed = get_theme_mod('hero_react_autoplay_speed', 5000);
  
  // Get images for carousel or grid
  $images = [];
  if ($carouselEnabled && $carouselCategory) {
    // Get products for carousel
    $category = get_term($carouselCategory, 'product_cat');
    if ($category && !is_wp_error($category)) {
      $products = wc_get_products([
        'limit' => $carouselCount,
        'category' => [$category->slug],
        'status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
      ]);
      
      foreach ($products as $product) {
        $imageUrl = wp_get_attachment_image_url($product->get_image_id(), 'full');
        if ($imageUrl) {
          $images[] = [
            'url' => $imageUrl,
            'alt' => $product->get_name(),
            'link' => $product->get_permalink()
          ];
        }
      }
    }
  } else {
    // Get products for image grid (not carousel)
    $heroProducts = wc_get_products([
      'limit' => 7,
      'status' => 'publish',
      'orderby' => 'date',
      'order' => 'DESC'
    ]);
    
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
?>


<script>
  window.heroReactData = {
    heading: <?php echo json_encode($heading, 15, 512) ?>,
    subheading: <?php echo json_encode($subheading, 15, 512) ?>,
    ctaText: <?php echo json_encode($ctaText, 15, 512) ?>,
    ctaUrl: <?php echo json_encode($ctaUrl, 15, 512) ?>,
    images: <?php echo json_encode($images, 15, 512) ?>,
    bgImage: <?php echo json_encode($bgUrl, 15, 512) ?>,
    bgColor: <?php echo json_encode($bgColor, 15, 512) ?>,
    alignment: <?php echo json_encode($alignment, 15, 512) ?>,
    height: <?php echo json_encode($height, 15, 512) ?>,
    overlay: <?php echo json_encode($overlay, 15, 512) ?>,
    carouselEnabled: <?php echo json_encode($carouselEnabled, 15, 512) ?>,
    autoplay: <?php echo json_encode($autoplay, 15, 512) ?>,
    autoplaySpeed: <?php echo json_encode($autoplaySpeed, 15, 512) ?>,
  };
  console.log('🔵 HeroReact Page Data loaded:', window.heroReactData);
</script>


<div id="hero-react-container"></div>


<?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/hero-react.jsx'); ?>


<script>
  (function() {
    console.log('🔵 HeroReact Blade: Script block executing...');
    
    // Verify data is available
    if (!window.heroReactData) {
      console.error('❌ HeroReact Blade: window.heroReactData is not defined!');
    } else {
      console.log('✅ HeroReact Blade: window.heroReactData is available');
      console.log('✅ HeroReact Blade: Data keys:', Object.keys(window.heroReactData));
    }
    
    // Wait for script to load and then check for mount function
    function waitForMountFunction(attempts = 0) {
      const maxAttempts = 100;
      
      if (typeof window.mountHeroReact === 'function') {
        console.log('✅ HeroReact Blade: mountHeroReact function is available');
        
        // Try manual mount if React hasn't mounted yet
        const mountPoint = document.getElementById('hero-react-container');
        if (mountPoint) {
          if (mountPoint.children.length === 0) {
            console.log('🔵 HeroReact Blade: Attempting manual mount...');
            try {
              window.mountHeroReact();
            } catch (error) {
              console.error('❌ HeroReact Blade: Error calling mountHeroReact:', error);
            }
          } else {
            console.log('✅ HeroReact Blade: Component already mounted');
          }
        }
        
        // Final check after delay
        setTimeout(function() {
          const mountPoint = document.getElementById('hero-react-container');
          if (mountPoint && mountPoint.children.length === 0) {
            console.error('❌ HeroReact Blade: React component did not mount after 3 seconds');
          } else if (mountPoint && mountPoint.children.length > 0) {
            console.log('✅ HeroReact Blade: React component appears to have mounted successfully');
          }
        }, 3000);
      } else if (attempts < maxAttempts) {
        setTimeout(() => waitForMountFunction(attempts + 1), 50);
      } else {
        console.error('❌ HeroReact Blade: mountHeroReact function not available after ' + maxAttempts + ' attempts');
      }
    }
    
    // Start waiting for mount function
    waitForMountFunction();
  })();
</script>

<?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/components/hero-react.blade.php ENDPATH**/ ?>