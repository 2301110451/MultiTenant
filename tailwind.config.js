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
                    50: '#EEF2FF',
                    100: '#E0E7FF',
                    200: '#C7D2FE',
                    300: '#A5B4FC',
                    400: '#818CF8',
                    500: '#6366F1',
                    600: '#4F46E5',
                    700: '#4338CA',
                    800: '#3730A3',
                    900: '#312E81',
                    950: '#1E1B4B',
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
                    950: '#020617',
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
                canvas:  'var(--bg-primary)',
                elevated: 'var(--bg-elevated)',
                inset:   'var(--bg-secondary)',
                accent: {
                    DEFAULT: 'var(--tenant-accent)',
                    soft:    'var(--tenant-accent-soft)',
                },
            },

            borderColor: {
                subtle: 'var(--border-subtle)',
                muted:  'var(--border-muted)',
            },

            boxShadow: {
                soft: '0 1px 2px 0 rgb(15 23 42 / 0.04), 0 4px 12px -4px rgb(15 23 42 / 0.08)',
                md:   '0 8px 20px -8px rgb(15 23 42 / 0.14)',
                lg:   '0 12px 32px -8px rgb(15 23 42 / 0.18)',
                card:     'var(--shadow-card)',
                elevated: 'var(--shadow-elevated)',
                overlay:  'var(--shadow-overlay)',
                glow: '0 0 40px -8px rgb(99 102 241 / 0.30)',
                'glow-lg': '0 0 60px -12px rgb(99 102 241 / 0.40)',
            },

            borderRadius: {
                lg: '0.625rem',
                xl: '0.875rem',
                '2xl': '1rem',
                '3xl': '1.25rem',
            },

            spacing: {
                4.5: '1.125rem',
                18:  '4.5rem',
                22:  '5.5rem',
            },

            animation: {
                'fade-in': 'fadeIn 0.4s ease both',
                'slide-up': 'slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) both',
                'scale-in': 'scaleIn 0.2s ease both',
                'pulse-soft': 'pulseSoft 3s ease-in-out infinite',
            },

            keyframes: {
                fadeIn: {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                slideUp: {
                    from: { opacity: '0', transform: 'translateY(16px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                scaleIn: {
                    from: { opacity: '0', transform: 'scale(0.95)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                pulseSoft: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.6' },
                },
            },

            backdropBlur: {
                xs: '2px',
            },
        },
    },

    plugins: [forms],
};
