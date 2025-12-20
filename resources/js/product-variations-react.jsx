/** @jsxImportSource react */
import React from "react";
import { createRoot } from "react-dom/client";
import ProductVariations from "./components/product-variations/ProductVariations";

// Export mount function
window.mountProductVariationsReact = function boot() {
  console.log("🔵 ProductVariationsReact: Boot function called");

  const el = document.getElementById("product-variations-react-container");
  if (!el) {
    console.error("❌ ProductVariationsReact: Container element '#product-variations-react-container' not found in DOM");
    return;
  }

  const props = window.productVariationsReactData || {};
  console.log("🔵 ProductVariationsReact: Props:", props);

  try {
    // Clear any existing content
    if (el._reactRoot) {
      console.log("🔵 ProductVariationsReact: Cleaning up existing React root");
      el._reactRoot.unmount();
      delete el._reactRoot;
    }

    console.log("🔵 ProductVariationsReact: Creating new React root");
    el._reactRoot = createRoot(el);

    console.log("🔵 ProductVariationsReact: Rendering component with props:", props);

    // Prepare props with defaults
    const componentProps = {
      productId: props.productId || null,
      productType: props.productType || 'simple',
      variations: props.variations || [],
      attributes: props.attributes || {},
      colorVariations: props.colorVariations || {},
      sizeVariations: props.sizeVariations || {},
      sizes: props.sizes || [],
      availableAttributes: props.availableAttributes || [],
    };

    el._reactRoot.render(<ProductVariations {...componentProps} />);
    console.log("✅ ProductVariationsReact: Component rendered successfully");
  } catch (error) {
    console.error("❌ ProductVariationsReact: Error rendering component:", error);
    console.error("❌ Error stack:", error.stack);
    el.innerHTML = `<div class="p-8 bg-red-50 border border-red-200 rounded-lg"><p class="text-red-800">Error loading product variations component: ${error.message}</p><p class="text-sm text-red-600 mt-2">Please check the console for more details.</p></div>`;
  }
};

// Try to mount - wait for both DOM and data
function tryMount(attempts = 0) {
  const maxAttempts = 50;
  const el = document.getElementById("product-variations-react-container");
  const data = window.productVariationsReactData;

  // If container doesn't exist, it's probably not a variable product - silently exit
  if (!el) {
    if (attempts === 0) {
      console.log("🔵 ProductVariationsReact: Container not found - likely not a variable product, skipping mount");
    }
    return;
  }

  console.log(`🔵 ProductVariationsReact: Mount attempt ${attempts + 1}`, {
    elementFound: !!el,
    dataFound: !!data,
    dataKeys: data ? Object.keys(data) : [],
    mountFunctionAvailable: typeof window.mountProductVariationsReact === "function",
  });

  if (el && data && typeof window.mountProductVariationsReact === "function") {
    console.log("🔵 ProductVariationsReact: Calling mountProductVariationsReact");
    window.mountProductVariationsReact();
  } else if (attempts < maxAttempts) {
    setTimeout(() => tryMount(attempts + 1), 50);
  } else {
    // Only log error if container exists but data is missing
    if (el) {
      console.error("❌ ProductVariationsReact: Failed to mount after max attempts", {
        elementFound: !!el,
        dataFound: !!data,
        mountFunctionAvailable: typeof window.mountProductVariationsReact === "function",
      });
    }
  }
}

// Auto-mount when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    console.log("🔵 ProductVariationsReact: DOMContentLoaded fired");
    setTimeout(tryMount, 100);
  });
} else {
  console.log("🔵 ProductVariationsReact: DOM already loaded, starting mount process");
  setTimeout(tryMount, 100);
}

// Also try mounting after a short delay as fallback
setTimeout(() => {
  const el = document.getElementById("product-variations-react-container");
  const data = window.productVariationsReactData;
  if (el && data && !el._reactRoot) {
    console.log("🔵 ProductVariationsReact: Fallback mount attempt");
    tryMount();
  }
}, 500);
