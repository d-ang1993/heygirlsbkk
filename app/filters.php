<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

/**
 * Debug CartFlow detection
 */
add_action('wp_head', function () {
    global $post;
    
    if (isset($post)) {
        echo '<!-- CartFlow Debug: Post Type: ' . $post->post_type . ' -->';
        echo '<!-- CartFlow Debug: Post ID: ' . $post->ID . ' -->';
        echo '<!-- CartFlow Debug: CartFlow Functions: ';
        echo 'wcf_is_flow_checkout: ' . (function_exists('wcf_is_flow_checkout') ? 'exists' : 'missing') . ', ';
        echo 'wcf_is_flow_landing: ' . (function_exists('wcf_is_flow_landing') ? 'exists' : 'missing') . ', ';
        echo 'cartflows_is_flow_checkout: ' . (function_exists('cartflows_is_flow_checkout') ? 'exists' : 'missing') . ', ';
        echo 'cartflows_is_flow_landing: ' . (function_exists('cartflows_is_flow_landing') ? 'exists' : 'missing');
        echo ' -->';
    }
});

/**
 * CartFlow template overrides - comprehensive detection
 */
add_filter('template_include', function ($template) {
    global $post;
    
    // Check if this is a CartFlow page by post type
    if (isset($post) && $post->post_type === 'cartflows_step') {
        $custom_template = get_template_directory() . '/resources/views/template-cartflows.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    // Check if this is a CartFlow page using proper functions
    if (function_exists('wcf_is_flow_checkout') && wcf_is_flow_checkout()) {
        $custom_template = get_template_directory() . '/resources/views/template-cartflows.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    // Check if this is a CartFlow landing page
    if (function_exists('wcf_is_flow_landing') && wcf_is_flow_landing()) {
        $custom_template = get_template_directory() . '/resources/views/template-cartflows.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    // Alternative check for CartFlow pages
    if (function_exists('cartflows_is_flow_checkout') && cartflows_is_flow_checkout()) {
        $custom_template = get_template_directory() . '/resources/views/template-cartflows.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    if (function_exists('cartflows_is_flow_landing') && cartflows_is_flow_landing()) {
        $custom_template = get_template_directory() . '/resources/views/template-cartflows.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    // Check by post meta
    if (isset($post) && get_post_meta($post->ID, '_cartflows_template_type', true)) {
        $custom_template = get_template_directory() . '/resources/views/template-cartflows.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    return $template;
});

/**
 * Add CartFlow body class
 */
add_filter('body_class', function ($classes) {
    if (function_exists('wcf_is_flow_checkout') && wcf_is_flow_checkout()) {
        $classes[] = 'cartflow-page';
        $classes[] = 'cartflow-checkout';
    }
    
    if (function_exists('wcf_is_flow_landing') && wcf_is_flow_landing()) {
        $classes[] = 'cartflow-page';
        $classes[] = 'cartflow-landing';
    }
    
    // Alternative function names
    if (function_exists('cartflows_is_flow_checkout') && cartflows_is_flow_checkout()) {
        $classes[] = 'cartflow-page';
        $classes[] = 'cartflow-checkout';
    }
    
    if (function_exists('cartflows_is_flow_landing') && cartflows_is_flow_landing()) {
        $classes[] = 'cartflow-page';
        $classes[] = 'cartflow-landing';
    }
    
    return $classes;
});
