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
  @endphp

  <div class="container my-12">
    @if (!isset($order) || ! $order)
      <h1 class="text-2xl font-semibold mb-4">Order not found</h1>
      <p class="text-gray-700">We couldn't find your order. If you believe this is a mistake, please contact us.</p>
    @else
      {{-- HEADER + MAIN INFO --}}
      <div class="mb-10">
        <h1 class="text-2xl md:text-3xl font-semibold mb-2">
          Thank you for your order ❤️
        </h1>
        <p class="text-gray-700 mb-1">
          <span class="font-medium">Order #{{ $order->get_order_number() }}</span>
        </p>
        <p class="text-gray-600 text-sm">
          Placed on {{ wc_format_datetime($order->get_date_created()) }}
        </p>
      </div>

      {{-- TOP SECTION: OVERVIEW + (OPTIONAL) PROMPTPAY QR --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        {{-- LEFT: ORDER OVERVIEW --}}
        <div>
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Order summary</h2>

            <dl class="space-y-2 text-sm text-gray-700">
              <div class="flex justify-between">
                <dt class="font-medium">Order number</dt>
                <dd>#{{ $order->get_order_number() }}</dd>
              </div>

              <div class="flex justify-between">
                <dt class="font-medium">Date</dt>
                <dd>{{ wc_format_datetime($order->get_date_created()) }}</dd>
              </div>

              @if ($order->get_billing_email())
                <div class="flex justify-between">
                  <dt class="font-medium">Email</dt>
                  <dd>{{ $order->get_billing_email() }}</dd>
                </div>
              @endif

              <div class="flex justify-between">
                <dt class="font-medium">Payment method</dt>
                <dd>{{ $order->get_payment_method_title() ?: '—' }}</dd>
              </div>

              <div class="flex justify-between">
                <dt class="font-medium">Order total</dt>
                <dd class="font-semibold">{!! $order->get_formatted_order_total() !!}</dd>
              </div>

              <div class="flex justify-between">
                <dt class="font-medium">Payment status</dt>
                <dd>
                  @if ($is_paid)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                      Paid
                    </span>
                  @elseif($is_promptpay)
                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                      Awaiting PromptPay payment
                    </span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                      {{ wc_get_order_status_name( $order->get_status() ) }}
                    </span>
                  @endif
                </dd>
              </div>
            </dl>
          </div>

          {{-- BILLING / SHIPPING SNIPPET --}}
          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
            <div>
              <h3 class="font-semibold mb-2">Billing address</h3>
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                {!! wp_kses_post( $order->get_formatted_billing_address() ?: '—' ) !!}
              </div>
            </div>

            <div>
              <h3 class="font-semibold mb-2">Shipping address</h3>
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                {!! wp_kses_post( $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address() ?: '—' ) !!}
              </div>
            </div>
          </div>
        </div>

        {{-- RIGHT: PROMPTPAY QR (ONLY FOR PROMPTPAY ORDERS) --}}
        <div>
          @if ($is_promptpay)
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 max-w-md mx-auto">
              <h2 class="text-lg font-semibold mb-3">Pay with PromptPay</h2>

              @if ($is_paid)
                <p class="mb-2 text-green-700 font-medium">
                  We’ve received your payment. Thank you! 💚
                </p>
                <p class="text-sm text-gray-700">
                  You’ll receive an email confirmation shortly. You don’t need to pay again.
                </p>
              @elseif ($qr_code_url)
                {{-- Server-side generated QR code --}}
                <p class="mb-4 text-sm text-gray-700">
                  Scan this QR code with your Thai banking app to complete payment.  
                  This QR is valid for a limited time.
                </p>
                
                <div class="flex justify-center mb-4">
                  <img src="{{ $qr_code_url }}" alt="PromptPay QR Code" style="max-width: 260px; height: auto; border: 1px solid #ccc; padding: 10px; background: white;" />
                </div>
                
                <p class="mt-4 text-xs text-gray-500 leading-relaxed">
                  After you complete the payment in your banking app, this page will update once Stripe
                  confirms your payment. You’ll also receive an email confirmation.
                </p>
                
                {{-- Poll for payment status updates --}}
                @if ($intent_id)
                  <script>
                    (function() {
                      var orderId = {{ $order->get_id() }};
                      var intentId = '{{ $intent_id }}';
                      var pollInterval;
                      var pollCount = 0;
                      var maxPolls = 60;

                      function checkPaymentStatus() {
                        pollCount++;
                        if (pollCount > maxPolls) {
                          clearInterval(pollInterval);
                          return;
                        }
                        
                        fetch('{{ admin_url('admin-ajax.php') }}', {
                          method: 'POST',
                          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                          body: new URLSearchParams({
                            action: 'hg_check_promptpay_status',
                            order_id: orderId,
                            intent_id: intentId,
                            nonce: '{{ wp_create_nonce('hg_promptpay_check_' . $order->get_id()) }}'
                          })
                        })
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                          if (data.success && data.data.status === 'succeeded') {
                            clearInterval(pollInterval);
                            location.reload();
                          }
                        })
                        .catch(function(err) { console.error('Polling error:', err); });
                      }
                      
                      pollInterval = setInterval(checkPaymentStatus, 5000);
                    })();
                  </script>
                @endif
              @elseif ($client_secret && $stripe_pp_key)
                <p class="mb-4 text-sm text-gray-700">
                  Scan this QR code with your Thai banking app to complete payment.  
                  This QR is valid for a limited time.
                </p>

                <div id="promptpay-status" class="mb-2 text-gray-700 text-sm">
                  Generating QR…
                </div>
                <div id="promptpay-qr-container" class="flex justify-center"></div>

                <p class="mt-4 text-xs text-gray-500 leading-relaxed">
                  After you complete the payment in your banking app, this page will update once Stripe
                  confirms your payment. You’ll also receive an email confirmation.
                </p>

                <script src="https://js.stripe.com/v3/"></script>
                <script>
                  (function() {
                    const clientSecret = @json($client_secret);
                    const stripe = Stripe(@json($stripe_pp_key));
                    
                    console.log('🔵 Client-side QR: Starting with clientSecret =', clientSecret ? 'exists' : 'missing');
                    console.log('🔵 Client-side QR: Stripe key =', @json($stripe_pp_key) ? 'exists' : 'missing');

                    const statusEl = document.getElementById('promptpay-status');
                    const qrContainer = document.getElementById('promptpay-qr-container');
                    
                    if (!clientSecret) {
                      statusEl.innerText = 'Error: Payment information missing. Please contact support.';
                      console.error('❌ Client-side QR: No clientSecret available');
                      return;
                    }
                    
                    if (!stripe) {
                      statusEl.innerText = 'Error: Stripe not initialized. Please refresh the page.';
                      console.error('❌ Client-side QR: Stripe not initialized');
                      return;
                    }

                    // Function to display QR code
                    function displayQRCode(qr) {
                      if (!qr) return false;
                      
                      const imageUrl = qr.image_url_png || qr.image_url_svg || qr.hosted_voucher_url;
                      if (!imageUrl) return false;

                      statusEl.innerText = 'Scan this QR code to pay:';
                      
                      // Clear container first
                      qrContainer.innerHTML = '';
                      
                      const img = document.createElement('img');
                      img.src = imageUrl;
                      img.style.width = '260px';
                      img.style.maxWidth = '100%';
                      img.style.height = 'auto';
                      img.alt = 'PromptPay QR Code';
                      img.loading = 'lazy';
                      img.onerror = function() {
                        statusEl.innerText = 'Error loading QR code. Please refresh the page.';
                      };
                      
                      qrContainer.appendChild(img);
                      return true;
                    }

                    // Function to check payment intent for QR code
                    function checkPaymentIntent(paymentIntent) {
                      if (!paymentIntent) {
                        console.log('⚠️ checkPaymentIntent: paymentIntent is null/undefined');
                        return false;
                      }
                      
                      console.log('🔵 checkPaymentIntent: Checking paymentIntent for QR code...');
                      console.log('🔵 checkPaymentIntent: status =', paymentIntent.status);
                      console.log('🔵 checkPaymentIntent: has next_action =', !!paymentIntent.next_action);
                      
                      // Try multiple possible locations for QR code
                      const nextAction = paymentIntent.next_action || {};
                      
                      console.log('🔵 checkPaymentIntent: next_action type =', nextAction.type);
                      console.log('🔵 checkPaymentIntent: next_action keys =', nextAction ? Object.keys(nextAction) : 'none');
                      
                      // Check various QR code locations
                      let qr = null;
                      
                      // First, check for promptpay_display_qr_code object
                      if (nextAction.promptpay_display_qr_code) {
                        qr = nextAction.promptpay_display_qr_code;
                        console.log('✅ checkPaymentIntent: Found promptpay_display_qr_code');
                      } 
                      // Check for display_qr_code (alternative name)
                      else if (nextAction.display_qr_code) {
                        qr = nextAction.display_qr_code;
                        console.log('✅ checkPaymentIntent: Found display_qr_code');
                      }
                      // Check if next_action itself has image URLs
                      else if (nextAction.image_url_png || nextAction.image_url_svg || nextAction.hosted_voucher_url) {
                        qr = nextAction;
                        console.log('✅ checkPaymentIntent: Found image URLs in next_action');
                      }
                      // Check direct properties on next_action
                      else if (paymentIntent.next_action?.promptpay_display_qr_code) {
                        qr = paymentIntent.next_action.promptpay_display_qr_code;
                        console.log('✅ checkPaymentIntent: Found promptpay_display_qr_code (direct access)');
                      }
                      
                      if (qr) {
                        console.log('🔵 checkPaymentIntent: QR object keys =', Object.keys(qr));
                        console.log('🔵 checkPaymentIntent: QR image_url_png =', qr.image_url_png);
                        console.log('🔵 checkPaymentIntent: QR image_url_svg =', qr.image_url_svg);
                        console.log('🔵 checkPaymentIntent: QR hosted_voucher_url =', qr.hosted_voucher_url);
                      } else {
                        console.warn('⚠️ checkPaymentIntent: No QR code found in paymentIntent');
                        console.log('🔵 checkPaymentIntent: Full next_action =', JSON.stringify(nextAction, null, 2));
                      }
                      
                      return displayQRCode(qr);
                    }

                    // Function to poll for payment intent updates
                    function pollForQRCode(attempts = 0, maxAttempts = 15) {
                      if (attempts >= maxAttempts) {
                        statusEl.innerText = 'QR code generation is taking longer than expected. Please refresh the page or contact support.';
                        return;
                      }

                      stripe.retrievePaymentIntent(clientSecret)
                        .then(function(result) {
                          if (result.error) {
                            statusEl.innerText = "Error: " + result.error.message;
                            console.error('Stripe polling error:', result.error);
                            return;
                          }

                          const paymentIntent = result.paymentIntent;
                          
                          // Check payment status
                          if (paymentIntent.status === 'succeeded' || paymentIntent.status === 'processing') {
                            statusEl.innerText = 'Payment confirmed! Thank you.';
                            return;
                          }

                          // Try to display QR code
                          if (checkPaymentIntent(paymentIntent)) {
                            return; // QR code displayed successfully
                          }

                          // If still no QR code and payment is not succeeded, keep polling
                          if (paymentIntent.status === 'requires_payment_method' || 
                              paymentIntent.status === 'requires_action') {
                            setTimeout(function() {
                              pollForQRCode(attempts + 1, maxAttempts);
                            }, 2000); // Poll every 2 seconds
                          } else {
                            statusEl.innerText = 'Payment status: ' + paymentIntent.status + '. Waiting for QR code...';
                            setTimeout(function() {
                              pollForQRCode(attempts + 1, maxAttempts);
                            }, 2000);
                          }
                        })
                        .catch(function(error) {
                          console.error('Polling error:', error);
                          if (attempts < maxAttempts) {
                            setTimeout(function() {
                              pollForQRCode(attempts + 1, maxAttempts);
                            }, 2000);
                          } else {
                            statusEl.innerText = "Error: Unable to retrieve payment information. Please refresh the page.";
                          }
                        });
                    }

                    // For PromptPay, we need to retrieve the PaymentIntent first
                    // The server-side code should have already confirmed it, but we'll check
                    statusEl.innerText = 'Retrieving payment information…';
                    
                    // First, try to retrieve the PaymentIntent (it may already be confirmed with QR code)
                    stripe.retrievePaymentIntent(clientSecret)
                    .then(function(result) {
                      console.log('🔵 Client-side QR: Retrieve result:', result);
                      
                      if (result.error) {
                        statusEl.innerText = "Error: " + result.error.message;
                        console.error('❌ Client-side QR: Retrieve error:', result.error);
                        return;
                      }

                      const paymentIntent = result.paymentIntent;
                      console.log('🔵 Client-side QR: PaymentIntent status =', paymentIntent?.status);
                      console.log('🔵 Client-side QR: PaymentIntent next_action =', paymentIntent?.next_action);
                      
                      // Check if payment succeeded
                      if (paymentIntent && (paymentIntent.status === 'succeeded' || paymentIntent.status === 'processing')) {
                        statusEl.innerText = 'Payment confirmed! Thank you.';
                        return;
                      }

                      // Try to display QR code if available
                      if (paymentIntent && checkPaymentIntent(paymentIntent)) {
                        console.log('✅ Client-side QR: QR code displayed successfully');
                        // Start polling for payment status updates
                        pollForQRCode(0, 15);
                        return;
                      }

                      // If no QR code and status allows it, try confirming the PaymentIntent
                      // This will generate the QR code
                      if (paymentIntent && paymentIntent.status !== 'requires_action' && 
                          paymentIntent.status !== 'succeeded' && paymentIntent.status !== 'processing') {
                        console.log('🔵 Client-side QR: No QR found, confirming PaymentIntent to generate QR...');
                        statusEl.innerText = 'Confirming payment to generate QR code…';
                        
                        // Use confirmPayment for PromptPay
                        return stripe.confirmPayment({
                          clientSecret: clientSecret,
                          confirmParams: {
                            payment_method_data: {
                              type: 'promptpay',
                            },
                            return_url: window.location.href,
                          },
                        });
                      }
                      
                      // If requires_action but no QR, start polling
                      console.warn('⚠️ Client-side QR: PaymentIntent requires_action but no QR code found, will poll...');
                      statusEl.innerText = 'Waiting for QR code generation…';
                      pollForQRCode(0, 15);
                      return null;
                    })
                    .then(function(result) {
                      // Handle confirmPayment result (if we got here from confirmation)
                      if (!result) return;
                      
                      console.log('🔵 Client-side QR: Confirm result:', result);
                      
                      if (result.error) {
                        statusEl.innerText = "Error: " + result.error.message;
                        console.error('❌ Client-side QR: Confirm error:', result.error);
                        
                        // Even if confirmation had an error, the PaymentIntent might have been updated
                        // Try retrieving again
                        return stripe.retrievePaymentIntent(clientSecret);
                      }

                      // Check result for QR code
                      const paymentIntent = result.paymentIntent || result;
                      console.log('🔵 Client-side QR: After confirm, status =', paymentIntent?.status);
                      console.log('🔵 Client-side QR: After confirm, next_action =', paymentIntent?.next_action);
                      
                      if (paymentIntent && (paymentIntent.status === 'succeeded' || paymentIntent.status === 'processing')) {
                        statusEl.innerText = 'Payment confirmed! Thank you.';
                        return;
                      }
                      
                      // Check for QR code after confirmation
                      if (paymentIntent && checkPaymentIntent(paymentIntent)) {
                        console.log('✅ Client-side QR: QR code found after confirmation');
                        pollForQRCode(0, 15);
                      } else {
                        console.warn('⚠️ Client-side QR: No QR code after confirmation, will poll...');
                        statusEl.innerText = 'Waiting for QR code generation…';
                        pollForQRCode(0, 15);
                      }
                      
                      return null;
                    })
                    .then(function(result) {
                      // If we got here from retrieve (after confirm error), check for QR code
                      if (!result) return;
                      
                      if (result.error) {
                        statusEl.innerText = "Error: " + result.error.message;
                        console.error('❌ Client-side QR: Final retrieve error:', result.error);
                        return;
                      }
                      
                      const paymentIntent = result.paymentIntent;
                      console.log('🔵 Client-side QR: Final retrieved PaymentIntent status =', paymentIntent?.status);
                      
                      if (paymentIntent && checkPaymentIntent(paymentIntent)) {
                        console.log('✅ Client-side QR: QR code found in final retrieved paymentIntent');
                        pollForQRCode(0, 15);
                      } else {
                        statusEl.innerText = 'Waiting for QR code generation…';
                        pollForQRCode(0, 15);
                      }
                    })
                    .catch(function(error) {
                      statusEl.innerText = "Error: " + (error.message || 'An error occurred while generating QR code');
                      console.error('❌ Client-side QR: Fatal error:', error);
                      console.error('❌ Error stack:', error.stack);
                    });
                  })();
                </script>
              @else
                <p class="text-sm text-red-600">
                  We couldn’t generate your PromptPay QR code. Please contact us so we can help you complete your payment.
                </p>
              @endif
            </div>
          @endif
        </div>
      </div>

      {{-- ORDER ITEMS + TOTALS --}}
      <div class="bg-white border border-gray-200 rounded-lg p-6 mb-12">
        <h2 class="text-lg font-semibold mb-4">Items in your order</h2>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm border-t border-gray-200">
            <thead class="bg-gray-50 text-gray-700">
              <tr>
                <th class="text-left py-2 pr-4">Product</th>
                <th class="text-right py-2 pl-4">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach ($order->get_items() as $item_id => $item)
                @php
                  $product = $item->get_product();
                @endphp
                <tr>
                  <td class="py-2 pr-4 align-top">
                    <div class="font-medium text-gray-900">
                      {{ $item->get_name() }} × {{ $item->get_quantity() }}
                    </div>
                    @if ($product && $product->get_sku())
                      <div class="text-xs text-gray-500">SKU: {{ $product->get_sku() }}</div>
                    @endif
                    @if ($meta = wc_display_item_meta($item, ['echo' => false]))
                      <div class="mt-1 text-xs text-gray-500">{!! $meta !!}</div>
                    @endif
                  </td>
                  <td class="py-2 pl-4 text-right align-top">
                    {!! wc_price( $item->get_total(), ['currency' => $order->get_currency()] ) !!}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
              <tr>
                <th class="text-right py-2 pr-4">Subtotal</th>
                <td class="text-right py-2 pl-4">
                  {!! $order->get_subtotal_to_display() !!}
                </td>
              </tr>
              @foreach ( $order->get_shipping_methods() as $shipping )
                <tr>
                  <th class="text-right py-2 pr-4">Shipping</th>
                  <td class="text-right py-2 pl-4">
                    {{ $shipping->get_name() }} – {!! wc_price( $shipping->get_total(), ['currency' => $order->get_currency()] ) !!}
                  </td>
                </tr>
              @endforeach
              @foreach ( $order->get_fees() as $fee )
                <tr>
                  <th class="text-right py-2 pr-4">{{ $fee->get_name() }}</th>
                  <td class="text-right py-2 pl-4">
                    {!! wc_price( $fee->get_total(), ['currency' => $order->get_currency()] ) !!}
                  </td>
                </tr>
              @endforeach
              @if ( ! empty( $order->get_discount_total() ) )
                <tr>
                  <th class="text-right py-2 pr-4">Discount</th>
                  <td class="text-right py-2 pl-4 text-green-700">
                    -{!! wc_price( $order->get_discount_total(), ['currency' => $order->get_currency()] ) !!}
                  </td>
                </tr>
              @endif
              <tr>
                <th class="text-right py-2 pr-4">Total</th>
                <td class="text-right py-2 pl-4 font-semibold">
                  {!! $order->get_formatted_order_total() !!}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- KEEP WOO HOOKS SO OTHER PLUGINS CAN INJECT CONTENT --}}
      @php
        do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id());
        do_action('woocommerce_thankyou', $order->get_id());
      @endphp
    @endif
  </div>
@endsection
