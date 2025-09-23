<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Demandes de remboursement</h1>
        <p class="mt-2 text-gray-600">Gérez les remboursements de vos clients</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="refreshData"
          :disabled="isRefreshing"
          class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors disabled:opacity-50">
          <ArrowPathIcon :class="['w-4 h-4 mr-2', { 'animate-spin': isRefreshing }]" />
          {{ isRefreshing ? 'Actualisation...' : 'Actualiser' }}
        </button>
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center px-4 py-2 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-lg transition-colors">
          <PlusIcon class="w-4 h-4 mr-2" />
          Nouvelle demande
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white p-6 rounded-xl shadow-lg border border-gray-100"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stat.value }}</p>
          </div>
          <div :class="['p-3 rounded-xl', stat.color]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Référence, client..."
              class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
              @input="applyFilters"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          >
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="approved">Approuvé</option>
            <option value="completed">Terminé</option>
            <option value="rejected">Rejeté</option>
            <option value="failed">Échoué</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
          <input
            v-model="filters.fromDate"
            type="date"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
          <input
            v-model="filters.toDate"
            type="date"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          />
        </div>
      </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-900">Demandes récentes</h3>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0099cc]"></div>
      </div>

      <div v-else-if="requests.length === 0" class="text-center py-12">
        <BanknotesIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune demande</h3>
        <p class="text-gray-600">Aucune demande de remboursement trouvée.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Référence
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Client
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Commande/Tombola
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Montant
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Statut
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="request in requests" :key="request.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ request.request_number }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                    <UserIcon class="w-5 h-5 text-gray-600" />
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ request.customer?.name }}</div>
                    <div class="text-sm text-gray-500">{{ request.customer_phone }}</div>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  <span v-if="request.order">
                    Commande #{{ request.order.order_number }}
                  </span>
                  <span v-else-if="request.lottery">
                    Tombola #{{ request.lottery.lottery_number }}
                  </span>
                  <span v-else>-</span>
                </div>
                <div class="text-sm text-gray-500">{{ request.product?.name || 'Produit supprimé' }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                      :class="getRefundTypeClass(request.refund_type)">
                  {{ getRefundTypeLabel(request.refund_type) }}
                </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-gray-900">{{ formatCurrency(request.refund_amount) }}</div>
                <div class="text-sm text-gray-500">{{ request.payment_operator }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(request.status)">
                  {{ getStatusLabel(request.status) }}
                </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDate(request.created_at) }}</div>
                <div class="text-sm text-gray-500">{{ formatTime(request.created_at) }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button
                    @click="viewRequest(request)"
                    class="text-[#0099cc] hover:text-[#0088bb] transition-colors"
                    title="Voir détails"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  <button
                    v-if="request.status === 'pending'"
                    @click="cancelRequest(request)"
                    class="text-red-600 hover:text-red-800 transition-colors"
                    title="Annuler"
                  >
                    <XCircleIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Affichage {{ requests.length }} résultats
        </div>
        <div class="flex space-x-2" v-if="pagination.total > pagination.per_page">
          <button
            @click="previousPage"
            :disabled="!pagination.prev_page_url"
            class="px-3 py-1 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Précédent
          </button>
          <button
            @click="nextPage"
            :disabled="!pagination.next_page_url"
            class="px-3 py-1 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Suivant
          </button>
        </div>
      </div>
    </div>

    <!-- Create Request Modal -->
    <PayoutRequestModal 
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @created="onRequestCreated" />

    <!-- Request Details Modal -->
    <RequestDetailsModal 
      v-if="showDetailsModal && selectedRequest"
      :request="selectedRequest"
      @close="showDetailsModal = false" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import {
  ArrowPathIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  BanknotesIcon,
  UserIcon,
  EyeIcon,
  XCircleIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CheckIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'
import PayoutRequestModal from '@/components/merchant/PayoutRequestModal.vue'
import RequestDetailsModal from '@/components/merchant/RequestDetailsModal.vue'
import { useToast } from '@/composables/useToast'
import { api } from '@/composables/api'

// Composables
const toast = useToast()

// State
const loading = ref(false)
const isRefreshing = ref(false)
const showCreateModal = ref(false)
const showDetailsModal = ref(false)
const selectedRequest = ref(null)
const requests = ref([])
const statsData = ref({})
const pagination = ref({})

const filters = reactive({
  search: '',
  status: '',
  fromDate: '',
  toDate: ''
})

// Computed
const stats = computed(() => {
  const data = statsData.value
  return [
    {
      label: 'Total demandes',
      value: data.total_requests || 0,
      icon: BanknotesIcon,
      color: 'bg-blue-500'
    },
    {
      label: 'En attente',
      value: data.pending_requests || 0,
      icon: ClockIcon,
      color: 'bg-yellow-500'
    },
    {
      label: 'Remboursé',
      value: formatCurrency(data.total_amount_refunded || 0),
      icon: CheckIcon,
      color: 'bg-green-500'
    },
    {
      label: 'En attente',
      value: formatCurrency(data.total_amount_pending || 0),
      icon: ExclamationTriangleIcon,
      color: 'bg-orange-500'
    }
  ]
})

// Methods
const loadRequests = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams()
    
    if (filters.status) params.append('status', filters.status)
    if (filters.fromDate) params.append('from_date', filters.fromDate)
    if (filters.toDate) params.append('to_date', filters.toDate)
    
    const response = await api.get(`/merchant/payout-requests?${params}`)
    requests.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total,
      prev_page_url: response.data.prev_page_url,
      next_page_url: response.data.next_page_url
    }
  } catch (error) {
    console.error('Erreur chargement demandes:', error)
    toast.error('Erreur lors du chargement des demandes')
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await api.get('/merchant/payout-requests/stats')
    statsData.value = response.data.data
  } catch (error) {
    console.error('Erreur chargement stats:', error)
  }
}

const applyFilters = async () => {
  await loadRequests()
}

const refreshData = async () => {
  isRefreshing.value = true
  try {
    await Promise.all([
      loadRequests(),
      loadStats()
    ])
    toast.success('Données actualisées')
  } catch (error) {
    toast.error('Erreur lors de l\'actualisation')
  } finally {
    isRefreshing.value = false
  }
}

const viewRequest = (request) => {
  selectedRequest.value = request
  showDetailsModal.value = true
}

const cancelRequest = async (request) => {
  if (!confirm('Voulez-vous vraiment annuler cette demande ?')) return
  
  try {
    await api.delete(`/merchant/payout-requests/${request.id}`)
    toast.success('Demande annulée')
    await loadRequests()
  } catch (error) {
    toast.error('Erreur lors de l\'annulation')
  }
}

const onRequestCreated = async () => {
  showCreateModal.value = false
  await refreshData()
  toast.success('Demande créée avec succès')
}

const previousPage = async () => {
  if (pagination.value.prev_page_url) {
    await loadRequests()
  }
}

const nextPage = async () => {
  if (pagination.value.next_page_url) {
    await loadRequests()
  }
}

// Helper functions
const getStatusClass = (status) => {
  const classes = {
    'pending': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800',
    'approved': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    'completed': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    'rejected': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800',
    'failed': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800'
  }
  return classes[status] || 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    'pending': 'En attente',
    'approved': 'Approuvé',
    'completed': 'Terminé',
    'rejected': 'Rejeté',
    'failed': 'Échoué'
  }
  return labels[status] || status
}

const getRefundTypeClass = (type) => {
  const classes = {
    'order_cancellation': 'bg-blue-100 text-blue-800',
    'lottery_cancellation': 'bg-purple-100 text-purple-800',
    'product_defect': 'bg-red-100 text-red-800',
    'customer_request': 'bg-green-100 text-green-800',
    'other': 'bg-gray-100 text-gray-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getRefundTypeLabel = (type) => {
  const labels = {
    'order_cancellation': 'Annulation commande',
    'lottery_cancellation': 'Annulation tombola',
    'product_defect': 'Produit défectueux',
    'customer_request': 'Demande client',
    'other': 'Autre'
  }
  return labels[type] || type
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(async () => {
  await refreshData()
})
</script>