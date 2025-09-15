import { ref, computed } from 'vue'
import { useApi } from './api'

export function useMerchantAnalytics() {
  const { get, loading, error } = useApi()
  
  // Reactive data
  const analyticsData = ref({
    period: '30d',
    daily_revenue: [],
    top_products: [],
    conversion: {
      total_views: 0,
      total_sales: 0,
      avg_conversion_rate: 0
    }
  })
  
  const selectedPeriod = ref('30d')
  
  // Periods for filter
  const periods = [
    { value: '7d', label: '7 derniers jours' },
    { value: '30d', label: '30 derniers jours' },
    { value: '90d', label: '90 derniers jours' },
    { value: '1y', label: '1 année' }
  ]
  
  // Computed analytics summary
  const summary = computed(() => {
    const dailyData = analyticsData.value.daily_revenue
    const totalRevenue = dailyData.reduce((sum, day) => sum + parseFloat(day.revenue || 0), 0)
    const totalOrders = dailyData.reduce((sum, day) => sum + parseInt(day.orders || 0), 0)
    const avgDaily = dailyData.length > 0 ? totalRevenue / dailyData.length : 0
    
    return {
      total_revenue: totalRevenue,
      total_orders: totalOrders,
      avg_daily_revenue: avgDaily,
      conversion_rate: analyticsData.value.conversion.avg_conversion_rate
    }
  })
  
  // Chart data for revenue evolution
  const revenueChartData = computed(() => {
    return {
      labels: analyticsData.value.daily_revenue.map(day => 
        new Date(day.date).toLocaleDateString('fr-FR', { 
          month: 'short', 
          day: 'numeric' 
        })
      ),
      datasets: [
        {
          label: 'Revenus (FCFA)',
          data: analyticsData.value.daily_revenue.map(day => parseFloat(day.revenue || 0)),
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.4
        },
        {
          label: 'Commandes',
          data: analyticsData.value.daily_revenue.map(day => parseInt(day.orders || 0)),
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          fill: false,
          yAxisID: 'y1'
        }
      ]
    }
  })
  
  // Top products formatted for display
  const topProductsFormatted = computed(() => {
    return analyticsData.value.top_products.map((product, index) => ({
      ...product,
      rank: index + 1,
      revenue_formatted: formatCurrency(product.revenue),
      avg_order_value: product.order_count > 0 ? 
        formatCurrency(product.revenue / product.order_count) : 
        formatCurrency(0)
    }))
  })
  
  // API calls
  const loadAnalytics = async (period = null) => {
    try {
      const requestPeriod = period || selectedPeriod.value
      const response = await get(`/stats/merchant/analytics?period=${requestPeriod}`)
      
      if (response && response.success) {
        analyticsData.value = response.data
        selectedPeriod.value = requestPeriod
      }
    } catch (err) {
      console.error('Erreur lors du chargement des analytics:', err)
    }
  }
  
  const changePeriod = async (newPeriod) => {
    selectedPeriod.value = newPeriod
    await loadAnalytics(newPeriod)
  }
  
  // Helper functions
  const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0
    if (num >= 1000000) {
      return (num / 1000000).toFixed(1) + 'M FCFA'
    }
    if (num >= 1000) {
      return (num / 1000).toFixed(0) + 'k FCFA'
    }
    return num.toLocaleString('fr-FR') + ' FCFA'
  }
  
  const formatNumber = (number) => {
    return new Intl.NumberFormat('fr-FR').format(number || 0)
  }
  
  const formatPercent = (percent) => {
    return `${(percent || 0).toFixed(1)}%`
  }
  
  const calculateGrowth = (current, previous) => {
    if (!previous || previous === 0) return 0
    return ((current - previous) / previous) * 100
  }
  
  const getGrowthColor = (growth) => {
    if (growth > 0) return 'text-green-600'
    if (growth < 0) return 'text-red-600'
    return 'text-gray-600'
  }
  
  const getGrowthIcon = (growth) => {
    if (growth > 0) return 'TrendingUpIcon'
    if (growth < 0) return 'TrendingDownIcon'
    return 'MinusIcon'
  }
  
  // Performance metrics
  const performanceMetrics = computed(() => [
    {
      label: 'Revenus totaux',
      value: formatCurrency(summary.value.total_revenue),
      icon: 'CurrencyDollarIcon',
      color: 'bg-green-500'
    },
    {
      label: 'Commandes totales',
      value: formatNumber(summary.value.total_orders),
      icon: 'ShoppingBagIcon',
      color: 'bg-blue-500'
    },
    {
      label: 'Revenus journaliers moyens',
      value: formatCurrency(summary.value.avg_daily_revenue),
      icon: 'ChartBarIcon',
      color: 'bg-purple-500'
    },
    {
      label: 'Taux de conversion',
      value: formatPercent(summary.value.conversion_rate),
      icon: 'CursorArrowRaysIcon',
      color: 'bg-yellow-500'
    }
  ])
  
  return {
    // Data
    analyticsData,
    selectedPeriod,
    periods,
    summary,
    revenueChartData,
    topProductsFormatted,
    performanceMetrics,
    
    // State
    loading,
    error,
    
    // Methods
    loadAnalytics,
    changePeriod,
    
    // Utilities
    formatCurrency,
    formatNumber,
    formatPercent,
    calculateGrowth,
    getGrowthColor,
    getGrowthIcon
  }
}