/** @jsxImportSource react */
import React, { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import { CartSlider } from "./index";
import { useCartSlider } from "./hooks/useCartSlider";

/**
 * Cart Slider App Component
 * Manages the cart slider state and integrates with existing cart trigger buttons
 */
function CartSliderApp() {
  const { isOpen, openCartSlider, closeCartSlider } = useCartSlider();

  useEffect(() => {
    // Find existing cart trigger button (try multiple selectors)
    const findCartTrigger = () => {
      return (
        document.querySelector(".cart-trigger") ||
        document.querySelector(".navbar-bag") ||
        document.querySelector('a[data-cart-url]')
      );
    };

    const cartTrigger = findCartTrigger();

    if (cartTrigger) {
      const handleCartClick = (e) => {
        e.preventDefault();
        e.stopPropagation();

        // Check if mobile (redirect to cart page on mobile)
        if (window.innerWidth <= 768) {
          const cartUrl = cartTrigger.getAttribute("data-cart-url");
          if (cartUrl) {
            window.location.href = cartUrl;
          }
          return;
        }

        // Open cart slider on desktop
        console.log("🛒 Opening cart slider from bag button");
        openCartSlider();
      };

      // Remove any existing listeners to avoid duplicates
      cartTrigger.removeEventListener("click", handleCartClick);
      cartTrigger.addEventListener("click", handleCartClick);

      // Expose global functions for backward compatibility
      window.openCartDrawer = openCartSlider;
      window.closeCartDrawer = closeCartSlider;

      console.log("✅ Cart slider connected to bag button");

      return () => {
        cartTrigger.removeEventListener("click", handleCartClick);
      };
    } else {
      // Retry after a short delay if button not found
      const timeout = setTimeout(() => {
        const retryTrigger = findCartTrigger();
        if (retryTrigger) {
          console.log("✅ Cart trigger found on retry");
          retryTrigger.addEventListener("click", (e) => {
            e.preventDefault();
            if (window.innerWidth > 768) {
              openCartSlider();
            } else {
              const cartUrl = retryTrigger.getAttribute("data-cart-url");
              if (cartUrl) window.location.href = cartUrl;
            }
          });
        }
      }, 500);

      return () => clearTimeout(timeout);
    }
  }, [openCartSlider, closeCartSlider]);

  // Listen for WooCommerce add to cart events
  useEffect(() => {
    const handleAddedToCart = () => {
      // Refresh cart if drawer is open
      if (isOpen) {
        // The CartSlider component will handle the refresh via its own event listeners
        console.log("🛒 Item added to cart, drawer will refresh");
      }
    };

    document.addEventListener("added_to_cart", handleAddedToCart);

    if (typeof jQuery !== "undefined") {
      jQuery(document.body).on("added_to_cart", handleAddedToCart);
    }

    return () => {
      document.removeEventListener("added_to_cart", handleAddedToCart);
      if (typeof jQuery !== "undefined") {
        jQuery(document.body).off("added_to_cart", handleAddedToCart);
      }
    };
  }, [isOpen]);

  return <CartSlider isOpen={isOpen} onClose={closeCartSlider} />;
}

/**
 * Initialize the Cart Slider React App
 * Call this function to mount the cart slider component
 */
export function initCartSliderApp() {
  // Find the existing cart drawer container or create one
  let container = document.getElementById("cart-drawer-react");

  if (!container) {
    // Create container if it doesn't exist
    container = document.createElement("div");
    container.id = "cart-drawer-react";
    document.body.appendChild(container);
  }

  // Clear any existing content
  container.innerHTML = "";

  // Mount React app
  const root = createRoot(container);
  root.render(<CartSliderApp />);

  console.log("✅ Cart Slider React app initialized");
}


