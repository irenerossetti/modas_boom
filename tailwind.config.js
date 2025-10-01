import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
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
                // Paleta de Neutros / Fondos
                'boom-cream': {
                    100: '#FFFCF8',
                    200: '#FEF7F3',
                    300: '#FEF5F2',
                    400: '#FDF3EF',
                    500: '#FDF2ED',
                    600: '#FEECE8',
                    700: '#FAE8E3',
                },
                'boom-rose': {
                    light: '#F5D4CE',
                    dark: '#E0CBC7',
                },
                // Paleta de Primarios / Acentos
                'boom-red': {
                    title: '#A20418',
                    report: '#A61123',
                    completed: '#A81526',
                    processing: '#C66047',
                },
                // Paleta de Texto
                'boom-text': {
                    dark: '#322725',
                    medium: '#4F4543',
                    light: '#6F6562',
                },
            },
        },
    },

    plugins: [forms],
    
};
