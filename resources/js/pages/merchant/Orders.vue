<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Mes Commandes</h1>
        <p class="mt-2 text-gray-600">Suivez et gérez toutes vos ventes de tickets de tombola</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="refreshOrders"
          class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
          <ArrowPathIcon class="w-4 h-4 mr-2" />
          Actualiser
        </button>
        <button
          @click="exportOrders"
          class="inline-flex items-center px-4 py-2 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-lg transition-colors">
          <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
          Exporter
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div
        v-for="stat in orderStats"
        :key="stat.label"
        class="bg-white p-6 rounded-xl shadow-lg border border-gray-100"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stat.value }}</p>
          </div>
          <div :class="[
            'p-3 rounded-xl',
            stat.color
          ]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center text-xs">
            <span :class="[
              'inline-flex items-center px-2 py-1 rounded-full font-medium',
              stat.change >= 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
            ]">
              <component
                :is="stat.change >= 0 ? ArrowUpIcon : ArrowDownIcon"
                class="w-3 h-3 mr-1"
              />
              {{ Math.abs(stat.change) }}%
            </span>
            <span class="ml-2 text-gray-500">vs semaine dernière</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="ID, client, produit..."
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
            <option value="confirmed">Confirmé</option>
            <option value="completed">Terminé</option>
            <option value="cancelled">Annulé</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Produit</label>
          <select
            v-model="filters.product"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          >
            <option value="">Tous les produits</option>
            <option v-for="product in products" :key="product.id" :value="product.id">
              {{ product.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
          <input
            v-model="filters.startDate"
            type="date"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
          <input
            v-model="filters.endDate"
            type="date"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          />
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-900">Commandes récentes</h3>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0099cc]"></div>
      </div>

      <div v-else-if="filteredOrders.length === 0" class="text-center py-12">
        <ShoppingBagIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune commande</h3>
        <p class="text-gray-600">Aucune commande ne correspond à vos critères.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Commande
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Client
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Produit
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tickets
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
            <tr v-for="order in paginatedOrders" :key="order.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">#{{ order.order_number }}</div>
                  <div class="text-sm text-gray-500">{{ order.payment_method }}</div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                    <UserIcon class="w-5 h-5 text-gray-600" />
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ order.customer_name }}</div>
                    <div class="text-sm text-gray-500">{{ order.customer_email }}</div>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <img class="h-12 w-12 rounded-lg object-cover" :src="order.product_image" :alt="order.product_name" />
                  <div class="ml-3">
                    <div class="text-sm font-medium text-gray-900">{{ order.product_name }}</div>
                    <div class="text-sm text-gray-500">{{ order.ticket_price.toLocaleString() }} FCFA/ticket</div>
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ order.tickets_count }} tickets</div>
                <div class="text-sm text-gray-500">N° {{ order.ticket_numbers.join(', ') }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-gray-900">{{ order.total_amount.toLocaleString() }} FCFA</div>
                <div class="text-sm text-gray-500">{{ order.currency }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(order.status)">
                  {{ getStatusLabel(order.status) }}
                </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDate(order.created_at) }}</div>
                <div class="text-sm text-gray-500">{{ formatTime(order.created_at) }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button
                    @click="viewOrder(order)"
                    class="text-[#0099cc] hover:text-[#0088bb] transition-colors"
                    title="Voir détails"
                  >
                    <EyeIcon class="w-4 h-4" />
                  </button>
                  <button
                    v-if="order.status === 'pending'"
                    @click="confirmOrder(order)"
                    class="text-blue-600 hover:text-blue-800 transition-colors"
                    title="Confirmer"
                  >
                    <CheckCircleIcon class="w-4 h-4" />
                  </button>
                  <button
                    v-if="['pending', 'confirmed'].includes(order.status)"
                    @click="cancelOrder(order)"
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
          Affichage de <span class="font-medium">{{ startIndex }}</span> à
          <span class="font-medium">{{ endIndex }}</span> sur
          <span class="font-medium">{{ filteredOrders.length }}</span> résultats
        </div>
        <div class="flex space-x-2">
          <button
            @click="previousPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Précédent
          </button>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 bg-gray-100 text-gray-600 rounded-md hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Suivant
          </button>
        </div>
      </div>
    </div>

    <!-- Order Details Modal -->
    <div v-if="showOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">Détails de la commande</h3>
          <button @click="closeOrderModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <div v-if="selectedOrder" class="space-y-6">
          <!-- Order Info -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">N° de commande</label>
              <p class="text-sm text-gray-900">{{ selectedOrder.order_number }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Date</label>
              <p class="text-sm text-gray-900">{{ formatDate(selectedOrder.created_at) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Statut</label>
              <span :class="getStatusClass(selectedOrder.status)">
                {{ getStatusLabel(selectedOrder.status) }}
              </span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Montant</label>
              <p class="text-sm text-gray-900 font-semibold">{{ selectedOrder.total_amount.toLocaleString() }} FCFA</p>
            </div>
          </div>

          <!-- Customer Info -->
          <div>
            <h4 class="text-md font-semibold text-gray-900 mb-3">Informations client</h4>
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Nom</label>
                  <p class="text-sm text-gray-900">{{ selectedOrder.customer_name }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Email</label>
                  <p class="text-sm text-gray-900">{{ selectedOrder.customer_email }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Tickets Info -->
          <div>
            <h4 class="text-md font-semibold text-gray-900 mb-3">Tickets achetés</h4>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-sm text-gray-700 mb-2">
                <strong>{{ selectedOrder.tickets_count }} tickets</strong> pour {{ selectedOrder.product_name }}
              </p>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="number in selectedOrder.ticket_numbers"
                  :key="number"
                  class="inline-block bg-[#0099cc] text-white px-2 py-1 rounded text-xs font-mono"
                >
                  #{{ number }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeOrderModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
            Fermer
          </button>
          <button
            v-if="selectedOrder?.status === 'pending'"
            @click="confirmOrder(selectedOrder)"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Confirmer la commande
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import {
  ArrowPathIcon,
  DocumentArrowDownIcon,
  MagnifyingGlassIcon,
  ShoppingBagIcon,
  UserIcon,
  EyeIcon,
  CheckCircleIcon,
  XCircleIcon,
  XMarkIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CheckIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// State
const loading = ref(false)
const showOrderModal = ref(false)
const selectedOrder = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(10)

const filters = reactive({
  search: '',
  status: '',
  product: '',
  startDate: '',
  endDate: ''
})

const orderStats = ref([
  {
    label: 'Total commandes',
    value: '247',
    change: 15.3,
    icon: ShoppingBagIcon,
    color: 'bg-[#0099cc]'
  },
  {
    label: 'Revenus',
    value: '156,200',
    change: 8.7,
    icon: CurrencyDollarIcon,
    color: 'bg-blue-500'
  },
  {
    label: 'En attente',
    value: '12',
    change: -2.1,
    icon: ClockIcon,
    color: 'bg-yellow-500'
  },
  {
    label: 'Taux confirmation',
    value: '94.2%',
    change: 3.2,
    icon: CheckIcon,
    color: 'bg-purple-500'
  }
])

const products = ref([
  { id: 1, name: 'iPhone 15 Pro' },
  { id: 2, name: 'MacBook Pro M3' },
  { id: 3, name: 'PlayStation 5' }
])

const orders = ref([
  {
    id: 1,
    order_number: 'ORD-2025-001',
    customer_name: 'Jean Dupont',
    customer_email: 'jean@example.com',
    product_name: 'iPhone 15 Pro',
    product_image: '/images/products/placeholder.jpg',
    tickets_count: 3,
    ticket_numbers: ['001', '045', '112'],
    ticket_price: 2500,
    total_amount: 7500,
    currency: 'FCFA',
    payment_method: 'Mobile Money',
    status: 'completed',
    created_at: '2025-01-09T10:30:00Z'
  },
  {
    id: 2,
    order_number: 'ORD-2025-002',
    customer_name: 'Marie Martin',
    customer_email: 'marie@example.com',
    product_name: 'MacBook Pro M3',
    product_image: '/images/products/placeholder.jpg',
    tickets_count: 1,
    ticket_numbers: ['278'],
    ticket_price: 5000,
    total_amount: 5000,
    currency: 'FCFA',
    payment_method: 'Carte bancaire',
    status: 'pending',
    created_at: '2025-01-09T09:15:00Z'
  },
  {
    id: 3,
    order_number: 'ORD-2025-003',
    customer_name: 'Paul Durant',
    customer_email: 'paul@example.com',
    product_name: 'PlayStation 5',
    product_image: '/images/products/placeholder.jpg',
    tickets_count: 2,
    ticket_numbers: ['089', '156'],
    ticket_price: 1500,
    total_amount: 3000,
    currency: 'FCFA',
    payment_method: 'Mobile Money',
    status: 'confirmed',
    created_at: '2025-01-09T08:45:00Z'
  }
])

// Computed
const filteredOrders = computed(() => {
  let filtered = orders.value

  if (filters.search) {
    const search = filters.search.toLowerCase()
    filtered = filtered.filter(order =>
      order.order_number.toLowerCase().includes(search) ||
      order.customer_name.toLowerCase().includes(search) ||
      order.product_name.toLowerCase().includes(search)
    )
  }

  if (filters.status) {
    filtered = filtered.filter(order => order.status === filters.status)
  }

  if (filters.product) {
    filtered = filtered.filter(order => order.product_name.includes(products.value.find(p => p.id == filters.product)?.name || ''))
  }

  if (filters.startDate) {
    filtered = filtered.filter(order => new Date(order.created_at) >= new Date(filters.startDate))
  }

  if (filters.endDate) {
    filtered = filtered.filter(order => new Date(order.created_at) <= new Date(filters.endDate))
  }

  return filtered
})

const totalPages = computed(() => Math.ceil(filteredOrders.value.length / itemsPerPage.value))

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredOrders.value.slice(start, end)
})

const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage.value + 1)

const endIndex = computed(() => {
  const end = currentPage.value * itemsPerPage.value
  return Math.min(end, filteredOrders.value.length)
})

// Methods
const applyFilters = () => {
  currentPage.value = 1
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800',
    'confirmed': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    'completed': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    'cancelled': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800'
  }
  return classes[status] || 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    'pending': 'En attente',
    'confirmed': 'Confirmé',
    'completed': 'Terminé',
    'cancelled': 'Annulé'
  }
  return labels[status] || status
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

const viewOrder = (order) => {
  selectedOrder.value = order
  showOrderModal.value = true
}

const closeOrderModal = () => {
  showOrderModal.value = false
  selectedOrder.value = null
}

const confirmOrder = async (order) => {
  if (confirm(`Confirmer la commande ${order.order_number} ?`)) {
    try {
      // Simulate API call
      order.status = 'confirmed'
      alert('Commande confirmée avec succès !')
      closeOrderModal()
    } catch (error) {
      alert('Erreur lors de la confirmation')
    }
  }
}

const cancelOrder = async (order) => {
  if (confirm(`Annuler la commande ${order.order_number} ? Cette action est irréversible.`)) {
    try {
      // Simulate API call
      order.status = 'cancelled'
      alert('Commande annulée')
      closeOrderModal()
    } catch (error) {
      alert('Erreur lors de l\'annulation')
    }
  }
}

const refreshOrders = () => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
    alert('Commandes actualisées')
  }, 1000)
}

const exportOrders = () => {
  alert('Export des commandes en cours de développement')
}

onMounted(() => {
  console.log('Orders page loaded')
})
</script>
