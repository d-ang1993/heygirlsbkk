/** @jsxImportSource react */
import React from "react";
import { createRoot } from "react-dom/client";
import HeroReact from "./components/HeroReact";

// Immediate console log to verify script is loading
console.log("🔵 HeroReact: Script file loaded and executing");

// Export mount function immediately - before any other code
window.mountHeroReact = function boot() {
  console.log("🔵 HeroReact: Boot function called");

  const el = document.getElementById("hero-react-container");
  if (!el) {
    console.error("❌ HeroReact: Container element '#hero-react-container' not found in DOM");
    console.log("Available IDs:", Array.from(document.querySelectorAll("[id]")).map((e) => e.id));
    return;
  }

  const props = window.heroReactData || {};
  console.log("🔵 HeroReact: Props:", props);
  console.log("🔵 HeroReact: Props keys:", Object.keys(props));

  try {
    // Clear any existing content
    if (el._reactRoot) {
      console.log("🔵 HeroReact: Cleaning up existing React root");
      el._reactRoot.unmount();
      delete el._reactRoot;
    }

    console.log("🔵 HeroReact: Creating new React root");
    el._reactRoot = createRoot(el);

    console.log("🔵 HeroReact: Rendering component with props:", props);

    // Prepare props with defaults
    const componentProps = {
      heading: props.heading || "NEW ARRIVAL",
      subheading: props.subheading || "",
      ctaText: props.ctaText || "Shop Now",
      ctaUrl: props.ctaUrl || "/shop",
      images: props.images || [],
      bgImage: props.bgImage || null,
      bgColor: props.bgColor || "#f8f9fa",
      alignment: props.alignment || "center",
      height: props.height || "60vh",
      overlay: props.overlay || 0.35,
      carouselEnabled: props.carouselEnabled || false,
      autoplay: props.autoplay !== false, // default true
      autoplaySpeed: props.autoplaySpeed || 5000,
    };

    el._reactRoot.render(<HeroReact {...componentProps} />);
    console.log("✅ HeroReact: Component rendered successfully");
  } catch (error) {
    console.error("❌ HeroReact: Error rendering component:", error);
    console.error("❌ Error stack:", error.stack);
    el.innerHTML = `<div class="p-8 bg-red-50 border border-red-200 rounded-lg"><p class="text-red-800">Error loading hero component: ${error.message}</p><p class="text-sm text-red-600 mt-2">Please check the console for more details.</p></div>`;
  }
};

// Try to mount - wait for both DOM and data
function tryMount(attempts = 0) {
  const maxAttempts = 50;
  const el = document.getElementById("hero-react-container");
  const data = window.heroReactData;

  console.log(`🔵 HeroReact: Mount attempt ${attempts + 1}`, {
    elementFound: !!el,
    dataFound: !!data,
    dataKeys: data ? Object.keys(data) : [],
    mountFunctionAvailable: typeof window.mountHeroReact === "function",
  });

  if (el && data && typeof window.mountHeroReact === "function") {
    console.log("🔵 HeroReact: Calling mountHeroReact");
    window.mountHeroReact();
  } else if (attempts < maxAttempts) {
    setTimeout(() => tryMount(attempts + 1), 50);
  } else {
    console.error("❌ HeroReact: Failed to mount after max attempts", {
      elementFound: !!el,
      dataFound: !!data,
      mountFunctionAvailable: typeof window.mountHeroReact === "function",
    });
  }
}

// Auto-mount when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    console.log("🔵 HeroReact: DOMContentLoaded fired");
    setTimeout(tryMount, 100);
  });
} else {
  console.log("🔵 HeroReact: DOM already loaded, starting mount process");
  setTimeout(tryMount, 100);
}

// Also try mounting after a short delay as fallback
setTimeout(() => {
  const el = document.getElementById("hero-react-container");
  const data = window.heroReactData;
  if (el && data && !el._reactRoot) {
    console.log("🔵 HeroReact: Fallback mount attempt");
    tryMount();
  }
}, 500);

