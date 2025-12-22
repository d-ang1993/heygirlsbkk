<?php $__env->startSection('content'); ?>
<div class="wishlist-page">
  <div class="wishlist-header">
    <h1 class="wishlist-title">My Wishlist</h1>
    <p class="wishlist-subtitle">Save your favorite items for later</p>
  </div>
  
  <div class="wishlist-content">
    <?php
      echo do_shortcode('[ti_wishlistsview]');
    ?>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/woocommerce/wishlist.blade.php ENDPATH**/ ?>