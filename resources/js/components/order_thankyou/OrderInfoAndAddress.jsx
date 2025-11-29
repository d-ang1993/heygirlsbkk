/** @jsxImportSource react */
import React from "react";

export default function OrderInfoAndAddress({
  formattedShippingAddress,
  formattedBillingAddress,
  paymentMethodTitle,
  billingEmail,
  shippingMethods = [],
}) {
  const primaryShippingMethod = shippingMethods[0] || null;

  return (
    <div className="sm:ml-40 sm:pl-6">
      <h3 className="sr-only">Your information</h3>

      {/* Addresses */}
      <h4 className="sr-only">Addresses</h4>
      <dl className="grid grid-cols-1 sm:grid-cols-2 gap-x-6 py-10 text-sm">
        <div>
          <dt className="font-medium text-gray-900">Shipping address</dt>
          <dd
            className="mt-2 text-gray-700"
            dangerouslySetInnerHTML={{
              __html: formattedShippingAddress || formattedBillingAddress || "—",
            }}
          />
        </div>
        <div>
          <dt className="font-medium text-gray-900">Billing address</dt>
          <dd
            className="mt-2 text-gray-700"
            dangerouslySetInnerHTML={{ __html: formattedBillingAddress || "—" }}
          />
        </div>
      </dl>

      {/* Payment and Shipping Info */}
      <h4 className="sr-only">Payment</h4>
      <dl className="grid grid-cols-1 sm:grid-cols-2 gap-x-6 border-t border-gray-200 py-10 text-sm">
        <div>
          <dt className="font-medium text-gray-900">Payment method</dt>
          <dd className="mt-2 text-gray-700">
            <p>{paymentMethodTitle || "—"}</p>
            {billingEmail && <p className="mt-1">{billingEmail}</p>}
          </dd>
        </div>
        <div>
          <dt className="font-medium text-gray-900">Shipping method</dt>
          <dd className="mt-2 text-gray-700">
            {primaryShippingMethod ? (
              <>
                <p>{primaryShippingMethod.name}</p>
                <p className="mt-1">Takes 4–10 business days</p>
              </>
            ) : (
              <p>—</p>
            )}
          </dd>
        </div>
      </dl>
    </div>
  );
}


