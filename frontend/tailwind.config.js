/** @type {import('tailwindcss').Config} */
export default {
  darkMode: ['class'],
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',
        card:        { DEFAULT:'hsl(var(--card))',       foreground:'hsl(var(--card-foreground))' },
        popover:     { DEFAULT:'hsl(var(--popover))',    foreground:'hsl(var(--popover-foreground))' },
        primary:     { DEFAULT:'hsl(var(--primary))',    foreground:'hsl(var(--primary-foreground))' },
        secondary:   { DEFAULT:'hsl(var(--secondary))',  foreground:'hsl(var(--secondary-foreground))' },
        muted:       { DEFAULT:'hsl(var(--muted))',      foreground:'hsl(var(--muted-foreground))' },
        accent:      { DEFAULT:'hsl(var(--accent))',     foreground:'hsl(var(--accent-foreground))' },
        destructive: { DEFAULT:'hsl(var(--destructive))',foreground:'hsl(var(--destructive-foreground))' },
        border:      'hsl(var(--border))',
        input:       'hsl(var(--input))',
        ring:        'hsl(var(--ring))',
        // Brand
        brand: {
          50:  '#f0faf4',
          100: '#dcf2e6',
          200: '#b7e4c7',
          300: '#74c69d',
          400: '#52b788',
          500: '#2d6a4f',
          600: '#235740',
          700: '#1b4332',
          800: '#12291f',
          900: '#081510',
        },
      },
      fontFamily: {
        sans: ['DM Sans', 'sans-serif'],
        mono: ['DM Mono', 'monospace'],
      },
      borderRadius: {
        lg: 'var(--radius)',
        md: 'calc(var(--radius) - 2px)',
        sm: 'calc(var(--radius) - 4px)',
      },
    },
  },
  plugins: [],
}
