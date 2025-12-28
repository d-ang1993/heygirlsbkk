/** @jsxImportSource react */
import React, { useEffect } from "react";
import { extractPriceNumber } from "../../utils/priceUtils";

export default function OrderTotals({ checkoutData, formData }) {
  
  return (
    <dl className="space-y-6 border-t border-gray-200 px-4 py-6 sm:px-6">
      <div className="flex items-center justify-between">
        <dt className="text-sm">Subtotal</dt>
        <dd
          className="text-sm font-medium text-gray-900"
          dangerouslySetInnerHTML={{
            __html: checkoutData.cart_subtotal || "$0.00",
          }}
        />
      </div>

      {/* Display discount if applied */}
      {(() => {
        // Get discount total from checkoutData or formData
        const discountTotal = checkoutData?.discount_total ?? formData?.discount_total ?? 0;
        const hasAppliedCoupons = checkoutData?.applied_coupons?.length > 0;
        
        // Only show discount if there's a discount amount or applied coupons
        if (discountTotal <= 0 && !hasAppliedCoupons) {
          return null;
        }
        
        // Format discount amount (discount_total is numeric, format as price)
        const discountAmount = Math.abs(discountTotal);
        const currencySymbol = '&#3647;'; // Thai Baht symbol
        
        // Get coupon type information to determine if it's percentage or fixed
        const appliedCouponCode = checkoutData?.applied_coupons?.[0];
        const couponInfo = appliedCouponCode 
          ? (checkoutData?.coupon_info?.[appliedCouponCode] || {})
          : {};
        const discountType = couponInfo?.discount_type;
        
        // Determine discount display text based on coupon type
        let discountText = '';
        if (appliedCouponCode) {
          if (discountType === 'percent' || discountType === 'percent_product') {
            // Percentage-based discount - show the percentage from coupon amount
            const couponPercent = couponInfo?.amount || 0;
            discountText = ` - ${couponPercent}% off`;
          } else if (discountType === 'fixed_cart' || discountType === 'fixed_product') {
            // Fixed amount discount - show the fixed amount
            discountText = ` - ฿${discountAmount.toFixed(2)} off`;
          } else {
            // Fallback: calculate percentage from discount amount and subtotal
            const subtotalNum = extractPriceNumber(checkoutData.cart_subtotal || "0");
            const calculatedPercent = subtotalNum > 0 
              ? ((discountAmount / subtotalNum) * 100).toFixed(0)
              : null;
            discountText = calculatedPercent 
              ? ` - ${calculatedPercent}% off`
              : ` - ฿${discountAmount.toFixed(2)} off`;
          }
        }
        
        return (
          <div className="flex items-center justify-between">
            <dt className="text-sm">
              Discount
              {appliedCouponCode && (
                <span className="block text-xs font-normal text-gray-500 mt-0.5">
                  {appliedCouponCode}{discountText}
                </span>
              )}
            </dt>
            <dd
              className="text-sm font-medium text-green-600"
              dangerouslySetInnerHTML={{
                __html: `<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">${currencySymbol}</span>${discountAmount.toFixed(2)}</bdi></span>`,
              }}
            />
          </div>
        );
      })()}

      {formData?.shipping_total && (
        <div className="flex items-center justify-between">
          <dt className="text-sm">Shipping</dt>
          <dd
            className="text-sm font-medium text-gray-900"
            dangerouslySetInnerHTML={{
              __html: formData.shipping_total,
            }}
          />
        </div>
      )}

      {/* Display calculated tax using dynamic tax rate */}
      {(() => {
        // Get tax rate from checkoutData or formData
        const taxRate = checkoutData?.vat_tax ?? formData?.vat_tax ?? 0;
        const taxEnabled = checkoutData?.tax_enabled ?? false;
        
        // Only show tax if enabled and rate is greater than 0
        if (!taxEnabled && taxRate === 0) {
          return null;
        }
        
        // Calculate tax amount: (subtotal - discount + shipping) * tax_rate
        const subtotalNum = extractPriceNumber(checkoutData.cart_subtotal || "0");
        const discountTotal = checkoutData?.discount_total ?? formData?.discount_total ?? 0;
        const discountAmount = Math.abs(discountTotal);
        const shippingNum = extractPriceNumber(formData.shipping_total || "0");
        // Tax is calculated on discounted subtotal + shipping
        const taxableAmount = (subtotalNum - discountAmount) + shippingNum;
        const taxAmount = taxableAmount * taxRate;
        const taxRatePercent = (taxRate * 100).toFixed(2);
        
        // Get tax label from checkoutData or use default
        const taxLabel = checkoutData?.tax_totals?.[0]?.label || "Tax";
        
        return (
          <div className="flex items-center justify-between">
            <dt className="text-sm">
              Taxes
              <span className="block text-xs font-normal text-gray-500 mt-0.5">
                {taxLabel} ({taxRatePercent}%)
              </span>
            </dt>
            <dd
              className="text-sm font-medium text-gray-900"
              dangerouslySetInnerHTML={{
                __html: `<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">&#3647;</span>${taxAmount.toFixed(2)}</bdi></span>`,
              }}
            />
          </div>
        );
      })()}

      <div className="flex items-center justify-between border-t border-gray-200 pt-6">
        <dt className="text-base font-medium">Total</dt>
        <dd
          className="text-base font-medium text-gray-900"
          dangerouslySetInnerHTML={{
            __html:
              formData?.final_total !== undefined
                ? `<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">&#3647;</span>${formData.final_total.toFixed(2)}</bdi></span>`
                : checkoutData.cart_total || "$0.00",
          }}
        />
      </div>
    </dl>
  );
}
