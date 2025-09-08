<template>
  <div class="order-management">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestion des Commandes</h1>
      <p class="text-gray-600">Suivre et gérer toutes les commandes de la plateforme</p>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.total_orders) }}</p>
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
            <p class="text-sm font-medium text-gray-600">Payées</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.paid_orders) }}</p>
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
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.pending_orders) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Traitées</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.fulfilled_orders) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-indigo-100">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">CA Total</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatPrice(stats.total_amount) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-teal-100">
            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"></path>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Aujourd'hui</p>
            <p class="text-2xl font-semibold text-gray-900">{{ formatNumber(stats.today_orders) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <!-- Recherche -->
        <div class="md:col-span-2">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Rechercher (n° commande, client, produit...)"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @keyup.enter="loadOrders(1)"
          />
        </div>

        <!-- Statut -->
        <div>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="paid">Payée</option>
            <option value="fulfilled">Traitée</option>
            <option value="cancelled">Annulée</option>
          </select>
        </div>

        <!-- Type -->
        <div>
          <select
            v-model="filters.type"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les types</option>
            <option value="lottery">Tombola</option>
            <option value="direct">Achat direct</option>
          </select>
        </div>

        <!-- Date de début -->
        <div>
          <input
            v-model="filters.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <!-- Boutons d'action -->
        <div class="flex space-x-2">
          <button
            @click="loadOrders(1)"
            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors text-sm"
          >
            Filtrer
          </button>
          <button
            @click="resetFilters"
            class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors text-sm"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Tableau des commandes -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Loading state -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-2 text-gray-600">Chargement des commandes...</span>
      </div>

      <!-- Empty state -->
      <div v-else-if="orders.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune commande</h3>
        <p class="mt-1 text-sm text-gray-500">Aucune commande ne correspond aux critères de recherche.</p>
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commande</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit/Tombola</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paiement</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Détails</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
              <!-- Commande -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">#{{ order.order_number }}</div>
                  <div class="text-sm text-gray-500">ID: {{ order.id }}</div>
                </div>
              </td>

              <!-- Client -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ order.customer_name }}</div>
                  <div class="text-sm text-gray-500">{{ order.user.email }}</div>
                </div>
              </td>

              <!-- Produit/Tombola -->
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ order.item_name || 'N/A' }}</div>
                <div v-if="order.lottery" class="text-sm text-gray-500">
                  Progress: {{ order.lottery_progress }}% ({{ order.lottery.sold_tickets }}/{{ order.lottery.max_tickets }})
                </div>
              </td>

              <!-- Type -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                  order.type === 'lottery' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'
                ]">
                  {{ order.type_label }}
                </span>
              </td>

              <!-- Montant -->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ formatPrice(order.total_amount) }}
              </td>

              <!-- Statut -->
              <td class="px-6 py-4 whitespace-nowrap">
                <select
                  :value="order.status"
                  @change="updateOrderStatus(order, $event.target.value)"
                  :class="[
                    'text-xs font-semibold rounded-full px-2 py-1 border-0',
                    getStatusClass(order.status)
                  ]"
                >
                  <option value="pending">En attente</option>
                  <option value="paid">Payée</option>
                  <option value="fulfilled">Traitée</option>
                  <option value="cancelled">Annulée</option>
                </select>
              </td>

              <!-- Paiement -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="order.payment_status">
                  <span :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    getPaymentStatusClass(order.payment_status)
                  ]">
                    {{ order.payment_status }}
                  </span>
                  <div v-if="order.payment_method" class="text-xs text-gray-500">
                    {{ order.payment_method }}
                  </div>
                </div>
                <span v-else class="text-sm text-gray-400">-</span>
              </td>

              <!-- Date -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(order.created_at) }}
              </td>

              <!-- Actions -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="viewOrderDetails(order.order_number)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  Voir
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="orders.length > 0" class="bg-white px-6 py-3 flex items-center justify-between border-t border-gray-200">
        <div class="flex-1 flex justify-between items-center">
          <div>
            <p class="text-sm text-gray-700">
              Affichage de
              <span class="font-medium">{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</span>
              à
              <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span>
              de
              <span class="font-medium">{{ pagination.total }}</span>
              résultats
            </p>
          </div>
          
          <div class="flex items-center space-x-2">
            <button
              @click="loadOrders(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Précédent
            </button>
            
            <span class="text-sm text-gray-700">
              Page {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>
            
            <button
              @click="loadOrders(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Suivant
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de détails de commande -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Détails de la commande #{{ selectedOrder?.order_number }}</h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div v-if="selectedOrder" class="space-y-6">
          <!-- Informations générales -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <h4 class="font-medium text-gray-900 mb-2">Informations client</h4>
              <p><strong>Nom:</strong> {{ selectedOrder.customer_name }}</p>
              <p><strong>Email:</strong> {{ selectedOrder.user.email }}</p>
              <p><strong>Téléphone:</strong> {{ selectedOrder.user.phone || 'N/A' }}</p>
            </div>
            <div>
              <h4 class="font-medium text-gray-900 mb-2">Informations commande</h4>
              <p><strong>Statut:</strong> {{ selectedOrder.status_label }}</p>
              <p><strong>Type:</strong> {{ selectedOrder.type_label }}</p>
              <p><strong>Montant:</strong> {{ formatPrice(selectedOrder.total_amount) }}</p>
              <p><strong>Date:</strong> {{ formatDate(selectedOrder.created_at) }}</p>
            </div>
          </div>

          <!-- Timeline -->
          <div v-if="selectedOrder.timeline">
            <h4 class="font-medium text-gray-900 mb-2">Chronologie</h4>
            <div class="space-y-2">
              <div v-for="event in selectedOrder.timeline" :key="event.type" class="flex items-center">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p class="font-medium">{{ event.title }}</p>
                  <p class="text-sm text-gray-500">{{ formatDate(event.timestamp) }}</p>
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

const { get: apiGet, put: apiPut } = useApi()
const { showError, showSuccess } = useToast()

// État réactif
const orders = ref([])
const stats = ref({
  total_orders: 0,
  paid_orders: 0,
  pending_orders: 0,
  fulfilled_orders: 0,
  total_amount: 0,
  today_orders: 0
})
const loading = ref(false)
const showModal = ref(false)
const selectedOrder = ref(null)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const filters = reactive({
  search: '',
  status: '',
  type: '',
  date_from: '',
  date_to: ''
})

// Méthodes
const formatNumber = (number) => {
  if (number === null || number === undefined || isNaN(number)) {
    return '0'
  }
  return new Intl.NumberFormat('fr-FR').format(Number(number))
}

const formatPrice = (price) => {
  if (price === null || price === undefined || isNaN(price)) {
    return '0 FCFA'
  }
  return new Intl.NumberFormat('fr-FR').format(Number(price)) + ' FCFA'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'paid': 'bg-green-100 text-green-800',
    'fulfilled': 'bg-blue-100 text-blue-800',
    'cancelled': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusClass = (status) => {
  const classes = {
    'paid': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'failed': 'bg-red-100 text-red-800',
    'expired': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// Charger les statistiques
const loadStats = async () => {
  try {
    const response = await apiGet('/admin/orders/stats')
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
      per_page: 15
    })

    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    if (filters.type) params.append('type', filters.type)
    if (filters.date_from) params.append('date_from', filters.date_from)
    if (filters.date_to) params.append('date_to', filters.date_to)

    const response = await apiGet(`/admin/orders?${params.toString()}`)
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
const viewOrderDetails = async (orderNumber) => {
  try {
    const response = await apiGet(`/admin/orders/${orderNumber}`)
    selectedOrder.value = response.data
    showModal.value = true
  } catch (error) {
    showError('Erreur lors du chargement des détails')
    console.error('Erreur:', error)
  }
}

// Mettre à jour le statut d'une commande
const updateOrderStatus = async (order, newStatus) => {
  if (order.status === newStatus) return

  try {
    const response = await apiPut(`/admin/orders/${order.order_number}/status`, {
      status: newStatus
    })
    
    if (response.success) {
      // Mettre à jour localement
      order.status = newStatus
      order.status_label = response.data.status_label
      
      showSuccess(`Statut de la commande #${order.order_number} mis à jour`)
      
      // Recharger les stats
      loadStats()
    }
  } catch (error) {
    showError('Erreur lors de la mise à jour du statut')
    console.error('Erreur:', error)
    // Recharger pour corriger l'état
    loadOrders(pagination.value.current_page)
  }
}

// Fermer la modal
const closeModal = () => {
  showModal.value = false
  selectedOrder.value = null
}

// Reset des filtres
const resetFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  loadOrders(1)
}

// Watchers
watch(filters, () => {
  // Auto-filter on change (debounced)
  clearTimeout(window.ordersFilterTimeout)
  window.ordersFilterTimeout = setTimeout(() => {
    loadOrders(1)
  }, 500)
}, { deep: true })

// Lifecycle
onMounted(() => {
  loadStats()
  loadOrders(1)
})
</script>