# 🎯 Section Ordering Feature

## Overview
The Section Ordering feature allows you to drag and drop sections in the WordPress Customizer to reorder how they appear on your homepage. This gives you complete control over your homepage layout without touching any code!

---

## 🎮 How to Use

### **Step 1: Access Section Ordering**
1. Go to **WordPress Admin > Appearance > Customize**
2. Look for **"Section Ordering"** at the very top of the customizer menu
3. Click to expand the section

### **Step 2: Drag and Drop**
1. You'll see a list of all your homepage sections:
   - **Homepage Hero**
   - **New Drops Carousel**
   - **Featured Products**
   - **New Arrival**
   - **Footer**

2. **Drag and drop** any section to reorder them
3. The order will update **instantly** in the preview
4. Click **"Publish"** to save your changes

### **Step 3: See the Results**
- Your homepage will now display sections in the order you chose
- The customizer menu will also reorder to match your preference

---

## 🎨 Available Sections

| Section | Description | Default Position |
|---------|-------------|------------------|
| **Homepage Hero** | Main banner with background image | 1st |
| **New Drops Carousel** | Image carousel with buttons | 2nd |
| **Featured Products** | Product showcase grid | 3rd |
| **New Arrival** | New products section | 4th |
| **Footer** | Site footer with links | 5th |

---

## 💡 Design Tips

### **Recommended Orders:**

#### **For Fashion/Beauty Brands:**
1. **Homepage Hero** - First impression
2. **New Drops Carousel** - Latest arrivals
3. **Featured Products** - Best sellers
4. **New Arrival** - Fresh products
5. **Footer** - Always last

#### **For Product-Focused Sites:**
1. **Homepage Hero** - Brand message
2. **Featured Products** - Main products
3. **New Drops Carousel** - Special offers
4. **New Arrival** - Latest additions
5. **Footer** - Always last

#### **For Seasonal Campaigns:**
1. **New Drops Carousel** - Seasonal focus
2. **Homepage Hero** - Campaign message
3. **Featured Products** - Seasonal products
4. **New Arrival** - New seasonal items
5. **Footer** - Always last

---

## 🔧 Technical Details

### **How It Works:**
- Uses WordPress Customizer API
- Drag and drop powered by jQuery UI Sortable
- Order is saved as a comma-separated string
- Homepage dynamically loads sections based on order
- Customizer menu reorders to match your preference

### **Files Modified:**
- `app/Providers/CustomizerServiceProvider.php` - Customizer controls
- `resources/views/index.blade.php` - Dynamic section loading

### **Default Order:**
```
hero,new_drops,featured_products,new_arrival,footer
```

---

## 🚀 Advanced Usage

### **Custom Order Examples:**

#### **Hero-First Layout:**
```
hero,featured_products,new_drops,new_arrival,footer
```

#### **Carousel-First Layout:**
```
new_drops,hero,featured_products,new_arrival,footer
```

#### **Product-Focused Layout:**
```
hero,featured_products,new_arrival,new_drops,footer
```

---

## 🎯 Best Practices

### **Do:**
- ✅ Keep **Footer** at the bottom
- ✅ Put your most important section first
- ✅ Group related sections together
- ✅ Test on mobile after reordering

### **Don't:**
- ❌ Put Footer in the middle
- ❌ Have too many sections at the top
- ❌ Forget to publish your changes
- ❌ Reorder too frequently (confuses users)

---

## 🔄 Resetting Order

### **To Reset to Default:**
1. Go to **Section Ordering** in customizer
2. Manually drag sections back to default order:
   - Homepage Hero (1st)
   - New Drops Carousel (2nd)
   - Featured Products (3rd)
   - New Arrival (4th)
   - Footer (5th)
3. Click **"Publish"**

---

## 🎨 Visual Guide

### **Drag and Drop Interface:**
```
┌─────────────────────────────────┐
│ 🎯 Section Ordering             │
├─────────────────────────────────┤
│ ☰ Homepage Hero                 │
│ ☰ New Drops Carousel            │
│ ☰ Featured Products             │
│ ☰ New Arrival                   │
│ ☰ Footer                        │
└─────────────────────────────────┘
```

### **After Reordering:**
```
┌─────────────────────────────────┐
│ 🎯 Section Ordering             │
├─────────────────────────────────┤
│ ☰ New Drops Carousel            │
│ ☰ Homepage Hero                 │
│ ☰ Featured Products             │
│ ☰ New Arrival                   │
│ ☰ Footer                        │
└─────────────────────────────────┘
```

---

## 🆘 Troubleshooting

### **Sections Not Reordering:**
- Make sure you clicked **"Publish"**
- Clear any caching plugins
- Check if JavaScript is enabled

### **Customizer Menu Not Updating:**
- Refresh the customizer page
- The menu order updates after publishing

### **Homepage Not Showing New Order:**
- Clear browser cache
- Check if you published the changes
- Verify the sections are enabled

---

## 🎉 Ready to Use!

Your Section Ordering feature is now active! Go to **Appearance > Customize > Section Ordering** to start reordering your homepage sections.

**Pro Tip:** Try different orders and see which one works best for your brand and audience! 🚀
