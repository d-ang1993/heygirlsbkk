# Cart Slider Component

A React-based shopping cart drawer component that replaces the vanilla JavaScript cart drawer functionality.

## Files Structure

```
cart-slider/
├── CartSlider.jsx          # Main cart drawer component
├── CartItem.jsx            # Individual cart item component
├── CartSliderApp.jsx       # App wrapper with integration logic
├── useCartSlider.js        # Custom hook for cart slider state
├── CartSlider.css          # Component styles
├── index.js                # Exports
└── README.md              # This file
```

## Usage

### Option 1: Auto-initialization (Recommended)

Import the entry point file in your main JavaScript bundle:

```javascript
import './cart-slider.js';
```

This will automatically:
- Find the `.cart-trigger` button in your navbar
- Mount the React cart slider component
- Handle all cart interactions

### Option 2: Manual Initialization

```javascript
import { initCartSliderApp } from './components/cart-slider';

// Initialize when ready
initCartSliderApp();
```

### Option 3: Use in React Components

```jsx
import { CartSlider } from './components/cart-slider';
import { useCartSlider } from './components/cart-slider';

function MyComponent() {
  const { isOpen, openCartSlider, closeCartSlider } = useCartSlider();
  
  return (
    <>
      <button onClick={openCartSlider}>Open Cart</button>
      <CartSlider isOpen={isOpen} onClose={closeCartSlider} />
    </>
  );
}
```

## Features

- ✅ React-based with hooks
- ✅ Automatic cart loading and caching
- ✅ Real-time cart updates via WooCommerce events
- ✅ Quantity management (increase/decrease)
- ✅ Remove items from cart
- ✅ Empty cart state
- ✅ Loading states
- ✅ Error handling
- ✅ Mobile responsive
- ✅ Keyboard support (ESC to close)
- ✅ Click overlay to close
- ✅ Backward compatible with existing cart trigger buttons

## Integration

The component automatically integrates with:
- Existing `.cart-trigger` buttons in the navbar
- WooCommerce AJAX endpoints
- WooCommerce cart update events
- Bag count updates

## Styling

Styles are in `CartSlider.css`. The component uses your brand's primary color (`#f7a9d0`) for buttons and accents.

## Migration from Vanilla JS

To migrate from the old `cart.js`:

1. Import the new React component instead of `cart.js`
2. Remove the old HTML cart drawer from `navbar.blade.php` (optional - React will create its own)
3. The component maintains the same API surface for backward compatibility


