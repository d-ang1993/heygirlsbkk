/** @jsxImportSource react */
import React from "react";
import { createRoot } from "react-dom/client";
import ArchiveFilters from "./components/ArchiveFilters";

function boot() {
  console.log("🔵 ArchiveFilters: Boot function called");
  
  const el = document.getElementById("archive-filters-react");
  if (!el) {
    console.warn("⚠️ ArchiveFilters: Container element not found");
    return;
  }

  const props = window.archiveFiltersData || {};
  console.log("🔵 ArchiveFilters: Props:", props);

  if (!props.filterOptions) {
    console.warn("⚠️ ArchiveFilters: No filterOptions in props");
  }

  try {
    if (!el._reactRoot) {
      console.log("🔵 ArchiveFilters: Creating new React root");
      el._reactRoot = createRoot(el);
    }

    console.log("🔵 ArchiveFilters: Rendering component");
    el._reactRoot.render(<ArchiveFilters {...props} />);
    console.log("✅ ArchiveFilters: Component rendered successfully");
  } catch (error) {
    console.error("❌ ArchiveFilters: Error rendering component:", error);
  }
}

// Try to mount - wait for both DOM and data
function tryMount(attempts = 0) {
  const maxAttempts = 50; // 50 attempts * 50ms = 2.5 seconds max wait
  const el = document.getElementById("archive-filters-react");
  const data = window.archiveFiltersData;
  
  console.log(`🔵 ArchiveFilters: Mount attempt ${attempts + 1}`, {
    elementFound: !!el,
    dataFound: !!data,
    dataKeys: data ? Object.keys(data) : []
  });
  
  if (el && data) {
    boot();
  } else if (attempts < maxAttempts) {
    // Retry after a short delay
    setTimeout(() => tryMount(attempts + 1), 50);
  } else {
    console.error("❌ ArchiveFilters: Failed to mount after max attempts", {
      elementFound: !!el,
      dataFound: !!data
    });
  }
}

// Start mounting process
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => tryMount(0), 100);
  });
} else {
  // DOM is already ready
  setTimeout(() => tryMount(0), 100);
}

window.mountArchiveFilters = boot;

