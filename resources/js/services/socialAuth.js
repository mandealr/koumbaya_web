import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/api'

// Export functions instead of a class instance to avoid initialization issues
export const socialAuth = {

  /**
   * Initiate Facebook login
   */
  async loginWithFacebook() {
    try {
      // For production, you would integrate with Facebook SDK
      // For now, we'll use a popup approach
      const api = useApi()
      const response = await api.get('/auth/facebook/redirect')
      if (response.redirect_url) {
        window.location.href = response.redirect_url
      }
    } catch (error) {
      console.error('Facebook login error:', error)
      throw new Error('Erreur lors de la connexion avec Facebook')
    }
  },

  /**
   * Initiate Google login
   */
  async loginWithGoogle() {
    try {
      // For production, you would integrate with Google SDK
      // For now, we'll use a popup approach
      const api = useApi()
      const response = await api.get('/auth/google/redirect')
      if (response.redirect_url) {
        window.location.href = response.redirect_url
      }
    } catch (error) {
      console.error('Google login error:', error)
      throw new Error('Erreur lors de la connexion avec Google')
    }
  },

  /**
   * Initiate Apple login
   */
  async loginWithApple() {
    try {
      // For production, you would integrate with Apple SDK
      // For now, we'll use a popup approach
      const api = useApi()
      const response = await api.get('/auth/apple/redirect')
      if (response.redirect_url) {
        window.location.href = response.redirect_url
      }
    } catch (error) {
      console.error('Apple login error:', error)
      throw new Error('Erreur lors de la connexion avec Apple')
    }
  },

  /**
   * Handle social login callback
   * This method would be called after the user is redirected back from the provider
   */
  async handleSocialCallback(provider, params) {
    try {
      const api = useApi()
      const response = await api.get(`/auth/${provider}/callback`, { params })
      
      if (response.access_token) {
        // Store the authentication data
        const authStore = useAuthStore()
        authStore.setUser(response.user)
        authStore.setToken(response.access_token)
        
        return {
          success: true,
          user: response.user,
          message: response.message
        }
      } else {
        throw new Error('Réponse invalide du serveur')
      }
    } catch (error) {
      console.error('Social callback error:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'Erreur lors de l\'authentification'
      }
    }
  },

  /**
   * Open social login in popup window
   * Alternative approach for better UX
   */
  async openSocialLoginPopup(provider) {
    return new Promise((resolve, reject) => {
      try {
        const popup = window.open(
          `/api/auth/${provider}`,
          'socialLogin',
          'width=600,height=700,scrollbars=yes,resizable=yes'
        )

        const checkClosed = setInterval(() => {
          if (popup.closed) {
            clearInterval(checkClosed)
            // Check if authentication was successful
            socialAuth.checkAuthenticationStatus().then(resolve).catch(reject)
          }
        }, 1000)

        // Timeout after 5 minutes
        setTimeout(() => {
          clearInterval(checkClosed)
          if (!popup.closed) {
            popup.close()
          }
          reject(new Error('Timeout lors de l\'authentification'))
        }, 300000)

      } catch (error) {
        reject(error)
      }
    })
  },

  /**
   * Check if user is authenticated after popup closes
   */
  async checkAuthenticationStatus() {
    try {
      const api = useApi()
      const response = await api.get('/auth/me')
      if (response.user) {
        const authStore = useAuthStore()
        authStore.setUser(response.user)
        return {
          success: true,
          user: response.user
        }
      }
      return { success: false }
    } catch (error) {
      return { success: false }
    }
  }
}

export default socialAuth