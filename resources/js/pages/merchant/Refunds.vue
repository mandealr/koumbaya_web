<template>
  <div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Gestion des Remboursements</h1>
      <p class="mt-2 text-gray-600">Gérez les remboursements de vos tombolas et commandes</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="p-2 bg-blue-100 rounded-lg">
            <CurrencyDollarIcon class="h-6 w-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Remboursements</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_refunds || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="p-2 bg-green-100 rounded-lg">
            <BanknotesIcon class="h-6 w-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Montant Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ (stats.total_amount || 0).toLocaleString('fr-FR') }} FCFA</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="p-2 bg-yellow-100 rounded-lg">
            <ClockIcon class="h-6 w-6 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">En Attente</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.pending_refunds || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="p-2 bg-purple-100 rounded-lg">
            <CheckCircleIcon class="h-6 w-6 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Traités</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.completed_refunds || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- Search -->
          <div class="relative">
            <MagnifyingGlassIcon class="h-5 w-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Rechercher un remboursement..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-full sm:w-64"
              @input="debouncedSearch"
            />
          </div>

          <!-- Status Filter -->
          <select
            v-model="filters.status"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @change="applyFilters"
          >
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="approved">Approuvé</option>
            <option value="processed">En cours</option>
            <option value="completed">Terminé</option>
            <option value="rejected">Rejeté</option>
          </select>

          <!-- Type Filter -->
          <select
            v-model="filters.type"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            @change="applyFilters"
          >
            <option value="">Tous les types</option>
            <option value="automatic">Automatique</option>
            <option value="manual">Manuel</option>
            <option value="admin">Administratif</option>
          </select>
        </div>

        <!-- Actions -->
        <div class="flex gap-2">
          <button
            @click="requestRefund"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <PlusIcon class="h-4 w-4" />
            Demander un Remboursement
          </button>

          <button
            @click="refreshData"
            :disabled="loading"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2 disabled:opacity-50"
          >
            <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
            Actualiser
          </button>
        </div>
      </div>
    </div>

    <!-- Refunds Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Remboursement
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Client
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tombola
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
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="refund in refunds" :key="refund.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ refund.refund_number }}</div>
                  <div class="text-sm text-gray-500">{{ refund.type }} - {{ refund.reason }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                    <UserIcon class="h-4 w-4 text-gray-500" />
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">
                      {{ refund.user?.first_name }} {{ refund.user?.last_name }}
                    </div>
                    <div class="text-sm text-gray-500">{{ refund.user?.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ refund.lottery?.lottery_number || 'N/A' }}</div>
                <div class="text-sm text-gray-500">{{ refund.lottery?.product?.name || 'Produit supprimé' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ refund.amount.toLocaleString('fr-FR') }} {{ refund.currency }}
                </div>
                <div class="text-sm text-gray-500">{{ refund.refund_method }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(refund.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ getStatusText(refund.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div>{{ formatDate(refund.created_at) }}</div>
                <div v-if="refund.processed_at" class="text-xs">
                  Traité: {{ formatDate(refund.processed_at) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button
                    @click="viewRefund(refund)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    <EyeIcon class="h-4 w-4" />
                  </button>
                  <button
                    v-if="refund.status === 'pending'"
                    @click="cancelRefund(refund)"
                    class="text-red-600 hover:text-red-900"
                  >
                    <XMarkIcon class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="refunds.length === 0 && !loading" class="text-center py-12">
        <CurrencyDollarIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun remboursement</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ filters.search || filters.status || filters.type ? 'Aucun remboursement ne correspond à vos filtres.' : 'Vous n\'avez encore aucun remboursement.' }}
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-sm text-gray-500">Chargement des remboursements...</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Affichage de {{ pagination.from }} à {{ pagination.to }} sur {{ pagination.total }} remboursements
      </div>
      <div class="flex gap-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          class="px-3 py-2 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Précédent
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="px-3 py-2 border border-gray-300 rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Suivant
        </button>
      </div>
    </div>

    <!-- Refund Request Modal -->
    <!-- TODO: Implement RefundRequestModal component -->
    <!-- <RefundRequestModal
      v-if="showRefundModal"
      @close="showRefundModal = false"
      @success="handleRefundRequestSuccess"
    /> -->

    <!-- Refund Detail Modal -->
    <!-- TODO: Implement RefundDetailModal component -->
    <!-- <RefundDetailModal
      v-if="showDetailModal && selectedRefund"
      :refund="selectedRefund"
      @close="showDetailModal = false"
      @updated="handleRefundUpdated"
    /> -->
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useApi } from '@/composables/api'
import { debounce } from 'lodash'
import {
  CurrencyDollarIcon,
  BanknotesIcon,
  ClockIcon,
  CheckCircleIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  ArrowPathIcon,
  EyeIcon,
  XMarkIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

// Components
// import RefundRequestModal from '@/components/merchant/RefundRequestModal.vue'
// import RefundDetailModal from '@/components/merchant/RefundDetailModal.vue'

// Composables
const { get, post, loading } = useApi()

// State
const refunds = ref([])
const stats = ref({})
const selectedRefund = ref(null)
const showRefundModal = ref(false)
const showDetailModal = ref(false)

const filters = ref({
  search: '',
  status: '',
  type: '',
  page: 1
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

// Computed
const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

// Methods
const loadRefunds = async () => {
  try {
    const params = new URLSearchParams()
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.status) params.append('status', filters.value.status)
    if (filters.value.type) params.append('type', filters.value.type)
    params.append('page', filters.value.page)

    const response = await get(`/merchant/refunds?${params.toString()}`)
    
    refunds.value = response.data || []
    pagination.value = response.meta || pagination.value
  } catch (error) {
    console.error('Error loading refunds:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du chargement des remboursements')
    }
  }
}

const loadStats = async () => {
  try {
    const response = await get('/merchant/refunds/stats')
    stats.value = response
  } catch (error) {
    console.error('Error loading refund stats:', error)
  }
}

const applyFilters = () => {
  filters.value.page = 1
  loadRefunds()
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    filters.value.page = page
    loadRefunds()
  }
}

const refreshData = () => {
  loadRefunds()
  loadStats()
}

const requestRefund = () => {
  showRefundModal.value = true
}

const viewRefund = (refund) => {
  selectedRefund.value = refund
  showDetailModal.value = true
}

const cancelRefund = async (refund) => {
  if (!confirm('Êtes-vous sûr de vouloir annuler cette demande de remboursement ?')) {
    return
  }

  try {
    await post(`/merchant/refunds/${refund.id}/cancel`)
    
    if (window.$toast) {
      window.$toast.success('Demande de remboursement annulée')
    }
    
    refreshData()
  } catch (error) {
    console.error('Error canceling refund:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de l\'annulation')
    }
  }
}

const handleRefundRequestSuccess = () => {
  showRefundModal.value = false
  refreshData()
  
  if (window.$toast) {
    window.$toast.success('Demande de remboursement créée avec succès')
  }
}

const handleRefundUpdated = () => {
  refreshData()
}

// Utility functions
const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    processed: 'bg-purple-100 text-purple-800',
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'En attente',
    approved: 'Approuvé',
    processed: 'En cours',
    completed: 'Terminé',
    rejected: 'Rejeté'
  }
  return texts[status] || status
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadRefunds()
  loadStats()
})
</script>