/** @jsxImportSource react */
import React from "react";

export default function OrderItems({ items = [] }) {
  if (!items || items.length === 0) return null;

  return (
    <>
      <h3 className="sr-only">Items</h3>
      {items.map((item, index) => (
        <div
          key={index}
          className="flex space-x-6 border-b border-gray-200 py-8 sm:py-10"
        >
          {item.imageSrc && (
            <img
              alt={item.imageAlt || item.name}
              src={item.imageSrc}
              className="h-28 w-28 flex-none rounded-2xl bg-gray-100 object-cover sm:h-36 sm:w-36"
            />
          )}
          <div className="flex flex-auto flex-col">
            <div>
              <h4 className="text-base font-semibold text-gray-900 sm:text-lg">
                {item.name}
              </h4>
              {item.description && (
                <p className="mt-2 text-sm text-gray-600 sm:text-base">
                  {item.description}
                </p>
              )}
              {item.meta && (
                <div
                  className="mt-2 text-xs text-gray-500 sm:text-sm"
                  dangerouslySetInnerHTML={{ __html: item.meta }}
                />
              )}
            </div>
            <div className="mt-4 flex flex-1 items-end sm:mt-6">
              <dl className="flex divide-x divide-gray-200 text-sm sm:text-base">
                <div className="flex pr-4 sm:pr-6">
                  <dt className="font-medium text-gray-900">Quantity</dt>
                  <dd className="ml-2 text-gray-800">{item.quantity}</dd>
                </div>
                <div className="flex pl-4 sm:pl-6">
                  <dt className="font-medium text-gray-900">Price</dt>
                  <dd
                    className="ml-2 font-semibold text-gray-900"
                    dangerouslySetInnerHTML={{ __html: item.formattedTotal }}
                  />
                </div>
              </dl>
            </div>
          </div>
        </div>
      ))}
    </>
  );
}
