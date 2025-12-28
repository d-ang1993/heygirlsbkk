/**
 * Cart utility functions
 */

/**
 * Calculate final total including subtotal, shipping, and tax
 * @param {string|number} subtotal - Cart subtotal
 * @param {string|number} shipping - Shipping cost
 * @param {number} vatTaxRate - VAT tax rate (e.g., 0.07 for 7%)
 * @param {Function} extractPriceNumber - Function to extract numeric value from price HTML
 * @returns {number} - Final total amount
 */
export const calculateFinalTotal = (subtotal, shipping, vatTaxRate, extractPriceNumber) => {
  const subtotalNum = extractPriceNumber(subtotal || "0");
  const shippingNum = extractPriceNumber(shipping || "0");
  const taxRate = parseFloat(vatTaxRate || 0);
  const taxAmount = (subtotalNum + shippingNum) * taxRate;
  return subtotalNum + shippingNum + taxAmount;
};

/**
 * Initialize quantities object from cart items
 * @param {Array} cartItems - Array of cart items
 * @returns {Object} - Object with item keys as keys and quantities as values
 */
export const initializeQuantities = (cartItems) => {
  const quantities = {};
  cartItems.forEach((item) => {
    quantities[item.key] = item.quantity;
  });
  return quantities;
};

/**
 * Get variation attributes for a cart item
 * @param {Object} item - Cart item object
 * @returns {Array} - Array of variation attribute strings
 */
export const getVariationAttributes = (item) => {
  const attrs = [];
  if (item.variation && typeof item.variation === "object") {
    Object.values(item.variation).forEach((value) => {
      if (value) attrs.push(value);
    });
  }
  return attrs;
};

/**
 * Update cart quantity via AJAX
 * @param {string} itemKey - Cart item key
 * @param {number} quantity - New quantity
 * @param {string} checkoutUrl - Checkout URL for AJAX request
 * @returns {Promise} - Promise that resolves when update is complete
 */
export const updateCartQuantity = (itemKey, quantity, checkoutUrl) => {
  if (typeof jQuery === "undefined") {
    return Promise.reject(new Error("jQuery is required"));
  }

  const fd = new FormData();
  fd.append(`cart[${itemKey}][qty]`, quantity);
  fd.append("update_cart", "Update Cart");

  return fetch(checkoutUrl, {
    method: "POST",
    body: fd,
  }).then(() => {
    jQuery("body").trigger("update_checkout");
  });
};

/**
 * Remove item from cart via AJAX
 * @param {string} itemKey - Cart item key to remove
 * @param {string} cartUrl - Cart URL for AJAX request
 * @param {string} nonce - WooCommerce cart nonce
 * @returns {Promise} - Promise that resolves when item is removed
 */
export const removeCartItem = (itemKey, cartUrl, nonce) => {
  if (typeof jQuery === "undefined") {
    return Promise.reject(new Error("jQuery is required"));
  }

  return fetch(cartUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      remove_item: itemKey,
      "woocommerce-cart-nonce": nonce,
    }),
  });
};
