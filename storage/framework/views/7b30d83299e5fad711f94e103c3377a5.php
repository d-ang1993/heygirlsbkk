<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'heading' => null,
    'subheading' => null,
    'ctaText' => null,
    'ctaUrl' => null,
    'images' => []
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'heading' => null,
    'subheading' => null,
    'ctaText' => null,
    'ctaUrl' => null,
    'images' => []
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
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
            $imageId = $product->get_image_id();
            $imageUrl = wp_get_attachment_image_url($imageId, 'woocommerce_single');
            if ($imageUrl) {
                // Get image dimensions for better CLS prevention
                $imageMeta = wp_get_attachment_image_src($imageId, 'woocommerce_single');
                $images[] = [
                    'url' => $imageUrl,
                    'alt' => $product->get_name(),
                    'link' => $product->get_permalink(),
                    'width' => $imageMeta[1] ?? 176,
                    'height' => $imageMeta[2] ?? 256
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
?>

<!-- Hero section - New Image Grid Layout -->
<div class="hero-new">
  <div class="hero-new__container">
    <div class="hero-new__content">
      <div class="hero-new__text">
        <h1 class="hero-new__title"><?php echo e($heading); ?></h1>
        <?php if($subheading): ?>
          <p class="hero-new__subtitle"><?php echo e($subheading); ?></p>
        <?php endif; ?>
        <?php if($ctaText): ?>
          <a href="<?php echo e($ctaUrl); ?>" class="hero-new__cta"><?php echo e($ctaText); ?></a>
        <?php endif; ?>
      </div>
      
      <div class="hero-new__action">
        <div class="hero-new__image-grid">
          <!-- Decorative image grid -->
          <div class="hero-new__grid-wrapper" aria-hidden="true">
            <div class="hero-new__grid-container">
              <div class="hero-new__grid">
                <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $colIndex => $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <div class="hero-new__grid-column">
                    <?php $__currentLoopData = $column; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <div class="hero-new__grid-item">
                        <a href="<?php echo e($image['link'] ?? '#'); ?>" class="hero-new__grid-link">
                          <img 
                            src="<?php echo e($image['url']); ?>" 
                            alt="<?php echo e($image['alt'] ?? 'Product image'); ?>" 
                            class="hero-new__grid-image"
                            width="<?php echo e($image['width'] ?? 176); ?>"
                            height="<?php echo e($image['height'] ?? 256); ?>"
                            loading="<?php echo e($colIndex === 0 && $loop->index === 0 ? 'eager' : 'lazy'); ?>"
                            fetchpriority="<?php echo e($colIndex === 0 && $loop->index === 0 ? 'high' : 'auto'); ?>"
                            decoding="async" />
                        </a>
                      </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/components/hero-new.blade.php ENDPATH**/ ?>