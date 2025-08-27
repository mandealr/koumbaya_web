<template>
  <div class="payments-page">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Mes Paiements</h1>
      <p class="text-gray-600">Consultez l'historique de vos paiements et transactions</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Paiements Réussis</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.successful_payments }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-yellow-100">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">En Attente</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.pending_payments }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-red-100">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Échoués</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.failed_payments }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Payé</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatPrice(stats.total_amount) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
          <input 
            type="text" 
            v-model="filters.search"
            placeholder="Référence ou ID de paiement..." 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @keyup.enter="loadPayments(1)"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les statuts</option>
            <option value="paid">Payé</option>
            <option value="pending">En attente</option>
            <option value="failed">Échoué</option>
            <option value="expired">Expiré</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Méthode</label>
          <select 
            v-model="filters.method"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Toutes les méthodes</option>
            <option value="airtel_money">Airtel Money</option>
            <option value="moov_money">Moov Money</option>
          </select>
        </div>
        
        <div class="flex items-end">
          <button 
            @click="loadPayments(1)"
            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200"
          >
            Rechercher
          </button>
        </div>
      </div>
    </div>

    <!-- Liste des paiements -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Historique des paiements</h2>
      </div>
      
      <!-- Loading -->
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-500 mt-4">Chargement des paiements...</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="payments.length === 0" class="p-8 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p class="text-gray-500">Aucun paiement trouvé</p>
      </div>

      <!-- Payments list -->
      <div v-else>
        <div 
          v-for="payment in payments" 
          :key="payment.id"
          class="border-b border-gray-200 hover:bg-gray-50 transition duration-200"
        >
          <div class="px-6 py-4">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <h3 class="text-lg font-medium text-gray-900">#{{ payment.billing_id || payment.id }}</h3>
                  <span :class="getStatusBadgeClass(payment.status)">{{ getStatusText(payment.status) }}</span>
                  <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">{{ getMethodText(payment.payment_method) }}</span>
                </div>
                
                <div class="text-sm text-gray-600 mb-2">
                  <span v-if="payment.transaction && payment.transaction.product">
                    {{ payment.transaction.product.name }}
                  </span>
                  <span v-else-if="payment.transaction && payment.transaction.lottery">
                    Loterie #{{ payment.transaction.lottery.lottery_number }}
                  </span>
                  <span v-else>
                    Paiement
                  </span>
                </div>
                
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <span>{{ formatDate(payment.created_at) }}</span>
                  <span>{{ formatPrice(payment.amount) }}</span>
                  <span v-if="payment.phone_number">{{ payment.phone_number }}</span>
                </div>

                <!-- Timeout info pour les paiements en attente -->
                <div v-if="payment.status === 'pending' && payment.timeout_at" class="mt-2 text-xs text-orange-600">
                  Expire le {{ formatDate(payment.timeout_at) }}
                </div>
              </div>
              
              <div class="flex space-x-2">
                <router-link 
                  :to="{ name: 'customer.payment.detail', params: { id: payment.id } }"
                  class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                >
                  Détails
                </router-link>
                
                <!-- Bouton relancer pour les paiements expirés -->
                <button 
                  v-if="payment.status === 'expired' && payment.transaction_id"
                  @click="retryPayment(payment.transaction_id)"
                  class="bg-orange-600 text-white px-3 py-1 rounded text-sm hover:bg-orange-700"
                >
                  Relancer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Pagination -->
      <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-600">
            Affichage de {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} à 
            {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} 
            sur {{ pagination.total }} résultats
          </div>
          
          <div class="flex space-x-1">
            <button 
              @click="loadPayments(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              Précédent
            </button>
            
            <button
              v-for="page in getPageNumbers()"
              :key="page"
              @click="loadPayments(page)"
              :class="[
                'px-3 py-1 border rounded',
                page === pagination.current_page 
                  ? 'bg-blue-600 text-white' 
                  : 'text-gray-700 hover:bg-gray-100'
              ]"
            >
              {{ page }}
            </button>
            
            <button 
              @click="loadPayments(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              Suivant
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de détails -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Détails du paiement</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div v-if="selectedPayment">
            <div class="grid grid-cols-1 gap-6">
              <div>
                <h4 class="text-md font-medium text-gray-900 mb-3">Informations du paiement</h4>
                <dl class="space-y-2">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">ID de paiement:</dt>
                    <dd class="text-sm font-medium">{{ selectedPayment.billing_id || selectedPayment.id }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Statut:</dt>
                    <dd><span :class="getStatusBadgeClass(selectedPayment.status)">{{ getStatusText(selectedPayment.status) }}</span></dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Montant:</dt>
                    <dd class="text-sm font-medium">{{ formatPrice(selectedPayment.amount) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Méthode:</dt>
                    <dd class="text-sm">{{ getMethodText(selectedPayment.payment_method) }}</dd>
                  </div>
                  <div v-if="selectedPayment.phone_number" class="flex justify-between">
                    <dt class="text-sm text-gray-600">Téléphone:</dt>
                    <dd class="text-sm">+241 {{ selectedPayment.phone_number }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Date de création:</dt>
                    <dd class="text-sm">{{ formatDate(selectedPayment.created_at) }}</dd>
                  </div>
                  <div v-if="selectedPayment.updated_at" class="flex justify-between">
                    <dt class="text-sm text-gray-600">Dernière mise à jour:</dt>
                    <dd class="text-sm">{{ formatDate(selectedPayment.updated_at) }}</dd>
                  </div>
                  <div v-if="selectedPayment.timeout_at" class="flex justify-between">
                    <dt class="text-sm text-gray-600">Expiration:</dt>
                    <dd class="text-sm">{{ formatDate(selectedPayment.timeout_at) }}</dd>
                  </div>
                </dl>
              </div>
              
              <!-- Transaction associée -->
              <div v-if="selectedPayment.transaction">
                <h4 class="text-md font-medium text-gray-900 mb-3">Transaction associée</h4>
                <div class="border rounded-lg p-4 bg-gray-50">
                  <p class="font-medium">{{ selectedPayment.transaction.reference }}</p>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ selectedPayment.transaction.description || 'Aucune description' }}
                  </p>
                  <div class="mt-2 text-xs text-gray-500">
                    Type: {{ getTransactionTypeText(selectedPayment.transaction.type) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const { apiRequest } = useApi()
const { showError, showSuccess } = useToast()

// État réactif
const payments = ref([])
const stats = ref({
  successful_payments: 0,
  pending_payments: 0,
  failed_payments: 0,
  total_amount: 0
})
const pagination = ref(null)
const loading = ref(false)
const showModal = ref(false)
const selectedPayment = ref(null)

// Filtres
const filters = reactive({
  search: '',
  status: '',
  method: ''
})

// Charger les statistiques
const loadStats = async () => {
  try {
    const response = await apiRequest('/api/payments/stats', 'GET')
    if (response.success) {
      stats.value = response.data
    }
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques:', error)
    // Fallback sur les stats des commandes
    try {
      const fallbackResponse = await apiRequest('/api/orders/stats', 'GET')
      if (fallbackResponse.success) {
        stats.value = {
          successful_payments: fallbackResponse.data.completed_orders,
          pending_payments: fallbackResponse.data.pending_orders,
          failed_payments: fallbackResponse.data.failed_orders,
          total_amount: fallbackResponse.data.total_amount_spent || 0
        }
      }
    } catch (fallbackError) {
      console.error('Erreur lors du chargement des statistiques de fallback:', fallbackError)
    }
  }
}

// Charger les paiements
const loadPayments = async (page = 1) => {
  loading.value = true
  
  try {
    const params = new URLSearchParams({
      page: page,
      per_page: 10
    })

    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    if (filters.method) params.append('method', filters.method)

    // Essayer d'abord l'API des paiements
    try {
      const response = await apiRequest(`/api/payments?${params.toString()}`, 'GET')
      if (response.success) {
        payments.value = response.data
        pagination.value = response.pagination
      }
    } catch (paymentsError) {
      console.log('API paiements non disponible, utilisation de l\'API commandes:', paymentsError)
      
      // Fallback sur l'API des transactions
      const response = await apiRequest(`/api/orders?${params.toString()}`, 'GET')
      if (response.success) {
        // Transformer les données pour ressembler à des paiements
        payments.value = response.data.map(transaction => ({
          id: transaction.id,
          billing_id: transaction.external_transaction_id,
          amount: transaction.amount,
          status: transaction.status === 'completed' ? 'paid' : transaction.status,
          payment_method: transaction.payment_method || 'airtel_money',
          phone_number: transaction.phone_number,
          transaction_id: transaction.transaction_id,
          transaction: transaction,
          created_at: transaction.created_at,
          updated_at: transaction.updated_at,
          timeout_at: transaction.status === 'pending' ? transaction.expires_at : null
        }))
        pagination.value = response.pagination
      }
    }
  } catch (error) {
    showError('Erreur lors du chargement des paiements')
    console.error('Erreur:', error)
  } finally {
    loading.value = false
  }
}

// Voir les détails d'un paiement
const viewPaymentDetails = (payment) => {
  selectedPayment.value = payment
  showModal.value = true
}

// Relancer un paiement
const retryPayment = (transactionId) => {
  router.push({
    name: 'payment.phone',
    query: {
      transaction_id: transactionId,
      retry: 'true'
    }
  })
}

// Fermer le modal
const closeModal = () => {
  showModal.value = false
  selectedPayment.value = null
}

// Fonctions utilitaires
const getStatusText = (status) => {
  const statusMap = {
    'paid': 'Payé',
    'pending': 'En attente',
    'payment_initiated': 'Paiement initié',
    'failed': 'Échoué',
    'expired': 'Expiré',
    'completed': 'Payé'
  }
  return statusMap[status] || status
}

const getMethodText = (method) => {
  const methodMap = {
    'airtel_money': 'Airtel Money',
    'moov_money': 'Moov Money'
  }
  return methodMap[method] || method || 'Non spécifié'
}

const getTransactionTypeText = (type) => {
  const typeMap = {
    'ticket_purchase': 'Achat de billet',
    'direct_purchase': 'Achat direct'
  }
  return typeMap[type] || type
}

const getStatusBadgeClass = (status) => {
  const baseClass = 'px-2 py-1 text-xs rounded-full font-medium'
  const statusClasses = {
    'paid': 'bg-green-100 text-green-800',
    'completed': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'payment_initiated': 'bg-blue-100 text-blue-800',
    'failed': 'bg-red-100 text-red-800',
    'expired': 'bg-gray-100 text-gray-800'
  }
  return `${baseClass} ${statusClasses[status] || 'bg-gray-100 text-gray-800'}`
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
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

const getPageNumbers = () => {
  if (!pagination.value) return []
  
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const pages = []
  
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }
  
  return pages
}

// Watchers
watch(() => [filters.status, filters.method], () => {
  loadPayments(1)
}, { deep: true })

// Lifecycle
onMounted(() => {
  loadStats()
  loadPayments()
})
</script>

<style scoped>
.payments-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>