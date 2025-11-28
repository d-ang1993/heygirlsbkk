/** @jsxImportSource react */
import React from "react";

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

  return (
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
  );
}
