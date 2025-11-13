/** @jsxImportSource react */
import React, { useState, useEffect, useCallback, useRef } from "react";

export default function ArchiveFilters({ initialFilters = {}, filterOptions = {}, ajaxUrl = "", nonce = "", currentCategory = null }) {
  console.log("🔵 ArchiveFilters Component: Rendered with props", { initialFilters, filterOptions, ajaxUrl: !!ajaxUrl, nonce: !!nonce, currentCategory });
  
  // Store initial filters in a ref to avoid dependency issues
  const initialFiltersRef = useRef(initialFilters);
  
  // Store currentCategory in a ref to ensure it's always available, even if prop changes
  const currentCategoryRef = useRef(currentCategory);
  if (currentCategory) {
    currentCategoryRef.current = currentCategory;
  }
  
  // Use ref value for currentCategory to ensure it persists across renders
  const effectiveCurrentCategory = currentCategoryRef.current || currentCategory;
  
  const [filters, setFilters] = useState({
    colors: initialFilters.colors || [],
    categories: initialFilters.categories || [],
    sizes: initialFilters.sizes || [],
    orderby: initialFilters.orderby || "menu_order",
  });

  // Auto-expand sections that have selected filters, or default to color expanded
  const [expandedSections, setExpandedSections] = useState({
    color: (initialFilters.colors && initialFilters.colors.length > 0) ? true : true,
    category: (initialFilters.categories && initialFilters.categories.length > 0) ? true : false,
    size: (initialFilters.sizes && initialFilters.sizes.length > 0) ? true : false,
  });

  const [loading, setLoading] = useState(false);
  const skipDebounceRef = useRef(false);

  const applyFilters = useCallback(async (filterState) => {
    setLoading(true);
    
    try {
      // Use FormData for proper PHP array handling
      const formData = new FormData();
      
      // IMPORTANT: Always include current category FIRST if on a category page
      // This ensures the category filter is always applied, even when other filters are cleared
      // Use the effective current category (from ref or prop)
      const categoryToUse = effectiveCurrentCategory || currentCategoryRef.current;
      if (categoryToUse) {
        formData.append("current_category", categoryToUse);
        console.log('🔵 ArchiveFilters: Including current category in request:', categoryToUse);
      } else {
        console.warn('⚠️ ArchiveFilters: No currentCategory available!', { 
          currentCategory, 
          effectiveCurrentCategory, 
          refValue: currentCategoryRef.current 
        });
      }
      
      // Add filter colors
      if (filterState.colors.length > 0) {
        filterState.colors.forEach((color) => {
          formData.append("filter_color[]", color);
        });
      }
      
      // Add filter categories
      if (filterState.categories.length > 0) {
        filterState.categories.forEach((category) => {
          formData.append("filter_category[]", category);
        });
      }
      
      // Add filter sizes
      if (filterState.sizes.length > 0) {
        filterState.sizes.forEach((size) => {
          formData.append("filter_size[]", size);
        });
      }
      
      // Add orderby
      if (filterState.orderby) {
        formData.append("orderby", filterState.orderby);
      }

      // Add action and nonce
      formData.append("action", "get_filtered_products");
      formData.append("nonce", nonce);
      
      // Log what we're sending
      console.log('🔵 ArchiveFilters: Sending filter request', {
        currentCategory: categoryToUse || currentCategory,
        effectiveCurrentCategory,
        colors: filterState.colors,
        categories: filterState.categories,
        sizes: filterState.sizes,
        orderby: filterState.orderby,
      });

      const response = await fetch(ajaxUrl, {
        method: "POST",
        body: formData, // FormData automatically sets Content-Type with boundary
      });

      const data = await response.json();
      
      if (data.success) {
        const productGridContainer = document.getElementById("archive-product-grid-container");
        if (productGridContainer) {
          productGridContainer.innerHTML = data.data.products_html;
          
          if (window.productGridInit) {
            window.productGridInit();
          }
          
          // Update URL without page reload
          const urlParams = new URLSearchParams();
          
          if (filterState.colors.length > 0) {
            filterState.colors.forEach((color) => {
              urlParams.append("filter_color[]", color);
            });
          }
          
          if (filterState.categories.length > 0) {
            filterState.categories.forEach((category) => {
              urlParams.append("filter_category[]", category);
            });
          }
          
          if (filterState.sizes.length > 0) {
            filterState.sizes.forEach((size) => {
              urlParams.append("filter_size[]", size);
            });
          }
          
          if (filterState.orderby && filterState.orderby !== 'menu_order') {
            urlParams.append("orderby", filterState.orderby);
          }
          
          const newUrl = new URL(window.location);
          newUrl.search = urlParams.toString();
          window.history.pushState({}, "", newUrl);
        }
      }
    } catch (error) {
      console.error("Error fetching products:", error);
    } finally {
      setLoading(false);
    }
  }, [ajaxUrl, nonce, effectiveCurrentCategory]); // Use effectiveCurrentCategory to ensure category is always included

  const isInitialMount = useRef(true);
  
  // Apply filters on initial mount if there are URL parameters
  useEffect(() => {
    if (isInitialMount.current) {
      isInitialMount.current = false;
      
      const initFilters = initialFiltersRef.current;
      
      // Check if there are active filters or non-default orderby
      const hasActiveFilters = 
        (initFilters.colors && initFilters.colors.length > 0) ||
        (initFilters.categories && initFilters.categories.length > 0) ||
        (initFilters.sizes && initFilters.sizes.length > 0) ||
        (initFilters.orderby && initFilters.orderby !== 'menu_order');
      
      // Apply filters on initial mount if URL parameters exist
      // This ensures the React component applies the same filters that were in the URL
      if (hasActiveFilters) {
        console.log('🔵 ArchiveFilters: Applying initial filters from URL', initFilters);
        // Use a small timeout to ensure DOM is ready
        setTimeout(() => {
          applyFilters({
            colors: initFilters.colors || [],
            categories: initFilters.categories || [],
            sizes: initFilters.sizes || [],
            orderby: initFilters.orderby || 'menu_order',
          });
        }, 100);
      }
      return;
    }

    // Skip debounce if orderby was updated programmatically
    if (skipDebounceRef.current) {
      skipDebounceRef.current = false;
      return;
    }

    const timeoutId = setTimeout(() => {
      applyFilters(filters);
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [filters, applyFilters]);

  const toggle = (key, value) => {
    setFilters((f) => {
      const current = f[key] || [];
      const next = current.includes(value)
        ? current.filter((v) => v !== value)
        : [...current, value];
      return { ...f, [key]: next };
    });
  };

  const toggleSection = (section) => {
    setExpandedSections((prev) => ({
      ...prev,
      [section]: !prev[section],
    }));
  };

  // Function to update orderby - exposed globally for sort dropdown
  const updateOrderby = useCallback((newOrderby) => {
    skipDebounceRef.current = true; // Skip the debounced useEffect
    setFilters((prev) => {
      const updatedFilters = {
        ...prev,
        orderby: newOrderby,
      };
      // Immediately apply filters with new orderby (don't wait for debounce)
      setTimeout(() => {
        applyFilters(updatedFilters);
      }, 0);
      return updatedFilters;
    });
  }, [applyFilters]);

  // Expose updateOrderby function globally so sort dropdown can use it
  useEffect(() => {
    window.updateArchiveSort = updateOrderby;
    return () => {
      delete window.updateArchiveSort;
    };
  }, [updateOrderby]);

  // Debug: Log what filter options are available
  console.log("🔵 ArchiveFilters: filterOptions", filterOptions);
  console.log("🔵 ArchiveFilters: colors count", filterOptions.colors?.length || 0);
  console.log("🔵 ArchiveFilters: categories count", filterOptions.categories?.length || 0);
  console.log("🔵 ArchiveFilters: sizes count", filterOptions.sizes?.length || 0);

  const hasFilters = 
    (filterOptions.colors && filterOptions.colors.length > 0) ||
    (filterOptions.categories && filterOptions.categories.length > 0) ||
    (filterOptions.sizes && filterOptions.sizes.length > 0);

  return (
    <aside className="pr-4">
      <h3 className="mb-3 text-lg font-semibold text-gray-900">Filters</h3>

      {!hasFilters ? (
        <div className="mb-4">
          <p className="text-sm text-gray-500">No filters available at this time.</p>
        </div>
      ) : (
        <>
          {/* Color Filter */}
          {filterOptions.colors && filterOptions.colors.length > 0 && (
            <div className="border-b border-gray-200 py-6">
              <h3 className="-my-3 flow-root">
                <button
                  type="button"
                  onClick={() => toggleSection("color")}
                  className="flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500"
                >
                  <span className="font-medium text-gray-900">Color</span>
                  <span className="ml-6 flex items-center">
                    {expandedSections.color ? (
                      <svg viewBox="0 0 20 20" fill="currentColor" className="size-5" aria-hidden="true">
                        <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clipRule="evenodd" fillRule="evenodd" />
                      </svg>
                    ) : (
                      <svg viewBox="0 0 20 20" fill="currentColor" className="size-5" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                      </svg>
                    )}
                  </span>
                </button>
              </h3>
              {expandedSections.color && (
                <div className="space-y-4 pt-6">
                  {filterOptions.colors.map((c, index) => (
                    <div key={c.slug} className="flex gap-3">
                      <div className="flex h-5 shrink-0 items-center">
                        <div className="group grid size-4 grid-cols-1">
                          <input
                            id={`filter-color-${index}`}
                            type="checkbox"
                            checked={filters.colors?.includes(c.slug) || false}
                            onChange={() => toggle("colors", c.slug)}
                            className="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto"
                          />
                          <svg viewBox="0 0 14 14" fill="none" className="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                            <path d="M3 8L6 11L11 3.5" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={`${filters.colors?.includes(c.slug) ? 'opacity-100' : 'opacity-0'}`} />
                            <path d="M3 7H11" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="opacity-0" />
                          </svg>
                        </div>
                      </div>
                      <label htmlFor={`filter-color-${index}`} className="text-sm text-gray-600 cursor-pointer">
                        {c.name}
                      </label>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}

              {/* Category Filter - Hide on category pages */}
              {!effectiveCurrentCategory && filterOptions.categories && filterOptions.categories.length > 0 && (
            <div className="border-b border-gray-200 py-6">
              <h3 className="-my-3 flow-root">
                <button
                  type="button"
                  onClick={() => toggleSection("category")}
                  className="flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500"
                >
                  <span className="font-medium text-gray-900">Category</span>
                  <span className="ml-6 flex items-center">
                    {expandedSections.category ? (
                      <svg viewBox="0 0 20 20" fill="currentColor" className="size-5" aria-hidden="true">
                        <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clipRule="evenodd" fillRule="evenodd" />
                      </svg>
                    ) : (
                      <svg viewBox="0 0 20 20" fill="currentColor" className="size-5" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                      </svg>
                    )}
                  </span>
                </button>
              </h3>
              {expandedSections.category && (
                <div className="space-y-4 pt-6">
                  {filterOptions.categories.map((category, index) => (
                    <div key={category.slug} className="flex gap-3">
                      <div className="flex h-5 shrink-0 items-center">
                        <div className="group grid size-4 grid-cols-1">
                          <input
                            id={`filter-category-${index}`}
                            type="checkbox"
                            checked={filters.categories?.includes(category.slug) || false}
                            onChange={() => toggle("categories", category.slug)}
                            className="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto"
                          />
                          <svg viewBox="0 0 14 14" fill="none" className="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                            <path d="M3 8L6 11L11 3.5" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={`${filters.categories?.includes(category.slug) ? 'opacity-100' : 'opacity-0'}`} />
                            <path d="M3 7H11" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="opacity-0" />
                          </svg>
                        </div>
                      </div>
                      <label htmlFor={`filter-category-${index}`} className="text-sm text-gray-600 cursor-pointer">
                        {category.name}
                      </label>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}

          {/* Size Filter */}
          {filterOptions.sizes && filterOptions.sizes.length > 0 && (
            <div className="border-b border-gray-200 py-6">
              <h3 className="-my-3 flow-root">
                <button
                  type="button"
                  onClick={() => toggleSection("size")}
                  className="flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500"
                >
                  <span className="font-medium text-gray-900">Size</span>
                  <span className="ml-6 flex items-center">
                    {expandedSections.size ? (
                      <svg viewBox="0 0 20 20" fill="currentColor" className="size-5" aria-hidden="true">
                        <path d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clipRule="evenodd" fillRule="evenodd" />
                      </svg>
                    ) : (
                      <svg viewBox="0 0 20 20" fill="currentColor" className="size-5" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                      </svg>
                    )}
                  </span>
                </button>
              </h3>
              {expandedSections.size && (
                <div className="space-y-4 pt-6">
                  {filterOptions.sizes.map((s, index) => (
                    <div key={s.slug} className="flex gap-3">
                      <div className="flex h-5 shrink-0 items-center">
                        <div className="group grid size-4 grid-cols-1">
                          <input
                            id={`filter-size-${index}`}
                            type="checkbox"
                            checked={filters.sizes?.includes(s.slug) || false}
                            onChange={() => toggle("sizes", s.slug)}
                            className="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto"
                          />
                          <svg viewBox="0 0 14 14" fill="none" className="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25">
                            <path d="M3 8L6 11L11 3.5" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={`${filters.sizes?.includes(s.slug) ? 'opacity-100' : 'opacity-0'}`} />
                            <path d="M3 7H11" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="opacity-0" />
                          </svg>
                        </div>
                      </div>
                      <label htmlFor={`filter-size-${index}`} className="text-sm text-gray-600 cursor-pointer">
                        {s.name}
                      </label>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}
        </>
      )}

      {loading && (
        <div className="mt-4 text-sm text-gray-500 text-center">
          Loading products...
        </div>
      )}
    </aside>
  );
}

