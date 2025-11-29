<?php $__env->startSection('content'); ?>
  <?php
    do_action('woocommerce_before_checkout_form', WC()->checkout());
    
    $checkout = WC()->checkout();
    
    // Get ALL shipping methods grouped by zones (not just applicable ones)
    $shipping_methods_by_zone = [];
    $chosen_shipping_method = WC()->session->get('chosen_shipping_methods', []);
    $chosen_method_id = isset($chosen_shipping_method[0]) ? $chosen_shipping_method[0] : '';
    
    // Get all shipping zones (including default/global zone)
    $all_zones = WC_Shipping_Zones::get_zones();
    
    // Also get the default/global zone (zone_id = 0)
    $default_zone = new WC_Shipping_Zone(0);
    
    // Combine all zones including default
    $zones_to_process = [];
    foreach ($all_zones as $zone_data) {
      $zones_to_process[] = new WC_Shipping_Zone($zone_data['zone_id']);
    }
    $zones_to_process[] = $default_zone; // Add default zone at the end
    
    // Ensure shipping address is set in session for package calculation
    // Get current checkout values or use defaults
    $checkout = WC()->checkout();
    $billing_country = $checkout->get_value('billing_country') ?: WC()->countries->get_base_country();
    $billing_state = $checkout->get_value('billing_state') ?: '';
    $billing_postcode = $checkout->get_value('billing_postcode') ?: '';
    $billing_city = $checkout->get_value('billing_city') ?: '';
    
    // Set shipping address in session if not already set (needed for package calculation)
    if (!WC()->customer->get_shipping_country()) {
      WC()->customer->set_shipping_country($billing_country);
    }
    if (!WC()->customer->get_shipping_state() && $billing_state) {
      WC()->customer->set_shipping_state($billing_state);
    }
    if (!WC()->customer->get_shipping_postcode() && $billing_postcode) {
      WC()->customer->set_shipping_postcode($billing_postcode);
    }
    if (!WC()->customer->get_shipping_city() && $billing_city) {
      WC()->customer->set_shipping_city($billing_city);
    }
    
    // Calculate shipping packages with the current address
    WC()->shipping()->calculate_shipping(WC()->cart->get_shipping_packages());
    
    // Get available methods from packages to check if they're available
    $available_method_ids = [];
    $packages = [];
    if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {
      $packages = WC()->shipping()->get_packages();
      foreach ($packages as $package) {
        if (isset($package['rates']) && is_array($package['rates'])) {
          foreach ($package['rates'] as $method_id => $method) {
            $available_method_ids[$method_id] = true;
          }
        }
      }
    }
    
    // Process each zone and get all its methods
    foreach ($zones_to_process as $zone) {
      $zone_name = $zone->get_zone_name();
      if (empty($zone_name)) {
        $zone_name = 'Global'; // Default zone has no name
      }
      
      // Initialize zone array if it doesn't exist
      if (!isset($shipping_methods_by_zone[$zone_name])) {
        $shipping_methods_by_zone[$zone_name] = [];
      }
      
      // Get all shipping methods from this zone
      $zone_methods = $zone->get_shipping_methods(true);
      
      foreach ($zone_methods as $zone_method) {
        // Get the rate ID that WooCommerce uses
        $method_id = $zone_method->get_rate_id();
        
        // Check if this method is available (in the package rates)
        $is_available_from_packages = isset($available_method_ids[$method_id]);
        
        // Also check if method should be available based on zone location restrictions
        // If no address is set yet, check if zone matches the default/base country
        $is_available_by_zone = false;
        if ($is_available_from_packages) {
          $is_available_by_zone = true;
        } else {
          // Check if zone location matches current or default country
          $zone_locations = $zone->get_zone_locations();
          $current_country = WC()->customer->get_shipping_country() ?: WC()->countries->get_base_country();
          
          // If zone has no location restrictions, it's available globally
          if (empty($zone_locations)) {
            $is_available_by_zone = true;
          } else {
            // Check if current country matches any zone location
            foreach ($zone_locations as $location) {
              if ($location->type === 'country' && $location->code === $current_country) {
                $is_available_by_zone = true;
                break;
              }
            }
          }
        }
        
        // Method is available if it's in packages OR if zone location matches
        $is_available = $is_available_from_packages || $is_available_by_zone;
        
        $method_label = $zone_method->get_title();
        if (empty($method_label)) {
          $method_label = $zone_method->get_method_title();
        }
        
        // Calculate cost (this might vary based on cart, so we'll use the method's default cost calculation)
        $cost = 0;
        $cost_html = '';
        try {
          if (WC()->cart && WC()->cart->needs_shipping() && !empty($packages)) {
            // Try to get cost from available methods if it exists
            if ($is_available_from_packages && isset($packages[0]['rates'][$method_id])) {
              $rate_obj = $packages[0]['rates'][$method_id];
              $cost = $rate_obj->get_cost();
              $cost_html = wc_price($cost);
            } else {
              // Estimate cost from method settings
              $cost = $zone_method->get_option('cost', 0);
              if (empty($cost)) {
                $cost = 0;
              }
              $cost_html = wc_price($cost);
            }
          } else {
            // Fallback: get cost from method settings
            $cost = $zone_method->get_option('cost', 0);
            if (empty($cost)) {
              $cost = 0;
            }
            $cost_html = wc_price($cost);
          }
        } catch (Exception $e) {
          $cost = 0;
          $cost_html = wc_price(0);
        }
        
        $is_chosen = ($chosen_method_id == $method_id);
        
        // Get minimum spend requirement for free shipping
        $min_amount = null;
        $min_amount_formatted = null;
        if (method_exists($zone_method, 'get_option')) {
          $min_amount = $zone_method->get_option('min_amount', '');
          if (!empty($min_amount) && is_numeric($min_amount)) {
            $min_amount_formatted = wc_price($min_amount);
          }
        }
        
        // Add method to zone
        $shipping_methods_by_zone[$zone_name][] = [
          'id' => $method_id,
          'label' => $method_label,
          'cost' => $cost_html,
          'cost_raw' => $cost,
          'delivery_time' => apply_filters('woocommerce_shipping_method_delivery_time', '4–10 business days', $zone_method),
          'chosen' => $is_chosen,
          'zone' => $zone_name,
          'available' => $is_available,
          'min_amount' => $min_amount ? floatval($min_amount) : null,
          'min_amount_formatted' => $min_amount_formatted,
        ];
      }
    }
    
    // Also get methods from packages (for methods that might not be in zones)
    if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {
      $packages = WC()->shipping()->get_packages();
      
      foreach ($packages as $i => $package) {
        $available_methods = $package['rates'];
        $chosen_method = isset($chosen_shipping_method[$i]) ? $chosen_shipping_method[$i] : '';
        $first_method = true;
        
        // Try to determine zone from package
        $zone_name = 'Global';
        
        if (isset($package['zone_id'])) {
          $zone_id = $package['zone_id'];
          if ($zone_id > 0) {
            $zone = new WC_Shipping_Zone($zone_id);
            $zone_name = $zone->get_zone_name();
          }
        }
        
        // If zone doesn't exist yet, create it
        if (!isset($shipping_methods_by_zone[$zone_name])) {
          $shipping_methods_by_zone[$zone_name] = [];
        }
        
        // Add methods from package (they might have updated costs)
        foreach ($available_methods as $method_id => $method) {
          // Check if method already exists in zone
          $method_exists = false;
          foreach ($shipping_methods_by_zone[$zone_name] as $key => $existing_method) {
            if ($existing_method['id'] === $method_id) {
              // Update with package data (cost might be different)
              // Preserve min_amount if it was already set
              $shipping_methods_by_zone[$zone_name][$key]['cost'] = wc_price($method->get_cost());
              $shipping_methods_by_zone[$zone_name][$key]['cost_raw'] = $method->get_cost();
              $shipping_methods_by_zone[$zone_name][$key]['available'] = true;
              // Preserve min_amount and min_amount_formatted if they exist
              if (!isset($shipping_methods_by_zone[$zone_name][$key]['min_amount'])) {
                // Try to get from rate meta if available
                if (method_exists($method, 'get_meta') && $method->get_meta('min_amount')) {
                  $min_amount = $method->get_meta('min_amount');
                  if (is_numeric($min_amount)) {
                    $shipping_methods_by_zone[$zone_name][$key]['min_amount'] = floatval($min_amount);
                    $shipping_methods_by_zone[$zone_name][$key]['min_amount_formatted'] = wc_price($min_amount);
                  }
                }
              }
              $method_exists = true;
              break;
            }
          }
          
          // If method doesn't exist yet, add it
          if (!$method_exists) {
            $is_chosen = ($chosen_method == $method_id) || ($first_method && empty($chosen_method));
            
            // Try to get min_amount from rate meta or method instance
            $min_amount = null;
            $min_amount_formatted = null;
            // Try to get from rate meta first
            if (method_exists($method, 'get_meta') && $method->get_meta('min_amount')) {
              $min_amount = $method->get_meta('min_amount');
              if (is_numeric($min_amount)) {
                $min_amount_formatted = wc_price($min_amount);
              }
            }
            
            $shipping_methods_by_zone[$zone_name][] = [
              'id' => $method_id,
              'label' => $method->get_label(),
              'cost' => wc_price($method->get_cost()),
              'cost_raw' => $method->get_cost(),
              'delivery_time' => apply_filters('woocommerce_shipping_method_delivery_time', '4–10 business days', $method),
              'chosen' => $is_chosen,
              'zone' => $zone_name,
              'available' => true,
              'min_amount' => $min_amount ? floatval($min_amount) : null,
              'min_amount_formatted' => $min_amount_formatted,
            ];
          }
          $first_method = false;
        }
      }
    }
    
    // Also create a flat array for backward compatibility
    $shipping_methods = [];
    foreach ($shipping_methods_by_zone as $zone_methods) {
      $shipping_methods = array_merge($shipping_methods, $zone_methods);
    }
    
    // Get payment gateways
    $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
    $payment_gateways = [];
    foreach ($available_gateways as $gateway_id => $gateway) {
      $payment_gateways[] = [
        'id' => $gateway_id,
        'title' => $gateway->get_title(),
      ];
    }
    
    // Get cart items
    $cart_items = [];
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
      $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
      $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
      
      if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
        $image = $_product->get_image('thumbnail', ['class' => 'w-20 rounded-md', 'alt' => esc_attr($_product->get_name())]);
        
        // Get variation attributes
        $variation_attributes = [];
        if ($_product->is_type('variation')) {
          $attributes = $_product->get_variation_attributes();
          foreach ($attributes as $key => $value) {
            $variation_attributes[str_replace('attribute_', '', $key)] = $value;
          }
        } elseif (isset($cart_item['variation']) && is_array($cart_item['variation'])) {
          $variation_attributes = $cart_item['variation'];
        }
        
        $cart_items[] = [
          'key' => $cart_item_key,
          'name' => $_product->get_name(),
          'quantity' => $cart_item['quantity'],
          'permalink' => $product_permalink,
          'image' => preg_match('/src=["\']([^"\']+)["\']/', $image, $matches) ? $matches[1] : '',
          'subtotal' => apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key),
          'variation' => $variation_attributes,
        ];
      }
    }
    
    // Get countries for dropdown - use WooCommerce's full country list
    $countries = [];
    $woocommerce_countries = WC()->countries->get_countries();
    if (!empty($woocommerce_countries)) {
      foreach ($woocommerce_countries as $key => $label) {
        $countries[] = ['key' => $key, 'label' => $label];
      }
    } else {
      // Fallback: try to get from checkout fields
      $country_fields = $checkout->get_checkout_fields('billing');
      if (isset($country_fields['billing_country']['options']) && is_array($country_fields['billing_country']['options'])) {
        foreach ($country_fields['billing_country']['options'] as $key => $label) {
          $countries[] = ['key' => $key, 'label' => $label];
        }
      }
    }
    
    // Get Stripe publishable key for credit card form
    // Must be defined in wp-config.php as HG_STRIPE_CC_PUBLISHABLE_KEY
    $stripe_publishable_key = defined('HG_STRIPE_CC_PUBLISHABLE_KEY') 
      ? HG_STRIPE_CC_PUBLISHABLE_KEY 
      : '';
    
    // Prepare checkout data
    $checkout_data = [
      'billing_email' => $checkout->get_value('billing_email'),
      'billing_first_name' => $checkout->get_value('billing_first_name'),
      'billing_last_name' => $checkout->get_value('billing_last_name'),
      'billing_company' => $checkout->get_value('billing_company'),
      'billing_address_1' => $checkout->get_value('billing_address_1'),
      'billing_address_2' => $checkout->get_value('billing_address_2'),
      'billing_city' => $checkout->get_value('billing_city'),
      'billing_country' => $checkout->get_value('billing_country') ?: WC()->countries->get_base_country(),
      'billing_state' => $checkout->get_value('billing_state'),
      'billing_postcode' => $checkout->get_value('billing_postcode'),
      'billing_phone' => $checkout->get_value('billing_phone'),
      'shipping_method' => isset($shipping_methods[0]) ? $shipping_methods[0]['id'] : '',
      'payment_method' => isset($payment_gateways[0]) ? $payment_gateways[0]['id'] : '',
      'order_comments' => $checkout->get_value('order_comments'),
      'countries' => $countries,
      'enable_order_notes' => apply_filters(
        'woocommerce_enable_order_notes_field',
        'yes' === get_option('woocommerce_enable_order_comments', 'yes')
      ),
      'cart_subtotal' => WC()->cart->get_cart_subtotal(),
      'cart_total' => WC()->cart->get_total(),
      'cart_total_amount' => WC()->cart->get_total(''), // Get numeric amount without currency symbol
      'currency' => get_woocommerce_currency(),
      'stripe_publishable_key' => $stripe_publishable_key,
    ];
    
    // Get shipping total
    if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {
      $packages = WC()->shipping()->get_packages();
      $chosen_methods = WC()->session->get('chosen_shipping_methods', []);
      foreach ($packages as $i => $package) {
        $available_methods = $package['rates'];
        $chosen_method = isset($chosen_methods[$i]) ? $chosen_methods[$i] : '';
        if ($chosen_method && isset($available_methods[$chosen_method])) {
          $method = $available_methods[$chosen_method];
          $checkout_data['shipping_total'] = wc_price($method->get_cost());
          break;
        }
      }
      if (!isset($checkout_data['shipping_total']) && count($packages) > 0) {
        $first_package = $packages[0];
        $first_method = reset($first_package['rates']);
        if ($first_method) {
          $checkout_data['shipping_total'] = wc_price($first_method->get_cost());
        }
      }
    }
    
    // Get tax rate for the shipping country
    // Frontend will calculate tax amount based on this rate
    $checkout_data['tax_enabled'] = wc_tax_enabled();
    $checkout_data['tax_rate'] = 0; // Tax rate as decimal (e.g., 0.07 for 7%)
    
    if (wc_tax_enabled()) {
      // Get shipping country (or billing country as fallback)
      $shipping_country = WC()->customer->get_shipping_country() ?: WC()->customer->get_billing_country() ?: WC()->countries->get_base_country();
      $shipping_state = WC()->customer->get_shipping_state() ?: WC()->customer->get_billing_state() ?: '';
      $shipping_postcode = WC()->customer->get_shipping_postcode() ?: WC()->customer->get_billing_postcode() ?: '';
      
      // Find tax rates for this country/state/postcode
      $tax_rates = WC_Tax::find_rates([
        'country' => $shipping_country,
        'state' => $shipping_state,
        'postcode' => $shipping_postcode,
      ]);
      
      // Get the first applicable tax rate (as decimal)
      if (!empty($tax_rates)) {
        $first_rate = reset($tax_rates);
        if (isset($first_rate['rate'])) {
          $checkout_data['tax_rate'] = $first_rate['rate'] / 100; // Convert percentage to decimal
        }
      }
    }
  ?>

  
  <?php do_action('woocommerce_before_checkout_form_cart_notices'); ?>

  
  <?php
    // Generate nonces for WooCommerce checkout, Stripe AJAX, and states
    $woo_checkout_nonce = wp_create_nonce('woocommerce-process_checkout');
    $hg_stripe_cc_nonce = wp_create_nonce('hg_stripe_cc_nonce');
    $checkout_states_nonce = wp_create_nonce('checkout-nonce');  // For states/provinces fetch
  ?>
  
  <script>
    window.checkoutFormData = {
      checkoutData: <?php echo json_encode($checkout_data, 15, 512) ?>,
      cartItems: <?php echo json_encode($cart_items, 15, 512) ?>,
      shippingMethods: <?php echo json_encode($shipping_methods, 15, 512) ?>,
      shippingMethodsByZone: <?php echo json_encode($shipping_methods_by_zone, 15, 512) ?>,
      paymentGateways: <?php echo json_encode($payment_gateways, 15, 512) ?>,
      checkoutUrl: <?php echo json_encode(esc_url(wc_get_checkout_url()), 15, 512) ?>,
      cartUrl: <?php echo json_encode(esc_url(wc_get_cart_url()), 15, 512) ?>,
      ajaxUrl: <?php echo json_encode(esc_url(admin_url('admin-ajax.php')), 15, 512) ?>,
      nonce: <?php echo json_encode(wp_create_nonce('woocommerce-cart'), 15, 512) ?>,
      checkoutNonce: <?php echo json_encode($hg_stripe_cc_nonce, 15, 512) ?>,        // Stripe AJAX nonce
      wooCheckoutNonce: <?php echo json_encode($woo_checkout_nonce, 15, 512) ?>,     // WooCommerce checkout nonce
      statesNonce: <?php echo json_encode($checkout_states_nonce, 15, 512) ?>,       // States/provinces nonce
    };
    console.log('🔵 CheckoutForm Data loaded:', window.checkoutFormData);
  </script>

  
  <div id="checkout-form-react"></div>

  
  <div style="display:none;">
    <?php
      // This outputs the real <div id="payment" class="woocommerce-checkout-payment">...</div>
      woocommerce_checkout_payment();
    ?>
  </div>

  
  <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
  <?php echo app('Illuminate\Foundation\Vite')('resources/js/checkout-form.jsx'); ?>

  
  <script>
    (function() {
      console.log('🔵 CheckoutForm Blade: Script block executing...');
      
      // Verify data is available
      if (!window.checkoutFormData) {
        console.error('❌ CheckoutForm Blade: window.checkoutFormData is not defined!');
      } else {
        console.log('✅ CheckoutForm Blade: window.checkoutFormData is available');
        console.log('✅ CheckoutForm Blade: Data keys:', Object.keys(window.checkoutFormData));
      }
      
      // Wait for script to load and then check for mount function
      function waitForMountFunction(attempts = 0) {
        const maxAttempts = 100; // Wait up to 5 seconds (100 * 50ms)
        
        if (typeof window.mountCheckoutForm === 'function') {
          console.log('✅ CheckoutForm Blade: mountCheckoutForm function is available');
          
          // Try manual mount if React hasn't mounted yet
          const mountPoint = document.getElementById('checkout-form-react');
          if (mountPoint) {
            if (mountPoint.children.length === 0) {
              console.log('🔵 CheckoutForm Blade: Attempting manual mount...');
              try {
                window.mountCheckoutForm();
              } catch (error) {
                console.error('❌ CheckoutForm Blade: Error calling mountCheckoutForm:', error);
              }
            } else {
              console.log('✅ CheckoutForm Blade: Component already mounted');
            }
          }
          
          // Final check after delay
          setTimeout(function() {
            const mountPoint = document.getElementById('checkout-form-react');
            if (mountPoint && mountPoint.children.length === 0) {
              console.error('❌ CheckoutForm Blade: React component did not mount after 3 seconds');
              console.error('Available scripts:', Array.from(document.querySelectorAll('script[src]')).map(s => s.src));
              mountPoint.innerHTML = '<div class="p-8 bg-red-50 border border-red-200 rounded-lg"><p class="text-red-800 font-semibold">Checkout form failed to load.</p><p class="text-red-700 mt-2">Please refresh the page or contact support.</p><p class="text-sm text-red-600 mt-2">Check the browser console (F12) for error details.</p><button onclick="location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Refresh Page</button></div>';
            } else if (mountPoint && mountPoint.children.length > 0) {
              console.log('✅ CheckoutForm Blade: React component appears to have mounted successfully');
            }
          }, 3000);
        } else if (attempts < maxAttempts) {
          setTimeout(() => waitForMountFunction(attempts + 1), 50);
        } else {
          console.error('❌ CheckoutForm Blade: mountCheckoutForm function not available after ' + maxAttempts + ' attempts');
          console.error('Script may not have loaded. Check Network tab for checkout-form-*.js');
          const mountPoint = document.getElementById('checkout-form-react');
          if (mountPoint && mountPoint.children.length === 0) {
            mountPoint.innerHTML = '<div class="p-8 bg-red-50 border border-red-200 rounded-lg"><p class="text-red-800 font-semibold">Checkout form script failed to load.</p><p class="text-red-700 mt-2">Please check your browser console and network tab for errors.</p><p class="text-sm text-red-600 mt-2">Make sure the build was successful (npm run build).</p><button onclick="location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Refresh Page</button></div>';
          }
        }
      }
      
      // Start waiting for mount function
      waitForMountFunction();
    })();
  </script>

  <?php do_action('woocommerce_after_checkout_form', WC()->checkout()); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dang/Local Sites/heygirlsbkk/app/public/wp-content/themes/heygirlsbkk/resources/views/woocommerce/checkout/form-checkout.blade.php ENDPATH**/ ?>