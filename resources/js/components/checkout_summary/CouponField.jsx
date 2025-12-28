/** @jsxImportSource react */
import React, { useState, useEffect } from "react";
import { extractPriceNumber } from "../../utils/priceUtils";

export default function CouponField({ 
  checkoutData, 
  ajaxUrl, 
  nonce,
  wooCheckoutNonce,
  onCouponApplied,
  onCouponRemoved 
}) {
  const [couponCode, setCouponCode] = useState("");
  const [isApplying, setIsApplying] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const [appliedCoupons, setAppliedCoupons] = useState([]);
  const [isExpanded, setIsExpanded] = useState(false);

  // Get applied coupons from checkout data
  useEffect(() => {
    if (checkoutData?.applied_coupons) {
      setAppliedCoupons(checkoutData.applied_coupons);
      setIsExpanded(checkoutData.applied_coupons.length > 0);
    }
  }, [checkoutData?.applied_coupons]);

  // Fetch and log all valid discount codes
  const fetchValidCoupons = async () => {
    try {
      const formData = new FormData();
      formData.append("action", "get_valid_coupons");
      formData.append("security", wooCheckoutNonce || nonce || "");

      const response = await fetch(ajaxUrl || "/wp-admin/admin-ajax.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();
      
      if (data.success && data.data?.coupons) {
        console.log("🎟️ Valid Discount Codes:", data.data.coupons);
        return data.data.coupons;
      } else {
        console.log("🎟️ No valid coupons found or error:", data);
        return [];
      }
    } catch (err) {
      console.error("Error fetching valid coupons:", err);
      return [];
    }
  };

  const handleApplyCoupon = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    const codeToApply = couponCode.trim();
    console.log("🎟️ Attempting to apply coupon:", codeToApply);
    
    if (!codeToApply) {
      setError("Please enter a coupon code");
      return;
    }

    // Check if this coupon is already applied (case-insensitive)
    if (appliedCoupons.some(code => code.toLowerCase() === codeToApply.toLowerCase())) {
      setError("This coupon is already applied");
      return;
    }

    setIsApplying(true);
    setError(null);

    // Fetch and log valid coupons before applying
    const validCoupons = await fetchValidCoupons();
    console.log("🎟️ Available valid coupons:", validCoupons);

    try {
      // Remove any existing coupons first (only one coupon allowed at a time)
      if (appliedCoupons.length > 0) {
        console.log("🎟️ Removing existing coupon before applying new one:", appliedCoupons);
        for (const existingCoupon of appliedCoupons) {
          try {
            const removeFormData = new FormData();
            removeFormData.append("action", "remove_checkout_coupon");
            removeFormData.append("coupon_code", existingCoupon);

            const removeResponse = await fetch(ajaxUrl || "/wp-admin/admin-ajax.php", {
              method: "POST",
              body: removeFormData,
            });

            if (removeResponse.ok) {
              const removeData = await removeResponse.json();
              if (removeData.success) {
                console.log("✅ Removed existing coupon:", existingCoupon);
                // Update local state
                setAppliedCoupons((prev) => prev.filter((code) => code !== existingCoupon));
                // Notify parent component
                if (onCouponRemoved && removeData.data) {
                  onCouponRemoved(removeData.data);
                }
              }
            }
          } catch (removeErr) {
            console.warn("⚠️ Error removing existing coupon:", existingCoupon, removeErr);
            // Continue even if removal fails - backend will handle it
          }
        }
      }

      // Use our custom AJAX handler that doesn't require WooCommerce checkout nonce
      const formData = new FormData();
      formData.append("action", "apply_checkout_coupon");
      formData.append("coupon_code", codeToApply);

      console.log("🎟️ Sending coupon apply request:", {
        code: codeToApply,
        action: "apply_checkout_coupon",
        ajaxUrl: ajaxUrl || "/wp-admin/admin-ajax.php"
      });

      const response = await fetch(ajaxUrl || "/wp-admin/admin-ajax.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      
      console.log("🎟️ Coupon apply response:", {
        success: data.success,
        data: data.data,
        message: data.data?.message,
        fullResponse: data
      });

      if (data.success && data.data) {
        const appliedCode = codeToApply;
        setCouponCode("");
        setError(null);
        setSuccess(`Coupon "${appliedCode}" applied successfully!`);
        
        // Clear success message after 3 seconds
        setTimeout(() => setSuccess(null), 3000);
        
        // Update applied coupons from response
        if (data.data.applied_coupons) {
          setAppliedCoupons(data.data.applied_coupons);
        }
        
        // Trigger checkout update with new cart totals
        if (onCouponApplied) {
          onCouponApplied(data.data);
        }
        
        // Trigger WooCommerce cart update event
        if (typeof jQuery !== "undefined") {
          jQuery(document.body).trigger("applied_coupon", [appliedCode]);
          jQuery(document.body).trigger("update_checkout");
          jQuery(document.body).trigger("updated_wc_div");
        }
      } else {
        const errorMessage = data.data?.message || "Invalid coupon code";
        console.error("❌ Coupon application failed:", {
          code: codeToApply,
          error: errorMessage,
          fullResponse: data,
          validCoupons: validCoupons
        });
        setError(errorMessage);
        setSuccess(null);
      }
    } catch (err) {
      console.error("❌ Error applying coupon:", {
        code: codeToApply,
        error: err,
        message: err.message,
        stack: err.stack
      });
      setError("Failed to apply coupon. Please try again.");
    } finally {
      setIsApplying(false);
    }
  };

  const handleRemoveCoupon = async (couponCodeToRemove) => {
    try {
      // Use our custom AJAX handler
      const formData = new FormData();
      formData.append("action", "remove_checkout_coupon");
      formData.append("coupon_code", couponCodeToRemove);

      const response = await fetch(ajaxUrl || "/wp-admin/admin-ajax.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();

      if (data.success && data.data) {
        // Update applied coupons from response
        if (data.data.applied_coupons) {
          setAppliedCoupons(data.data.applied_coupons);
        } else {
          setAppliedCoupons((prev) =>
            prev.filter((code) => code !== couponCodeToRemove)
          );
        }
        
        setError(null);
        setSuccess(`Coupon "${couponCodeToRemove}" removed`);
        
        // Clear success message after 3 seconds
        setTimeout(() => setSuccess(null), 3000);
        
        if (onCouponRemoved) {
          onCouponRemoved(data.data);
        }
        
        // Trigger WooCommerce cart update event
        if (typeof jQuery !== "undefined") {
          jQuery(document.body).trigger("removed_coupon", [couponCodeToRemove]);
          jQuery(document.body).trigger("update_checkout");
          jQuery(document.body).trigger("updated_wc_div");
        }
      } else {
        setError(data.data?.message || "Failed to remove coupon");
        setSuccess(null);
      }
    } catch (err) {
      console.error("Error removing coupon:", err);
      setError("Failed to remove coupon. Please try again.");
    }
  };

  return (
    <div className="border-b border-gray-200 px-4 py-4 sm:px-6">
      {/* Toggle button */}
      <button
        type="button"
        onClick={() => setIsExpanded(!isExpanded)}
        className="flex w-full items-center justify-between text-left py-2 -mx-2 px-2 rounded-lg hover:bg-gray-50 transition-colors"
      >
        <span className="text-xs sm:text-sm font-medium text-gray-700">
          {appliedCoupons.length > 0
            ? `Discount${appliedCoupons.length > 1 ? "s" : ""} (${appliedCoupons.length})`
            : "Have a coupon?"}
        </span>
        <svg
          className={`h-4 w-4 sm:h-5 sm:w-5 text-gray-500 transition-transform flex-shrink-0 ${
            isExpanded ? "rotate-180" : ""
          }`}
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth={2}
            d="M19 9l-7 7-7-7"
          />
        </svg>
      </button>

      {/* Applied coupons display */}
      {appliedCoupons.length > 0 && (() => {
        // Calculate discount info
        const discountTotal = checkoutData?.discount_total ?? 0;
        const discountAmount = Math.abs(discountTotal);
        const subtotalNum = extractPriceNumber(checkoutData?.cart_subtotal || "0");
        const discountPercent = subtotalNum > 0 
          ? ((discountAmount / subtotalNum) * 100).toFixed(0)
          : null;
        
        return (
          <div className="mt-3 space-y-2">
            {appliedCoupons.map((code) => (
              <div
                key={code}
                className="flex items-center justify-between rounded-md bg-green-50 px-3 py-2 text-sm"
              >
                <div className="flex flex-col">
                  <span className="font-medium text-green-800">{code}</span>
                  {discountAmount > 0 && (
                    <span className="text-xs text-green-600 mt-0.5">
                      {discountPercent 
                        ? `Save ${discountPercent}% (฿${discountAmount.toFixed(2)})`
                        : `Save ฿${discountAmount.toFixed(2)}`
                      }
                    </span>
                  )}
                </div>
                <button
                  type="button"
                  onClick={() => handleRemoveCoupon(code)}
                  className="text-green-600 hover:text-green-800 transition-colors"
                  aria-label={`Remove coupon ${code}`}
                >
                  <svg
                    className="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth={2}
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>
            ))}
          </div>
        );
      })()}

      {/* Coupon form - using div instead of form to avoid nested forms */}
      {isExpanded && (
        <div className="mt-3">
          <div className="flex flex-col sm:flex-row gap-2">
            <input
              type="text"
              value={couponCode}
              onChange={(e) => {
                setCouponCode(e.target.value);
                setError(null);
                setSuccess(null);
              }}
              onKeyDown={(e) => {
                if (e.key === "Enter") {
                  e.preventDefault();
                  handleApplyCoupon(e);
                }
              }}
              placeholder="Enter coupon code"
              className="flex-1 rounded-md border border-gray-300 px-3 py-2 text-base sm:text-sm focus:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900/10"
              disabled={isApplying}
            />
            <button
              type="button"
              onClick={handleApplyCoupon}
              disabled={isApplying || !couponCode.trim()}
              className="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors touch-manipulation whitespace-nowrap"
            >
              {isApplying ? "Applying..." : "Apply"}
            </button>
          </div>
          
          {error && (
            <p className="mt-2 text-xs sm:text-sm text-red-600" role="alert">
              {error}
            </p>
          )}
          
          {success && (
            <p className="mt-2 text-xs sm:text-sm text-green-600" role="alert">
              {success}
            </p>
          )}
        </div>
      )}
    </div>
  );
}

