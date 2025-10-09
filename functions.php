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

// Enable AJAX add to cart for product grid
add_action('wp_enqueue_scripts', 'enable_ajax_add_to_cart');

function enable_ajax_add_to_cart() {
    if (function_exists('is_woocommerce') && class_exists('WooCommerce')) {
        // Enqueue WooCommerce scripts
        wp_enqueue_script('wc-add-to-cart');
        wp_enqueue_script('wc-cart-fragments');

        // Localize script for AJAX parameters
        wp_localize_script('wc-add-to-cart', 'wc_add_to_cart_params', array(
            'ajax_url' => WC()->ajax_url(),
            'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
            'i18n_view_cart' => esc_attr__('View cart', 'woocommerce'),
            // 'cart_url' => apply_filters('woocommerce_add_to_cart_redirect', wc_get_cart_url()),
            'is_cart' => is_cart(),
            'cart_redirect_after_add' => get_option('woocommerce_cart_redirect_after_add')
        ));

        // Also localize for our custom cart.js
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
        
        $cart_items[] = array(
            'cart_item_key' => $cart_item_key,
            'name' => $product->get_name(),
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

function clear_cart_drawer_cache() {
    // This will be handled by the JavaScript cache invalidation
    // when the cart drawer is opened after modifications
}
