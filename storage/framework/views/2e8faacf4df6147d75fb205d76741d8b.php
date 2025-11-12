<?php $__env->startSection('content'); ?>
  <div class="search-results-page">
    <?php echo $__env->make('partials.page-header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="search-content">
      <?php if(! have_posts()): ?>
        <div class="no-results">
          <div class="no-results-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
              <circle cx="11" cy="11" r="8"></circle>
              <path d="m21 21-4.35-4.35"></path>
            </svg>
          </div>
          <h2>No results found</h2>
          <p>Sorry, we couldn't find anything matching your search. Try different keywords or browse our categories.</p>
          
          <div class="search-again">
            <?php echo $__env->make('forms.search', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          </div>
        </div>
      <?php else: ?>
        <div class="search-info">
          <?php
            global $wp_query;
            $found_posts = $wp_query->found_posts ?? 0;
          ?>
          <p>Found <?php echo e($found_posts); ?> result<?php echo e($found_posts !== 1 ? 's' : ''); ?> for "<?php echo e(get_search_query()); ?>"</p>
        </div>

        <div class="search-results">
          <?php while(have_posts()): ?> <?php (the_post()); ?>
            <?php echo $__env->make('partials.content-search', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
          <?php endwhile; ?>
        </div>

        <div class="search-pagination">
          <?php echo get_the_posts_navigation(); ?>

        </div>
      <?php endif; ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/search.blade.php ENDPATH**/ ?>