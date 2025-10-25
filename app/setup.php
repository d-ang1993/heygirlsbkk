<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_filter('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    $dependencies = json_decode(Vite::content('editor.deps.json'));

    foreach ($dependencies as $dependency) {
        if (! wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }

    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
        'primary' => __('Main Navigation Bar', 'sage'),
        'shop-dropdown' => __('Shop Dropdown Menu', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Add custom image sizes for product displays.
     */
    add_image_size('product-thumbnail', 400, 400, true);
    add_image_size('product-grid', 500, 500, true);
    add_image_size('product-hero', 800, 800, true);
    add_image_size('product-large', 1200, 1200, false);

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Improve image quality and add WebP support.
 *
 * @return void
 */
add_action('init', function () {
    /**
     * Increase JPEG quality from default 82% to 90%.
     */
    add_filter('jpeg_quality', function () {
        return 90;
    });

    /**
     * Add WebP support for modern browsers (simplified version).
     */
    add_filter('wp_generate_attachment_metadata', function ($metadata, $attachment_id) {
        // Only process if we have the required functions
        if (!function_exists('imagewebp') || !isset($metadata['sizes'])) {
            return $metadata;
        }

        try {
            $upload_dir = wp_upload_dir();
            $attachment_path = get_attached_file($attachment_id);
            
            if (!$attachment_path || !file_exists($attachment_path)) {
                return $metadata;
            }

            // Convert main image to WebP
            $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $attachment_path);
            if (convert_to_webp($attachment_path, $webp_path)) {
                $metadata['file_webp'] = str_replace($upload_dir['basedir'], '', $webp_path);
            }

            // Convert thumbnails to WebP
            foreach ($metadata['sizes'] as $size => $size_data) {
                $thumbnail_path = $upload_dir['basedir'] . '/' . dirname($metadata['file']) . '/' . $size_data['file'];
                if (file_exists($thumbnail_path)) {
                    $webp_thumbnail_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $thumbnail_path);
                    if (convert_to_webp($thumbnail_path, $webp_thumbnail_path)) {
                        $metadata['sizes'][$size]['file_webp'] = basename($webp_thumbnail_path);
                    }
                }
            }
        } catch (Exception $e) {
            // Log error but don't break the site
            error_log('WebP conversion error: ' . $e->getMessage());
        }

        return $metadata;
    }, 10, 2);

    /**
     * Serve WebP images when supported by browser (simplified).
     */
    add_filter('wp_get_attachment_image_src', function ($image, $attachment_id, $size, $icon) {
        if (!$image || !browser_supports_webp() || !is_string($size)) {
            return $image;
        }

        try {
            $metadata = wp_get_attachment_metadata($attachment_id);
            if (!$metadata) {
                return $image;
            }

            // Check for WebP version of the requested size
            if ($size === 'full') {
                if (isset($metadata['file_webp'])) {
                    $upload_dir = wp_upload_dir();
                    $webp_url = $upload_dir['baseurl'] . $metadata['file_webp'];
                    if (file_exists($upload_dir['basedir'] . $metadata['file_webp'])) {
                        $image[0] = $webp_url;
                    }
                }
            } else {
                if (isset($metadata['sizes'][$size]['file_webp'])) {
                    $upload_dir = wp_upload_dir();
                    $webp_url = $upload_dir['baseurl'] . '/' . dirname($metadata['file']) . '/' . $metadata['sizes'][$size]['file_webp'];
                    $webp_path = $upload_dir['basedir'] . '/' . dirname($metadata['file']) . '/' . $metadata['sizes'][$size]['file_webp'];
                    if (file_exists($webp_path)) {
                        $image[0] = $webp_url;
                    }
                }
            }
        } catch (Exception $e) {
            // Log error but don't break the site
            error_log('WebP serving error: ' . $e->getMessage());
        }

        return $image;
    }, 15, 4);
});

/**
 * Convert image to WebP format.
 */
function convert_to_webp($source_path, $destination_path) {
    if (!function_exists('imagewebp')) {
        return false;
    }

    $image_info = getimagesize($source_path);
    if (!$image_info) {
        return false;
    }

    switch ($image_info[2]) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source_path);
            // Preserve transparency
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    $result = imagewebp($image, $destination_path, 85);
    imagedestroy($image);
    
    return $result;
}

/**
 * Check if browser supports WebP.
 */
function browser_supports_webp() {
    if (!isset($_SERVER['HTTP_ACCEPT'])) {
        return false;
    }
    
    return strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
}

/**
 * Add additional image optimization hooks.
 *
 * @return void
 */
add_action('init', function () {
    /**
     * Add retina support for high-DPI displays.
     */
    add_filter('wp_get_attachment_image_src', function ($image, $attachment_id, $size, $icon) {
        if (!$image || $icon || !is_string($size)) {
            return $image;
        }

        // Check if device supports retina
        if (isset($_COOKIE['devicePixelRatio']) && $_COOKIE['devicePixelRatio'] >= 2) {
            $retina_size = $size . '_2x';
            $retina_image = wp_get_attachment_image_src($attachment_id, $retina_size, $icon);
            if ($retina_image) {
                return $retina_image;
            }
        }

        return $image;
    }, 5, 4);

    /**
     * Add srcset attribute for responsive images.
     */
    add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment, $size) {
        if (!isset($attr['src'])) {
            return $attr;
        }

        // Generate srcset for different sizes
        $srcset = [];
        $sizes = ['thumbnail', 'medium', 'large', 'full'];
        
        foreach ($sizes as $size_name) {
            $src = wp_get_attachment_image_url($attachment->ID, $size_name);
            if ($src && $src !== $attr['src']) {
                $image_meta = wp_get_attachment_metadata($attachment->ID);
                if (isset($image_meta['sizes'][$size_name])) {
                    $width = $image_meta['sizes'][$size_name]['width'];
                    $srcset[] = $src . ' ' . $width . 'w';
                }
            }
        }

        if (!empty($srcset)) {
            $attr['srcset'] = implode(', ', $srcset);
            $attr['sizes'] = '(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw';
        }

        return $attr;
    }, 10, 3);

    /**
     * Preload critical images.
     */
    add_action('wp_head', function () {
        if (is_front_page() || is_shop()) {
            echo '<link rel="preload" as="image" href="' . get_template_directory_uri() . '/resources/images/logo.png">';
        }
    });

    /**
     * Preload critical font to prevent FOUT/FOIT.
     */
    add_action('wp_head', function () {
        echo '<link rel="preload" as="font" type="font/otf" href="' . get_template_directory_uri() . '/public/fonts/hiragino-sans-gb.otf" crossorigin="anonymous">';
    }, 1); // Priority 1 to load early in <head>
});

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});
