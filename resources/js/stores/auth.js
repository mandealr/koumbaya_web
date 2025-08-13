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
  
  // Détection des managers selon le système hybride
  const isManager = computed(() => {
    // 1. PRIORITÉ PRINCIPALE : vérification par user_type_id
    if (user.value?.userType?.name === 'MANAGER') {
      return true
    }
    
    // 2. Vérification par user_type_id direct
    if (user.value?.user_type_id === 1) {
      return true // MANAGER
    }
    
    // 3. Vérification par les rôles hybrides
    if (user.value?.roles && Array.isArray(user.value.roles)) {
      const managerRoles = ['Super Admin', 'Admin', 'Agent', 'Agent Back Office']
      return user.value.roles.some(role => managerRoles.includes(role.name))
    }
    
    // 4. Fallback : vérification par email (pour les comptes existants)
    if (user.value?.email) {
      const managerEmails = [
        'superadmin@koumbaya.ga',
        'admin@koumbaya.ga', 
        'agent@koumbaya.ga',
        'backoffice@koumbaya.ga'
      ]
      return managerEmails.includes(user.value.email)
    }
    
    return false
  })
  
  const isAdmin = computed(() => {
    // Vérification par les rôles hybrides
    if (user.value?.roles && Array.isArray(user.value.roles)) {
      const adminRoles = ['Super Admin', 'Admin']
      return user.value.roles.some(role => adminRoles.includes(role.name))
    }
    
    // Fallback : vérification classique par email
    if (user.value?.email?.includes('superadmin')) {
      return true
    }
    
    // Fallback : vérification par email
    if (user.value?.email) {
      const adminEmails = ['superadmin@koumbaya.ga', 'admin@koumbaya.ga']
      return adminEmails.includes(user.value.email)
    }
    
    return false
  })
  
  const isAgent = computed(() => {
    // Vérification par les rôles hybrides
    if (user.value?.roles && Array.isArray(user.value.roles)) {
      const agentRoles = ['Agent', 'Agent Back Office']
      return user.value.roles.some(role => agentRoles.includes(role.name))
    }
    
    // Fallback : vérification par email
    if (user.value?.email) {
      const agentEmails = ['agent@koumbaya.ga', 'backoffice@koumbaya.ga']
      return agentEmails.includes(user.value.email)
    }
    
    return false
  })
  
  const isMerchant = computed(() => {
    // Système de rôles simplifié : Business = marchand uniquement
    if (user.value?.roles && Array.isArray(user.value.roles)) {
      return user.value.roles.some(role => role.name === 'Business')
    }
    
    // Fallback : vérifications classiques (pour compatibilité transitoire)
    if (user.value?.can_sell && user.value?.account_type === 'business') return true
    
    // Fallback : vérification par email (pour comptes existants)
    if (user.value?.email) {
      const businessEmails = ['business@koumbaya.ga', 'business2@koumbaya.ga']
      return businessEmails.includes(user.value.email)
    }
    
    return false
  })
  
  const isCustomer = computed(() => {
    // Les managers ne sont pas des customers
    if (isManager.value) return false
    
    // Système de rôles simplifié : Particulier = client uniquement
    if (user.value?.roles && Array.isArray(user.value.roles)) {
      return user.value.roles.some(role => role.name === 'Particulier')
    }
    
    // Fallback : vérifications classiques (pour compatibilité transitoire)
    if (user.value?.account_type === 'personal') return true
    
    // Par défaut, si ce n'est ni un manager ni un business, c'est un customer
    return !isManager.value && !isMerchant.value
  })
  
  const isBusiness = computed(() => 
    user.value?.account_type === 'business' ||
    user.value?.roles?.some(role => role.name === 'Business')
  )
  
  const isPersonal = computed(() => 
    user.value?.account_type === 'personal' ||
    user.value?.roles?.some(role => role.name === 'Particulier')
  )
  
  const canSell = computed(() => 
    user.value?.can_sell === true ||
    isBusiness.value ||
    user.value?.roles?.some(role => role.name === 'Business')
  )

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
      user: user.value,
      roles: user.value?.roles,
      isManager: isManager.value,
      isAdmin: isAdmin.value,
      isAgent: isAgent.value,
      isMerchant: isMerchant.value,
      isCustomer: isCustomer.value,
      account_type: user.value?.account_type,
      roles: user.value?.roles?.map(r => r.name)
    })
    
    // 1. MANAGERS → Admin Dashboard
    if (isManager.value) {
      console.log('✅ Redirection vers admin.dashboard (Manager)')
      return 'admin.dashboard'
    }
    
    // 2. BUSINESS (rôle Business uniquement) → Merchant Dashboard  
    if (user.value?.roles && Array.isArray(user.value.roles)) {
      const hasBusiness = user.value.roles.some(role => role.name === 'Business')
      
      if (hasBusiness) {
        console.log('✅ Redirection vers merchant.dashboard (Business)')
        return 'merchant.dashboard'
      }
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
    isBusiness,
    isPersonal,
    canSell,
    
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