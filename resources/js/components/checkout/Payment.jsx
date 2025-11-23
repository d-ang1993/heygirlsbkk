/** @jsxImportSource react */
import React, { useMemo } from "react";
import StripeCardForm from "../StripeCardForm";

export default function Payment({
  formData,
  checkoutData,
  paymentGateways,
  onInputChange,
  isStripeCreditCard,
  isStripePaymentReady,
  onStripePaymentReady,
  stripeRef,
  isExpanded,
  onToggle,
  isContactComplete,
  isShippingAddressComplete,
}) {
  // Check if payment section is complete
  const isComplete = useMemo(() => {
    if (!formData.payment_method) return false;
    if (isStripeCreditCard) {
      return isStripePaymentReady;
    }
    return true;
  }, [formData.payment_method, isStripeCreditCard, isStripePaymentReady]);

  return (
    <div className="pt-6">
      <div className="flex w-full items-center justify-between text-left">
        <div className="flex-1">
          <h2 className="text-base font-semibold text-gray-900">
            Payment
          </h2>
        </div>
        <div className="ml-4 flex items-center gap-3">
          {isComplete && (
            <svg
              className="h-5 w-5 text-green-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M5 13l4 4L19 7"
              />
            </svg>
          )}
        </div>
      </div>

      <div className="mt-4">
          {/* Trust message */}
          <div className="mb-4 rounded-lg bg-blue-50 px-3 py-2 text-xs text-gray-600">
            <div className="flex items-center gap-1.5">
              <svg
                className="h-3.5 w-3.5 flex-shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                />
              </svg>
              <span>Secure checkout via Stripe • Card details encrypted and never stored</span>
            </div>
          </div>

          <fieldset>
            <legend className="sr-only">Payment type</legend>
            <div className="space-y-3 sm:flex sm:items-center sm:space-x-8 sm:space-y-0">
              {paymentGateways.map((gateway) => (
                <div key={gateway.id} className="flex items-center">
                  <input
                    id={gateway.id}
                    name="payment_method"
                    type="radio"
                    value={gateway.id}
                    checked={formData.payment_method === gateway.id}
                    onChange={onInputChange}
                    className="payment-method-radio relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden"
                  />
                  <label
                    htmlFor={gateway.id}
                    className="ml-3 block text-sm/6 font-medium text-gray-700"
                  >
                    {gateway.title}
                  </label>
                </div>
              ))}
            </div>
          </fieldset>

          {/* Payment Gateway Fields */}
          {isStripeCreditCard && (
            <StripeCardForm
              ref={stripeRef}
              publishableKey={checkoutData.stripe_publishable_key}
              amount={checkoutData.cart_total_amount}
              currency={checkoutData.currency || "thb"}
              onPaymentReady={onStripePaymentReady}
            />
          )}

          {/* Order Notes */}
          {checkoutData.enable_order_notes && (
            <div className="mt-4">
              <label
                htmlFor="order_comments"
                className="block text-sm font-medium text-gray-700 mb-1.5"
              >
                Order notes (optional)
              </label>
              <textarea
                id="order_comments"
                name="order_comments"
                rows={3}
                value={formData.order_comments}
                onChange={onInputChange}
                placeholder="Notes about your order, e.g. special notes for delivery."
                className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors resize-none"
              />
            </div>
          )}
      </div>
    </div>
  );
}

