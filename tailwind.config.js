import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      colors: {
        brand: {
          rose:   '#F83B71', // French rose
          pink:   '#FC6A80', // Bright pink (Crayola)
          salmon: '#FC8F77', // Salmon
          sand:   '#FAC589', // Sunset
          cream:  '#FCEDCF', // Papaya whip
          // color de acción (rojo vino como en tus mockups)
          crimson:'#B21724',
        },
      },
      boxShadow: {
        soft: '0 15px 45px rgba(0,0,0,.10)',
      },
      borderRadius: {
        xl2: '1.25rem',
      }
    },
  },
  plugins: [forms],
}
