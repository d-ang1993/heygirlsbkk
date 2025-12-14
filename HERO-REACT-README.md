# React Hero Component

You now have a 3rd hero component built with React! 🎉

## Files Created

1. **`resources/js/components/HeroReact.jsx`** - The React component
2. **`resources/js/hero-react.jsx`** - Entry file that mounts the component
3. **`resources/views/components/hero-react.blade.php`** - Blade template to use it
4. Updated **`vite.config.js`** - Added hero-react.jsx to build entries

## Features

- ✅ **Carousel mode** - Slideshow with autoplay, navigation arrows, and dots
- ✅ **Image grid mode** - 3-column layout (2-3-2) like hero-new
- ✅ **Static background** - Single background image option
- ✅ **Fully configurable** - Heading, subheading, CTA, colors, alignment, etc.
- ✅ **Auto-pause on hover** - Carousel pauses when user hovers
- ✅ **Keyboard navigation** - (can be added)
- ✅ **Touch/swipe support** - (can be added)

## How to Use

### Option 1: Use the Blade Component (Recommended)

Simply include it in any Blade template:

```blade
<x-hero-react />
```

Or with custom props:

```blade
@php
  // Override theme mods if needed
  set_theme_mod('hero_react_heading', 'Custom Heading');
  set_theme_mod('hero_react_subheading', 'Custom subheading');
@endphp

<x-hero-react />
```

### Option 2: Include in Homepage

Update `resources/views/index.blade.php` to add it as an option:

```blade
@if($section === 'hero')
  <div class="hero-wrapper">
    <!-- React Hero -->
    <x-hero-react />
    
    <!-- OR keep existing desktop/mobile split -->
    <div class="hero-desktop">
      <x-hero-new ... />
    </div>
    <div class="hero-mobile">
      @include('partials.hero')
    </div>
  </div>
@endif
```

### Option 3: Use Directly in a Template

Copy the content from `resources/views/components/hero-react.blade.php` and customize as needed.

## Configuration

The component uses WordPress theme mods (customizer settings). You can configure:

- `hero_react_heading` - Main heading text
- `hero_react_subheading` - Subheading text
- `hero_react_cta_text` - Button text
- `hero_react_cta_url` - Button link
- `hero_react_bg_image` - Background image (attachment ID or URL)
- `hero_react_bg_color` - Background color
- `hero_react_alignment` - Text alignment (center, left, right)
- `hero_react_height` - Section height (e.g., "60vh")
- `hero_react_overlay` - Overlay opacity (0.0 to 1.0)
- `hero_react_carousel_enable` - Enable carousel mode (true/false)
- `hero_react_product_category` - Product category for images
- `hero_react_carousel_count` - Number of carousel images
- `hero_react_autoplay` - Auto-advance carousel (true/false)
- `hero_react_autoplay_speed` - Milliseconds between slides

All settings fall back to the regular hero settings if not set.

## Building for Production

After making changes, rebuild assets:

```bash
npm run build
```

For development with hot reload:

```bash
npm run dev
```

## Styling

The component uses CSS classes prefixed with `hero-react__`. You'll want to add styles in `resources/css/components/hero-react.css` or add them to your existing hero CSS. The component expects:

- `.hero-react` - Main container
- `.hero-react__carousel` - Carousel container
- `.hero-react__carousel-item` - Individual carousel slides
- `.hero-react__bg` - Background element
- `.hero-react__overlay` - Overlay element
- `.hero-react__inner` - Content wrapper
- `.hero-react__title` - Heading
- `.hero-react__subtitle` - Subheading
- `.hero-react__cta` - CTA button
- `.hero-react__image-grid` - Image grid container
- `.hero-react__grid` - Grid wrapper
- `.hero-react__grid-column` - Grid column
- `.hero-react__grid-item` - Grid item
- `.hero-react__arrow` - Navigation arrows
- `.hero-react__carousel-nav` - Dots navigation
- `.hero-react__carousel-dot` - Individual dot

## Next Steps

1. **Add CSS styling** - Create `resources/css/components/hero-react.css` or add to existing hero CSS
2. **Test it out** - Include `<x-hero-react />` in a template and see it in action
3. **Customize** - Adjust the component to match your design needs
4. **Add to homepage** - Update your homepage section order to use it

Enjoy your new React hero component! 🚀




