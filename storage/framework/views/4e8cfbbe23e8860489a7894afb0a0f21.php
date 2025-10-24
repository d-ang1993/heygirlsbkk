<?php
$show = get_theme_mod('new_drops_enable', false);
$title = get_theme_mod('new_drops_title', 'NEW DROPS');
$subtitle = get_theme_mod('new_drops_subtitle', 'Fresh styles just dropped');
$count = get_theme_mod('new_drops_count', 3);
$autoplay = get_theme_mod('new_drops_autoplay', true);
$autoplaySpeed = get_theme_mod('new_drops_autoplay_speed', 5000);
$height = get_theme_mod('new_drops_height', '400px');
$imageOpacity = get_theme_mod('new_drops_image_opacity', 1);
$gradientStart = get_theme_mod('new_drops_title_gradient_start', '#000000');
$gradientEnd = get_theme_mod('new_drops_title_gradient_end', '#000000');
$marginTop = get_theme_mod('new_drops_margin_top', '60');
$marginBottom = get_theme_mod('new_drops_margin_bottom', '60');
$headerMargin = get_theme_mod('new_drops_header_margin', '40');

// Collect slides data
$slides = [];
for ($i = 1; $i <= $count; $i++) {
    $imageId = get_theme_mod("new_drops_slide_{$i}_image");
    
    // Image processing for slide {$i}
    
    if ($imageId) {
        // Try multiple methods to get the image URL
        $imageUrl = '';
        
        // Method 1: wp_get_attachment_image_url
        $imageUrl = wp_get_attachment_image_url($imageId, 'full');
        
        // Method 2: If that fails, try wp_get_attachment_url
        if (!$imageUrl) {
            $imageUrl = wp_get_attachment_url($imageId);
        }
        
        // Method 3: If still no URL, try getting the attachment post
        if (!$imageUrl) {
            $attachment = get_post($imageId);
            if ($attachment && $attachment->post_type === 'attachment') {
                $imageUrl = wp_get_attachment_url($imageId);
            }
        }
        
        // Method 4: Last resort - check if it's already a URL
        if (!$imageUrl && filter_var($imageId, FILTER_VALIDATE_URL)) {
            $imageUrl = $imageId;
        }
        
        // Method 5: Try to get the image from the media library by ID
        if (!$imageUrl && is_numeric($imageId)) {
            $attachment = get_attached_file($imageId);
            if ($attachment) {
                $imageUrl = wp_get_attachment_url($imageId);
            }
        }
        
        if ($imageUrl) {
            $slides[] = [
                'image' => $imageUrl,
                'url' => esc_url(get_theme_mod("new_drops_slide_{$i}_url", '')),
                'button_text' => get_theme_mod("new_drops_slide_{$i}_button_text", 'SHOP NOW'),
                'button_url' => esc_url(get_theme_mod("new_drops_slide_{$i}_button_url", '')),
                'button_position' => get_theme_mod("new_drops_slide_{$i}_button_position", 'center'),
                'show_button' => get_theme_mod("new_drops_slide_{$i}_show_button", true),
            ];
        }
    }
}
?>



<?php if($show): ?>
<section class="new-drops-carousel" style="
    --carousel-height: <?php echo e($height); ?>;
    --image-opacity: <?php echo e($imageOpacity); ?>;
    --title-gradient-start: <?php echo e($gradientStart); ?>;
    --title-gradient-end: <?php echo e($gradientEnd); ?>;
    padding-top: <?php echo e($marginTop); ?>px;
    padding-bottom: <?php echo e($marginBottom); ?>px;
">
    <div class="container">
        <?php if($title || $subtitle): ?>
        <div class="new-drops-header" style="margin-bottom: <?php echo e($headerMargin); ?>px;">
            <?php if($title): ?>
                <h2 class="new-drops-title"><?php echo e($title); ?></h2>
            <?php endif; ?>
            <?php if($subtitle): ?>
                <p class="new-drops-subtitle"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if(empty($slides)): ?>
        <div style="background: #f7a9d0; padding: 40px; text-align: center; border-radius: 12px; color: white;">
            <h3>New Drops Carousel</h3>
            <p>Upload images in the WordPress Customizer to see your carousel here.</p>
            <p><strong>Go to: Appearance > Customize > New Drops Carousel</strong></p>
        </div>
        <?php else: ?>

        <div class="new-drops-carousel-container" 
             data-autoplay="<?php echo e($autoplay ? 'true' : 'false'); ?>" 
             data-autoplay-speed="<?php echo e($autoplaySpeed); ?>">
            
            <div class="new-drops-slides">
                <?php $__currentLoopData = $slides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="new-drops-slide <?php echo e($index === 0 ? 'active' : ''); ?>" 
                     data-slide="<?php echo e($index); ?>">
                    
                    <?php if($slide['url']): ?>
                        <a href="<?php echo e($slide['url']); ?>" class="new-drops-slide-link">
                    <?php endif; ?>
                    
                    <img src="<?php echo e($slide['image']); ?>" 
                         alt="New Drop <?php echo e($index + 1); ?>" 
                         class="new-drops-slide-image" />
                    
                    <?php if($slide['show_button'] && $slide['button_url']): ?>
                        <div class="new-drops-button-container new-drops-button-<?php echo e($slide['button_position']); ?>">
                            <a href="<?php echo e($slide['button_url']); ?>" 
                               class="new-drops-button btn btn-primary">
                                <?php echo e($slide['button_text']); ?>

                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($slide['url']): ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if(count($slides) > 1): ?>
            <!-- Navigation Arrows -->
            <div class="new-drops-navigation">
                <button class="new-drops-arrow new-drops-arrow--prev" aria-label="Previous slide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                </button>
                <button class="new-drops-arrow new-drops-arrow--next" aria-label="Next slide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </button>
            </div>

            <!-- Dots Indicator -->
            <div class="new-drops-dots">
                <?php $__currentLoopData = $slides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button class="new-drops-dot <?php echo e($index === 0 ? 'active' : ''); ?>" 
                        data-slide="<?php echo e($index); ?>"
                        aria-label="Go to slide <?php echo e($index + 1); ?>">
                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
<?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/partials/new-drops-carousel.blade.php ENDPATH**/ ?>