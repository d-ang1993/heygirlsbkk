/**
 * Shipping utility functions
 */

/**
 * Get default shipping method from available methods
 * @param {Array} shippingMethods - Array of available shipping methods
 * @returns {Object|null} - Default shipping method object or null
 */
export const getDefaultShippingMethod = (shippingMethods) => {
  if (!shippingMethods || shippingMethods.length === 0) {
    return null;
  }
  // Use the chosen method if available, otherwise use the first one
  return shippingMethods.find((m) => m.chosen) || shippingMethods[0];
};

/**
 * Get shipping cost from shipping method
 * @param {Array} shippingMethods - Array of available shipping methods
 * @param {string} methodId - Shipping method ID
 * @param {string} fallbackCost - Fallback cost if method not found
 * @returns {string} - Shipping cost (HTML formatted)
 */
export const getShippingCost = (shippingMethods, methodId, fallbackCost = "฿0.00") => {
  const selectedMethod = shippingMethods.find((m) => m.id === methodId);
  return selectedMethod?.cost || fallbackCost;
};

/**
 * Update shipping method via AJAX
 * @param {string} methodId - Shipping method ID
 * @param {string} checkoutUrl - Checkout URL for AJAX request
 * @returns {Promise} - Promise that resolves when update is complete
 */
export const updateShippingMethod = (methodId, checkoutUrl) => {
  if (typeof jQuery === "undefined") {
    return Promise.reject(new Error("jQuery is required"));
  }

  const fd = new FormData();
  fd.append("shipping_method[0]", methodId);
  fd.append("calc_shipping", "Calculate shipping");

  return fetch(checkoutUrl, {
    method: "POST",
    body: fd,
  }).then(() => {
    jQuery("body").trigger("update_checkout");
  });
};

/**
 * Check if shipping method is complete
 * @param {string} shippingMethod - Shipping method ID
 * @returns {boolean} - True if shipping method is set
 */
export const isShippingMethodComplete = (shippingMethod) => {
  return !!(shippingMethod && shippingMethod.length > 0);
};
