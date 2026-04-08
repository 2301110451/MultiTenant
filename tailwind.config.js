import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                },
                neutral: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                },
                surface: {
                    DEFAULT: '#ffffff',
                    soft: '#f8fafc',
                    muted: '#f1f5f9',
                },
                border: {
                    DEFAULT: '#e2e8f0',
                    soft: '#e5e7eb',
                },
            },
            borderRadius: {
                lg: '0.625rem',
                xl: '0.875rem',
            },
            boxShadow: {
                soft: '0 1px 2px 0 rgb(15 23 42 / 0.06), 0 6px 18px -8px rgb(15 23 42 / 0.10)',
                md: '0 10px 24px -12px rgb(15 23 42 / 0.18)',
            },
            spacing: {
                4.5: '1.125rem',
                18: '4.5rem',
                22: '5.5rem',
            },
        },
    },

    plugins: [forms],
};
