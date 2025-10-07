<template>
  <div class="min-h-screen bg-gradient-to-br from-koumbaya-primary/5 via-white to-koumbaya-primary/10 flex items-center justify-center">
    <div class="text-center">
      <div v-if="loading" class="mb-4">
        <div class="animate-spin rounded-full h-16 w-16 border-4 border-koumbaya-primary border-t-transparent mx-auto"></div>
        <p class="mt-4 text-lg text-gray-700">Authentification en cours...</p>
      </div>

      <div v-else-if="error" class="max-w-md mx-auto p-8 bg-white rounded-xl shadow-lg">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Erreur d'authentification</h2>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <router-link
          to="/login"
          class="inline-block px-6 py-3 bg-koumbaya-primary text-white rounded-lg hover:bg-koumbaya-primary-dark transition-colors"
        >
          Retour à la connexion
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  const token = route.query.token
  const errorParam = route.query.error
  const provider = route.query.provider

  if (errorParam) {
    error.value = errorParam
    loading.value = false
    return
  }

  if (!token) {
    error.value = 'Token manquant'
    loading.value = false
    return
  }

  try {
    // Store the token
    localStorage.setItem('token', token)

    // Fetch user data
    const response = await fetch('/api/auth/me', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error('Impossible de récupérer les informations utilisateur')
    }

    const data = await response.json()

    if (data.success && data.user) {
      // Set user and token in store
      authStore.setUser(data.user)
      authStore.setToken(token)

      // Show success message
      if (window.$toast) {
        window.$toast.success(
          `Bienvenue ${data.user.first_name || data.user.name} !`,
          '✅ Connexion réussie'
        )
      }

      // Redirect to appropriate dashboard
      setTimeout(() => {
        const redirectTo = authStore.getDefaultRedirect()
        router.push({ name: redirectTo })
      }, 1000)
    } else {
      throw new Error('Réponse invalide du serveur')
    }
  } catch (err) {
    console.error('Social auth callback error:', err)
    error.value = err.message || 'Une erreur est survenue'
    loading.value = false
  }
})
</script>
