/** @jsxImportSource react */
import { useState, useCallback } from "react";

/**
 * Custom hook for managing cart slider state
 * Can be used to control the cart slider from anywhere in the app
 */
export function useCartSlider() {
  const [isOpen, setIsOpen] = useState(false);

  const openCartSlider = useCallback(() => {
    setIsOpen(true);
    document.body.style.overflow = "hidden";
  }, []);

  const closeCartSlider = useCallback(() => {
    setIsOpen(false);
    document.body.style.overflow = "";
  }, []);

  const toggleCartSlider = useCallback(() => {
    if (isOpen) {
      closeCartSlider();
    } else {
      openCartSlider();
    }
  }, [isOpen, openCartSlider, closeCartSlider]);

  return {
    isOpen,
    openCartSlider,
    closeCartSlider,
    toggleCartSlider,
  };
}


