/** @jsxImportSource react */
import React, { useState } from "react";
import { Button, QuantitySelector } from "../../ui";
import DeleteIcon from '@mui/icons-material/Delete';

export default function CartItem({ item, onQuantityUpdate, onRemove }) {
  const [quantity, setQuantity] = useState(item.quantity);
  const [isUpdating, setIsUpdating] = useState(false);

  // Parse variation data to extract color and size
  const parseVariation = (variationString) => {
    if (!variationString) return { color: "", size: "" };

    const colorMatch = variationString.match(/Color:?\s*([^,<]+)/i);
    const sizeMatch = variationString.match(/Size:?\s*([^,<]+)/i);

    return {
      color: colorMatch ? colorMatch[1].trim() : "",
      size: sizeMatch ? sizeMatch[1].trim() : "",
    };
  };

  const { color, size } = parseVariation(item.variation);

  const handleQuantityChange = async (newQuantity) => {
    const qty = Math.max(1, parseInt(newQuantity) || 1);
    setQuantity(qty);
    setIsUpdating(true);

    try {
      await onQuantityUpdate(item.cart_item_key, qty);
    } finally {
      setIsUpdating(false);
    }
  };


  const handleRemove = () => {
    if (window.confirm("Remove this item from cart?")) {
      onRemove(item.cart_item_key);
    }
  };

  return (
    <div className="cart-item" data-cart-item-key={item.cart_item_key}>
      <div className="cart-item-image">
        <img
          src={item.image}
          alt={item.productName || item.name}
          loading="lazy"
        />
      </div>
      <div className="cart-item-details">
        <div className="cart-item-top">
          <div className="cart-item-info">
            <div className="cart-item-name">
              {item.productName || item.name}
            </div>
            {color && (
              <div className="cart-item-variation">{color}</div>
            )}
            {size && <div className="cart-item-variation">{size}</div>}
          </div>
          <div 
            className="cart-item-price"
            dangerouslySetInnerHTML={{ __html: item.price }}
          ></div>
        </div>
        <div className="cart-item-bottom">
          <div className="cart-item-bottom-left">
            <QuantitySelector
              value={quantity}
              onChange={handleQuantityChange}
              min={1}
              size="sm"
              disabled={isUpdating}
            />
          </div>
          <div className="cart-item-bottom-middle"></div>
          <div className="cart-item-bottom-right">
            <button
              onClick={handleRemove}
              disabled={isUpdating}
              type="button"
              className="cart-item-delete-button"
              aria-label="Remove item"
            >
              <DeleteIcon />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

