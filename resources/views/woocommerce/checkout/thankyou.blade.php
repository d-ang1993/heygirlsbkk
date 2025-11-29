@extends('layouts.app')

@section('content')
  @php
    // Try to use $order if passed in, otherwise fetch from query var.
    if (!isset($order) || ! $order) {
      $order_id = absint(get_query_var('order-received'));
      $order    = $order_id ? wc_get_order($order_id) : null;
    }

    $client_secret = $order ? $order->get_meta('_stripe_promptpay_client_secret') : '';
    $intent_id = $order ? $order->get_meta('_stripe_promptpay_intent_id') : '';
    $is_promptpay  = $order && $order->get_payment_method() === 'hg_stripe_promptpay';

    // Stripe publishable key (from wp-config).
    $stripe_pp_key = defined('HG_STRIPE_PP_PUBLISHABLE_KEY') ? HG_STRIPE_PP_PUBLISHABLE_KEY : '';
    $stripe_secret_key = defined('HG_STRIPE_PP_SECRET_KEY') ? HG_STRIPE_PP_SECRET_KEY : '';

    $is_paid = $order ? $order->is_paid() : false;
    
    // Try to get QR code server-side if possible (similar to plugin logic)
    $qr_code_url = null;
    $intent = null; // Initialize to avoid undefined variable errors
    if ($is_promptpay && !$is_paid && $intent_id && $stripe_secret_key && class_exists('\Stripe\Stripe') && $order) {
      try {
        \Stripe\Stripe::setApiKey($stripe_secret_key);
        
        // Retrieve the PaymentIntent
        $intent = \Stripe\PaymentIntent::retrieve($intent_id);
        
        error_log('🔵 Server-side QR: PaymentIntent status = ' . $intent->status);
        error_log('🔵 Server-side QR: Has next_action = ' . (isset($intent->next_action) ? 'yes' : 'no'));
        
        // Check if already has QR code
        if (isset($intent->next_action) && is_object($intent->next_action)) {
          if (isset($intent->next_action->promptpay_display_qr_code) && is_object($intent->next_action->promptpay_display_qr_code)) {
            $qr_data = $intent->next_action->promptpay_display_qr_code;
            $qr_code_url = $qr_data->image_url_png ?? $qr_data->image_url_svg ?? null;
            error_log('🔵 Server-side QR: Found QR code URL = ' . ($qr_code_url ?: 'null'));
          } elseif (isset($intent->next_action->image_url_png) || isset($intent->next_action->image_url_svg)) {
            $qr_code_url = $intent->next_action->image_url_png ?? $intent->next_action->image_url_svg ?? null;
            error_log('🔵 Server-side QR: Found QR code URL (alt path) = ' . ($qr_code_url ?: 'null'));
          }
        }
        
        // If no QR code and status is requires_payment_method, confirm with payment method
        if (!$qr_code_url && $intent->status === 'requires_payment_method') {
          try {
            error_log('🔵 Server-side QR: PaymentIntent requires_payment_method, confirming with PromptPay payment method...');
            
            // Get billing details from order (with safety checks)
            $billing_email = $order && method_exists($order, 'get_billing_email') ? $order->get_billing_email() : '';
            $billing_first_name = $order && method_exists($order, 'get_billing_first_name') ? $order->get_billing_first_name() : '';
            $billing_last_name = $order && method_exists($order, 'get_billing_last_name') ? $order->get_billing_last_name() : '';
            $billing_name = trim($billing_first_name . ' ' . $billing_last_name);
            
            if (!$billing_name && $order && method_exists($order, 'get_formatted_billing_full_name')) {
              $billing_name = $order->get_formatted_billing_full_name();
            }
            
            error_log('🔵 Server-side QR: Billing email = ' . ($billing_email ?: 'not found'));
            
            // Confirm PaymentIntent with PromptPay payment method in a single call
            $confirm_params = [
              'payment_method_data' => [
                'type' => 'promptpay',
              ],
            ];
            
            // Add billing details (email is required by Stripe for PromptPay)
            // If no email, use a placeholder (though this might fail)
            $billing_email = $billing_email ?: 'customer@example.com';
            $confirm_params['payment_method_data']['billing_details'] = [
              'email' => $billing_email,
            ];
            if ($billing_name) {
              $confirm_params['payment_method_data']['billing_details']['name'] = $billing_name;
            }
            
            $intent = \Stripe\PaymentIntent::confirm($intent_id, $confirm_params);
            
            error_log('🔵 Server-side QR: After confirm, status = ' . $intent->status);
            error_log('🔵 Server-side QR: After confirm, has next_action = ' . (isset($intent->next_action) ? 'yes' : 'no'));
            
            // Extract QR code after confirmation
            if (isset($intent->next_action) && is_object($intent->next_action)) {
              if (isset($intent->next_action->promptpay_display_qr_code) && is_object($intent->next_action->promptpay_display_qr_code)) {
                $qr_data = $intent->next_action->promptpay_display_qr_code;
                $qr_code_url = $qr_data->image_url_png ?? $qr_data->image_url_svg ?? null;
                error_log('✅ Server-side QR: Found QR code URL after confirm = ' . ($qr_code_url ?: 'null'));
              } elseif (isset($intent->next_action->image_url_png) || isset($intent->next_action->image_url_svg)) {
                $qr_code_url = $intent->next_action->image_url_png ?? $intent->next_action->image_url_svg ?? null;
                error_log('✅ Server-side QR: Found QR code URL after confirm (alt path) = ' . ($qr_code_url ?: 'null'));
              }
            }
          } catch (\Stripe\Exception\ApiErrorException $e) {
            // Intent might already be in a state that can't be confirmed
            error_log('⚠️ Server-side QR: Confirm with payment method error - ' . $e->getMessage() . ' | Code: ' . $e->getStripeCode());
            // Try to retrieve again with expanded next_action in case it was already confirmed
            try {
              $intent = \Stripe\PaymentIntent::retrieve($intent_id, ['expand' => ['next_action']]);
              error_log('🔵 Server-side QR: Retrieved after error, status = ' . $intent->status);
              if (isset($intent->next_action) && is_object($intent->next_action)) {
                if (isset($intent->next_action->promptpay_display_qr_code) && is_object($intent->next_action->promptpay_display_qr_code)) {
                  $qr_data = $intent->next_action->promptpay_display_qr_code;
                  $qr_code_url = $qr_data->image_url_png ?? $qr_data->image_url_svg ?? null;
                  error_log('✅ Server-side QR: Found QR code URL after retrieve = ' . ($qr_code_url ?: 'null'));
                } elseif (isset($intent->next_action->image_url_png) || isset($intent->next_action->image_url_svg)) {
                  $qr_code_url = $intent->next_action->image_url_png ?? $intent->next_action->image_url_svg ?? null;
                  error_log('✅ Server-side QR: Found QR code URL after retrieve (alt path) = ' . ($qr_code_url ?: 'null'));
                }
              }
            } catch (\Exception $e2) {
              error_log('⚠️ Server-side QR: Expand error - ' . $e2->getMessage());
            }
          }
        } elseif (!$qr_code_url && $intent->status === 'requires_action') {
          // If status is already requires_action, QR code should be available
          error_log('🔵 Server-side QR: PaymentIntent already requires_action, extracting QR code...');
          if (isset($intent->next_action) && is_object($intent->next_action)) {
            if (isset($intent->next_action->promptpay_display_qr_code) && is_object($intent->next_action->promptpay_display_qr_code)) {
              $qr_data = $intent->next_action->promptpay_display_qr_code;
              $qr_code_url = $qr_data->image_url_png ?? $qr_data->image_url_svg ?? null;
              error_log('✅ Server-side QR: Found QR code URL (requires_action) = ' . ($qr_code_url ?: 'null'));
            } elseif (isset($intent->next_action->image_url_png) || isset($intent->next_action->image_url_svg)) {
              $qr_code_url = $intent->next_action->image_url_png ?? $intent->next_action->image_url_svg ?? null;
              error_log('✅ Server-side QR: Found QR code URL (requires_action, alt path) = ' . ($qr_code_url ?: 'null'));
            }
          }
          // If still no QR, try expanding
          if (!$qr_code_url) {
            try {
              $intent = \Stripe\PaymentIntent::retrieve($intent_id, ['expand' => ['next_action']]);
              if (isset($intent->next_action) && is_object($intent->next_action)) {
                if (isset($intent->next_action->promptpay_display_qr_code) && is_object($intent->next_action->promptpay_display_qr_code)) {
                  $qr_data = $intent->next_action->promptpay_display_qr_code;
                  $qr_code_url = $qr_data->image_url_png ?? $qr_data->image_url_svg ?? null;
                  error_log('✅ Server-side QR: Found QR code URL after expand (requires_action) = ' . ($qr_code_url ?: 'null'));
                }
              }
            } catch (\Exception $e) {
              error_log('⚠️ Server-side QR: Expand error (requires_action) - ' . $e->getMessage());
            }
          }
        } elseif (!$qr_code_url && $intent->status !== 'succeeded' && $intent->status !== 'processing') {
          // For other statuses, try to confirm directly (though this might fail)
          try {
            error_log('🔵 Server-side QR: Attempting to confirm PaymentIntent (status: ' . $intent->status . ')...');
            $intent = $intent->confirm();
            error_log('🔵 Server-side QR: After confirm, status = ' . $intent->status);
            
            // Extract QR code after confirmation
            if (isset($intent->next_action) && is_object($intent->next_action)) {
              if (isset($intent->next_action->promptpay_display_qr_code) && is_object($intent->next_action->promptpay_display_qr_code)) {
                $qr_data = $intent->next_action->promptpay_display_qr_code;
                $qr_code_url = $qr_data->image_url_png ?? $qr_data->image_url_svg ?? null;
                error_log('✅ Server-side QR: Found QR code URL after confirm = ' . ($qr_code_url ?: 'null'));
              }
            }
          } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log('⚠️ Server-side QR: Confirm error - ' . $e->getMessage() . ' | Code: ' . $e->getStripeCode());
          }
        }
        
        // Update order status if payment succeeded
        if ($intent && $intent->status === 'succeeded' && $order && !$order->is_paid()) {
          $order->payment_complete();
          $order->add_order_note(__('Payment confirmed via Stripe PromptPay.', 'hg-stripe-promptpay'));
          $is_paid = true;
        }
        
        if (!$qr_code_url && $intent) {
          error_log('⚠️ Server-side QR: No QR code URL found. PaymentIntent structure: ' . json_encode([
            'status' => $intent->status,
            'has_next_action' => isset($intent->next_action),
            'next_action_type' => isset($intent->next_action->type) ? $intent->next_action->type : null,
          ]));
        }
      } catch (\Exception $e) {
        error_log('❌ Server-side QR generation error: ' . $e->getMessage());
        error_log('❌ Error trace: ' . $e->getTraceAsString());
        // Continue with client-side generation as fallback
        $intent = null; // Reset intent on error
      }
    } else {
      if ($is_promptpay && !$is_paid) {
        error_log('⚠️ Server-side QR: Cannot generate - is_promptpay=' . ($is_promptpay ? 'yes' : 'no') . ', intent_id=' . ($intent_id ?: 'empty') . ', secret_key=' . ($stripe_secret_key ? 'exists' : 'missing') . ', Stripe class=' . (class_exists('\Stripe\Stripe') ? 'exists' : 'missing') . ', order=' . ($order ? 'exists' : 'missing'));
      }
    }
    
    // Prepare order data for React component
    $order_data = [];
    if ($order) {
      // Basic order info
      $order_data['id'] = $order->get_id();
      $order_data['orderNumber'] = $order->get_order_number();
      $order_data['dateCreated'] = wc_format_datetime($order->get_date_created());
      $order_data['billingEmail'] = $order->get_billing_email();
      $order_data['paymentMethodTitle'] = $order->get_payment_method_title();
      $order_data['formattedTotal'] = $order->get_formatted_order_total();
      $order_data['statusName'] = wc_get_order_status_name($order->get_status());
      $order_data['formattedBillingAddress'] = $order->get_formatted_billing_address();
      $order_data['formattedShippingAddress'] = $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address();
      
      // Order items
      $order_data['items'] = [];
      foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $item_data = [
          'name' => $item->get_name(),
          'quantity' => $item->get_quantity(),
          'formattedTotal' => wc_price($item->get_total(), ['currency' => $order->get_currency()]),
          'meta' => wc_display_item_meta($item, ['echo' => false]),
        ];
        if ($product && $product->get_sku()) {
          $item_data['sku'] = $product->get_sku();
        }
        
        // Add product image
        if ($product) {
          $image_id = $product->get_image_id();
          if ($image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
            if ($image_url) {
              $item_data['imageSrc'] = $image_url;
              $item_data['imageAlt'] = $product->get_name();
            }
          }
          
          // Add product description/short description
          $short_description = $product->get_short_description();
          if ($short_description) {
            $item_data['description'] = wp_strip_all_tags($short_description);
          }
        }
        
        $order_data['items'][] = $item_data;
      }
      
      // Order totals
      $order_data['subtotalToDisplay'] = $order->get_subtotal_to_display();
      $order_data['discountTotal'] = (float) $order->get_discount_total();
      if ($order_data['discountTotal'] > 0) {
        $order_data['formattedDiscountTotal'] = wc_price($order->get_discount_total(), ['currency' => $order->get_currency()]);
      }
      
      // Shipping methods
      $order_data['shippingMethods'] = [];
      foreach ($order->get_shipping_methods() as $shipping) {
        $order_data['shippingMethods'][] = [
          'name' => $shipping->get_name(),
          'formattedTotal' => wc_price($shipping->get_total(), ['currency' => $order->get_currency()]),
        ];
      }
      
      // Fees
      $order_data['fees'] = [];
      foreach ($order->get_fees() as $fee) {
        $order_data['fees'][] = [
          'name' => $fee->get_name(),
          'formattedTotal' => wc_price($fee->get_total(), ['currency' => $order->get_currency()]),
        ];
      }
      
      // Tax/VAT information
      $order_data['taxTotals'] = [];
      $tax_totals = $order->get_tax_totals();
      if (!empty($tax_totals)) {
        foreach ($tax_totals as $tax) {
          $order_data['taxTotals'][] = [
            'label' => $tax->label,
            'formattedAmount' => wc_price($tax->amount, ['currency' => $order->get_currency()]),
            'amount' => (float) $tax->amount,
          ];
        }
      } else {
        // Fallback: if no detailed tax totals, get total tax
        $total_tax = (float) $order->get_total_tax();
        if ($total_tax > 0) {
          $order_data['taxTotals'][] = [
            'label' => 'VAT',
            'formattedAmount' => wc_price($total_tax, ['currency' => $order->get_currency()]),
            'amount' => $total_tax,
          ];
        }
      }
    }
    
    // Generate nonce for AJAX calls
    $thankyou_nonce = $order ? wp_create_nonce('hg_promptpay_check_' . $order->get_id()) : '';
  @endphp

  {{-- Pass data to React component --}}
  <script>
    window.thankYouPageData = {
      orderData: @json($order_data),
      qrCodeUrl: @json($qr_code_url),
      isPromptPay: @json($is_promptpay),
      isPaid: @json($is_paid),
      clientSecret: @json($client_secret),
      intentId: @json($intent_id),
      stripePublishableKey: @json($stripe_pp_key),
      ajaxUrl: @json(esc_url(admin_url('admin-ajax.php'))),
      nonce: @json($thankyou_nonce),
    };
    console.log('🔵 ThankYou Page Data loaded:', window.thankYouPageData);
  </script>

  {{-- React Component Mount Point --}}
  <div id="thankyou-page-react"></div>

  {{-- Load React Component Script --}}
  @viteReactRefresh
  @vite('resources/js/thankyou-page.jsx')

  {{-- Client-side QR code generation script (for fallback) --}}
  @if ($is_promptpay && !$is_paid && $client_secret && $stripe_pp_key && !$qr_code_url)
    <script src="https://js.stripe.com/v3/"></script>
    <script>
      // Client-side QR code generation script
      // This is kept here as a fallback if server-side generation fails
      // The React component will handle the QR code display once generated
    </script>
  @endif

  {{-- Fallback: Manual mount trigger and error detection --}}
  <script>
    (function() {
      console.log('🔵 ThankYou Blade: Script block executing...');
      
      // Verify data is available
      if (!window.thankYouPageData) {
        console.error('❌ ThankYou Blade: window.thankYouPageData is not defined!');
      } else {
        console.log('✅ ThankYou Blade: window.thankYouPageData is available');
        console.log('✅ ThankYou Blade: Data keys:', Object.keys(window.thankYouPageData));
      }
      
      // Wait for script to load and then check for mount function
      function waitForMountFunction(attempts = 0) {
        const maxAttempts = 100;
        
        if (typeof window.mountThankYouPage === 'function') {
          console.log('✅ ThankYou Blade: mountThankYouPage function is available');
          
          // Try manual mount if React hasn't mounted yet
          const mountPoint = document.getElementById('thankyou-page-react');
          if (mountPoint) {
            if (mountPoint.children.length === 0) {
              console.log('🔵 ThankYou Blade: Attempting manual mount...');
              try {
                window.mountThankYouPage();
              } catch (error) {
                console.error('❌ ThankYou Blade: Error calling mountThankYouPage:', error);
              }
            } else {
              console.log('✅ ThankYou Blade: Component already mounted');
            }
          }
          
          // Final check after delay
          setTimeout(function() {
            const mountPoint = document.getElementById('thankyou-page-react');
            if (mountPoint && mountPoint.children.length === 0) {
              console.error('❌ ThankYou Blade: React component did not mount after 3 seconds');
              mountPoint.innerHTML = '<div class="p-8 bg-red-50 border border-red-200 rounded-lg"><p class="text-red-800 font-semibold">Thank you page failed to load.</p><p class="text-red-700 mt-2">Please refresh the page or contact support.</p><p class="text-sm text-red-600 mt-2">Check the browser console (F12) for error details.</p><button onclick="location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Refresh Page</button></div>';
            } else if (mountPoint && mountPoint.children.length > 0) {
              console.log('✅ ThankYou Blade: React component appears to have mounted successfully');
            }
          }, 3000);
        } else if (attempts < maxAttempts) {
          setTimeout(() => waitForMountFunction(attempts + 1), 50);
        } else {
          console.error('❌ ThankYou Blade: mountThankYouPage function not available after ' + maxAttempts + ' attempts');
          const mountPoint = document.getElementById('thankyou-page-react');
          if (mountPoint && mountPoint.children.length === 0) {
            mountPoint.innerHTML = '<div class="p-8 bg-red-50 border border-red-200 rounded-lg"><p class="text-red-800 font-semibold">Thank you page script failed to load.</p><p class="text-red-700 mt-2">Please check your browser console and network tab for errors.</p><p class="text-sm text-red-600 mt-2">Make sure the build was successful (npm run build).</p><button onclick="location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Refresh Page</button></div>';
          }
        }
      }
      
      // Start waiting for mount function
      waitForMountFunction();
    })();
  </script>

  {{-- Old Blade HTML content removed - now handled by React component --}}
  
  @if (!isset($order) || ! $order)
    <div class="container my-12">
      <h1 class="text-2xl font-semibold mb-4">Order not found</h1>
      <p class="text-gray-700">We couldn't find your order. If you believe this is a mistake, please contact us.</p>
    </div>
  @endif
@endsection
