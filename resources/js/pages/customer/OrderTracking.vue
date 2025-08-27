<template>
  <div class="order-tracking">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Suivi de mes commandes</h1>
      <p class="text-gray-600">Suivez l'état de vos commandes et billets de loterie</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Commandes</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_orders }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Complétées</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.completed_orders }}</p>
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
            <p class="text-sm font-medium text-gray-600">En attente</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.pending_orders }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Billets Achetés</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_tickets_purchased }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
          <input 
            type="text" 
            v-model="filters.search"
            placeholder="Numéro de commande ou ticket..." 
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            @keyup.enter="loadOrders(1)"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les statuts</option>
            <option value="completed">Complété</option>
            <option value="pending">En attente</option>
            <option value="payment_initiated">Paiement initié</option>
            <option value="failed">Échoué</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
          <select 
            v-model="filters.type"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les types</option>
            <option value="ticket_purchase">Achat de billet</option>
            <option value="direct_purchase">Achat direct</option>
          </select>
        </div>
        
        <div class="flex items-end">
          <button 
            @click="loadOrders(1)"
            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200"
          >
            Rechercher
          </button>
        </div>
      </div>
    </div>

    <!-- Liste des commandes -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Mes commandes</h2>
      </div>
      
      <!-- Loading -->
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-500 mt-4">Chargement des commandes...</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="orders.length === 0" class="p-8 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 009.586 13H7"></path>
        </svg>
        <p class="text-gray-500">Aucune commande trouvée</p>
      </div>

      <!-- Orders list -->
      <div v-else>
        <div 
          v-for="order in orders" 
          :key="order.id"
          class="border-b border-gray-200 hover:bg-gray-50 transition duration-200"
        >
          <div class="px-6 py-4">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <h3 class="text-lg font-medium text-gray-900">#{{ order.transaction_id }}</h3>
                  <span :class="getStatusBadgeClass(order.status)">{{ getStatusText(order.status) }}</span>
                  <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ getTypeText(order.type) }}</span>
                </div>
                
                <div class="text-sm text-gray-600 mb-2">
                  {{ order.product ? order.product.name : 'Produit supprimé' }}
                  <span v-if="order.lottery"> - Loterie #{{ order.lottery.lottery_number }}</span>
                </div>
                
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <span>{{ formatDate(order.created_at) }}</span>
                  <span>{{ order.amount }} {{ order.currency }}</span>
                  <span v-if="order.quantity">{{ order.quantity }} billet(s)</span>
                </div>
              </div>
              
              <div class="flex space-x-2">
                <button 
                  @click="viewOrderDetails(order.transaction_id)"
                  class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700"
                >
                  Détails
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
              @click="loadOrders(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-1 border rounded text-gray-700 hover:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              Précédent
            </button>
            
            <button
              v-for="page in getPageNumbers()"
              :key="page"
              @click="loadOrders(page)"
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
              @click="loadOrders(pagination.current_page + 1)"
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
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Détails de la commande</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div v-if="selectedOrder">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 class="text-md font-medium text-gray-900 mb-3">Informations de la commande</h4>
                <dl class="space-y-2">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Référence:</dt>
                    <dd class="text-sm font-medium">{{ selectedOrder.transaction_id }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Statut:</dt>
                    <dd><span :class="getStatusBadgeClass(selectedOrder.status)">{{ getStatusText(selectedOrder.status) }}</span></dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Type:</dt>
                    <dd class="text-sm">{{ getTypeText(selectedOrder.type) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Montant:</dt>
                    <dd class="text-sm font-medium">{{ selectedOrder.amount }} {{ selectedOrder.currency }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Méthode de paiement:</dt>
                    <dd class="text-sm">{{ selectedOrder.payment_method || 'Non spécifié' }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Date de création:</dt>
                    <dd class="text-sm">{{ formatDate(selectedOrder.created_at) }}</dd>
                  </div>
                  <div v-if="selectedOrder.completed_at" class="flex justify-between">
                    <dt class="text-sm text-gray-600">Date de completion:</dt>
                    <dd class="text-sm">{{ formatDate(selectedOrder.completed_at) }}</dd>
                  </div>
                </dl>
              </div>
              
              <div>
                <div v-if="selectedOrder.product">
                  <h4 class="text-md font-medium text-gray-900 mb-3">Produit</h4>
                  <div class="border rounded-lg p-4">
                    <h5 class="font-medium">{{ selectedOrder.product.name }}</h5>
                    <p class="text-sm text-gray-600 mt-1">{{ selectedOrder.product.description || '' }}</p>
                  </div>
                </div>
                
                <div v-if="selectedOrder.lottery" class="mt-6">
                  <h4 class="text-md font-medium text-gray-900 mb-3">Loterie</h4>
                  <div class="border rounded-lg p-4">
                    <h5 class="font-medium">{{ selectedOrder.lottery.title }}</h5>
                    <div class="text-sm text-gray-600 mt-2">
                      <p>Numéro: {{ selectedOrder.lottery.lottery_number }}</p>
                      <p>Prix du billet: {{ selectedOrder.lottery.ticket_price }} {{ selectedOrder.currency }}</p>
                      <p>Billets vendus: {{ selectedOrder.lottery.sold_tickets }}/{{ selectedOrder.lottery.max_tickets }}</p>
                      <p v-if="selectedOrder.lottery.draw_date">Date du tirage: {{ formatDate(selectedOrder.lottery.draw_date) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Billets -->
            <div v-if="selectedOrder.tickets && selectedOrder.tickets.length > 0" class="mt-6">
              <h4 class="text-md font-medium text-gray-900 mb-3">Billets de loterie</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div 
                  v-for="ticket in selectedOrder.tickets" 
                  :key="ticket.id"
                  :class="[
                    'border rounded-lg p-4',
                    ticket.is_winner ? 'bg-green-50 border-green-200' : 'bg-gray-50'
                  ]"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <p class="font-medium">{{ ticket.ticket_number }}</p>
                      <p class="text-sm text-gray-600">{{ ticket.price_paid }} {{ selectedOrder.currency }}</p>
                    </div>
                    <div class="text-right">
                      <span :class="getStatusBadgeClass(ticket.status)">{{ getStatusText(ticket.status) }}</span>
                      <p v-if="ticket.is_winner" class="text-sm text-green-600 mt-1">🎉 Gagnant!</p>
                    </div>
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
import { useApi } from '@/composables/api'
import { useToast } from '@/composables/useToast'

const { apiRequest } = useApi()
const { showError } = useToast()

// État réactif
const orders = ref([])
const stats = ref({
  total_orders: 0,
  completed_orders: 0,
  pending_orders: 0,
  total_tickets_purchased: 0
})
const pagination = ref(null)
const loading = ref(false)
const showModal = ref(false)
const selectedOrder = ref(null)

// Filtres
const filters = reactive({
  search: '',
  status: '',
  type: ''
})

// Charger les statistiques
const loadStats = async () => {
  try {
    const response = await apiRequest('/api/orders/stats', 'GET')
    stats.value = response.data
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques:', error)
  }
}

// Charger les commandes
const loadOrders = async (page = 1) => {
  loading.value = true
  
  try {
    const params = new URLSearchParams({
      page: page,
      per_page: 10
    })

    if (filters.search) params.append('q', filters.search)
    if (filters.status) params.append('status', filters.status)
    if (filters.type) params.append('type', filters.type)

    const response = await apiRequest(`/api/orders?${params.toString()}`, 'GET')
    orders.value = response.data
    pagination.value = response.pagination
  } catch (error) {
    showError('Erreur lors du chargement des commandes')
    console.error('Erreur:', error)
  } finally {
    loading.value = false
  }
}

// Voir les détails d'une commande
const viewOrderDetails = async (transactionId) => {
  try {
    const response = await apiRequest(`/api/orders/${transactionId}`, 'GET')
    selectedOrder.value = response.data
    showModal.value = true
  } catch (error) {
    showError('Erreur lors du chargement des détails')
    console.error('Erreur:', error)
  }
}

// Fermer le modal
const closeModal = () => {
  showModal.value = false
  selectedOrder.value = null
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

const getTypeText = (type) => {
  const typeMap = {
    'ticket_purchase': 'Achat de billet',
    'direct_purchase': 'Achat direct'
  }
  return typeMap[type] || type
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
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
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
watch(() => [filters.status, filters.type], () => {
  loadOrders(1)
}, { deep: true })

// Lifecycle
onMounted(() => {
  loadStats()
  loadOrders()
})
</script>

<style scoped>
.order-tracking {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>