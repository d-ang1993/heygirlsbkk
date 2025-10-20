# 🎨 Color System Guide

## Overview
This theme uses a beautiful pink and mint color scheme that creates a fresh, elegant aesthetic perfect for a beauty/fashion brand.

---

## Primary Color - Pink 💗
**Base:** `#f7a9d0` (Soft Pink)

### When to use:
- ✅ Navbar background
- ✅ Footer background  
- ✅ Primary action buttons (e.g., "Add to Cart", "Buy Now", "Subscribe")
- ✅ Brand-critical elements
- ✅ Main CTAs (Call-to-Actions)

### CSS Usage:
```css
/* Use the CSS variable */
background: var(--color-primary);

/* For hover states */
background: var(--color-primary-dark);    /* #f271ba */

/* For active/pressed states */
background: var(--color-primary-darker);  /* #e252a2 */
```

### In HTML:
```html
<button class="btn btn-primary">Add to Cart</button>
```

---

## Secondary Color - Mint 🌿
**Base:** `#a9f7d0` (Soft Mint/Teal)

### When to use:
- ✅ Secondary buttons (e.g., "Learn More", "View Details", "Continue Shopping")
- ✅ Accent elements and highlights
- ✅ Special badges or tags (e.g., "New", "Sale", "Featured")
- ✅ Background accents for content blocks
- ✅ Borders or dividers to create visual interest
- ✅ Alternative CTAs that should be visible but not compete with primary pink

### CSS Usage:
```css
/* Use the CSS variable */
background: var(--color-secondary);

/* For hover states */
background: var(--color-secondary-dark);    /* #7ee5b8 */

/* For active/pressed states */
background: var(--color-secondary-darker);  /* #5cd4a3 */
```

### In HTML:
```html
<button class="btn btn-secondary">Learn More</button>
```

---

## Design Guidelines

### ✨ Best Practices

1. **Primary for Action, Secondary for Information**
   - Primary pink = "Do this now!" (purchase, subscribe, main actions)
   - Secondary mint = "Learn more" (informational, secondary actions)

2. **Don't Overuse**
   - Use colors intentionally, not everywhere
   - White space is your friend
   - Let the colors stand out by using them strategically

3. **Hierarchy**
   - Primary buttons should be the most prominent
   - Secondary buttons should be visible but less dominant
   - Use neutral colors for less important elements

4. **Accessibility**
   - Both colors have good contrast with black text
   - Always test readability with your text colors

### 🎨 Color Combinations That Work Well

```css
/* Pink background with white text */
.hero-section {
  background: var(--color-primary);
  color: var(--color-white);
}

/* Mint accent on white background */
.info-card {
  background: var(--color-white);
  border: 2px solid var(--color-secondary);
}

/* Alternating sections */
.section-pink {
  background: var(--color-primary);
}

.section-mint {
  background: var(--color-secondary);
}

/* Badges and tags */
.badge-sale {
  background: var(--color-secondary);
  color: var(--color-black);
}

.badge-new {
  background: var(--color-primary-dark);
  color: var(--color-white);
}
```

---

## Examples

### Button Hierarchy
```html
<!-- Most important action - Primary -->
<button class="btn btn-primary">Add to Cart</button>

<!-- Secondary action -->
<button class="btn btn-secondary">View Details</button>

<!-- Least important action -->
<button class="btn btn-outline">Cancel</button>
```

### Highlighting Content
```html
<!-- Featured product with mint accent -->
<div class="product-card featured" style="border-left: 4px solid var(--color-secondary);">
  <!-- Product content -->
</div>

<!-- Special offer with pink background -->
<div class="offer-banner" style="background: var(--color-primary); color: white;">
  Limited Time Offer!
</div>
```

---

## Available CSS Variables

### Colors
```css
/* Primary Pink */
--color-primary: #f7a9d0;
--color-primary-dark: #f271ba;
--color-primary-darker: #e252a2;

/* Secondary Mint */
--color-secondary: #a9f7d0;
--color-secondary-dark: #7ee5b8;
--color-secondary-darker: #5cd4a3;

/* Neutrals */
--color-white: #ffffff;
--color-black: #000000;
--color-gray-light: #f3f4f6;
--color-gray: #6b7280;
--color-gray-dark: #374151;
```

---

## Need to Change Colors?

All color definitions are in: `resources/css/base/variables.css`

Update the hex values there, and the changes will apply throughout the entire theme automatically! 🎉

