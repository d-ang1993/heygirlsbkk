# 🔧 Section Ordering Troubleshooting

## Current Issue
The Section Ordering drag and drop interface is not appearing in the WordPress Customizer.

## Quick Fix Options

### Option 1: Check Browser Console
1. Open WordPress Customizer
2. Press F12 to open Developer Tools
3. Go to Console tab
4. Look for any JavaScript errors
5. Look for "Section Ordering:" messages

### Option 2: Simple Manual Ordering
If the drag and drop doesn't work, you can manually set the order by editing the theme options:

1. Go to **WordPress Admin > Appearance > Customize**
2. Look for **"Section Ordering"** section
3. You should see a hidden field called "Homepage Section Order"
4. The current order is: `hero,new_drops,featured_products,new_arrival,footer`

### Option 3: Test Different Orders
Try these different orders by changing the setting:

#### Fashion Brand Order:
```
new_drops,hero,featured_products,new_arrival,footer
```

#### Product-Focused Order:
```
hero,featured_products,new_drops,new_arrival,footer
```

#### Campaign-Focused Order:
```
new_drops,hero,featured_products,new_arrival,footer
```

## How to Change Order Manually

### Method 1: WordPress Customizer
1. Go to **Appearance > Customize**
2. Find **"Section Ordering"** section
3. Look for the hidden field
4. Change the comma-separated list

### Method 2: Database (Advanced)
1. Go to **WordPress Admin > Tools > Database**
2. Find the `wp_options` table
3. Look for `theme_mods_heygirlsbkk` option
4. Find `homepage_section_order` and change the value

## Current Section Mapping

| Section ID | Display Name | Template File |
|------------|--------------|---------------|
| `hero` | Homepage Hero | `partials.hero` |
| `new_drops` | New Drops Carousel | `partials.new-drops-carousel` |
| `featured_products` | Featured Products | `partials.featured-products` |
| `new_arrival` | New Arrival | `partials.new-arrival` |
| `footer` | Footer | `partials.footer` |

## Testing the Order

### Check Homepage
1. Go to your website homepage
2. Sections should appear in the order you set
3. If not, clear any caching plugins

### Check Customizer Menu
1. The customizer menu should reorder to match your preference
2. If not, refresh the customizer page

## Common Issues

### JavaScript Not Loading
- Check if jQuery UI Sortable is available
- Look for JavaScript errors in console
- Try refreshing the customizer page

### Sections Not Reordering
- Make sure you clicked "Publish" in customizer
- Clear any caching plugins
- Check if the homepage template is using the dynamic order

### Customizer Menu Not Updating
- The menu order updates after publishing
- Try refreshing the customizer page
- Check if all sections are enabled

## Quick Test

### Test Current Order
1. Go to your homepage
2. Check if sections appear in this order:
   - Homepage Hero
   - New Drops Carousel
   - Featured Products
   - New Arrival
   - Footer

### Test New Order
1. Change the order to: `new_drops,hero,featured_products,new_arrival,footer`
2. Publish changes
3. Check homepage - New Drops should appear first

## Fallback Solution

If the drag and drop never works, you can:

1. **Use the manual method** above
2. **Edit the default order** in the code
3. **Use a different ordering plugin**

## Need Help?

If none of these solutions work:

1. Check browser console for errors
2. Try a different browser
3. Disable other plugins temporarily
4. Check if the theme is properly activated

The Section Ordering feature is working in the backend - the issue is just with the drag and drop interface display.
