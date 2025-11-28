/**
 * Form validation utility functions
 */

/**
 * Validate contact information section
 * @param {Object} formData - Form data object
 * @returns {boolean} - True if contact info is valid
 */
export const validateContactInfo = (formData) => {
  return !!(
    formData.billing_email?.trim() &&
    formData.billing_phone?.trim()
  );
};

/**
 * Validate shipping address section
 * @param {Object} formData - Form data object
 * @param {boolean} hasStates - Whether the country has states/provinces
 * @returns {boolean} - True if shipping address is valid
 */
export const validateShippingAddress = (formData, hasStates = false) => {
  const requiredFields = [
    formData.billing_first_name?.trim(),
    formData.billing_last_name?.trim(),
    formData.billing_address_1?.trim(),
    formData.billing_city?.trim(),
    formData.billing_country?.trim(),
  ];

  const allRequiredFilled = requiredFields.every(
    (field) => field && field.length > 0
  );

  const stateValid =
    !hasStates || (hasStates && formData.billing_state?.trim());

  return allRequiredFilled && stateValid;
};

/**
 * Validate shipping method section
 * @param {string} shippingMethod - Shipping method ID
 * @returns {boolean} - True if shipping method is valid
 */
export const validateShippingMethod = (shippingMethod) => {
  return !!(shippingMethod && shippingMethod.length > 0);
};

/**
 * Validate payment method section
 * @param {string} paymentMethod - Payment method ID
 * @param {boolean} isStripeCreditCard - Whether Stripe credit card is selected
 * @param {boolean} isStripePaymentReady - Whether Stripe payment is ready
 * @returns {boolean} - True if payment method is valid
 */
export const validatePaymentMethod = (
  paymentMethod,
  isStripeCreditCard = false,
  isStripePaymentReady = false
) => {
  const paymentValid = paymentMethod && paymentMethod.length > 0;

  if (isStripeCreditCard) {
    return isStripePaymentReady;
  }

  return paymentValid;
};

/**
 * Validate entire checkout form
 * @param {Object} formData - Form data object
 * @param {boolean} hasStates - Whether the country has states/provinces
 * @param {boolean} isStripeCreditCard - Whether Stripe credit card is selected
 * @param {boolean} isStripePaymentReady - Whether Stripe payment is ready
 * @returns {boolean} - True if entire form is valid
 */
export const validateForm = (
  formData,
  hasStates = false,
  isStripeCreditCard = false,
  isStripePaymentReady = false
) => {
  const requiredFields = {
    billing_email: formData.billing_email?.trim(),
    billing_first_name: formData.billing_first_name?.trim(),
    billing_last_name: formData.billing_last_name?.trim(),
    billing_address_1: formData.billing_address_1?.trim(),
    billing_city: formData.billing_city?.trim(),
    billing_country: formData.billing_country?.trim(),
    billing_phone: formData.billing_phone?.trim(),
    payment_method: formData.payment_method?.trim(),
  };

  const allFieldsFilled = Object.values(requiredFields).every(
    (v) => v && v.length > 0
  );

  const stateValid =
    !hasStates || (hasStates && formData.billing_state?.trim());

  const paymentValid =
    formData.payment_method && formData.payment_method.length > 0;

  const paymentMethodValid = isStripeCreditCard
    ? isStripePaymentReady
    : paymentValid;

  return allFieldsFilled && stateValid && paymentMethodValid;
};
