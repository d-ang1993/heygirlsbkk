/** @jsxImportSource react */
import React from "react";
import { createRoot } from "react-dom/client";
import ThankYou from "./components/ThankYou";

// Immediate console log to verify script is loading
console.log("🔵 ThankYou: Script file loaded and executing");

// Export mount function immediately - before any other code
window.mountThankYouPage = function boot() {
  console.log("🔵 ThankYou: Boot function called");

  const el = document.getElementById("thankyou-page-react");
  if (!el) {
    console.error("❌ ThankYou: Container element '#thankyou-page-react' not found in DOM");
    console.log("Available IDs:", Array.from(document.querySelectorAll("[id]")).map((e) => e.id));
    return;
  }

  const props = window.thankYouPageData || {};
  console.log("🔵 ThankYou: Props:", props);
  console.log("🔵 ThankYou: Props keys:", Object.keys(props));

  if (!props.orderData) {
    console.warn("⚠️ ThankYou: No orderData in props - component may not work correctly");
    // Still try to render with empty data
  }

  try {
    // Clear any existing content
    if (el._reactRoot) {
      console.log("🔵 ThankYou: Cleaning up existing React root");
      el._reactRoot.unmount();
      delete el._reactRoot;
    }

    console.log("🔵 ThankYou: Creating new React root");
    el._reactRoot = createRoot(el);

    console.log("🔵 ThankYou: Rendering component with props:", props);

    // Destructure props to pass correctly to component
    const componentProps = {
      orderData: props.orderData || {},
      qrCodeUrl: props.qrCodeUrl || null,
      isPromptPay: props.isPromptPay || false,
      isPaid: props.isPaid || false,
      clientSecret: props.clientSecret || "",
      intentId: props.intentId || "",
      stripePublishableKey: props.stripePublishableKey || "",
      ajaxUrl: props.ajaxUrl || "",
      nonce: props.nonce || "",
    };

    el._reactRoot.render(<ThankYou {...componentProps} />);
    console.log("✅ ThankYou: Component rendered successfully");

    // Hide loading spinner if exists and show component
    const loadingEl = document.getElementById("thankyou-loading");
    if (loadingEl) {
      loadingEl.classList.add("hidden");
    }

    if (el) {
      el.style.display = "block";
    }
  } catch (error) {
    console.error("❌ ThankYou: Error rendering component:", error);
    console.error("❌ Error stack:", error.stack);
    el.innerHTML = `<div class="p-8 bg-red-50 border border-red-200 rounded-lg"><p class="text-red-800">Error loading thank you page: ${error.message}</p><p class="text-sm text-red-600 mt-2">Please check the console for more details.</p></div>`;
  }
};

// Try to mount - wait for both DOM and data
function tryMount(attempts = 0) {
  const maxAttempts = 50;
  const el = document.getElementById("thankyou-page-react");
  const data = window.thankYouPageData;

  console.log(`🔵 ThankYou: Mount attempt ${attempts + 1}`, {
    elementFound: !!el,
    dataFound: !!data,
    dataKeys: data ? Object.keys(data) : [],
    mountFunctionAvailable: typeof window.mountThankYouPage === "function",
  });

  if (el && data && typeof window.mountThankYouPage === "function") {
    console.log("🔵 ThankYou: Calling mountThankYouPage");
    window.mountThankYouPage();
  } else if (attempts < maxAttempts) {
    setTimeout(() => tryMount(attempts + 1), 50);
  } else {
    console.error("❌ ThankYou: Failed to mount after max attempts", {
      elementFound: !!el,
      dataFound: !!data,
      mountFunctionAvailable: typeof window.mountThankYouPage === "function",
    });
  }
}

// Auto-mount when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    console.log("🔵 ThankYou: DOMContentLoaded fired");
    setTimeout(tryMount, 100);
  });
} else {
  console.log("🔵 ThankYou: DOM already loaded, starting mount process");
  setTimeout(tryMount, 100);
}

// Also try mounting after a short delay as fallback
setTimeout(() => {
  const el = document.getElementById("thankyou-page-react");
  const data = window.thankYouPageData;
  if (el && data && !el._reactRoot) {
    console.log("🔵 ThankYou: Fallback mount attempt");
    tryMount();
  }
}, 500);

