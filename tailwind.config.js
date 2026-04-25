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
                    50:  '#EFF6FF',
                    100: '#DBEAFE',
                    200: '#BFDBFE',
                    300: '#93C5FD',
                    400: '#60A5FA',
                    500: '#3B82F6',
                    600: '#2563EB',
                    700: '#1D4ED8',
                    800: '#1E40AF',
                    900: '#1E3A8E',
                    950: '#172554',
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
                /* Semantic status palettes */
                success: {
                    DEFAULT: '#059669',
                    light:   '#D1FAE5',
                    dark:    '#065F46',
                },
                warning: {
                    DEFAULT: '#D97706',
                    light:   '#FEF3C7',
                    dark:    '#92400E',
                },
                error: {
                    DEFAULT: '#DC2626',
                    light:   '#FEE2E2',
                    dark:    '#7F1D1D',
                },
                gold: {
                    DEFAULT: '#CA8A04',
                    light:   '#FEF9C3',
                    dark:    '#A16207',
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
