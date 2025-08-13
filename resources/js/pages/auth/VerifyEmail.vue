<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-cyan-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl border border-gray-100 p-8">
      <!-- Logo -->
      <div class="text-center mb-8">
        <img 
          :src="logoUrl" 
          alt="Logo Koumbaya" 
          class="h-16 w-auto mx-auto mb-4"
        />
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#0099cc] border-t-transparent mx-auto mb-4"></div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Vérification en cours...</h2>
        <p class="text-gray-600">Nous vérifions votre lien de vérification.</p>
      </div>

      <!-- Success state -->
      <div v-else-if="verificationResult?.success" class="text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-green-600 mb-2">
          {{ verificationResult.already_verified ? 'Déjà vérifié !' : 'Compte vérifié !' }}
        </h2>
        <p class="text-gray-600 mb-6">{{ verificationResult.message }}</p>
        
        <div class="space-y-3">
          <button
            @click="redirectToDashboard"
            class="w-full bg-[#0099cc] hover:bg-[#0088bb] text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl"
          >
            Accéder à mon compte
          </button>
          <button
            @click="$router.push('/')"
            class="w-full border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-all duration-200"
          >
            Retour à l'accueil
          </button>
        </div>
      </div>

      <!-- Error state -->
      <div v-else class="text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-red-600 mb-2">Vérification échouée</h2>
        <p class="text-gray-600 mb-6">{{ errorMessage }}</p>
        
        <div class="space-y-3">
          <button
            @click="$router.push('/register')"
            class="w-full bg-[#0099cc] hover:bg-[#0088bb] text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200"
          >
            Créer un nouveau compte
          </button>
          <button
            @click="$router.push('/login')"
            class="w-full border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-all duration-200"
          >
            Se connecter
          </button>
        </div>
      </div>

      <!-- Information -->
      <div class="mt-8 p-4 bg-blue-50 rounded-xl border border-blue-200">
        <div class="flex items-start">
          <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <h4 class="text-sm font-semibold text-blue-900">Pourquoi vérifier mon compte ?</h4>
            <p class="text-sm text-blue-700 mt-1">
              La vérification de votre compte est nécessaire pour pouvoir acheter des produits et participer aux tombolas sur Koumbaya.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/api'
import logoUrl from '@/assets/logo.png'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { get } = useApi()

const loading = ref(true)
const verificationResult = ref(null)
const errorMessage = ref('')

const verifyEmail = async () => {
  try {
    const token = route.query.token
    if (!token) {
      throw new Error('Token de vérification manquant')
    }

    const response = await get(`/auth/verify-email/${encodeURIComponent(token)}`)
    
    if (response.success) {
      verificationResult.value = response
      
      // Si l'utilisateur est connecté, actualiser ses informations
      if (authStore.isAuthenticated) {
        await authStore.fetchUser()
      }
    } else {
      throw new Error(response.message || 'Erreur de vérification')
    }
  } catch (error) {
    console.error('Erreur lors de la vérification:', error)
    verificationResult.value = { success: false }
    errorMessage.value = error.response?.data?.message || error.message || 'Une erreur est survenue lors de la vérification'
  } finally {
    loading.value = false
  }
}

const redirectToDashboard = () => {
  if (authStore.isAuthenticated) {
    router.push('/dashboard')
  } else {
    router.push('/login')
  }
}

onMounted(() => {
  verifyEmail()
})
</script>