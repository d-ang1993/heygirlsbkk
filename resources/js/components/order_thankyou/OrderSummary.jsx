/** @jsxImportSource react */
import React from "react";

export default function OrderSummary({
  subtotalToDisplay,
  discountTotal,
  discountCode,
  formattedDiscountTotal,
  discountPercentage,
  shippingMethods = [],
  taxTotals = [],
  fees = [],
  formattedTotal,
}) {
  return (
    <>
      <h3 className="sr-only">Summary</h3>
      <dl className="space-y-6 border-t border-gray-200 pt-10 text-sm">
        <div className="flex justify-between">
          <dt className="font-medium text-gray-900">Subtotal</dt>
          <dd
            className="text-gray-700"
            dangerouslySetInnerHTML={{ __html: subtotalToDisplay }}
          />
        </div>

        {discountTotal > 0 && (
          <div className="flex justify-between">
            <dt className="flex font-medium text-gray-900">
              Discount
              {discountCode && (
                <span className="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-600">
                  {discountCode}
                </span>
              )}
            </dt>
            <dd className="text-gray-700">
              -<span dangerouslySetInnerHTML={{ __html: formattedDiscountTotal }} />
              {discountPercentage && ` (${discountPercentage}%)`}
            </dd>
          </div>
        )}

        {shippingMethods.map((shipping, index) => (
          <div key={index} className="flex justify-between">
            <dt className="font-medium text-gray-900">Shipping</dt>
            <dd
              className="text-gray-700"
              dangerouslySetInnerHTML={{ __html: shipping.formattedTotal }}
            />
          </div>
        ))}

        {taxTotals.map((tax, index) => (
          <div key={index} className="flex justify-between">
            <dt className="font-medium text-gray-900">{tax.label}</dt>
            <dd
              className="text-gray-700"
              dangerouslySetInnerHTML={{ __html: tax.formattedAmount }}
            />
          </div>
        ))}

        {fees.map((fee, index) => (
          <div key={index} className="flex justify-between">
            <dt className="font-medium text-gray-900">{fee.name}</dt>
            <dd
              className="text-gray-700"
              dangerouslySetInnerHTML={{ __html: fee.formattedTotal }}
            />
          </div>
        ))}

        <div className="flex justify-between">
          <dt className="font-medium text-gray-900">Total</dt>
          <dd
            className="text-gray-900 font-semibold"
            dangerouslySetInnerHTML={{ __html: formattedTotal }}
          />
        </div>
      </dl>
    </>
  );
}


