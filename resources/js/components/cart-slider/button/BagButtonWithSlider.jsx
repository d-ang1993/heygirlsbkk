/** @jsxImportSource react */
import React, { useEffect, useState } from "react";
import { motion } from "framer-motion";
import { CartSlider } from "../index";
import { useCartSlider } from "../hooks/useCartSlider";
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';

/**
 * Bag Button with Cart Slider Component
 * Combines the bag button and cart slider in one component
 */
export default function BagButtonWithSlider({ cartUrl, bagCount: initialBagCount = 0 }) {
  const { isOpen, openCartSlider, closeCartSlider } = useCartSlider();
  const [bagCount, setBagCount] = useState(initialBagCount);

  const handleBagClick = (e) => {
    e.preventDefault();
    e.stopPropagation();

    // Check if mobile (redirect to cart page on mobile)
    if (window.innerWidth <= 768) {
      if (cartUrl) {
        window.location.href = cartUrl;
      }
      return;
    }

    // Open cart slider on desktop
    console.log("🛒 Opening cart slider from bag button");
    openCartSlider();
  };

  // Expose global functions for backward compatibility
  useEffect(() => {
    window.openCartDrawer = openCartSlider;
    window.closeCartDrawer = closeCartSlider;
  }, [openCartSlider, closeCartSlider]);

  // Listen for WooCommerce cart updates to sync bag count
  useEffect(() => {
    const updateBagCount = async () => {
      try {
        // Fetch current cart count from WooCommerce
        const ajaxUrl =
          window.wc_cart_params?.ajax_url || "/wp-admin/admin-ajax.php";
        
        const response = await fetch(ajaxUrl, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=woocommerce_get_cart_count&nonce=${
            window.wc_cart_params?.nonce || ""
          }`,
        });

        const data = await response.json();
        
        if (data.success && data.data !== undefined) {
          setBagCount(data.data);
          
          // Also update any static bag-count elements in the DOM (for compatibility)
          const bagCountElements = document.querySelectorAll(".bag-count");
          bagCountElements.forEach((el) => {
            el.textContent = data.data;
          });
        } else {
          // Fallback: try to get from DOM if WooCommerce fragments updated it
          const bagCountElement = document.querySelector(".bag-count");
          if (bagCountElement) {
            const count = parseInt(bagCountElement.textContent || "0", 10);
            if (!isNaN(count)) {
              setBagCount(count);
            }
          }
        }
      } catch (err) {
        console.error("Error updating bag count:", err);
        // Fallback: try to get from DOM
        const bagCountElement = document.querySelector(".bag-count");
        if (bagCountElement) {
          const count = parseInt(bagCountElement.textContent || "0", 10);
          if (!isNaN(count)) {
            setBagCount(count);
          }
        }
      }
    };

    const handleCartUpdate = () => {
      // Small delay to let WooCommerce update
      setTimeout(updateBagCount, 150);
    };

    // Listen to WooCommerce events
    if (typeof jQuery !== "undefined") {
      jQuery(document.body).on(
        "updated_wc_div wc_fragment_refresh wc_cart_updated added_to_cart removed_from_cart",
        handleCartUpdate
      );
    }

    document.addEventListener("added_to_cart", handleCartUpdate);
    document.addEventListener("cartUpdated", handleCartUpdate);
    document.addEventListener("removed_from_cart", handleCartUpdate);

    // Initial sync
    updateBagCount();

    // Also set up an observer to watch for DOM changes (WooCommerce fragments)
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === "childList" || mutation.type === "characterData") {
          const target = mutation.target;
          if (
            target.classList?.contains("bag-count") ||
            target.querySelector?.(".bag-count")
          ) {
            updateBagCount();
          }
        }
      });
    });

    // Observe the navbar area for bag count changes
    const navbar = document.querySelector(".navbar");
    if (navbar) {
      observer.observe(navbar, {
        childList: true,
        subtree: true,
        characterData: true,
      });
    }

    return () => {
      if (typeof jQuery !== "undefined") {
        jQuery(document.body).off(
          "updated_wc_div wc_fragment_refresh wc_cart_updated added_to_cart removed_from_cart",
          handleCartUpdate
        );
      }
      document.removeEventListener("added_to_cart", handleCartUpdate);
      document.removeEventListener("cartUpdated", handleCartUpdate);
      document.removeEventListener("removed_from_cart", handleCartUpdate);
      observer.disconnect();
    };
  }, []);

  return (
    <>
      <a
        href="#"
        className="navbar-link navbar-bag cart-trigger"
        data-cart-url={cartUrl}
        onClick={handleBagClick}
      >
        <motion.div 
          whileHover={{ scale: 1.2 }}
          transition={{ duration: 0.3, ease: "easeOut" }}
        >
          <ShoppingCartIcon 
            sx={{ 
              fontSize: '1.25rem',
              color: 'var(--color-primary)'
            }} 
          />
        </motion.div>
        <span className="bag-count">{bagCount}</span>
      </a>
      <CartSlider isOpen={isOpen} onClose={closeCartSlider} />
    </>
  );
}

