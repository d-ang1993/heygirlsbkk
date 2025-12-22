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
        // Register Section Ordering
        \add_action('customize_register', function($wp_customize) {
            // Section Ordering Section
            $wp_customize->add_section('section_ordering', [
                'title' => __('Section Ordering', 'sage'),
                'priority' => 5, // Very top
                'description' => __('Drag and drop to reorder sections on your homepage', 'sage'),
            ]);

            // Section Order Setting
            $wp_customize->add_setting('homepage_section_order', [
                'default' => 'hero,new_drops,featured_products,new_arrival',
                'transport' => 'refresh',
                'sanitize_callback' => [$this, 'sanitize_section_order'],
            ]);

            $wp_customize->add_control(new \WP_Customize_Control($wp_customize, 'homepage_section_order', [
                'type' => 'hidden',
                'label' => __('Homepage Section Order', 'sage'),
                'section' => 'section_ordering',
                'settings' => 'homepage_section_order',
            ]));

            // Add custom JavaScript for drag and drop
            \add_action('customize_controls_enqueue_scripts', function() {
                wp_enqueue_script('jquery-ui-sortable');
                wp_add_inline_script('customize-controls', '
                    (function($) {
                        // Wait for customizer to be ready
                        wp.customize.bind("ready", function() {
                            console.log("Section Ordering: Initializing...");
                            
                            // Define sections with their display names
                            var sections = {
                                "hero": "Homepage Hero",
                                "new_drops": "New Drops Carousel", 
                                "featured_products": "Featured Products",
                                "new_arrival": "New Arrival"
                            };
                            
                            // Function to create the sortable list
                            function createSortableList() {
                                // Try multiple selectors to find the section
                                var $sectionOrderingSection = $("#accordion-section-section_ordering .accordion-section-content, " +
                                                               "#accordion-section-section_ordering .accordion-section, " +
                                                               ".accordion-section[data-section=\"section_ordering\"] .accordion-section-content, " +
                                                               ".accordion-section[data-section=\"section_ordering\"]");
                                
                                if ($sectionOrderingSection.length === 0) {
                                    console.log("Section Ordering: Section not found, retrying...");
                                    console.log("Section Ordering: Available sections:", $(".accordion-section").map(function() { return $(this).attr("data-section"); }).get());
                                    
                                    // Try to find any section that contains "section_ordering" in the text
                                    var $fallbackSection = $(".accordion-section").filter(function() {
                                        return $(this).text().toLowerCase().indexOf("section ordering") !== -1;
                                    });
                                    
                                    if ($fallbackSection.length > 0) {
                                        console.log("Section Ordering: Found fallback section");
                                        $sectionOrderingSection = $fallbackSection.find(".accordion-section-content").length > 0 ? 
                                            $fallbackSection.find(".accordion-section-content") : $fallbackSection;
                                    } else {
                                        setTimeout(createSortableList, 1000);
                                        return;
                                    }
                                }
                                
                                console.log("Section Ordering: Section found, creating list");
                                
                                // Remove existing list if any
                                $("#section-ordering-list").remove();
                                
                                // Create sortable list
                                var $sectionOrdering = $("<div id=\"section-ordering-list\"></div>");
                                $sectionOrderingSection.append($sectionOrdering);
                                
                                // Get current order
                                var currentOrder = wp.customize("homepage_section_order").get();
                                console.log("Section Ordering: Current order:", currentOrder);
                                
                                var orderArray = currentOrder ? currentOrder.split(",") : Object.keys(sections);
                                
                                // Create sortable list items
                                orderArray.forEach(function(sectionId) {
                                    if (sections[sectionId]) {
                                        $sectionOrdering.append(
                                            "<div class=\"section-order-item\" data-section=\"" + sectionId + "\">" +
                                            "<span class=\"dashicons dashicons-menu\"></span> " + sections[sectionId] +
                                            "</div>"
                                        );
                                    }
                                });
                                
                                // Make sortable
                                $sectionOrdering.sortable({
                                    placeholder: "section-order-placeholder",
                                    update: function(event, ui) {
                                        var newOrder = [];
                                        $sectionOrdering.find(".section-order-item").each(function() {
                                            newOrder.push($(this).data("section"));
                                        });
                                        console.log("Section Ordering: New order:", newOrder.join(","));
                                        wp.customize("homepage_section_order").set(newOrder.join(","));
                                    }
                                });
                                
                                console.log("Section Ordering: Sortable list created successfully");
                            }
                            
                            // Create the list
                            createSortableList();
                        });
                        
                        // Add CSS
                        $("<style>")
                            .prop("type", "text/css")
                            .html("#section-ordering-list { margin: 10px 0; }" +
                                  ".section-order-item { " +
                                  "  background: #fff; " +
                                  "  border: 1px solid #ddd; " +
                                  "  padding: 10px 15px; " +
                                  "  margin: 5px 0; " +
                                  "  cursor: move; " +
                                  "  border-radius: 4px; " +
                                  "  display: flex; " +
                                  "  align-items: center; " +
                                  "}" +
                                  ".section-order-item:hover { " +
                                  "  background: #f9f9f9; " +
                                  "  border-color: #999; " +
                                  "}" +
                                  ".section-order-item .dashicons { " +
                                  "  margin-right: 8px; " +
                                  "  color: #666; " +
                                  "}" +
                                  ".section-order-placeholder { " +
                                  "  background: #f0f0f0; " +
                                  "  border: 2px dashed #ccc; " +
                                  "  height: 40px; " +
                                  "  margin: 5px 0; " +
                                  "  border-radius: 4px; " +
                                  "}")
                            .appendTo("head");
                            
                    })(jQuery);
                ');
            });
        });

        // Get section order for priorities (shared across all customizer sections)
        $sectionOrder = get_theme_mod('homepage_section_order', 'hero,new_drops,featured_products,new_arrival');
        $sections = explode(',', $sectionOrder);
        $priorities = [];
        
        // Assign priorities based on order (starting from 20)
        foreach ($sections as $index => $section) {
            $priorities[$section] = 20 + ($index * 5);
        }

        // Register Customizer options
        \add_action('customize_register', function($wp_customize) use ($priorities) {
            
            // Section
            $wp_customize->add_section('hero_section', [
                'title'    => __('Homepage Hero', 'sage'),
                'priority' => $priorities['hero'] ?? 25,
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
                'type'    => 'textarea',
                'label'   => __('Heading', 'sage'),
                'section' => 'hero_section',
                'description' => __('Use line breaks or &lt;br&gt; tags to add line breaks. Each line will be on a new line.', 'sage'),
            ]);

            // Heading Line Break Mode
            $wp_customize->add_setting('hero_heading_line_breaks', [
                'default' => 'manual',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('hero_heading_line_breaks', [
                'type'    => 'select',
                'label'   => __('Heading Line Break Mode', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'manual' => 'Manual (use line breaks or &lt;br&gt; tags)',
                    'each-word' => 'Each Word on New Line',
                    'preserve-spaces' => 'Preserve Line Breaks from Text',
                ],
                'description' => __('How to handle line breaks in the heading', 'sage'),
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

            // Font Sizes
            $wp_customize->add_setting('hero_heading_font_size', [
                'default' => 'clamp(32px, 6vw, 64px)',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_heading_font_size', [
                'type'    => 'text',
                'label'   => __('Heading Font Size', 'sage'),
                'section' => 'hero_section',
                'description' => __('e.g., 48px, 3rem, or clamp(32px, 6vw, 64px) for responsive', 'sage'),
            ]);

            $wp_customize->add_setting('hero_subheading_font_size', [
                'default' => 'clamp(16px, 2.2vw, 20px)',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_subheading_font_size', [
                'type'    => 'text',
                'label'   => __('Subheading Font Size', 'sage'),
                'section' => 'hero_section',
                'description' => __('e.g., 18px, 1.25rem, or clamp(16px, 2.2vw, 20px) for responsive', 'sage'),
            ]);

            $wp_customize->add_setting('hero_cta_font_size', [
                'default' => '1rem',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_cta_font_size', [
                'type'    => 'text',
                'label'   => __('Button Font Size', 'sage'),
                'section' => 'hero_section',
                'description' => __('e.g., 16px, 1rem, or 1.125rem', 'sage'),
            ]);

            // Font Weights
            $wp_customize->add_setting('hero_heading_font_weight', [
                'default' => '800',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_heading_font_weight', [
                'type'    => 'select',
                'label'   => __('Heading Font Weight', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    '100' => '100 (Thin)',
                    '200' => '200 (Extra Light)',
                    '300' => '300 (Light)',
                    '400' => '400 (Normal)',
                    '500' => '500 (Medium)',
                    '600' => '600 (Semi Bold)',
                    '700' => '700 (Bold)',
                    '800' => '800 (Extra Bold)',
                    '900' => '900 (Black)',
                ],
            ]);

            $wp_customize->add_setting('hero_subheading_font_weight', [
                'default' => '400',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_subheading_font_weight', [
                'type'    => 'select',
                'label'   => __('Subheading Font Weight', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    '100' => '100 (Thin)',
                    '200' => '200 (Extra Light)',
                    '300' => '300 (Light)',
                    '400' => '400 (Normal)',
                    '500' => '500 (Medium)',
                    '600' => '600 (Semi Bold)',
                    '700' => '700 (Bold)',
                    '800' => '800 (Extra Bold)',
                    '900' => '900 (Black)',
                ],
            ]);

            $wp_customize->add_setting('hero_cta_font_weight', [
                'default' => '600',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_cta_font_weight', [
                'type'    => 'select',
                'label'   => __('Button Font Weight', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    '100' => '100 (Thin)',
                    '200' => '200 (Extra Light)',
                    '300' => '300 (Light)',
                    '400' => '400 (Normal)',
                    '500' => '500 (Medium)',
                    '600' => '600 (Semi Bold)',
                    '700' => '700 (Bold)',
                    '800' => '800 (Extra Bold)',
                    '900' => '900 (Black)',
                ],
            ]);

            // Button Font Family
            $wp_customize->add_setting('hero_cta_font_family', [
                'default' => 'Hiragino Sans GB',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_cta_font_family', [
                'type'    => 'select',
                'label'   => __('Button Font Family', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'Hiragino Sans GB' => 'Hiragino Sans GB',
                    'Noto Serif Display' => 'Noto Serif Display (Regular)',
                    'Noto Serif Display Italic' => 'Noto Serif Display (Italic)',
                    'Noto Serif Display Condensed' => 'Noto Serif Display (Condensed)',
                    'Arial' => 'Arial',
                    'Helvetica' => 'Helvetica',
                    'Georgia' => 'Georgia',
                    'Times New Roman' => 'Times New Roman',
                    'Courier New' => 'Courier New',
                    'Verdana' => 'Verdana',
                    'Trebuchet MS' => 'Trebuchet MS',
                    'Impact' => 'Impact',
                    'Comic Sans MS' => 'Comic Sans MS',
                    'Palatino' => 'Palatino',
                    'Garamond' => 'Garamond',
                    'Bookman' => 'Bookman',
                    'Avant Garde' => 'Avant Garde',
                    'Verdana' => 'Verdana',
                    'Lucida Grande' => 'Lucida Grande',
                    'Lucida Sans Unicode' => 'Lucida Sans Unicode',
                    'Tahoma' => 'Tahoma',
                    'Geneva' => 'Geneva',
                    'sans-serif' => 'Sans Serif',
                    'serif' => 'Serif',
                    'monospace' => 'Monospace',
                    'cursive' => 'Cursive',
                    'fantasy' => 'Fantasy',
                    '-apple-system' => 'Apple System',
                ],
            ]);

            // Button Color
            $wp_customize->add_setting('hero_cta_color', [
                'default' => '#c4b5a8',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'hero_cta_color', [
                'label'   => __('Button Background Color', 'sage'),
                'section' => 'hero_section',
                'description' => __('Choose the background color for the CTA button', 'sage'),
            ]));

            // Background image
            $wp_customize->add_setting('hero_bg_image', ['transport' => 'refresh']);
            $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, 'hero_bg_image', [
                'label'    => __('Background image', 'sage'),
                'section'  => 'hero_section',
                'settings' => 'hero_bg_image',
            ]));

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

            // Font Selection - Heading
            $wp_customize->add_setting('hero_heading_font_family', [
                'default' => 'Hiragino Sans GB',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_heading_font_family', [
                'type'    => 'select',
                'label'   => __('Heading Font Family', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'Hiragino Sans GB' => 'Hiragino Sans GB',
                    'Noto Serif Display' => 'Noto Serif Display (Regular)',
                    'Noto Serif Display Italic' => 'Noto Serif Display (Italic)',
                    'Noto Serif Display Condensed' => 'Noto Serif Display (Condensed)',
                    'Arial' => 'Arial',
                    'Helvetica' => 'Helvetica',
                    'Georgia' => 'Georgia',
                    'Times New Roman' => 'Times New Roman',
                    'Courier New' => 'Courier New',
                    'Verdana' => 'Verdana',
                    'Trebuchet MS' => 'Trebuchet MS',
                    'Impact' => 'Impact',
                    'Comic Sans MS' => 'Comic Sans MS',
                    'Palatino' => 'Palatino',
                    'Garamond' => 'Garamond',
                    'Bookman' => 'Bookman',
                    'Avant Garde' => 'Avant Garde',
                    'system-ui' => 'System UI',
                    '-apple-system' => 'Apple System',
                ],
            ]);

            // Font Selection - Subheading
            $wp_customize->add_setting('hero_subheading_font_family', [
                'default' => 'Hiragino Sans GB',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_subheading_font_family', [
                'type'    => 'select',
                'label'   => __('Subheading Font Family', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'Hiragino Sans GB' => 'Hiragino Sans GB',
                    'Noto Serif Display' => 'Noto Serif Display (Regular)',
                    'Noto Serif Display Italic' => 'Noto Serif Display (Italic)',
                    'Noto Serif Display Condensed' => 'Noto Serif Display (Condensed)',
                    'Arial' => 'Arial',
                    'Helvetica' => 'Helvetica',
                    'Georgia' => 'Georgia',
                    'Times New Roman' => 'Times New Roman',
                    'Courier New' => 'Courier New',
                    'Verdana' => 'Verdana',
                    'Trebuchet MS' => 'Trebuchet MS',
                    'Impact' => 'Impact',
                    'Comic Sans MS' => 'Comic Sans MS',
                    'Palatino' => 'Palatino',
                    'Garamond' => 'Garamond',
                    'Bookman' => 'Bookman',
                    'Avant Garde' => 'Avant Garde',
                    'system-ui' => 'System UI',
                    '-apple-system' => 'Apple System',
                ],
            ]);

            // Grid Layout - Column Position
            $wp_customize->add_setting('hero_grid_column', [
                'default' => 'left',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('hero_grid_column', [
                'type'    => 'select',
                'label'   => __('Text Column Position', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'left' => 'Left Column',
                    'right' => 'Right Column',
                ],
                'description' => __('Choose which column the text appears in (grid layout)', 'sage'),
            ]);

            // Grid Layout - Vertical Alignment
            $wp_customize->add_setting('hero_grid_vertical', [
                'default' => 'center',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('hero_grid_vertical', [
                'type'    => 'select',
                'label'   => __('Vertical Alignment', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'top' => 'Top',
                    'center' => 'Center',
                    'bottom' => 'Bottom',
                ],
                'description' => __('Vertical position within the column', 'sage'),
            ]);

            // Grid Layout - Horizontal Alignment
            $wp_customize->add_setting('hero_grid_horizontal', [
                'default' => 'center',
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('hero_grid_horizontal', [
                'type'    => 'select',
                'label'   => __('Horizontal Alignment', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'flex-start' => 'Left (Flex Start)',
                    'center' => 'Center',
                    'flex-end' => 'Right (Flex End)',
                ],
                'description' => __('Horizontal alignment of text within the column', 'sage'),
            ]);

            // Background Position
            $wp_customize->add_setting('hero_bg_position', [
                'default' => 'center center',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_bg_position', [
                'type'    => 'select',
                'label'   => __('Background Image Position', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'left top' => 'Left Top',
                    'center top' => 'Center Top',
                    'right top' => 'Right Top',
                    'left center' => 'Left Center',
                    'center center' => 'Center Center',
                    'right center' => 'Right Center',
                    'left bottom' => 'Left Bottom',
                    'center bottom' => 'Center Bottom',
                    'right bottom' => 'Right Bottom',
                ],
                'description' => __('Controls where the background image is positioned', 'sage'),
            ]);

            // Background Position (Mobile)
            $wp_customize->add_setting('hero_bg_position_mobile', [
                'default' => 'center center',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('hero_bg_position_mobile', [
                'type'    => 'select',
                'label'   => __('Background Image Position (Mobile)', 'sage'),
                'section' => 'hero_section',
                'choices' => [
                    'left top' => 'Left Top',
                    'center top' => 'Center Top',
                    'right top' => 'Right Top',
                    'left center' => 'Left Center',
                    'center center' => 'Center Center',
                    'right center' => 'Right Center',
                    'left bottom' => 'Left Bottom',
                    'center bottom' => 'Center Bottom',
                    'right bottom' => 'Right Bottom',
                ],
                'description' => __('Background position for mobile devices (≤768px)', 'sage'),
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

        // New Drops Carousel Section
        \add_action('customize_register', function($wp_customize) {
            $wp_customize->add_section('new_drops_section', [
                'title' => __('New Drops Carousel', 'sage'),
                'priority' => $priorities['new_drops'] ?? 26,
            ]);

            // Enable/disable New Drops
            $wp_customize->add_setting('new_drops_enable', [
                'default' => false,
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('new_drops_enable', [
                'type'    => 'checkbox',
                'label'   => __('Enable New Drops Carousel', 'sage'),
                'section' => 'new_drops_section',
            ]);

            // Section Title
            $wp_customize->add_setting('new_drops_title', [
                'default' => 'NEW DROPS',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('new_drops_title', [
                'type'    => 'text',
                'label'   => __('Section Title', 'sage'),
                'section' => 'new_drops_section',
            ]);

            // Section Subtitle
            $wp_customize->add_setting('new_drops_subtitle', [
                'default' => 'Fresh styles just dropped',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('new_drops_subtitle', [
                'type'    => 'text',
                'label'   => __('Section Subtitle', 'sage'),
                'section' => 'new_drops_section',
            ]);

            // Number of slides
            $wp_customize->add_setting('new_drops_count', [
                'default' => 3,
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('new_drops_count', [
                'type'        => 'number',
                'label'       => __('Number of Slides', 'sage'),
                'section'     => 'new_drops_section',
                'input_attrs' => ['min' => 1, 'max' => 10, 'step' => 1],
            ]);

            // Auto-play
            $wp_customize->add_setting('new_drops_autoplay', [
                'default' => true,
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('new_drops_autoplay', [
                'type'    => 'checkbox',
                'label'   => __('Auto-play carousel', 'sage'),
                'section' => 'new_drops_section',
            ]);

            // Auto-play speed
            $wp_customize->add_setting('new_drops_autoplay_speed', [
                'default' => 5000,
                'transport' => 'refresh',
            ]);
            $wp_customize->add_control('new_drops_autoplay_speed', [
                'type'        => 'number',
                'label'       => __('Auto-play Speed (milliseconds)', 'sage'),
                'section'     => 'new_drops_section',
                'input_attrs' => ['min' => 2000, 'max' => 10000, 'step' => 500],
            ]);

            // Carousel height
            $wp_customize->add_setting('new_drops_height', [
                'default' => '400px',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('new_drops_height', [
                'type'    => 'text',
                'label'   => __('Carousel Height (e.g. 400px or 50vh)', 'sage'),
                'section' => 'new_drops_section',
            ]);

            // Image opacity
            $wp_customize->add_setting('new_drops_image_opacity', [
                'default' => 1,
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('new_drops_image_opacity', [
                'type'        => 'number',
                'label'       => __('Image Opacity (0–1)', 'sage'),
                'section'     => 'new_drops_section',
                'input_attrs' => ['min' => 0, 'max' => 1, 'step' => 0.05],
            ]);

            // Title gradient start color
            $wp_customize->add_setting('new_drops_title_gradient_start', [
                'default' => '#000000',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'new_drops_title_gradient_start', [
                'label'    => __('Title Gradient Start Color', 'sage'),
                'section'  => 'new_drops_section',
                'settings' => 'new_drops_title_gradient_start',
            ]));

            // Title gradient end color
            $wp_customize->add_setting('new_drops_title_gradient_end', [
                'default' => '#000000',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'new_drops_title_gradient_end', [
                'label'    => __('Title Gradient End Color', 'sage'),
                'section'  => 'new_drops_section',
                'settings' => 'new_drops_title_gradient_end',
            ]));

            // Section top margin
            $wp_customize->add_setting('new_drops_margin_top', [
                'default' => '60',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('new_drops_margin_top', [
                'type'        => 'number',
                'label'       => __('Top Margin (pixels)', 'sage'),
                'section'     => 'new_drops_section',
                'input_attrs' => ['min' => 0, 'max' => 200, 'step' => 5],
            ]);

            // Section bottom margin
            $wp_customize->add_setting('new_drops_margin_bottom', [
                'default' => '60',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('new_drops_margin_bottom', [
                'type'        => 'number',
                'label'       => __('Bottom Margin (pixels)', 'sage'),
                'section'     => 'new_drops_section',
                'input_attrs' => ['min' => 0, 'max' => 200, 'step' => 5],
            ]);

            // Header bottom margin
            $wp_customize->add_setting('new_drops_header_margin', [
                'default' => '40',
                'transport' => 'postMessage',
            ]);
            $wp_customize->add_control('new_drops_header_margin', [
                'type'        => 'number',
                'label'       => __('Header Bottom Margin (pixels)', 'sage'),
                'section'     => 'new_drops_section',
                'input_attrs' => ['min' => 0, 'max' => 100, 'step' => 5],
            ]);

            // Add settings for each slide
            for ($i = 1; $i <= 10; $i++) {
                // Slide Image
                $wp_customize->add_setting("new_drops_slide_{$i}_image", [
                    'transport' => 'refresh',
                ]);
                $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, "new_drops_slide_{$i}_image", [
                    'label'    => sprintf(__('Slide %d Image', 'sage'), $i),
                    'section'  => 'new_drops_section',
                    'settings' => "new_drops_slide_{$i}_image",
                ]));

                // Slide URL
                $wp_customize->add_setting("new_drops_slide_{$i}_url", [
                    'default' => '',
                    'transport' => 'refresh',
                    'sanitize_callback' => 'esc_url_raw',
                ]);
                $wp_customize->add_control("new_drops_slide_{$i}_url", [
                    'type'    => 'url',
                    'label'   => sprintf(__('Slide %d URL', 'sage'), $i),
                    'section' => 'new_drops_section',
                ]);

                // Button Text
                $wp_customize->add_setting("new_drops_slide_{$i}_button_text", [
                    'default' => 'SHOP NOW',
                    'transport' => 'postMessage',
                ]);
                $wp_customize->add_control("new_drops_slide_{$i}_button_text", [
                    'type'    => 'text',
                    'label'   => sprintf(__('Slide %d Button Text', 'sage'), $i),
                    'section' => 'new_drops_section',
                ]);

                // Button URL
                $wp_customize->add_setting("new_drops_slide_{$i}_button_url", [
                    'default' => '',
                    'transport' => 'refresh',
                    'sanitize_callback' => 'esc_url_raw',
                ]);
                $wp_customize->add_control("new_drops_slide_{$i}_button_url", [
                    'type'    => 'url',
                    'label'   => sprintf(__('Slide %d Button URL', 'sage'), $i),
                    'section' => 'new_drops_section',
                ]);

                // Button Position
                $wp_customize->add_setting("new_drops_slide_{$i}_button_position", [
                    'default' => 'center',
                    'transport' => 'postMessage',
                ]);
                $wp_customize->add_control("new_drops_slide_{$i}_button_position", [
                    'type'    => 'select',
                    'label'   => sprintf(__('Slide %d Button Position', 'sage'), $i),
                    'section' => 'new_drops_section',
                    'choices' => [
                        'top' => 'Top',
                        'center' => 'Center',
                        'bottom' => 'Bottom',
                    ],
                ]);

                // Show Button
                $wp_customize->add_setting("new_drops_slide_{$i}_show_button", [
                    'default' => true,
                    'transport' => 'postMessage',
                ]);
                $wp_customize->add_control("new_drops_slide_{$i}_show_button", [
                    'type'    => 'checkbox',
                    'label'   => sprintf(__('Show Button on Slide %d', 'sage'), $i),
                    'section' => 'new_drops_section',
                ]);
            }
        });

        // Featured Products Section
        \add_action('customize_register', function($wp_customize) use ($priorities) {
            $wp_customize->add_section('featured_products', [
                'title' => __('Featured Products', 'sage'),
                'priority' => $priorities['featured_products'] ?? 30,
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

            // Featured Products Gradient Type
            $wp_customize->add_setting('featured_products_gradient_type', [
                'default' => 'none',
                'sanitize_callback' => 'sanitize_text_field',
            ]);

            $wp_customize->add_control('featured_products_gradient_type', [
                'label' => __('Title Text Gradient Type', 'sage'),
                'description' => __('Apply a gradient to the section title text', 'sage'),
                'section' => 'featured_products',
                'type' => 'select',
                'choices' => [
                    'none' => __('None', 'sage'),
                    'linear' => __('Linear Gradient', 'sage'),
                    'radial' => __('Radial Gradient', 'sage'),
                ],
            ]);

            // Featured Products Gradient Start Color
            $wp_customize->add_setting('featured_products_gradient_start', [
                'default' => '#000000',
                'sanitize_callback' => 'sanitize_hex_color',
            ]);

            $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'featured_products_gradient_start', [
                'label' => __('Gradient Start Color', 'sage'),
                'section' => 'featured_products',
                'active_callback' => function($control) {
                    $gradient_type = $control->manager->get_setting('featured_products_gradient_type')->value();
                    return in_array($gradient_type, ['linear', 'radial']);
                },
            ]));

            // Featured Products Gradient End Color
            $wp_customize->add_setting('featured_products_gradient_end', [
                'default' => '#666666',
                'sanitize_callback' => 'sanitize_hex_color',
            ]);

            $wp_customize->add_control(new \WP_Customize_Color_Control($wp_customize, 'featured_products_gradient_end', [
                'label' => __('Gradient End Color', 'sage'),
                'section' => 'featured_products',
                'active_callback' => function($control) {
                    $gradient_type = $control->manager->get_setting('featured_products_gradient_type')->value();
                    return in_array($gradient_type, ['linear', 'radial']);
                },
            ]));

            // Featured Products Gradient Direction (for linear gradients)
            $wp_customize->add_setting('featured_products_gradient_direction', [
                'default' => 'to bottom',
                'sanitize_callback' => 'sanitize_text_field',
            ]);

            $wp_customize->add_control('featured_products_gradient_direction', [
                'label' => __('Gradient Direction', 'sage'),
                'section' => 'featured_products',
                'type' => 'select',
                'choices' => [
                    'to bottom' => __('Top to Bottom', 'sage'),
                    'to top' => __('Bottom to Top', 'sage'),
                    'to right' => __('Left to Right', 'sage'),
                    'to left' => __('Right to Left', 'sage'),
                    'to bottom right' => __('Top Left to Bottom Right', 'sage'),
                    'to bottom left' => __('Top Right to Bottom Left', 'sage'),
                    'to top right' => __('Bottom Left to Top Right', 'sage'),
                    'to top left' => __('Bottom Right to Top Left', 'sage'),
                    '45deg' => __('45 Degrees', 'sage'),
                    '90deg' => __('90 Degrees', 'sage'),
                    '135deg' => __('135 Degrees', 'sage'),
                    '180deg' => __('180 Degrees', 'sage'),
                ],
                'active_callback' => function($control) {
                    $gradient_type = $control->manager->get_setting('featured_products_gradient_type')->value();
                    return $gradient_type === 'linear';
                },
            ]);
        });

        // New Arrival Section
        \add_action('customize_register', function($wp_customize) use ($priorities) {
            $wp_customize->add_section('new_arrival_section', [
                'title' => __('New Arrival', 'sage'),
                'priority' => $priorities['new_arrival'] ?? 30,
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
        \add_action('customize_register', function($wp_customize) use ($priorities) {
            $wp_customize->add_section('footer_section', [
                'title' => __('Footer', 'sage'),
                'priority' => $priorities['footer'] ?? 35,
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

            // Logo image upload
            $wp_customize->add_setting('navbar_logo_image', [
                'default' => '',
                'transport' => 'refresh',
                'sanitize_callback' => 'esc_url_raw',
            ]);

            $wp_customize->add_control(new \WP_Customize_Image_Control($wp_customize, 'navbar_logo_image', [
                'label' => __('Logo Image', 'sage'),
                'section' => 'navbar_section',
                'description' => __('Upload your logo image (SVG recommended). If not set, will use default HEYGIRLS.svg', 'sage'),
            ]));

            // Logo text
            $wp_customize->add_setting('navbar_logo', [
                'default' => 'HEYGIRLSBKK',
                'transport' => 'postMessage',
            ]);

            $wp_customize->add_control('navbar_logo', [
                'label' => __('Logo Text (for alt text and center logo)', 'sage'),
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

    /**
     * Sanitize section order
     */
    public function sanitize_section_order($input)
    {
        $allowed_sections = ['hero', 'new_drops', 'featured_products', 'new_arrival'];
        $sections = explode(',', $input);
        $sanitized = [];
        
        foreach ($sections as $section) {
            $section = trim($section);
            if (in_array($section, $allowed_sections)) {
                $sanitized[] = $section;
            }
        }
        
        // Ensure all sections are included
        foreach ($allowed_sections as $section) {
            if (!in_array($section, $sanitized)) {
                $sanitized[] = $section;
            }
        }
        
        return implode(',', $sanitized);
    }
}

