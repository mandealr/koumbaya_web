import axios from 'axios'
import { ref } from 'vue'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor pour ajouter le token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor pour gérer les erreurs
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Ne rediriger vers login que si :
    // 1. C'est une erreur 401 
    // 2. Ce n'est pas une tentative de login
    // 3. ET l'utilisateur avait un token (donc était connecté)
    const hadToken = localStorage.getItem('auth_token')
    
    if (error.response?.status === 401 && 
        !error.config?.url?.includes('/auth/login') && 
        hadToken) {
      // Token expiré ou invalide - mais seulement pour les utilisateurs connectés
      localStorage.removeItem('auth_token')
      // Éviter le rafraîchissement forcé, utiliser le router Vue à la place
      if (window.router) {
        window.router.push('/login')
      } else {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

// Composable useApi pour Vue 3 Composition API
export function useApi() {
  const loading = ref(false)
  const error = ref(null)

  const makeRequest = async (requestFn) => {
    loading.value = true
    error.value = null
    try {
      const response = await requestFn()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Une erreur est survenue'
      throw err
    } finally {
      loading.value = false
    }
  }

  const get = (url, config = {}) => makeRequest(() => api.get(url, config))
  const post = (url, data = {}, config = {}) => makeRequest(() => api.post(url, data, config))
  const put = (url, data = {}, config = {}) => makeRequest(() => api.put(url, data, config))
  const del = (url, config = {}) => makeRequest(() => api.delete(url, config))

  return {
    loading,
    error,
    get,
    post,
    put,
    delete: del,
    api // Expose l'instance axios pour les cas avancés
  }
}

export default api