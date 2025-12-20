<?php if($product_description ?? null): ?>
  <?php
    // Get the current WooCommerce product
    $product = $product ?? wc_get_product();
    
    if (!$product) {
      return;
    }
    
    // Get all available images (main + gallery)
    $all_images = [];
    $main_image_id = $product->get_image_id();
    if ($main_image_id) {
      $all_images[] = [
        'id' => $main_image_id,
        'url' => wp_get_attachment_image_url($main_image_id, 'woocommerce_single'),
        'alt' => get_post_meta($main_image_id, '_wp_attachment_image_alt', true) ?: ($product_name ?? 'Product')
      ];
    }
    
    $gallery_image_ids = $product->get_gallery_image_ids();
    foreach ($gallery_image_ids as $image_id) {
      $all_images[] = [
        'id' => $image_id,
        'url' => wp_get_attachment_image_url($image_id, 'woocommerce_single'),
        'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: ($product_name ?? 'Product')
      ];
    }
    
    // Get 2 images for right side (smaller, more text-focused)
    $index_images = [];
    if (!empty($all_images)) {
      // Use gallery images, exclude the one used for left background
      $available_images = array_filter($all_images, function($img) use ($left_image) {
        return $img['url'] !== $left_image;
      });
      $index_images = array_slice(array_values($available_images), 0, 2);
    }
    
    // Extract product attributes for accordion dropdowns
    $product_attributes = $product->get_attributes();
    
    // Get Sizes
    $sizes = [];
    if ($product->is_type('variable')) {
      $variation_attributes = $product->get_variation_attributes();
      foreach ($variation_attributes as $attribute_name => $options) {
        if (stripos($attribute_name, 'size') !== false) {
          $sizes = array_merge($sizes, $options);
        }
      }
      $sizes = array_unique($sizes);
    } else {
      foreach ($product_attributes as $attribute_name => $attribute) {
        if (stripos($attribute_name, 'size') !== false) {
          if ($attribute->is_taxonomy()) {
            $terms = wp_get_post_terms($product->get_id(), $attribute->get_name());
            if (!empty($terms) && !is_wp_error($terms)) {
              $sizes = array_map(function($term) { return $term->name; }, $terms);
            }
          } elseif (method_exists($attribute, 'get_options')) {
            $sizes = $attribute->get_options() ?? [];
          }
        }
      }
    }
    
    // Get Colors
    $colors = [];
    if ($product->is_type('variable')) {
      $variation_attributes = $product->get_variation_attributes();
      foreach ($variation_attributes as $attribute_name => $options) {
        if (stripos($attribute_name, 'color') !== false) {
          $colors = array_merge($colors, $options);
        }
      }
      $colors = array_unique($colors);
    } else {
      foreach ($product_attributes as $attribute_name => $attribute) {
        if (stripos($attribute_name, 'color') !== false) {
          if ($attribute->is_taxonomy()) {
            $terms = wp_get_post_terms($product->get_id(), $attribute->get_name());
            if (!empty($terms) && !is_wp_error($terms)) {
              $colors = array_map(function($term) { return $term->name; }, $terms);
            }
          } elseif (method_exists($attribute, 'get_options')) {
            $colors = $attribute->get_options() ?? [];
          }
        }
      }
    }
    
    // Get Material/Fabric
    $material = '';
    foreach ($product_attributes as $attribute_name => $attribute) {
      $attr_name = strtolower($attribute->get_name());
      if (stripos($attr_name, 'fabric') !== false || stripos($attr_name, 'material') !== false) {
        if ($attribute->is_taxonomy()) {
          $terms = wp_get_post_terms($product->get_id(), $attribute->get_name());
          if (!empty($terms) && !is_wp_error($terms)) {
            $material = implode(', ', array_map(function($term) { return $term->name; }, $terms));
          }
        } else {
          $material = $attribute->get_options() ? implode(', ', $attribute->get_options()) : '';
        }
        break;
      }
    }
    
    // Fallback: try to extract from short description
    if (empty($material)) {
      $short_desc = $product->get_short_description();
      if (stripos($short_desc, 'stretchy') !== false || stripos($short_desc, 'stretch') !== false) {
        $material = 'Soft stretch knit';
      } elseif (stripos($short_desc, 'cotton') !== false) {
        $material = 'Cotton';
      } elseif (stripos($short_desc, 'polyester') !== false) {
        $material = 'Polyester';
      }
    }
    
    // Get "Where to wear" - could be from custom field or description
    $where_to_wear = get_post_meta($product->get_id(), '_where_to_wear', true) ?: 
                     get_post_meta($product->get_id(), 'where_to_wear', true) ?: 
                     'Day to night, casual to elegant';
    
    // Get product name for title (use variable if available, otherwise get from product)
    $product_name = $product_name ?? $product->get_name();
    
    // Get images for left side - main background + 2 additional images
    $left_image = '';
    $left_additional_images = [];
    
    if (!empty($gallery_image_ids)) {
      // Use a random gallery image for main background
      $random_gallery_id = $gallery_image_ids[array_rand($gallery_image_ids)];
      $left_image = wp_get_attachment_image_url($random_gallery_id, 'woocommerce_single');
      
      // Get 2 more images from gallery (exclude the one used for background)
      $remaining_gallery_ids = array_filter($gallery_image_ids, function($id) use ($random_gallery_id) {
        return $id !== $random_gallery_id;
      });
      
      if (count($remaining_gallery_ids) >= 2) {
        $selected_ids = array_slice(array_values($remaining_gallery_ids), 0, 2);
      } else {
        // If not enough gallery images, use main image
        $selected_ids = $main_image_id ? [$main_image_id] : [];
      }
      
      foreach ($selected_ids as $img_id) {
        $left_additional_images[] = [
          'url' => wp_get_attachment_image_url($img_id, 'woocommerce_single'),
          'alt' => get_post_meta($img_id, '_wp_attachment_image_alt', true) ?: ($product_name ?? 'Product')
        ];
      }
    } elseif ($main_image_id) {
      // Fallback to main image if no gallery images
      $left_image = wp_get_attachment_image_url($main_image_id, 'woocommerce_single');
    }
  ?>
  
  <section class="editorial-spread">
    <div class="spread-left-wrapper">
      <div class="spread-left" <?php if($left_image): ?> style="background-image: url('<?php echo e($left_image); ?>');" <?php endif; ?>>
        <div class="spread-left-overlay"></div>
        <div class="spread-left-content">
          <p class="spread-kicker">Why we made this</p>
          <h2 class="spread-title"><?php echo e($product_name); ?></h2>
        </div>
        <div class="spread-left-bottom">
          <div class="spread-body">
            <?php echo wpautop($product_short_description); ?>

          </div>
        </div>
      </div>
      
      <?php if(!empty($left_additional_images)): ?>
        <div class="spread-left-images">
          <?php $__currentLoopData = $left_additional_images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="spread-left-image-item" data-bg-image="<?php echo e($img['url']); ?>">
              <img src="<?php echo e($img['url']); ?>" alt="<?php echo e($img['alt']); ?>" loading="lazy" style="display: none;">
            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="spread-right">
      <div class="spread-index">
        <span class="index-label">Index</span>
        <span class="index-line"></span>
      </div>

      <div class="spread-right-content">
        <div class="spread-right-text">
          <p class="spread-right-intro">
            Each piece is thoughtfully designed with attention to detail, quality materials, and a focus on comfort and style.
          </p>
          
          <!-- Accordion Dropdowns -->
          <div class="spec-accordions">
            <?php if(!empty($where_to_wear)): ?>
              <div class="spec-accordion">
                <button class="spec-accordion-header" type="button" aria-expanded="false">
                  <span class="spec-accordion-label">Where to wear</span>
                  <svg class="spec-accordion-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 4.5L6 7.5L9 4.5"/>
                  </svg>
                </button>
                <div class="spec-accordion-content">
                  <p><?php echo e($where_to_wear); ?></p>
                </div>
              </div>
            <?php endif; ?>
            
            <?php if(!empty($material)): ?>
              <div class="spec-accordion">
                <button class="spec-accordion-header" type="button" aria-expanded="false">
                  <span class="spec-accordion-label">Material</span>
                  <svg class="spec-accordion-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 4.5L6 7.5L9 4.5"/>
                  </svg>
                </button>
                <div class="spec-accordion-content">
                  <p><?php echo e($material); ?></p>
                </div>
              </div>
            <?php endif; ?>
            
            <?php if(!empty($colors)): ?>
              <div class="spec-accordion">
                <button class="spec-accordion-header" type="button" aria-expanded="false">
                  <span class="spec-accordion-label">Colors</span>
                  <svg class="spec-accordion-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 4.5L6 7.5L9 4.5"/>
                  </svg>
                </button>
                <div class="spec-accordion-content">
                  <div class="spec-colors-list">
                    <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <span class="spec-color-item"><?php echo e(ucwords(str_replace(['-', '_'], ' ', $color))); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            
            <?php if(!empty($sizes)): ?>
              <div class="spec-accordion">
                <button class="spec-accordion-header" type="button" aria-expanded="false">
                  <span class="spec-accordion-label">Sizes</span>
                  <svg class="spec-accordion-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 4.5L6 7.5L9 4.5"/>
                  </svg>
                </button>
                <div class="spec-accordion-content">
                  <div class="spec-sizes-list">
                    <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <span class="spec-size-item"><?php echo e(strtoupper($size)); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

       
      </div>
    </div>
  </section>
<?php endif; ?>
<?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/components/product/description.blade.php ENDPATH**/ ?>