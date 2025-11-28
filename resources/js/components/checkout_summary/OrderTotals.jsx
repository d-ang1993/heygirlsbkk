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
        
        // Calculate tax amount: (subtotal + shipping) * tax_rate
        const subtotalNum = extractPriceNumber(checkoutData.cart_subtotal || "0");
        const shippingNum = extractPriceNumber(formData.shipping_total || "0");
        const taxAmount = (subtotalNum + shippingNum) * taxRate;
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
