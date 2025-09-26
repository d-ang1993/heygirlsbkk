<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


class CustomizerServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register any bindings here if needed
    }

    public function boot()
    {
        // Register Customizer options
        \add_action('customize_register', function($wp_customize) {
            // Section
            $wp_customize->add_section('hero_section', [
                'title'    => __('Homepage Hero', 'sage'),
                'priority' => 25,
            ]);

            // Enable/disable
            $wp_customize->add_setting('hero_enable', ['default' => true, 'transport' => 'refresh']);
            $wp_customize->add_control('hero_enable', [
                'type'    => 'checkbox',
                'label'   => __('Show hero on homepage', 'sage'),
                'section' => 'hero_section',
            ]);

            // Heading
            $wp_customize->add_setting('hero_heading', ['default' => 'NEW ARRIVAL', 'transport' => 'postMessage']);
            $wp_customize->add_control('hero_heading', [
                'type'    => 'text',
                'label'   => __('Heading', 'sage'),
                'section' => 'hero_section',
            ]);

            // Subheading
            $wp_customize->add_setting('hero_subheading', ['default' => 'Korean/Japanese fashion drops • 24h launch', 'transport' => 'postMessage']);
            $wp_customize->add_control('hero_subheading', [
                'type'    => 'textarea',
                'label'   => __('Subheading', 'sage'),
                'section' => 'hero_section',
            ]);

            // CTA
            $wp_customize->add_setting('hero_cta_text', ['default' => 'Shop Now', 'transport' => 'postMessage']);
            $wp_customize->add_control('hero_cta_text', [
                'type'    => 'text',
                'label'   => __('CTA text', 'sage'),
                'section' => 'hero_section',
            ]);

            $wp_customize->add_setting('hero_cta_url', [
                'default' => '/shop',
                'transport' => 'refresh',
                'sanitize_callback' => 'esc_url_raw',
            ]);
            $wp_customize->add_control('hero_cta_url', [
                'type'    => 'url',
                'label'   => __('CTA URL', 'sage'),
                'section' => 'hero_section',
            ]);

            // Background image
            $wp_customize->add_setting('hero_bg_image', ['transport' => 'refresh']);
            $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, 'hero_bg_image', [
                'label'    => __('Background image', 'sage'),
                'section'  => 'hero_section',
                'settings' => 'hero_bg_image',
            ]));

            // Alignment
            $wp_customize->add_setting('hero_align', ['default' => 'center', 'transport' => 'refresh']);
            $wp_customize->add_control('hero_align', [
                'type'    => 'select',
                'label'   => __('Text alignment', 'sage'),
                'section' => 'hero_section',
                'choices' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'],
            ]);

            // Height
            $wp_customize->add_setting('hero_height', ['default' => '60vh', 'transport' => 'refresh']);
            $wp_customize->add_control('hero_height', [
                'type'    => 'text',
                'label'   => __('Height (e.g. 60vh or 520px)', 'sage'),
                'section' => 'hero_section',
            ]);

            // Overlay
            $wp_customize->add_setting('hero_overlay', ['default' => 0.35, 'transport' => 'postMessage']);
            $wp_customize->add_control('hero_overlay', [
                'type'        => 'number',
                'label'       => __('Overlay opacity (0–1)', 'sage'),
                'section'     => 'hero_section',
                'input_attrs' => ['min' => 0, 'max' => 1, 'step' => 0.05],
            ]);

            // Product Category for Carousel
            $wp_customize->add_setting('hero_product_category', [
                'default' => '',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('hero_product_category', [
                'type'    => 'select',
                'label'   => __('Product Category for Carousel', 'sage'),
                'section' => 'hero_section',
                'choices' => $this->get_product_categories(),
            ]);

            // Carousel Settings
            $wp_customize->add_setting('hero_carousel_enable', [
                'default' => false,
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('hero_carousel_enable', [
                'type'    => 'checkbox',
                'label'   => __('Enable Product Carousel', 'sage'),
                'section' => 'hero_section',
            ]);

            $wp_customize->add_setting('hero_carousel_count', [
                'default' => 6,
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('hero_carousel_count', [
                'type'        => 'number',
                'label'       => __('Number of Products in Carousel', 'sage'),
                'section'     => 'hero_section',
                'input_attrs' => ['min' => 1, 'max' => 20, 'step' => 1],
            ]);
        });

        // Featured Products Section
        \add_action('customize_register', function($wp_customize) {
            $wp_customize->add_section('featured_products', [
                'title' => __('Featured Products', 'sage'),
                'priority' => 30,
            ]);

            // Featured Products Enable
            $wp_customize->add_setting('featured_products_enable', [
                'default' => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ]);

            $wp_customize->add_control('featured_products_enable', [
                'label' => __('Enable Featured Products Section', 'sage'),
                'section' => 'featured_products',
                'type' => 'checkbox',
            ]);

            // Featured Products Title
            $wp_customize->add_setting('featured_products_title', [
                'default' => 'Featured Products',
                'sanitize_callback' => 'sanitize_text_field',
            ]);

            $wp_customize->add_control('featured_products_title', [
                'label' => __('Section Title', 'sage'),
                'section' => 'featured_products',
                'type' => 'text',
            ]);

            // Featured Products Category
            $wp_customize->add_setting('featured_products_category', [
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ]);

            $wp_customize->add_control('featured_products_category', [
                'label' => __('Product Category', 'sage'),
                'section' => 'featured_products',
                'type' => 'select',
                'choices' => $this->get_product_categories(),
            ]);

            // Featured Products Count
            $wp_customize->add_setting('featured_products_count', [
                'default' => 6,
                'sanitize_callback' => 'absint',
            ]);

            $wp_customize->add_control('featured_products_count', [
                'label' => __('Number of Products', 'sage'),
                'section' => 'featured_products',
                'type' => 'number',
                'input_attrs' => [
                    'min' => 1,
                    'max' => 12,
                ],
            ]);

            // Featured Products Columns
            $wp_customize->add_setting('featured_products_columns', [
                'default' => 3,
                'sanitize_callback' => 'absint',
            ]);

            $wp_customize->add_control('featured_products_columns', [
                'label' => __('Number of Columns', 'sage'),
                'section' => 'featured_products',
                'type' => 'select',
                'choices' => [
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                ],
            ]);
        });

        // Enqueue live preview JS
        \add_action('customize_preview_init', function () {
            $uri  = \get_stylesheet_directory_uri() . '/resources/scripts/customize-preview.js';
            $path = \get_stylesheet_directory() . '/resources/scripts/customize-preview.js';

            if (file_exists($path)) {
                \wp_enqueue_script(
                    'hero-customize-preview',
                    $uri,
                    ['customize-preview'],
                    null,
                    true
                );
            }
        });
    }

    /**
     * Get WooCommerce product categories for customizer dropdown
     */
    private function get_product_categories()
    {
        $categories = ['' => 'None'];
        
        if (function_exists('\get_terms')) {
            $terms = \get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
            ]);
            
            if (!\is_wp_error($terms) && !empty($terms)) {
                foreach ($terms as $term) {
                    $categories[$term->term_id] = $term->name;
                }
            }
        }
        
        return $categories;
    }
}

