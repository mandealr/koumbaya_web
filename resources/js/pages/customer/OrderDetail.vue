<template>
  <div class="order-detail">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Commande introuvable</h2>
      <p class="text-gray-600 mb-4">{{ error }}</p>
      <router-link 
        to="/customer/orders" 
        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
      >
        Retour aux commandes
      </router-link>
    </div>

    <!-- Order Details -->
    <div v-else-if="order">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Commande #{{ order.transaction_id }}</h1>
            <p class="text-gray-600 mt-2">{{ formatDate(order.created_at) }}</p>
          </div>
          <div class="flex items-center space-x-3">
            <span :class="getStatusBadgeClass(order.status)">{{ getStatusText(order.status) }}</span>
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

      <!-- Order Summary -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Order Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Order Status Timeline -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Statut de la commande</h2>
            <div class="space-y-4">
              <div class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Commande créée</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                </div>
              </div>
              
              <div v-if="order.paid_at || order.completed_at" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Paiement confirmé</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.completed_at || order.paid_at) }}</p>
                </div>
              </div>

              <div v-if="order.status === 'pending'" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                  <ClockIcon class="w-5 h-5 text-yellow-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">En attente de paiement</p>
                  <p class="text-sm text-gray-500">Expire le {{ formatDate(order.expires_at) }}</p>
                </div>
              </div>

              <div v-if="order.status === 'failed'" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                  <XMarkIcon class="w-5 h-5 text-red-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Paiement échoué</p>
                  <p class="text-sm text-gray-500">{{ order.failure_reason || 'Raison non spécifiée' }}</p>
                  <p v-if="order.failed_at" class="text-sm text-gray-500">{{ formatDate(order.failed_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Product/Lottery Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails de l'achat</h2>
            
            <!-- Product Info -->
            <div v-if="order.product" class="flex items-start space-x-4 mb-6">
              <div class="flex-shrink-0 w-16 h-16 bg-gray-200 rounded-lg overflow-hidden">
                <img 
                  v-if="order.product.image"
                  :src="order.product.image"
                  :alt="order.product.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <ShoppingBagIcon class="w-8 h-8 text-gray-400" />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900">{{ order.product.name }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ order.product.description }}</p>
                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                  <span>Mode: {{ getSaleModeText(order.product.sale_mode) }}</span>
                  <span v-if="order.quantity">Quantité: {{ order.quantity }}</span>
                </div>
              </div>
            </div>

            <!-- Lottery Info -->
            <div v-if="order.lottery" class="border rounded-lg p-4 bg-blue-50">
              <div class="flex items-center mb-3">
                <TicketIcon class="w-5 h-5 text-blue-600 mr-2" />
                <h3 class="text-lg font-medium text-gray-900">{{ order.lottery.title }}</h3>
              </div>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Numéro de loterie:</span>
                  <span class="font-medium ml-2">{{ order.lottery.lottery_number }}</span>
                </div>
                <div>
                  <span class="text-gray-600">Prix du billet:</span>
                  <span class="font-medium ml-2">{{ formatPrice(order.lottery.ticket_price) }}</span>
                </div>
                <div>
                  <span class="text-gray-600">Billets vendus:</span>
                  <span class="font-medium ml-2">{{ order.lottery.sold_tickets }}/{{ order.lottery.max_tickets }}</span>
                </div>
                <div v-if="order.lottery.draw_date">
                  <span class="text-gray-600">Date du tirage:</span>
                  <span class="font-medium ml-2">{{ formatDate(order.lottery.draw_date) }}</span>
                </div>
              </div>
              
              <!-- Winner Info -->
              <div v-if="order.lottery.winner_ticket_number" class="mt-3 p-3 bg-green-100 rounded-lg">
                <div class="flex items-center">
                  <TrophyIcon class="w-5 h-5 text-green-600 mr-2" />
                  <span class="text-sm font-medium text-green-800">
                    Billet gagnant: {{ order.lottery.winner_ticket_number }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Tickets -->
          <div v-if="order.tickets && order.tickets.length > 0" class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
              Billets de loterie ({{ order.tickets.length }})
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div 
                v-for="ticket in order.tickets" 
                :key="ticket.id"
                :class="[
                  'border rounded-lg p-4 transition-colors',
                  ticket.is_winner 
                    ? 'bg-green-50 border-green-200 ring-2 ring-green-500' 
                    : 'bg-gray-50 border-gray-200'
                ]"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <p class="font-medium text-gray-900">{{ ticket.ticket_number }}</p>
                    <p class="text-sm text-gray-600">{{ formatPrice(ticket.price_paid) }}</p>
                  </div>
                  <div class="text-right">
                    <span :class="getStatusBadgeClass(ticket.status)">{{ getStatusText(ticket.status) }}</span>
                    <div v-if="ticket.is_winner" class="mt-1">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <TrophyIcon class="w-3 h-3 mr-1" />
                        Gagnant
                      </span>
                    </div>
                  </div>
                </div>
                <div v-if="ticket.purchased_at" class="mt-2 text-xs text-gray-500">
                  Acheté le {{ formatDate(ticket.purchased_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="space-y-6">
          <!-- Financial Summary -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Résumé financier</h2>
            <dl class="space-y-3">
              <div class="flex justify-between text-sm">
                <dt class="text-gray-600">Sous-total:</dt>
                <dd class="font-medium">{{ formatPrice(order.amount) }}</dd>
              </div>
              <div class="border-t pt-3">
                <div class="flex justify-between">
                  <dt class="text-base font-medium text-gray-900">Total:</dt>
                  <dd class="text-base font-medium text-gray-900">{{ formatPrice(order.amount) }}</dd>
                </div>
              </div>
            </dl>
          </div>

          <!-- Payment Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations de paiement</h2>
            <dl class="space-y-3 text-sm">
              <div>
                <dt class="text-gray-600">Référence:</dt>
                <dd class="font-medium font-mono">{{ order.reference }}</dd>
              </div>
              <div v-if="order.payment_method">
                <dt class="text-gray-600">Méthode:</dt>
                <dd class="font-medium">{{ getPaymentMethodText(order.payment_method) }}</dd>
              </div>
              <div v-if="order.phone_number">
                <dt class="text-gray-600">Téléphone:</dt>
                <dd class="font-medium">+241 {{ order.phone_number }}</dd>
              </div>
            </dl>
          </div>

          <!-- Actions -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
            <div class="space-y-3">
              <button
                v-if="order.status === 'failed'"
                @click="retryOrder"
                class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors"
              >
                <ArrowPathIcon class="w-4 h-4 inline mr-2" />
                Réessayer le paiement
              </button>
              
              <button
                @click="downloadInvoice"
                class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors"
              >
                <DocumentArrowDownIcon class="w-4 h-4 inline mr-2" />
                Télécharger le reçu
              </button>
              
              <router-link
                to="/customer/orders"
                class="w-full block text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
              >
                Voir toutes les commandes
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
  ShoppingBagIcon,
  TicketIcon,
  TrophyIcon,
  ArrowPathIcon,
  DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const { apiRequest } = useApi()
const { showError, showSuccess } = useToast()

// État
const order = ref(null)
const loading = ref(true)
const error = ref(null)

// Charger les détails de la commande
const loadOrder = async () => {
  try {
    loading.value = true
    const transactionId = route.params.id
    
    const response = await apiRequest(`/api/orders/${transactionId}`, 'GET')
    if (response.success) {
      order.value = response.data
    } else {
      error.value = response.message || 'Commande non trouvée'
    }
  } catch (err) {
    console.error('Erreur lors du chargement de la commande:', err)
    error.value = 'Erreur lors du chargement de la commande'
  } finally {
    loading.value = false
  }
}

// Réessayer une commande échouée
const retryOrder = () => {
  router.push({
    name: 'payment.phone',
    query: {
      transaction_id: order.value.transaction_id,
      retry: 'true'
    }
  })
}

// Télécharger le reçu
const downloadInvoice = () => {
  // TODO: Implémenter le téléchargement de reçu
  showSuccess('Fonctionnalité de téléchargement en cours de développement')
}

// Fonctions utilitaires
const getStatusText = (status) => {
  const statusMap = {
    'completed': 'Complété',
    'pending': 'En attente',
    'payment_initiated': 'Paiement initié',
    'failed': 'Échoué',
    'paid': 'Payé',
    'refunded': 'Remboursé'
  }
  return statusMap[status] || status
}

const getSaleModeText = (mode) => {
  const modeMap = {
    'lottery': 'Loterie',
    'direct': 'Achat direct'
  }
  return modeMap[mode] || mode
}

const getPaymentMethodText = (method) => {
  const methodMap = {
    'airtel_money': 'Airtel Money',
    'moov_money': 'Moov Money'
  }
  return methodMap[method] || method || 'Non spécifié'
}

const getStatusBadgeClass = (status) => {
  const baseClass = 'px-2 py-1 text-xs rounded-full font-medium'
  const statusClasses = {
    'completed': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'payment_initiated': 'bg-blue-100 text-blue-800',
    'failed': 'bg-red-100 text-red-800',
    'paid': 'bg-green-100 text-green-800',
    'refunded': 'bg-gray-100 text-gray-800'
  }
  return `${baseClass} ${statusClasses[status] || 'bg-gray-100 text-gray-800'}`
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
  loadOrder()
})
</script>

<style scoped>
.order-detail {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>