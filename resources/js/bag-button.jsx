/** @jsxImportSource react */
import React from "react";
import { createRoot } from "react-dom/client";
import { BagButtonWithSlider } from "./components/cart-slider";

/**
 * Initialize Bag Button with Cart Slider
 */
function initBagButton() {
  const mountPoint = document.getElementById("bag-button-react");

  if (!mountPoint) {
    console.warn("⚠️ Bag button mount point not found");
    return;
  }

  // Get data from mount point attributes
  const cartUrl = mountPoint.getAttribute("data-cart-url") || "/cart/";
  const bagCount = parseInt(mountPoint.getAttribute("data-bag-count") || "0", 10);

  // Mount React component
  const root = createRoot(mountPoint);
  root.render(<BagButtonWithSlider cartUrl={cartUrl} bagCount={bagCount} />);

  console.log("✅ Bag Button with Cart Slider initialized");
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initBagButton);
} else {
  initBagButton();
}

// Also listen for WooCommerce cart updates to refresh bag count
if (typeof jQuery !== "undefined") {
  jQuery(document.body).on("updated_wc_div wc_fragment_refresh wc_cart_updated", function () {
    // Update bag count from WooCommerce fragments
    const bagCountElement = document.querySelector(".bag-count");
    if (bagCountElement) {
      // WooCommerce will update this via fragments
      // We just need to ensure the React component re-renders if needed
    }
  });
}


