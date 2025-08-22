<template>
  <div
    v-if="user && !user.verified_at && shouldShowBanner"
    class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-4 mb-6 shadow-sm"
  >
    <div class="flex items-start">
      <!-- Icône -->
      <div class="flex-shrink-0">
        <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600" />
      </div>

      <!-- Contenu -->
      <div class="ml-3 flex-1">
        <h3 class="text-sm font-semibold text-yellow-800 mb-1">
          Compte non vérifié
        </h3>
        <p class="text-sm text-yellow-700 mb-3">
          Votre compte n'est pas encore vérifié. Certaines fonctionnalités peuvent être limitées.
        </p>

        <div class="flex flex-col sm:flex-row gap-2">
          <button
            @click="resendVerification"
            :disabled="loading"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-yellow-800 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors disabled:opacity-50"
          >
            <span v-if="!loading">Renvoyer l'email de vérification</span>
            <span v-else class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-yellow-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Envoi en cours...
            </span>
          </button>

          <button
            @click="hideBanner"
            class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-lg text-yellow-600 hover:text-yellow-800 transition-colors"
          >
            Masquer
          </button>
        </div>
      </div>

      <!-- Bouton fermer -->
      <div class="flex-shrink-0 ml-2">
        <button
          @click="hideBanner"
          class="inline-flex rounded-lg p-1.5 text-yellow-400 hover:text-yellow-600 hover:bg-yellow-100 transition-colors"
        >
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/api'
import { ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const { post } = useApi()
const loading = ref(false)
const bannerHidden = ref(false)

const user = computed(() => authStore.user)
const shouldShowBanner = computed(() => !bannerHidden.value)

const resendVerification = async () => {
  loading.value = true

  try {
    // Appel API pour renvoyer l'email de vérification
    const response = await post('/auth/resend-verification', {
      email: user.value.email
    })

    if (window.$toast) {
      window.$toast.success(
        `Un nouvel email de vérification a été envoyé à ${user.value.email}. Vérifiez votre boîte email.`,
        '✅ Email envoyé',
        8000
      )
    }

    // Masquer temporairement le banner après envoi
    setTimeout(() => {
      bannerHidden.value = true
      localStorage.setItem('verification_banner_hidden_until', Date.now() + (30 * 60 * 1000)) // 30 minutes
    }, 2000)

  } catch (error) {
    console.error('Erreur lors de l\'envoi de l\'email de vérification:', error)

    let errorMessage = 'Une erreur est survenue lors de l\'envoi de l\'email.'

    if (error.response?.status === 429) {
      errorMessage = 'Trop de tentatives. Veuillez patienter avant de renvoyer un email.'
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }

    if (window.$toast) {
      window.$toast.error(errorMessage, 'Erreur')
    }
  } finally {
    loading.value = false
  }
}

const hideBanner = () => {
  bannerHidden.value = true
  // Sauvegarder dans localStorage pour ne pas réafficher pendant 1 heure
  localStorage.setItem('verification_banner_hidden_until', Date.now() + (60 * 60 * 1000))
}

// Restaurer l'état du banner depuis localStorage avec expiration
const hiddenUntil = localStorage.getItem('verification_banner_hidden_until')
if (hiddenUntil && Date.now() < parseInt(hiddenUntil)) {
  bannerHidden.value = true
}
</script>
