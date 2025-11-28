/** @jsxImportSource react */
import React from "react";
import CartItemsList from "./checkout_summary/CartItemsList";
import OrderTotals from "./checkout_summary/OrderTotals";
import PlaceOrderButton from "./checkout_summary/PlaceOrderButton";

export default function CheckoutOrderSummary({
  cartItems,
  quantities,
  checkoutData,
  formData,
  onQuantityChange,
  onRemoveItem,
  getVariationAttributes,
  isFormValid = false,
  isStripeCreditCard = false,
  isSubmitting = false,
}) {
  return (
    <div className="mt-10 lg:mt-0">
      <h2 className="text-lg font-medium text-gray-900">Order summary</h2>

      <div className="mt-4 rounded-lg border border-gray-200 bg-white shadow-sm">
        <CartItemsList
          cartItems={cartItems}
          quantities={quantities}
          onQuantityChange={onQuantityChange}
          onRemoveItem={onRemoveItem}
          getVariationAttributes={getVariationAttributes}
        />

        <OrderTotals checkoutData={checkoutData} formData={formData} />

        <PlaceOrderButton
          isFormValid={isFormValid}
          isSubmitting={isSubmitting}
        />
      </div>
    </div>
  );
}
