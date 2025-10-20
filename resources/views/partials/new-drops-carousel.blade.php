@php
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
@endphp

{{-- Debug info removed - carousel is working! --}}

@if($show)
<section class="new-drops-carousel" style="
    --carousel-height: {{ $height }};
    --image-opacity: {{ $imageOpacity }};
    --title-gradient-start: {{ $gradientStart }};
    --title-gradient-end: {{ $gradientEnd }};
    padding-top: {{ $marginTop }}px;
    padding-bottom: {{ $marginBottom }}px;
">
    <div class="container">
        @if($title || $subtitle)
        <div class="new-drops-header" style="margin-bottom: {{ $headerMargin }}px;">
            @if($title)
                <h2 class="new-drops-title">{{ $title }}</h2>
            @endif
            @if($subtitle)
                <p class="new-drops-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @endif

        @if(empty($slides))
        <div style="background: #f7a9d0; padding: 40px; text-align: center; border-radius: 12px; color: white;">
            <h3>New Drops Carousel</h3>
            <p>Upload images in the WordPress Customizer to see your carousel here.</p>
            <p><strong>Go to: Appearance > Customize > New Drops Carousel</strong></p>
        </div>
        @else

        <div class="new-drops-carousel-container" 
             data-autoplay="{{ $autoplay ? 'true' : 'false' }}" 
             data-autoplay-speed="{{ $autoplaySpeed }}">
            
            <div class="new-drops-slides">
                @foreach($slides as $index => $slide)
                <div class="new-drops-slide {{ $index === 0 ? 'active' : '' }}" 
                     data-slide="{{ $index }}">
                    
                    @if($slide['url'])
                        <a href="{{ $slide['url'] }}" class="new-drops-slide-link">
                    @endif
                    
                    <img src="{{ $slide['image'] }}" 
                         alt="New Drop {{ $index + 1 }}" 
                         class="new-drops-slide-image" />
                    
                    @if($slide['show_button'] && $slide['button_url'])
                        <div class="new-drops-button-container new-drops-button-{{ $slide['button_position'] }}">
                            <a href="{{ $slide['button_url'] }}" 
                               class="new-drops-button btn btn-primary">
                                {{ $slide['button_text'] }}
                            </a>
                        </div>
                    @endif
                    
                    @if($slide['url'])
                        </a>
                    @endif
                </div>
                @endforeach
            </div>

            @if(count($slides) > 1)
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
                @foreach($slides as $index => $slide)
                <button class="new-drops-dot {{ $index === 0 ? 'active' : '' }}" 
                        data-slide="{{ $index }}"
                        aria-label="Go to slide {{ $index + 1 }}">
                </button>
                @endforeach
            </div>
            @endif
        </div>
        @endif
    </div>
</section>
@endif
