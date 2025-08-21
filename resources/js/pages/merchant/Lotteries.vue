<template>
    <div class="space-y-6">
      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Mes Tombolas</h1>
          <p class="mt-2 text-gray-600">Gérez vos tombolas et effectuez les tirages</p>
        </div>
      </div>

      <!-- Status Tabs -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key; loadLotteries()"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.key
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.label }}
            <span v-if="tab.count" class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Filters -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nom du produit ou numéro..."
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
            <select
              v-model="filters.category"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Toutes les catégories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Progression</label>
            <select
              v-model="filters.completion"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Toutes</option>
              <option value="low">0-25%</option>
              <option value="medium">25-75%</option>
              <option value="high">75-100%</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
            <button
              @click="loadLotteries()"
              class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
            >
              Filtrer
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="lotteries.length === 0" class="text-center py-12">
        <GiftIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune tombola</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ activeTab === 'all' ? 'Vous n\'avez pas encore créé de tombola.' : 
             `Aucune tombola ${getTabLabel(activeTab)}.` }}
        </p>
      </div>

      <!-- Lotteries Table -->
      <div v-else class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produit
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Numéro
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Progression
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Revenus
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fin prévue
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="lottery in lotteries" :key="lottery.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <img 
                      :src="lottery.product?.image_url || '/images/products/placeholder.jpg'" 
                      :alt="lottery.product_title"
                      class="w-12 h-12 rounded-lg object-cover mr-4"
                    />
                    <div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ lottery.product_title }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ lottery.product?.category?.name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-mono text-gray-900">{{ lottery.lottery_number }}</div>
                  <div class="text-xs text-gray-500">{{ formatDate(lottery.start_date) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    lottery.status === 'active' ? 'bg-blue-100 text-blue-800' :
                    lottery.status === 'completed' ? 'bg-blue-100 text-blue-800' :
                    lottery.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                  ]">
                    {{ getStatusText(lottery.status) }}
                  </span>
                  <div v-if="lottery.is_ending_soon" class="text-xs text-red-600 mt-1">
                    Se termine bientôt
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-3">
                      <div 
                        :class="[
                          'h-2 rounded-full',
                          lottery.completion_rate >= 80 ? 'bg-blue-500' :
                          lottery.completion_rate >= 50 ? 'bg-yellow-500' : 'bg-red-500'
                        ]"
                        :style="{ width: lottery.completion_rate + '%' }"
                      ></div>
                    </div>
                    <span class="text-sm text-gray-600">{{ lottery.completion_rate }}%</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ lottery.sold_tickets }}/{{ lottery.total_tickets }} tickets
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ formatCurrency(lottery.total_revenue) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ formatCurrency(lottery.ticket_price) }}/ticket
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ formatDate(lottery.end_date) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ lottery.days_remaining > 0 ? lottery.days_remaining + ' jours' : 'Terminé' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-y-1">
                  <div class="flex space-x-2">
                    <button
                      @click="viewLottery(lottery)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      <EyeIcon class="w-4 h-4 inline mr-1" />
                      Voir
                    </button>
                    
                    <button
                      v-if="lottery.status === 'active' && lottery.completion_rate >= 80"
                      @click="drawLottery(lottery)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      <GiftIcon class="w-4 h-4 inline mr-1" />
                      Tirer
                    </button>
                    
                    <button
                      v-if="lottery.status === 'active'"
                      @click="extendLottery(lottery)"
                      class="text-yellow-600 hover:text-yellow-900"
                    >
                      <ClockIcon class="w-4 h-4 inline mr-1" />
                      Étendre
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="mt-6 flex justify-center">
        <nav class="flex items-center space-x-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Précédent
          </button>
          
          <span class="px-3 py-2 text-sm text-gray-700">
            Page {{ pagination.current_page }} sur {{ Math.ceil(pagination.total / pagination.per_page) }}
          </span>
          
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= Math.ceil(pagination.total / pagination.per_page)"
            class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Suivant
          </button>
        </nav>
      </div>
    </div>

  <!-- Draw Lottery Modal -->
  <LotteryDrawModal
    v-if="showDrawModal"
    :lottery="selectedLottery"
    @close="showDrawModal = false"
    @drawn="onLotteryDrawn"
  />

  <!-- Extend Lottery Modal -->
  <LotteryExtendModal
    v-if="showExtendModal"
    :lottery="selectedLottery"
    @close="showExtendModal = false"
    @extended="onLotteryExtended"
  />

  <!-- Lottery Details Modal -->
  <LotteryDetailsModal
    v-if="showDetailsModal"
    :lottery="selectedLottery"
    @close="showDetailsModal = false"
  />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useApi } from '@/composables/api'
import LotteryDrawModal from '@/components/merchant/LotteryDrawModal.vue'
import LotteryExtendModal from '@/components/merchant/LotteryExtendModal.vue'
import LotteryDetailsModal from '@/components/merchant/LotteryDetailsModal.vue'
import {
  GiftIcon,
  EyeIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

const { get, post } = useApi()

// Data
const lotteries = ref([])
const categories = ref([
  { id: 1, name: 'Électronique' },
  { id: 2, name: 'Mode' },
  { id: 3, name: 'Maison' }
])
const loading = ref(false)
const activeTab = ref('all')

// Modals
const showDrawModal = ref(false)
const showExtendModal = ref(false)
const showDetailsModal = ref(false)
const selectedLottery = ref(null)

// Pagination
const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0
})

// Filters
const filters = ref({
  search: '',
  category: '',
  completion: ''
})

// Tabs
const tabs = ref([
  { key: 'all', label: 'Toutes', count: 0 },
  { key: 'active', label: 'Actives', count: 0 },
  { key: 'ending_soon', label: 'Se terminent bientôt', count: 0 },
  { key: 'completed', label: 'Terminées', count: 0 }
])

// Methods
const loadLotteries = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pagination.value.per_page.toString(),
      status: activeTab.value === 'all' ? '' : activeTab.value,
      ...filters.value
    })

    const response = await get(`/merchant/dashboard/lottery-performance?${params}`)
    
    // Gestion flexible de la structure de réponse
    if (response?.data) {
      lotteries.value = response.data.lotteries || response.data || []
      // Update pagination if available
      if (response.data.pagination) {
        pagination.value = response.data.pagination
      }
    } else {
      lotteries.value = []
    }
    
    // Update tab counts
    updateTabCounts()
  } catch (error) {
    console.error('Erreur lors du chargement des tombolas:', error)
    lotteries.value = []
  } finally {
    loading.value = false
  }
}

const updateTabCounts = () => {
  tabs.value[0].count = lotteries.value.length
  tabs.value[1].count = lotteries.value.filter(l => l.status === 'active').length
  tabs.value[2].count = lotteries.value.filter(l => l.is_ending_soon).length
  tabs.value[3].count = lotteries.value.filter(l => l.status === 'completed').length
}

const loadCategories = async () => {
  try {
    const response = await get('/categories')
    // Gestion flexible de la structure de réponse
    if (response?.data) {
      categories.value = response.data.categories || response.data || []
    } else if (Array.isArray(response)) {
      categories.value = response
    } else {
      categories.value = []
    }
  } catch (error) {
    console.error('Erreur lors du chargement des catégories:', error)
    categories.value = []
  }
}

const viewLottery = (lottery) => {
  selectedLottery.value = lottery
  showDetailsModal.value = true
}

const drawLottery = (lottery) => {
  if (lottery.completion_rate < 80) {
    if (window.$toast) {
      window.$toast.warning('La tombola doit être à au moins 80% avant de pouvoir effectuer le tirage.', '⚠️ Tirage impossible')
    }
    return
  }
  selectedLottery.value = lottery
  showDrawModal.value = true
}

const extendLottery = (lottery) => {
  selectedLottery.value = lottery
  showExtendModal.value = true
}

const onLotteryDrawn = () => {
  showDrawModal.value = false
  selectedLottery.value = null
  loadLotteries()
}

const onLotteryExtended = () => {
  showExtendModal.value = false
  selectedLottery.value = null
  loadLotteries()
}

const changePage = (page) => {
  if (page > 0 && page <= Math.ceil(pagination.value.total / pagination.value.per_page)) {
    pagination.value.current_page = page
    loadLotteries(page)
  }
}

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const formatDate = (dateString) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(new Date(dateString))
}

const getStatusText = (status) => {
  const statusMap = {
    'active': 'Active',
    'completed': 'Terminée',
    'pending': 'En attente',
    'cancelled': 'Annulée'
  }
  return statusMap[status] || status
}

const getTabLabel = (tabKey) => {
  const tab = tabs.value.find(t => t.key === tabKey)
  return tab ? tab.label.toLowerCase() : tabKey
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadLotteries(),
    loadCategories()
  ])
})
</script>