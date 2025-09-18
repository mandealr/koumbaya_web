import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/composables/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref('')
  const initializing = ref(true) // Nouvel état pour gérer l'initialisation

  // Getters
  const isAuthenticated = computed(() => !initializing.value && !!token.value && !!user.value)
  
  // Role helper function
  const hasRole = (roleName) => {
    if (!user.value?.roles || !Array.isArray(user.value.roles)) {
      return false
    }
    
    // Support pour les deux formats: tableau de strings ou tableau d'objets avec property 'name'
    return user.value.roles.some(role => {
      if (typeof role === 'string') {
        return role === roleName
      } else if (role && typeof role === 'object' && role.name) {
        return role.name === roleName
      }
      return false
    })
  }
  
  const isManager = computed(() => {
    return hasRole('Super Admin') || hasRole('Admin') || hasRole('Agent')
  })
  
  const isAdmin = computed(() => {
    return hasRole('Super Admin') || hasRole('Admin')
  })
  
  const isAgent = computed(() => {
    return hasRole('Agent')
  })
  
  const isMerchant = computed(() => {
    return hasRole('Business Enterprise') || hasRole('Business Individual') || hasRole('Business')
  })
  
  const isCustomer = computed(() => {
    return hasRole('Particulier')
  })
  
  const canSell = computed(() => {
    return hasRole('Business Enterprise') || hasRole('Business Individual') || hasRole('Business')
  })

  // Actions
  async function login(credentials) {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.post('/auth/login', credentials)
      
      // Debug: Log de la structure complète de la réponse
      console.log('🔍 Structure complète de la réponse API login:', response.data)
      
      // Gérer différentes structures de réponse API
      let authToken, userData
      
      if (response.data.data) {
        // Structure avec wrapper data
        authToken = response.data.data.access_token || response.data.data.token
        userData = response.data.data.user
        console.log('📦 Structure avec wrapper data détectée')
      } else {
        // Structure directe
        authToken = response.data.access_token || response.data.token
        userData = response.data.user
        console.log('📦 Structure directe détectée')
      }
      
      console.log('🎫 Token extrait:', authToken ? 'présent' : 'absent')
      console.log('👤 User data extrait:', userData)
      
      if (!authToken) {
        throw new Error('Token d\'authentification manquant dans la réponse')
      }
      
      if (!userData) {
        throw new Error('Données utilisateur manquantes dans la réponse')
      }
      
      token.value = authToken
      user.value = userData
      
      localStorage.setItem('auth_token', authToken)
      
      console.log('✅ Login store: Token et user définis avec succès')
      
      return { success: true }
    } catch (err) {
      // Gestion d'erreurs spécifique pour le login
      let errorMessage = 'Erreur de connexion'
      
      if (err.response?.status === 401) {
        errorMessage = 'Identifiants incorrects'
      } else if (err.response?.status === 429) {
        errorMessage = 'Trop de tentatives. Veuillez patienter.'
      } else if (err.response?.status === 422) {
        // Erreurs de validation
        const validationErrors = err.response?.data?.errors
        if (validationErrors) {
          errorMessage = Object.values(validationErrors).flat().join(', ')
        } else {
          errorMessage = err.response?.data?.message || 'Données invalides'
        }
      } else if (!err.response) {
        errorMessage = 'Problème de connexion réseau'
      } else {
        errorMessage = err.response?.data?.error || err.response?.data?.message || 'Erreur de connexion'
      }
      
      error.value = errorMessage
      return { success: false, error: errorMessage }
    } finally {
      loading.value = false
    }
  }

  async function register(userData) {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.post('/auth/register', userData)
      
      // Debug: Log de la structure complète de la réponse
      console.log('🔍 Structure complète de la réponse API register:', response.data)
      
      // Gérer différentes structures de réponse API
      let authToken, newUser
      
      if (response.data.data) {
        // Structure avec wrapper data
        authToken = response.data.data.access_token || response.data.data.token
        newUser = response.data.data.user
        console.log('📦 Register: Structure avec wrapper data détectée')
      } else {
        // Structure directe
        authToken = response.data.access_token || response.data.token
        newUser = response.data.user
        console.log('📦 Register: Structure directe détectée')
      }
      
      token.value = authToken
      user.value = newUser
      
      localStorage.setItem('auth_token', authToken)
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.error || err.response?.data?.message || 'Erreur lors de l\'inscription'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch (err) {
      console.error('Erreur lors de la déconnexion:', err)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
    }
  }

  async function checkAuth() {
    initializing.value = true
    
    if (!token.value) {
      initializing.value = false
      return
    }
    
    try {
      const response = await api.get('/auth/me')
      console.log('🔍 Structure réponse /auth/me:', response.data)
      
      // Gérer différentes structures de réponse API
      if (response.data.data?.user) {
        user.value = response.data.data.user
        console.log('📦 /auth/me: Structure avec wrapper data détectée')
      } else if (response.data.user) {
        user.value = response.data.user
        console.log('📦 /auth/me: Structure directe détectée')
      } else {
        console.warn('⚠️ Structure réponse /auth/me inattendue:', response.data)
        user.value = null
      }
      
      console.log('👤 User chargé via /auth/me:', user.value)
    } catch (err) {
      // Token invalide, déconnecter
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
    } finally {
      initializing.value = false
    }
  }

  async function refreshUser() {
    if (!isAuthenticated.value) return
    
    try {
      const response = await api.get('/auth/me')
      console.log('🔍 Structure réponse /auth/me:', response.data)
      
      // Gérer différentes structures de réponse API
      if (response.data.data?.user) {
        user.value = response.data.data.user
        console.log('📦 /auth/me: Structure avec wrapper data détectée')
      } else if (response.data.user) {
        user.value = response.data.user
        console.log('📦 /auth/me: Structure directe détectée')
      } else {
        console.warn('⚠️ Structure réponse /auth/me inattendue:', response.data)
        user.value = null
      }
      
      console.log('👤 User chargé via /auth/me:', user.value)
    } catch (err) {
      console.error('Erreur lors du rafraîchissement:', err)
    }
  }

  function clearError() {
    error.value = ''
  }

  function setError(errorMessage) {
    error.value = errorMessage
  }

  function setUser(userData) {
    user.value = userData
  }

  function setToken(tokenValue) {
    token.value = tokenValue
    if (tokenValue) {
      localStorage.setItem('auth_token', tokenValue)
    } else {
      localStorage.removeItem('auth_token')
    }
  }

  function getDefaultRedirect() {
    if (!user.value) {
      console.log('❌ Pas d\'utilisateur connecté, redirection vers home')
      return 'home'
    }
    
    console.log('🔄 Logique de redirection:', {
      email: user.value?.email,
      userRoles: user.value?.roles,
      isManager: isManager.value,
      isAdmin: isAdmin.value,
      isAgent: isAgent.value,
      isMerchant: isMerchant.value,
      isCustomer: isCustomer.value,
      isIndividualSeller: hasRole('Business Individual'),
      isBusinessSeller: hasRole('Business Enterprise'),
      roles: user.value?.roles?.map(r => r.name)
    })
    
    // 1. MANAGERS (Super Admin, Admin, Agent) → Admin Dashboard
    if (isManager.value) {
      console.log('✅ Utilisateur manager détecté, redirection vers admin.dashboard')
      console.log('  - isAdmin:', isAdmin.value)
      console.log('  - hasRole Super Admin:', hasRole('Super Admin'))
      console.log('  - hasRole Admin:', hasRole('Admin'))
      console.log('  - hasRole Agent:', hasRole('Agent'))
      return 'admin.dashboard'
    }
    
    // 2. BUSINESS INDIVIDUAL → Simple Merchant Dashboard (vendeur individuel)
    if (hasRole('Business Individual')) {
      console.log('✅ Redirection vers merchant.simple-dashboard (Vendeur Individuel)')
      return 'merchant.simple-dashboard'
    }
    
    // 3. BUSINESS ENTERPRISE → Full Merchant Dashboard (vendeur entreprise)
    if (hasRole('Business Enterprise')) {
      console.log('✅ Redirection vers merchant.dashboard (Vendeur Entreprise - dashboard complet)')
      return 'merchant.dashboard'
    }
    
    // 4. LEGACY BUSINESS → Simple Merchant Dashboard (rétrocompatibilité)
    if (hasRole('Business')) {
      console.log('✅ Redirection vers merchant.simple-dashboard (Business legacy - simple dashboard)')
      return 'merchant.simple-dashboard'
    }
    
    // 5. CUSTOMERS (rôle Particulier ou par défaut) → Customer Dashboard
    console.log('✅ Redirection vers customer.dashboard (Customer par défaut)')
    return 'customer.dashboard'
  }

  return {
    // State
    user,
    token,
    loading,
    error,
    initializing,
    
    // Getters
    isAuthenticated,
    isManager,
    isAdmin,
    isAgent,
    isMerchant,
    isCustomer,
    canSell,
    hasRole,
    
    // Nouveaux helpers pour les profils vendeurs
    isIndividualSeller: computed(() => hasRole('Business Individual')),
    isBusinessSeller: computed(() => hasRole('Business Enterprise')),
    
    // Actions
    login,
    register,
    logout,
    checkAuth,
    refreshUser,
    clearError,
    setError,
    setUser,
    setToken,
    getDefaultRedirect
  }
})

// Alias pour compatibilité
export const useAuth = () => useAuthStore()