<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="mb-6">
          <img :src="logoUrl" alt="Koumbaya" class="h-12 mx-auto" />
        </div>
        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
             :class="paymentMethod === 'airtel_money' ? 'bg-red-100' : 'bg-blue-100'">
          <PhoneIcon :class="[
            'h-8 w-8',
            paymentMethod === 'airtel_money' ? 'text-red-600' : 'text-blue-600'
          ]" />
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
          <!-- Opérateur -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Opérateur</label>
            <div class="grid grid-cols-2 gap-3">
              <div 
                @click="selectedOperator = 'airtel'"
                :class="[
                  'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 flex items-center space-x-3',
                  selectedOperator === 'airtel' 
                    ? 'border-red-500 bg-red-50 ring-2 ring-red-200' 
                    : 'border-gray-200 hover:border-red-300'
                ]"
              >
                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                  <span class="text-xs font-bold text-white">A</span>
                </div>
                <span class="font-medium text-gray-900">Airtel</span>
                <div class="ml-auto">
                  <div :class="[
                    'w-4 h-4 rounded-full border-2',
                    selectedOperator === 'airtel' 
                      ? 'border-red-500 bg-red-500' 
                      : 'border-gray-300'
                  ]">
                    <div v-if="selectedOperator === 'airtel'" 
                         class="w-full h-full rounded-full bg-white transform scale-50">
                    </div>
                  </div>
                </div>
              </div>

              <div 
                @click="selectedOperator = 'moov'"
                :class="[
                  'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 flex items-center space-x-3',
                  selectedOperator === 'moov' 
                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' 
                    : 'border-gray-200 hover:border-blue-300'
                ]"
              >
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                  <span class="text-xs font-bold text-white">M</span>
                </div>
                <span class="font-medium text-gray-900">Moov</span>
                <div class="ml-auto">
                  <div :class="[
                    'w-4 h-4 rounded-full border-2',
                    selectedOperator === 'moov' 
                      ? 'border-blue-500 bg-blue-500' 
                      : 'border-gray-300'
                  ]">
                    <div v-if="selectedOperator === 'moov'" 
                         class="w-full h-full rounded-full bg-white transform scale-50">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Numéro de téléphone -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Numéro de téléphone
            </label>
            <input
              v-model="phoneNumber"
              type="tel"
              :placeholder="selectedOperator === 'airtel' ? '74010203' : '65010203'"
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
              Format: {{ selectedOperator === 'airtel' ? '74010203 (74, 77, 76 pour Airtel)' : '65010203 (65, 62, 66, 60 pour Moov)' }}
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
              <span v-if="loading">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                Traitement...
              </span>
              <span v-else>
                Payer {{ formatPrice(amount) }}
                <ArrowRightIcon class="h-5 w-5 ml-2" />
              </span>
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
        <div class="flex items-start">
          <InformationCircleIcon class="h-5 w-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" />
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
    return cleanPhone.startsWith('74') || cleanPhone.startsWith('77') || cleanPhone.startsWith('76')
  } else if (selectedOperator.value === 'moov') {
    return cleanPhone.startsWith('65') || cleanPhone.startsWith('62') || cleanPhone.startsWith('66') || cleanPhone.startsWith('60')
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
    const prefix = selectedOperator.value === 'airtel' ? '74, 77 ou 76' : '65, 62, 66 ou 60'
    errors.value.phone = `Le numéro doit commencer par ${prefix} pour ${selectedOperator.value === 'airtel' ? 'Airtel' : 'Moov'}`
    return false
  }
  
  return true
}

const processPayment = async () => {
  if (!validatePhone() || !isFormValid.value) return
  
  loading.value = true
  
  try {
    // Utiliser le nouvel endpoint pour créer le paiement depuis une transaction existante
    const response = await post('/payments/initiate-from-transaction', {
      transaction_id: route.query.transaction_id,
      phone: phoneNumber.value,
      operator: selectedOperator.value
    })
    
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
          transaction_id: response.data.transaction_id
        }
      })
    } else {
      if (window.$toast) {
        window.$toast.error(response.message || 'Erreur lors de l\'initiation du paiement', '❌ Erreur')
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
  orderId.value = route.query.transaction_id
  
  // Définir l'opérateur par défaut basé sur la méthode
  if (paymentMethod.value === 'airtel_money') {
    selectedOperator.value = 'airtel'
  } else if (paymentMethod.value === 'moov_money') {
    selectedOperator.value = 'moov'
  }
  
  if (!route.query.transaction_id) {
    router.push('/')
  }
})
</script>