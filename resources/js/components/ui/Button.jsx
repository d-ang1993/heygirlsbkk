/** @jsxImportSource react */
import React from "react";
import styled from "styled-components";

// Styled Button component
const StyledButton = styled.button`
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  border: 1px solid transparent;
  font-weight: 500;
  transition: all 0.2s ease-in-out;
  cursor: pointer;
  outline: none;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  text-decoration: none;
  
  /* Focus styles */
  &:focus {
    outline: none;
    box-shadow: 0 0 0 2px white, 0 0 0 4px var(--color-primary);
  }
  
  /* Disabled state */
  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
  }

  /* Size variants */
  ${(props) => {
    switch (props.$size) {
      case "sm":
        return `
          padding: 0.5rem 1rem;
          font-size: 0.875rem;
          line-height: 1.25rem;
        `;
      case "lg":
        return `
          padding: 0.875rem 1.75rem;
          font-size: 1.125rem;
          line-height: 1.75rem;
        `;
      default:
        return `
          padding: 0.75rem 1.5rem;
          font-size: 1rem;
          line-height: 1.5rem;
        `;
    }
  }}

  /* Variant styles */
  ${(props) => {
    if (props.$variant === "primary") {
      return `
        background-color: var(--color-primary-dark);
        color: white;
        
        &:hover:not(:disabled) {
          background-color: var(--color-primary-darker);
        }
        
        &:active:not(:disabled) {
          background-color: var(--color-primary-darker);
        }
      `;
    }
    
    if (props.$variant === "secondary") {
      return `
        background-color: var(--color-secondary);
        color: var(--color-black);
        
        &:hover:not(:disabled) {
          background-color: var(--color-secondary-dark);
        }
        
        &:active:not(:disabled) {
          background-color: var(--color-secondary-darker);
        }
      `;
    }
    
    if (props.$variant === "outline") {
      return `
        background-color: transparent;
        color: var(--color-primary);
        border-color: var(--color-primary);
        
        &:hover:not(:disabled) {
          background-color: var(--color-primary);
          color: white;
        }
        
        &:active:not(:disabled) {
          background-color: var(--color-primary-dark);
        }
      `;
    }
    
    if (props.$variant === "ghost") {
      return `
        background-color: transparent;
        color: var(--color-primary);
        border-color: transparent;
        box-shadow: none;
        
        &:hover:not(:disabled) {
          background-color: rgba(247, 169, 208, 0.1);
        }
        
        &:active:not(:disabled) {
          background-color: rgba(247, 169, 208, 0.2);
        }
      `;
    }
    
    if (props.$variant === "danger" || props.$variant === "remove") {
      return `
        background-color: transparent;
        color: #ef4444;
        border-color: transparent;
        box-shadow: none;
        
        &:hover:not(:disabled) {
          color: #dc2626;
          background-color: rgba(239, 68, 68, 0.1);
        }
        
        &:active:not(:disabled) {
          background-color: rgba(239, 68, 68, 0.2);
        }
      `;
    }
    
    if (props.$variant === "black") {
      return `
        background-color: #000000;
        color: #ffffff;
        border-color: #000000;
        
        &:hover:not(:disabled) {
          background-color: #1a1a1a;
          border-color: #1a1a1a;
          transform: translateY(-1px);
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        &:active:not(:disabled) {
          background-color: #2a2a2a;
          transform: translateY(0);
        }
      `;
    }
    
    if (props.$variant === "neutral" || props.$variant === "outline-neutral") {
      return `
        background-color: transparent;
        color: #6b7280;
        border-color: #e5e7eb;
        
        &:hover:not(:disabled) {
          background-color: #f9fafb;
          color: #374151;
          border-color: #d1d5db;
          transform: translateY(-1px);
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        &:active:not(:disabled) {
          background-color: #f3f4f6;
          transform: translateY(0);
        }
      `;
    }
    
    // Default (primary)
    return `
      background-color: var(--color-primary);
      color: white;
      
      &:hover:not(:disabled) {
        background-color: var(--color-primary-dark);
      }
      
      &:active:not(:disabled) {
        background-color: var(--color-primary-darker);
      }
    `;
  }}

  /* Full width */
  ${(props) => props.$fullWidth && `width: 100%;`}
`;

/**
 * Button Component
 * 
 * @param {string} variant - Button style variant: 'primary' (default), 'secondary', 'outline', 'ghost', 'black', 'neutral'
 * @param {string} size - Button size: 'sm', 'md' (default), 'lg'
 * @param {boolean} fullWidth - Make button full width
 * @param {boolean} disabled - Disable the button
 * @param {React.ReactNode} children - Button content
 * @param {object} props - All other standard button props
 */
export default function Button({
  variant = "primary",
  size = "md",
  fullWidth = false,
  children,
  ...props
}) {
  return (
    <StyledButton
      $variant={variant}
      $size={size}
      $fullWidth={fullWidth}
      {...props}
    >
      {children}
    </StyledButton>
  );
}

