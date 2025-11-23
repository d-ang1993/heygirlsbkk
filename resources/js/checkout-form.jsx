/** @jsxImportSource react */
import React from "react";
import { createRoot } from "react-dom/client";
import CheckoutForm from "./components/CheckoutForm";

// Immediate console log to verify script is loading
console.log("🔵 CheckoutForm: Script file loaded and executing");

// Export mount function immediately - before any other code
window.mountCheckoutForm = function boot() {
  console.log("🔵 CheckoutForm: Boot function called");
  
  const el = document.getElementById("checkout-form-react");
  if (!el) {
    console.error("❌ CheckoutForm: Container element '#checkout-form-react' not found in DOM");
    console.log("Available IDs:", Array.from(document.querySelectorAll('[id]')).map(e => e.id));
    return;
  }

  const props = window.checkoutFormData || {};
  console.log("🔵 CheckoutForm: Props:", props);
  console.log("🔵 CheckoutForm: Props keys:", Object.keys(props));

  if (!props.checkoutData) {
    console.warn("⚠️ CheckoutForm: No checkoutData in props - component may not work correctly");
    // Still try to render with empty data
  }

  try {
    // Clear any existing content
    if (el._reactRoot) {
      console.log("🔵 CheckoutForm: Cleaning up existing React root");
      el._reactRoot.unmount();
      delete el._reactRoot;
    }

    console.log("🔵 CheckoutForm: Creating new React root");
    el._reactRoot = createRoot(el);

    console.log("🔵 CheckoutForm: Rendering component with props:", props);
    
    // Destructure props to pass correctly to component
    const componentProps = {
      checkoutData: props.checkoutData || {},
      cartItems: props.cartItems || [],
      shippingMethods: props.shippingMethods || [],
      shippingMethodsByZone: props.shippingMethodsByZone || {},
      paymentGateways: props.paymentGateways || [],
      checkoutUrl: props.checkoutUrl || '',
      cartUrl: props.cartUrl || '',
      ajaxUrl: props.ajaxUrl || '',
      nonce: props.nonce || '',
      checkoutNonce: props.checkoutNonce || '',        // Stripe AJAX nonce
      wooCheckoutNonce: props.wooCheckoutNonce || '',  // WooCommerce checkout nonce
      statesNonce: props.statesNonce || '',            // States/provinces nonce
    };
    
    el._reactRoot.render(<CheckoutForm {...componentProps} />);
    console.log("✅ CheckoutForm: Component rendered successfully");
    
    // Hide loading spinner and show form
    const loadingEl = document.getElementById('checkout-loading');
    const formEl = document.getElementById('checkout-form-react');
    
    if (loadingEl) {
      loadingEl.classList.add('hidden');
    }
    
    if (formEl) {
      formEl.style.display = 'block';
    }
  } catch (error) {
    console.error("❌ CheckoutForm: Error rendering component:", error);
    console.error("❌ Error stack:", error.stack);
    el.innerHTML = `<div class="p-8 bg-red-50 border border-red-200 rounded-lg"><p class="text-red-800">Error loading checkout form: ${error.message}</p><p class="text-sm text-red-600 mt-2">Please check the console for more details.</p></div>`;
  }
};

// Try to mount - wait for both DOM and data
function tryMount(attempts = 0) {
  const maxAttempts = 50;
  const el = document.getElementById("checkout-form-react");
  const data = window.checkoutFormData;
  
  console.log(`🔵 CheckoutForm: Mount attempt ${attempts + 1}`, {
    elementFound: !!el,
    dataFound: !!data,
    dataKeys: data ? Object.keys(data) : [],
    mountFunctionAvailable: typeof window.mountCheckoutForm === 'function'
  });
  
  if (el && data && typeof window.mountCheckoutForm === 'function') {
    console.log("🔵 CheckoutForm: Calling mountCheckoutForm");
    window.mountCheckoutForm();
  } else if (attempts < maxAttempts) {
    setTimeout(() => tryMount(attempts + 1), 50);
  } else {
    console.error("❌ CheckoutForm: Failed to mount after max attempts", {
      elementFound: !!el,
      dataFound: !!data,
      mountFunctionAvailable: typeof window.mountCheckoutForm === 'function'
    });
  }
}

// Auto-mount when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    console.log("🔵 CheckoutForm: DOMContentLoaded fired");
    setTimeout(tryMount, 100);
  });
} else {
  console.log("🔵 CheckoutForm: DOM already loaded, starting mount process");
  setTimeout(tryMount, 100);
}

// Also try mounting after a short delay as fallback
setTimeout(() => {
  const el = document.getElementById("checkout-form-react");
  const data = window.checkoutFormData;
  if (el && data && !el._reactRoot) {
    console.log("🔵 CheckoutForm: Fallback mount attempt");
    tryMount();
  }
}, 500);

