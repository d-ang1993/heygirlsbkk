/** @jsxImportSource react */
import React, { useMemo } from "react";

export default function Delivery({
  formData,
  shippingMethods = [],
  shippingMethodsByZone = {},
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

  // Get zone names in a consistent order (Thailand first, then Global, then others)
  const zoneNames = useMemo(() => {
    const zones = Object.keys(groupedByZone);
    const ordered = [];
    if (zones.includes('Thailand')) ordered.push('Thailand');
    if (zones.includes('Global')) ordered.push('Global');
    zones.forEach(zone => {
      if (!ordered.includes(zone)) ordered.push(zone);
    });
    return ordered;
  }, [groupedByZone]);

  return (
    <div className="border-b border-gray-200 pt-6 pb-6">
      <button
        type="button"
        onClick={onToggle}
        className="flex w-full items-center justify-between text-left"
      >
        <div className="flex-1">
          <h2 className="text-base font-semibold text-gray-900">
            Delivery
          </h2>
          {!isExpanded && isComplete && (
            <div className="mt-1 text-sm text-gray-600">
              {selectedShippingMethod?.label || "No method selected"}
            </div>
          )}
        </div>
        <div className="ml-4 flex items-center gap-3">
          {isComplete && (
            <>
              <svg
                className="h-5 w-5 text-green-600"
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
              <span className="text-sm text-gray-600">Edit</span>
            </>
          )}
          <svg
            className={`h-5 w-5 text-gray-500 transition-transform ${
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
        <div className="mt-4">
          {/* Information box */}
          <div className="mb-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-gray-700">
            <p>
              Please allow 1-3 additional business days for us to get your order ready to go. Keep in mind we only deliver on business days (aka not on weekends or public holidays).
            </p>
          </div>

          {zoneNames.length > 0 ? (
            <div className="space-y-6">
              {zoneNames.map((zoneName) => {
                const zoneMethods = groupedByZone[zoneName] || [];
                if (zoneMethods.length === 0) return null;

                return (
                  <div key={zoneName} className="space-y-3">
                    {/* Zone Header */}
                    <h3 className="text-sm font-semibold text-gray-900 uppercase tracking-wide">
                      {zoneName}
                    </h3>
                    
                    {/* Shipping Methods for this Zone */}
                    <div className="space-y-3 pl-1">
                      {zoneMethods.map((method) => (
                        <div key={method.id} className="flex items-center">
                          <input
                            id={`shipping_method_${method.id}`}
                            name="shipping_method[0]"
                            type="radio"
                            value={method.id}
                            checked={formData.shipping_method === method.id}
                            onChange={(e) => {
                              onInputChange(e);
                              if (onShippingChange) onShippingChange(method.id);
                            }}
                            className="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden"
                          />
                          <label
                            htmlFor={`shipping_method_${method.id}`}
                            className="ml-3 flex flex-1 items-center justify-between text-sm font-medium text-gray-700 cursor-pointer"
                          >
                            <span>{method.label}</span>
                            {method.cost && (
                              <span
                                className="ml-2 text-gray-500"
                                dangerouslySetInnerHTML={{ __html: method.cost }}
                              />
                            )}
                          </label>
                        </div>
                      ))}
                    </div>
                  </div>
                );
              })}
            </div>
          ) : shippingMethods && shippingMethods.length > 0 ? (
            // Fallback: display flat list if no zones
            <div className="space-y-3">
              {shippingMethods.map((method) => (
                <div key={method.id} className="flex items-center">
                  <input
                    id={`shipping_method_${method.id}`}
                    name="shipping_method[0]"
                    type="radio"
                    value={method.id}
                    checked={formData.shipping_method === method.id}
                    onChange={(e) => {
                      onInputChange(e);
                      if (onShippingChange) onShippingChange(method.id);
                    }}
                    className="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden"
                  />
                  <label
                    htmlFor={`shipping_method_${method.id}`}
                    className="ml-3 flex flex-1 items-center justify-between text-sm font-medium text-gray-700 cursor-pointer"
                  >
                    <span>{method.label}</span>
                    {method.cost && (
                      <span
                        className="ml-2 text-gray-500"
                        dangerouslySetInnerHTML={{ __html: method.cost }}
                      />
                    )}
                  </label>
                </div>
              ))}
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

