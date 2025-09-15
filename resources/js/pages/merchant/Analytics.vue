<template>
  <div class="px-6">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Analytiques</h1>
      <p class="mt-2 text-gray-600">Analyse de vos performances de vente</p>
    </div>

    <!-- Filtres de période -->
    <div class="mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
            <select v-model="selectedPeriod" @change="loadAnalytics" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
              <option value="today">Aujourd'hui</option>
              <option value="yesterday">Hier</option>
              <option value="this_week">Cette semaine</option>
              <option value="last_week">Semaine dernière</option>
              <option value="this_month">Ce mois</option>
              <option value="last_month">Mois dernier</option>
              <option value="this_year">Cette année</option>
              <option value="custom">Période personnalisée</option>
            </select>
          </div>
          
          <div v-if="selectedPeriod === 'custom'" class="flex gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
              <input v-model="customDateStart" @change="loadAnalytics" type="date"
                     class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
              <input v-model="customDateEnd" @change="loadAnalytics" type="date"
                     class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
          </div>
          
          <div class="flex items-end">
            <button @click="loadAnalytics" :disabled="loading"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
              <span v-if="loading">Chargement...</span>
              <span v-else>Actualiser</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <StatsCard
        title="Revenus Totaux"
        :value="stats.total_revenue || 0"
        format="currency"
        :change="stats.revenue_change || 0"
        color="green"
        :icon="CurrencyDollarIcon"
        :loading="loading"
        :actions="[{label: 'Détails', primary: true}]"
      />
      
      <StatsCard
        title="Ventes ce mois"
        :value="stats.monthly_sales || 0"
        :change="stats.sales_change || 0"
        color="blue"
        :icon="ShoppingBagIcon"
        :loading="loading"
        :show-progress="true"
        :progress-value="stats.monthly_sales || 0"
        :progress-target="stats.sales_target || 200"
      />
      
      <StatsCard
        title="Produits Actifs"
        :value="stats.active_products || 0"
        :change="stats.products_change || 0"
        color="purple"
        :icon="GiftIcon"
        :loading="loading"
      />
      
      <StatsCard
        title="Taux Conversion"
        :value="stats.conversion_rate || 0"
        format="percentage"
        :change="stats.conversion_change || 0"
        color="orange"
        :icon="ChartBarIcon"
        :loading="loading"
      />
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="koumbaya-card">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">Évolution des Ventes</h3>
        </div>
        <div class="koumbaya-card-body">
          <div v-if="loading" class="h-64 bg-gradient-to-br from-blue-50 to-blue-50 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
              <p class="text-gray-500">Chargement...</p>
            </div>
          </div>
          <div v-else-if="salesChart && salesChart.length > 0" class="h-64">
            <!-- Graphique simple en barres avec les données de l'API -->
            <div class="h-full flex items-end justify-between space-x-2 p-4">
              <div v-for="(data, index) in salesChart.slice(-7)" :key="index" class="flex-1 flex flex-col items-center">
                <div class="w-full bg-blue-200 rounded-t" 
                     :style="{ height: `${Math.max(10, (data.sales / Math.max(...salesChart.map(d => d.sales)) * 180))}px` }">
                  <div class="w-full bg-blue-600 rounded-t" 
                       :style="{ height: `${Math.max(5, (data.sales / Math.max(...salesChart.map(d => d.sales)) * 180))}px` }">
                  </div>
                </div>
                <span class="text-xs text-gray-600 mt-2">{{ formatDate(data.date) }}</span>
                <span class="text-xs font-medium text-gray-900">{{ data.sales }}</span>
              </div>
            </div>
          </div>
          <div v-else class="h-64 bg-gradient-to-br from-blue-50 to-blue-50 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <ChartBarIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-500">Aucune donnée disponible</p>
            </div>
          </div>
        </div>
      </div>

      <div class="koumbaya-card">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">Performance des Tombolas</h3>
        </div>
        <div class="koumbaya-card-body">
          <div v-if="loading" class="h-64 bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto mb-4"></div>
              <p class="text-gray-500">Chargement...</p>
            </div>
          </div>
          <div v-else-if="lotteryPerformance && lotteryPerformance.length > 0" class="h-64 overflow-y-auto">
            <div class="space-y-3">
              <div v-for="lottery in lotteryPerformance.slice(0, 5)" :key="lottery.id" 
                   class="flex items-center justify-between p-3 bg-gray-100 rounded-lg">
                <div>
                  <h4 class="font-medium text-gray-900">{{ lottery.name }}</h4>
                  <p class="text-sm text-gray-600">{{ lottery.tickets_sold }} tickets vendus</p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900">{{ formatPrice(lottery.revenue) }}</p>
                  <p class="text-sm" :class="lottery.performance_change >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ lottery.performance_change >= 0 ? '+' : '' }}{{ lottery.performance_change }}%
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="h-64 bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <ChartPieIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-500">Aucune tombola disponible</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Table -->
    <div class="koumbaya-card">
      <div class="koumbaya-card-header">
        <h3 class="koumbaya-heading-4">Top Produits</h3>
      </div>
      <div class="koumbaya-card-body">
        <div v-if="loading" class="flex justify-center items-center h-32">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-2 text-gray-600">Chargement des produits...</span>
        </div>
        <div v-else-if="topProducts && topProducts.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produit
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ventes
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Revenus
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tendance
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in topProducts" :key="product.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <img :src="product.image_url || product.main_image || product.image || '/images/products/placeholder.jpg'" 
                           :alt="product.name" 
                           class="h-10 w-10 rounded-lg object-cover bg-gray-100">
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                      <div class="text-sm text-gray-500">{{ product.category?.name || product.category || 'Sans catégorie' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ product.sales_count || product.sales || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatPrice(product.total_revenue || product.revenue || 0) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <ArrowTrendingUpIcon v-if="(product.trend || product.performance_change || 0) > 0" 
                                         class="w-4 h-4 text-green-500 mr-1" />
                    <ArrowTrendingDownIcon v-else-if="(product.trend || product.performance_change || 0) < 0" 
                                           class="w-4 h-4 text-red-500 mr-1" />
                    <span v-if="(product.trend || product.performance_change || 0) !== 0"
                          :class="(product.trend || product.performance_change || 0) > 0 ? 'text-green-600' : 'text-red-600'" 
                          class="text-sm font-medium">
                      {{ Math.abs(product.trend || product.performance_change || 0) }}%
                    </span>
                    <span v-else class="text-sm text-gray-500">-</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-8">
          <GiftIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <p class="text-gray-500">Aucun produit trouvé pour cette période</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useApi } from '@/composables/api.js'
import StatsCard from '@/components/common/StatsCard.vue'
import {
  CurrencyDollarIcon,
  GiftIcon,
  ChartBarIcon,
  ChartPieIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon
} from '@heroicons/vue/24/outline'
import { ShoppingBagIcon } from '@heroicons/vue/24/solid'

// Composables
const { loading, error, get } = useApi()

// State
const stats = ref({})
const salesChart = ref([])
const topProducts = ref([])
const lotteryPerformance = ref([])

// Filtres
const selectedPeriod = ref('this_month')
const customDateStart = ref('')
const customDateEnd = ref('')

// Computed
const filterParams = computed(() => {
  const params = { period: selectedPeriod.value }
  
  if (selectedPeriod.value === 'custom') {
    if (customDateStart.value) params.start_date = customDateStart.value
    if (customDateEnd.value) params.end_date = customDateEnd.value
  }
  
  return params
})

// Methods
const loadAnalytics = async () => {
  try {
    await Promise.all([
      loadStats(),
      loadSalesChart(),
      loadTopProducts(),
      loadLotteryPerformance()
    ])
  } catch (err) {
    console.error('Erreur lors du chargement des analytics:', err)
  }
}

const loadStats = async () => {
  try {
    const response = await get('/stats/merchant/dashboard', { params: filterParams.value })
    if (response && response.success) {
      const data = response.data
      stats.value = {
        total_revenue: data.revenue.total,
        monthly_sales: data.orders.total,
        active_products: data.products.active,
        conversion_rate: data.products.total_views > 0 ? 
          (data.orders.total / data.products.total_views * 100) : 0,
        revenue_change: 0, // Calculate from comparison data if needed
        sales_change: 0,
        products_change: 0,
        conversion_change: 0
      }
    }
  } catch (err) {
    console.error('Erreur lors du chargement des stats:', err)
    stats.value = {}
  }
}

const loadSalesChart = async () => {
  try {
    const response = await get('/stats/merchant/analytics', { params: filterParams.value })
    if (response && response.success) {
      salesChart.value = response.data.daily_revenue.map(day => ({
        date: day.date,
        sales: day.orders,
        revenue: day.revenue
      }))
    }
  } catch (err) {
    console.error('Erreur lors du chargement du graphique des ventes:', err)
    salesChart.value = []
  }
}

const loadTopProducts = async () => {
  try {
    const response = await get('/stats/merchant/analytics', { params: filterParams.value })
    if (response && response.success) {
      topProducts.value = response.data.top_products.map(product => ({
        ...product,
        sales: product.order_count,
        total_revenue: product.revenue,
        performance_change: 0 // Calculate from historical data if needed
      }))
    }
  } catch (err) {
    console.error('Erreur lors du chargement des top produits:', err)
    topProducts.value = []
  }
}

const loadLotteryPerformance = async () => {
  try {
    const response = await get('/stats/merchant/lotteries', { params: filterParams.value })
    if (response && response.success) {
      lotteryPerformance.value = response.data.recent_lotteries.map(lottery => ({
        id: lottery.id,
        name: lottery.product_name,
        tickets_sold: lottery.sold_tickets,
        revenue: lottery.sold_tickets * 1000, // Estimate based on ticket price
        performance_change: lottery.completion_rate - 50 // Simple calculation
      }))
    }
  } catch (err) {
    console.error('Erreur lors du chargement de la performance des tombolas:', err)
    lotteryPerformance.value = []
  }
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price || 0).replace('XAF', 'FCFA')
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', { 
    month: 'short', 
    day: 'numeric' 
  })
}

// Lifecycle
onMounted(() => {
  loadAnalytics()
})
</script>