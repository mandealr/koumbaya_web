import { ref, computed } from 'vue'
import securityService from '../services/security.js'

/**
 * Composable pour la sécurité dans les composants Vue
 * Fournit des méthodes réactives pour la validation et la sécurisation des données
 */
export function useSecurity() {
  const securityWarnings = ref([])
  const isSecurityValidating = ref(false)
  const securityErrors = ref({})

  /**
   * État de sécurité global
   */
  const securityState = computed(() => ({
    hasWarnings: securityWarnings.value.length > 0,
    hasErrors: Object.keys(securityErrors.value).length > 0,
    isValidating: isSecurityValidating.value,
    isSecure: !securityWarnings.value.length && !Object.keys(securityErrors.value).length
  }))

  /**
   * Valider un formulaire complet avec règles de sécurité
   * @param {Object} formData - Données du formulaire
   * @param {Object} validationRules - Règles de validation
   * @returns {Promise<Object>} Résultat de la validation
   */
  const validateForm = async (formData, validationRules = {}) => {
    isSecurityValidating.value = true
    securityErrors.value = {}
    securityWarnings.value = []

    try {
      const result = securityService.validateFormData(formData, validationRules)
      
      if (!result.isValid) {
        securityErrors.value = result.errors
        
        // Logger l'événement de sécurité
        securityService.logSecurityEvent('FORM_VALIDATION_FAILED', {
          errors: result.errors,
          formFields: Object.keys(formData)
        })
      }

      return {
        isValid: result.isValid,
        sanitized: result.sanitized,
        errors: result.errors,
        hasSecurityIssues: !result.isValid
      }
    } finally {
      isSecurityValidating.value = false
    }
  }

  /**
   * Valider une entrée utilisateur spécifique
   * @param {string} fieldName - Nom du champ
   * @param {*} value - Valeur à valider
   * @param {Object} options - Options de validation
   * @returns {Object} Résultat de la validation
   */
  const validateField = (fieldName, value, options = {}) => {
    const validation = securityService.validateInput(value)
    
    if (!validation.isValid) {
      securityErrors.value = {
        ...securityErrors.value,
        [fieldName]: options.errorMessage || 'Contenu non autorisé détecté'
      }
      
      // Logger la tentative d'attaque
      securityService.logSecurityEvent('FIELD_VALIDATION_FAILED', {
        field: fieldName,
        threats: validation.threats,
        value: value?.substring(0, 100) // Limiter la longueur pour les logs
      })
    } else {
      // Supprimer l'erreur si elle existe
      const { [fieldName]: _, ...remainingErrors } = securityErrors.value
      securityErrors.value = remainingErrors
    }

    return {
      isValid: validation.isValid,
      sanitized: validation.sanitized,
      threats: validation.threats
    }
  }

  /**
   * Nettoyer le HTML de manière sécurisée
   * @param {string} html - HTML à nettoyer
   * @param {Object} options - Options de nettoyage
   * @returns {string} HTML nettoyé
   */
  const sanitizeHtml = (html, options = {}) => {
    return securityService.sanitizeHtml(html, options)
  }

  /**
   * Valider une URL
   * @param {string} url - URL à valider
   * @returns {boolean} true si l'URL est sûre
   */
  const validateUrl = (url) => {
    const isValid = securityService.isValidUrl(url)
    
    if (!isValid) {
      securityService.logSecurityEvent('DANGEROUS_URL_BLOCKED', {
        url: url,
        timestamp: Date.now()
      })
    }
    
    return isValid
  }

  /**
   * Créer un validateur réactif pour un champ
   * @param {string} fieldName - Nom du champ
   * @param {Object} rules - Règles de validation
   * @returns {Object} Validateur réactif
   */
  const createFieldValidator = (fieldName, rules = {}) => {
    const fieldError = computed(() => securityErrors.value[fieldName])
    const hasError = computed(() => !!fieldError.value)

    const validate = (value) => {
      return validateField(fieldName, value, rules)
    }

    const clearError = () => {
      const { [fieldName]: _, ...remainingErrors } = securityErrors.value
      securityErrors.value = remainingErrors
    }

    return {
      error: fieldError,
      hasError,
      validate,
      clearError
    }
  }

  /**
   * Vérifier les headers de sécurité de la réponse
   * @param {Response} response - Réponse HTTP
   */
  const validateResponseSecurity = (response) => {
    const securityStatus = securityService.validateSecurityHeaders(response)
    
    if (!securityStatus.isSecure) {
      securityWarnings.value.push({
        type: 'SECURITY_HEADERS',
        message: 'Headers de sécurité manquants ou incorrects',
        details: securityStatus
      })
    }

    return securityStatus
  }

  /**
   * Middleware pour les requêtes API
   * @param {Object} config - Configuration de la requête
   * @returns {Object} Configuration modifiée
   */
  const secureApiRequest = (config) => {
    // Ajouter le token CSRF
    config.headers = {
      ...config.headers,
      'X-CSRF-TOKEN': securityService.generateCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest'
    }

    // Valider les données envoyées
    if (config.data && typeof config.data === 'object') {
      const validation = securityService.validateFormData(config.data)
      if (!validation.isValid) {
        throw new Error('Request blocked: security validation failed')
      }
      config.data = validation.sanitized
    }

    return config
  }

  /**
   * Intercepteur pour les réponses API
   * @param {Response} response - Réponse de l'API
   * @returns {Response} Réponse validée
   */
  const validateApiResponse = (response) => {
    // Valider les headers de sécurité
    validateResponseSecurity(response)

    // Si la réponse contient du HTML, la nettoyer
    if (response.headers.get('content-type')?.includes('text/html')) {
      response.data = securityService.sanitizeHtml(response.data)
    }

    return response
  }

  /**
   * Fonction utilitaire pour encoder les entités HTML
   * @param {string} text - Texte à encoder
   * @returns {string} Texte encodé
   */
  const escapeHtml = (text) => {
    return securityService.escapeHtml(text)
  }

  /**
   * Vérifier si une chaîne contient des caractères dangereux
   * @param {string} input - Chaîne à vérifier
   * @returns {boolean} true si la chaîne est dangereuse
   */
  const containsThreat = (input) => {
    const validation = securityService.validateInput(input)
    return !validation.isValid
  }

  /**
   * Nettoyer toutes les erreurs de sécurité
   */
  const clearSecurityErrors = () => {
    securityErrors.value = {}
    securityWarnings.value = []
  }

  /**
   * Obtenir un résumé de la sécurité
   */
  const getSecuritySummary = computed(() => {
    const errorCount = Object.keys(securityErrors.value).length
    const warningCount = securityWarnings.value.length
    
    return {
      errors: errorCount,
      warnings: warningCount,
      total: errorCount + warningCount,
      level: errorCount > 0 ? 'error' : warningCount > 0 ? 'warning' : 'safe'
    }
  })

  /**
   * Règles de validation prédéfinies pour Koumbaya
   */
  const koumbayaValidationRules = {
    email: { 
      type: 'email',
      xssMessage: 'Format d\'email invalide ou contenu non autorisé'
    },
    phone: { 
      type: 'phone',
      xssMessage: 'Format de téléphone invalide'
    },
    price: { 
      type: 'price',
      xssMessage: 'Prix invalide'
    },
    productName: {
      type: 'text',
      xssMessage: 'Nom de produit invalide ou contenu non autorisé'
    },
    description: {
      type: 'text',
      xssMessage: 'Description invalide ou contenu non autorisé'
    }
  }

  /**
   * Créer un ensemble complet de validateurs pour un formulaire Koumbaya
   * @param {Array} fields - Liste des champs à valider
   * @returns {Object} Validateurs pour chaque champ
   */
  const createKoumbayaValidators = (fields) => {
    const validators = {}
    
    fields.forEach(field => {
      const rules = koumbayaValidationRules[field] || { type: 'text' }
      validators[field] = createFieldValidator(field, rules)
    })
    
    return validators
  }

  return {
    // État réactif
    securityState,
    securityWarnings,
    securityErrors,
    isSecurityValidating,
    
    // Méthodes de validation
    validateForm,
    validateField,
    validateUrl,
    validateResponseSecurity,
    
    // Utilitaires de sécurité
    sanitizeHtml,
    escapeHtml,
    containsThreat,
    
    // Validateurs réactifs
    createFieldValidator,
    createKoumbayaValidators,
    
    // Middleware API
    secureApiRequest,
    validateApiResponse,
    
    // Gestion d'état
    clearSecurityErrors,
    getSecuritySummary,
    
    // Règles prédéfinies
    koumbayaValidationRules
  }
}