<template>
  <div 
    v-if="shouldShowBanner" 
    class="mx-4 mb-4 p-4 rounded-lg border border-orange-300 animate-slideDown"
    style="background: linear-gradient(135deg, rgb(254, 215, 170) 0%, rgb(254, 235, 200) 100%); box-shadow: 0 2px 8px rgba(251, 191, 36, 0.15);"
  >
    <div class="flex items-start space-x-3 mb-3">
      <div class="flex-shrink-0 text-orange-600">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
      </div>
      
      <div class="flex-1">
        <h4 class="text-lg font-semibold text-orange-800 mb-1">Compte non vérifié</h4>
        <p class="text-sm text-orange-700 leading-relaxed">
          {{ getVerificationMessage() }}
        </p>
      </div>
    </div>
    
    <div class="flex justify-end">
      <button 
        @click="handleVerification"
        :disabled="isLoading"
        class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed hover:transform hover:scale-105 hover:shadow-lg"
      >
        <svg v-if="isLoading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ isLoading ? 'Envoi en cours...' : 'Envoyer l\'email de vérification' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/api'

const authStore = useAuthStore()
const router = useRouter()
const { post } = useApi()
const isLoading = ref(false)

// Définir les props (si nécessaire)
const props = defineProps({
  actionText: {
    type: String,
    default: 'Vérifier maintenant'
  }
})

// Computed pour déterminer si la bannière doit être affichée
const shouldShowBanner = computed(() => {
  return authStore.isAuthenticated && 
         authStore.user && 
         !authStore.user.is_email_verified &&
         !authStore.user.verified_at
})

// Message adapté selon le type d'utilisateur
const getVerificationMessage = () => {
  if (authStore.isAdmin || authStore.isManager) {
    return 'Vous devez vérifier votre compte pour accéder à toutes les fonctionnalités d\'administration.'
  } else if (authStore.isMerchant) {
    return 'Vous devez vérifier votre compte pour créer des produits et gérer vos tombolas.'
  } else {
    return 'Vous devez vérifier votre compte pour acheter des articles et participer aux tirages spéciaux.'
  }
}

// Gérer la vérification
const handleVerification = async () => {
  if (isLoading.value) return
  
  isLoading.value = true
  
  try {
    // Envoyer un nouveau lien de vérification (pour web)
    const response = await post('/auth/resend-verification', {
      email: authStore.user.email
    })
    
    if (response.success) {
      // Pour web : afficher message pour consulter les emails
      // Toast de succès
      if (window.$toast) {
        window.$toast.success('Email de vérification envoyé ! Consultez votre boîte email.', '📧 Vérification')
      }
      
      // Message explicatif
      if (window.$toast) {
        setTimeout(() => {
          window.$toast.info(
            'Cliquez sur le lien dans l\'email pour vérifier votre compte. Vérifiez aussi vos spams.',
            '💡 Instructions',
            { duration: 8000 }
          )
        }, 1500)
      }
    } else {
      throw new Error(response.message || 'Erreur lors de l\'envoi')
    }
  } catch (error) {
    console.error('Erreur verification:', error)
    
    // Toast d'erreur
    if (window.$toast) {
      const message = error.response?.data?.message || error.message || 'Erreur lors de l\'envoi de l\'email'
      window.$toast.error(message, '❌ Erreur')
    }
  } finally {
    isLoading.value = false
  }
}

// Utilitaire pour masquer l'email
const maskEmail = (email) => {
  if (!email) return ''
  const [localPart, domain] = email.split('@')
  if (localPart.length <= 2) {
    return '*'.repeat(localPart.length) + '@' + domain
  }
  return localPart.substring(0, 2) + '*'.repeat(localPart.length - 2) + '@' + domain
}
</script>

<style scoped>
/* Animation d'apparition */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slideDown {
  animation: slideDown 0.5s ease-out;
}

/* Responsive */
@media (max-width: 640px) {
  .verification-banner-mobile {
    margin: 0.5rem;
    padding: 0.75rem;
  }
  
  .verification-title-mobile {
    font-size: 1rem;
  }
  
  .verification-description-mobile {
    font-size: 0.75rem;
  }
  
  .verify-btn-mobile {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
  }
}
</style>