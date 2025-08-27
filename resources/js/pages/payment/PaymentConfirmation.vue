<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Status Header -->
      <div class="text-center mb-8">
        <div class="mb-6">
          <img :src="logoUrl" alt="Koumbaya" class="h-12 mx-auto" />
        </div>
        <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center"
             :class="getStatusClasses().bg">
          <component :is="getStatusIcon()" 
                     :class="['h-10 w-10', getStatusClasses().icon]" />
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">
          {{ getStatusTitle() }}
        </h1>
        <p class="text-gray-600">{{ getStatusMessage() }}</p>
      </div>

      <!-- Timer (only when pending) -->
      <div v-if="paymentStatus === 'pending'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="text-center">
          <div class="text-4xl font-bold mb-2"
               :class="timeRemaining <= 30 ? 'text-red-600' : 'text-blue-600'">
            {{ formatTime(timeRemaining) }}
          </div>
          <div class="text-sm text-gray-500 mb-4">
            Temps restant pour confirmer le paiement
          </div>
          
          <!-- Progress bar -->
          <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
            <div 
              class="h-2 rounded-full transition-all duration-1000"
              :class="timeRemaining <= 30 ? 'bg-red-500' : 'bg-blue-500'"
              :style="{ width: (timeRemaining / 90) * 100 + '%' }"
            ></div>
          </div>
          
          <div class="text-xs text-gray-400">
            Le paiement expirera automatiquement à la fin du timer
          </div>
        </div>
      </div>

      <!-- Payment Details -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails du paiement</h2>
        
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">Montant</span>
            <span class="font-semibold text-gray-900">{{ formatPrice(amount) }}</span>
          </div>
          
          <div class="flex justify-between">
            <span class="text-gray-600">Opérateur</span>
            <span class="font-semibold text-gray-900">
              {{ operator === 'airtel' ? 'Airtel Money' : 'Moov Money' }}
            </span>
          </div>
          
          <div class="flex justify-between">
            <span class="text-gray-600">Téléphone</span>
            <span class="font-semibold text-gray-900">+241 {{ phoneNumber }}</span>
          </div>
          
          <div class="flex justify-between">
            <span class="text-gray-600">Référence</span>
            <span class="font-semibold text-gray-900 font-mono text-sm">{{ reference }}</span>
          </div>
          
          <hr class="my-3">
          
          <div class="flex justify-between text-lg">
            <span class="font-semibold text-gray-900">Total</span>
            <span class="font-bold text-gray-900">{{ formatPrice(amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Instructions based on status -->
      <div v-if="paymentStatus === 'pending'" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
          <InformationCircleIcon class="h-5 w-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" />
          <div class="text-sm text-blue-700">
            <p class="font-medium mb-2">Instructions :</p>
            <ol class="space-y-1 text-xs">
              <li>1. Composez le code USSD sur votre téléphone</li>
              <li>2. Suivez les instructions de votre opérateur</li>
              <li>3. Confirmez le paiement avec votre code PIN</li>
              <li>4. Le paiement sera confirmé automatiquement</li>
            </ol>
          </div>
        </div>
      </div>

      <div v-else-if="paymentStatus === 'failed'" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" />
          <div class="text-sm text-red-700">
            <p class="font-medium mb-2">Le paiement a échoué</p>
            <p class="text-xs">{{ errorMessage || 'Veuillez réessayer ou vérifier votre solde.' }}</p>
          </div>
        </div>
      </div>

      <div v-else-if="paymentStatus === 'expired'" class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
          <ClockIcon class="h-5 w-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0" />
          <div class="text-sm text-orange-700">
            <p class="font-medium mb-2">Paiement expiré</p>
            <p class="text-xs">Le délai de 90 secondes est écoulé. Veuillez recommencer le processus.</p>
          </div>
        </div>
      </div>

      <div v-else-if="paymentStatus === 'success'" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
          <CheckCircleIcon class="h-5 w-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" />
          <div class="text-sm text-green-700">
            <p class="font-medium mb-2">Paiement confirmé !</p>
            <p class="text-xs">Votre commande a été traitée avec succès. Vous allez recevoir une confirmation par email.</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-3">
        <button
          v-if="paymentStatus === 'pending'"
          @click="checkPaymentStatus"
          :disabled="loading"
          class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center"
        >
          <span v-if="loading">
            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            Vérification...
          </span>
          <span v-else>
            <ArrowPathIcon class="h-5 w-5 mr-2" />
            Vérifier le statut
          </span>
        </button>

        <button
          v-if="paymentStatus === 'success'"
          @click="goToProfile"
          class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center"
        >
          <UserIcon class="h-5 w-5 mr-2" />
          Voir mes achats
        </button>

        <button
          v-if="paymentStatus === 'pending'"
          @click="retryUssdPush"
          :disabled="loading"
          class="w-full py-3 px-4 bg-orange-600 hover:bg-orange-700 disabled:bg-orange-300 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center"
        >
          <span v-if="loading">
            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            Relance...
          </span>
          <span v-else>
            <ArrowPathIcon class="h-5 w-5 mr-2" />
            Relancer le push USSD
          </span>
        </button>

        <button
          v-if="['failed', 'expired'].includes(paymentStatus)"
          @click="retryPayment"
          class="w-full py-3 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all duration-200 flex items-center justify-center"
        >
          <ArrowPathIcon class="h-5 w-5 mr-2" />
          Réessayer le paiement
        </button>

        <button 
          @click="goHome" 
          class="w-full py-3 px-4 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
        >
          Retour à l'accueil
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { 
  ClockIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  ArrowPathIcon,
  UserIcon
} from '@heroicons/vue/24/outline'
import logoUrl from '@/assets/logo.png'

const route = useRoute()
const router = useRouter()
const { get, post } = useApi()

// State
const paymentStatus = ref('pending') // 'pending', 'success', 'failed', 'expired'
const timeRemaining = ref(90) // 90 seconds
const loading = ref(false)
const errorMessage = ref('')
const timer = ref(null)
const statusCheckInterval = ref(null)

// Payment details from query params
const billId = ref('')
const reference = ref('')
const amount = ref(0)
const phoneNumber = ref('')
const operator = ref('')

// Methods
const getStatusClasses = () => {
  switch (paymentStatus.value) {
    case 'pending':
      return { bg: 'bg-blue-100', icon: 'text-blue-600' }
    case 'success':
      return { bg: 'bg-green-100', icon: 'text-green-600' }
    case 'failed':
      return { bg: 'bg-red-100', icon: 'text-red-600' }
    case 'expired':
      return { bg: 'bg-orange-100', icon: 'text-orange-600' }
    default:
      return { bg: 'bg-gray-100', icon: 'text-gray-600' }
  }
}

const getStatusIcon = () => {
  switch (paymentStatus.value) {
    case 'pending': return ClockIcon
    case 'success': return CheckCircleIcon
    case 'failed': 
    case 'expired': return ExclamationTriangleIcon
    default: return ClockIcon
  }
}

const getStatusTitle = () => {
  switch (paymentStatus.value) {
    case 'pending': return 'Paiement en cours...'
    case 'success': return 'Paiement réussi !'
    case 'failed': return 'Paiement échoué'
    case 'expired': return 'Paiement expiré'
    default: return 'Traitement...'
  }
}

const getStatusMessage = () => {
  switch (paymentStatus.value) {
    case 'pending': return 'Confirmez le paiement sur votre téléphone'
    case 'success': return 'Votre paiement a été traité avec succès'
    case 'failed': return 'Une erreur est survenue lors du paiement'
    case 'expired': return 'Le délai de paiement a expiré'
    default: return 'Veuillez patienter...'
  }
}

const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price).replace('XAF', 'FCFA')
}

const startTimer = () => {
  timer.value = setInterval(() => {
    if (timeRemaining.value > 0) {
      timeRemaining.value--
    } else {
      paymentStatus.value = 'expired'
      clearInterval(timer.value)
      clearInterval(statusCheckInterval.value)
    }
  }, 1000)
}

const checkPaymentStatus = async () => {
  if (!billId.value) return
  
  loading.value = true
  
  try {
    const response = await get(`/payments/status/${billId.value}`)
    
    if (response.success) {
      const status = response.data.status
      
      if (status === 'paid') {
        paymentStatus.value = 'success'
        clearInterval(timer.value)
        clearInterval(statusCheckInterval.value)
        
        if (window.$toast) {
          window.$toast.success('Paiement confirmé avec succès !', '✅ Succès')
        }
      } else if (status === 'failed') {
        paymentStatus.value = 'failed'
        errorMessage.value = response.data.error_message || ''
        clearInterval(timer.value)
        clearInterval(statusCheckInterval.value)
      }
    }
  } catch (error) {
    console.error('Error checking payment status:', error)
  } finally {
    loading.value = false
  }
}

const startStatusCheck = () => {
  // Vérifier le statut toutes les 5 secondes
  statusCheckInterval.value = setInterval(() => {
    if (paymentStatus.value === 'pending') {
      checkPaymentStatus()
    }
  }, 5000)
}

const retryUssdPush = async () => {
  if (!billId.value || !operator.value) return
  
  loading.value = true
  
  try {
    const response = await post('/payments/retry-ussd', {
      bill_id: billId.value,
      operator: operator.value
    })
    
    if (response.success) {
      if (window.$toast) {
        window.$toast.success('Push USSD relancé avec succès !', '✅ Succès')
      }
    } else {
      if (window.$toast) {
        window.$toast.error(response.message || 'Erreur lors du relancement du push USSD', '❌ Erreur')
      }
    }
  } catch (error) {
    console.error('Error retrying USSD push:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du relancement du push USSD', '❌ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const retryPayment = () => {
  router.push({
    name: 'payment.phone',
    query: route.query
  })
}

const goToProfile = () => {
  router.push('/profile')
}

const goHome = () => {
  router.push('/')
}

// Lifecycle
onMounted(() => {
  // Récupérer les paramètres depuis l'URL
  billId.value = route.query.bill_id || ''
  reference.value = route.query.reference || ''
  amount.value = parseFloat(route.query.amount) || 0
  phoneNumber.value = route.query.phone || ''
  operator.value = route.query.operator || ''
  
  if (!billId.value || !amount.value) {
    router.push('/')
    return
  }
  
  // Démarrer le timer et la vérification de statut
  startTimer()
  startStatusCheck()
  
  // Vérifier le statut immédiatement
  checkPaymentStatus()
})

onUnmounted(() => {
  if (timer.value) {
    clearInterval(timer.value)
  }
  if (statusCheckInterval.value) {
    clearInterval(statusCheckInterval.value)
  }
})
</script>