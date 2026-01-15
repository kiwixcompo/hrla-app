# Website CMS Setup Guide
## Complete Content Management System for HR Leave Assist

---

## Overview

Your website now has a full Content Management System (CMS) that allows the admin to edit:
- ✅ **Colors & Branding** - Primary, secondary, text, and background colors
- ✅ **Hero Section** - Main headline, subtitle, features, and CTA buttons
- ✅ **Features Section** - All feature titles and descriptions
- ✅ **About Section** - About page content
- ✅ **Contact Information** - Email addresses and contact details

---

## How to Access

1. **Log in as Admin:**
   - Go to: `https://www.hrleaveassist.com/login.php`
   - Email: `talk2char@gmail.com`
   - Password: `Password@123`

2. **Navigate to Site Settings:**
   - Click on **"Site Settings"** in the left sidebar
   - You'll see 5 tabs: Colors, Hero, Features, About, Contact

---

## Features

### 1. Colors & Branding Tab
- **Visual Color Pickers** - Click to choose colors
- **Hex Code Display** - See exact color codes
- **Reset Buttons** - Restore default colors
- **Live Preview** - See changes before saving
- **Auto-Apply** - Colors update across entire site

**Customizable Colors:**
- Primary Color (Blue) - Used for buttons, links, highlights
- Secondary Color (Green) - Used for success states, checkmarks
- Text Color - Main text throughout site
- Background Color - Page backgrounds

### 2. Hero Section Tab
- Edit main headline text
- Edit highlighted portion of headline
- Edit subtitle
- Edit all 4 hero features
- Edit CTA button text

**Character Counter** - Shows how long your text is

### 3. Features Section Tab
- Edit section title
- Edit all 5 feature titles
- Changes reflect immediately on homepage

### 4. About Section Tab
- Edit about section title
- Edit full about content (multi-line)
- Perfect for company description

### 5. Contact Tab
- Update contact email
- Update support email
- Changes apply to footer and contact forms

---

## How to Edit Content

### Step-by-Step:

1. **Select a Tab** - Click on the tab you want to edit (Colors, Hero, etc.)

2. **Make Your Changes:**
   - For text: Type directly in the input fields
   - For colors: Click the color box to open picker
   - For long text: Use the textarea boxes

3. **Preview (Colors Only):**
   - Click "Preview Changes" to see colors temporarily
   - No save required for preview

4. **Save Changes:**
   - Click the "Save" button at the bottom
   - You'll see a success message
   - Changes are live immediately!

5. **Refresh Homepage:**
   - Visit your homepage to see changes
   - Colors may require a page refresh (Ctrl+F5)

---

## Mobile Optimization

The mobile display has been completely optimized:

### Fixed Issues:
- ✅ Header no longer cuts off text
- ✅ Logo properly sized for mobile
- ✅ Hero content appears before visual card
- ✅ Proper spacing and padding
- ✅ Responsive font sizes
- ✅ Full-width buttons that stack vertically
- ✅ Touch-friendly interface

### Mobile-Specific Features:
- Smaller, optimized logo (35px height)
- Reduced font sizes for readability
- Better spacing between elements
- Centered layout for better UX
- Proper margin to account for fixed header

---

## Technical Details

### Database Structure

New table: `site_settings`
- Stores all customizable content
- Organized by category
- Tracks who made changes and when
- Supports multiple data types (text, color, email, etc.)

### Files Created:

1. **config/site-settings.php** - Core CMS functions
2. **admin/site-settings-section.php** - Admin interface
3. **api/site-settings.php** - API for saving settings
4. **assets/css/custom.php** - Dynamic CSS generator

### How It Works:

1. **Admin edits content** → Saved to database
2. **Frontend loads** → Reads from database
3. **CSS generated** → Colors applied dynamically
4. **Cache cleared** → Changes visible immediately

---

## Adding New Editable Fields

To add more editable content:

1. **Add to database:**
   ```php
   ['key' => 'new_field', 'value' => 'Default text', 'type' => 'text', 
    'category' => 'hero', 'label' => 'New Field Label', 'order' => 10]
   ```

2. **Use in frontend:**
   ```php
   <?php echo getSiteSetting('new_field', 'Default'); ?>
   ```

3. **It automatically appears in admin panel!**

---

## Best Practices

### Content Guidelines:
- **Headlines:** Keep under 60 characters for mobile
- **Features:** Be concise, 1-2 sentences max
- **Colors:** Test contrast for accessibility
- **Emails:** Use valid email format

### Color Tips:
- **Primary Color:** Should be bold and eye-catching
- **Secondary Color:** Should complement primary
- **Text Color:** Must be readable on background
- **Test on Mobile:** Always check mobile view

### Workflow:
1. Make changes in admin panel
2. Preview if available
3. Save changes
4. Test on actual website
5. Check mobile view
6. Adjust if needed

---

## Troubleshooting

### Changes Not Showing:
- **Clear browser cache:** Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
- **Check you saved:** Look for success message
- **Refresh page:** Sometimes needs a hard refresh

### Colors Not Applying:
- **Save first:** Must click "Save Colors"
- **Clear cache:** Browser may cache old CSS
- **Check hex codes:** Must be valid format (#RRGGBB)

### Text Too Long:
- **Check character count:** Shown below each field
- **Test on mobile:** Long text may wrap oddly
- **Be concise:** Shorter is usually better

### Can't Access Admin:
- **Check login:** Must be logged in as admin
- **Check permissions:** Only admins can edit
- **Clear cookies:** Try logging out and back in

---

## Future Enhancements

Easily add more editable sections:
- ✨ Pricing plans and amounts
- ✨ FAQ questions and answers
- ✨ Footer text and links
- ✨ Social media links
- ✨ Images and logos
- ✨ Meta descriptions for SEO
- ✨ Custom CSS snippets
- ✨ JavaScript tracking codes

---

## Security

- ✅ **CSRF Protection:** All forms protected
- ✅ **Admin Only:** Only admins can edit
- ✅ **Input Validation:** All inputs sanitized
- ✅ **Audit Trail:** Changes logged with user ID
- ✅ **Database Escaping:** SQL injection prevented

---

## Support

If you need help:
1. Check this guide first
2. Test in different browser
3. Clear cache and cookies
4. Check browser console for errors
5. Review application logs

---

## Quick Reference

### Access CMS:
```
URL: https://www.hrleaveassist.com/admin/index.php
Section: Site Settings (in sidebar)
```

### Get Setting Value (in code):
```php
<?php echo getSiteSetting('hero_title', 'Default Title'); ?>
```

### Update Setting (via admin):
```
Admin Panel → Site Settings → Select Tab → Edit → Save
```

### Apply Custom Colors:
```html
<link rel="stylesheet" href="assets/css/custom.php">
```

---

**Your website is now fully customizable without touching code!** 🎉
