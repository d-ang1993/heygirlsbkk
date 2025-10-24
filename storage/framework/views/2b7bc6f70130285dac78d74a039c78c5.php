<?php
$enable = get_theme_mod('featured_products_enable', false);
$title = get_theme_mod('featured_products_title', 'Featured Products');
$categoryId = get_theme_mod('featured_products_category', '');
$count = get_theme_mod('featured_products_count', 6);
$columns = get_theme_mod('featured_products_columns', 3);

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
?>

<?php if($enable && !empty($featuredProducts)): ?>
<?php
  // Generate the collection URL
  $collectionUrl = '#';
  if ($categoryId) {
    $category = get_term($categoryId, 'product_cat');
    if ($category && !is_wp_error($category)) {
      $collectionUrl = get_term_link($category);
    }
  }
?>
<?php if (isset($component)) { $__componentOriginala87666a6c7dfd023b4375fc379bf394c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala87666a6c7dfd023b4375fc379bf394c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product-grid','data' => ['title' => ''.e($title).'','products' => $featuredProducts,'columns' => $columns,'viewAllUrl' => $collectionUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product-grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($title).'','products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($featuredProducts),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($columns),'viewAllUrl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($collectionUrl)]); ?>
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
<?php else: ?>
<!-- Debug info for Featured Products -->
<?php if(current_user_can('manage_options')): ?>
<div style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
  <h3>Featured Products Debug Info:</h3>
  <p><strong>Enabled:</strong> <?php echo e($enable ? 'Yes' : 'No'); ?></p>
  <p><strong>Category ID:</strong> <?php echo e($categoryId ?: 'Not set'); ?></p>
  <p><strong>Products found:</strong> <?php echo e(count($featuredProducts)); ?></p>
  <p><strong>Title:</strong> <?php echo e($title); ?></p>
  <?php if($categoryId): ?>
    <?php $category = get_term($categoryId, 'product_cat'); ?>
    <p><strong>Category:</strong> <?php echo e($category && !is_wp_error($category) ? $category->name : 'Category not found'); ?></p>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/partials/featured-products.blade.php ENDPATH**/ ?>