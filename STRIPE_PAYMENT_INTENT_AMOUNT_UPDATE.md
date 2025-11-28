# Stripe PaymentIntent Amount Update

## Client-Side (React) - ✅ COMPLETE

The client-side code in `CheckoutForm.jsx` now sends the calculated `final_total` amount when creating the PaymentIntent:

```javascript
const amountInSatang = Math.round((formData.final_total || 0) * 100);
// Sent as 'amount' parameter in AJAX request
```

The amount is:
- Calculated client-side from: `subtotal + shipping + tax`
- Tax is calculated as: `(subtotal + shipping) * vatTaxRate`
- Converted to satang (smallest currency unit): `amount * 100`
- Sent as integer to match Stripe's requirement

## Server-Side (PHP) - ⚠️ REQUIRES UPDATE

The PHP server code in `hg_stripe_cc_create_payment_intent()` needs to be updated to accept and use the client-provided amount.

### Current Server Code:
```php
$total_raw = WC()->cart->get_total( 'edit' ); // Gets from WooCommerce cart
$amount    = (int) round( floatval( $total_raw ) * 100 );
```

### Recommended Update:

Replace the amount calculation section in `hg_stripe_cc_create_payment_intent()` with:

```php
// Check if client provided amount (in satang)
$client_amount = isset( $_POST['amount'] ) ? intval( $_POST['amount'] ) : 0;

if ( $client_amount > 0 ) {
    // Use client-provided amount for consistency with client-side calculations
    $amount = $client_amount;
    
    // Log for debugging
    error_log( sprintf( 
        '🔵 Using client-provided amount: %s satang (%s baht)', 
        $amount, 
        number_format( $amount / 100, 2 ) 
    ) );
} else {
    // Fallback to WooCommerce cart total if client amount not provided
    $total_raw = WC()->cart->get_total( 'edit' );
    $amount    = (int) round( floatval( $total_raw ) * 100 );
    
    error_log( sprintf( 
        '⚠️ Using WooCommerce cart total (client amount not provided): %s satang (%s baht)', 
        $amount, 
        number_format( $amount / 100, 2 ) 
    ) );
}

$currency = strtolower( get_woocommerce_currency() );
```

### Why This Change?

1. **Consistency**: Ensures PaymentIntent amount matches what the user sees on the client
2. **Real-time Calculations**: Client-side VAT calculations (which include shipping in the tax base) are used
3. **Accuracy**: Prevents mismatches between client display and PaymentIntent amount
4. **Fallback Safety**: Still works with WooCommerce cart total if client doesn't provide amount

### Important Notes:

- The amount is already in **satang** (smallest currency unit) - no need to multiply by 100 again
- The server should still validate the amount is reasonable (not negative, not zero, within expected range)
- The existing amount verification in `process_payment()` will still catch any mismatches
- The 50 cent tolerance in `process_payment()` helps with rounding differences

### Verification:

After updating the PHP code, verify:
1. PaymentIntent is created with the correct amount
2. Amount matches the order total when form is submitted
3. Payment verification in `process_payment()` passes without errors

