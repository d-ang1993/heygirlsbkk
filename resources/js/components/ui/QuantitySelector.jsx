/** @jsxImportSource react */
import React, { useState } from "react";
import styled from "styled-components";
import AddCircleIcon from '@mui/icons-material/AddCircle';
import RemoveCircleIcon from '@mui/icons-material/RemoveCircle';

const QuantityContainer = styled.div`
  display: flex;
  align-items: center;
  gap: 4px;
  border: none;
  background: none;
`;

const QuantityButton = styled.button`
  width: 20px;
  height: 20px;
  min-width: 20px;
  min-height: 20px;
  aspect-ratio: 1;
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 400;
  color: var(--color-primary-dark, #f271ba);
  padding: 0;
  margin: 0;
  transition: all 0.2s ease;

  &:hover:not(:disabled) {
    background: #f3f4f6;
    opacity: 0.8;
  }

  &:active:not(:disabled) {
    background: #e5e7eb;
    opacity: 0.6;
  }

  &:disabled {
    opacity: 0.3;
    cursor: not-allowed;
  }

  ${(props) =>
    props.$size === "sm" &&
    `
    width: 18px;
    height: 18px;
    min-width: 18px;
    min-height: 18px;
    font-size: 14px;
  `}

  ${(props) =>
    props.$size === "lg" &&
    `
    width: 32px;
    height: 32px;
    min-width: 32px;
    min-height: 32px;
    font-size: 20px;
  `}
`;

const QuantityInput = styled.input`
  font-size: 14px;
  font-weight: 400;
  color: #000;
  text-decoration: underline;
  min-width: 12px;
  text-align: center;
  border: none;
  background: transparent;
  padding: 0;
  -moz-appearance: textfield;
  outline: none;
  width: auto;

  &::-webkit-outer-spin-button,
  &::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  &:focus {
    text-decoration: none;
    outline: 1px solid #000;
    outline-offset: 2px;
    border-radius: 2px;
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  ${(props) =>
    props.$size === "sm" &&
    `
    font-size: 12px;
    min-width: 10px;
  `}

  ${(props) =>
    props.$size === "lg" &&
    `
    font-size: 16px;
    min-width: 20px;
  `}
`;

/**
 * QuantitySelector Component
 * 
 * @param {number} value - Current quantity value
 * @param {function} onChange - Callback when quantity changes (receives new quantity)
 * @param {number} min - Minimum quantity (default: 1)
 * @param {number} max - Maximum quantity (optional)
 * @param {string} size - Size variant: 'sm', 'md' (default), 'lg'
 * @param {boolean} disabled - Disable the selector
 * @param {object} props - All other standard props
 */
export default function QuantitySelector({
  value,
  onChange,
  min = 1,
  max,
  size = "md",
  disabled = false,
  ...props
}) {
  const [localValue, setLocalValue] = useState(value?.toString() || "1");

  // Sync with external value changes
  React.useEffect(() => {
    setLocalValue(value?.toString() || "1");
  }, [value]);

  const handleDecrease = () => {
    const current = parseInt(localValue) || min;
    const newValue = Math.max(min, current - 1);
    setLocalValue(newValue.toString());
    if (onChange) {
      onChange(newValue);
    }
  };

  const handleIncrease = () => {
    const current = parseInt(localValue) || min;
    const newValue = max ? Math.min(max, current + 1) : current + 1;
    setLocalValue(newValue.toString());
    if (onChange) {
      onChange(newValue);
    }
  };

  const handleInputChange = (e) => {
    const inputValue = e.target.value;
    // Allow empty string for editing, or valid numbers
    if (inputValue === "" || /^\d+$/.test(inputValue)) {
      setLocalValue(inputValue);
    }
  };

  const handleInputBlur = (e) => {
    const numValue = parseInt(e.target.value) || min;
    const clampedValue = max
      ? Math.min(Math.max(min, numValue), max)
      : Math.max(min, numValue);
    setLocalValue(clampedValue.toString());
    if (onChange) {
      onChange(clampedValue);
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === "ArrowUp") {
      e.preventDefault();
      handleIncrease();
    } else if (e.key === "ArrowDown") {
      e.preventDefault();
      handleDecrease();
    }
  };

  const currentValue = parseInt(localValue) || min;
  const isMin = currentValue <= min;
  const isMax = max ? currentValue >= max : false;

  return (
    <QuantityContainer {...props}>
      <QuantityButton
        $size={size}
        type="button"
        onClick={handleDecrease}
        disabled={disabled || isMin}
        aria-label="Decrease quantity"
      >
        <RemoveCircleIcon 
          fontSize={size === "sm" ? "small" : size === "lg" ? "large" : "medium"}
          style={{ fontSize: size === "sm" ? "18px" : size === "lg" ? "26px" : "22px", color: 'inherit' }}
        />
      </QuantityButton>
      <QuantityInput
        $size={size}
        type="number"
        value={localValue}
        min={min}
        max={max}
        onChange={handleInputChange}
        onBlur={handleInputBlur}
        onKeyDown={handleKeyDown}
        disabled={disabled}
        aria-label="Quantity"
      />
      <QuantityButton
        $size={size}
        type="button"
        onClick={handleIncrease}
        disabled={disabled || isMax}
        aria-label="Increase quantity"
      >
        <AddCircleIcon 
          fontSize={size === "sm" ? "small" : size === "lg" ? "large" : "medium"}
          style={{ fontSize: size === "sm" ? "18px" : size === "lg" ? "26px" : "22px", color: 'inherit' }}
        />
      </QuantityButton>
    </QuantityContainer>
  );
}

