/** @jsxImportSource react */
import React, { useMemo, useState } from "react";

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
  // Track focus state for floating labels
  const [focusedFields, setFocusedFields] = useState({});
  
  const handleFocus = (fieldName) => {
    setFocusedFields(prev => ({ ...prev, [fieldName]: true }));
  };
  
  const handleBlur = (fieldName) => {
    setFocusedFields(prev => ({ ...prev, [fieldName]: false }));
  };
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
    <div className="border-b border-gray-200 pt-4 sm:pt-6 pb-4 sm:pb-6">
      <button
        type="button"
        onClick={onToggle}
        className="flex w-full items-center justify-between text-left py-2 -mx-2 px-2 rounded-lg hover:bg-gray-50 transition-colors"
      >
        <div className="flex-1 min-w-0 pr-2">
          <h2 className="text-sm sm:text-base font-semibold text-gray-900">
            Shipping Address
          </h2>
          {!isExpanded && isComplete && (
            <div className="mt-1 text-xs sm:text-sm text-gray-600 line-clamp-2">
              {formatAddressSummary()}
            </div>
          )}
        </div>
        <div className="ml-2 sm:ml-4 flex items-center gap-2 sm:gap-3 flex-shrink-0">
          {isComplete && (
            <>
              <svg
                className="h-4 w-4 sm:h-5 sm:w-5 text-green-600 flex-shrink-0"
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
              <span className="text-xs sm:text-sm text-gray-600 hidden sm:inline">Edit</span>
            </>
          )}
          <svg
            className={`h-4 w-4 sm:h-5 sm:w-5 text-gray-500 transition-transform flex-shrink-0 ${
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
        <div className="mt-3 sm:mt-4 grid grid-cols-1 gap-y-3 sm:gap-y-4 sm:grid-cols-2 sm:gap-x-4">
          <div className="relative">
            <input
              id="first-name"
              name="billing_first_name"
              type="text"
              autoComplete="given-name"
              value={formData.billing_first_name || ""}
              onChange={onInputChange}
              onFocus={() => handleFocus('billing_first_name')}
              onBlur={() => handleBlur('billing_first_name')}
              className="block w-full rounded-lg bg-white border border-gray-300 pl-3 pr-3 sm:pr-4 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all"
              placeholder=" "
            />
            <label
              htmlFor="first-name"
                className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                  formData.billing_first_name || focusedFields.billing_first_name
                    ? "top-1.5 sm:top-2 text-xs text-gray-700"
                    : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
                }`}
            >
              First name{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
          </div>

          <div className="relative">
            <input
              id="last-name"
              name="billing_last_name"
              type="text"
              autoComplete="family-name"
              value={formData.billing_last_name || ""}
              onChange={onInputChange}
              onFocus={() => handleFocus('billing_last_name')}
              onBlur={() => handleBlur('billing_last_name')}
              className="block w-full rounded-lg bg-white border border-gray-300 pl-3 pr-3 sm:pr-4 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all"
              placeholder=" "
            />
            <label
              htmlFor="last-name"
              className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                formData.billing_last_name || focusedFields.billing_last_name
                  ? "top-2 text-xs text-gray-700"
                  : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
              }`}
            >
              Last name{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
          </div>

          <div className="sm:col-span-2 relative">
            <input
              id="company"
              name="billing_company"
              type="text"
              value={formData.billing_company || ""}
              onChange={onInputChange}
              onFocus={() => handleFocus('billing_company')}
              onBlur={() => handleBlur('billing_company')}
              className="block w-full rounded-lg bg-white border border-gray-300 pl-3 pr-3 sm:pr-4 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all"
              placeholder=" "
            />
            <label
              htmlFor="company"
              className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                formData.billing_company || focusedFields.billing_company
                  ? "top-2 text-xs text-gray-700"
                  : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
              }`}
            >
              Company
            </label>
          </div>

          <div className="sm:col-span-2 relative">
            <input
              id="address"
              name="billing_address_1"
              type="text"
              autoComplete="street-address"
              value={formData.billing_address_1 || ""}
              onChange={onInputChange}
              onFocus={() => handleFocus('billing_address_1')}
              onBlur={() => handleBlur('billing_address_1')}
              className="block w-full rounded-lg bg-white border border-gray-300 pl-3 pr-3 sm:pr-4 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all"
              placeholder=" "
            />
            <label
              htmlFor="address"
              className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                formData.billing_address_1 || focusedFields.billing_address_1
                  ? "top-2 text-xs text-gray-700"
                  : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
              }`}
            >
              Address{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
          </div>

          <div className="sm:col-span-2 relative">
            <input
              id="apartment"
              name="billing_address_2"
              type="text"
              value={formData.billing_address_2 || ""}
              onChange={onInputChange}
              onFocus={() => handleFocus('billing_address_2')}
              onBlur={() => handleBlur('billing_address_2')}
              className="block w-full rounded-lg bg-white border border-gray-300 pl-3 pr-3 sm:pr-4 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all"
              placeholder=" "
            />
            <label
              htmlFor="apartment"
              className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                formData.billing_address_2 || focusedFields.billing_address_2
                  ? "top-2 text-xs text-gray-700"
                  : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
              }`}
            >
              Apartment, suite, etc.
            </label>
          </div>

          <div className="relative">
            <input
              id="city"
              name="billing_city"
              type="text"
              autoComplete="address-level2"
              value={formData.billing_city || ""}
              onChange={onInputChange}
              onFocus={() => handleFocus('billing_city')}
              onBlur={() => handleBlur('billing_city')}
              className="block w-full rounded-lg bg-white border border-gray-300 pl-3 pr-3 sm:pr-4 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all"
              placeholder=" "
            />
            <label
              htmlFor="city"
              className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                formData.billing_city || focusedFields.billing_city
                  ? "top-2 text-xs text-gray-700"
                  : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
              }`}
            >
              City{" "}
              <abbr className="required text-red-600 ml-0.5" title="required">
                *
              </abbr>
            </label>
          </div>

          <div className="relative">
            <div className="relative">
              <select
                id="country"
                name="billing_country"
                autoComplete="country-name"
                value={formData.billing_country || ""}
                onChange={onInputChange}
                onFocus={() => handleFocus('billing_country')}
                onBlur={() => handleBlur('billing_country')}
                className="w-full appearance-none rounded-lg bg-white border border-gray-300 pl-3 pr-8 sm:pr-10 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all hover:border-gray-400 hover:shadow-sm cursor-pointer shadow-sm touch-manipulation"
              >
                <option value=""></option>
                {checkoutData.countries && checkoutData.countries.length > 0 ? (
                  checkoutData.countries
                    .filter((country) => country.key === 'TH' || country.key === 'CA')
                    .map((country) => (
                      <option key={country.key} value={country.key}>
                        {country.label}
                      </option>
                    ))
                ) : (
                  <option value="">No countries available</option>
                )}
              </select>
              <label
                htmlFor="country"
                className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                  formData.billing_country || focusedFields.billing_country
                    ? "top-1.5 sm:top-2 text-xs text-gray-700"
                    : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
                }`}
              >
                Country{" "}
                <abbr className="required text-red-600 ml-0.5" title="required">
                  *
                </abbr>
              </label>
              <svg
                viewBox="0 0 16 16"
                fill="currentColor"
                aria-hidden="true"
                className="pointer-events-none absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 size-4 sm:size-5 text-gray-400 z-10 transition-transform duration-200"
              >
                <path
                  d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                  clipRule="evenodd"
                  fillRule="evenodd"
                />
              </svg>
            </div>
          </div>

          <div className="relative">
            {hasStates && states.length > 0 ? (
              <div className="relative">
                <select
                  id="region"
                  name="billing_state"
                  autoComplete="address-level1"
                  value={formData.billing_state || ""}
                  onChange={onInputChange}
                  onFocus={() => handleFocus('billing_state')}
                  onBlur={() => handleBlur('billing_state')}
                  disabled={loadingStates}
                  className="w-full appearance-none rounded-lg bg-white border border-gray-300 pl-3 pr-8 sm:pr-10 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all hover:border-gray-400 hover:shadow-sm cursor-pointer shadow-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-gray-300 disabled:hover:shadow-sm touch-manipulation"
                >
                  <option value=""></option>
                  {states.map((state) => (
                    <option key={state.key} value={state.key}>
                      {state.label}
                    </option>
                  ))}
                </select>
                <label
                  htmlFor="region"
                  className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                    formData.billing_state || focusedFields.billing_state
                      ? "top-2 text-xs text-gray-700"
                      : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
                  }`}
                >
                  State / Province
                </label>
                <svg
                  viewBox="0 0 16 16"
                  fill="currentColor"
                  aria-hidden="true"
                  className="pointer-events-none absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 size-4 sm:size-5 text-gray-400 z-10 transition-transform duration-200"
                >
                  <path
                    d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                    clipRule="evenodd"
                    fillRule="evenodd"
                  />
                </svg>
              </div>
            ) : (
              <>
                <input
                  id="region"
                  name="billing_state"
                  type="text"
                  autoComplete="address-level1"
                  value={formData.billing_state || ""}
                  onChange={onInputChange}
                  onFocus={() => handleFocus('billing_state')}
                  onBlur={() => handleBlur('billing_state')}
                  disabled={loadingStates}
                  placeholder=" "
                  className="block w-full rounded-lg bg-white border border-gray-300 pl-3 pr-4 pt-6 pb-2 text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                />
                <label
                  htmlFor="region"
                  className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                    formData.billing_state || focusedFields.billing_state
                      ? "top-2 text-xs text-gray-700"
                      : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
                  }`}
                >
                  {loadingStates ? "Loading states..." : "State / Province"}
                </label>
              </>
            )}
          </div>

          <div className="relative">
            <input
              id="postal-code"
              name="billing_postcode"
              type="text"
              autoComplete="postal-code"
              value={formData.billing_postcode || ""}
              onChange={onInputChange}
              onFocus={() => handleFocus('billing_postcode')}
              onBlur={() => handleBlur('billing_postcode')}
              className="block w-full rounded-lg bg-white border border-gray-300 pl-3 pr-3 sm:pr-4 pt-5 sm:pt-6 pb-2 text-base sm:text-sm text-gray-900 placeholder:text-transparent focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 focus:outline-none transition-all"
              placeholder=" "
            />
            <label
              htmlFor="postal-code"
              className={`absolute left-3 pointer-events-none transition-all duration-200 ${
                formData.billing_postcode || focusedFields.billing_postcode
                  ? "top-2 text-xs text-gray-700"
                  : "top-1/2 -translate-y-1/2 text-sm text-gray-500"
              }`}
            >
              Postal code
            </label>
          </div>
        </div>
      )}
    </div>
  );
}

