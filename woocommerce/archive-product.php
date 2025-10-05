<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

// Make sure WooCommerce functions are loaded
if (!function_exists('woocommerce_product_loop')) {
    wc_get_template_part('archive', 'product');
    return;
}

// Collect basic context
global $wp_query;

// Load Sage/Acorn Blade view
echo view('woocommerce.archive-product', [
    'products' => $wp_query->posts ?? [],
    'title'    => woocommerce_page_title(false),
])->render();
