/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./include/*.php",
    "./assets/js/*.js"
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075985',
          900: '#0c4a6e',
        },
      },
      boxShadow: {
        'glossy': '0 8px 32px 0 rgba(31, 38, 135, 0.37)',
        'glossy-sm': '0 4px 16px 0 rgba(31, 38, 135, 0.25)',
      },
      backdropBlur: {
        xs: '2px',
      }
    },
  },
  plugins: [],
}
