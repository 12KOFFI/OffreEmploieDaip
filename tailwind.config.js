/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './assets/**/*.js',
        './templates/**/*.html.twig',
    ],
    safelist: [
        'badge-brouillon',
        'badge-publiee',
        'badge-retiree',
        'badge-expiree',
        'badge',
    ],
    theme: {
        extend: {
            colors: {
                navy: { DEFAULT: '#16204A', deep: '#10173A' },
                brand: {
                    purple: '#7C4DFF',
                    indigo: '#4F46E5',
                    orange: '#F7941D',
                    orangedark: '#DD7E0A',
                    green: '#16A34A',
                },
            },
            fontFamily: {
                display: ['Poppins', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(135deg, #8258FF 0%, #4A63F2 100%)',
            },
            borderRadius: { '2xl': '1rem', '3xl': '1.25rem' },
        },
    },
    plugins: [],
};
