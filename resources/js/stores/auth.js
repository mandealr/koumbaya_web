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
  const isAdmin = computed(() => user.value?.role === 'ADMIN')
  const isMerchant = computed(() => user.value?.role === 'MERCHANT')
  const isCustomer = computed(() => user.value?.role === 'CUSTOMER')

  // Actions
  async function login(credentials) {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.post('/auth/login', credentials)
      const { token: authToken, user: userData } = response.data
      
      token.value = authToken
      user.value = userData
      
      localStorage.setItem('auth_token', authToken)
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur de connexion'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function register(userData) {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.post('/auth/register', userData)
      const { token: authToken, user: newUser } = response.data
      
      token.value = authToken
      user.value = newUser
      
      localStorage.setItem('auth_token', authToken)
      
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de l\'inscription'
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

  return {
    // State
    user,
    token,
    loading,
    error,
    
    // Getters
    isAuthenticated,
    isAdmin,
    isMerchant,
    isCustomer,
    
    // Actions
    login,
    register,
    logout,
    checkAuth,
    refreshUser,
    clearError
  }
})