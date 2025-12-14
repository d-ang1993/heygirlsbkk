/** @jsxImportSource react */
import React from "react";
import CartActions from "./CartActions";

export default function CartDrawerFooter({ items, subtotal, total, onClose }) {
  // Decode HTML entities
  const decodeHTMLEntities = (str) => {
    if (!str || typeof str !== "string") return "";
    const textarea = document.createElement("textarea");
    textarea.innerHTML = str;
    return textarea.value;
  };

  // Extract text content from HTML price string
  const extractPriceText = (htmlPrice) => {
    if (!htmlPrice) return "";
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = htmlPrice;
    return tempDiv.textContent || tempDiv.innerText || "";
  };

  return (
    <div className="cart-drawer-footer">
      <div className="cart-totals">
        <div className="cart-subtotal-section">
          <div className="cart-subtotal-breakdown">
            {items.map((item, index) => {
              const priceText = extractPriceText(item.price);
              const quantity = item.quantity || 1;
              const itemName = item.productName || item.name || "Item";
              return (
                <div key={item.cart_item_key || index} className="cart-item-calculation">
                  <span className="item-calculation-name">{itemName}</span>
                  <span className="item-calculation-math">
                    {priceText} × {quantity}
                  </span>
                </div>
              );
            })}
          </div>
        </div>
        <div className="cart-totals">
          <span>Total:</span>
          <span
            className="cart-total-amount"
            dangerouslySetInnerHTML={{
              __html: decodeHTMLEntities(total || subtotal || ""),
            }}
          ></span>
        </div>
      </div>
      <CartActions />
    </div>
  );
}

