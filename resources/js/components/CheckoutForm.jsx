/** @jsxImportSource react */
import React, { useState, useEffect, useRef, useMemo } from "react";
import CheckoutFormFields from "./CheckoutFormFields";
import CheckoutOrderSummary from "./CheckoutOrderSummary";
import { extractPriceNumber } from "../utils/priceUtils";
import {
  calculateFinalTotal,
  initializeQuantities,
  getVariationAttributes,
  updateCartQuantity,
  removeCartItem,
} from "../utils/cartUtils";
import {
  getDefaultShippingMethod,
  getShippingCost,
  updateShippingMethod,
  isShippingMethodComplete as checkShippingMethodComplete,
} from "../utils/shippingUtils";
import {
  fetchStatesForCountry,
  fetchTaxRateForCountry,
  isAddressField,
  triggerCheckoutUpdate,
} from "../utils/countryUtils";
import {
  validateForm,
  validateContactInfo,
  validateShippingAddress,
} from "../utils/formValidation";

export default function CheckoutForm({
  checkoutData = {},
  cartItems = [],
  shippingMethods = [],
  shippingMethodsByZone = {},
  paymentGateways = [],
  checkoutUrl = "",
  cartUrl = "",
  ajaxUrl = "",
  wcAjaxUrl = "",          // WooCommerce AJAX endpoint URL
  nonce = "",
  checkoutNonce = "",      // Stripe AJAX nonce
  wooCheckoutNonce = "",   // WooCommerce checkout nonce
  statesNonce = "",        // States/provinces nonce
  stripeGatewayId = "hg_stripe_creditcard",
}) {
  // Get initial shipping total from selected shipping method
  // Keep the WooCommerce HTML format
  const getInitialShippingTotal = () => {
    const initialMethodId = checkoutData.shipping_method || shippingMethods[0]?.id || "";
    const selectedMethod = shippingMethods.find(m => m.id === initialMethodId);
    return selectedMethod?.cost || checkoutData.shipping_total || "฿0.00";
  };

  const [formData, setFormData] = useState({
    billing_email: checkoutData.billing_email || "",
    billing_first_name: checkoutData.billing_first_name || "",
    billing_last_name: checkoutData.billing_last_name || "",
    billing_company: checkoutData.billing_company || "",
    billing_address_1: checkoutData.billing_address_1 || "",
    billing_address_2: checkoutData.billing_address_2 || "",
    billing_city: checkoutData.billing_city || "",
    billing_country: checkoutData.billing_country || "",
    billing_state: checkoutData.billing_state || "",
    billing_postcode: checkoutData.billing_postcode || "",
    billing_phone: checkoutData.billing_phone || "",
    shipping_method:
      checkoutData.shipping_method || (shippingMethods[0]?.id || ""),
    shipping_total: getInitialShippingTotal(),
    // Use WooCommerce tax rate if available, otherwise default to 0.07 (7%)
    vat_tax: checkoutData.tax_rate || 0.07,
    payment_method:
      checkoutData.payment_method || (paymentGateways[0]?.id || ""),
    order_comments: checkoutData.order_comments || "",
    final_total: 0,
  });

  const [quantities, setQuantities] = useState({});
  const [states, setStates] = useState([]);
  const [hasStates, setHasStates] = useState(false);
  const [loadingStates, setLoadingStates] = useState(false);
  const [isStripePaymentReady, setIsStripePaymentReady] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  const formRef = useRef(null);
  const stripeRef = useRef(null);
  
  // Helper function to determine initial section states
  const getInitialSectionStates = (contact, shipping, shippingMethod) => {
    // Close all completed sections on mount, then open first incomplete section
    const states = {
      contact: false,
      shipping: false,
      shippingMethod: false,
      payment: false, // Payment is always visible, not collapsible
    };
    
    // Open the first incomplete section
    if (!contact) {
      states.contact = true;
    } else if (!shipping) {
      // Contact is complete, close it and open shipping
      states.contact = false;
      states.shipping = true;
    } else if (!shippingMethod) {
      // Contact and shipping are complete, open shipping method
      states.contact = false;
      states.shipping = false;
      states.shippingMethod = true;
    } else {
      // All sections complete, keep all closed
      states.contact = false;
      states.shipping = false;
      states.shippingMethod = false;
    }
    
    return states;
  };

  // Initialize section states based on completion status
  // Use a ref to track if we've initialized to avoid recalculating on every render
  const initializationRef = useRef(false);
  const [expandedSections, setExpandedSections] = useState(() => {
    const contactComplete = !!(
      checkoutData.billing_email?.trim() && 
      checkoutData.billing_phone?.trim()
    );
    const shippingComplete = (() => {
      const requiredFields = [
        checkoutData.billing_first_name?.trim(),
        checkoutData.billing_last_name?.trim(),
        checkoutData.billing_address_1?.trim(),
        checkoutData.billing_city?.trim(),
        checkoutData.billing_country?.trim(),
      ];
      const allRequiredFilled = requiredFields.every(field => field && field.length > 0);
      return allRequiredFilled;
    })();
    const shippingMethodComplete = !!(
      checkoutData.shipping_method && checkoutData.shipping_method.length > 0
    );
    
    initializationRef.current = true;
    return getInitialSectionStates(contactComplete, shippingComplete, shippingMethodComplete);
  });
  
  // Toggle section expand/collapse
  const toggleSection = (section) => {
    setExpandedSections((prev) => ({
      ...prev,
      [section]: !prev[section],
    }));
  };

  // Check if contact is complete
  const isContactComplete = validateContactInfo(formData);

  // Check if shipping address is complete
  const isShippingAddressComplete = validateShippingAddress(formData, hasStates);

  // Check if shipping method is complete
  const isShippingMethodComplete = checkShippingMethodComplete(formData.shipping_method);

  // Track previous completion states to detect when sections become complete
  const prevCompletionRef = useRef({
    contact: isContactComplete,
    shipping: isShippingAddressComplete,
    shippingMethod: isShippingMethodComplete,
  });

  // Auto-expand next section when current section is completed
  useEffect(() => {
    // Wait for initialization to complete
    if (!initializationRef.current) return;

    const prev = prevCompletionRef.current;
    
    // When contact becomes complete, close it and open shipping (if not complete)
    if (!prev.contact && isContactComplete) {
      setTimeout(() => {
        setExpandedSections((prev) => ({
          ...prev,
          contact: false,
          shipping: !isShippingAddressComplete, // Open shipping only if it's not complete
        }));
      }, 500);
    }
    
    // When shipping becomes complete, close it and open shipping method (if not complete)
    if (!prev.shipping && isShippingAddressComplete) {
      setTimeout(() => {
        setExpandedSections((prev) => ({
          ...prev,
          shipping: false,
          shippingMethod: !isShippingMethodComplete, // Open shipping method only if it's not complete
        }));
      }, 500);
    }
    
    // When shipping method becomes complete, close it
    // Payment is always visible so no need to expand it
    if (!prev.shippingMethod && isShippingMethodComplete) {
      setTimeout(() => {
        setExpandedSections((prev) => ({
          ...prev,
          shippingMethod: false,
        }));
      }, 500);
    }
    
    // Update previous states
    prevCompletionRef.current = {
      contact: isContactComplete,
      shipping: isShippingAddressComplete,
      shippingMethod: isShippingMethodComplete,
    };
  }, [isContactComplete, isShippingAddressComplete, isShippingMethodComplete]);

  // Initialize quantities
  useEffect(() => {
    const initialQuantities = initializeQuantities(cartItems);
    setQuantities(initialQuantities);
  }, [cartItems]);

  // Update tax rate when WooCommerce tax data changes (e.g., country change)
  useEffect(() => {
    if (checkoutData.tax_rate !== undefined && checkoutData.tax_rate !== formData.vat_tax) {
      setFormData((prev) => ({
        ...prev,
        vat_tax: checkoutData.tax_rate,
      }));
      console.log("🔵 Tax rate updated from WooCommerce:", {
        oldRate: formData.vat_tax,
        newRate: checkoutData.tax_rate,
        taxEnabled: checkoutData.tax_enabled,
      });
    }
  }, [checkoutData.tax_rate, checkoutData.tax_enabled]);

  // Ensure shipping method is set if not already set
  useEffect(() => {
    if (!formData.shipping_method && shippingMethods.length > 0) {
      const defaultMethod = getDefaultShippingMethod(shippingMethods);
      if (defaultMethod) {
        setFormData((prev) => ({ ...prev, shipping_method: defaultMethod.id }));
      }
    }
  }, [shippingMethods, formData.shipping_method]);

  // Is current payment method our Stripe credit card gateway?
  const isStripeCreditCard =
    formData.payment_method === stripeGatewayId ||
    (formData.payment_method?.includes("creditcard") &&
      !formData.payment_method?.includes("promptpay"));

  // Reset Stripe "ready" state when switching away
  useEffect(() => {
    if (!isStripeCreditCard) {
      setIsStripePaymentReady(false);
    }
  }, [formData.payment_method, isStripeCreditCard]);

  // Debug: Log cart and checkout data
  useEffect(() => {
    console.log("🛒 ========== CART & CHECKOUT DATA ==========");
    console.log("📦 Cart Items:", cartItems);
    console.log("📦 Cart Items Count:", cartItems.length);
    console.log("📦 Quantities:", quantities);
    
    console.log("💰 Checkout Data:", checkoutData);
    console.log("💰 Subtotal:", checkoutData.cart_subtotal);
    console.log("💰 Shipping Total (from checkoutData):", checkoutData.shipping_total);
    console.log("💰 Tax Total (from checkoutData):", checkoutData.tax_total);
    console.log("💰 Cart Total:", checkoutData.cart_total);
    
    console.log("📝 Form Data:", formData);
    console.log("📝 Shipping Method:", formData.shipping_method);
    console.log("📝 Shipping Total (from formData):", formData.shipping_total);
    console.log("📝 VAT Tax:", formData.vat_tax);
    
    // Calculate totals from formData
    const subtotalNum = parseFloat(checkoutData.cart_subtotal?.replace(/[^\d.]/g, '') || 0);
    const shippingNum = parseFloat(formData.shipping_total || 0);
    const vatTaxRate = parseFloat(formData.vat_tax || 0);
    const taxAmount = subtotalNum * vatTaxRate;
    const calculatedTotal = subtotalNum + shippingNum + taxAmount;
    
    console.log("🧮 Calculated Totals:", {
      subtotal: subtotalNum,
      shipping: shippingNum,
      taxRate: vatTaxRate,
      taxAmount: taxAmount,
      total: calculatedTotal,
    });
    
    // Also expose to window for easy debugging in console
    if (typeof window !== "undefined") {
      window.debugCartData = {
        cartItems,
        quantities,
        checkoutData,
        formData,
        calculatedTotals: {
          subtotal: subtotalNum,
          shipping: shippingNum,
          taxRate: vatTaxRate,
          taxAmount: taxAmount,
          total: calculatedTotal,
        }
      };
      console.log("💡 Tip: Access all data via window.debugCartData");
    }
    console.log("🛒 =========================================");
  }, [cartItems, quantities, checkoutData, formData]);

  // Calculate final_total whenever cart items, shipping, or taxes change
  // Use WooCommerce tax amount if available, otherwise calculate from rate
  const calculatedFinalTotal = useMemo(() => {
    return calculateFinalTotal(
      checkoutData.cart_subtotal,
      formData.shipping_total,
      formData.vat_tax,
      extractPriceNumber,
      checkoutData.tax_total_amount // Use WooCommerce calculated tax if available
    );
  }, [
    checkoutData.cart_subtotal,
    formData.shipping_total,
    formData.vat_tax,
    checkoutData.tax_total_amount,
  ]);

  // Update formData.final_total when calculated value changes
  useEffect(() => {
    setFormData((prev) => ({
      ...prev,
      final_total: calculatedFinalTotal,
    }));
  }, [calculatedFinalTotal]);

  // Debug: countries
  useEffect(() => {
    console.log("🔵 Countries data:", {
      countriesCount: checkoutData.countries?.length || 0,
      countries: checkoutData.countries,
      selectedCountry: formData.billing_country,
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => {
      const updated = { ...prev, [name]: value };
      
      // Update tax rate when country, state, or postcode changes
      if (name === "billing_country") {
        handleFetchStatesForCountry(value);
        handleFetchTaxRateForCountry(value, updated.billing_state, updated.billing_postcode);
      } else if (name === "billing_state" || name === "billing_postcode") {
        // Update tax rate when state or postcode changes (if country is set)
        if (updated.billing_country) {
          handleFetchTaxRateForCountry(updated.billing_country, updated.billing_state, updated.billing_postcode);
        }
      }
      
      return updated;
    });

    // Trigger shipping recalculation when address fields change
    // This ensures available shipping methods are updated based on the new address
    if (isAddressField(name)) {
      triggerCheckoutUpdate();
    }
  };

  const handleFetchTaxRateForCountry = async (countryCode, state = '', postcode = '') => {
    if (!countryCode || !statesNonce || !ajaxUrl) {
      console.log("💰 Tax Rate: Unable to fetch (missing country, nonce, or ajaxUrl)");
      return;
    }

    try {
      const data = await fetchTaxRateForCountry(countryCode, ajaxUrl, statesNonce, state, postcode);
      
      if (data.success && data.data) {
        const taxRate = data.data.tax_rate || 0;
        const taxEnabled = data.data.tax_enabled || false;
        const taxRatePercent = (taxRate * 100).toFixed(2);
        
        // Update formData with the fetched tax rate
        setFormData((prev) => ({
          ...prev,
          vat_tax: taxRate,
        }));
        
        console.log("💰 Tax Rate:", {
          country: countryCode,
          rate: taxRate,
          ratePercent: `${taxRatePercent}%`,
          enabled: taxEnabled,
        });
      } else {
        console.log("💰 Tax Rate: No tax rate found for country", countryCode);
        // Set tax rate to 0 if no tax rate found
        setFormData((prev) => ({
          ...prev,
          vat_tax: 0,
        }));
      }
    } catch (error) {
      console.error("❌ Error fetching tax rate:", error);
    }
  };

  const handleFetchStatesForCountry = async (countryCode, preserveState = false) => {
    if (!countryCode || !statesNonce || !ajaxUrl) {
      setStates([]);
      setHasStates(false);
      if (!preserveState) {
        setFormData((prev) => ({ ...prev, billing_state: "" }));
      }
      return;
    }

    // Save current state value to preserve it if valid
    const currentState = formData.billing_state;

    setLoadingStates(true);

    try {
      const data = await fetchStatesForCountry(countryCode, ajaxUrl, statesNonce);
      setLoadingStates(false);

      if (data.success && data.data) {
        const statesList = data.data.states || [];
        const hasStatesList = data.data.has_states || false;
        setStates(statesList);
        setHasStates(hasStatesList);

        // If preserving state and current state is valid, keep it; otherwise clear
        if (preserveState && currentState) {
          const isValidState = statesList.some((state) => state.key === currentState);
          if (isValidState) {
            // Keep the current state - don't update formData
            return;
          }
        }

        // Clear state if not preserving or if preserved state is invalid
        setFormData((prev) => ({ ...prev, billing_state: "" }));
      } else {
        setStates([]);
        setHasStates(false);
        if (!preserveState) {
          setFormData((prev) => ({ ...prev, billing_state: "" }));
        }
      }
    } catch (error) {
      console.error("❌ Error fetching states:", error);
      setLoadingStates(false);
      setStates([]);
      setHasStates(false);
      if (!preserveState) {
        setFormData((prev) => ({ ...prev, billing_state: "" }));
      }
    }
  };

  // Fetch states on mount if country already set (preserve existing state from account)
  useEffect(() => {
    if (formData.billing_country) {
      // Preserve state on initial load if user has account data
      handleFetchStatesForCountry(formData.billing_country, true);
      handleFetchTaxRateForCountry(formData.billing_country, formData.billing_state, formData.billing_postcode);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleQuantityChange = (itemKey, quantity) => {
    setQuantities((prev) => ({ ...prev, [itemKey]: quantity }));
    updateCartQuantity(itemKey, quantity, checkoutUrl).catch((error) => {
      console.error("Error updating cart quantity:", error);
    });
  };

  const handleRemoveItem = (itemKey) => {
    removeCartItem(itemKey, cartUrl, nonce)
      .then(() => {
        window.location.reload();
      })
      .catch((error) => {
        console.error("Error removing cart item:", error);
      });
  };

  const handleShippingChange = (methodId, shippingCost = null) => {
    // Get shipping cost from method if not provided
    // Keep the WooCommerce HTML format
    const shippingTotal = shippingCost || getShippingCost(shippingMethods, methodId);
    
    console.log("🚚 handleShippingChange called:", {
      methodId,
      shippingCost,
      shippingTotal,
    });
    
    setFormData((prev) => {
      const updated = {
        ...prev, 
        shipping_method: methodId,
        shipping_total: shippingTotal,
      };
      console.log("✅ Updated formData with shipping:", {
        shipping_method: updated.shipping_method,
        shipping_total: updated.shipping_total,
      });
      return updated;
    });

    updateShippingMethod(methodId, checkoutUrl).catch((error) => {
      console.error("Error updating shipping method:", error);
    });
  };

  // Basic validation
  const isFormValid = validateForm(
    formData,
    hasStates,
    isStripeCreditCard,
    isStripePaymentReady
  );

  // 🔑 Final submission handler: Stripe first, then Woo
  const handleFormSubmit = async (e) => {
    e.preventDefault();

    if (!isFormValid || isSubmitting) {
      return;
    }

    // Ensure cart has items
    if (!cartItems || cartItems.length === 0) {
      console.error('❌ Cannot checkout: Cart is empty');
      alert('Your cart is empty. Please add items to your cart before checkout.');
      return;
    }

    setIsSubmitting(true);

    try {
      // If Stripe CC is selected, run full Stripe flow before posting to Woo
      if (isStripeCreditCard && stripeRef.current) {
        try {
          // Step 1: Create PaymentIntent via WP AJAX
          // Send client-calculated final_total to ensure consistency with client-side calculations
          // Convert final_total to satang (smallest currency unit for THB: 1 baht = 100 satang)
          const amountInSatang = Math.round((formData.final_total || 0) * 100);
          
          console.log("🔵 Creating PaymentIntent via AJAX", { 
            ajaxUrl, 
            checkoutNonce,
            clientCalculatedAmount: formData.final_total,
            amountInSatang,
            note: "Sending client-calculated amount to server. Server should use this if provided, otherwise fallback to WC()->cart->get_total()"
          });

          const response = await fetch(ajaxUrl, {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
              action: "hg_stripe_cc_create_pi",
              security: checkoutNonce,
              amount: amountInSatang.toString(),
            }),
          });
          
          const rawText = await response.text();
          console.log("🔵 PI raw response:", rawText);
          
          let data;
          try {
            data = JSON.parse(rawText);
          } catch (e) {
            console.error("❌ JSON parse error:", e);
            alert("Server returned invalid JSON: " + rawText);
            setIsSubmitting(false);
            return;
          }
          
          if (!data || !data.success || !data.data || !data.data.clientSecret) {
            const errorMsg =
              (data && data.data && data.data.message) ||
              "Unable to start payment. Please try again.";
            console.error("❌ Create PI error structured:", data);
            alert(errorMsg);
            setIsSubmitting(false);
            return;
          }
          
          const clientSecret = data.data.clientSecret;
          console.log("✅ PaymentIntent created:", clientSecret);

          // Step 2: Confirm payment with PaymentElement (StripeCardForm)
          const billingDetails = {
            name: `${formData.billing_first_name || ""} ${
              formData.billing_last_name || ""
            }`.trim() || undefined,
            email: formData.billing_email || undefined,
            phone: formData.billing_phone || undefined,
            address: {
              line1: formData.billing_address_1 || undefined,
              line2: formData.billing_address_2 || undefined,
              city: formData.billing_city || undefined,
              postal_code: formData.billing_postcode || undefined,
              country: formData.billing_country || undefined,
            },
          };

          const paymentResult = await stripeRef.current.confirmPayment(
            clientSecret,
            billingDetails
          );

          if (!paymentResult || !paymentResult.paymentIntent) {
            throw new Error("No PaymentIntent returned from Stripe.");
          }

          const pi = paymentResult.paymentIntent;
          const paymentIntentId = typeof pi === "string" ? pi : pi.id;

          console.log("✅ Stripe payment confirmed:", paymentIntentId);

          // Step 3: Add hidden field so PHP process_payment() can verify
          const paymentIntentInput = document.createElement("input");
          paymentIntentInput.type = "hidden";
          paymentIntentInput.name = "hg_stripe_cc_payment_intent_id";
          paymentIntentInput.value = paymentIntentId;
          formRef.current.appendChild(paymentIntentInput);
        } catch (stripeError) {
          console.error("❌ Stripe payment error:", stripeError);
          alert(
            stripeError.message ||
              "Payment failed. Please check your card and try again."
          );
          setIsSubmitting(false);
          return;
        }
      }

      // Step 4: Submit form to WooCommerce via wc-ajax=checkout endpoint
      // Build FormData with all form fields
      const formDataToSubmit = new FormData();
      
      // First, add all form fields from the DOM (for any visible inputs)
      const formElements = formRef.current.elements;
      for (let element of formElements) {
        if (element.name && !element.disabled) {
          if (element.type === 'checkbox' || element.type === 'radio') {
            if (element.checked) {
              formDataToSubmit.append(element.name, element.value);
            }
          } else if (element.type === 'select-multiple') {
            for (let option of element.selectedOptions) {
              formDataToSubmit.append(element.name, option.value);
            }
          } else {
            formDataToSubmit.append(element.name, element.value);
          }
        }
      }
      
      // Then, explicitly add all formData values from React state
      // This ensures fields are included even if sections are collapsed
      // Always add fields (even if empty) so they're present in FormData
      formDataToSubmit.set('billing_email', formData.billing_email || '');
      formDataToSubmit.set('billing_phone', formData.billing_phone || '');
      formDataToSubmit.set('billing_first_name', formData.billing_first_name || '');
      formDataToSubmit.set('billing_last_name', formData.billing_last_name || '');
      formDataToSubmit.set('billing_company', formData.billing_company || '');
      formDataToSubmit.set('billing_address_1', formData.billing_address_1 || '');
      formDataToSubmit.set('billing_address_2', formData.billing_address_2 || '');
      formDataToSubmit.set('billing_city', formData.billing_city || '');
      formDataToSubmit.set('billing_country', formData.billing_country || '');
      formDataToSubmit.set('billing_state', formData.billing_state || '');
      formDataToSubmit.set('billing_postcode', formData.billing_postcode || '');
      formDataToSubmit.set('payment_method', formData.payment_method || '');
      if (formData.order_comments) {
        formDataToSubmit.set('order_comments', formData.order_comments);
      }
      
      // Explicitly ensure shipping method is included if shipping methods are available
      if (shippingMethods && shippingMethods.length > 0) {
        const shippingMethodValue = formData.shipping_method || shippingMethods[0]?.id;
        if (shippingMethodValue) {
          formDataToSubmit.set('shipping_method[0]', shippingMethodValue);
          console.log('✅ Explicitly added shipping_method[0]:', shippingMethodValue);
        } else {
          console.error('❌ No shipping method value available!', {
            formDataShippingMethod: formData.shipping_method,
            firstMethodId: shippingMethods[0]?.id,
            shippingMethods: shippingMethods
          });
        }
      }
      
      // Add cart item quantities if they've been changed
      // WooCommerce stores cart in session, but we need to send quantity updates
      if (cartItems && cartItems.length > 0 && quantities) {
        cartItems.forEach((item) => {
          const quantity = quantities[item.key] || item.quantity;
          // Only send if quantity differs from original or if explicitly set
          if (quantities[item.key] !== undefined) {
            formDataToSubmit.append(`cart[${item.key}][qty]`, quantity);
            console.log(`✅ Added cart quantity for ${item.key}:`, quantity);
          }
        });
      }
      
      // Add wc-ajax parameter for WooCommerce AJAX endpoint
      formDataToSubmit.append('wc-ajax', 'checkout');
      
      // Debug: Log form data
      const formDataObj = {};
      for (let [key, value] of formDataToSubmit.entries()) {
        formDataObj[key] = value;
      }
      
      // Calculate final totals before submission
      const subtotalNum = parseFloat(checkoutData.cart_subtotal?.replace(/[^\d.]/g, '') || 0);
      const shippingNum = parseFloat(formData.shipping_total || 0);
      const vatTaxRate = parseFloat(formData.vat_tax || 0);
      const taxAmount = subtotalNum * vatTaxRate;
      const calculatedTotal = subtotalNum + shippingNum + taxAmount;
      
      console.log("🔵 ========== CHECKOUT SUBMISSION DATA ==========");
      console.log("📦 Cart Items:", cartItems);
      console.log("📦 Cart Items Count:", cartItems.length);
      console.log("📦 Quantities:", quantities);
      console.log("💰 PHP Session Data (from checkoutData):", {
        subtotal: checkoutData.cart_subtotal,
        shipping: checkoutData.shipping_total,
        tax: checkoutData.tax_total,
        total: checkoutData.cart_total,
      });
      console.log("📝 React Form Data:", {
        shipping_method: formData.shipping_method,
        shipping_total: formData.shipping_total,
        vat_tax: formData.vat_tax,
      });
      console.log("🧮 Calculated Totals:", {
        subtotal: subtotalNum,
        shipping: shippingNum,
        taxRate: vatTaxRate,
        taxAmount: taxAmount,
        total: calculatedTotal,
      });
      console.log("🔵 Submitting form to WooCommerce via wc-ajax=checkout:", {
        cartItems: cartItems.length,
        paymentMethod: formData.payment_method,
        shippingMethod: formData.shipping_method,
        shippingMethodsAvailable: shippingMethods?.length || 0,
        hasCartItems: cartItems.length > 0,
        formFields: Object.keys(formDataObj),
        hasShippingMethodInForm: formDataToSubmit.has('shipping_method[0]'),
        shippingMethodValue: formDataToSubmit.get('shipping_method[0]'),
        allFormData: formDataObj
      });
      console.log("🔵 ============================================");
      
      // Ensure all form fields are present
      const criticalFields = [
        'billing_email',
        'billing_first_name', 
        'billing_last_name',
        'billing_address_1',
        'billing_city',
        'billing_country',
        'billing_phone',
        'payment_method',
        'woocommerce-process-checkout-nonce',
        'woocommerce_checkout'
      ];
      
      // Add shipping method if shipping is needed
      if (shippingMethods && shippingMethods.length > 0) {
        criticalFields.push('shipping_method[0]');
      }
      
      const missingFields = criticalFields.filter(field => {
        const hasField = formDataToSubmit.has(field);
        const fieldValue = formDataToSubmit.get(field);
        if (!hasField || !fieldValue) {
          console.error(`❌ Missing field: ${field}`, { hasField, fieldValue });
        }
        return !hasField || !fieldValue;
      });
      
      if (missingFields.length > 0) {
        console.error('❌ Missing required fields:', missingFields);
        console.error('FormData contents:', Array.from(formDataToSubmit.entries()));
        alert('Missing required fields: ' + missingFields.join(', '));
        setIsSubmitting(false);
        return;
      }
      
      console.log("✅ All required fields present, submitting via wc-ajax...");
      
      // Submit via WooCommerce AJAX endpoint
      // Use the WooCommerce AJAX URL if provided, otherwise construct it
      const submitUrl = wcAjaxUrl || (checkoutUrl.includes('?') 
        ? `${checkoutUrl}&wc-ajax=checkout`
        : `${checkoutUrl}?wc-ajax=checkout`);
      
      console.log("🔵 Submitting to:", submitUrl);
      
      fetch(submitUrl, {
        method: 'POST',
        body: formDataToSubmit,
        credentials: 'same-origin',
        headers: {
          'Cache-Control': 'no-cache',
        },
      })
      .then(response => {
        console.log("🔵 WooCommerce response status:", response.status);
        return response.text();
      })
      .then(responseText => {
        console.log("🔵 WooCommerce response:", responseText);
        
        // Try to parse as JSON (WooCommerce may return JSON for errors)
        try {
          const jsonResponse = JSON.parse(responseText);
          
          if (jsonResponse.result === 'success') {
            // Redirect to thank you page
            if (jsonResponse.redirect) {
              window.location.href = jsonResponse.redirect;
            } else {
              // Fallback: redirect to checkout URL (should show thank you page)
              window.location.href = checkoutUrl;
            }
          } else {
            // Error response
            console.error('❌ WooCommerce checkout error:', jsonResponse);
            alert(jsonResponse.messages || 'Checkout failed. Please try again.');
            setIsSubmitting(false);
          }
        } catch (e) {
          // Not JSON - might be HTML (success page or error messages)
          // If it's HTML, WooCommerce likely processed it and we should redirect
          // Check if response contains error indicators
          if (responseText.includes('woocommerce-error') || 
              responseText.includes('error') && responseText.includes('checkout')) {
            console.error('❌ WooCommerce returned error HTML');
            // Try to extract error messages
            const errorMatch = responseText.match(/<li[^>]*class="[^"]*woocommerce-error[^"]*"[^>]*>(.*?)<\/li>/);
            if (errorMatch) {
              alert(errorMatch[1]);
            } else {
              alert('Checkout failed. Please check the form and try again.');
            }
            setIsSubmitting(false);
          } else {
            // Likely success - redirect to thank you page
            console.log("✅ Checkout successful, redirecting...");
            window.location.href = checkoutUrl;
          }
        }
      })
      .catch(error => {
        console.error("❌ Form submission error:", error);
        alert("An error occurred during checkout. Please try again.");
        setIsSubmitting(false);
      });
    } catch (error) {
      console.error("❌ Form submission error:", error);
      alert("An error occurred. Please try again.");
      setIsSubmitting(false);
    }
  };

  return (
    <div className="bg-gray-50">
      <div className="mx-auto max-w-2xl px-4 pb-24 pt-16 sm:px-6 lg:max-w-7xl lg:px-8">
        <h2 className="sr-only">Checkout</h2>

        <form
          ref={formRef}
          name="checkout"
          method="post"
          className="checkout woocommerce-checkout"
          action={checkoutUrl}
          encType="multipart/form-data"
          onSubmit={handleFormSubmit}
        >
          <div className="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
            {/* Left: form fields + payment selector + Stripe PaymentElement */}
            <CheckoutFormFields
              formData={formData}
              checkoutData={checkoutData}
              paymentGateways={paymentGateways}
              shippingMethods={shippingMethods}
              shippingMethodsByZone={shippingMethodsByZone}
              states={states}
              hasStates={hasStates}
              loadingStates={loadingStates}
              onInputChange={handleInputChange}
              onShippingChange={handleShippingChange}
              isStripeCreditCard={isStripeCreditCard}
              isStripePaymentReady={isStripePaymentReady}
              onStripePaymentReady={setIsStripePaymentReady}
              stripeRef={stripeRef}
              expandedSections={expandedSections}
              onToggleSection={toggleSection}
              isContactComplete={isContactComplete}
              isShippingAddressComplete={isShippingAddressComplete}
            />

            {/* Right: order summary + place order button */}
            <CheckoutOrderSummary
              cartItems={cartItems}
              quantities={quantities}
              checkoutData={checkoutData}
              formData={formData}
              onQuantityChange={handleQuantityChange}
              onRemoveItem={handleRemoveItem}
              getVariationAttributes={getVariationAttributes}
              isFormValid={isFormValid}
              isStripeCreditCard={isStripeCreditCard}
              isSubmitting={isSubmitting}
              onShippingChange={handleShippingChange}
            />
          </div>

          {/* WooCommerce required hidden fields */}
          <input
            type="hidden"
            name="woocommerce-process-checkout-nonce"
            value={wooCheckoutNonce}
          />
          <input type="hidden" name="woocommerce_checkout" value="1" />
          <input type="hidden" name="_wp_http_referer" value={checkoutUrl} />
          
          {/* Shipping method - WooCommerce expects this as array */}
          {formData.shipping_method && (
            <input
              type="hidden"
              name="shipping_method[0]"
              value={formData.shipping_method}
            />
          )}
          
          {/* Tell WooCommerce we're using the same address for shipping */}
          <input
            type="hidden"
            name="ship_to_different_address"
            value="0"
          />
          
          {/* Shipping address fields (same as billing since ship_to_different_address is 0) */}
          {/* WooCommerce may still expect these to be present */}
          <input type="hidden" name="shipping_first_name" value={formData.billing_first_name || ""} />
          <input type="hidden" name="shipping_last_name" value={formData.billing_last_name || ""} />
          <input type="hidden" name="shipping_company" value={formData.billing_company || ""} />
          <input type="hidden" name="shipping_address_1" value={formData.billing_address_1 || ""} />
          <input type="hidden" name="shipping_address_2" value={formData.billing_address_2 || ""} />
          <input type="hidden" name="shipping_city" value={formData.billing_city || ""} />
          <input type="hidden" name="shipping_state" value={formData.billing_state || ""} />
          <input type="hidden" name="shipping_postcode" value={formData.billing_postcode || ""} />
          <input type="hidden" name="shipping_country" value={formData.billing_country || ""} />
        </form>
      </div>
    </div>
  );
}
