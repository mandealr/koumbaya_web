<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="mb-6">
          <router-link to="/">
            <img :src="logoUrl" alt="Koumbaya" class="h-12 mx-auto hover:opacity-80 transition-opacity" />
          </router-link>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Méthode de paiement</h1>
        <p class="mt-2 text-gray-600">Choisissez votre mode de paiement préféré</p>
      </div>

      <!-- Résumé de la commande -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Résumé de votre commande</h2>
        
        <div class="flex items-center space-x-4 mb-4">
          <img 
            :src="orderSummary.image || '/images/products/placeholder.jpg'" 
            :alt="orderSummary.productName"
            class="w-16 h-16 object-cover rounded-lg"
          />
          <div class="flex-1">
            <h3 class="font-medium text-gray-900">{{ orderSummary.productName }}</h3>
            <p class="text-sm text-gray-600">
              <span v-if="orderSummary.type === 'ticket'">
                {{ orderSummary.quantity }} ticket(s) • {{ formatPrice(orderSummary.unitPrice) }}/ticket
              </span>
              <span v-else>
                Achat direct • {{ formatPrice(orderSummary.unitPrice) }}
              </span>
            </p>
          </div>
          <div class="text-right">
            <div class="text-lg font-bold text-gray-900">{{ formatPrice(orderSummary.totalAmount) }}</div>
            <div v-if="orderSummary.type === 'ticket'" class="text-sm text-gray-500">
              {{ orderSummary.quantity }} x {{ formatPrice(orderSummary.unitPrice) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Méthodes de paiement -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Sélectionnez votre méthode de paiement</h2>
        
        <div class="space-y-4">
          <!-- Mobile Money -->
          <div class="space-y-3">
            <h3 class="text-sm font-medium text-gray-700 flex items-center">
              <PhoneIcon class="h-5 w-5 mr-2 text-blue-500" />
              Mobile Money
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <!-- Airtel Money -->
              <div 
                @click="selectPaymentMethod('airtel_money')"
                :class="[
                  'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200',
                  selectedMethod === 'airtel_money' 
                    ? 'border-red-500 bg-red-50 ring-2 ring-red-200' 
                    : 'border-gray-200 hover:border-red-300 hover:bg-red-50'
                ]"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 rounded-lg overflow-hidden flex items-center justify-center bg-white border border-gray-200">
                    <img 
                      :src="airtelLogo" 
                      alt="Airtel Money" 
                      class="w-10 h-10 object-contain"
                    />
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-gray-900">Airtel Money</div>
                    <div class="text-sm text-gray-500">Paiement mobile</div>
                  </div>
                  <div class="ml-auto">
                    <div :class="[
                      'w-4 h-4 rounded-full border-2',
                      selectedMethod === 'airtel_money' 
                        ? 'border-red-500 bg-red-500' 
                        : 'border-gray-300'
                    ]">
                      <div v-if="selectedMethod === 'airtel_money'" 
                           class="w-full h-full rounded-full bg-white transform scale-50">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Moov Money -->
              <div 
                @click="selectPaymentMethod('moov_money')"
                :class="[
                  'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200',
                  selectedMethod === 'moov_money' 
                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200' 
                    : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50'
                ]"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 rounded-lg overflow-hidden flex items-center justify-center bg-white border border-gray-200">
                    <img 
                      :src="moovLogo" 
                      alt="Moov Money" 
                      class="w-10 h-10 object-contain"
                    />
                  </div>
                  <div class="flex-1">
                    <div class="font-medium text-gray-900">Moov Money</div>
                    <div class="text-sm text-gray-500">Paiement mobile</div>
                  </div>
                  <div class="ml-auto">
                    <div :class="[
                      'w-4 h-4 rounded-full border-2',
                      selectedMethod === 'moov_money' 
                        ? 'border-blue-500 bg-blue-500' 
                        : 'border-gray-300'
                    ]">
                      <div v-if="selectedMethod === 'moov_money'" 
                           class="w-full h-full rounded-full bg-white transform scale-50">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Autres méthodes (pour plus tard) -->
          <div class="pt-4 border-t border-gray-200">
            <h3 class="text-sm font-medium text-gray-400 mb-3">Bientôt disponible</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 opacity-50">
              <div class="p-4 border-2 border-gray-100 rounded-lg bg-gray-50">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-gray-300 rounded-lg flex items-center justify-center">
                    <CreditCardIcon class="h-6 w-6 text-gray-500" />
                  </div>
                  <div>
                    <div class="font-medium text-gray-500">Carte bancaire</div>
                    <div class="text-sm text-gray-400">Visa, Mastercard</div>
                  </div>
                </div>
              </div>
              
              <div class="p-4 border-2 border-gray-100 rounded-lg bg-gray-50">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-gray-300 rounded-lg flex items-center justify-center">
                    <BuildingLibraryIcon class="h-6 w-6 text-gray-500" />
                  </div>
                  <div>
                    <div class="font-medium text-gray-500">Virement bancaire</div>
                    <div class="text-sm text-gray-400">IBAN, SWIFT</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bouton Continuer -->
        <div class="mt-8 flex items-center justify-between">
          <button 
            @click="goBack" 
            class="flex items-center text-gray-600 hover:text-gray-900 transition-colors"
          >
            <ArrowLeftIcon class="h-5 w-5 mr-2" />
            Retour
          </button>
          
          <button 
            @click="proceedToPayment"
            :disabled="!selectedMethod || loading"
            class="bg-[#0099cc] hover:bg-[#0088bb] disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold px-8 py-3 rounded-xl transition-all duration-200 flex items-center"
          >
            <span v-if="loading">Traitement...</span>
            <span v-else>
              Continuer
              <ArrowRightIcon class="h-5 w-5 ml-2" />
            </span>
          </button>
        </div>
      </div>

      <!-- Sécurité -->
      <div class="mt-6 text-center">
        <div class="flex items-center justify-center text-sm text-gray-500">
          <ShieldCheckIcon class="h-5 w-5 mr-2 text-green-500" />
          Paiement 100% sécurisé et chiffré
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { 
  PhoneIcon, 
  CreditCardIcon,
  BuildingLibraryIcon,
  ArrowLeftIcon,
  ArrowRightIcon,
  ShieldCheckIcon
} from '@heroicons/vue/24/outline'
import logoUrl from '@/assets/logo.png'
import airtelLogo from '@/assets/am.png'
import moovLogo from '@/assets/mm.png'

const route = useRoute()
const router = useRouter()

// State
const selectedMethod = ref('')
const loading = ref(false)
const orderSummary = ref({
  type: 'ticket', // 'ticket' ou 'product'
  productName: '',
  quantity: 1,
  unitPrice: 0,
  totalAmount: 0,
  image: '',
  transactionId: null
})

// Methods
const selectPaymentMethod = (method) => {
  selectedMethod.value = method
}

const goBack = () => {
  router.back()
}

const proceedToPayment = () => {
  if (!selectedMethod.value) return
  
  loading.value = true
  
  // Rediriger vers la page de saisie du numéro
  router.push({
    name: 'payment.phone',
    query: {
      method: selectedMethod.value,
      order_number: orderSummary.value.orderNumber || orderSummary.value.transactionId,
      amount: orderSummary.value.totalAmount,
      type: orderSummary.value.type // Passer le type de transaction
    }
  })
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
  // Récupérer les données de la commande depuis les query params ou le store
  const { type, product, quantity, amount, transaction_id, order_number } = route.query
  
  // Accepter soit transaction_id (ancien système) soit order_number (nouveau système)
  const orderRef = order_number || transaction_id
  
  if (!orderRef) {
    router.push('/') // Rediriger si pas de commande
    return
  }
  
  orderSummary.value = {
    type: type || 'ticket',
    productName: product || 'Produit',
    quantity: parseInt(quantity) || 1,
    unitPrice: parseFloat(amount) / (parseInt(quantity) || 1),
    totalAmount: parseFloat(amount) || 0,
    image: '', // À récupérer via API
    transactionId: transaction_id, // Garde pour compatibilité
    orderNumber: order_number // Nouveau champ
  }
})
</script>