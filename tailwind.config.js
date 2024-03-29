/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'selector',
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
    ],
    theme: {
        extend: {
        colors: {
            'appoly-red': '#d92128',
        },
    },
    },
    plugins: [],
}