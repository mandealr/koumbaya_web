<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
          <h1 class="text-4xl font-bold mb-4">📚 Historique des Tombolas</h1>
          <p class="text-xl opacity-90 mb-8">
            Explorez l'historique complet de toutes nos tombolas terminées
          </p>

          <!-- Quick Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <div class="bg-white/20 rounded-lg p-4">
              <div class="text-2xl font-bold">{{ stats.total_lotteries || 0 }}</div>
              <div class="text-sm opacity-90">Tombolas terminées</div>
            </div>
            <div class="bg-white/20 rounded-lg p-4">
              <div class="text-2xl font-bold">{{ stats.total_winners || 0 }}</div>
              <div class="text-sm opacity-90">Gagnants heureux</div>
            </div>
            <div class="bg-white/20 rounded-lg p-4">
              <div class="text-2xl font-bold">{{ formatCurrencyShort(stats.total_prizes_value) }}</div>
              <div class="text-sm opacity-90">Valeur totale</div>
            </div>
            <div class="bg-white/20 rounded-lg p-4">
              <div class="text-2xl font-bold">{{ Math.round(stats.average_participation_rate || 0) }}%</div>
              <div class="text-sm opacity-90">Participation moy.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Period Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
              <select
                v-model="filters.period"
                @change="loadHistory"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              >
                <option value="all">Toute la période</option>
                <option value="month">Ce mois</option>
                <option value="quarter">Ce trimestre</option>
                <option value="year">Cette année</option>
              </select>
            </div>

            <!-- Category Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
              <select
                v-model="filters.category"
                @change="loadHistory"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              >
                <option value="">Toutes les catégories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Sort Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
              <select
                v-model="filters.sort"
                @change="loadHistory"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
              >
                <option value="date_desc">Plus récent</option>
                <option value="date_asc">Plus ancien</option>
                <option value="value_desc">Valeur croissante</option>
                <option value="value_asc">Valeur décroissante</option>
                <option value="participation_desc">Participation élevée</option>
              </select>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <button
              @click="resetFilters"
              class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
            >
              Réinitialiser
            </button>
            <div class="text-sm text-gray-500">
              {{ paginatedHistory.length }} résultat(s)
            </div>
          </div>
        </div>
      </div>

      <!-- History Grid -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Chargement de l'historique...</p>
      </div>

      <div v-else-if="history.length === 0" class="text-center py-12">
        <ArchiveBoxXMarkIcon class="mx-auto h-16 w-16 text-gray-400 mb-4" />
        <h3 class="text-xl font-medium text-gray-900 mb-2">Aucun historique</h3>
        <p class="text-gray-600">Aucune tombola terminée ne correspond à vos critères.</p>
      </div>

      <div v-else>
        <!-- Results Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
          <div
            v-for="lottery in paginatedHistory"
            :key="lottery.lottery_id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200"
          >
            <!-- Product Image -->
            <div class="relative h-48 bg-gray-200">
              <img
                v-if="lottery.product.image_url"
                :src="lottery.product.image_url"
                :alt="lottery.product.title"
                class="w-full h-full object-cover"
              />
              <div class="absolute top-3 right-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  Terminé
                </span>
              </div>
              <div class="absolute bottom-3 left-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white text-gray-900">
                  {{ lottery.product.category?.name || lottery.product.category || 'Sans catégorie' }}
                </span>
              </div>
            </div>

            <!-- Card Content -->
            <div class="p-6">
              <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ lottery.product.title }}</h3>
                <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(lottery.product.price) }}</p>
                <p class="text-sm text-gray-500">Tombola {{ lottery.lottery_number }}</p>
              </div>

              <!-- Winner Info -->
              <div class="bg-blue-50 rounded-lg p-4 mb-4">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                    {{ lottery.winner.initial }}
                  </div>
                  <div class="flex-1">
                    <div class="text-sm font-medium text-blue-900">🏆 Gagnant</div>
                    <div class="text-sm text-blue-700">
                      {{ lottery.winner.name }} • {{ lottery.winner.city }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center">
                  <div class="text-lg font-bold text-gray-900">{{ lottery.sold_tickets }}</div>
                  <div class="text-xs text-gray-500">Participants</div>
                </div>
                <div class="text-center">
                  <div class="text-lg font-bold text-gray-900">{{ lottery.participation_rate }}%</div>
                  <div class="text-xs text-gray-500">Participation</div>
                </div>
              </div>

              <!-- Progress Bar -->
              <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                <div
                  class="bg-blue-500 h-2 rounded-full"
                  :style="{ width: lottery.participation_rate + '%' }"
                ></div>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                  {{ formatDate(lottery.draw_date) }}
                </div>
                <button
                  @click="viewDetails(lottery)"
                  class="text-purple-600 hover:text-purple-700 text-sm font-medium"
                >
                  Voir détails →
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-center space-x-2">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Précédent
          </button>

          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page !== '...'"
              @click="currentPage = page"
              :class="[
                'px-4 py-2 text-sm font-medium border rounded-md',
                currentPage === page
                  ? 'bg-purple-600 text-white border-purple-600'
                  : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            <span v-else class="px-3 py-2 text-sm text-gray-500">...</span>
          </template>

          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Suivant
          </button>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedLottery" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/20" @click="selectedLottery = null"></div>
        <div class="relative bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold">Détails de la tombola</h3>
              <button
                @click="selectedLottery = null"
                class="text-gray-400 hover:text-gray-600"
              >
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>

            <div class="space-y-6">
              <!-- Product Info -->
              <div class="flex items-start space-x-4">
                <img
                  v-if="selectedLottery.product.image_url"
                  :src="selectedLottery.product.image_url"
                  :alt="selectedLottery.product.title"
                  class="w-24 h-24 rounded-lg object-cover"
                />
                <div class="flex-1">
                  <h4 class="text-xl font-bold text-gray-900">{{ selectedLottery.product.title }}</h4>
                  <p class="text-lg text-purple-600 font-semibold">{{ formatCurrency(selectedLottery.product.price) }}</p>
                  <p class="text-sm text-gray-600">{{ selectedLottery.product.category?.name || selectedLottery.product.category || 'Sans catégorie' }}</p>
                </div>
              </div>

              <!-- Winner -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h5 class="font-medium text-blue-900 mb-2">🏆 Gagnant officiel</h5>
                <div class="flex items-center">
                  <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-4 text-lg font-bold">
                    {{ selectedLottery.winner.initial }}
                  </div>
                  <div>
                    <p class="font-medium text-blue-900">{{ selectedLottery.winner.name }}</p>
                    <p class="text-sm text-blue-700">{{ selectedLottery.winner.city }}</p>
                    <p class="text-sm text-blue-600">Ticket: {{ selectedLottery.winning_ticket }}</p>
                  </div>
                </div>
              </div>

              <!-- Statistics -->
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900">{{ selectedLottery.sold_tickets }}</div>
                  <div class="text-sm text-gray-600">Tickets vendus</div>
                  <div class="text-xs text-gray-500">sur {{ selectedLottery.total_tickets }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900">{{ selectedLottery.participation_rate }}%</div>
                  <div class="text-sm text-gray-600">Participation</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900">{{ formatCurrencyShort(selectedLottery.total_revenue) }}</div>
                  <div class="text-sm text-gray-600">Revenus</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900 font-mono">{{ selectedLottery.verification_code }}</div>
                  <div class="text-sm text-gray-600">Code vérif.</div>
                </div>
              </div>

              <!-- Timeline -->
              <div>
                <h5 class="font-medium text-gray-900 mb-3">📅 Chronologie</h5>
                <div class="text-sm text-gray-600 space-y-1">
                  <p><strong>Tirage :</strong> {{ formatDate(selectedLottery.draw_date) }}</p>
                  <p><strong>Revenus :</strong> {{ formatCurrency(selectedLottery.total_revenue) }}</p>
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
import { ref, computed, onMounted, watch } from 'vue'
import { useApi } from '@/composables/api'
import {
  ArchiveBoxXMarkIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const { get } = useApi()

// Data
const history = ref([])
const stats = ref({})
const categories = ref([])
const loading = ref(false)
const selectedLottery = ref(null)

// Filters
const filters = ref({
  period: 'all',
  category: '',
  sort: 'date_desc'
})

// Pagination
const currentPage = ref(1)
const itemsPerPage = 12

// Computed
const filteredHistory = computed(() => {
  let filtered = [...history.value]

  // Apply sorting
  switch (filters.value.sort) {
    case 'date_asc':
      filtered.sort((a, b) => new Date(a.draw_date) - new Date(b.draw_date))
      break
    case 'date_desc':
      filtered.sort((a, b) => new Date(b.draw_date) - new Date(a.draw_date))
      break
    case 'value_asc':
      filtered.sort((a, b) => a.product.price - b.product.price)
      break
    case 'value_desc':
      filtered.sort((a, b) => b.product.price - a.product.price)
      break
    case 'participation_desc':
      filtered.sort((a, b) => b.participation_rate - a.participation_rate)
      break
  }

  return filtered
})

const totalPages = computed(() => Math.ceil(filteredHistory.value.length / itemsPerPage))

const paginatedHistory = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredHistory.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const current = currentPage.value
  const total = totalPages.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    }
  }

  return pages
})

// Methods
const loadHistory = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      period: filters.value.period,
      limit: 500 // Load more for client-side filtering
    })

    if (filters.value.category) {
      params.append('category', filters.value.category)
    }

    const [historyResponse, statsResponse, categoriesResponse] = await Promise.all([
      get(`/public/results?${params}`),
      get('/public/results/stats'),
      get('/categories')
    ])

    history.value = historyResponse.data.results || []
    stats.value = historyResponse.data.stats || {}
    categories.value = categoriesResponse.data || []
  } catch (error) {
    console.error('Erreur lors du chargement de l\'historique:', error)
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    period: 'all',
    category: '',
    sort: 'date_desc'
  }
  currentPage.value = 1
}

const viewDetails = (lottery) => {
  selectedLottery.value = lottery
}

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const formatCurrencyShort = (amount) => {
  if (!amount) return '0'

  if (amount >= 1000000) {
    return (amount / 1000000).toFixed(1) + 'M'
  } else if (amount >= 1000) {
    return (amount / 1000).toFixed(0) + 'K'
  }
  return amount.toString()
}

const formatDate = (dateString) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(dateString))
}

// Watch for filter changes and reset pagination
watch(filters, () => {
  currentPage.value = 1
}, { deep: true })

// Lifecycle
onMounted(() => {
  loadHistory()
})
</script>
