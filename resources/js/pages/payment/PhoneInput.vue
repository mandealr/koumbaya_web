<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="mb-6">
          <router-link to="/">
            <img :src="logoUrl" alt="Koumbaya" class="h-12 mx-auto hover:opacity-80 transition-opacity" />
          </router-link>
        </div>
        <div class="w-20 h-20 mx-auto mb-4 rounded-lg overflow-hidden flex items-center justify-center bg-white border border-gray-200 shadow-sm">
          <img 
            :src="paymentMethod === 'airtel_money' ? airtelLogo : moovLogo" 
            :alt="paymentMethod === 'airtel_money' ? 'Airtel Money' : 'Moov Money'"
            class="w-16 h-16 object-contain"
          />
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ paymentMethod === 'airtel_money' ? 'Airtel Money' : 'Moov Money' }}
        </h1>
        <p class="mt-2 text-gray-600">Saisissez votre numéro de téléphone</p>
      </div>

      <!-- Montant -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 text-center">
        <div class="text-sm text-gray-500 mb-1">Montant à payer</div>
        <div class="text-3xl font-bold text-gray-900">{{ formatPrice(amount) }}</div>
      </div>

      <!-- Formulaire -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form @submit.prevent="processPayment" class="space-y-6">
          <!-- Numéro de téléphone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Numéro de téléphone
            </label>
            <input
              v-model="phoneNumber"
              type="tel"
              :placeholder="selectedOperator === 'airtel' ? '074010203' : '065010203'"
              maxlength="9"
              :class="[
                'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:border-transparent',
                selectedOperator === 'airtel' 
                  ? 'focus:ring-red-500' 
                  : 'focus:ring-blue-500',
                errors.phone ? 'border-red-300' : ''
              ]"
              @input="formatPhoneNumber"
            />
            <div v-if="errors.phone" class="mt-1 text-sm text-red-600">
              {{ errors.phone }}
            </div>
            <div class="mt-2 text-xs text-gray-500">
              Format: {{ selectedOperator === 'airtel' ? '074010203 (074, 077, 076 pour Airtel)' : '065010203 (065, 062, 066, 060 pour Moov)' }}
            </div>
          </div>

          <!-- Boutons -->
          <div class="space-y-3">
            <button
              type="submit"
              :disabled="!isFormValid || loading"
              :class="[
                'w-full py-3 px-4 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center',
                selectedOperator === 'airtel' 
                  ? 'bg-red-600 hover:bg-red-700 disabled:bg-red-300' 
                  : 'bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300',
                'text-white disabled:cursor-not-allowed'
              ]"
            >
              <template v-if="loading">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                <span>Traitement...</span>
              </template>
              <template v-else>
                <span>Payer {{ formatPrice(amount) }}</span>
                <ArrowRightIcon class="h-5 w-5" />
              </template>
            </button>

            <button 
              type="button"
              @click="goBack" 
              class="w-full py-3 px-4 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Retour
            </button>
          </div>
        </form>
      </div>

      <!-- Instructions -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <InformationCircleIcon class="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
          <div class="text-sm text-blue-700">
            <p class="font-medium mb-1">Instructions de paiement :</p>
            <ul class="space-y-1 text-xs">
              <li>• Assurez-vous d'avoir suffisamment de crédit</li>
              <li>• Gardez votre téléphone près de vous</li>
              <li>• Vous recevrez un code de confirmation USSD</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { 
  PhoneIcon,
  ArrowRightIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'
import logoUrl from '@/assets/logo.png'
import airtelLogo from '@/assets/am.png'
import moovLogo from '@/assets/mm.png'

const route = useRoute()
const router = useRouter()
const { post } = useApi()

// State
const paymentMethod = ref('airtel_money')
const selectedOperator = ref('airtel')
const phoneNumber = ref('')
const loading = ref(false)
const errors = ref({})

// Query params
const amount = ref(0)
const orderId = ref(null)

// Computed
const isFormValid = computed(() => {
  return selectedOperator.value && 
         phoneNumber.value.length === 9 &&
         validatePhonePrefix()
})

// Methods
const formatPhoneNumber = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  
  if (value.length <= 9) {
    phoneNumber.value = value
  }
  
  // Validation en temps réel
  validatePhone()
}

const validatePhonePrefix = () => {
  const cleanPhone = phoneNumber.value
  
  if (selectedOperator.value === 'airtel') {
    return cleanPhone.startsWith('074') || cleanPhone.startsWith('077') || cleanPhone.startsWith('076')
  } else if (selectedOperator.value === 'moov') {
    return cleanPhone.startsWith('065') || cleanPhone.startsWith('062') || cleanPhone.startsWith('066') || cleanPhone.startsWith('060')
  }
  return false
}

const validatePhone = () => {
  errors.value = {}
  
  const cleanPhone = phoneNumber.value
  
  if (!cleanPhone) {
    errors.value.phone = 'Le numéro de téléphone est requis'
    return false
  }
  
  if (cleanPhone.length !== 9) {
    errors.value.phone = 'Le numéro doit contenir 9 chiffres'
    return false
  }
  
  if (!validatePhonePrefix()) {
    const prefix = selectedOperator.value === 'airtel' ? '074, 077 ou 076' : '065, 062, 066 ou 060'
    errors.value.phone = `Le numéro doit commencer par ${prefix} pour ${selectedOperator.value === 'airtel' ? 'Airtel' : 'Moov'}`
    return false
  }
  
  return true
}

const processPayment = async () => {
  if (!validatePhone() || !isFormValid.value) return
  
  // Vérifier qu'on a bien un orderId valide
  if (!orderId.value) {
    if (window.$toast) {
      window.$toast.error('Aucun numéro de commande fourni', '❌ Erreur')
    }
    return
  }
  
  loading.value = true
  
  try {
    // Préparer les données de la requête
    const requestData = {
      phone: phoneNumber.value,
      operator: selectedOperator.value
    }
    
    // Ajouter le bon paramètre selon ce qu'on a reçu
    if (route.query.order_number) {
      requestData.order_number = orderId.value
    } else if (route.query.transaction_id) {
      requestData.transaction_id = orderId.value
    } else {
      // Par défaut, traiter comme order_number
      requestData.order_number = orderId.value
    }
    
    // Utiliser le nouvel endpoint pour créer le paiement depuis une commande existante
    const response = await post('/payments/initiate-from-transaction', requestData)
    
    if (response.success) {
      // Rediriger vers la page de confirmation avec timer
      router.push({
        name: 'payment.confirmation',
        query: {
          bill_id: response.data.bill_id,
          reference: response.data.reference,
          amount: response.data.amount,
          phone: phoneNumber.value,
          operator: selectedOperator.value,
          transaction_id: response.data.transaction_id,
          type: route.query.type, // Passer le type de transaction
          order_number: response.data.order_number || route.query.order_number // Passer le numéro de commande
        }
      })
    } else {
      // Gestion spéciale pour les commandes expirées
      if (response.error_code === 'ORDER_EXPIRED') {
        if (window.$toast) {
          window.$toast.error('Cette commande a expiré. Veuillez créer une nouvelle commande.', '⏰ Commande expirée')
        }
        // Rediriger vers la page d'accueil après 2 secondes
        setTimeout(() => {
          router.push('/')
        }, 2000)
      } else {
        if (window.$toast) {
          window.$toast.error(response.message || 'Erreur lors de l\'initiation du paiement', '❌ Erreur')
        }
      }
    }
  } catch (error) {
    console.error('Payment initiation error:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de l\'initiation du paiement', '❌ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.back()
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price).replace('XAF', 'FCFA')
}

// Lifecycle
onMounted(() => {
  paymentMethod.value = route.query.method || 'airtel_money'
  amount.value = parseFloat(route.query.amount) || 0
  
  // Accepter soit order_number soit transaction_id
  orderId.value = route.query.order_number || route.query.transaction_id
  
  // Définir automatiquement l'opérateur basé sur la méthode sélectionnée
  if (paymentMethod.value === 'airtel_money') {
    selectedOperator.value = 'airtel'
  } else if (paymentMethod.value === 'moov_money') {
    selectedOperator.value = 'moov'
  }
  
  // Vérifications des paramètres requis
  if (!orderId.value) {
    if (window.$toast) {
      window.$toast.error('Paramètres de paiement manquants', '❌ Erreur')
    }
    router.push('/')
    return
  }
  
  if (!amount.value || amount.value <= 0) {
    if (window.$toast) {
      window.$toast.error('Montant invalide', '❌ Erreur')
    }
    router.push('/')
    return
  }
})
</script>