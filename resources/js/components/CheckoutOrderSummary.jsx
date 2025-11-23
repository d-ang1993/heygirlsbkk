/** @jsxImportSource react */
import React from "react";

export default function CheckoutOrderSummary({
  cartItems,
  quantities,
  checkoutData,
  onQuantityChange,
  onRemoveItem,
  getVariationAttributes,
  isFormValid = false,
  isStripeCreditCard = false,
  isSubmitting = false,
}) {
  // Derive button label once so it's consistent for:
  // - visible text
  // - value attribute
  // - data-value (Woo JS uses this)
  const buttonLabel = isSubmitting
    ? "Processing payment..."
    : !isFormValid
    ? "Continue"
    : "Place order";

  return (
    <div className="mt-10 lg:mt-0">
      <h2 className="text-lg font-medium text-gray-900">Order summary</h2>

      <div className="mt-4 rounded-lg border border-gray-200 bg-white shadow-sm">
        <h3 className="sr-only">Items in your cart</h3>
        <ul role="list" className="divide-y divide-gray-200">
          {cartItems.map((item) => (
            <li key={item.key} className="flex px-4 py-6 sm:px-6">
              <div className="shrink-0">
                {item.permalink ? (
                  <a href={item.permalink}>
                    <img
                      src={item.image}
                      alt={item.name}
                      className="w-20 rounded-md"
                    />
                  </a>
                ) : (
                  <img
                    src={item.image}
                    alt={item.name}
                    className="w-20 rounded-md"
                  />
                )}
              </div>

              <div className="ml-6 flex flex-1 flex-col">
                <div className="flex">
                  <div className="min-w-0 flex-1">
                    <h4 className="text-sm">
                      {item.permalink ? (
                        <a
                          href={item.permalink}
                          className="font-medium text-gray-700 hover:text-gray-800"
                        >
                          {item.name}
                        </a>
                      ) : (
                        <span className="font-medium text-gray-700">
                          {item.name}
                        </span>
                      )}
                    </h4>
                    {getVariationAttributes(item).map((attr, idx) => (
                      <p key={idx} className="mt-1 text-sm text-gray-500">
                        {attr}
                      </p>
                    ))}
                  </div>

                  <div className="ml-4 flow-root shrink-0">
                    <button
                      type="button"
                      onClick={() => onRemoveItem(item.key)}
                      className="-m-2.5 flex items-center justify-center bg-white p-2.5 text-gray-400 hover:text-gray-500"
                    >
                      <span className="sr-only">Remove</span>
                      <svg
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        className="size-5"
                        aria-hidden="true"
                      >
                        <path
                          d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z"
                          clipRule="evenodd"
                          fillRule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>
                </div>

                <div className="flex flex-1 items-end justify-between pt-2">
                  <p
                    className="mt-1 text-sm font-medium text-gray-900"
                    dangerouslySetInnerHTML={{ __html: item.subtotal }}
                  />

                  <div className="ml-4">
                    <div className="grid grid-cols-1">
                      <select
                        id={`quantity-${item.key}`}
                        name={`cart[${item.key}][qty]`}
                        value={quantities[item.key] || item.quantity}
                        onChange={(e) =>
                          onQuantityChange(item.key, parseInt(e.target.value))
                        }
                        aria-label="Quantity"
                        className="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                      >
                        {[...Array(10)].map((_, i) => (
                          <option key={i + 1} value={i + 1}>
                            {i + 1}
                          </option>
                        ))}
                      </select>
                      <svg
                        viewBox="0 0 16 16"
                        fill="currentColor"
                        className="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4"
                        aria-hidden="true"
                      >
                        <path
                          d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                          clipRule="evenodd"
                          fillRule="evenodd"
                        />
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          ))}
        </ul>

        {/* Order Totals */}
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

          {checkoutData.shipping_total && (
            <div className="flex items-center justify-between">
              <dt className="text-sm">Shipping</dt>
              <dd
                className="text-sm font-medium text-gray-900"
                dangerouslySetInnerHTML={{
                  __html: checkoutData.shipping_total,
                }}
              />
            </div>
          )}

          {checkoutData.tax_total && (
            <div className="flex items-center justify-between">
              <dt className="text-sm">Taxes</dt>
              <dd
                className="text-sm font-medium text-gray-900"
                dangerouslySetInnerHTML={{
                  __html: checkoutData.tax_total,
                }}
              />
            </div>
          )}

          <div className="flex items-center justify-between border-t border-gray-200 pt-6">
            <dt className="text-base font-medium">Total</dt>
            <dd
              className="text-base font-medium text-gray-900"
              dangerouslySetInnerHTML={{
                __html: checkoutData.cart_total || "$0.00",
              }}
            />
          </div>
        </dl>

        {/* Place Order Button */}
        <div className="border-t border-gray-200 px-4 py-6 sm:px-6">
          <button
            type="submit"
            // This name/id/value/data-value combo matches Woo defaults
            name="woocommerce_checkout_place_order"
            id="place_order"
            value={buttonLabel}
            data-value={buttonLabel}
            disabled={!isFormValid || isSubmitting}
            title={!isFormValid ? "Complete required fields to continue" : ""}
            className={`w-full rounded-md border border-transparent px-4 py-3 text-base font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50 transition-opacity ${
              isFormValid && !isSubmitting
                ? "bg-indigo-600 hover:bg-indigo-700 cursor-pointer"
                : "bg-indigo-600 opacity-60 cursor-not-allowed"
            }`}
          >
            {buttonLabel}
          </button>
        </div>
      </div>
    </div>
  );
}
