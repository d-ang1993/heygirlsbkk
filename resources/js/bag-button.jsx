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

  // Create temporary button that works immediately (before React mounts)
  // This prevents the race condition where user clicks before React is ready
  const tempButton = document.createElement('a');
  tempButton.href = '#';
  tempButton.className = 'navbar-link navbar-bag cart-trigger';
  tempButton.setAttribute('data-cart-url', cartUrl);
  tempButton.setAttribute('role', 'button');
  tempButton.setAttribute('aria-label', 'Shopping cart');
  tempButton.innerHTML = `BAG(<span class="bag-count">${bagCount}</span>)`;
  
  // Click handler for temporary button
  const tempButtonHandler = (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    // Check if mobile (redirect to cart page on mobile)
    if (window.innerWidth <= 768) {
      if (cartUrl) {
        window.location.href = cartUrl;
      }
      return;
    }

    // On desktop, try to open cart slider if available
    if (typeof window.openCartDrawer === 'function') {
      window.openCartDrawer();
    } else {
      // If cart slider not ready yet, redirect to cart page as fallback
      if (cartUrl) {
        window.location.href = cartUrl;
      }
    }
  };
  
  tempButton.addEventListener('click', tempButtonHandler);
  
  // Add temporary button to mount point immediately
  mountPoint.appendChild(tempButton);

  // Mount React component (this will replace the temporary button)
  // Use requestAnimationFrame to ensure DOM is ready
  requestAnimationFrame(() => {
  const root = createRoot(mountPoint);
  root.render(<BagButtonWithSlider cartUrl={cartUrl} bagCount={bagCount} />);
  });

  console.log("✅ Bag Button with Cart Slider initialized");
}

// Initialize immediately if mount point exists, otherwise wait for DOM
// This ensures the button is available as early as possible
function tryInitBagButton() {
  const mountPoint = document.getElementById("bag-button-react");
  if (mountPoint) {
    initBagButton();
  } else if (document.readyState === "loading") {
    // Mount point not found yet, wait for DOM
    document.addEventListener("DOMContentLoaded", tryInitBagButton);
} else {
    // DOM is ready but mount point still not found, try one more time
    setTimeout(tryInitBagButton, 100);
  }
}

// Try to initialize immediately
tryInitBagButton();

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


