/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./views/**/*.php",
    "./public/assets/js/**/*.js"
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Work Sans"', 'sans-serif'],
        serif: ['"Fraunces"', 'serif'],
      },
      colors: {
        primary: {
          DEFAULT: '#3C5530', // Paddy green
          hover: '#4B6B3C',
        },
        secondary: '#6C8B54',
        accent: '#DDBE7E', // Warm gold
        background: '#F7F4EE',
        surface: '#FFFFFF',
        text: {
          primary: '#2B2620',
          muted: '#6E665A',
        },
        border: '#E1D9C8',
        success: '#3E7A44',
        warning: '#C77F1E',
        error: '#B3402A',
        info: '#3E6B8A',
        // Status badge background tints
        status: {
          successBg: '#E6F0E7',
          warningBg: '#F7E9D3',
          errorBg: '#F6E1DC',
          infoBg: '#E3EDF4',
          cancelBg: '#EDEBE6',
        },
        // Disabled form state
        disabled: {
          bg: '#F2F0EA',
          text: '#A79E8E',
        }
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'], // Generic sans baseline
      }
    },
  },
  plugins: [],
}
