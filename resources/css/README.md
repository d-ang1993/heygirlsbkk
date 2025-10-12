# Modular CSS Structure

This CSS is organized using a modular approach with PostCSS imports for better maintainability.

## Structure

```
resources/css/
├── app.css                          # Main entry point - imports all modules
├── base/                            # Base styles and variables
│   ├── variables.css                # CSS variables and Tailwind imports
│   └── images.css                   # Image optimization and lazy loading
├── components/                      # Reusable UI components
│   ├── buttons.css                  # Button styles
│   ├── hero.css                     # Hero section and carousel
│   ├── product-carousel.css         # Product image carousel
│   ├── product-grid.css             # Product grid layouts
│   ├── navbar.css                   # Navigation bar
│   ├── cart-drawer.css              # Shopping cart drawer
│   ├── footer.css                   # Footer styles
│   └── woocommerce.css              # WooCommerce forms and components
└── pages/                           # Page-specific styles
    ├── home.css                     # Homepage styles
    └── product.css                  # Product page styles
```

## How It Works

All CSS files are imported in `app.css` in this order:
1. **Base** - Variables, resets, global styles
2. **Components** - Reusable UI components
3. **Pages** - Page-specific styles

Vite will bundle all these imports into a single optimized CSS file.

## Benefits

- ✅ **Easier to maintain** - Find styles quickly by component/page
- ✅ **Better organization** - Logical separation of concerns
- ✅ **Reusable** - Components can be used across pages
- ✅ **Scalable** - Easy to add new components or pages
- ✅ **Same output** - Still builds to a single CSS file

## Adding New Styles

### New Component
Create a new file in `components/` and import it in `app.css`:
```css
@import './components/your-component.css';
```

### New Page
Create a new file in `pages/` and import it in `app.css`:
```css
@import './pages/your-page.css';
```

## Building

Run `npm run build` to compile all CSS into a single optimized file.
The modular structure is only for development - production still gets one CSS file.

