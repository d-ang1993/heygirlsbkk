# Stripe Payment Testing Guide

This guide explains how to test Stripe payments in your checkout form and verify that payments are being processed correctly.

## How the Payment Flow Works

1. **User fills out checkout form** - All required fields including billing/shipping information
2. **User selects payment method** - Chooses between "PromptPay (Thai QR)" or "Credit Card (Stripe)"
3. **User enters card details** - If Stripe is selected, user enters card information in the Stripe Payment Element
4. **User clicks "Confirm order"** - Form validates all fields are complete
5. **Stripe payment is confirmed** - Before form submission, Stripe payment is confirmed on the client side
6. **Form is submitted** - Payment intent ID is sent with form data to WooCommerce backend
7. **WooCommerce processes order** - Backend creates order and finalizes payment with Stripe

## Testing Setup

### 1. Verify Stripe Test Mode Keys

Make sure you're using Stripe test keys. Check the publishable key in:
- `resources/views/woocommerce/checkout/form-checkout.blade.php` (line 97)
- Should start with `pk_test_...` for test mode

### 2. Enable Stripe Test Mode in WooCommerce

1. Go to WooCommerce → Settings → Payments
2. Find your Stripe gateway (e.g., "HG Stripe Credit Card")
3. Enable test mode
4. Verify test publishable key and secret key are configured

## Testing the Payment Flow

### Step 1: Prepare Test Card

Use Stripe's test card numbers:
- **Success**: `4242 4242 4242 4242`
- **Decline**: `4000 0000 0000 0002`
- **Requires Authentication**: `4000 0025 0000 3155`

For all test cards:
- **Expiry**: Any future date (e.g., `12/34`)
- **CVC**: Any 3 digits (e.g., `123`)
- **ZIP**: Any 5 digits (e.g., `12345`)

### Step 2: Test Successful Payment

1. **Add product to cart** and go to checkout
2. **Fill out all required fields**:
   - Email address
   - First name, Last name
   - Address, City, Country
   - Phone number
3. **Select "Credit Card (Stripe)"** payment method
4. **Enter test card details**:
   - Card: `4242 4242 4242 4242`
   - Expiry: `12/34`
   - CVC: `123`
5. **Click "Confirm order"**
6. **Watch browser console** - You should see:
   - `✅ Stripe payment confirmed:` with payment intent details
   - Payment intent ID logged
7. **Check form submission** - Payment intent ID should be added as hidden field
8. **Verify redirect** - Should redirect to order confirmation/thank you page

### Step 3: Verify Payment in Stripe Dashboard

1. **Go to Stripe Dashboard**: https://dashboard.stripe.com/test/payments
2. **Look for the payment**:
   - Status should be "Succeeded"
   - Amount should match your order total
   - Payment method should show the test card (ending in 4242)
3. **Check payment details**:
   - Click on the payment to see full details
   - Verify amount, currency (THB), and customer email match your order

### Step 4: Verify Order in WooCommerce

1. **Go to WooCommerce → Orders**
2. **Find the new order**:
   - Should show order status (usually "Processing" or "Completed")
   - Payment method should be "Credit Card (Stripe)"
3. **Check order details**:
   - Billing/shipping information should match form data
   - Order total should match cart total
   - Payment status should be "Paid"

### Step 5: Test Payment Failures

1. **Use decline card**: `4000 0000 0000 0002`
2. **Try to submit** - Should show error message from Stripe
3. **Payment should not be processed** - No order should be created
4. **Check console** - Should see error logged

## Debugging

### Browser Console Logs

When testing, watch for these console messages:

- `✅ Stripe payment confirmed:` - Payment was successfully confirmed
- `❌ Stripe payment error:` - Payment failed (expected with decline card)
- `❌ Form submission error:` - General form submission error

### Common Issues

1. **"Stripe not initialized" error**
   - Check that publishable key is correct
   - Verify Stripe.js loaded successfully
   - Check network tab for failed requests

2. **Payment confirmed but order not created**
   - Check WooCommerce logs (WooCommerce → Status → Logs)
   - Verify Stripe secret key is configured in WooCommerce
   - Check that backend is processing the payment intent ID

3. **"Please complete all fields and payment details" stays disabled**
   - Verify all required fields are filled
   - Check that Stripe payment element shows card as complete
   - Look for validation errors in console

### Check Network Requests

1. **Open browser DevTools** → Network tab
2. **Submit form** and look for:
   - Stripe API requests (to `api.stripe.com`)
   - Form submission (POST to checkout URL)
   - Payment intent confirmation requests

## Production Checklist

Before going live:

- [ ] Switch to live Stripe keys (start with `pk_live_...`)
- [ ] Update publishable key in `form-checkout.blade.php`
- [ ] Configure live secret key in WooCommerce Stripe gateway settings
- [ ] Test with real card (small amount first)
- [ ] Verify webhooks are configured in Stripe dashboard
- [ ] Test failed payment scenarios
- [ ] Test refund process
- [ ] Set up order confirmation emails

## Additional Resources

- [Stripe Test Cards](https://stripe.com/docs/testing)
- [Stripe Dashboard](https://dashboard.stripe.com/)
- [WooCommerce Stripe Documentation](https://woocommerce.com/document/stripe/)

## Notes

- Payments are only processed when the form is submitted (not when card details are entered)
- Stripe uses "Payment Intents" API which provides better security and SCA compliance
- The payment intent ID is sent to WooCommerce backend to finalize the order
- Test mode payments don't actually charge cards - use test card numbers only

