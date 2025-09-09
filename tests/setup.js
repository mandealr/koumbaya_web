import { vi } from 'vitest'
import { config } from '@vue/test-utils'

// Configuration globale pour @vue/test-utils
config.global.mocks = {
  $t: (key) => key, // Mock pour les traductions
  $route: {
    path: '/',
    name: 'home',
    params: {},
    query: {}
  },
  $router: {
    push: vi.fn(),
    replace: vi.fn(),
    go: vi.fn(),
    back: vi.fn(),
    forward: vi.fn()
  }
}

// Configuration globale des stubs
config.global.stubs = {
  'router-link': {
    template: '<a><slot /></a>',
    props: ['to']
  },
  'router-view': {
    template: '<div><slot /></div>'
  }
}

// Mock des APIs globales
global.fetch = vi.fn()
global.localStorage = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
}

// Mock de window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(), // Deprecated
    removeListener: vi.fn(), // Deprecated
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
})

// Configuration pour les tests de sécurité
globalThis.__SECURITY_TEST_MODE__ = true

// Mock pour DOMPurify
vi.mock('dompurify', () => ({
  default: {
    sanitize: vi.fn(input => input),
    isValidAttribute: vi.fn(() => true)
  }
}))

// Fonction utilitaire pour les tests de sécurité
global.createSecurityTestUtils = () => ({
  // Payloads XSS communs pour les tests
  xssPayloads: [
    '<script>alert("XSS")</script>',
    'javascript:alert("XSS")',
    '<img src=x onerror=alert("XSS")>',
    '<svg onload=alert("XSS")>',
    '<iframe src="javascript:alert(\'XSS\')">',
    '"><script>alert("XSS")</script>',
    '\'\';!--"<XSS>=&{()}',
  ],
  
  // Payloads d'injection SQL pour les tests
  sqlPayloads: [
    "'; DROP TABLE users; --",
    "' OR '1'='1",
    "' UNION SELECT * FROM users --",
    "1' OR 1=1 --",
    "admin'--",
    "admin')--",
  ],
  
  // Caractères dangereux pour les tests de validation
  dangerousChars: [
    '<', '>', '"', "'", '&', '`',
    'javascript:', 'data:', 'vbscript:',
    'onload=', 'onerror=', 'onclick=',
  ],
  
  // Headers de test pour la sécurité
  securityHeaders: [
    'X-XSS-Protection',
    'X-Content-Type-Options',
    'X-Frame-Options',
    'Content-Security-Policy',
    'Strict-Transport-Security',
    'Referrer-Policy'
  ]
})