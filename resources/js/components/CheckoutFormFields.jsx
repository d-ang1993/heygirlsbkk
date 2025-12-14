/** @jsxImportSource react */
import React from "react";
import ContactInformation from "./checkout/ContactInformation";
import ShippingAddress from "./checkout/ShippingAddress";
import Delivery from "./checkout/Delivery";
import Payment from "./checkout/Payment";

export default function CheckoutFormFields({
  formData,
  checkoutData,
  paymentGateways,
  shippingMethods = [],
  shippingMethodsByZone = {},
  states,
  hasStates,
  loadingStates,
  onInputChange,
  onShippingChange,
  isStripeCreditCard,
  isStripePaymentReady,
  onStripePaymentReady,
  onStripePaymentMethodChange,
  stripeRef,
  expandedSections,
  onToggleSection,
  isContactComplete,
  isShippingAddressComplete,
}) {
  return (
    <div>
      {/* Title - aligned with Order Summary */}
      <h2 className="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Checkout details</h2>

      {/* Contact Information Section */}
      <ContactInformation
        formData={formData}
        onInputChange={onInputChange}
        isExpanded={expandedSections.contact}
        onToggle={() => onToggleSection("contact")}
      />

      {/* Shipping Address Section */}
      <ShippingAddress
        formData={formData}
        checkoutData={checkoutData}
        states={states}
        hasStates={hasStates}
        loadingStates={loadingStates}
        onInputChange={onInputChange}
        isExpanded={expandedSections.shipping}
        onToggle={() => onToggleSection("shipping")}
      />

      {/* Delivery (Shipping Method) Section */}
      <Delivery
        formData={formData}
        checkoutData={checkoutData}
        shippingMethods={shippingMethods}
        shippingMethodsByZone={shippingMethodsByZone || {}}
        onInputChange={onInputChange}
        onShippingChange={onShippingChange}
        isExpanded={expandedSections.shippingMethod}
        onToggle={() => onToggleSection("shippingMethod")}
      />

      {/* Payment Section */}
      <Payment
        formData={formData}
        checkoutData={checkoutData}
        paymentGateways={paymentGateways}
        onInputChange={onInputChange}
        isStripeCreditCard={isStripeCreditCard}
        isStripePaymentReady={isStripePaymentReady}
        onStripePaymentReady={onStripePaymentReady}
        onStripePaymentMethodChange={onStripePaymentMethodChange}
        stripeRef={stripeRef}
        isExpanded={expandedSections.payment}
        onToggle={() => onToggleSection("payment")}
        isContactComplete={isContactComplete}
        isShippingAddressComplete={isShippingAddressComplete}
      />
    </div>
  );
}
