/** @jsxImportSource react */
import React from "react";
import { Button } from "../ui";

export default function PlaceOrderButton({
  isFormValid = false,
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

  // Only disable when form is not valid (not when submitting, as we want to show it's processing)
  const isDisabled = !isFormValid;
  
  // Prevent double submission while keeping button enabled visually
  const handleClick = (e) => {
    if (isSubmitting) {
      e.preventDefault();
      return false;
    }
  };
  
  return (
    <div className="border-t border-gray-200 px-4 py-6 sm:px-6">
      <Button
        type="submit"
        // This name/id/value/data-value combo matches Woo defaults
        name="woocommerce_checkout_place_order"
        id="place_order"
        value={buttonLabel}
        data-value={buttonLabel}
        variant="primary"
        size="md"
        fullWidth
        disabled={isDisabled}
        dimWhenDisabled={true}
        onClick={handleClick}
        title={!isFormValid ? "Complete required fields to continue" : isSubmitting ? "Processing payment..." : ""}
      >
        {buttonLabel}
      </Button>
    </div>
  );
}
