/** @jsxImportSource react */
import React from "react";
import { Button } from "../../ui";

export default function CartActions() {
  return (
    <div className="cart-actions">
      <Button
        as="a"
        href="/cart/"
        variant="neutral"
        fullWidth
      >
        View Cart
      </Button>
      <Button
        as="a"
        href="/checkout/"
        variant="black"
        fullWidth
      >
        Checkout
      </Button>
    </div>
  );
}

