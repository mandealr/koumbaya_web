import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/composables/api'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref('')

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  
  // Role helper function
  const hasRole = (roleName) => {
    return user.value?.roles && Array.isArray(user.value.roles) &&
           user.value.roles.some(role => role.name === roleName)
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
    return hasRole('Business')
  })
  
  const isCustomer = computed(() => {
    return hasRole('Particulier')
  })
  
  const canSell = computed(() => {
    return hasRole('Business')
  })

  // Actions
  async function login(credentials) {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.post('/auth/login', credentials)
      const { access_token: authToken, user: userData } = response.data
      
      token.value = authToken
      user.value = userData
      
      localStorage.setItem('auth_token', authToken)
      
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
      const { access_token: authToken, user: newUser } = response.data
      
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
    if (!token.value) return
    
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user
    } catch (err) {
      // Token invalide, déconnecter
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
    }
  }

  async function refreshUser() {
    if (!isAuthenticated.value) return
    
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user
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
    if (!user.value) return 'home'
    
    console.log('🔄 Logique de redirection:', {
      email: user.value?.email,
      isManager: isManager.value,
      isAdmin: isAdmin.value,
      isAgent: isAgent.value,
      isMerchant: isMerchant.value,
      isCustomer: isCustomer.value,
      roles: user.value?.roles?.map(r => r.name)
    })
    
    // 1. MANAGERS → Admin Dashboard
    if (isManager.value) {
      console.log('✅ Redirection vers admin.dashboard (Manager)')
      return 'admin.dashboard'
    }
    
    // 2. BUSINESS (rôle Business uniquement) → Merchant Dashboard  
    if (isMerchant.value) {
      console.log('✅ Redirection vers merchant.dashboard (Business)')
      return 'merchant.dashboard'
    }
    
    // 3. CUSTOMERS (rôle Particulier ou par défaut) → Customer Dashboard
    console.log('✅ Redirection vers customer.dashboard (Customer par défaut)')
    return 'customer.dashboard'
  }

  return {
    // State
    user,
    token,
    loading,
    error,
    
    // Getters
    isAuthenticated,
    isManager,
    isAdmin,
    isAgent,
    isMerchant,
    isCustomer,
    canSell,
    hasRole,
    
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