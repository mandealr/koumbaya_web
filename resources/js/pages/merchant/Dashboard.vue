<template>
  <div class="px-6">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Dashboard Marchand</h1>
      <p class="mt-2 text-gray-600">Suivez vos ventes et gérez vos tombolas</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div 
        v-for="stat in stats" 
        :key="stat.label"
        class="bg-white p-6 rounded-lg shadow-sm border border-gray-200"
      >
        <div class="flex items-center">
          <div :class="[
            'p-3 rounded-md',
            stat.color
          ]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
        <div class="mt-4">
          <div class="flex items-center text-sm">
            <span :class="[
              'font-medium flex items-center',
              stat.change >= 0 ? 'text-green-600' : 'text-red-600'
            ]">
              <component 
                :is="stat.change >= 0 ? ArrowUpIcon : ArrowDownIcon" 
                class="w-4 h-4 mr-1"
              />
              {{ Math.abs(stat.change) }}%
            </span>
            <span class="ml-2 text-gray-600">vs mois dernier</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Sales Chart -->
      <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">Évolution des Ventes</h3>
          <div class="flex space-x-2">
            <button 
              v-for="period in chartPeriods"
              :key="period.value"
              @click="selectedPeriod = period.value; loadSalesChart()"
              :class="[
                'px-3 py-1 rounded-md text-sm font-medium',
                selectedPeriod === period.value 
                  ? 'bg-green-100 text-green-700' 
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              {{ period.label }}
            </button>
          </div>
        </div>
        
        <div v-if="chartLoading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
        </div>
        
        <div v-else-if="salesChartData.length > 0" class="h-64">
          <canvas ref="salesChart"></canvas>
        </div>
        
        <div v-else class="flex justify-center items-center h-64 text-gray-500">
          Aucune donnée de vente disponible
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé Rapide</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Taux de conversion</span>
            <span class="text-sm font-medium text-gray-900">{{ dashboardStats.conversion_rate }}%</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Prix moyen ticket</span>
            <span class="text-sm font-medium text-gray-900">{{ formatCurrency(dashboardStats.avg_ticket_price) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Revenus ce mois</span>
            <span class="text-sm font-medium text-green-600">{{ formatCurrency(dashboardStats.revenue_this_month) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Revenus mois dernier</span>
            <span class="text-sm font-medium text-gray-900">{{ formatCurrency(dashboardStats.revenue_last_month) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Top Products -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Produits Top Ventes</h3>
          <router-link 
            to="/merchant/products" 
            class="text-sm text-green-600 hover:text-green-700"
          >
            Voir tout
          </router-link>
        </div>
        <div class="space-y-4">
          <div 
            v-for="product in topProducts" 
            :key="product.id"
            class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg"
          >
            <img 
              :src="product.image_url || '/images/products/placeholder.jpg'" 
              :alt="product.title"
              class="w-12 h-12 rounded-lg object-cover"
            />
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ product.title }}</p>
              <p class="text-sm text-gray-600">{{ product.tickets_sold }} billets vendus</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatCurrency(product.total_revenue) }}</p>
              <p class="text-xs text-green-600">{{ product.conversion_rate }}% conversion</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Transactions Récentes</h3>
          <router-link 
            to="/merchant/transactions" 
            class="text-sm text-green-600 hover:text-green-700"
          >
            Voir tout
          </router-link>
        </div>
        <div class="space-y-4">
          <div 
            v-for="transaction in recentTransactions" 
            :key="transaction.id"
            class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg"
          >
            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center">
              <CurrencyDollarIcon class="w-5 h-5" />
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ transaction.user.name }}</p>
              <p class="text-sm text-gray-600">{{ transaction.product.title }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ formatCurrency(transaction.amount) }}</p>
              <p class="text-xs text-gray-500">{{ formatDate(transaction.completed_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lottery Performance Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Performance des Tombolas</h3>
          <router-link 
            to="/merchant/lotteries" 
            class="text-sm text-green-600 hover:text-green-700"
          >
            Gérer les tombolas
          </router-link>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Produit
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
                Temps restant
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="lottery in lotteryPerformance" :key="lottery.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ lottery.product_title }}
                </div>
                <div class="text-sm text-gray-600">
                  {{ lottery.lottery_number }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  lottery.status === 'active' ? 'bg-green-100 text-green-800' :
                  lottery.status === 'completed' ? 'bg-blue-100 text-blue-800' :
                  'bg-gray-100 text-gray-800'
                ]">
                  {{ getStatusText(lottery.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div 
                      class="bg-green-600 h-2 rounded-full" 
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
                  {{ lottery.days_remaining > 0 ? lottery.days_remaining + ' jours' : 'Terminé' }}
                </div>
                <div v-if="lottery.is_ending_soon" class="text-xs text-red-600">
                  Se termine bientôt
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <router-link 
                  :to="`/merchant/lotteries/${lottery.id}`"
                  class="text-green-600 hover:text-green-900 mr-3"
                >
                  Voir
                </router-link>
                <button 
                  v-if="lottery.status === 'active' && lottery.completion_rate >= 80" 
                  @click="drawLottery(lottery.id)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  Tirer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import {
  ShoppingBagIcon,
  GiftIcon,
  CurrencyDollarIcon,
  TicketIcon,
  ArrowUpIcon,
  ArrowDownIcon
} from '@heroicons/vue/24/outline'
import { useApi } from '@/composables/api'

Chart.register(...registerables)

const { get } = useApi()

// Data
const dashboardStats = ref({})
const topProducts = ref([])
const recentTransactions = ref([])
const lotteryPerformance = ref([])
const salesChartData = ref([])
const chartLoading = ref(false)
const selectedPeriod = ref('30d')

// Chart
const salesChart = ref(null)
let chartInstance = null

// Periods for chart
const chartPeriods = [
  { value: '7d', label: '7j' },
  { value: '30d', label: '30j' },
  { value: '90d', label: '90j' },
  { value: '1y', label: '1an' }
]

// Computed stats
const stats = ref([
  {
    label: 'Mes Produits',
    value: '0',
    change: 0,
    color: 'bg-blue-500',
    icon: ShoppingBagIcon
  },
  {
    label: 'Tombolas Actives',
    value: '0',
    change: 0,
    color: 'bg-green-500',
    icon: GiftIcon
  },
  {
    label: 'Tickets Vendus',
    value: '0',
    change: 0,
    color: 'bg-yellow-500',
    icon: TicketIcon
  },
  {
    label: 'Revenus Total',
    value: '0',
    change: 0,
    color: 'bg-purple-500',
    icon: CurrencyDollarIcon
  }
])

// Methods
const loadDashboardStats = async () => {
  try {
    const response = await get('/merchant/dashboard/stats')
    dashboardStats.value = response.data
    
    // Update stats cards
    stats.value[0].value = response.data.total_products.toString()
    stats.value[1].value = response.data.active_lotteries.toString()
    stats.value[2].value = response.data.tickets_sold.toLocaleString()
    stats.value[3].value = formatCurrency(response.data.total_sales)
    
    // Update growth rates
    stats.value[0].change = 0 // Could calculate if we had historical data
    stats.value[1].change = 0
    stats.value[2].change = 0
    stats.value[3].change = response.data.growth_rate
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques:', error)
  }
}

const loadSalesChart = async () => {
  chartLoading.value = true
  try {
    const response = await get(`/merchant/dashboard/sales-chart?period=${selectedPeriod.value}`)
    salesChartData.value = response.data.data
    
    await nextTick()
    renderChart()
  } catch (error) {
    console.error('Erreur lors du chargement du graphique:', error)
  } finally {
    chartLoading.value = false
  }
}

const loadTopProducts = async () => {
  try {
    const response = await get('/merchant/dashboard/top-products?limit=5')
    topProducts.value = response.data.products
  } catch (error) {
    console.error('Erreur lors du chargement des top produits:', error)
  }
}

const loadRecentTransactions = async () => {
  try {
    const response = await get('/merchant/dashboard/recent-transactions?limit=5')
    recentTransactions.value = response.data.transactions
  } catch (error) {
    console.error('Erreur lors du chargement des transactions:', error)
  }
}

const loadLotteryPerformance = async () => {
  try {
    const response = await get('/merchant/dashboard/lottery-performance')
    lotteryPerformance.value = response.data.lotteries.slice(0, 10)
  } catch (error) {
    console.error('Erreur lors du chargement des performances:', error)
  }
}

const renderChart = () => {
  if (!salesChart.value || salesChartData.value.length === 0) return
  
  if (chartInstance) {
    chartInstance.destroy()
  }
  
  const ctx = salesChart.value.getContext('2d')
  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: salesChartData.value.map(item => formatDateShort(item.date)),
      datasets: [
        {
          label: 'Revenus (FCFA)',
          data: salesChartData.value.map(item => item.revenue),
          borderColor: 'rgb(34, 197, 94)',
          backgroundColor: 'rgba(34, 197, 94, 0.1)',
          tension: 0.3,
          fill: true
        },
        {
          label: 'Tickets Vendus',
          data: salesChartData.value.map(item => item.tickets),
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.3,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        mode: 'index'
      },
      scales: {
        y: {
          type: 'linear',
          display: true,
          position: 'left',
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          grid: {
            drawOnChartArea: false,
          },
        },
      },
      plugins: {
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              if (context.datasetIndex === 0) {
                return `Revenus: ${formatCurrency(context.raw)}`
              }
              return `Tickets: ${context.raw}`
            }
          }
        }
      }
    }
  })
}

const drawLottery = async (lotteryId) => {
  if (!confirm('Êtes-vous sûr de vouloir procéder au tirage ?')) return
  
  try {
    await post(`/lotteries/${lotteryId}/draw`, {})
    await loadLotteryPerformance()
    alert('Tirage effectué avec succès !')
  } catch (error) {
    alert('Erreur lors du tirage: ' + error.message)
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
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(dateString))
}

const formatDateShort = (dateString) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit'
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

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadDashboardStats(),
    loadSalesChart(),
    loadTopProducts(),
    loadRecentTransactions(),
    loadLotteryPerformance()
  ])
})
</script>