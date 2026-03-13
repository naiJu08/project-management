/** @type {import('tailwindcss').Config} */
const colors = require('tailwindcss/colors')

module.exports = {
    content: [
        './resources/**/*.blade.php',
        './app/Filament/**/*.php',
        './app/Http/Livewire/**/*.php',
        './vendor/filament/**/*.blade.php',
        './node_modules/flowbite/**/*.js'
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                danger: colors.rose,
                primary: colors.blue,
                success: colors.green,
                warning: colors.yellow,
                accent: colors.cyan,
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Arial', 'sans-serif'],
            },
            fontSize: {
                'xs': ['0.65rem', { lineHeight: '1.2' }],
                'sm': ['0.75rem', { lineHeight: '1.3' }],
                'base': ['0.813rem', { lineHeight: '1.3' }],
                'lg': ['0.938rem', { lineHeight: '1.4' }],
                'xl': ['1.063rem', { lineHeight: '1.4' }],
                '2xl': ['1.188rem', { lineHeight: '1.4' }],
                '3xl': ['1.375rem', { lineHeight: '1.3' }],
            },
            spacing: {
                'compact-xs': '0.25rem',
                'compact-sm': '0.375rem',
                'compact': '0.5rem',
                'compact-md': '0.625rem',
                'compact-lg': '0.75rem',
            },
            lineHeight: {
                'compact': '1.3',
                'compact-relaxed': '1.4',
            },
            padding: {
                'compact': '0.5rem',
            },
            margin: {
                'compact': '0.5rem',
            },
            gap: {
                'compact': '0.5rem',
            },
            boxShadow: {
                'sm': '0 1px 2px 0 rgb(0 0 0 / 0.05)',
                DEFAULT: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
                'md': '0 2px 4px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
                neon: '0 0 0 1px rgb(34 211 238 / 0.3), 0 2px 8px -2px rgb(34 211 238 / 0.6), 0 0 24px -6px rgb(34 211 238 / 0.7)',
            },
            backgroundImage: {
                'ai-gradient': 'radial-gradient(1200px circle at 20% 10%, rgba(59,130,246,0.15), transparent 40%), radial-gradient(800px circle at 80% 20%, rgba(34,211,238,0.12), transparent 35%), radial-gradient(1000px circle at 50% 90%, rgba(168,85,247,0.10), transparent 45%)',
            },
            keyframes: {
                'pulse-glow': {
                    '0%, 100%': { boxShadow: '0 0 0 0 rgba(34,211,238,0.4)' },
                    '50%': { boxShadow: '0 0 0 6px rgba(34,211,238,0.0)' },
                },
            },
            animation: {
                'pulse-glow': 'pulse-glow 2.5s ease-in-out infinite',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('flowbite/plugin')
    ],
}
