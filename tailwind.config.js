import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    dark: '#4a3222',
                    DEFAULT: '#6f4e2f',
                    light: '#f4ede3',
                },
                accent: {
                    DEFAULT: '#a8652b',
                    dark: '#8a501f',
                },
            },
        },
    },

    plugins: [forms],
};
