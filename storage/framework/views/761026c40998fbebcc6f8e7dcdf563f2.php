<?php $__env->startSection('content'); ?>
  <?php 
    // Remove WooCommerce default breadcrumbs and controls
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    do_action('woocommerce_before_main_content') 
  ?>

  <?php if(woocommerce_product_loop()): ?>
    <?php do_action('woocommerce_before_shop_loop') ?>

    <?php
      // Collect all products from the current query
      $archive_products = [];
      while (have_posts()) {
        the_post();
        global $product;
        if ($product && is_a($product, 'WC_Product')) {
          $archive_products[] = $product;
        }
      }
      wp_reset_postdata();
      
      // Get current query info
      global $wp_query;
      $total_products = $wp_query->found_posts;
    ?>

    <!-- Custom Archive Header with Styled Elements -->
    <div class="archive-header">
      <div class="container">
        <!-- Breadcrumbs -->
        <nav class="woocommerce-breadcrumb" aria-label="Breadcrumb">
          <a href="<?php echo e(home_url()); ?>">Home</a>
          <span class="breadcrumb-separator">/</span>
          <a href="<?php echo e(wc_get_page_permalink('shop')); ?>">Shop</a>
          <span class="breadcrumb-separator">/</span>
          <a href="<?php echo e(wc_get_page_permalink('shop')); ?>/category/collections/">Collections</a>
          <span class="breadcrumb-separator">/</span>
          <span class="breadcrumb-current"><?php echo e(woocommerce_page_title(false)); ?></span>
        </nav>

        <!-- Results and Sorting -->
        <div class="archive-controls">
          <div class="woocommerce-result-count">
            Showing all <?php echo e($total_products); ?> <?php echo e($total_products === 1 ? 'result' : 'results'); ?>

          </div>
          
          <div class="woocommerce-ordering">
            <form method="get">
              <select name="orderby" class="orderby" aria-label="Shop order">
                <option value="menu_order" <?php echo e(selected('menu_order', $_GET['orderby'] ?? 'menu_order')); ?>>Default sorting</option>
                <option value="popularity" <?php echo e(selected('popularity', $_GET['orderby'] ?? '')); ?>>Sort by popularity</option>
                <option value="rating" <?php echo e(selected('rating', $_GET['orderby'] ?? '')); ?>>Sort by average rating</option>
                <option value="date" <?php echo e(selected('date', $_GET['orderby'] ?? '')); ?>>Sort by latest</option>
                <option value="price" <?php echo e(selected('price', $_GET['orderby'] ?? '')); ?>>Sort by price: low to high</option>
                <option value="price-desc" <?php echo e(selected('price-desc', $_GET['orderby'] ?? '')); ?>>Sort by price: high to low</option>
              </select>
              <input type="hidden" name="paged" value="1" />
              <?php $__currentLoopData = $_GET; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($key !== 'orderby' && $key !== 'paged'): ?>
                  <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>" />
                <?php endif; ?>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </form>
          </div>
        </div>
      </div>
    </div>

    <?php echo $__env->make('components.product-grid', [
      'title' => woocommerce_page_title(false),
      'products' => $archive_products,
      'columns' => 4,
      'showDiscount' => true,
      'showQuickView' => true
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php do_action('woocommerce_after_shop_loop') ?>
  <?php else: ?>
    <?php do_action('woocommerce_no_products_found') ?>
  <?php endif; ?>

  <?php do_action('woocommerce_after_main_content') ?>

  <!-- Archive Page JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-submit sorting form when selection changes
      const orderbySelect = document.querySelector('.orderby');
      if (orderbySelect) {
        orderbySelect.addEventListener('change', function() {
          this.form.submit();
        });
      }
    });
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/woocommerce/archive-product.blade.php ENDPATH**/ ?>