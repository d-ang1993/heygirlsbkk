/** @jsxImportSource react */
import React, { useState, useEffect, useCallback } from "react";
import CartItem from "../components/CartItem";
import CartDrawerFooter from "../components/CartDrawerFooter";
import "./CartSlider.css";

export default function CartSlider({ isOpen, onClose }) {
  const [cartData, setCartData] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  // Cache for cart data
  const [cartCache, setCartCache] = useState(null);
  const [cacheTimestamp, setCacheTimestamp] = useState(0);
  const CACHE_DURATION = 5000;

  // Load cart content
  const loadCartContent = useCallback(
    async (forceRefresh = false) => {
      const now = Date.now();

      // Use cache if available and not forcing refresh
      if (!forceRefresh && cartCache && now - cacheTimestamp < CACHE_DURATION) {
        console.log("Using cached cart data");
        setCartData(cartCache);
        setIsLoading(false);
        return;
      }

      setIsLoading(true);
      setError(null);

      try {
        const ajaxUrl =
          window.wc_cart_params?.ajax_url || "/wp-admin/admin-ajax.php";
        const fetchUrl = forceRefresh
          ? `${ajaxUrl}?t=${Date.now()}`
          : ajaxUrl;

        const response = await fetch(fetchUrl, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body:
            "action=woocommerce_get_cart_contents&nonce=" +
            (window.wc_cart_params?.nonce || ""),
        });

        const data = await response.json();

        if (data.success) {
          setCartData(data.data);
          setCartCache(data.data);
          setCacheTimestamp(now);
        } else {
          setError("Failed to load cart");
        }
      } catch (err) {
        console.error("Error loading cart:", err);
        setError("Error loading cart. Please try again.");
      } finally {
        setIsLoading(false);
      }
    },
    [cartCache, cacheTimestamp]
  );

  // Load cart when drawer opens
  useEffect(() => {
    if (isOpen) {
      // Check if we should force refresh (e.g., after adding item to cart)
      const shouldForceRefresh = sessionStorage.getItem('forceCartRefresh') === 'true';
      if (shouldForceRefresh) {
        sessionStorage.removeItem('forceCartRefresh');
        // Clear cache when forcing refresh
        setCartCache(null);
        setCacheTimestamp(0);
        // Small delay to ensure server has processed the add-to-cart request
        setTimeout(() => {
          loadCartContent(true);
        }, 200);
      } else {
        loadCartContent(false);
      }
    }
  }, [isOpen, loadCartContent]);

  // Listen for cart updates
  useEffect(() => {
    const handleCartUpdate = () => {
      if (isOpen) {
        // Clear cache when cart is updated
        setCartCache(null);
        setCacheTimestamp(0);
        setTimeout(() => {
          loadCartContent(true);
        }, 100);
      } else {
        // If cart is closed but will open soon, clear cache for next open
        setCartCache(null);
        setCacheTimestamp(0);
      }
    };

    // WooCommerce events
    if (typeof jQuery !== "undefined") {
      jQuery(document.body).on(
        "updated_wc_div wc_fragment_refresh wc_cart_updated added_to_cart",
        handleCartUpdate
      );
    }

    // Custom cart update event
    document.addEventListener("cartUpdated", handleCartUpdate);

    return () => {
      if (typeof jQuery !== "undefined") {
        jQuery(document.body).off(
          "updated_wc_div wc_fragment_refresh wc_cart_updated added_to_cart",
          handleCartUpdate
        );
      }
      document.removeEventListener("cartUpdated", handleCartUpdate);
    };
  }, [isOpen, loadCartContent]);

  // Handle overlay click
  const handleOverlayClick = (e) => {
    if (e.target === e.currentTarget) {
      onClose();
    }
  };

  // Handle escape key
  useEffect(() => {
    const handleEscape = (e) => {
      if (e.key === "Escape" && isOpen) {
        onClose();
      }
    };

    document.addEventListener("keydown", handleEscape);
    return () => document.removeEventListener("keydown", handleEscape);
  }, [isOpen, onClose]);

  // Update quantity
  const handleQuantityUpdate = useCallback(
    async (cartItemKey, quantity) => {
      try {
        const ajaxUrl =
          window.wc_cart_params?.ajax_url || "/wp-admin/admin-ajax.php";

        const response = await fetch(ajaxUrl, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=woocommerce_update_cart_item&cart_item_key=${cartItemKey}&quantity=${quantity}&nonce=${
            window.wc_cart_params?.nonce || ""
          }`,
        });

        const data = await response.json();

        if (data.success) {
          setCartCache(null);
          setCacheTimestamp(0);
          loadCartContent(true);
          updateBagCount();

          if (typeof jQuery !== "undefined") {
            jQuery(document.body).trigger("updated_wc_div");
          }
        }
      } catch (err) {
        console.error("Error updating cart item:", err);
      }
    },
    [loadCartContent]
  );

  // Remove item
  const handleRemoveItem = useCallback(
    async (cartItemKey) => {
      try {
        const ajaxUrl =
          window.wc_cart_params?.ajax_url || "/wp-admin/admin-ajax.php";

        const response = await fetch(ajaxUrl, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `action=woocommerce_remove_cart_item&cart_item_key=${cartItemKey}&nonce=${
            window.wc_cart_params?.nonce || ""
          }`,
        });

        const data = await response.json();

        if (data.success) {
          setCartCache(null);
          setCacheTimestamp(0);
          loadCartContent(true);
          updateBagCount();

          if (typeof jQuery !== "undefined") {
            jQuery(document.body).trigger("updated_wc_div");
          }
        }
      } catch (err) {
        console.error("Error removing cart item:", err);
      }
    },
    [loadCartContent]
  );

  // Update bag count
  const updateBagCount = useCallback(() => {
    const ajaxUrl =
      window.wc_cart_params?.ajax_url || "/wp-admin/admin-ajax.php";
    fetch(ajaxUrl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=woocommerce_get_cart_count&nonce=${
        window.wc_cart_params?.nonce || ""
      }`,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const bagCount = document.querySelector(".bag-count");
          if (bagCount) bagCount.textContent = data.data;
        }
      });
  }, []);

  const hasItems = cartData?.items && cartData.items.length > 0;

  if (!isOpen) return null;

  return (
    <div className={`cart-drawer ${isOpen ? "active" : ""}`}>
      <div
        className="cart-drawer-overlay"
        onClick={handleOverlayClick}
      ></div>
      <div className="cart-drawer-content">
        {/* Header */}
        <div className="cart-drawer-header">
          <h3>Shopping Cart</h3>
          <button
            className="cart-drawer-close"
            type="button"
            onClick={onClose}
            aria-label="Close cart"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        {/* Body */}
        <div className="cart-drawer-body">
          {isLoading && (
            <div className="cart-loading">
              <div className="spinner"></div>
              <p>Loading cart...</p>
            </div>
          )}

          {error && (
            <div className="cart-error">
              <p>{error}</p>
            </div>
          )}

          {!isLoading && !error && (
            <div className="cart-content">
              {hasItems ? (
                <>
                  <div className="cart-items">
                    {cartData.items.map((item) => (
                      <CartItem
                        key={item.cart_item_key}
                        item={item}
                        onQuantityUpdate={handleQuantityUpdate}
                        onRemove={handleRemoveItem}
                      />
                    ))}
                  </div>
                </>
              ) : (
                <div className="cart-empty">
                  <div className="empty-cart-icon">
                    <svg
                      width="64"
                      height="64"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="1.5"
                    >
                      <circle cx="9" cy="21" r="1"></circle>
                      <circle cx="20" cy="21" r="1"></circle>
                      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                  </div>
                  <h4>Your cart is empty</h4>
                  <p>Add some items to get started</p>
                  <button
                    className="btn btn-primary continue-shopping"
                    onClick={onClose}
                  >
                    Continue Shopping
                  </button>
                </div>
              )}
            </div>
          )}
        </div>

        {/* Footer */}
        {!isLoading && !error && hasItems && (
          <CartDrawerFooter
            items={cartData.items}
            subtotal={cartData?.subtotal}
            total={cartData?.total}
            onClose={onClose}
          />
        )}
      </div>
    </div>
  );
}


