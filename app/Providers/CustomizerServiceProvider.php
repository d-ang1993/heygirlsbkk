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

            // Background image position
            $wp_customize->add_setting('hero_bg_position', [
                'default' => 'center center',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_bg_position', [
                'type'    => 'select',
                'label'   => __('Background position', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'center center' => 'Center',
                    'top center' => 'Top Center',
                    'bottom center' => 'Bottom Center',
                    'left center' => 'Left Center',
                    'right center' => 'Right Center',
                    'top left' => 'Top Left',
                    'top right' => 'Top Right',
                    'bottom left' => 'Bottom Left',
                    'bottom right' => 'Bottom Right',
                ],
            ]);

            // Background image size
            $wp_customize->add_setting('hero_bg_size', [
                'default' => 'cover',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_bg_size', [
                'type'    => 'select',
                'label'   => __('Background size', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'cover' => 'Cover (recommended)',
                    'contain' => 'Contain',
                    '100% 100%' => 'Stretch to fit',
                    'auto' => 'Original size',
                ],
            ]);

            // Background image repeat
            $wp_customize->add_setting('hero_bg_repeat', [
                'default' => 'no-repeat',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_bg_repeat', [
                'type'    => 'select',
                'label'   => __('Background repeat', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'no-repeat' => 'No repeat',
                    'repeat' => 'Repeat',
                    'repeat-x' => 'Repeat horizontally',
                    'repeat-y' => 'Repeat vertically',
                ],
            ]);

            // Background color fallback
            $wp_customize->add_setting('hero_bg_color', [
                'default' => '#f8f9fa',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'hero_bg_color', [
                'label'   => __('Background color (fallback)', 'sage'),
                'section' => 'hero_section',
                'description' => __('This color will show if no image is set or while image loads', 'sage'),
            ]));

            // Background image attachment
            $wp_customize->add_setting('hero_bg_attachment', [
                'default' => 'scroll',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_bg_attachment', [
                'type'    => 'select',
                'label'   => __('Background attachment', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'scroll' => 'Scroll with content',
                    'fixed' => 'Fixed (parallax effect)',
                ],
            ]);

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

        // New Arrival Section
        \add_action('customize_register', function($wp_customize) {
            $wp_customize->add_section('new_arrival_section', [
                'title' => __('New Arrival', 'sage'),
                'priority' => 30,
            ]);

            // Enable/disable
            $wp_customize->add_setting('new_arrival_enable', [
                'default' => true,
                'transport' => 'refresh',
            ]);

            $wp_customize->add_control('new_arrival_enable', [
                'label' => __('Enable New Arrival Section', 'sage'),
                'section' => 'new_arrival_section',
                'type' => 'checkbox',
            ]);

            // Title
            $wp_customize->add_setting('new_arrival_title', [
                'default' => 'NEW ARRIVAL',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('new_arrival_title', [
                'label' => __('Section Title', 'sage'),
                'section' => 'new_arrival_section',
                'type' => 'text',
            ]);

            // Number of products
            $wp_customize->add_setting('new_arrival_count', [
                'default' => 4,
                'transport' => 'refresh',
                'sanitize_callback' => 'absint',
            ]);

            $wp_customize->add_control('new_arrival_count', [
                'label' => __('Number of Products', 'sage'),
                'section' => 'new_arrival_section',
                'type' => 'select',
                'choices' => [
                    '4' => '4 Products',
                    '6' => '6 Products',
                    '8' => '8 Products',
                ],
            ]);

            // Number of columns
            $wp_customize->add_setting('new_arrival_columns', [
                'default' => 4,
                'transport' => 'refresh',
                'sanitize_callback' => 'absint',
            ]);

            $wp_customize->add_control('new_arrival_columns', [
                'label' => __('Number of Columns', 'sage'),
                'section' => 'new_arrival_section',
                'type' => 'select',
                'choices' => [
                    '2' => '2 Columns',
                    '3' => '3 Columns',
                    '4' => '4 Columns',
                ],
            ]);
        });

        // Footer Section
        \add_action('customize_register', function($wp_customize) {
            $wp_customize->add_section('footer_section', [
                'title' => __('Footer', 'sage'),
                'priority' => 35,
            ]);

            // Enable/disable footer
            $wp_customize->add_setting('footer_enable', [
                'default' => true,
                'transport' => 'refresh',
            ]);

            $wp_customize->add_control('footer_enable', [
                'label' => __('Enable Footer', 'sage'),
                'section' => 'footer_section',
                'type' => 'checkbox',
            ]);

            // Customer Center Section
            $wp_customize->add_setting('footer_customer_title', [
                'default' => 'CUSTOMER CENTER',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_customer_title', [
                'label' => __('Customer Center Title', 'sage'),
                'section' => 'footer_section',
                'type' => 'text',
            ]);

            $wp_customize->add_setting('footer_customer_phone', [
                'default' => '070-4364-9255',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_customer_phone', [
                'label' => __('Customer Center Phone', 'sage'),
                'section' => 'footer_section',
                'type' => 'text',
            ]);

            $wp_customize->add_setting('footer_customer_hours', [
                'default' => "OPEN 월-금\n오후 13:00 ~ 오후 18:00\n토/일/공휴일 OFF",
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_customer_hours', [
                'label' => __('Operating Hours', 'sage'),
                'section' => 'footer_section',
                'type' => 'textarea',
            ]);

            // Company Information Section
            $wp_customize->add_setting('footer_company_brand', [
                'default' => 'HYPNOTIC.',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_company_brand', [
                'label' => __('Company Brand Name', 'sage'),
                'section' => 'footer_section',
                'type' => 'text',
            ]);

            $wp_customize->add_setting('footer_company_info', [
                'default' => "COMPANY : (주)히프나틱\nCOMPANY: HYPNOTIC INC.\nOWNER : 김윤주, 김지수\nOWNER: KIM YUNJU, KIM JISOO\nTEL: 070-4364-9255\nBUSINESS NUMBER: 589-88-00495 [사업자정보확인]\nADD : 서울특별시 중구 소공로 70(충무로1가, 서울 중앙 우체국) 히프나틱 물류센터\nMAIL ORDER LICENSE: 2017-서울중구-0147\nCHIEF PRIVACY OFFICER : 김윤주, 김지수 (PLUSHYPNOTIC@HYPNOTIC.CO.KR)\n광고, 제휴문의 : PLUSHYPNOTIC@NAVER.COM",
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_company_info', [
                'label' => __('Company Information', 'sage'),
                'section' => 'footer_section',
                'type' => 'textarea',
            ]);

            // Social Links Section
            $wp_customize->add_setting('footer_tiktok_url', [
                'default' => '',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_tiktok_url', [
                'label' => __('TikTok URL', 'sage'),
                'section' => 'footer_section',
                'type' => 'url',
            ]);

            $wp_customize->add_setting('footer_instagram_url', [
                'default' => '',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_instagram_url', [
                'label' => __('Instagram URL', 'sage'),
                'section' => 'footer_section',
                'type' => 'url',
            ]);

            $wp_customize->add_setting('footer_shopee_url', [
                'default' => '',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_shopee_url', [
                'label' => __('Shopee URL', 'sage'),
                'section' => 'footer_section',
                'type' => 'url',
            ]);

            $wp_customize->add_setting('footer_line_url', [
                'default' => '',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('footer_line_url', [
                'label' => __('Line URL', 'sage'),
                'section' => 'footer_section',
                'type' => 'url',
            ]);
        });

        // Navbar Section
        \add_action('customize_register', function($wp_customize) {
            $wp_customize->add_section('navbar_section', [
                'title' => __('Navigation Bar', 'sage'),
                'priority' => 20,
            ]);

            // Enable/disable navbar
            $wp_customize->add_setting('navbar_enable', [
                'default' => true,
                'transport' => 'refresh',
            ]);

            $wp_customize->add_control('navbar_enable', [
                'label' => __('Enable Navigation Bar', 'sage'),
                'section' => 'navbar_section',
                'type' => 'checkbox',
            ]);

            // Logo text
            $wp_customize->add_setting('navbar_logo', [
                'default' => 'HEYGIRLSBKK',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('navbar_logo', [
                'label' => __('Logo Text', 'sage'),
                'section' => 'navbar_section',
                'type' => 'text',
            ]);

            // Collections (left side of dropdown)
            $wp_customize->add_setting('navbar_collections', [
                'default' => "HEYGIRLSBKK\nLITTLE SISTER\nHEYGIRLSBKK PARLOUR",
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('navbar_collections', [
                'label' => __('Collections (one per line)', 'sage'),
                'section' => 'navbar_section',
                'type' => 'textarea',
                'description' => __('These will appear in the left column of the dropdown menu', 'sage'),
            ]);

            // Categories (right side of dropdown)
            $wp_customize->add_setting('navbar_categories', [
                'default' => "VIEW ALL\nNEW ARRIVALS\nFALL - WINTER 2025\nPRE - FALL 2025\nSPRING - SUMMER 2025\nRESORT 2025\nFALL - WINTER 2024\nTOPS\nBOTTOMS\nDRESSES\nJUMPSUITS\nOUTERWEAR\nACCESSORIES\nJEWELRY\nHEYGIRLSBKK REIMAGINED\nSALE",
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('navbar_categories', [
                'label' => __('Categories (one per line)', 'sage'),
                'section' => 'navbar_section',
                'type' => 'textarea',
                'description' => __('These will appear in the right column of the dropdown menu', 'sage'),
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

