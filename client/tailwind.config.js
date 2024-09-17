/** @type {import('tailwindcss').Config} */

export default {
    content: [
        "./src/**/*.{vue,scss}",
    ],
    theme: {
        extend: {
            colors: {
                'primary-900': '#00222b',
                'primary-800': '#004455',
                'primary-700': '#006680',
                'primary-600': '#0088aa',
                'primary-500': '#00aad4',
                'primary-400': '#33aacf',
                'primary-300': '#2ad4ff',
                'primary-200': '#55ddff',
                'primary-100': '#aaeeff',
                'primary-50': '#d5f6ff',
            }
        }
    },
    plugins: [],
}