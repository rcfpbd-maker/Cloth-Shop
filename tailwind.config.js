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
            backgroundImage: {
                'helsinki-dark': 'linear-gradient(135deg, #0f172a, #1e293b)',
                'vibrant-indigo': 'linear-gradient(130deg, #5c6ac4, #7f74c0)',
                'success-emerald': 'linear-gradient(135deg, #2ecc71, #27ae60)',
                'indigo-emerald': 'linear-gradient(145deg, #5c6ac4, #2ecc71)',
            },
        },
    },

    plugins: [forms],
};
