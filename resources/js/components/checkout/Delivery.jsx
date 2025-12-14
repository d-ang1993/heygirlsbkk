/** @jsxImportSource react */
import React, { useMemo, useEffect } from "react";
import { extractPriceNumber } from "../../utils/priceUtils";

export default function Delivery({
  formData,
  shippingMethods = [],
  shippingMethodsByZone = {},
  checkoutData,
  onInputChange,
  onShippingChange,
  isExpanded,
  onToggle,
}) {
  // Check if shipping method section is complete
  const isComplete = useMemo(() => {
    return !!(formData.shipping_method && formData.shipping_method.length > 0);
  }, [formData.shipping_method]);

  // Get shipping method label
  const selectedShippingMethod = shippingMethods.find(
    (m) => m.id === formData.shipping_method
  );

  // Group shipping methods by zone
  // Use shippingMethodsByZone if available, otherwise group by zone property
  const groupedByZone = useMemo(() => {
    if (Object.keys(shippingMethodsByZone).length > 0) {
      return shippingMethodsByZone;
    }
    
    // Fallback: group by zone property if available
    const grouped = {};
    shippingMethods.forEach((method) => {
      const zone = method.zone || 'Global';
      if (!grouped[zone]) {
        grouped[zone] = [];
      }
      grouped[zone].push(method);
    });
    return grouped;
  }, [shippingMethodsByZone, shippingMethods]);

  // Dynamically build zone-to-country mapping from available countries
  const zoneToCountryMap = useMemo(() => {
    const mapping = {
      'Global': '*', // Global zone is available for all countries
    };
    
    if (checkoutData && checkoutData.countries) {
      const zones = Object.keys(groupedByZone);
      
      // Match zone names to countries
      checkoutData.countries.forEach((country) => {
        const countryCode = country.key;
        const countryName = country.label;
        
        // Check if any zone name matches this country
        zones.forEach((zoneName) => {
          if (zoneName === 'Global') return; // Skip Global, already handled
          
          const zoneNameLower = zoneName.toLowerCase();
          const countryNameLower = countryName.toLowerCase();
          const countryCodeUpper = countryCode.toUpperCase();
          
          // Exact match: zone name equals country name or code
          if (
            zoneNameLower === countryNameLower ||
            zoneName === countryCode ||
            zoneName === countryCodeUpper
          ) {
            mapping[zoneName] = countryCode;
          }
          // Partial match: zone name contains country name or vice versa
          else if (
            zoneNameLower.includes(countryNameLower) ||
            countryNameLower.includes(zoneNameLower)
          ) {
            mapping[zoneName] = countryCode;
          }
        });
      });
    }
    
    return mapping;
  }, [checkoutData, groupedByZone]);

  // Check if there's a country-specific zone that matches the selected country
  const hasCountrySpecificZone = useMemo(() => {
    const selectedCountry = formData.billing_country;
    if (!selectedCountry) return false;
    
    // Get country name from checkoutData if available
    let selectedCountryName = null;
    if (checkoutData && checkoutData.countries) {
      const countryObj = checkoutData.countries.find(c => c.key === selectedCountry);
      if (countryObj) {
        selectedCountryName = countryObj.label;
      }
    }
    
    // Check all zones except Global
    const zones = Object.keys(groupedByZone).filter(zone => zone !== 'Global');
    
    for (const zoneName of zones) {
      // Check if zone maps to the selected country code via dynamic mapping
      const zoneCountryCode = zoneToCountryMap[zoneName];
      if (zoneCountryCode && zoneCountryCode === selectedCountry) {
        return true;
      }
      
      // Try to match zone name with country name (case-insensitive)
      if (selectedCountryName) {
        const zoneNameLower = zoneName.toLowerCase();
        const countryNameLower = selectedCountryName.toLowerCase();
        if (zoneNameLower === countryNameLower || zoneNameLower.includes(countryNameLower)) {
          return true;
        }
      }
    }
    
    return false;
  }, [formData.billing_country, checkoutData, groupedByZone, zoneToCountryMap]);

  // Check if a zone is applicable for the selected country
  const isZoneApplicable = useMemo(() => {
    const selectedCountry = formData.billing_country;
    if (!selectedCountry) return () => true; // If no country selected, show all zones
    
    // Get country name from checkoutData if available
    let selectedCountryName = null;
    if (checkoutData && checkoutData.countries) {
      const countryObj = checkoutData.countries.find(c => c.key === selectedCountry);
      if (countryObj) {
        selectedCountryName = countryObj.label;
      }
    }
    
    return (zoneName) => {
      // If Global zone and there's a country-specific zone match, gray out Global
      if (zoneName === 'Global') {
        return !hasCountrySpecificZone;
      }
      
      // Check if zone maps to the selected country code via dynamic mapping
      const zoneCountryCode = zoneToCountryMap[zoneName];
      if (zoneCountryCode) {
        // Global zone (marked with '*') - handled above
        if (zoneCountryCode === '*') return true;
        
        // Check if zone country code matches selected country code
        if (zoneCountryCode === selectedCountry) return true;
      }
      
      // Try to match zone name with country name (case-insensitive) as fallback
      if (selectedCountryName) {
        // Check if zone name matches or contains country name
        const zoneNameLower = zoneName.toLowerCase();
        const countryNameLower = selectedCountryName.toLowerCase();
        if (zoneNameLower === countryNameLower || zoneNameLower.includes(countryNameLower)) {
          return true;
        }
      }
      
      // Zone is not applicable
      return false;
    };
  }, [formData.billing_country, checkoutData, hasCountrySpecificZone, zoneToCountryMap]);

  // Get zone names in a consistent order (country-specific zones first, then Global, then others)
  // Filter out "Locations not covered by your other zones"
  const zoneNames = useMemo(() => {
    const excludedZone = 'Locations not covered by your other zones';
    const zones = Object.keys(groupedByZone).filter(zone => zone !== excludedZone);
    
    // Separate zones into categories
    const countrySpecificZones = [];
    const globalZone = [];
    const otherZones = [];
    
    zones.forEach(zone => {
      if (zone === 'Global') {
        globalZone.push(zone);
      } else if (zoneToCountryMap[zone] && zoneToCountryMap[zone] !== '*') {
        // Zone is mapped to a specific country
        countrySpecificZones.push(zone);
      } else {
        otherZones.push(zone);
      }
    });
    
    // Sort country-specific zones alphabetically for consistency
    countrySpecificZones.sort();
    otherZones.sort();
    
    // Return in order: country-specific zones, Global, then others
    return [...countrySpecificZones, ...globalZone, ...otherZones];
  }, [groupedByZone, zoneToCountryMap]);

  // Calculate numeric subtotal from checkoutData
  const subtotal = useMemo(() => {
    if (!checkoutData || !checkoutData.cart_subtotal) return 0;
    return extractPriceNumber(checkoutData.cart_subtotal);
  }, [checkoutData]);

  // Check if a shipping method meets free shipping criteria
  const meetsFreeShippingCriteria = useMemo(() => {
    return (method) => {
      // If method doesn't have a min_amount requirement, it's always available
      if (!method.min_amount) return true;
      
      // Check if subtotal meets or exceeds the minimum amount
      return subtotal >= method.min_amount;
    };
  }, [subtotal]);

  // Helper function to find the best method for an applicable zone
  const findBestMethodForZone = useMemo(() => {
    return (zoneName) => {
      const zoneMethods = groupedByZone[zoneName] || [];
      if (zoneMethods.length === 0) return null;

      // Separate methods by type: free shipping (has min_amount) vs flat rate (no min_amount)
      const freeShippingMethods = zoneMethods.filter(m => m.min_amount);
      const flatRateMethods = zoneMethods.filter(m => !m.min_amount);

      // ALWAYS prefer free shipping if it meets criteria (even if flat rate is already selected)
      // Look for a free shipping method that meets criteria
      if (freeShippingMethods.length > 0) {
        const eligibleFreeShipping = freeShippingMethods.find(m => meetsFreeShippingCriteria(m));
        if (eligibleFreeShipping) {
          return eligibleFreeShipping;
        }
      }
      
      // No eligible free shipping available, use flat rate (first non-free shipping method)
      return flatRateMethods.length > 0 ? flatRateMethods[0] : null;
    };
  }, [subtotal, groupedByZone, meetsFreeShippingCriteria]);

  // Auto-select the appropriate shipping method based on zone and free shipping eligibility
  useEffect(() => {
    // Skip if no shipping methods available
    if (!shippingMethods || shippingMethods.length === 0) return;

    // Find applicable zones
    const applicableZones = zoneNames.filter(zoneName => isZoneApplicable(zoneName));
    
    if (applicableZones.length === 0) {
      // No applicable zones, can't select anything
      return;
    }

    // Determine the best method from the first applicable zone
    const targetZone = applicableZones[0];
    const bestMethod = findBestMethodForZone(targetZone);

    if (!bestMethod) {
      // No method found for applicable zone
      return;
    }

    // Verify the method is not disabled (should never happen, but double-check)
    const meetsCriteria = meetsFreeShippingCriteria(bestMethod);
    const isDisabled = bestMethod.min_amount && !meetsCriteria;
    
    if (isDisabled) {
      // This should not happen, but if it does, try to find a flat rate method instead
      const zoneMethods = groupedByZone[targetZone] || [];
      const flatRateMethod = zoneMethods.find(m => !m.min_amount);
      if (flatRateMethod) {
        const syntheticEvent = {
          target: {
            name: 'shipping_method[0]',
            value: flatRateMethod.id,
          },
        };
        onInputChange(syntheticEvent);
        if (onShippingChange) {
          onShippingChange(flatRateMethod.id, flatRateMethod.cost);
        }
      }
      return;
    }

    const currentMethodId = formData.shipping_method;
    
    // If no method is currently selected, select the best one
    if (!currentMethodId) {
      const syntheticEvent = {
        target: {
          name: 'shipping_method[0]',
          value: bestMethod.id,
        },
      };
      onInputChange(syntheticEvent);
      if (onShippingChange) {
        onShippingChange(bestMethod.id, bestMethod.cost);
      }
      return;
    }

    // Check if current method is valid
    const currentMethod = shippingMethods.find(m => m.id === currentMethodId);
    if (!currentMethod) {
      // Current method not found, select best one
      const syntheticEvent = {
        target: {
          name: 'shipping_method[0]',
          value: bestMethod.id,
        },
      };
      onInputChange(syntheticEvent);
      if (onShippingChange) {
        onShippingChange(bestMethod.id, bestMethod.cost);
      }
      return;
    }

    // Find which zone the current method belongs to
    let currentMethodZone = null;
    for (const [zoneName, methods] of Object.entries(groupedByZone)) {
      if (methods.some(m => m.id === currentMethodId)) {
        currentMethodZone = zoneName;
        break;
      }
    }

    // Check if current method is valid (not disabled and in applicable zone)
    const isCurrentZoneApplicable = currentMethodZone ? applicableZones.includes(currentMethodZone) : false;
    const currentMeetsCriteria = meetsFreeShippingCriteria(currentMethod);
    const currentIsDisabled = !isCurrentZoneApplicable || (currentMethod.min_amount && !currentMeetsCriteria);

    // Determine if current method is free shipping
    const currentIsFreeShipping = currentMethod.min_amount && currentMeetsCriteria;
    
    // Determine if best method is free shipping
    const bestIsFreeShipping = bestMethod.min_amount && meetsFreeShippingCriteria(bestMethod);

    // Cases where we should NOT change:
    // 1. Current method is valid, in correct zone, and is the same as best method
    if (!currentIsDisabled && currentMethodZone === targetZone && bestMethod.id === currentMethodId) {
      return;
    }

    // Cases where we SHOULD change:
    // 1. Current method is disabled
    // 2. Current method is in wrong zone
    // 3. Best method is free shipping and current is not (always prefer free shipping)
    // 4. Method is different and we're in the correct zone
    const shouldChange = 
      currentIsDisabled || 
      !isCurrentZoneApplicable || 
      (bestIsFreeShipping && !currentIsFreeShipping) ||
      (currentMethodZone === targetZone && bestMethod.id !== currentMethodId);

    if (shouldChange && bestMethod.id !== currentMethodId) {
      // Create a synthetic event for onInputChange
      const syntheticEvent = {
        target: {
          name: 'shipping_method[0]',
          value: bestMethod.id,
        },
      };
      onInputChange(syntheticEvent);
      if (onShippingChange) {
        onShippingChange(bestMethod.id, bestMethod.cost);
      }
    }
  }, [
    subtotal,
    shippingMethods,
    formData.shipping_method,
    formData.billing_country,
    zoneNames,
    groupedByZone,
    isZoneApplicable,
    meetsFreeShippingCriteria,
    findBestMethodForZone,
    onInputChange,
    onShippingChange,
  ]);

  return (
    <div className="border-b border-gray-200 pt-4 sm:pt-6 pb-4 sm:pb-6">
      <button
        type="button"
        onClick={onToggle}
        className="flex w-full items-center justify-between text-left py-2 -mx-2 px-2 rounded-lg hover:bg-gray-50 transition-colors"
      >
        <div className="flex-1 min-w-0 pr-2">
          <h2 className="text-sm sm:text-base font-semibold text-gray-900">
            Delivery
          </h2>
          {!isExpanded && isComplete && (
            <div className="mt-1 text-xs sm:text-sm text-gray-600 truncate">
              {selectedShippingMethod?.label || "No method selected"}
            </div>
          )}
        </div>
        <div className="ml-2 sm:ml-4 flex items-center gap-2 sm:gap-3 flex-shrink-0">
          {isComplete && (
            <>
              <svg
                className="h-4 w-4 sm:h-5 sm:w-5 text-green-600 flex-shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M5 13l4 4L19 7"
                />
              </svg>
              <span className="text-xs sm:text-sm text-gray-600 hidden sm:inline">Edit</span>
            </>
          )}
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
        </div>
      </button>

      {isExpanded && (
        <div className="mt-3 sm:mt-4">
          {/* Information box */}
          <div className="mb-3 sm:mb-4 rounded-lg bg-amber-50 px-3 sm:px-4 py-2.5 sm:py-3 text-xs sm:text-sm text-gray-700">
            <p>
              Please allow 1-3 additional business days for us to get your order ready to go. Keep in mind we only deliver on business days (aka not on weekends or public holidays).
            </p>
          </div>

          {zoneNames.length > 0 ? (
            <div className="space-y-6">
              {zoneNames.map((zoneName) => {
                const zoneMethods = groupedByZone[zoneName] || [];
                if (zoneMethods.length === 0) return null;

                const isApplicable = isZoneApplicable(zoneName);
                const isGrayedOut = !isApplicable;

                return (
                  <div 
                    key={zoneName} 
                    className={`space-y-3 ${isGrayedOut ? 'opacity-50' : ''}`}
                  >
                    {/* Zone Header */}
                    <h3 className={`text-xs sm:text-sm font-semibold uppercase tracking-wide ${
                      isGrayedOut ? 'text-gray-400' : 'text-gray-900'
                    }`}>
                      {zoneName}
                    </h3>
                    
                    {/* Shipping Methods for this Zone */}
                    <div className="space-y-2.5 sm:space-y-3 pl-0 sm:pl-1">
                      {zoneMethods.map((method) => {
                        // Check if method meets free shipping criteria
                        const meetsCriteria = meetsFreeShippingCriteria(method);
                        // Method is disabled if zone is grayed out OR if it has min_amount but doesn't meet criteria
                        const isMethodDisabled = isGrayedOut || (method.min_amount && !meetsCriteria);
                        const isMethodGrayedOut = isMethodDisabled;

                        return (
                          <div 
                            key={method.id} 
                            className={`flex items-start ${isMethodGrayedOut ? 'opacity-50' : ''}`}
                          >
                            <input
                              id={`shipping_method_${method.id}`}
                              name="shipping_method[0]"
                              type="radio"
                              value={method.id}
                              checked={formData.shipping_method === method.id}
                              onChange={(e) => {
                                onInputChange(e);
                                if (onShippingChange) onShippingChange(method.id, method.cost);
                              }}
                              disabled={isMethodDisabled}
                              className="relative size-4 sm:size-4 mt-0.5 flex-shrink-0 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden touch-manipulation"
                            />
                            <label
                              htmlFor={`shipping_method_${method.id}`}
                              className={`ml-2 sm:ml-3 flex-1 min-w-0 ${
                                isMethodGrayedOut ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 cursor-pointer'
                              }`}
                            >
                              <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-2">
                                <span className="text-xs sm:text-sm font-medium break-words">{method.label}</span>
                                {method.cost && (
                                  <span
                                    className={`text-xs sm:text-sm whitespace-nowrap ${isMethodGrayedOut ? 'text-gray-400' : 'text-gray-500'}`}
                                    dangerouslySetInnerHTML={{ __html: method.cost }}
                                  />
                                )}
                              </div>
                              {method.min_amount && method.min_amount_formatted && (
                                <div className={`mt-1 text-xs ${
                                  isMethodGrayedOut ? 'text-gray-400' : 'text-gray-500'
                                }`}>
                                  Free shipping on orders over{' '}
                                  <span dangerouslySetInnerHTML={{ __html: method.min_amount_formatted }} />
                                </div>
                              )}
                            </label>
                          </div>
                        );
                      })}
                    </div>
                  </div>
                );
              })}
            </div>
          ) : shippingMethods && shippingMethods.length > 0 ? (
            // Fallback: display flat list if no zones
            <div className="space-y-2.5 sm:space-y-3">
              {shippingMethods.map((method) => {
                // Check if method meets free shipping criteria
                const meetsCriteria = meetsFreeShippingCriteria(method);
                // Method is disabled if it has min_amount but doesn't meet criteria
                const isMethodDisabled = method.min_amount && !meetsCriteria;
                const isMethodGrayedOut = isMethodDisabled;

                return (
                  <div 
                    key={method.id} 
                    className={`flex items-start ${isMethodGrayedOut ? 'opacity-50' : ''}`}
                  >
                    <input
                      id={`shipping_method_${method.id}`}
                      name="shipping_method[0]"
                      type="radio"
                      value={method.id}
                      checked={formData.shipping_method === method.id}
                      onChange={(e) => {
                        onInputChange(e);
                        if (onShippingChange) onShippingChange(method.id, method.cost);
                      }}
                      disabled={isMethodDisabled}
                      className="relative size-4 sm:size-4 mt-0.5 flex-shrink-0 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden touch-manipulation"
                    />
                    <label
                      htmlFor={`shipping_method_${method.id}`}
                      className={`ml-2 sm:ml-3 flex-1 min-w-0 ${
                        isMethodGrayedOut ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 cursor-pointer'
                      }`}
                    >
                      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-2">
                        <span className="text-xs sm:text-sm font-medium break-words">{method.label}</span>
                        {method.cost && (
                          <span
                            className={`text-xs sm:text-sm whitespace-nowrap ${isMethodGrayedOut ? 'text-gray-400' : 'text-gray-500'}`}
                            dangerouslySetInnerHTML={{ __html: method.cost }}
                          />
                        )}
                      </div>
                      {method.min_amount && method.min_amount_formatted && (
                        <div className={`mt-1 text-xs ${
                          isMethodGrayedOut ? 'text-gray-400' : 'text-gray-500'
                        }`}>
                          Free shipping on orders over{' '}
                          <span dangerouslySetInnerHTML={{ __html: method.min_amount_formatted }} />
                        </div>
                      )}
                    </label>
                  </div>
                );
              })}
            </div>
          ) : (
            <div className="mt-3">
              <p className="text-sm text-gray-500">
                No shipping methods available. Please check your shipping settings.
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

