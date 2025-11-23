<?php
/**
 * The Template for displaying order received page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

defined('ABSPATH') || exit;

// Get order ID and order object
$order_id = absint(get_query_var('order-received'));
$order = $order_id ? wc_get_order($order_id) : null;

// Load Sage/Acorn Blade view
echo view('woocommerce.checkout.thankyou', [
    'order' => $order,
])->render();



