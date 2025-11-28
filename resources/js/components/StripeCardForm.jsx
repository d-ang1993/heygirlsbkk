/** @jsxImportSource react */
import React, {
  useEffect,
  useState,
  useImperativeHandle,
  forwardRef,
  useRef,
} from "react";
import { loadStripe } from "@stripe/stripe-js";
import {
  Elements,
  PaymentElement,
  useStripe,
  useElements,
} from "@stripe/react-stripe-js";

// Inner component that uses Stripe hooks + PaymentElement
const StripePaymentElement = forwardRef(function StripePaymentElement(
  { onPaymentReady },
  ref
) {
  const stripe = useStripe();
  const elements = useElements();
  const [error, setError] = useState(null);
  const [isReady, setIsReady] = useState(false);
  const [isComplete, setIsComplete] = useState(false);

  // Stripe + Elements mounted
  useEffect(() => {
    if (!stripe || !elements) return;
    setIsReady(true);
  }, [stripe, elements]);

  // Let parent know if payment UI is fully usable
  useEffect(() => {
    if (onPaymentReady) {
      onPaymentReady(isReady && isComplete && !error);
    }
  }, [isReady, isComplete, error, onPaymentReady]);

  // Expose confirmPayment to parent via ref
  useImperativeHandle(
    ref,
    () => ({
      /**
       * Confirm payment with a PaymentIntent client secret.
       * This matches your backend flow:
       *  - PI created via AJAX (`hg_stripe_cc_create_pi`)
       *  - clientSecret passed into this function
       *  - billingDetails contains customer name, email, phone, and address
       */
      async confirmPayment(clientSecret, billingDetails = {}) {
        if (!stripe || !elements) {
          throw new Error("Stripe not initialized");
        }

        if (!clientSecret) {
          throw new Error("Client secret is required");
        }

        // First submit PaymentElement to validate details
        const { error: submitError } = await elements.submit();
        if (submitError) {
          setError(submitError.message);
          throw submitError;
        }

        // Prepare confirmParams with billing details
        const confirmParams = {
          // We don't actually want a redirect; backend will verify PI.
          return_url: window.location.href,
        };

        // Add receipt email if provided (for receipt delivery)
        if (billingDetails.email) {
          confirmParams.receipt_email = billingDetails.email;
        }

        // Add billing details to payment method data
        // This ensures Stripe has customer name, email, and address for the payment
        const billingDetailsForStripe = {};
        
        if (billingDetails.name) {
          billingDetailsForStripe.name = billingDetails.name;
        }
        
        if (billingDetails.email) {
          billingDetailsForStripe.email = billingDetails.email;
        }
        
        if (billingDetails.phone) {
          billingDetailsForStripe.phone = billingDetails.phone;
        }
        
        if (billingDetails.address) {
          const address = {};
          if (billingDetails.address.line1) address.line1 = billingDetails.address.line1;
          if (billingDetails.address.line2) address.line2 = billingDetails.address.line2;
          if (billingDetails.address.city) address.city = billingDetails.address.city;
          if (billingDetails.address.postal_code) address.postal_code = billingDetails.address.postal_code;
          if (billingDetails.address.country) address.country = billingDetails.address.country;
          
          if (Object.keys(address).length > 0) {
            billingDetailsForStripe.address = address;
          }
        }

        // Only add payment_method_data if we have billing details to send
        if (Object.keys(billingDetailsForStripe).length > 0) {
          confirmParams.payment_method_data = {
            billing_details: billingDetailsForStripe,
          };
        }

        // Confirm the payment with the given client secret
        const { error: confirmError, paymentIntent } =
          await stripe.confirmPayment({
            elements,
            clientSecret,
            confirmParams,
            redirect: "if_required",
          });

        if (confirmError) {
          setError(confirmError.message);
          throw confirmError;
        }

        if (!paymentIntent || paymentIntent.status !== "succeeded") {
          throw new Error("Payment did not complete. Please try again.");
        }

        // Clear any previous error
        setError(null);

        // Return shape consumed by CheckoutForm
        return {
          paymentIntent, // full object; CheckoutForm will read .id
        };
      },

      // Optional accessor if you ever need it
      isReady: isReady && isComplete && !error,
    }),
    [stripe, elements, isReady, isComplete, error]
  );

  const paymentElementOptions = {
    layout: "tabs",
    paymentMethodOrder: ["card", "apple_pay", "google_pay", "link"],
  };

  return (
    <div className="mt-6">
      <div className="rounded-md bg-white px-3 py-2 outline outline-1 -outline-offset-1 outline-gray-300 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
        <PaymentElement
          options={paymentElementOptions}
          onChange={(e) => {
            if (e.error) {
              setError(e.error.message);
              setIsComplete(false);
            } else {
              setError(null);
              setIsComplete(e.complete);
            }
          }}
        />
      </div>

      {error && (
        <div className="mt-2 text-sm text-red-600" role="alert">
          {error}
        </div>
      )}

      {!isReady && (
        <div className="mt-2 text-sm text-gray-500">
          Loading payment options...
        </div>
      )}
    </div>
  );
});

// Main wrapper that loads Stripe and renders <Elements>
const StripeCardForm = forwardRef(function StripeCardForm(
  { publishableKey, amount = 0, currency = "thb", onPaymentReady },
  ref
) {
  const [stripePromise, setStripePromise] = useState(null);
  const [loadingError, setLoadingError] = useState(null);
  const paymentElementRef = useRef(null);

  // Expose confirmPayment to parent by delegating to inner ref
  useImperativeHandle(ref, () => ({
    async confirmPayment(clientSecret, billingDetails) {
      if (!paymentElementRef.current) {
        throw new Error("Payment element not ready");
      }
      // Pass billing details (name, email, phone, address) to Stripe
      // This ensures customer information is captured in Stripe
      return await paymentElementRef.current.confirmPayment(
        clientSecret,
        billingDetails
      );
    },
  }));

  useEffect(() => {
    if (!publishableKey) {
      setLoadingError("No publishable key provided");
      return;
    }

    let isMounted = true;

    loadStripe(publishableKey)
      .then((stripe) => {
        if (isMounted) {
          setStripePromise(stripe);
          setLoadingError(null);
        }
      })
      .catch((error) => {
        if (isMounted) {
          setLoadingError(error.message || "Failed to load Stripe");
        }
      });

    return () => {
      isMounted = false;
    };
  }, [publishableKey]);

  if (loadingError) {
    return (
      <div className="mt-6">
        <div className="text-sm text-red-600" role="alert">
          Error loading payment form: {loadingError}
        </div>
      </div>
    );
  }

  if (!stripePromise) {
    return (
      <div className="mt-6">
        <div className="text-sm text-gray-500">Loading payment form...</div>
      </div>
    );
  }

  // Convert amount to cents; this is for Display / PaymentElement session
  const amountInCents = Math.round(parseFloat(amount || 0) * 100) || 0;

  const options = {
    mode: "payment",
    amount: amountInCents,
    currency: (currency || "thb").toLowerCase(),
    appearance: {
      theme: "stripe",
      variables: {
        colorPrimary: "#4F46E5",
        colorBackground: "#ffffff",
        colorText: "#374151",
        colorDanger: "#EF4444",
        fontFamily: "system-ui, sans-serif",
        spacingUnit: "4px",
        borderRadius: "6px",
      },
    },
  };

  return (
    <Elements stripe={stripePromise} options={options}>
      <StripePaymentElement
        ref={paymentElementRef}
        onPaymentReady={onPaymentReady}
      />
    </Elements>
  );
});

export default StripeCardForm;
