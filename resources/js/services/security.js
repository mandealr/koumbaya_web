import DOMPurify from 'dompurify'

/**
 * Service de sécurité pour l'application Koumbaya
 * Contient les utilitaires de validation, sanitisation et protection contre les attaques
 */
class SecurityService {
  constructor() {
    this.initDOMPurify()
  }

  /**
   * Configuration de DOMPurify
   */
  initDOMPurify() {
    // Configuration sécurisée de DOMPurify
    DOMPurify.addHook('beforeSanitizeElements', (node) => {
      // Bloquer tous les éléments script
      if (node.nodeName && node.nodeName.match(/script|iframe|object|embed|form/i)) {
        return node.parentNode?.removeChild(node)
      }
    })

    // Ajouter des attributs autorisés spécifiques à Koumbaya
    DOMPurify.addHook('afterSanitizeAttributes', (node) => {
      // Nettoyer les attributs href
      if (node.hasAttribute('href')) {
        const href = node.getAttribute('href')
        if (href && !this.isValidUrl(href)) {
          node.removeAttribute('href')
        }
      }
    })
  }

  /**
   * Nettoyer le contenu HTML de manière sécurisée
   * @param {string} html - HTML à nettoyer
   * @param {Object} options - Options de configuration
   * @returns {string} HTML nettoyé
   */
  sanitizeHtml(html, options = {}) {
    if (!html || typeof html !== 'string') return ''

    const config = {
      ALLOWED_TAGS: options.allowedTags || ['b', 'i', 'em', 'strong', 'p', 'br', 'span'],
      ALLOWED_ATTR: options.allowedAttributes || ['class', 'id'],
      FORBID_TAGS: ['script', 'iframe', 'object', 'embed', 'form', 'input'],
      FORBID_ATTR: ['onclick', 'onload', 'onerror', 'onmouseover', 'onmouseout', 'onfocus', 'onblur'],
      ...options.config
    }

    return DOMPurify.sanitize(html, config)
  }

  /**
   * Valider une URL
   * @param {string} url - URL à valider
   * @returns {boolean} true si l'URL est valide et sûre
   */
  isValidUrl(url) {
    if (!url || typeof url !== 'string') return false

    // Bloquer les protocoles dangereux
    const dangerousProtocols = /^(javascript|data|vbscript|file|ftp):/i
    if (dangerousProtocols.test(url)) return false

    // Valider le format d'URL
    try {
      const urlObj = new URL(url, window.location.origin)
      return ['http:', 'https:', 'mailto:'].includes(urlObj.protocol)
    } catch {
      // Si ce n'est pas une URL absolue, vérifier les chemins relatifs
      return /^[/.]/.test(url) && !/[<>\"'`]/.test(url)
    }
  }

  /**
   * Valider les entrées utilisateur contre les attaques XSS
   * @param {string} input - Entrée utilisateur
   * @returns {Object} Résultat de la validation
   */
  validateInput(input) {
    if (!input || typeof input !== 'string') {
      return { isValid: true, sanitized: input || '' }
    }

    // Détecter les patterns XSS dangereux
    const xssPatterns = [
      /<script[^>]*>.*?<\/script>/gi,
      /javascript:/gi,
      /on\w+\s*=/gi,
      /<iframe[^>]*>/gi,
      /<object[^>]*>/gi,
      /<embed[^>]*>/gi,
      /vbscript:/gi,
      /data:.*base64/gi
    ]

    let hasXss = false
    for (const pattern of xssPatterns) {
      if (pattern.test(input)) {
        hasXss = true
        break
      }
    }

    return {
      isValid: !hasXss,
      sanitized: this.sanitizeHtml(input),
      threats: hasXss ? ['XSS_DETECTED'] : []
    }
  }

  /**
   * Valider les données de formulaire
   * @param {Object} formData - Données du formulaire
   * @param {Object} rules - Règles de validation
   * @returns {Object} Résultat de la validation
   */
  validateFormData(formData, rules = {}) {
    const errors = {}
    const sanitized = {}
    let isValid = true

    for (const [field, value] of Object.entries(formData)) {
      const fieldRules = rules[field] || {}
      
      // Validation de base
      const validation = this.validateInput(value)
      if (!validation.isValid) {
        errors[field] = fieldRules.xssMessage || 'Contenu non autorisé détecté'
        isValid = false
      }

      // Validation spécifique par type
      if (fieldRules.type) {
        const typeValidation = this.validateByType(value, fieldRules.type)
        if (!typeValidation.isValid) {
          errors[field] = typeValidation.message
          isValid = false
        }
      }

      sanitized[field] = validation.sanitized
    }

    return { isValid, errors, sanitized }
  }

  /**
   * Validation par type de données
   * @param {*} value - Valeur à valider
   * @param {string} type - Type de validation
   * @returns {Object} Résultat de la validation
   */
  validateByType(value, type) {
    switch (type) {
      case 'email':
        return this.validateEmail(value)
      case 'phone':
        return this.validatePhone(value)
      case 'price':
        return this.validatePrice(value)
      case 'text':
        return this.validateText(value)
      default:
        return { isValid: true }
    }
  }

  /**
   * Valider une adresse email
   */
  validateEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
    const isValid = emailRegex.test(email) && email.length <= 255
    return {
      isValid,
      message: isValid ? '' : 'Format d\'email invalide'
    }
  }

  /**
   * Valider un numéro de téléphone
   */
  validatePhone(phone) {
    const phoneRegex = /^\+?[\d\s\-()]{8,15}$/
    const isValid = phoneRegex.test(phone)
    return {
      isValid,
      message: isValid ? '' : 'Format de téléphone invalide'
    }
  }

  /**
   * Valider un prix
   */
  validatePrice(price) {
    const numPrice = parseFloat(price)
    const isValid = !isNaN(numPrice) && numPrice >= 0 && numPrice <= 10000000
    return {
      isValid,
      message: isValid ? '' : 'Prix invalide (doit être entre 0 et 10,000,000)'
    }
  }

  /**
   * Valider un texte générique
   */
  validateText(text) {
    const isValid = text && text.length <= 10000 && typeof text === 'string'
    return {
      isValid,
      message: isValid ? '' : 'Texte invalide (maximum 10,000 caractères)'
    }
  }

  /**
   * Encoder les entités HTML pour prévenir XSS
   * @param {string} text - Texte à encoder
   * @returns {string} Texte encodé
   */
  escapeHtml(text) {
    if (!text || typeof text !== 'string') return text
    
    const div = document.createElement('div')
    div.textContent = text
    return div.innerHTML
  }

  /**
   * Générer/récupérer un token CSRF
   * @returns {string} Token CSRF
   */
  generateCsrfToken() {
    // Pour Laravel, récupérer le token CSRF depuis le meta tag
    const metaToken = document.querySelector('meta[name="csrf-token"]')
    if (metaToken) {
      return metaToken.getAttribute('content')
    }

    // Récupérer depuis les cookies (XSRF-TOKEN)
    const cookies = document.cookie.split(';')
    for (let cookie of cookies) {
      const [name, value] = cookie.trim().split('=')
      if (name === 'XSRF-TOKEN') {
        return decodeURIComponent(value)
      }
    }

    // Récupérer depuis le sessionStorage
    const sessionToken = sessionStorage.getItem('csrf_token')
    if (sessionToken) {
      return sessionToken
    }

    // Si aucun token n'est trouvé, faire une requête pour en obtenir un
    this.refreshCsrfToken()
    
    return metaToken?.getAttribute('content') || ''
  }

  /**
   * Rafraîchir le token CSRF
   */
  async refreshCsrfToken() {
    try {
      const response = await fetch('/csrf-token', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        
        // Mettre à jour le meta tag
        let metaToken = document.querySelector('meta[name="csrf-token"]')
        if (!metaToken) {
          metaToken = document.createElement('meta')
          metaToken.name = 'csrf-token'
          document.head.appendChild(metaToken)
        }
        metaToken.setAttribute('content', data.token)
        
        // Stocker dans le sessionStorage
        sessionStorage.setItem('csrf_token', data.token)
        
        return data.token
      }
    } catch (error) {
      console.warn('Impossible de rafraîchir le token CSRF:', error)
    }
    
    return null
  }

  /**
   * Valider le token CSRF côté client
   * @param {string} token Token à valider
   * @returns {boolean} true si le token est valide
   */
  validateCsrfToken(token) {
    const currentToken = this.generateCsrfToken()
    return currentToken && token && currentToken === token
  }

  /**
   * Valider les headers de sécurité de la réponse
   * @param {Response} response - Réponse HTTP
   * @returns {Object} État de la sécurité
   */
  validateSecurityHeaders(response) {
    const requiredHeaders = {
      'x-content-type-options': 'nosniff',
      'x-frame-options': 'DENY',
      'x-xss-protection': '1; mode=block',
      'content-security-policy': true, // Doit être présent
      'referrer-policy': true
    }

    const securityStatus = {
      isSecure: true,
      missingHeaders: [],
      warnings: []
    }

    for (const [header, expectedValue] of Object.entries(requiredHeaders)) {
      const actualValue = response.headers.get(header)
      
      if (!actualValue) {
        securityStatus.missingHeaders.push(header)
        securityStatus.isSecure = false
      } else if (expectedValue !== true && actualValue !== expectedValue) {
        securityStatus.warnings.push(`${header}: ${actualValue}`)
      }
    }

    return securityStatus
  }

  /**
   * Logger les événements de sécurité
   * @param {string} event - Type d'événement
   * @param {Object} data - Données de l'événement
   */
  logSecurityEvent(event, data = {}) {
    if (process.env.NODE_ENV === 'development') {
      console.warn('🛡️ Security Event:', event, data)
    }

    // En production, envoyer vers un service de monitoring
    if (process.env.NODE_ENV === 'production') {
      this.sendSecurityAlert(event, data)
    }
  }

  /**
   * Envoyer une alerte de sécurité
   * @param {string} event - Type d'événement
   * @param {Object} data - Données
   */
  async sendSecurityAlert(event, data) {
    try {
      await fetch('/api/security/alert', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          event,
          data,
          timestamp: Date.now(),
          userAgent: navigator.userAgent,
          url: window.location.href
        })
      })
    } catch (error) {
      console.error('Failed to send security alert:', error)
    }
  }
}

// Instance singleton
const securityService = new SecurityService()

export default securityService