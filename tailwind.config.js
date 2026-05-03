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
                brand: ['Libre Baskerville', 'Georgia', 'Times New Roman', 'serif'],
            },
            colors: {
                bf: {
                    brand: 'var(--bf-brand)',
                    rust: 'var(--bf-rust)',
                    'rust-deep': 'var(--bf-rust-deep)',
                    red: 'var(--bf-red)',
                    mahogany: 'var(--bf-mahogany)',
                    crimson: 'var(--bf-crimson)',
                    orange: 'var(--bf-orange)',
                    sun: 'var(--bf-sun)',
                    gold: 'var(--bf-gold)',
                    cream: 'var(--bf-cream)',
                    'cream-muted': 'var(--bf-cream-muted)',
                    ink: 'var(--bf-ink)',
                    muted: 'var(--bf-muted)',
                },
            },
        },
    },

    plugins: [require("daisyui"), forms],
};
