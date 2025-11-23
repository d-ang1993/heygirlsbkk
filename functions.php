<?php

use Roots\Acorn\Application;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'sage'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Register The Bootloader
|--------------------------------------------------------------------------
|
| The first thing we will do is schedule a new Acorn application container
| to boot when WordPress is finished loading the theme. The application
| serves as the "glue" for all the components of Laravel and is
| the IoC container for the system binding all of the various parts.
|
*/

Application::configure()
    ->withProviders([
        App\Providers\ThemeServiceProvider::class,
        App\Providers\CustomizerServiceProvider::class,
    ])
    ->boot();

/*
|--------------------------------------------------------------------------
| Register Sage Theme Files
|--------------------------------------------------------------------------
|
| Out of the box, Sage ships with categorically named theme files
| containing common functionality and setup to be bootstrapped with your
| theme. Simply add (or remove) files from the array below to change what
| is registered alongside Sage.
|
*/

collect(['setup', 'filters'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });
    // add_theme_support('woocommerce');

// Custom WooCommerce template handling
add_action('template_redirect', function() {
    if (is_shop() || is_product_category() || is_product_tag()) {
        if (is_singular('product')) {
            // woocommerce_content();
        } else {
            // For ANY product archive - load our custom template directly
            $template_path = get_template_directory() . '/woocommerce/archive-product.php';
            if (file_exists($template_path)) {
                include $template_path;
                exit;
            }
        }
    }
});

// Handle WooCommerce registration with first and last name
add_action('woocommerce_created_customer', 'save_registration_names');
function save_registration_names($customer_id) {
    if (isset($_POST['first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));
        update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['first_name']));
        update_user_meta($customer_id, 'shipping_first_name', sanitize_text_field($_POST['first_name']));
    }
    
    if (isset($_POST['last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));
        update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['last_name']));
        update_user_meta($customer_id, 'shipping_last_name', sanitize_text_field($_POST['last_name']));
    }
}

// Apply orderby from URL to WooCommerce product query
add_action('woocommerce_product_query', 'apply_url_orderby_to_product_query');
function apply_url_orderby_to_product_query($query) {
    if (!is_admin() && (is_shop() || is_product_category() || is_product_tag())) {
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
        
        if (!empty($orderby)) {
            switch ($orderby) {
                case 'popularity':
                    $query->set('meta_key', 'total_sales');
                    $query->set('orderby', 'meta_value_num');
                    $query->set('order', 'DESC');
                    break;
                case 'rating':
                    $query->set('meta_key', '_wc_average_rating');
                    $query->set('orderby', 'meta_value_num');
                    $query->set('order', 'DESC');
                    break;
                case 'date':
                    $query->set('orderby', 'date');
                    $query->set('order', 'DESC');
                    break;
                case 'price':
                    $query->set('meta_key', '_price');
                    $query->set('orderby', 'meta_value_num');
                    $query->set('order', 'ASC');
                    break;
                case 'price-desc':
                    $query->set('meta_key', '_price');
                    $query->set('orderby', 'meta_value_num');
                    $query->set('order', 'DESC');
                    break;
                case 'menu_order':
                default:
                    $query->set('orderby', 'menu_order');
                    $query->set('order', 'ASC');
                    break;
            }
        }
    }
}

// Validate first and last name on registration
add_action('woocommerce_register_post', 'validate_registration_names', 10, 3);
function validate_registration_names($username, $email, $validation_errors) {
    if (empty($_POST['first_name'])) {
        $validation_errors->add('first_name_error', __('First name is required.', 'woocommerce'));
    }
    
    if (empty($_POST['last_name'])) {
        $validation_errors->add('last_name_error', __('Last name is required.', 'woocommerce'));
    }
    
    // Check if email already exists
    if (!empty($_POST['email'])) {
        if (email_exists($_POST['email'])) {
            $validation_errors->add('email_exists_error', __('Email already exists. Please try logging in or use a different email address.', 'woocommerce'));
        }
    }
}

// Add custom error notices for registration
add_action('woocommerce_register_post', 'add_custom_registration_errors', 20, 3);
function add_custom_registration_errors($username, $email, $validation_errors) {
    if ($validation_errors->get_error_codes()) {
        foreach ($validation_errors->get_error_codes() as $code) {
            $message = $validation_errors->get_error_message($code);
            
            // Customize email exists message
            if ($code === 'email_exists_error' || strpos($message, 'email') !== false && strpos($message, 'exists') !== false) {
                wc_add_notice('<strong>Email already exists!</strong> This email is already registered. Please <a href="' . add_query_arg('action', 'login', wc_get_page_permalink('myaccount')) . '">try logging in</a> or use a different email address.', 'error');
            } else {
                wc_add_notice($message, 'error');
            }
        }
    }
}

// WooCommerce template override
add_filter('woocommerce_locate_template', 'override_woocommerce_templates', 10, 3);
function override_woocommerce_templates($template, $template_name, $template_path) {
    // Define the templates we want to override
    $custom_templates = array(
        'myaccount/form-login.php',
        'myaccount/my-account.php',
        'myaccount/navigation.php',
        'myaccount/form-edit-address.php',
        'myaccount/edit-address.php'
    );
    
    // Check if this is one of our custom templates
    if (in_array($template_name, $custom_templates)) {
        $custom_template = get_template_directory() . '/woocommerce/' . $template_name;
        
        // If our custom template exists, use it
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    // Return the original template
    return $template;
}

// Handle edit-address form submission
add_action('template_redirect', 'handle_edit_address_submission');
function handle_edit_address_submission() {
    if (!is_wc_endpoint_url('edit-address') || !isset($_POST['save_address'])) {
        return;
    }
    
    if (!wp_verify_nonce($_POST['woocommerce-edit-address-nonce'], 'woocommerce-edit_address')) {
        return;
    }
    
    $load_address = '';
    if (isset($_GET['address']) && in_array($_GET['address'], array('billing', 'shipping'))) {
        $load_address = sanitize_text_field($_GET['address']);
    }
    
    if (empty($load_address)) {
        return;
    }
    
    $user_id = get_current_user_id();
    $address_fields = WC()->countries->get_address_fields('', $load_address . '_');
    
    // Validate and save each field
    foreach ($address_fields as $key => $field) {
        if (isset($_POST[$key])) {
            $value = sanitize_text_field($_POST[$key]);
            
            // Validate required fields
            if (!empty($field['required']) && empty($value)) {
                wc_add_notice(sprintf(__('%s is required.', 'woocommerce'), $field['label']), 'error');
                continue;
            }
            
            // Save the field
            update_user_meta($user_id, $key, $value);
        }
    }
    
    // Check for any errors
    if (wc_notice_count('error') === 0) {
        wc_add_notice(__('Address changed successfully.', 'woocommerce'), 'success');
        wp_redirect(wc_get_account_endpoint_url('edit-address'));
        exit;
    }
}

// AJAX handler for loading account sections
add_action('wp_ajax_load_account_section', 'handle_load_account_section');
add_action('wp_ajax_nopriv_load_account_section', 'handle_load_account_section');

function handle_load_account_section() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'account_section_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_die('User not logged in');
    }
    
    $section = sanitize_text_field($_POST['section']);
    
    ob_start();
    
    switch ($section) {
        case 'dashboard':
            ?>
            <div class="dashboard-info">
              <h2>Account Overview</h2>
              <p>Use the navigation menu on the left to manage different aspects of your account.</p>
            </div>
            <?php
            break;
            
        case 'addresses':
            include get_template_directory() . '/woocommerce/myaccount/form-edit-address.php';
            break;
            
        case 'orders':
            ?>
            <div class="orders-section">
              <div class="page-header">
                <h1>My Orders</h1>
                <p>Track and view your order history</p>
              </div>
              <div class="orders-content">
                <div class="coming-soon-card">
                  <div class="coming-soon-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                      <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg>
                  </div>
                  <h3>Orders Coming Soon</h3>
                  <p>We're working on bringing you a comprehensive order management system where you can track, view, and manage all your purchases.</p>
                </div>
              </div>
            </div>
            <?php
            break;
            
        case 'account':
            ?>
            <div class="account-section">
              <div class="page-header">
                <h1>Account Details</h1>
                <p>Update your personal information</p>
              </div>
              <div class="account-content">
                <div class="coming-soon-card">
                  <div class="coming-soon-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                  </div>
                  <h3>Account Details Coming Soon</h3>
                  <p>We're building a comprehensive account management system where you can update your profile, change passwords, and manage your preferences.</p>
                </div>
              </div>
            </div>
            <?php
            break;
            
        case 'downloads':
            ?>
            <div class="downloads-section">
              <div class="page-header">
                <h1>Downloads</h1>
                <p>Access your downloadable files</p>
              </div>
              <div class="downloads-content">
                <div class="coming-soon-card">
                  <div class="coming-soon-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                      <polyline points="7,10 12,15 17,10"></polyline>
                      <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                  </div>
                  <h3>Downloads Coming Soon</h3>
                  <p>We're working on a digital downloads system where you can access your purchased files, e-books, and digital content.</p>
                </div>
              </div>
            </div>
            <?php
            break;
            
        case 'payment':
            ?>
            <div class="payment-section">
              <div class="page-header">
                <h1>Payment Methods</h1>
                <p>Manage your saved payment options</p>
              </div>
              <div class="payment-content">
                <div class="coming-soon-card">
                  <div class="coming-soon-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                      <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                  </div>
                  <h3>Payment Methods Coming Soon</h3>
                  <p>We're building a secure payment management system where you can add, edit, and manage your saved payment methods for faster checkout.</p>
                </div>
              </div>
            </div>
            <?php
            break;
            
        default:
            echo '<div class="error-message"><p>Section not found</p></div>';
    }
    
    $content = ob_get_clean();
    
    wp_send_json_success($content);
}

// AJAX handler for loading address forms
add_action('wp_ajax_load_address_form', 'handle_load_address_form');
add_action('wp_ajax_nopriv_load_address_form', 'handle_load_address_form');

function handle_load_address_form() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'address_form_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_die('User not logged in');
    }
    
    $address_type = sanitize_text_field($_POST['address_type']);
    
    if (!in_array($address_type, ['billing', 'shipping'])) {
        wp_die('Invalid address type');
    }
    
    ob_start();
    ?>
    <div class="address-edit">
      <div class="page-header">
        <h1><?php echo $address_type === 'billing' ? 'Edit Billing Address' : 'Edit Shipping Address'; ?></h1>
        <p>Update your <?php echo esc_html($address_type); ?> details below.</p>
        <a href="#" class="back-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15,18 9,12 15,6"></polyline>
          </svg>
          Back to Addresses
        </a>
      </div>

      <form method="post" class="woocommerce-EditAddressForm edit-address-form">
        <?php
          do_action("woocommerce_before_edit_address_form_{$address_type}");
          foreach (wc()->countries->get_address_fields('', $address_type . '_') as $key => $field) {
            woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, get_user_meta(get_current_user_id(), $key, true)));
          }
          do_action("woocommerce_after_edit_address_form_{$address_type}");
        ?>
        <?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
        <input type="hidden" name="address_type" value="<?php echo esc_attr($address_type); ?>">
        <button type="submit" name="save_address" class="btn-primary"><?php esc_html_e('Save address', 'woocommerce'); ?></button>
      </form>
    </div>
    <?php
    
    $content = ob_get_clean();
    
    wp_send_json_success($content);
}

// AJAX handler to get states for a country
add_action('wp_ajax_get_states_for_country', 'handle_get_states_for_country');
add_action('wp_ajax_nopriv_get_states_for_country', 'handle_get_states_for_country');

function handle_get_states_for_country() {
    check_ajax_referer('checkout-nonce', 'nonce');
    
    $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
    
    if (empty($country)) {
        wp_send_json_error(array('message' => 'Country code is required'));
        return;
    }
    
    $states = WC()->countries->get_states($country);
    
    $states_array = array();
    if (!empty($states) && is_array($states)) {
        foreach ($states as $key => $label) {
            $states_array[] = array(
                'key' => $key,
                'label' => $label
            );
        }
    }
    
    wp_send_json_success(array(
        'states' => $states_array,
        'has_states' => !empty($states_array)
    ));
}

// WooCommerce Cart Fragments Support
add_filter('woocommerce_add_to_cart_fragments', 'add_to_cart_fragments');

function add_to_cart_fragments($fragments) {
    // Update bag count
    $fragments['.bag-count'] = '<span class="bag-count">' . WC()->cart->get_cart_contents_count() . '</span>';
    
    // Update cart total if needed
    $fragments['.cart-total'] = '<span class="cart-total">' . WC()->cart->get_cart_total() . '</span>';
    
    return $fragments;
}

// Enable WooCommerce AJAX for add to cart
add_action('wp_enqueue_scripts', 'enqueue_woocommerce_ajax_scripts');

function enqueue_woocommerce_ajax_scripts() {
    if (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_shop() || is_product())) {
        wp_enqueue_script('wc-add-to-cart');
        wp_enqueue_script('wc-cart-fragments');
    }
}

/**
 * Enable WooCommerce AJAX Add to Cart and Cart Fragments (safe, non-duplicate)
 */
add_action('wp_enqueue_scripts', 'xircus_enqueue_woocommerce_ajax_scripts');

// ✅ Add product attributes to JavaScript for all product pages
add_action('wp_footer', 'add_product_attributes_to_js');
function add_product_attributes_to_js() {
    if (function_exists('is_product') && is_product()) {
        global $product;
        
        if ($product && $product->is_type('variable')) {
            $attributes = $product->get_variation_attributes();
            $availableAttributes = [];
            
            if (isset($attributes) && is_array($attributes)) {
                foreach ($attributes as $attrName => $attrValues) {
                    // Remove 'pa_' prefix if it exists for cleaner attribute name
                    $cleanName = str_replace('pa_', '', $attrName);
                    $availableAttributes[] = $cleanName;
                }
            }
            
            echo '<script>';
            echo 'console.log("🚀 Setting attributes from functions.php:", ' . json_encode($availableAttributes) . ');';
            echo 'window.productAttributes = ' . json_encode($availableAttributes) . ';';
            echo '</script>';
        }
    }
}

function xircus_enqueue_woocommerce_ajax_scripts() {
    // Only load on WooCommerce-related pages
    if (function_exists('is_woocommerce') && class_exists('WooCommerce')) {

        // ✅ Enqueue WooCommerce core scripts if not already loaded
        if (!wp_script_is('wc-add-to-cart', 'enqueued')) {
            wp_enqueue_script('wc-add-to-cart');
        }
        if (!wp_script_is('wc-cart-fragments', 'enqueued')) {
            wp_enqueue_script('wc-cart-fragments');
        }

        // ✅ Localize parameters for wc-add-to-cart.js (native support)
        if (wp_script_is('wc-add-to-cart', 'enqueued')) {
            wp_localize_script('wc-add-to-cart', 'wc_add_to_cart_params', array(
                'ajax_url' => WC()->ajax_url(),
                'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
                'i18n_view_cart' => esc_attr__('View cart', 'woocommerce'),
                'is_cart' => is_cart(),
                'cart_redirect_after_add' => get_option('woocommerce_cart_redirect_after_add')
            ));
        }

        // ✅ Localize your custom cart.js safely (for drawer, etc.)
        wp_localize_script('wc-add-to-cart', 'wc_cart_params', array(
            'ajax_url' => WC()->ajax_url(),
            'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
            'cart_url' => wc_get_cart_url(),
            'checkout_url' => wc_get_checkout_url(),
            'nonce' => wp_create_nonce('cart_drawer_nonce')
        ));
    }
}
// Cart Drawer AJAX Handlers

add_action('wp_ajax_woocommerce_get_cart_contents', 'handle_get_cart_contents');
add_action('wp_ajax_nopriv_woocommerce_get_cart_contents', 'handle_get_cart_contents');

function handle_get_cart_contents() {
    // Verify nonce (only for logged-in users)
    if (is_user_logged_in() && !wp_verify_nonce($_POST['nonce'], 'cart_drawer_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce not available');
        return;
    }

    $cart = WC()->cart;
    $cart_items = array();
    
    // Debug: Log cart contents
    error_log('Cart contents: ' . print_r($cart->get_cart(), true));
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $variation = '';
        
        // Get variation attributes if it's a variable product
        if ($product->is_type('variation')) {
            $variation_attributes = array();
            foreach ($product->get_variation_attributes() as $attribute_name => $attribute_value) {
                if ($attribute_value) {
                    $variation_attributes[] = wc_attribute_label(str_replace('attribute_', '', $attribute_name)) . ': ' . $attribute_value;
                }
            }
            $variation = implode(', ', $variation_attributes);
        }
        
        // Get parent product for productName
        $parent_product = $product->is_type('variation') ? wc_get_product($product->get_parent_id()) : $product;
        $product_name = $parent_product ? $parent_product->get_name() : $product->get_name();
        
        $cart_items[] = array(
            'cart_item_key' => $cart_item_key,
            'name' => $product->get_name(), // Full name with variation
            'productName' => $product_name, // Just the product name
            'variation' => $variation,
            'quantity' => $cart_item['quantity'],
            'price' => strip_tags(wc_price($product->get_price() * $cart_item['quantity'])),
            'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail')
        );
    }
    
    $response_data = array(
        'items' => $cart_items,
        'subtotal' => strip_tags($cart->get_cart_subtotal()),
        'total' => strip_tags($cart->get_cart_total()),
        'count' => $cart->get_cart_contents_count()
    );
    
    // Debug: Log response data
    error_log('Cart response data: ' . print_r($response_data, true));
    
    wp_send_json_success($response_data);
}

add_action('wp_ajax_woocommerce_update_cart_item', 'handle_update_cart_item');
add_action('wp_ajax_nopriv_woocommerce_update_cart_item', 'handle_update_cart_item');

function handle_update_cart_item() {
    // Verify nonce (only for logged-in users)
    if (is_user_logged_in() && !wp_verify_nonce($_POST['nonce'], 'cart_drawer_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce not available');
        return;
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    $cart = WC()->cart;
    $cart->set_quantity($cart_item_key, $quantity);
    
    wp_send_json_success('Cart item updated');
}

add_action('wp_ajax_woocommerce_remove_cart_item', 'handle_remove_cart_item');
add_action('wp_ajax_nopriv_woocommerce_remove_cart_item', 'handle_remove_cart_item');

function handle_remove_cart_item() {
    // Verify nonce (only for logged-in users)
    if (is_user_logged_in() && !wp_verify_nonce($_POST['nonce'], 'cart_drawer_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce not available');
        return;
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $cart = WC()->cart;
    $cart->remove_cart_item($cart_item_key);
    
    wp_send_json_success('Cart item removed');
}

add_action('wp_ajax_woocommerce_get_cart_count', 'handle_get_cart_count');
add_action('wp_ajax_nopriv_woocommerce_get_cart_count', 'handle_get_cart_count');

function handle_get_cart_count() {
    // Verify nonce (only for logged-in users)
    if (is_user_logged_in() && !wp_verify_nonce($_POST['nonce'], 'cart_drawer_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce not available');
        return;
    }

    $count = WC()->cart->get_cart_contents_count();
    wp_send_json_success($count);
}

// Clear cart cache when items are added/removed
add_action('woocommerce_add_to_cart', 'clear_cart_drawer_cache');
add_action('woocommerce_cart_item_removed', 'clear_cart_drawer_cache');
add_action('woocommerce_cart_item_restored', 'clear_cart_drawer_cache');
add_action('woocommerce_after_cart_item_quantity_update', 'clear_cart_drawer_cache');

/**
 * AJAX handler for Quick View modal
 */
function handle_get_quick_view() {
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Product ID is required']);
        return;
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product) {
        wp_send_json_error(['message' => 'Product not found']);
        return;
    }
    
    try {
        // Render the quick view component
        $html = \Roots\view('components.product-quick-view', [
            'product' => $product
        ])->render();
        
        wp_send_json_success(['html' => $html]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Error rendering quick view: ' . $e->getMessage()]);
    }
}

add_action('wp_ajax_get_quick_view', 'handle_get_quick_view');
add_action('wp_ajax_nopriv_get_quick_view', 'handle_get_quick_view');

/**
 * AJAX handler for getting product variations (used by Quick View)
 */
function handle_get_product_variations() {
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Product ID is required']);
        return;
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        wp_send_json_success(['variations' => []]);
        return;
    }
    
    $variations = $product->get_available_variations();
    $formatted_variations = [];
    
    foreach ($variations as $variation_data) {
        $variation_id = $variation_data['variation_id'];
        $variation = wc_get_product($variation_id);
        
        if (!$variation) {
            continue;
        }
        
        // Get variation image
        $image_id = $variation->get_image_id();
        $image_url = '';
        if ($image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
        } else {
            // Fallback to parent product image
            $image_id = $product->get_image_id();
            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
            }
        }
        
        $formatted_variations[] = [
            'id' => $variation_id,
            'variation_id' => $variation_id,
            'price' => $variation->get_price(),
            'regular_price' => $variation->get_regular_price(),
            'sale_price' => $variation->get_sale_price(),
            'price_html' => $variation->get_price_html(),
            'is_in_stock' => $variation->is_in_stock(),
            'stock_status' => $variation->get_stock_status(),
            'stock_quantity' => $variation->get_stock_quantity(),
            'attributes' => $variation_data['attributes'],
            'image_url' => $image_url,
        ];
    }
    
    wp_send_json_success(['variations' => $formatted_variations]);
}

add_action('wp_ajax_get_product_variations', 'handle_get_product_variations');
add_action('wp_ajax_nopriv_get_product_variations', 'handle_get_product_variations');

/**
 * AJAX handler for getting filtered products (used by Archive Filters)
 */
function handle_get_filtered_products() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'archive_filters_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }
    
    // Get filter parameters (handle both POST and $_REQUEST)
    $filter_colors = [];
    if (isset($_POST['filter_color'])) {
        $filter_colors = is_array($_POST['filter_color']) ? $_POST['filter_color'] : [$_POST['filter_color']];
    }
    
    $filter_categories = [];
    if (isset($_POST['filter_category'])) {
        $filter_categories = is_array($_POST['filter_category']) ? $_POST['filter_category'] : [$_POST['filter_category']];
    }
    
    $filter_sizes = [];
    if (isset($_POST['filter_size'])) {
        $filter_sizes = is_array($_POST['filter_size']) ? $_POST['filter_size'] : [$_POST['filter_size']];
    }
    
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';
    $current_category = isset($_POST['current_category']) ? sanitize_text_field($_POST['current_category']) : '';
    
    // Debug logging
    error_log('🔵 handle_get_filtered_products: current_category = ' . var_export($current_category, true));
    error_log('🔵 handle_get_filtered_products: filter_colors = ' . print_r($filter_colors, true));
    error_log('🔵 handle_get_filtered_products: filter_sizes = ' . print_r($filter_sizes, true));
    error_log('🔵 handle_get_filtered_products: $_POST = ' . print_r($_POST, true));
    
    // Build query args
    $args = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => get_option('posts_per_page', 12),
        'orderby' => $orderby,
        'meta_query' => [],
        'tax_query' => ['relation' => 'AND'],
    ];
    
    // Handle ordering
    switch ($orderby) {
        case 'popularity':
            $args['meta_key'] = 'total_sales';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'rating':
            $args['meta_key'] = '_wc_average_rating';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'date':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'price':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'menu_order';
            $args['order'] = 'ASC';
    }
    
    // CRITICAL: If on a category page, ALWAYS filter by that category
    // This must be applied FIRST, before any other filters
    // This ensures we only show products from the current collection, even when all filters are cleared
    if (!empty($current_category)) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $current_category,
            'operator' => 'IN',
        ];
        error_log('🔵 handle_get_filtered_products: Applied category filter: ' . $current_category);
    } else {
        error_log('⚠️ handle_get_filtered_products: No current_category provided!');
    }
    
    // Note: If current_category is set, we already filtered by it above
    // Additional category filters from the UI are subcategories within the collection
    // Since we're on a category page, we don't need to add additional category filters
    // as they would already be included in the current_category filter
    // The category filter in the UI is mainly for shop page navigation
    
    // Filter by colors (product attribute)
    if (!empty($filter_colors)) {
        $args['tax_query'][] = [
            'taxonomy' => 'pa_color',
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $filter_colors),
            'operator' => 'IN',
        ];
    }
    
    // Filter by sizes (product attribute)
    if (!empty($filter_sizes)) {
        // Try both pa_sizes and pa_size taxonomies
        $size_taxonomy = taxonomy_exists('pa_sizes') ? 'pa_sizes' : 'pa_size';
        $args['tax_query'][] = [
            'taxonomy' => $size_taxonomy,
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $filter_sizes),
            'operator' => 'IN',
        ];
    }
    
    // Execute query
    $query = new WP_Query($args);
    
    // Collect products
    $products = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            if ($product && is_a($product, 'WC_Product')) {
                $products[] = $product;
            }
        }
    }
    wp_reset_postdata();
    
    // Render product grid HTML
    ob_start();
    if (!empty($products)) {
        echo view('components.product-grid', [
            'title' => '',
            'products' => $products,
            'columns' => 4,
            'showDiscount' => true,
            'showQuickView' => true,
            'viewAllUrl' => null,
        ])->render();
    } else {
        echo '<p class="text-center text-gray-500 py-8">No products found matching your filters.</p>';
    }
    $products_html = ob_get_clean();
    
    wp_send_json_success([
        'products_html' => $products_html,
        'found_posts' => $query->found_posts,
    ]);
}

add_action('wp_ajax_get_filtered_products', 'handle_get_filtered_products');
add_action('wp_ajax_nopriv_get_filtered_products', 'handle_get_filtered_products');

function clear_cart_drawer_cache() {
    // This will be handled by the JavaScript cache invalidation
    // when the cart drawer is opened after modifications
}

// Display color swatches on product page
add_action( 'woocommerce_before_add_to_cart_form', 'show_color_swatches_on_product_page' );
function show_color_swatches_on_product_page() {
    global $product;

    if ( ! $product->is_type( 'variable' ) ) return;

    $attributes = $product->get_variation_attributes();

    if ( isset( $attributes['pa_color'] ) ) {
        echo '<div class="product-color-swatches">';
        echo '<h4>Color:</h4>';
        foreach ( $attributes['pa_color'] as $color ) {
            echo '<span class="color-swatch" style="background:' . esc_attr( $color ) . ';"></span>';
        }
        echo '</div>';
    }
}

// Basic styling for swatches
add_action( 'wp_head', function() {
    echo '<style>
        .product-color-swatches { margin-bottom: 10px; }
        .product-color-swatches .color-swatch {
            display:inline-block;
            width:25px;
            height:25px;
            border-radius:50%;
            border:1px solid #ccc;
            margin-right:6px;
            cursor:pointer;
        }
        .product-color-swatches .color-swatch:hover {
            border-color:#000;
        }
    </style>';
});

/**
 * Debug Cart Contents
 * Add this to see what's actually in the cart
 */
add_action('wp_head', function() {
    if (is_admin()) return;
    
    // Check if we're on a cart or checkout page
    if (is_cart() || is_checkout() || (isset($_GET['debug_cart']) && $_GET['debug_cart'] === '1')) {
        $cart = WC()->cart;
        
        if ($cart && !$cart->is_empty()) {
            echo '<!-- CART DEBUG START -->';
            echo '<!-- Cart Items Count: ' . $cart->get_cart_contents_count() . ' -->';
            
            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                echo '<!-- Cart Item: ' . $cart_item_key . ' -->';
                echo '<!-- Product ID: ' . $cart_item['product_id'] . ' -->';
                echo '<!-- Variation ID: ' . ($cart_item['variation_id'] ?? 'none') . ' -->';
                echo '<!-- Quantity: ' . $cart_item['quantity'] . ' -->';
                
                if (!empty($cart_item['variation'])) {
                    echo '<!-- Variations: ' . json_encode($cart_item['variation']) . ' -->';
                } else {
                    echo '<!-- Variations: none -->';
                }
                
                // Get product object
                $product = $cart_item['data'];
                if ($product) {
                    echo '<!-- Product Name: ' . $product->get_name() . ' -->';
                    echo '<!-- Product Type: ' . $product->get_type() . ' -->';
                    echo '<!-- In Stock: ' . ($product->is_in_stock() ? 'yes' : 'no') . ' -->';
                }
                
                echo '<!-- Cart Item Data: ' . json_encode($cart_item) . ' -->';
                echo '<!-- --- -->';
            }
            
            echo '<!-- CART DEBUG END -->';
        } else {
            echo '<!-- CART DEBUG: Cart is empty -->';
        }
    }
});

/**
 * Add cart debug to cart page content
 */
add_action('woocommerce_cart_contents', function() {
    if (isset($_GET['debug_cart']) && $_GET['debug_cart'] === '1') {
        $cart = WC()->cart;
        
        echo '<div style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 2px solid #333;">';
        echo '<h3>Cart Debug Information</h3>';
        
        if ($cart && !$cart->is_empty()) {
            echo '<p><strong>Cart Items Count:</strong> ' . $cart->get_cart_contents_count() . '</p>';
            
            foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
                echo '<div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0;">';
                echo '<h4>Cart Item: ' . $cart_item_key . '</h4>';
                echo '<p><strong>Product ID:</strong> ' . $cart_item['product_id'] . '</p>';
                echo '<p><strong>Variation ID:</strong> ' . ($cart_item['variation_id'] ?? 'none') . '</p>';
                echo '<p><strong>Quantity:</strong> ' . $cart_item['quantity'] . '</p>';
                
                if (!empty($cart_item['variation'])) {
                    echo '<p><strong>Variations:</strong> ' . json_encode($cart_item['variation'], JSON_PRETTY_PRINT) . '</p>';
                } else {
                    echo '<p><strong>Variations:</strong> none</p>';
                }
                
                $product = $cart_item['data'];
                if ($product) {
                    echo '<p><strong>Product Name:</strong> ' . $product->get_name() . '</p>';
                    echo '<p><strong>Product Type:</strong> ' . $product->get_type() . '</p>';
                    echo '<p><strong>In Stock:</strong> ' . ($product->is_in_stock() ? 'yes' : 'no') . '</p>';
                }
                
                echo '<details><summary>Full Cart Item Data</summary><pre>' . json_encode($cart_item, JSON_PRETTY_PRINT) . '</pre></details>';
                echo '</div>';
            }
        } else {
            echo '<p><strong>Cart is empty</strong></p>';
        }
        
        echo '</div>';
    }
});

add_action('woocommerce_add_to_cart', function() {
    if (WC()->cart && WC()->session) {
        WC()->cart->calculate_totals();
        WC()->session->set('cart', WC()->cart->get_cart());
        WC()->session->save_data();
    }
});

remove_action('woocommerce_before_cart_table', 'woocommerce_cart_totals_coupon_form');

/**
 * 🔇 Completely disable all WooCommerce system notices
 */
// add_filter('woocommerce_add_notice', '__return_false');
// add_filter('woocommerce_add_error', '__return_false');
// add_filter('woocommerce_add_success', '__return_false');
// add_filter('woocommerce_add_message', '__return_false');

/**
 * Prevent WooCommerce from printing any existing notices
 */
remove_action('woocommerce_before_shop_loop', 'wc_print_notices', 10);
remove_action('woocommerce_before_single_product', 'wc_print_notices', 10);
remove_action('woocommerce_before_cart', 'wc_print_notices', 10);
remove_action('woocommerce_before_checkout_form', 'wc_print_notices', 10);
remove_action('woocommerce_before_account_navigation', 'wc_print_notices', 10);
remove_action('woocommerce_before_my_account', 'wc_print_notices', 10);

/**
 * Override WooCommerce cart template to use Blade template
 */
add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
    // Override cart templates
    if ($template_name === 'cart/cart.php' || $template_name === 'cart.php') {
        $blade_template = get_stylesheet_directory() . '/resources/views/woocommerce/cart.blade.php';
        
        if (file_exists($blade_template)) {
            return $blade_template;
        }
    }
    
    // Override checkout form template - use PHP wrapper that loads Blade template
    if ($template_name === 'checkout/form-checkout.php' || $template_name === 'form-checkout.php') {
        $custom_template = get_template_directory() . '/woocommerce/checkout/form-checkout.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    // Override thankyou template - use PHP wrapper that loads Blade template
    if ($template_name === 'checkout/thankyou.php') {
        $custom_template = get_template_directory() . '/woocommerce/checkout/thankyou.php';
        
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    
    return $template;
}, 10, 3);

/**
 * Handle cart page specifically
 */
add_filter('template_include', function ($template) {
    // Only use WooCommerce function, not URL detection
    if (function_exists('is_cart') && is_cart()) {
        $blade_template = get_stylesheet_directory() . '/resources/views/woocommerce/cart.blade.php';
        
        if (file_exists($blade_template)) {
            return $blade_template;
        }
    }
    
    // Handle other Blade templates
    if (str_ends_with($template, '.blade.php')) {
        // Convert full path to Sage view name
        $view = str_replace(
            [get_stylesheet_directory() . '/resources/views/', '.blade.php'],
            '',
            $template
        );
        
        try {
            echo \Roots\view($view)->render();
            exit; // Prevent WooCommerce from double-loading
        } catch (Exception $e) {
            // Silent fail - let WordPress handle the template
        }
    }
    
    return $template;
}, 99);

// Note: Custom image sizes are defined in app/setup.php to avoid conflicts

// Redirect special shop subpages to main /shop with appropriate sorting
add_action('template_redirect', function () {

    // 🛍️ Best Sellers → Sort by popularity
    if (is_page('best-sellers')) {
        wp_safe_redirect(
            add_query_arg('orderby', 'popularity', wc_get_page_permalink('shop')),
            301
        );
        exit;
    }

    // 🆕 New Arrivals → Sort by latest
    if (is_page('new-arrivals')) {
        wp_safe_redirect(
            add_query_arg('orderby', 'date', wc_get_page_permalink('shop')),
            301
        );
        exit;
    }

    // 💸 Sale → Show on-sale products only
    if (is_page('sale')) {
        wp_safe_redirect(
            add_query_arg('onsale', '1', wc_get_page_permalink('shop')),
            301
        );
        exit;
    }
});
