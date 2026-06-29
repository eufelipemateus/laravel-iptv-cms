import js from '@eslint/js';

export default [
    {
        ignores: ['bootstrap/**', 'node_modules/**', 'public/**', 'storage/**', 'vendor/**'],
    },
    {
        files: ['resources/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                console: 'readonly',
                document: 'readonly',
                module: 'readonly',
                require: 'readonly',
                window: 'readonly',
            },
        },
        rules: js.configs.recommended.rules,
    },
];
