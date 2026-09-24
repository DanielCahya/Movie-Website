/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'brand': {
          DEFAULT: '#06b6d4', // Cyan accent
          dark: '#0f766e',    // Teal button
          bg: '#0f172a',      // Dark slate background
          card: '#1e293b',    // Slightly lighter card
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
