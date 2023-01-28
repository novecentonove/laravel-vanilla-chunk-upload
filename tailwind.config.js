const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        screens: {
            'sm': '576px',
            // => @media (min-width: 576px) { ... }
      
            'md': '960px',
            // => @media (min-width: 960px) { ... }
      
            'lg': '1200px',
            // => @media (min-width: 1440px) { ... }
          },
        extend: {
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
