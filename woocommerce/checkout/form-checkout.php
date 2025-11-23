<?php
/**
 * The Template for displaying checkout form page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

// Load Sage/Acorn Blade view
echo view('woocommerce.checkout.form-checkout')->render();

