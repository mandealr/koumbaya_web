<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Gestion des Paiements</h1>
        <p class="mt-2 text-gray-600">Suivez et gérez toutes les transactions financières</p>
      </div>
      <div class="flex space-x-3">
        <button @click="exportPayments" class="admin-btn-secondary">
          <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
          Exporter
        </button>
        <button @click="refreshData" class="admin-btn-primary">
          <ArrowPathIcon class="w-4 h-4 mr-2" />
          Actualiser
        </button>
      </div>
    </div>

    <!-- Payment Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="stat in paymentStats" 
        :key="stat.label"
        class="admin-card hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex items-center">
          <div :class="['p-3 rounded-lg', stat.bgColor]">
            <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center text-sm">
            <span :class="[
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
              stat.change >= 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
            ]">
              <component 
                :is="stat.change >= 0 ? ArrowUpIcon : ArrowDownIcon" 
                class="w-3 h-3 mr-1" 
              />
              {{ Math.abs(stat.change) }}%
            </span>
            <span class="ml-2 text-gray-500">vs mois dernier</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="admin-card">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Transaction ID, utilisateur..."
              class="admin-input pl-10"
              @input="applyFilters"
            />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select v-model="filters.status" @change="applyFilters" class="admin-input">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="completed">Complété</option>
            <option value="failed">Échoué</option>
            <option value="refunded">Remboursé</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
          <input
            v-model="filters.startDate"
            type="date"
            class="admin-input"
            @change="applyFilters"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
          <input
            v-model="filters.endDate"
            type="date"
            class="admin-input"
            @change="applyFilters"
          />
        </div>
      </div>
    </div>

    <!-- Payments Table -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3 class="text-lg font-semibold text-gray-900">Transactions récentes</h3>
      </div>
      
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0099cc]"></div>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Transaction
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Utilisateur
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Produit/Tombola
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
            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">#{{ payment.transaction_id }}</div>
                <div class="text-sm text-gray-500">{{ payment.payment_method }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                      <UserIcon class="h-4 w-4 text-gray-600" />
                    </div>
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ payment.user_name }}</div>
                    <div class="text-sm text-gray-500">{{ payment.user_email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ payment.product_name }}</div>
                <div class="text-sm text-gray-500">{{ payment.tickets_count }} tickets</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ formatAmount(payment.amount) }}</div>
                <div class="text-sm text-gray-500">{{ payment.currency }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(payment.status)">
                  {{ getStatusLabel(payment.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(payment.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button 
                    @click="viewPayment(payment)" 
                    class="text-[#0099cc] hover:text-[#0088bb]"
                    title="Voir détails"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  <button 
                    v-if="payment.status === 'completed'" 
                    @click="initiateRefund(payment)"
                    class="text-orange-600 hover:text-orange-900"
                    title="Initier remboursement"
                  >
                    <ArrowUturnLeftIcon class="w-4 h-4" />
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
          Affichage de <span class="font-medium">{{ startIndex }}</span> à 
          <span class="font-medium">{{ endIndex }}</span> sur 
          <span class="font-medium">{{ totalPayments }}</span> résultats
        </div>
        <div class="flex space-x-2">
          <button 
            @click="previousPage" 
            :disabled="currentPage === 1"
            class="admin-btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Précédent
          </button>
          <button 
            @click="nextPage" 
            :disabled="currentPage === totalPages"
            class="admin-btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Suivant
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Details Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-20 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Détails du paiement</h3>
            <button @click="closePaymentModal" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
          
          <div v-if="selectedPayment" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">ID Transaction</label>
                <p class="text-sm text-gray-900">{{ selectedPayment.transaction_id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Montant</label>
                <p class="text-sm text-gray-900">{{ formatAmount(selectedPayment.amount) }} {{ selectedPayment.currency }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Statut</label>
                <span :class="getStatusClass(selectedPayment.status)">
                  {{ getStatusLabel(selectedPayment.status) }}
                </span>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Méthode</label>
                <p class="text-sm text-gray-900">{{ selectedPayment.payment_method }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <p class="text-sm text-gray-900">{{ formatDate(selectedPayment.created_at) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Utilisateur</label>
                <p class="text-sm text-gray-900">{{ selectedPayment.user_name }}</p>
              </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button @click="closePaymentModal" class="admin-btn-secondary">
              Fermer
            </button>
            <button v-if="selectedPayment?.status === 'completed'" @click="initiateRefund(selectedPayment)" class="admin-btn-accent">
              Initier remboursement
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import {
  CurrencyDollarIcon,
  CreditCardIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  MagnifyingGlassIcon,
  DocumentArrowDownIcon,
  ArrowPathIcon,
  EyeIcon,
  UserIcon,
  ArrowUturnLeftIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const { get, post } = useApi()

// Reactive data
const loading = ref(false)
const payments = ref([])
const paymentStats = ref([
  {
    label: 'Total des revenus',
    value: '45,2M FCFA',
    change: 15.3,
    icon: CurrencyDollarIcon,
    bgColor: 'bg-blue-100',
    iconColor: 'text-blue-600'
  },
  {
    label: 'Transactions réussies',
    value: '2,847',
    change: 8.7,
    icon: CreditCardIcon,
    bgColor: 'bg-blue-100',
    iconColor: 'text-[#0099cc]'
  },
  {
    label: 'Taux de réussite',
    value: '96.8%',
    change: 2.1,
    icon: ChartBarIcon,
    bgColor: 'bg-purple-100',
    iconColor: 'text-purple-600'
  },
  {
    label: 'Transactions échouées',
    value: '94',
    change: -12.5,
    icon: ExclamationTriangleIcon,
    bgColor: 'bg-red-100',
    iconColor: 'text-red-600'
  }
])

const filters = reactive({
  search: '',
  status: '',
  startDate: '',
  endDate: ''
})

const currentPage = ref(1)
const perPage = ref(20)
const totalPayments = ref(0)
const showPaymentModal = ref(false)
const selectedPayment = ref(null)

// Mock data for demonstration
const mockPayments = ref([
  {
    id: 1,
    transaction_id: 'TXN-2025-001',
    user_name: 'Jean Dupont',
    user_email: 'jean@example.com',
    product_name: 'iPhone 15 Pro Max',
    tickets_count: 2,
    amount: 5000,
    currency: 'FCFA',
    status: 'completed',
    payment_method: 'Mobile Money',
    created_at: '2025-01-09T10:30:00Z'
  },
  {
    id: 2,
    transaction_id: 'TXN-2025-002',
    user_name: 'Marie Martin',
    user_email: 'marie@example.com',
    product_name: 'MacBook Pro M3',
    tickets_count: 1,
    amount: 2500,
    currency: 'FCFA',
    status: 'pending',
    payment_method: 'Carte bancaire',
    created_at: '2025-01-09T09:15:00Z'
  },
  {
    id: 3,
    transaction_id: 'TXN-2025-003',
    user_name: 'Paul Durand',
    user_email: 'paul@example.com',
    product_name: 'PlayStation 5',
    tickets_count: 3,
    amount: 7500,
    currency: 'FCFA',
    status: 'failed',
    payment_method: 'Mobile Money',
    created_at: '2025-01-09T08:45:00Z'
  }
])

// Computed properties
const totalPages = computed(() => Math.ceil(totalPayments.value / perPage.value))
const startIndex = computed(() => (currentPage.value - 1) * perPage.value + 1)
const endIndex = computed(() => Math.min(currentPage.value * perPage.value, totalPayments.value))

// Methods
const loadPayments = async () => {
  loading.value = true
  try {
    // TODO: Replace with actual API call
    // const response = await get('/admin/payments', { params: filters })
    // payments.value = response.data
    // totalPayments.value = response.total
    
    // Using mock data for now
    payments.value = mockPayments.value
    totalPayments.value = mockPayments.value.length
  } catch (error) {
    console.error('Error loading payments:', error)
    // Use mock data as fallback
    payments.value = mockPayments.value
    totalPayments.value = mockPayments.value.length
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  loadPayments()
}

const formatAmount = (amount) => {
  return new Intl.NumberFormat('fr-FR').format(amount)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'admin-badge-warning',
    completed: 'admin-badge-success',
    failed: 'admin-badge-danger',
    refunded: 'bg-gray-100 text-gray-800 px-2 py-1 text-xs font-medium rounded-full'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 px-2 py-1 text-xs font-medium rounded-full'
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'En attente',
    completed: 'Complété',
    failed: 'Échoué',
    refunded: 'Remboursé'
  }
  return labels[status] || status
}

const viewPayment = (payment) => {
  selectedPayment.value = payment
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  selectedPayment.value = null
}

const initiateRefund = async (payment) => {
  if (confirm('Êtes-vous sûr de vouloir initier un remboursement pour cette transaction ?')) {
    try {
      // TODO: Implement refund API call
      console.log('Initiating refund for:', payment.transaction_id)
      // await post(`/admin/payments/${payment.id}/refund`)
      if (window.$toast) {
        window.$toast.success('Remboursement initié avec succès', '✅ Remboursement')
      }
      loadPayments()
    } catch (error) {
      console.error('Error initiating refund:', error)
      if (window.$toast) {
        window.$toast.error('Erreur lors de l\'initiation du remboursement', '❌ Erreur')
      }
    }
  }
}

const exportPayments = () => {
  // TODO: Implement export functionality
  console.log('Exporting payments...')
  if (window.$toast) {
    window.$toast.info('Fonctionnalité d\'export en cours de développement', '🚀 En développement')
  }
}

const refreshData = () => {
  loadPayments()
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    loadPayments()
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    loadPayments()
  }
}

onMounted(() => {
  loadPayments()
})
</script>