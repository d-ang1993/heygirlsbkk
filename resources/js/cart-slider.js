/**
 * Cart Slider Entry Point
 * This file initializes the React-based cart slider
 */

import { initCartSliderApp } from "./components/cart-slider/CartSliderApp.jsx";

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initCartSliderApp);
} else {
  initCartSliderApp();
}

// Export for manual initialization if needed
export { initCartSliderApp };


