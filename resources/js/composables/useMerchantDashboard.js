import { ref, computed } from 'vue'
import { useApi } from './api'

export function useMerchantDashboard() {
  const { get, loading, error } = useApi()
  
  // Reactive data
  const dashboardStats = ref({
    products: {
      total: 0,
      active: 0,
      total_views: 0
    },
    orders: {
      total: 0,
      total_revenue: 0
    },
    lotteries: {
      total: 0,
      active: 0,
      completed: 0,
      total_tickets_sold: 0
    },
    revenue: {
      monthly: 0,
      weekly: 0,
      total: 0
    }
  })
  
  const recentOrders = ref([])
  const recentLotteries = ref([])
  const topProducts = ref([])
  
  // Computed stats for dashboard cards
  const stats = computed(() => [
    {
      label: 'Produits actifs',
      value: dashboardStats.value.products.active.toString(),
      total: `/ ${dashboardStats.value.products.total}`,
      color: 'bg-blue-500',
      icon: 'CubeIcon'
    },
    {
      label: 'Commandes',
      value: dashboardStats.value.orders.total.toString(),
      color: 'bg-green-500',
      icon: 'ShoppingBagIcon'
    },
    {
      label: 'Revenus total',
      value: formatCurrency(dashboardStats.value.revenue.total),
      color: 'bg-yellow-500',
      icon: 'CurrencyDollarIcon'
    },
    {
      label: 'Tombolas actives',
      value: dashboardStats.value.lotteries.active.toString(),
      total: `/ ${dashboardStats.value.lotteries.total}`,
      color: 'bg-purple-500',
      icon: 'TicketIcon'
    }
  ])
  
  // API calls
  const loadDashboardStats = async () => {
    try {
      const response = await get('/stats/merchant/dashboard')
      
      if (response && response.success) {
        dashboardStats.value = response.data
      }
    } catch (err) {
      console.error('Erreur lors du chargement des statistiques:', err)
    }
  }
  
  const loadRecentData = async () => {
    try {
      // Load recent orders
      const ordersResponse = await get('/stats/merchant/orders')
      if (ordersResponse && ordersResponse.success) {
        recentOrders.value = ordersResponse.data.recent_orders || []
      }
      
      // Load recent lotteries
      const lotteriesResponse = await get('/stats/merchant/lotteries')
      if (lotteriesResponse && lotteriesResponse.success) {
        recentLotteries.value = lotteriesResponse.data.recent_lotteries || []
      }
      
      // Load top products
      const analyticsResponse = await get('/stats/merchant/analytics?period=30d')
      if (analyticsResponse && analyticsResponse.success) {
        topProducts.value = analyticsResponse.data.top_products || []
      }
    } catch (err) {
      console.error('Erreur lors du chargement des données récentes:', err)
    }
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
  
  const formatDate = (date) => {
    return new Intl.DateTimeFormat('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(new Date(date))
  }
  
  const getStatusColor = (status) => {
    const statusColors = {
      'paid': 'bg-green-100 text-green-800',
      'pending': 'bg-yellow-100 text-yellow-800',
      'cancelled': 'bg-red-100 text-red-800',
      'active': 'bg-blue-100 text-blue-800',
      'completed': 'bg-green-100 text-green-800'
    }
    return statusColors[status] || 'bg-gray-100 text-gray-800'
  }
  
  // Load all dashboard data
  const loadDashboardData = async () => {
    await Promise.all([
      loadDashboardStats(),
      loadRecentData()
    ])
  }
  
  return {
    // Data
    stats,
    dashboardStats,
    recentOrders,
    recentLotteries,
    topProducts,
    
    // State
    loading,
    error,
    
    // Methods
    loadDashboardData,
    loadDashboardStats,
    loadRecentData,
    
    // Utilities
    formatCurrency,
    formatDate,
    getStatusColor
  }
}