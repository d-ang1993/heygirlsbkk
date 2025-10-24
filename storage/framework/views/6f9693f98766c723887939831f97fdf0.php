<?php $__env->startSection('content'); ?>
<?php
// Get the custom section order (removed footer since it's in the main layout)
$sectionOrder = get_theme_mod('homepage_section_order', 'hero,new_drops,featured_products,new_arrival');
$sections = explode(',', $sectionOrder);

// Define section mappings (removed footer)
$sectionMap = [
    'hero' => 'partials.hero',
    'new_drops' => 'partials.new-drops-carousel', 
    'featured_products' => 'partials.featured-products',
    'new_arrival' => 'partials.new-arrival'
];
?>

<?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php if(isset($sectionMap[$section])): ?>
    <?php if($section === 'hero'): ?>
      <?php echo $__env->make($sectionMap[$section], [
        'show' => get_theme_mod('hero_enable', true),
        'heading' => get_theme_mod('hero_heading', 'NEW ARRIVAL'),
        'subheading' => get_theme_mod('hero_subheading', 'Korean/Japanese fashion drops • 24h launch'),
        'ctaText' => get_theme_mod('hero_cta_text', 'Shop Now'),
        'ctaUrl' => get_theme_mod('hero_cta_url', '/shop'),
        'bgImage' => get_theme_mod('hero_bg_image'),
        'bgPosition' => get_theme_mod('hero_bg_position', 'center center'),
        'bgSize' => get_theme_mod('hero_bg_size', 'cover'),
        'bgRepeat' => get_theme_mod('hero_bg_repeat', 'no-repeat'),
        'bgColor' => get_theme_mod('hero_bg_color', '#f8f9fa'),
        'bgAttachment' => get_theme_mod('hero_bg_attachment', 'scroll'),
        'align' => get_theme_mod('hero_align', 'center'),
        'height' => get_theme_mod('hero_height', '60vh'),
        'overlay' => get_theme_mod('hero_overlay', 0.35),
        'carouselEnable' => get_theme_mod('hero_carousel_enable', false),
        'carouselProducts' => []
      ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
      <?php echo $__env->make($sectionMap[$section], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
  <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

  <!-- BEST SELLER Section -->
  <?php
    $best_products = wc_get_products([
        'limit' => 8,
        'featured' => true,
        'status' => 'publish'
    ]);
  ?>
  
  <?php if (isset($component)) { $__componentOriginala87666a6c7dfd023b4375fc379bf394c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala87666a6c7dfd023b4375fc379bf394c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product-grid','data' => ['title' => 'BEST SELLER','products' => $best_products,'columns' => 4,'viewAllUrl' => ''.e(add_query_arg('orderby', 'popularity', wc_get_page_permalink('shop'))).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product-grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'BEST SELLER','products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($best_products),'columns' => 4,'viewAllUrl' => ''.e(add_query_arg('orderby', 'popularity', wc_get_page_permalink('shop'))).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala87666a6c7dfd023b4375fc379bf394c)): ?>
<?php $attributes = $__attributesOriginala87666a6c7dfd023b4375fc379bf394c; ?>
<?php unset($__attributesOriginala87666a6c7dfd023b4375fc379bf394c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala87666a6c7dfd023b4375fc379bf394c)): ?>
<?php $component = $__componentOriginala87666a6c7dfd023b4375fc379bf394c; ?>
<?php unset($__componentOriginala87666a6c7dfd023b4375fc379bf394c); ?>
<?php endif; ?>

  <!-- SALE Section -->
  <?php
    $sale_products = wc_get_products([
        'limit' => 8,
        'on_sale' => true,
        'status' => 'publish'
    ]);
  ?>
  
  <?php if (isset($component)) { $__componentOriginala87666a6c7dfd023b4375fc379bf394c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala87666a6c7dfd023b4375fc379bf394c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product-grid','data' => ['title' => 'SALE','products' => $sale_products,'columns' => 4,'viewAllUrl' => ''.e(add_query_arg('on_sale', '1', wc_get_page_permalink('shop'))).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product-grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'SALE','products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sale_products),'columns' => 4,'viewAllUrl' => ''.e(add_query_arg('on_sale', '1', wc_get_page_permalink('shop'))).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala87666a6c7dfd023b4375fc379bf394c)): ?>
<?php $attributes = $__attributesOriginala87666a6c7dfd023b4375fc379bf394c; ?>
<?php unset($__attributesOriginala87666a6c7dfd023b4375fc379bf394c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala87666a6c7dfd023b4375fc379bf394c)): ?>
<?php $component = $__componentOriginala87666a6c7dfd023b4375fc379bf394c; ?>
<?php unset($__componentOriginala87666a6c7dfd023b4375fc379bf394c); ?>
<?php endif; ?>

  <!-- Category Navigation
  <section class="category-navigation">
    <div class="container">
      <div class="category-grid">
        <div class="category-item">
          <a href="#" class="category-link">
            <div class="category-image">
              <img src="/app/themes/heygirlsbkk/resources/images/category-tops.jpg" alt="상의" />
            </div>
            <h3 class="category-title">상의</h3>
          </a>
        </div>
        <div class="category-item">
          <a href="#" class="category-link">
            <div class="category-image">
              <img src="/app/themes/heygirlsbkk/resources/images/category-bottoms.jpg" alt="하의" />
            </div>
            <h3 class="category-title">하의</h3>
          </a>
        </div>
        <div class="category-item">
          <a href="#" class="category-link">
            <div class="category-image">
              <img src="/app/themes/heygirlsbkk/resources/images/category-outer.jpg" alt="아우터" />
            </div>
            <h3 class="category-title">아우터</h3>
          </a>
        </div>
        <div class="category-item">
          <a href="#" class="category-link">
            <div class="category-image">
              <img src="/app/themes/heygirlsbkk/resources/images/category-dress.jpg" alt="원피스" />
            </div>
            <h3 class="category-title">원피스</h3>
          </a>
        </div>
      </div>
    </div>
  </section> -->

  <!-- Newsletter Signup -->
  <section class="newsletter-section">
    <div class="container">
      <div class="newsletter-content">
        <h2 class="newsletter-title">Stay Updated</h2>
        <p class="newsletter-subtitle">Get the latest updates on new arrivals and exclusive offers</p>
        <form class="newsletter-form">
          <input type="email" placeholder="Enter your email address" class="newsletter-input" required>
          <button type="submit" class="newsletter-button">Subscribe</button>
        </form>
      </div>
    </div>
  </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/index.blade.php ENDPATH**/ ?>