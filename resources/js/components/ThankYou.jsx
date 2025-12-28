/** @jsxImportSource react */
import React, { useState, useEffect } from "react";
import OrderSection from "./order_thankyou/OrderSection";
import { trackEvent } from "../utils/mixpanel.js";

export default function ThankYou({
  orderData = {},
  qrCodeUrl = null,
  isPromptPay = false,
  isPaid = false,
  clientSecret = "",
  intentId = "",
  stripePublishableKey = "",
  ajaxUrl = "",
  nonce = "",
}) {
  const [paymentStatus, setPaymentStatus] = useState(
    isPaid ? "paid" : isPromptPay ? "awaiting_payment" : "pending"
  );
  const [qrCodeImageUrl, setQrCodeImageUrl] = useState(qrCodeUrl);

  // Track Purchase event on mount
  useEffect(() => {
    if (orderData?.id) {
      const cart = orderData.items?.map(item => ({
        product_id: item.productId,
        name: item.name,
        quantity: item.quantity,
        price: item.price,
      })) || [];
      
      trackEvent("Purchase", {
        user_id: window.mixpanelUser?.id || null,
        transaction_id: orderData.id.toString(),
        revenue: parseFloat(orderData.total || 0),
        currency: orderData.currency || "THB",
        Cart: cart,
        Price: parseFloat(orderData.total || 0),
      });
    }
  }, [orderData]);

  // Poll for payment status updates if PromptPay
  useEffect(() => {
    if (!isPromptPay || isPaid || !intentId) return;

    let pollInterval;
    let pollCount = 0;
    const maxPolls = 60;

    const checkPaymentStatus = () => {
      pollCount++;
      if (pollCount > maxPolls) {
        clearInterval(pollInterval);
        return;
      }

      fetch(ajaxUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "hg_check_promptpay_status",
          order_id: orderData.id || "",
          intent_id: intentId,
          nonce: nonce,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success && data.data.status === "succeeded") {
            clearInterval(pollInterval);
            setPaymentStatus("paid");
            window.location.reload();
          }
        })
        .catch((err) => console.error("Polling error:", err));
    };

    pollInterval = setInterval(checkPaymentStatus, 5000);
    return () => clearInterval(pollInterval);
  }, [isPromptPay, isPaid, intentId, orderData.id, ajaxUrl, nonce]);

  if (!orderData || !orderData.id) {
    return (
      <main className="bg-white px-4 pb-24 pt-16 sm:px-6 sm:pt-24 lg:px-8 lg:py-32">
        <div className="mx-auto max-w-3xl">
          <h1 className="text-2xl font-semibold mb-4">Order not found</h1>
          <p className="text-gray-700">
            We couldn't find your order. If you believe this is a mistake, please contact us.
          </p>
        </div>
      </main>
    );
  }

  // Get status message
  const getStatusMessage = () => {
    if (paymentStatus === "paid") {
      return {
        title: "It's on the way!",
        description: `Your order #${orderData.orderNumber} has been confirmed and will be with you soon.`,
      };
    } else if (paymentStatus === "awaiting_payment") {
      return {
        title: "Complete your payment",
        description: `Your order #${orderData.orderNumber} is awaiting payment. Please complete the payment below.`,
      };
    } else {
      return {
        title: "Thank you for your order!",
        description: `Your order #${orderData.orderNumber} has been received.`,
      };
    }
  };

  const statusMessage = getStatusMessage();

  return (
    <OrderSection
      orderData={orderData}
      statusMessage={statusMessage}
      isPromptPay={isPromptPay}
      paymentStatus={paymentStatus}
      qrCodeImageUrl={qrCodeImageUrl}
    />
  );
}

