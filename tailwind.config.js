/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './admin/src/**/*.{js,jsx,ts,tsx}',
    './front/src/**/*.{js,jsx,ts,tsx}',
    './includes/**/*.php',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
  corePlugins: {
    preflight: false, // Disable Tailwind's reset to avoid conflicts with WordPress admin styles
  },
}