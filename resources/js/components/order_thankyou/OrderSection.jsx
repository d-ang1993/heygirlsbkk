/** @jsxImportSource react */
import React, { useEffect } from "react";
import OrderItems from "./OrderItems";
import OrderInfoAndAddress from "./OrderInfoAndAddress";
import OrderSummary from "./OrderSummary";

export default function OrderSection({
  orderData,
  statusMessage,
  isPromptPay,
  paymentStatus,
  qrCodeImageUrl,
}) {
  useEffect(() => {
    console.log("🧾 OrderSection orderData:", orderData);
    console.log("🧾 OrderSection statusMessage:", statusMessage);
  }, [orderData, statusMessage]);

  return (
    <main className="bg-white px-4 pb-24 pt-11 sm:px-6 sm:pt-24 lg:px-8 lg:py-32">
      <div className="mx-auto max-w-3xl">
        {/* Header */}
        <div className="max-w-xl">
          <h1 className="text-base font-medium text-indigo-600">Thank you!</h1>
          <p className="mt-2 text-4xl font-bold tracking-tight">{statusMessage.title}</p>
          <p className="mt-2 text-base text-gray-500">{statusMessage.description} </p>

          {/* Order meta: number + date */}
          {orderData?.orderNumber && orderData?.dateCreated && (
            <p className="mt-3 text-sm text-gray-500">
              <span>Placed on {orderData.dateCreated}</span>
            </p>
          )}

          {/* PromptPay QR Code Section */}
          {isPromptPay && paymentStatus !== "paid" && (
            <div className="mt-6">
              {qrCodeImageUrl ? (
                <div className="bg-gray-50 border border-gray-200 rounded-lg p-6">
                  <h2 className="text-lg font-semibold mb-3">Pay with PromptPay</h2>
                  <p className="mb-4 text-sm text-gray-700">
                    Scan this QR code with your Thai banking app to complete payment. This QR is
                    valid for a limited time.
                  </p>
                  <div className="flex justify-center mb-4">
                    <img
                      src={qrCodeImageUrl}
                      alt="PromptPay QR Code"
                      className="max-w-[260px] h-auto border border-gray-300 p-2.5 bg-white"
                    />
                  </div>
                  <p className="mt-4 text-xs text-gray-500 leading-relaxed">
                    After you complete the payment in your banking app, this page will update once
                    Stripe confirms your payment. You'll also receive an email confirmation.
                  </p>
                </div>
              ) : (
                <div className="bg-gray-50 border border-gray-200 rounded-lg p-6">
                  <div id="promptpay-status" className="mb-2 text-gray-700 text-sm">
                    Generating QR…
                  </div>
                  <div id="promptpay-qr-container" className="flex justify-center"></div>
                </div>
              )}
            </div>
          )}
        </div>

        {/* Order Section */}
        <section aria-labelledby="order-heading" className="mt-10 border-t border-gray-200">
          <h2 id="order-heading" className="sr-only">
            Your order
          </h2>

          <OrderItems items={orderData.items || []} />

          <OrderInfoAndAddress
            formattedShippingAddress={orderData.formattedShippingAddress}
            formattedBillingAddress={orderData.formattedBillingAddress}
            paymentMethodTitle={orderData.paymentMethodTitle}
            billingEmail={orderData.billingEmail}
            shippingMethods={orderData.shippingMethods || []}
          />

          <OrderSummary
            subtotalToDisplay={orderData.subtotalToDisplay}
            discountTotal={orderData.discountTotal}
            discountCode={orderData.discountCode}
            formattedDiscountTotal={orderData.formattedDiscountTotal}
            discountPercentage={orderData.discountPercentage}
            shippingMethods={orderData.shippingMethods || []}
            taxTotals={orderData.taxTotals || []}
            fees={orderData.fees || []}
            formattedTotal={orderData.formattedTotal}
          />
        </section>
      </div>
    </main>
  );
}


