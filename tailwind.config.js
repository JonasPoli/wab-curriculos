/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'selector',
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
    ],
    safelist: [
        'nav-link',
        'nav-link--active',
    ],
    theme: {
        fontFamily: {
            'body': ['Inter', 'sans-serif'],
            'sans': ['Inter', 'sans-serif'],
        },
        extend: {
            colors: {
                // ── Brand Primary ─────────────────────────────────
                'primary': {
                    DEFAULT: '#0769a1',
                    light:   '#1679b1',
                    dark:    '#055a87',
                    50:  '#eff7ff',
                    100: '#dbeffe',
                    200: '#bfe3fd',
                    300: '#93d0fc',
                    400: '#60b5f8',
                    500: '#3b96f3',
                    600: '#2578e7',
                    700: '#1d62d4',
                    800: '#1e4fab',
                    900: '#1e4487',
                },

                // ── Semantic Colors ───────────────────────────────
                'success': {
                    DEFAULT: '#059669',
                    light:   '#10b981',
                    dark:    '#047857',
                },
                'danger': {
                    DEFAULT: '#dc2626',
                    light:   '#ef4444',
                    dark:    '#b91c1c',
                },
                'warning': {
                    DEFAULT: '#d97706',
                    light:   '#f59e0b',
                    dark:    '#b45309',
                },
                'info': {
                    DEFAULT: '#0891b2',
                    light:   '#06b6d4',
                    dark:    '#0e7490',
                },
            },

            spacing: {
                '55vw': '55vw',
            },

            container: {
                center: true,
                padding: {
                    DEFAULT: '1rem',
                    sm: '2rem',
                },
                screens: {
                    sm:  '600px',
                    md:  '728px',
                    lg:  '984px',
                    xl:  '1240px',
                    '2xl': '1240px',
                },
            },

            backdropBlur: {
                xs: '2px',
            },

            borderOpacity: {
                '8': '0.08',
            },

            backgroundOpacity: {
                '3': '0.03',
                '8': '0.08',
            },

            animation: {
                'slide-down': 'slideDown 0.25s ease forwards',
                'fade-in':    'fadeIn 0.3s ease forwards',
            },

            keyframes: {
                slideDown: {
                    '0%':   { opacity: '0', transform: 'translateY(-8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                fadeIn: {
                    '0%':   { opacity: '0' },
                    '100%': { opacity: '1' },
                },
            },
        },
    },
    plugins: [
        // require('@tailwindcss/forms'),   // Uncomment if needed for pub forms
        // require('@tailwindcss/typography'), // Uncomment for blog/rich text content
    ],
}
