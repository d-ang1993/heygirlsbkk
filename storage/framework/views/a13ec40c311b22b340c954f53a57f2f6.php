<?php
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
?>

<?php if($new_arrival_enable && !empty($recent_products)): ?>
<?php if (isset($component)) { $__componentOriginala87666a6c7dfd023b4375fc379bf394c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala87666a6c7dfd023b4375fc379bf394c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product-grid','data' => ['title' => ''.e($new_arrival_title).'','products' => $recent_products,'columns' => $new_arrival_columns]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product-grid'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($new_arrival_title).'','products' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($recent_products),'columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($new_arrival_columns)]); ?>
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
<?php endif; ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/partials/new-arrival.blade.php ENDPATH**/ ?>