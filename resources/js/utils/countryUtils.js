/**
 * Country and state utility functions
 */

/**
 * List of address fields that trigger shipping recalculation
 */
export const ADDRESS_FIELDS = [
  'billing_country',
  'billing_state',
  'billing_city',
  'billing_postcode',
  'billing_address_1'
];

/**
 * Fetch states/provinces for a country
 * @param {string} countryCode - Country code
 * @param {string} ajaxUrl - AJAX URL endpoint
 * @param {string} statesNonce - Nonce for state request
 * @returns {Promise<Object>} - Promise resolving to {success: boolean, data: {states: Array, has_states: boolean}}
 */
export const fetchStatesForCountry = async (countryCode, ajaxUrl, statesNonce) => {
  if (!countryCode || !statesNonce || !ajaxUrl) {
    return {
      success: false,
      data: { states: [], has_states: false },
    };
  }

  const fd = new FormData();
  fd.append("action", "get_states_for_country");
  fd.append("country", countryCode);
  fd.append("nonce", statesNonce);

  try {
    const response = await fetch(ajaxUrl, {
      method: "POST",
      body: fd,
    });
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("❌ Error fetching states:", error);
    return {
      success: false,
      data: { states: [], has_states: false },
      error,
    };
  }
};

/**
 * Check if a state is valid for the given states list
 * @param {string} stateCode - State code to validate
 * @param {Array} statesList - Array of state objects with 'key' property
 * @returns {boolean} - True if state is valid
 */
export const isStateValid = (stateCode, statesList) => {
  if (!stateCode || !statesList || statesList.length === 0) {
    return false;
  }
  return statesList.some((state) => state.key === stateCode);
};

/**
 * Check if a field name is an address field
 * @param {string} fieldName - Field name to check
 * @returns {boolean} - True if field is an address field
 */
export const isAddressField = (fieldName) => {
  return ADDRESS_FIELDS.includes(fieldName);
};

/**
 * Fetch tax rate for a country
 * @param {string} countryCode - Country code
 * @param {string} ajaxUrl - AJAX URL endpoint
 * @param {string} statesNonce - Nonce for tax rate request (uses same nonce as states)
 * @param {string} state - Optional state code
 * @param {string} postcode - Optional postcode
 * @returns {Promise<Object>} - Promise resolving to {success: boolean, data: {tax_rate: number, tax_enabled: boolean}}
 */
export const fetchTaxRateForCountry = async (countryCode, ajaxUrl, statesNonce, state = '', postcode = '') => {
  if (!countryCode || !statesNonce || !ajaxUrl) {
    return {
      success: false,
      data: { tax_rate: 0, tax_enabled: false },
    };
  }

  const fd = new FormData();
  fd.append("action", "get_tax_rate_for_country");
  fd.append("country", countryCode);
  fd.append("nonce", statesNonce);
  if (state) {
    fd.append("state", state);
  }
  if (postcode) {
    fd.append("postcode", postcode);
  }

  try {
    const response = await fetch(ajaxUrl, {
      method: "POST",
      body: fd,
    });
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("❌ Error fetching tax rate:", error);
    return {
      success: false,
      data: { tax_rate: 0, tax_enabled: false },
      error,
    };
  }
};

/**
 * Trigger WooCommerce checkout update
 */
export const triggerCheckoutUpdate = () => {
  if (typeof jQuery !== "undefined") {
    setTimeout(() => {
      jQuery("body").trigger("update_checkout");
    }, 100);
  }
};
