import js from '@eslint/js'
import vue from 'eslint-plugin-vue'
import security from 'eslint-plugin-security'

export default [
  js.configs.recommended,
  ...vue.configs['flat/essential'],
  {
    plugins: {
      security
    },
    rules: {
      // Règles de sécurité générales
      'security/detect-object-injection': 'error',
      'security/detect-non-literal-regexp': 'warn',
      'security/detect-unsafe-regex': 'error',
      'security/detect-buffer-noassert': 'error',
      'security/detect-child-process': 'error',
      'security/detect-disable-mustache-escape': 'error',
      'security/detect-eval-with-expression': 'error',
      'security/detect-no-csrf-before-method-override': 'error',
      'security/detect-non-literal-fs-filename': 'warn',
      'security/detect-non-literal-require': 'warn',
      'security/detect-possible-timing-attacks': 'warn',
      'security/detect-pseudoRandomBytes': 'error',
      
      // Règles Vue.js spécifiques à la sécurité
      'vue/no-v-html': 'error', // Prévient XSS via v-html
      'vue/no-v-text-v-html-on-component': 'error',
      'vue/no-unescaped-entities': 'error',
      
      // Règles JavaScript générales pour la sécurité
      'no-eval': 'error',
      'no-implied-eval': 'error',
      'no-new-func': 'error',
      'no-script-url': 'error',
      'no-restricted-globals': ['error', 'eval', 'execScript'],
      
      // Validation des entrées utilisateur
      'no-unused-vars': 'warn',
      'no-console': 'warn',
      'prefer-const': 'error',
      
      // Règles personnalisées pour Koumbaya
      'no-restricted-syntax': [
        'error',
        {
          selector: 'CallExpression[callee.property.name="innerHTML"]',
          message: 'Utiliser textContent ou des méthodes sécurisées au lieu de innerHTML'
        },
        {
          selector: 'AssignmentExpression[left.property.name="innerHTML"]',
          message: 'Utiliser textContent ou des méthodes sécurisées au lieu de innerHTML'
        }
      ]
    },
    languageOptions: {
      ecmaVersion: 2023,
      sourceType: 'module',
      globals: {
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        process: 'readonly',
        global: 'readonly',
        __dirname: 'readonly',
        __filename: 'readonly',
        Buffer: 'readonly',
        require: 'readonly',
        module: 'readonly',
        exports: 'readonly'
      }
    }
  },
  {
    files: ['**/*.vue'],
    languageOptions: {
      parserOptions: {
        parser: '@babel/eslint-parser'
      }
    }
  },
  {
    ignores: [
      'node_modules/**',
      'vendor/**',
      'storage/**',
      'bootstrap/**',
      'public/build/**',
      'public/hot',
      '*.min.js',
      'coverage/**'
    ]
  }
]