<template>
  <MerchantLayout>
    <div class="px-6">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Analytiques Avancées</h1>
        <p class="mt-2 text-gray-600">Analyse détaillée de vos performances de vente</p>
      </div>

      <!-- Period Selector -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Période d'analyse</h3>
          <div class="flex space-x-2">
            <button 
              v-for="period in periods"
              :key="period.value"
              @click="selectedPeriod = period.value; loadAllData()"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                selectedPeriod === period.value 
                  ? 'bg-green-100 text-green-700' 
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              {{ period.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div 
          v-for="kpi in kpis" 
          :key="kpi.label"
          class="bg-white p-6 rounded-lg shadow-sm border border-gray-200"
        >
          <div class="flex items-center">
            <div :class="[
              'p-3 rounded-md',
              kpi.color
            ]">
              <component :is="kpi.icon" class="w-6 h-6 text-white" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">{{ kpi.label }}</p>
              <p class="text-2xl font-bold text-gray-900">{{ kpi.value }}</p>
            </div>
          </div>
          <div class="mt-4">
            <div class="flex items-center text-sm">
              <span :class="[
                'font-medium flex items-center',
                kpi.trend >= 0 ? 'text-green-600' : 'text-red-600'
              ]">
                <component 
                  :is="kpi.trend >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" 
                  class="w-4 h-4 mr-1"
                />
                {{ Math.abs(kpi.trend) }}%
              </span>
              <span class="ml-2 text-gray-600">vs période précédente</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Évolution des Revenus</h3>
          <div v-if="chartLoading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
          </div>
          <div v-else class="h-64">
            <canvas ref="revenueChart"></canvas>
          </div>
        </div>

        <!-- Performance by Category -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Performance par Catégorie</h3>
          <div v-if="chartLoading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
          </div>
          <div v-else class="h-64">
            <canvas ref="categoryChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Detailed Metrics -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Conversion Funnel -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Entonnoir de Conversion</h3>
          <div class="space-y-4">
            <div v-for="(step, index) in conversionFunnel" :key="index" class="relative">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">{{ step.label }}</span>
                <span class="text-sm text-gray-900">{{ step.value }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all duration-300" 
                  :style="{ width: step.percentage + '%' }"
                ></div>
              </div>
              <div class="text-xs text-gray-500 mt-1">{{ step.percentage }}%</div>
            </div>
          </div>
        </div>

        <!-- Top Performing Products -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Produits Top Performance</h3>
          <div class="space-y-3">
            <div 
              v-for="product in topPerformingProducts" 
              :key="product.id"
              class="flex items-center space-x-3"
            >
              <img 
                :src="product.image_url || '/images/products/placeholder.jpg'" 
                :alt="product.title"
                class="w-10 h-10 rounded-lg object-cover"
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ product.title }}</p>
                <p class="text-sm text-gray-500">{{ product.tickets_sold }} tickets</p>
              </div>
              <div class="text-sm font-medium text-green-600">
                {{ formatCurrency(product.total_revenue) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Time Analysis -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Analyse Temporelle</h3>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Meilleur jour</span>
                <span class="text-sm text-green-600 font-medium">{{ bestDay.day }}</span>
              </div>
              <p class="text-xs text-gray-500">{{ formatCurrency(bestDay.revenue) }} de revenus</p>
            </div>
            <div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Meilleure heure</span>
                <span class="text-sm text-green-600 font-medium">{{ bestHour.hour }}h</span>
              </div>
              <p class="text-xs text-gray-500">{{ bestHour.transactions }} transactions</p>
            </div>
            <div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Temps moyen d'achat</span>
                <span class="text-sm text-blue-600 font-medium">{{ avgPurchaseTime }}min</span>
              </div>
              <p class="text-xs text-gray-500">Depuis la première vue</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Product Performance Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Performance Détaillée des Produits</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Produit
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Vues
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ventes
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Conversion
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Revenus
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in productPerformance" :key="product.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <img :src="product.image_url || '/images/products/placeholder.jpg'" 
                           :alt="product.title" 
                           class="w-8 h-8 rounded-lg object-cover mr-3" />
                      <div class="text-sm font-medium text-gray-900 max-w-xs truncate">
                        {{ product.title }}
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ product.views_count || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ product.tickets_sold }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ product.conversion_rate }}%</div>
                    <div :class="[
                      'text-xs',
                      product.conversion_rate >= 5 ? 'text-green-600' :
                      product.conversion_rate >= 2 ? 'text-yellow-600' : 'text-red-600'
                    ]">
                      {{ product.conversion_rate >= 5 ? 'Excellent' :
                         product.conversion_rate >= 2 ? 'Bon' : 'À améliorer' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ formatCurrency(product.total_revenue) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Monthly Comparison -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Comparaison Mensuelle</h3>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="month in monthlyComparison" :key="month.month" 
                   class="flex items-center justify-between p-4 hover:bg-gray-50 rounded-lg">
                <div>
                  <div class="font-medium text-gray-900">{{ month.month }}</div>
                  <div class="text-sm text-gray-500">{{ month.transactions }} transactions</div>
                </div>
                <div class="text-right">
                  <div class="font-medium text-gray-900">{{ formatCurrency(month.revenue) }}</div>
                  <div :class="[
                    'text-sm flex items-center',
                    month.growth >= 0 ? 'text-green-600' : 'text-red-600'
                  ]">
                    <component 
                      :is="month.growth >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon" 
                      class="w-4 h-4 mr-1"
                    />
                    {{ Math.abs(month.growth) }}%
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MerchantLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import MerchantLayout from '@/components/common/MerchantLayout.vue'
import { useApi } from '@/composables/api'
import {
  CurrencyDollarIcon,
  EyeIcon,
  ShoppingCartIcon,
  UsersIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon
} from '@heroicons/vue/24/outline'

Chart.register(...registerables)

const { get } = useApi()

// Data
const selectedPeriod = ref('30d')
const chartLoading = ref(false)
const revenueChart = ref(null)
const categoryChart = ref(null)
let revenueChartInstance = null
let categoryChartInstance = null

// Periods
const periods = [
  { value: '7d', label: '7 jours' },
  { value: '30d', label: '30 jours' },
  { value: '90d', label: '90 jours' },
  { value: '1y', label: '1 an' }
]

// Analytics Data
const kpis = ref([
  {
    label: 'Revenus Total',
    value: '0 FCFA',
    trend: 0,
    color: 'bg-green-500',
    icon: CurrencyDollarIcon
  },
  {
    label: 'Vues Produits',
    value: '0',
    trend: 0,
    color: 'bg-blue-500',
    icon: EyeIcon
  },
  {
    label: 'Tickets Vendus',
    value: '0',
    trend: 0,
    color: 'bg-purple-500',
    icon: ShoppingCartIcon
  },
  {
    label: 'Taux Conversion',
    value: '0%',
    trend: 0,
    color: 'bg-yellow-500',
    icon: UsersIcon
  }
])

const conversionFunnel = ref([
  { label: 'Vues Produits', value: '0', percentage: 100 },
  { label: 'Pages Détails', value: '0', percentage: 75 },
  { label: 'Ajouts Panier', value: '0', percentage: 45 },
  { label: 'Achats Complétés', value: '0', percentage: 15 }
])

const topPerformingProducts = ref([])
const productPerformance = ref([])
const monthlyComparison = ref([])

const bestDay = ref({ day: 'Lundi', revenue: 0 })
const bestHour = ref({ hour: '14', transactions: 0 })
const avgPurchaseTime = ref(0)

// Methods
const loadAllData = async () => {
  chartLoading.value = true
  try {
    await Promise.all([
      loadKPIs(),
      loadChartData(),
      loadTopProducts(),
      loadProductPerformance(),
      loadTimeAnalysis(),
      loadMonthlyComparison()
    ])
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error)
  } finally {
    chartLoading.value = false
  }
}

const loadKPIs = async () => {
  try {
    const response = await get(`/merchant/dashboard/stats?period=${selectedPeriod.value}`)
    const data = response.data
    
    kpis.value[0].value = formatCurrency(data.total_sales)
    kpis.value[0].trend = data.growth_rate || 0
    
    kpis.value[1].value = (data.total_views || 0).toLocaleString()
    kpis.value[1].trend = data.views_growth || 0
    
    kpis.value[2].value = (data.tickets_sold || 0).toLocaleString()
    kpis.value[2].trend = data.tickets_growth || 0
    
    kpis.value[3].value = (data.conversion_rate || 0) + '%'
    kpis.value[3].trend = data.conversion_growth || 0
    
    // Update conversion funnel
    const totalViews = data.total_views || 1
    conversionFunnel.value = [
      { label: 'Vues Produits', value: totalViews.toLocaleString(), percentage: 100 },
      { label: 'Pages Détails', value: Math.round(totalViews * 0.75).toLocaleString(), percentage: 75 },
      { label: 'Ajouts Panier', value: Math.round(totalViews * 0.45).toLocaleString(), percentage: 45 },
      { label: 'Achats Complétés', value: data.tickets_sold.toLocaleString(), percentage: data.conversion_rate || 0 }
    ]
  } catch (error) {
    console.error('Erreur KPIs:', error)
  }
}

const loadChartData = async () => {
  try {
    const response = await get(`/merchant/dashboard/sales-chart?period=${selectedPeriod.value}`)
    const salesData = response.data.data
    
    await nextTick()
    renderRevenueChart(salesData)
    renderCategoryChart()
  } catch (error) {
    console.error('Erreur graphiques:', error)
  }
}

const loadTopProducts = async () => {
  try {
    const response = await get(`/merchant/dashboard/top-products?limit=5`)
    topPerformingProducts.value = response.data.products
  } catch (error) {
    console.error('Erreur top produits:', error)
  }
}

const loadProductPerformance = async () => {
  try {
    const response = await get(`/merchant/dashboard/top-products?limit=10`)
    productPerformance.value = response.data.products
  } catch (error) {
    console.error('Erreur performance produits:', error)
  }
}

const loadTimeAnalysis = async () => {
  // Mock data for time analysis
  bestDay.value = { day: 'Dimanche', revenue: 45000 }
  bestHour.value = { hour: '15', transactions: 12 }
  avgPurchaseTime.value = 8
}

const loadMonthlyComparison = async () => {
  // Mock data for monthly comparison
  monthlyComparison.value = [
    { month: 'Janvier 2025', revenue: 150000, transactions: 45, growth: 12 },
    { month: 'Février 2025', revenue: 180000, transactions: 52, growth: 20 },
    { month: 'Mars 2025', revenue: 165000, transactions: 48, growth: -8 },
    { month: 'Avril 2025', revenue: 220000, transactions: 63, growth: 33 }
  ]
}

const renderRevenueChart = (salesData) => {
  if (!revenueChart.value || !salesData.length) return
  
  if (revenueChartInstance) {
    revenueChartInstance.destroy()
  }
  
  const ctx = revenueChart.value.getContext('2d')
  revenueChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: salesData.map(item => formatDateShort(item.date)),
      datasets: [
        {
          label: 'Revenus',
          data: salesData.map(item => item.revenue),
          borderColor: 'rgb(34, 197, 94)',
          backgroundColor: 'rgba(34, 197, 94, 0.1)',
          tension: 0.4,
          fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: (context) => `Revenus: ${formatCurrency(context.raw)}`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (value) => formatCurrency(value)
          }
        }
      }
    }
  })
}

const renderCategoryChart = () => {
  if (!categoryChart.value) return
  
  if (categoryChartInstance) {
    categoryChartInstance.destroy()
  }
  
  const ctx = categoryChart.value.getContext('2d')
  categoryChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Électronique', 'Mode', 'Maison', 'Sport', 'Autres'],
      datasets: [{
        data: [35, 25, 20, 15, 5],
        backgroundColor: [
          'rgb(34, 197, 94)',
          'rgb(59, 130, 246)',
          'rgb(168, 85, 247)',
          'rgb(245, 158, 11)',
          'rgb(107, 114, 128)'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  })
}

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const formatDateShort = (dateString) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit'
  }).format(new Date(dateString))
}

// Lifecycle
onMounted(() => {
  loadAllData()
})
</script>