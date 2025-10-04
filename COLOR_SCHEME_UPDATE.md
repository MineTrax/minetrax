# Color Scheme Update - New Tailwind Theming

This document outlines the comprehensive theming system in the MineTrax project using modern CSS custom properties and semantic color tokens.

## 🎨 **NEW TAILWIND THEMING SYSTEM**

The project now uses a modern theming approach based on **CSS custom properties (CSS variables)** with HSL color values, inspired by shadcn/ui. This provides superior flexibility, type-safety, and maintainability compared to the old approach.

---

## Architecture Overview

### 1. Three-Layer System

#### Layer 1: CSS Variables (app.css)
**File:** `resources/default/css/app.css`

Defines HSL color values as CSS custom properties:
```css
:root {
    --primary: 217.2193 91.2195% 59.8039%;
    --primary-foreground: 0 0% 100%;
    --background: 220 13% 91%;
    --foreground: 0 0% 20%;
    /* ... more variables */
}

.dark {
    --primary: 217.2193 91.2195% 59.8039%;
    --primary-foreground: 0 0% 100%;
    --background: 0 0% 9.0196%;
    --foreground: 0 0% 89.8039%;
    /* ... dark mode overrides */
}
```

#### Layer 2: Tailwind Config (tailwind.config.js)
**File:** `tailwind.config.js`

Maps CSS variables to Tailwind color classes:
```javascript
colors: {
    border: "hsl(var(--border))",
    background: "hsl(var(--background))",
    foreground: "hsl(var(--foreground))",
    primary: {
        DEFAULT: "hsl(var(--primary))",
        foreground: "hsl(var(--primary-foreground))",
    },
    // ... more color definitions
}
```

#### Layer 3: Component Usage
Use semantic Tailwind classes in your components:
```html
<div class="bg-background text-foreground">
    <button class="bg-primary text-primary-foreground">Click me</button>
    <div class="bg-card text-card-foreground border border-border">
        Card content
    </div>
</div>
```

---

## Semantic Color Tokens

### Core Tokens

| Token | Purpose | Usage Example |
|-------|---------|---------------|
| `background` | Main page background | `bg-background` |
| `foreground` | Main text color | `text-foreground` |
| `border` | Default border color | `border` or `border-border` |
| `input` | Input field borders | `border-input` |
| `ring` | Focus ring color | `ring` or `ring-ring` |

### Component Tokens

Each component token comes with a `DEFAULT` and `foreground` variant for accessible color pairings:

| Token | Purpose | Usage Example |
|-------|---------|---------------|
| `primary` | Primary brand color, main CTAs | `bg-primary text-primary-foreground` |
| `secondary` | Secondary actions, less prominent elements | `bg-secondary text-secondary-foreground` |
| `destructive` | Destructive actions (delete, remove) | `bg-destructive text-destructive-foreground` |
| `muted` | Muted backgrounds and text | `bg-muted text-muted-foreground` |
| `accent` | Highlighted content, hover states | `bg-accent text-accent-foreground` |
| `popover` | Popover and dropdown backgrounds | `bg-popover text-popover-foreground` |
| `card` | Card component backgrounds | `bg-card text-card-foreground` |
| `sidebar` | Sidebar backgrounds | `bg-sidebar text-sidebar-foreground` |

### Chart Colors

Pre-configured colors for data visualization:

```html
<div class="bg-chart-1">Chart series 1</div>
<div class="bg-chart-2">Chart series 2</div>
<div class="bg-chart-3">Chart series 3</div>
<div class="bg-chart-4">Chart series 4</div>
<div class="bg-chart-5">Chart series 5</div>
```

---

## Design System Features

### 1. Automatic Color Pairing

Every component token has a foreground variant for guaranteed accessibility:

```html
<!-- ✅ Good: Automatic contrast -->
<button class="bg-primary text-primary-foreground">Submit</button>
<div class="bg-card text-card-foreground">Content</div>

<!-- ❌ Avoid: Manual color pairing -->
<button class="bg-primary text-white">Submit</button>
```

### 2. Border Radius System

Dynamic border radius using CSS variables:

```javascript
borderRadius: {
    lg: "var(--radius)",           // Base radius (0.375rem)
    md: "calc(var(--radius) - 2px)",
    sm: "calc(var(--radius) - 4px)",
    xs: "calc(var(--radius) - 6px)",
    xl: "calc(var(--radius) + 4px)",
}
```

Usage:
```html
<div class="rounded-lg">Base radius</div>
<div class="rounded-md">Slightly smaller</div>
<div class="rounded-xl">Slightly larger</div>
```

### 3. Shadow System

Custom shadows defined via CSS variables:

```html
<div class="shadow-2xs">Minimal shadow</div>
<div class="shadow-sm">Small shadow</div>
<div class="shadow">Default shadow</div>
<div class="shadow-lg">Large shadow</div>
<div class="shadow-2xl">Extra large shadow</div>
```

### 4. Typography Integration

Typography plugin configured to use semantic colors:

```javascript
typography: (theme) => ({
    dark: {
        css: {
            color: theme("colors.foreground"),
            a: {
                color: theme("colors.primary.DEFAULT"),
            },
            h1: { color: theme("colors.foreground") },
            // ... more elements
        },
    },
}),
```

---

## Usage Guidelines

### ✅ Best Practices

1. **Use semantic tokens based on purpose, not appearance:**
   ```html
   <!-- Good -->
   <button class="bg-destructive text-destructive-foreground">Delete</button>
   
   <!-- Bad -->
   <button class="bg-red-500 text-white">Delete</button>
   ```

2. **Always pair color with its foreground variant:**
   ```html
   <!-- Good -->
   <div class="bg-primary text-primary-foreground">Content</div>
   
   <!-- Bad -->
   <div class="bg-primary text-white">Content</div>
   ```

3. **Use `background` and `foreground` for main page areas:**
   ```html
   <body class="bg-background text-foreground">
   ```

4. **Use `card` for contained content:**
   ```html
   <div class="bg-card text-card-foreground border rounded-lg p-6">
       Card content
   </div>
   ```

5. **Use `muted` for less important information:**
   ```html
   <p class="text-muted-foreground">Supporting text</p>
   <div class="bg-muted text-muted-foreground p-4">Secondary content</div>
   ```

### ❌ Avoid

1. **Don't use hardcoded colors:**
   ```html
   <!-- Bad -->
   <div class="bg-blue-500 text-gray-900">
   ```

2. **Don't mix old and new color systems:**
   ```html
   <!-- Bad -->
   <button class="bg-primary text-surface-100">
   ```

3. **Don't use numbered shades for semantic tokens:**
   ```html
   <!-- Bad -->
   <div class="bg-primary-500">  <!-- primary doesn't have shades -->
   
   <!-- Good -->
   <div class="bg-primary">
   ```

---

## Dark Mode

Dark mode is handled automatically through CSS variable overrides in the `.dark` class selector. Simply add the `dark` class to the root element:

```html
<html class="dark">
```

All components will automatically adapt to dark mode without code changes:

```html
<!-- This works in both light and dark mode -->
<div class="bg-card text-card-foreground">
    Auto-adapting card
</div>
```

---

## Creating a New Theme

### Step 1: Define Colors in CSS

Edit `resources/default/css/app.css`:

```css
:root {
    /* Light mode colors */
    --primary: 280 100% 70%;        /* Purple primary */
    --primary-foreground: 0 0% 100%;
    --background: 0 0% 100%;
    --foreground: 0 0% 10%;
    /* ... define all variables */
}

.dark {
    /* Dark mode colors */
    --primary: 280 100% 70%;        /* Same or different */
    --primary-foreground: 0 0% 100%;
    --background: 0 0% 5%;
    --foreground: 0 0% 90%;
    /* ... define all variables */
}
```

### Step 2: Test All Components

The changes will automatically apply to all components. Test:
- Buttons and interactive elements
- Cards and containers
- Forms and inputs
- Navigation and sidebars
- Dark mode appearance

### HSL Color Format

Colors use HSL (Hue, Saturation, Lightness) without the `hsl()` wrapper:

```css
/* ✅ Correct */
--primary: 217.2193 91.2195% 59.8039%;

/* ❌ Wrong */
--primary: hsl(217.2193, 91.2195%, 59.8039%);
```

The `hsl()` wrapper is added in the Tailwind config.

---

## Complete Variable Reference

### Required CSS Variables (app.css)

#### Light Mode (`:root`)
```css
--background: 220 13% 91%;
--foreground: 0 0% 20%;
--card: 0 0% 100%;
--card-foreground: 0 0% 20%;
--popover: 0 0% 100%;
--popover-foreground: 0 0% 20%;
--primary: 217.2193 91.2195% 59.8039%;
--primary-foreground: 0 0% 100%;
--secondary: 220 14.2857% 95.8824%;
--secondary-foreground: 215 13.7931% 34.1176%;
--muted: 210 20% 98.0392%;
--muted-foreground: 220 8.9362% 46.0784%;
--accent: 204 93.75% 93.7255%;
--accent-foreground: 224.4444 64.2857% 32.9412%;
--destructive: 0 84.2365% 60.1961%;
--destructive-foreground: 0 0% 100%;
--border: 220 13.0435% 90.9804%;
--input: 220 13.0435% 90.9804%;
--ring: 217.2193 91.2195% 59.8039%;
--chart-1: 217.2193 91.2195% 59.8039%;
--chart-2: 221.2121 83.1933% 53.3333%;
--chart-3: 224.2781 76.3265% 48.0392%;
--chart-4: 225.931 70.7317% 40.1961%;
--chart-5: 224.4444 64.2857% 32.9412%;
--sidebar: 210 20% 98.0392%;
--sidebar-foreground: 0 0% 20%;
--radius: 0.375rem;
```

#### Dark Mode (`.dark`)
```css
--background: 0 0% 9.0196%;
--foreground: 0 0% 89.8039%;
--card: 0 0% 14.902%;
--card-foreground: 0 0% 89.8039%;
--popover: 0 0% 14.902%;
--popover-foreground: 0 0% 89.8039%;
--primary: 217.2193 91.2195% 59.8039%;
--primary-foreground: 0 0% 100%;
--secondary: 0 0% 14.902%;
--secondary-foreground: 0 0% 89.8039%;
--muted: 0 0% 14.902%;
--muted-foreground: 0 0% 63.9216%;
--accent: 224.4444 64.2857% 32.9412%;
--accent-foreground: 213.3333 96.9231% 87.2549%;
--destructive: 0 84.2365% 60.1961%;
--destructive-foreground: 0 0% 100%;
--border: 0 0% 25.098%;
--input: 0 0% 25.098%;
--ring: 217.2193 91.2195% 59.8039%;
--chart-1: 213.1169 93.9024% 67.8431%;
--chart-2: 217.2193 91.2195% 59.8039%;
--chart-3: 221.2121 83.1933% 53.3333%;
--chart-4: 224.2781 76.3265% 48.0392%;
--chart-5: 225.931 70.7317% 40.1961%;
--sidebar: 0 0% 11.96%;
--sidebar-foreground: 0 0% 89.8039%;
```

---

## Backward Compatibility

### Old Color System (Deprecated)

The following colors are kept for backward compatibility but should **not be used** in new code:

```javascript
// ⚠️ DEPRECATED - Do not use in new code
success: colors.green,    // Use 'destructive' with appropriate styling
warning: colors.yellow,   // Use 'accent' or 'muted' appropriately
error: colors.red,        // Use 'destructive'
info: colors.cyan,        // Use 'primary' or 'accent'
surface: colors.gray,     // Use 'background', 'card', or 'muted'
```

### Migration from Old System

| Old Class | New Class |
|-----------|-----------|
| `bg-surface-50` | `bg-background` or `bg-card` |
| `text-secondary-400` | `text-foreground` or `text-muted-foreground` |
| `border-secondary-200` | `border` or `border-border` |
| `bg-primary-500` | `bg-primary` |
| `text-primary-500` | `text-primary` |
| `bg-error-500` | `bg-destructive` |
| `text-error-500` | `text-destructive` |

---

## Benefits of New System

1. **Type-Safe Color Pairing**: Every color has a guaranteed foreground for accessibility
2. **Single Source of Truth**: Change theme by editing CSS variables only
3. **Automatic Dark Mode**: Dark mode handled entirely through CSS
4. **Better DX**: Semantic names make component intent clear
5. **Flexibility**: Easy to create unlimited themes
6. **Performance**: CSS variables are browser-native and fast
7. **Maintainability**: No need to search/replace color values across components
8. **Framework Agnostic**: CSS variables work with any framework or vanilla JS

---

## Common Patterns

### Buttons

```html
<!-- Primary button -->
<button class="bg-primary text-primary-foreground hover:bg-primary/90">
    Primary Action
</button>

<!-- Secondary button -->
<button class="bg-secondary text-secondary-foreground hover:bg-secondary/80">
    Secondary Action
</button>

<!-- Destructive button -->
<button class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
    Delete
</button>

<!-- Ghost button -->
<button class="hover:bg-accent hover:text-accent-foreground">
    Ghost
</button>
```

### Cards

```html
<div class="bg-card text-card-foreground border rounded-lg shadow-lg p-6">
    <h2 class="text-foreground font-bold">Card Title</h2>
    <p class="text-muted-foreground">Card description</p>
</div>
```

### Forms

```html
<input 
    type="text" 
    class="bg-background text-foreground border-input focus:ring-ring rounded-md"
    placeholder="Enter text..."
/>
```

### Status Indicators

```html
<!-- Success -->
<div class="bg-accent text-accent-foreground">Success message</div>

<!-- Error -->
<div class="bg-destructive text-destructive-foreground">Error message</div>

<!-- Info -->
<div class="bg-muted text-muted-foreground">Info message</div>
```

---

## Resources

- **Primary Configuration**: `tailwind.config.js` (lines 35-92)
- **CSS Variables**: `resources/default/css/app.css` (lines 300-392)
- **Typography Theme**: `tailwind.config.js` (lines 121-164)
- **Component Examples**: `THEME_EXAMPLES.md`

---

## Summary

The new Tailwind theming system provides a modern, maintainable approach to color management in MineTrax:

✅ **CSS custom properties** for easy theme switching  
✅ **Semantic color tokens** based on purpose, not appearance  
✅ **Automatic color pairing** for accessibility  
✅ **Native dark mode support** via CSS variables  
✅ **Complete design system** with borders, shadows, and radius  
✅ **Backward compatibility** with gradual migration path  

This architecture ensures that MineTrax can support multiple themes, maintain consistent design, and provide an excellent user experience across all color schemes and modes.
