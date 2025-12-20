# Product Variations React Component

This folder contains the React version of the product variations component.

## Files

- `ProductVariations.jsx` - Main React component for product variations (color and size selection)

## Usage

The component is currently enabled in the blade template. To disable it:

1. Comment out the React component section in `resources/views/components/product/variations.blade.php`
2. Rebuild assets: `npm run build` or `npm run dev`
3. The component will mount to `#product-variations-react-container`

## Props

- `productId` - Product ID
- `productType` - Product type ('variable' or 'simple')
- `variations` - Array of variation objects
- `attributes` - Product attributes
- `colorVariations` - Object of color variations with hex values
- `sizeVariations` - Object of size variations
- `sizes` - Array of available sizes
- `availableAttributes` - Array of available attribute names

## Notes

- The component handles both singular (`attribute_pa_size`) and plural (`attribute_pa_sizes`) size attribute names
- Stock status is checked from multiple possible properties: `is_in_stock`, `in_stock`, `stock_status`
- Color selection auto-initializes with the first available color
- Size buttons are enabled/disabled based on stock availability for the selected color
