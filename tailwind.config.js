import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                success: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                },
                warning: {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    200: '#fde68a',
                    300: '#fcd34d',
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                    700: '#b45309',
                    800: '#92400e',
                    900: '#78350f',
                },
                danger: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'fade-out': 'fadeOut 0.5s ease-in-out',
                'slide-in': 'slideIn 0.3s ease-out',
                'slide-out': 'slideOut 0.3s ease-in',
                'bounce-in': 'bounceIn 0.6s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'wiggle': 'wiggle 1s ease-in-out infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeOut: {
                    '0%': { opacity: '1' },
                    '100%': { opacity: '0' },
                },
                slideIn: {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(0)' },
                },
                slideOut: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-100%)' },
                },
                bounceIn: {
                    '0%, 20%, 40%, 60%, 80%': {
                        transform: 'translateY(0)',
                        animationTimingFunction: 'cubic-bezier(0.215, 0.61, 0.355, 1)',
                    },
                    '40%': {
                        transform: 'translateY(-30px) scaleY(1.1)',
                        animationTimingFunction: 'cubic-bezier(0.755, 0.05, 0.855, 0.06)',
                    },
                    '60%': {
                        transform: 'translateY(-15px) scaleY(1.05)',
                        animationTimingFunction: 'cubic-bezier(0.215, 0.61, 0.355, 1)',
                    },
                    '80%': {
                        transform: 'translateY(0) scaleY(0.95)',
                        animationTimingFunction: 'cubic-bezier(0.215, 0.61, 0.355, 1)',
                    },
                    '100%': {
                        transform: 'translateY(0) scaleY(1)',
                        animationTimingFunction: 'cubic-bezier(0.215, 0.61, 0.355, 1)',
                    },
                },
                wiggle: {
                    '0%, 100%': { transform: 'rotate(-3deg)' },
                    '50%': { transform: 'rotate(3deg)' },
                },
            },
            boxShadow: {
                'soft': '0 2px 15px 0 rgba(0, 0, 0, 0.1)',
                'medium': '0 4px 25px 0 rgba(0, 0, 0, 0.15)',
                'hard': '0 10px 40px 0 rgba(0, 0, 0, 0.2)',
            },
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
                '128': '32rem',
            },
            borderRadius: {
                'xl': '1rem',
                '2xl': '1.5rem',
                '3xl': '2rem',
            },
            backdropBlur: {
                xs: '2px',
            },
        },
    },

    plugins: [
        forms,
        function({ addUtilities }) {
            const newUtilities = {
                '.glass': {
                    'background': 'rgba(255, 255, 255, 0.25)',
                    'backdrop-filter': 'blur(10px)',
                    'border': '1px solid rgba(255, 255, 255, 0.18)',
                },
                '.glass-dark': {
                    'background': 'rgba(0, 0, 0, 0.25)',
                    'backdrop-filter': 'blur(10px)',
                    'border': '1px solid rgba(255, 255, 255, 0.18)',
                },
                '.text-shadow': {
                    'text-shadow': '0 2px 4px rgba(0, 0, 0, 0.1)',
                },
                '.text-shadow-lg': {
                    'text-shadow': '0 4px 8px rgba(0, 0, 0, 0.2)',
                },
            }
            addUtilities(newUtilities)
        }
    ],
};
