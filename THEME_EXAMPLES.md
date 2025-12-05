# Theme Examples - Semantic Color System

This file demonstrates how easy it is to create different themes using the new semantic color system.

## Current Active Theme (Dark Violet - Almost Black) ✨

```javascript
// In tailwind.config.js - CURRENTLY ACTIVE
colors: {
    'primary': colors.violet,   // 🟣 VIOLET primary colors
    'secondary': colors.slate,  // ⚫ SLATE text/borders for contrast
    'surface': {
        // Light shades remain slate
        50: colors.slate[50],
        100: colors.slate[100],
        200: colors.slate[200],
        300: colors.slate[300],
        400: colors.slate[400],
        500: colors.slate[500],
        600: colors.slate[600],
        // Dark shades are ALMOST BLACK
        700: '#1a1a1a',  // Very dark gray
        800: '#0f0f0f',  // Almost black  
        900: '#050505',  // Nearly black
    },
    'success': colors.emerald,  // 🟢 EMERALD success states
    'warning': colors.amber,    // 🟡 AMBER warnings
    'error': colors.rose,       // 🔴 ROSE error states
    'info': colors.cyan,        // 🔵 CYAN info states
}
```

## Previous Default Theme (Sky Blue)

```javascript
// In tailwind.config.js
colors: {
    'primary': colors.sky,
    'secondary': colors.gray,
    'surface': colors.gray,
    'success': colors.green,
    'warning': colors.yellow,
    'error': colors.red,
    'info': colors.cyan,
}
```

## Purple Theme

```javascript
// In tailwind.config.js
colors: {
    'primary': colors.purple,
    'secondary': colors.slate,
    'surface': colors.slate,
    'success': colors.emerald,
    'warning': colors.amber,
    'error': colors.rose,
    'info': colors.blue,
}
```

## Green Nature Theme

```javascript
// In tailwind.config.js
colors: {
    'primary': colors.emerald,
    'secondary': colors.gray,
    'surface': colors.stone,
    'success': colors.green,
    'warning': colors.orange,
    'error': colors.red,
    'info': colors.teal,
}
```

## Dark Theme with Warm Backgrounds

```javascript
// In tailwind.config.js
colors: {
    'primary': colors.orange,
    'secondary': colors.neutral,
    'surface': {
        50: colors.orange[50],
        100: colors.orange[100],
        200: colors.orange[200],
        300: colors.neutral[300],
        400: colors.neutral[400],
        500: colors.neutral[500],
        600: colors.neutral[600],
        700: colors.neutral[700],
        800: colors.neutral[800],
        900: colors.neutral[900]
    },
    'success': colors.lime,
    'warning': colors.yellow,
    'error': colors.red,
    'info': colors.cyan,
}
```

## Professional Blue Theme

```javascript
// In tailwind.config.js
colors: {
    'primary': colors.blue,
    'secondary': colors.slate,
    'surface': colors.slate,
    'success': colors.green,
    'warning': colors.yellow,
    'error': colors.red,
    'info': colors.sky,
}
```

## High Contrast Theme

```javascript
// In tailwind.config.js
colors: {
    'primary': colors.indigo,
    'secondary': colors.gray,
    'surface': {
        50: colors.white,
        100: colors.gray[50],
        200: colors.gray[100],
        300: colors.gray[200],
        400: colors.gray[300],
        500: colors.gray[400],
        600: colors.gray[500],
        700: colors.gray[600],
        800: colors.black,
        900: colors.black
    },
    'success': colors.green,
    'warning': colors.yellow,
    'error': colors.red,
    'info': colors.blue,
}
```

## How to Switch Themes

1. Open `tailwind.config.js`
2. Update the color mappings in the `colors` object
3. Run your build process to regenerate the CSS
4. All components automatically use the new theme!

## Custom Color Definitions

You can also define completely custom colors:

```javascript
colors: {
    'primary': {
        50: '#eff6ff',
        100: '#dbeafe',
        200: '#bfdbfe',
        300: '#93c5fd',
        400: '#60a5fa',
        500: '#3b82f6',  // Your brand color
        600: '#2563eb',
        700: '#1d4ed8',
        800: '#1e40af',
        900: '#1e3a8a',
    },
    // ... other semantic colors
}
```

## Theme-Specific Customizations

For advanced theming, you can even conditionally apply different colors based on data attributes or classes:

```css
/* In your CSS file */
[data-theme="dark"] .custom-component {
    @apply bg-surface-900 text-foreground;
}

[data-theme="light"] .custom-component {
    @apply bg-surface-50 text-foreground;
}
```

This semantic color system makes it incredibly easy to maintain consistent theming across your entire application! 