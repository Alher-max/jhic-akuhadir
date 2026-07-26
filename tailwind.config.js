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
                'brand-bg': '#FBF9F9',
                'brand-surface': '#FFFFFF',
                'brand-border': '#EAE2E3',
                'brand-primary': '#B81D24',
                'brand-text-main': '#1A1516',
                'brand-text-muted': '#6B5E60'
            }
        },
    },

    plugins: [forms],
};
