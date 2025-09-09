import { describe, it, expect, vi, beforeEach } from 'vitest'
import securityService from '../../../resources/js/services/security.js'

describe('SecurityService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('sanitizeHtml', () => {
    it('should remove script tags', () => {
      const input = '<script>alert("XSS")</script>Hello World'
      const result = securityService.sanitizeHtml(input)
      expect(result).not.toContain('<script>')
      expect(result).toContain('Hello World')
    })

    it('should remove dangerous attributes', () => {
      const input = '<div onclick="alert(\'XSS\')">Click me</div>'
      const result = securityService.sanitizeHtml(input)
      expect(result).not.toContain('onclick')
      expect(result).toContain('Click me')
    })

    it('should preserve safe HTML tags', () => {
      const input = '<p><strong>Bold text</strong></p>'
      const result = securityService.sanitizeHtml(input)
      expect(result).toContain('<strong>')
      expect(result).toContain('Bold text')
    })

    it('should handle empty or null input', () => {
      expect(securityService.sanitizeHtml('')).toBe('')
      expect(securityService.sanitizeHtml(null)).toBe('')
      expect(securityService.sanitizeHtml(undefined)).toBe('')
    })

    it('should respect custom configuration', () => {
      const input = '<b>Bold</b><i>Italic</i>'
      const result = securityService.sanitizeHtml(input, {
        allowedTags: ['b'],
        config: { FORBID_TAGS: ['i'] }
      })
      expect(result).toContain('<b>')
      expect(result).not.toContain('<i>')
    })
  })

  describe('isValidUrl', () => {
    it('should accept valid HTTP URLs', () => {
      expect(securityService.isValidUrl('https://example.com')).toBe(true)
      expect(securityService.isValidUrl('http://example.com')).toBe(true)
      expect(securityService.isValidUrl('https://sub.example.com/path?query=1')).toBe(true)
    })

    it('should accept valid relative URLs', () => {
      expect(securityService.isValidUrl('/path/to/page')).toBe(true)
      expect(securityService.isValidUrl('./relative-path')).toBe(true)
      expect(securityService.isValidUrl('../parent-path')).toBe(true)
    })

    it('should accept mailto URLs', () => {
      expect(securityService.isValidUrl('mailto:contact@example.com')).toBe(true)
    })

    it('should reject dangerous protocols', () => {
      expect(securityService.isValidUrl('javascript:alert("XSS")')).toBe(false)
      expect(securityService.isValidUrl('data:text/html,<script>alert("XSS")</script>')).toBe(false)
      expect(securityService.isValidUrl('vbscript:msgbox("XSS")')).toBe(false)
      expect(securityService.isValidUrl('file:///etc/passwd')).toBe(false)
    })

    it('should reject URLs with dangerous characters', () => {
      expect(securityService.isValidUrl('/path<script>')).toBe(false)
      expect(securityService.isValidUrl('/path"onclick')).toBe(false)
      expect(securityService.isValidUrl('/path\'alert')).toBe(false)
    })

    it('should handle invalid input', () => {
      expect(securityService.isValidUrl('')).toBe(false)
      expect(securityService.isValidUrl(null)).toBe(false)
      expect(securityService.isValidUrl(undefined)).toBe(false)
      expect(securityService.isValidUrl(123)).toBe(false)
    })
  })

  describe('validateInput', () => {
    it('should detect XSS patterns', () => {
      const xssInputs = [
        '<script>alert("XSS")</script>',
        'javascript:alert("XSS")',
        '<img src=x onerror=alert("XSS")>',
        '<svg onload=alert("XSS")>',
        '<iframe src="javascript:alert(\'XSS\')">',
      ]

      xssInputs.forEach(input => {
        const result = securityService.validateInput(input)
        expect(result.isValid).toBe(false)
        expect(result.threats).toContain('XSS_DETECTED')
      })
    })

    it('should allow safe content', () => {
      const safeInputs = [
        'Hello World',
        'Contact us at contact@example.com',
        'Price: 25.50 FCFA',
        'Visit https://example.com',
        'Call +241 01 02 03 04',
      ]

      safeInputs.forEach(input => {
        const result = securityService.validateInput(input)
        expect(result.isValid).toBe(true)
        expect(result.threats).toHaveLength(0)
      })
    })

    it('should sanitize content while validating', () => {
      const input = '<p>Safe content</p><script>alert("XSS")</script>'
      const result = securityService.validateInput(input)
      expect(result.sanitized).not.toContain('<script>')
      expect(result.sanitized).toContain('Safe content')
    })
  })

  describe('validateFormData', () => {
    it('should validate all form fields', () => {
      const formData = {
        name: 'John Doe',
        email: 'john@example.com',
        message: 'Hello <script>alert("XSS")</script>',
        phone: '+241 01 02 03 04'
      }

      const rules = {
        email: { type: 'email' },
        phone: { type: 'phone' },
        message: { xssMessage: 'Message contains forbidden content' }
      }

      const result = securityService.validateFormData(formData, rules)
      expect(result.isValid).toBe(false)
      expect(result.errors.message).toBe('Message contains forbidden content')
      expect(result.sanitized.name).toBe('John Doe')
    })

    it('should validate email format', () => {
      const formData = { email: 'invalid-email' }
      const rules = { email: { type: 'email' } }
      
      const result = securityService.validateFormData(formData, rules)
      expect(result.isValid).toBe(false)
      expect(result.errors.email).toContain('invalide')
    })

    it('should validate phone format', () => {
      const formData = { phone: 'abc123' }
      const rules = { phone: { type: 'phone' } }
      
      const result = securityService.validateFormData(formData, rules)
      expect(result.isValid).toBe(false)
      expect(result.errors.phone).toContain('invalide')
    })

    it('should validate price range', () => {
      const formData = { 
        validPrice: '100.50',
        invalidPrice: '-50',
        excessivePrice: '999999999'
      }
      const rules = {
        validPrice: { type: 'price' },
        invalidPrice: { type: 'price' },
        excessivePrice: { type: 'price' }
      }
      
      const result = securityService.validateFormData(formData, rules)
      expect(result.errors.validPrice).toBeUndefined()
      expect(result.errors.invalidPrice).toBeDefined()
      expect(result.errors.excessivePrice).toBeDefined()
    })
  })

  describe('escapeHtml', () => {
    it('should escape dangerous HTML characters', () => {
      const input = '<script>alert("XSS")</script>'
      const result = securityService.escapeHtml(input)
      expect(result).toBe('&lt;script&gt;alert("XSS")&lt;/script&gt;')
    })

    it('should escape quotes and ampersands', () => {
      const input = 'Company & "Partners" \\'name\\''
      const result = securityService.escapeHtml(input)
      expect(result).toContain('&amp;')
      expect(result).toContain('&quot;')
    })

    it('should handle non-string input', () => {
      expect(securityService.escapeHtml(null)).toBe(null)
      expect(securityService.escapeHtml(undefined)).toBe(undefined)
      expect(securityService.escapeHtml(123)).toBe(123)
    })
  })

  describe('generateCsrfToken', () => {
    it('should generate unique tokens', () => {
      const token1 = securityService.generateCsrfToken()
      const token2 = securityService.generateCsrfToken()
      
      expect(token1).toBeDefined()
      expect(token2).toBeDefined()
      expect(token1).not.toBe(token2)
    })

    it('should generate base64 encoded tokens', () => {
      const token = securityService.generateCsrfToken()
      expect(() => atob(token)).not.toThrow()
    })
  })

  describe('validateSecurityHeaders', () => {
    it('should validate required security headers', () => {
      const mockResponse = {
        headers: {
          get: vi.fn((header) => {
            const headers = {
              'x-content-type-options': 'nosniff',
              'x-frame-options': 'DENY',
              'x-xss-protection': '1; mode=block',
              'content-security-policy': 'default-src \\'self\\'',
              'referrer-policy': 'strict-origin'
            }
            return headers[header.toLowerCase()]
          })
        }
      }

      const result = securityService.validateSecurityHeaders(mockResponse)
      expect(result.isSecure).toBe(true)
      expect(result.missingHeaders).toHaveLength(0)
    })

    it('should detect missing security headers', () => {
      const mockResponse = {
        headers: {
          get: vi.fn(() => null)
        }
      }

      const result = securityService.validateSecurityHeaders(mockResponse)
      expect(result.isSecure).toBe(false)
      expect(result.missingHeaders.length).toBeGreaterThan(0)
    })
  })

  describe('logSecurityEvent', () => {
    beforeEach(() => {
      // Mock console et fetch pour les tests
      vi.spyOn(console, 'warn').mockImplementation(() => {})
      global.fetch = vi.fn()
    })

    it('should log to console in development', () => {
      const originalEnv = process.env.NODE_ENV
      process.env.NODE_ENV = 'development'

      securityService.logSecurityEvent('XSS_ATTEMPT', { field: 'message' })
      
      expect(console.warn).toHaveBeenCalledWith(
        '🛡️ Security Event:', 
        'XSS_ATTEMPT', 
        { field: 'message' }
      )

      process.env.NODE_ENV = originalEnv
    })

    it('should send alert in production', async () => {
      const originalEnv = process.env.NODE_ENV
      process.env.NODE_ENV = 'production'

      await securityService.logSecurityEvent('XSS_ATTEMPT', { field: 'message' })
      
      expect(fetch).toHaveBeenCalledWith('/api/security/alert', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: expect.stringContaining('XSS_ATTEMPT')
      })

      process.env.NODE_ENV = originalEnv
    })
  })

  describe('Security Test Utilities', () => {
    it('should provide XSS test payloads', () => {
      const utils = global.createSecurityTestUtils()
      expect(utils.xssPayloads).toBeInstanceOf(Array)
      expect(utils.xssPayloads.length).toBeGreaterThan(0)
      expect(utils.xssPayloads).toContain('<script>alert("XSS")</script>')
    })

    it('should provide SQL injection test payloads', () => {
      const utils = global.createSecurityTestUtils()
      expect(utils.sqlPayloads).toBeInstanceOf(Array)
      expect(utils.sqlPayloads.length).toBeGreaterThan(0)
      expect(utils.sqlPayloads.some(payload => payload.includes('DROP TABLE'))).toBe(true)
    })

    it('should provide security headers list', () => {
      const utils = global.createSecurityTestUtils()
      expect(utils.securityHeaders).toContain('X-XSS-Protection')
      expect(utils.securityHeaders).toContain('Content-Security-Policy')
    })
  })
})