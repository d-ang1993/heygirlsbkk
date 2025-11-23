/** @jsxImportSource react */
import React, { useMemo } from "react";

export default function ShippingAddress({
  formData,
  checkoutData,
  states,
  hasStates,
  loadingStates,
  onInputChange,
  isExpanded,
  onToggle,
}) {
  // Check if shipping address section is complete
  const isComplete = useMemo(() => {
    const requiredFields = [
      formData.billing_first_name?.trim(),
      formData.billing_last_name?.trim(),
      formData.billing_address_1?.trim(),
      formData.billing_city?.trim(),
      formData.billing_country?.trim(),
    ];
    
    const allRequiredFilled = requiredFields.every(field => field && field.length > 0);
    const stateValid = !hasStates || (hasStates && formData.billing_state?.trim());
    
    return allRequiredFilled && stateValid;
  }, [
    formData.billing_first_name,
    formData.billing_last_name,
    formData.billing_address_1,
    formData.billing_city,
    formData.billing_country,
    formData.billing_state,
    hasStates,
  ]);

  // Format address summary for collapsed view
  const formatAddressSummary = () => {
    const parts = [];
    if (formData.billing_address_1) {
      parts.push(formData.billing_address_1);
    }
    if (formData.billing_city || formData.billing_postcode || formData.billing_country) {
      const locationParts = [
        formData.billing_city,
        formData.billing_postcode,
      ].filter(Boolean);
      
      // Get country name from checkoutData if available
      if (formData.billing_country && checkoutData.countries) {
        const country = checkoutData.countries.find(c => c.key === formData.billing_country);
        if (country) {
          locationParts.push(country.label);
        }
      }
      
      if (locationParts.length > 0) {
        parts.push(locationParts.join(", "));
      }
    }
    return parts.length > 0 ? parts.join(", ") : "No address entered";
  };

  return (
    <div className="border-b border-gray-200 pt-6 pb-6">
      <button
        type="button"
        onClick={onToggle}
        className="flex w-full items-center justify-between text-left"
      >
        <div className="flex-1">
          <h2 className="text-base font-semibold text-gray-900">
            Shipping Address
          </h2>
          {!isExpanded && isComplete && (
            <div className="mt-1 text-sm text-gray-600">
              {formatAddressSummary()}
            </div>
          )}
        </div>
        <div className="ml-4 flex items-center gap-3">
          {isComplete && (
            <>
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
              <span className="text-sm text-gray-600">Edit</span>
            </>
          )}
          <svg
            className={`h-5 w-5 text-gray-500 transition-transform ${
              isExpanded ? "rotate-180" : ""
            }`}
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M19 9l-7 7-7-7"
            />
          </svg>
        </div>
      </button>

      {isExpanded && (
        <div className="mt-4 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-4">
          <div>
            <label
              htmlFor="first-name"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              First name{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
            <input
              id="first-name"
              name="billing_first_name"
              type="text"
              autoComplete="given-name"
              value={formData.billing_first_name}
              onChange={onInputChange}
              className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors"
            />
          </div>

          <div>
            <label
              htmlFor="last-name"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              Last name{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
            <input
              id="last-name"
              name="billing_last_name"
              type="text"
              autoComplete="family-name"
              value={formData.billing_last_name}
              onChange={onInputChange}
              className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors"
            />
          </div>

          <div className="sm:col-span-2">
            <label
              htmlFor="company"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              Company
            </label>
            <input
              id="company"
              name="billing_company"
              type="text"
              value={formData.billing_company}
              onChange={onInputChange}
              className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors"
            />
          </div>

          <div className="sm:col-span-2">
            <label
              htmlFor="address"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              Address{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
            <input
              id="address"
              name="billing_address_1"
              type="text"
              autoComplete="street-address"
              value={formData.billing_address_1}
              onChange={onInputChange}
              className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors"
            />
          </div>

          <div className="sm:col-span-2">
            <label
              htmlFor="apartment"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              Apartment, suite, etc.
            </label>
            <input
              id="apartment"
              name="billing_address_2"
              type="text"
              value={formData.billing_address_2}
              onChange={onInputChange}
              className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors"
            />
          </div>

          <div>
            <label
              htmlFor="city"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              City{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
            <input
              id="city"
              name="billing_city"
              type="text"
              autoComplete="address-level2"
              value={formData.billing_city}
              onChange={onInputChange}
              className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors"
            />
          </div>

          <div>
            <label
              htmlFor="country"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              Country{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
            <div className="relative">
              <select
                id="country"
                name="billing_country"
                autoComplete="country-name"
                value={formData.billing_country || ""}
                onChange={onInputChange}
                className="w-full appearance-none rounded-lg bg-white py-2 pl-3 pr-10 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors"
              >
                <option value="">Select a country</option>
                {checkoutData.countries && checkoutData.countries.length > 0 ? (
                  checkoutData.countries.map((country) => (
                    <option key={country.key} value={country.key}>
                      {country.label}
                    </option>
                  ))
                ) : (
                  <option value="">No countries available</option>
                )}
              </select>
              <svg
                viewBox="0 0 16 16"
                fill="currentColor"
                aria-hidden="true"
                className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 size-4 text-gray-500"
              >
                <path
                  d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                  clipRule="evenodd"
                  fillRule="evenodd"
                />
              </svg>
            </div>
          </div>

          <div>
            <label
              htmlFor="region"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              State / Province
            </label>
            {hasStates && states.length > 0 ? (
              <div className="relative">
                <select
                  id="region"
                  name="billing_state"
                  autoComplete="address-level1"
                  value={formData.billing_state || ""}
                  onChange={onInputChange}
                  disabled={loadingStates}
                  className="w-full appearance-none rounded-lg bg-white py-2 pl-3 pr-10 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <option value="">Select a state</option>
                  {states.map((state) => (
                    <option key={state.key} value={state.key}>
                      {state.label}
                    </option>
                  ))}
                </select>
                <svg
                  viewBox="0 0 16 16"
                  fill="currentColor"
                  aria-hidden="true"
                  className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 size-4 text-gray-500"
                >
                  <path
                    d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                    clipRule="evenodd"
                    fillRule="evenodd"
                  />
                </svg>
              </div>
            ) : (
              <input
                id="region"
                name="billing_state"
                type="text"
                autoComplete="address-level1"
                value={formData.billing_state || ""}
                onChange={onInputChange}
                disabled={loadingStates}
                placeholder={
                  loadingStates ? "Loading states..." : "State / Province"
                }
                className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              />
            )}
          </div>

          <div>
            <label
              htmlFor="postal-code"
              className="block text-sm font-medium text-gray-700 mb-1.5"
            >
              Postal code
            </label>
            <input
              id="postal-code"
              name="billing_postcode"
              type="text"
              autoComplete="postal-code"
              value={formData.billing_postcode}
              onChange={onInputChange}
              className="block w-full rounded-lg bg-white px-3 py-2 text-sm text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 transition-colors"
            />
          </div>
        </div>
      )}
    </div>
  );
}

