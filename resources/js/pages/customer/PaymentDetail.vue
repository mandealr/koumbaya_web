<template>
  <div class="payment-detail">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Paiement introuvable</h2>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <router-link 
        to="/customer/payments" 
        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
      >
        Retour aux paiements
      </router-link>
    </div>

    <!-- Payment Details -->
    <div v-else-if="payment">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              Paiement #{{ payment.ebilling_id || payment.id }}
            </h1>
            <p class="text-gray-600 mt-2">{{ formatDate(payment.created_at) }}</p>
          </div>
          <div class="flex items-center space-x-3">
            <span :class="getStatusBadgeClass(payment.status)">{{ getStatusText(payment.status) }}</span>
            <button
              @click="$router.go(-1)"
              class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors"
            >
              <ArrowLeftIcon class="w-4 h-4 inline mr-2" />
              Retour
            </button>
          </div>
        </div>
      </div>

      <!-- Payment Summary -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Payment Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Payment Status Timeline -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Statut du paiement</h2>
            <div class="space-y-4">
              <div class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Paiement initié</p>
                  <p class="text-sm text-gray-500">{{ formatDate(payment.created_at) }}</p>
                </div>
              </div>
              
              <div v-if="payment.paid_at" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Paiement confirmé</p>
                  <p class="text-sm text-gray-500">{{ formatDate(payment.paid_at) }}</p>
                </div>
              </div>

              <div v-if="payment.processed_at" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Paiement traité</p>
                  <p class="text-sm text-gray-500">{{ formatDate(payment.processed_at) }}</p>
                </div>
              </div>

              <div v-if="payment.status === 'pending'" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                  <ClockIcon class="w-5 h-5 text-yellow-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">En attente de confirmation</p>
                  <p class="text-sm text-gray-500">Veuillez confirmer sur votre téléphone</p>
                </div>
              </div>

              <div v-if="payment.status === 'failed' || payment.status === 'expired'" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                  <XMarkIcon class="w-5 h-5 text-red-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">
                    {{ payment.status === 'expired' ? 'Paiement expiré' : 'Paiement échoué' }}
                  </p>
                  <p v-if="payment.gateway_response && payment.gateway_response.failure_reason" 
                     class="text-sm text-gray-500">
                    {{ payment.gateway_response.failure_reason }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Method Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Méthode de paiement</h2>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                <img 
                  v-if="getPaymentLogo(payment.payment_method)"
                  :src="getPaymentLogo(payment.payment_method)"
                  :alt="getPaymentMethodText(payment.payment_method)"
                  class="w-12 h-12 object-contain"
                />
                <CreditCardIcon v-else class="w-8 h-8 text-gray-400" />
              </div>
              <div>
                <h3 class="text-lg font-medium text-gray-900">
                  {{ getPaymentMethodText(payment.payment_method) }}
                </h3>
                <p v-if="payment.customer_phone" class="text-sm text-gray-600">
                  +241 {{ payment.customer_phone }}
                </p>
                <p v-if="payment.gateway" class="text-sm text-gray-500">
                  Via {{ payment.gateway.display_name }}
                </p>
              </div>
            </div>
          </div>

          <!-- Transaction Info -->
          <div v-if="payment.transaction" class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Transaction associée</h2>
            
            <!-- Product Info -->
            <div v-if="payment.transaction.product" class="flex items-start space-x-4 mb-4">
              <div class="flex-shrink-0 w-16 h-16 bg-gray-200 rounded-lg overflow-hidden">
                <img 
                  v-if="payment.transaction.product.image"
                  :src="payment.transaction.product.image"
                  :alt="payment.transaction.product.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <ShoppingBagIcon class="w-8 h-8 text-gray-400" />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900">{{ payment.transaction.product.name }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ payment.transaction.product.description }}</p>
                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                  <span>Référence: {{ payment.transaction.reference }}</span>
                  <span v-if="payment.transaction.quantity">Quantité: {{ payment.transaction.quantity }}</span>
                </div>
              </div>
            </div>

            <!-- Lottery Info -->
            <div v-if="payment.transaction.lottery" class="border rounded-lg p-4 bg-blue-50">
              <div class="flex items-center mb-3">
                <TicketIcon class="w-5 h-5 text-blue-600 mr-2" />
                <h3 class="text-lg font-medium text-gray-900">{{ payment.transaction.lottery.title }}</h3>
              </div>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Numéro de loterie:</span>
                  <span class="font-medium ml-2">{{ payment.transaction.lottery.lottery_number }}</span>
                </div>
                <div>
                  <span class="text-gray-600">Prix du billet:</span>
                  <span class="font-medium ml-2">{{ formatPrice(payment.transaction.lottery.ticket_price) }}</span>
                </div>
              </div>
            </div>

            <!-- Tickets -->
            <div v-if="payment.transaction.tickets && payment.transaction.tickets.length > 0" class="mt-4">
              <h3 class="text-md font-medium text-gray-900 mb-3">
                Billets achetés ({{ payment.transaction.tickets.length }})
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div 
                  v-for="ticket in payment.transaction.tickets" 
                  :key="ticket.id"
                  :class="[
                    'border rounded-lg p-3 text-sm',
                    ticket.is_winner 
                      ? 'bg-green-50 border-green-200' 
                      : 'bg-gray-50 border-gray-200'
                  ]"
                >
                  <div class="flex justify-between items-center">
                    <span class="font-medium">{{ ticket.ticket_number }}</span>
                    <div class="flex items-center space-x-2">
                      <span :class="getStatusBadgeClass(ticket.status, true)">{{ getStatusText(ticket.status) }}</span>
                      <TrophyIcon v-if="ticket.is_winner" class="w-4 h-4 text-green-600" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Gateway Response -->
          <div v-if="payment.gateway_response" class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Réponse de la passerelle</h2>
            <div class="bg-gray-50 rounded-lg p-4">
              <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ JSON.stringify(payment.gateway_response, null, 2) }}</pre>
            </div>
          </div>
        </div>

        <!-- Payment Summary Sidebar -->
        <div class="space-y-6">
          <!-- Financial Summary -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Résumé du paiement</h2>
            <dl class="space-y-3">
              <div class="flex justify-between text-sm">
                <dt class="text-gray-600">Montant:</dt>
                <dd class="font-medium">{{ formatPrice(payment.amount) }}</dd>
              </div>
              <div class="flex justify-between text-sm">
                <dt class="text-gray-600">Devise:</dt>
                <dd class="font-medium">{{ payment.currency || 'XAF' }}</dd>
              </div>
              <div class="border-t pt-3">
                <div class="flex justify-between">
                  <dt class="text-base font-medium text-gray-900">Total:</dt>
                  <dd class="text-base font-medium text-gray-900">{{ formatPrice(payment.amount) }}</dd>
                </div>
              </div>
            </dl>
          </div>

          <!-- Payment Details -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails techniques</h2>
            <dl class="space-y-3 text-sm">
              <div v-if="payment.reference">
                <dt class="text-gray-600">Référence:</dt>
                <dd class="font-medium font-mono break-all">{{ payment.reference }}</dd>
              </div>
              <div v-if="payment.ebilling_id">
                <dt class="text-gray-600">ID E-Billing:</dt>
                <dd class="font-medium font-mono">{{ payment.ebilling_id }}</dd>
              </div>
              <div v-if="payment.external_transaction_id">
                <dt class="text-gray-600">ID Transaction externe:</dt>
                <dd class="font-medium font-mono break-all">{{ payment.external_transaction_id }}</dd>
              </div>
              <div v-if="payment.customer_name">
                <dt class="text-gray-600">Nom du client:</dt>
                <dd class="font-medium">{{ payment.customer_name }}</dd>
              </div>
              <div v-if="payment.customer_email">
                <dt class="text-gray-600">Email:</dt>
                <dd class="font-medium">{{ payment.customer_email }}</dd>
              </div>
            </dl>
          </div>

          <!-- Actions -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
            <div class="space-y-3">
              <button
                v-if="payment.status === 'failed' || payment.status === 'expired'"
                @click="retryPayment"
                class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors"
              >
                <ArrowPathIcon class="w-4 h-4 inline mr-2" />
                Réessayer le paiement
              </button>
              
              <button
                v-if="payment.transaction"
                @click="viewOrder"
                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
              >
                <EyeIcon class="w-4 h-4 inline mr-2" />
                Voir la commande
              </button>
              
              <button
                @click="downloadReceipt"
                class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors"
              >
                <DocumentArrowDownIcon class="w-4 h-4 inline mr-2" />
                Télécharger le reçu
              </button>
              
              <router-link
                to="/customer/payments"
                class="w-full block text-center bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors"
              >
                Voir tous les paiements
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { useToast } from '@/composables/useToast'
import {
  ArrowLeftIcon,
  CheckIcon,
  ClockIcon,
  XMarkIcon,
  CreditCardIcon,
  ShoppingBagIcon,
  TicketIcon,
  TrophyIcon,
  ArrowPathIcon,
  DocumentArrowDownIcon,
  EyeIcon
} from '@heroicons/vue/24/outline'
import airtelLogo from '@/assets/am.png'
import moovLogo from '@/assets/mm.png'

const route = useRoute()
const router = useRouter()
const { get: apiGet } = useApi()
const { showError, showSuccess } = useToast()

// État
const payment = ref(null)
const loading = ref(true)
const error = ref(null)

// Charger les détails du paiement
const loadPayment = async () => {
  try {
    loading.value = true
    const paymentId = route.params.id
    
    const response = await apiGet(`/payments/${paymentId}`)
    if (response.success) {
      payment.value = response.data
    } else {
      error.value = response.message || 'Paiement non trouvé'
    }
  } catch (err) {
    console.error('Erreur lors du chargement du paiement:', err)
    error.value = 'Erreur lors du chargement du paiement'
  } finally {
    loading.value = false
  }
}

// Actions
const retryPayment = () => {
  if (payment.value.transaction) {
    router.push({
      name: 'payment.phone',
      query: {
        transaction_id: payment.value.transaction.transaction_id,
        retry: 'true'
      }
    })
  }
}

const viewOrder = () => {
  if (payment.value.transaction) {
    router.push({
      name: 'customer.order.detail',
      params: { id: payment.value.transaction.transaction_id }
    })
  }
}

const downloadReceipt = () => {
  // TODO: Implémenter le téléchargement de reçu
  showSuccess('Fonctionnalité de téléchargement en cours de développement')
}

// Fonctions utilitaires
const getStatusText = (status) => {
  const statusMap = {
    'paid': 'Payé',
    'pending': 'En attente',
    'failed': 'Échoué',
    'expired': 'Expiré',
    'processed': 'Traité'
  }
  return statusMap[status] || status
}

const getPaymentMethodText = (method) => {
  const methodMap = {
    'airtel_money': 'Airtel Money',
    'moov_money': 'Moov Money'
  }
  return methodMap[method] || method || 'Non spécifié'
}

const getPaymentLogo = (method) => {
  const logoMap = {
    'airtel_money': airtelLogo,
    'moov_money': moovLogo
  }
  return logoMap[method]
}

const getStatusBadgeClass = (status, small = false) => {
  const baseClass = small ? 'px-1 py-0.5 text-xs' : 'px-2 py-1 text-xs'
  const statusClasses = {
    'paid': 'bg-green-100 text-green-800',
    'processed': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'failed': 'bg-red-100 text-red-800',
    'expired': 'bg-gray-100 text-gray-800'
  }
  return `${baseClass} rounded-full font-medium ${statusClasses[status] || 'bg-gray-100 text-gray-800'}`
}

const formatDate = (dateString) => {
  if (!dateString) return 'Non défini'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price || 0).replace('XAF', 'FCFA')
}

// Lifecycle
onMounted(() => {
  loadPayment()
})
</script>

<style scoped>
.payment-detail {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>