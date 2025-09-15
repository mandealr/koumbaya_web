import { ref, computed } from 'vue'
import { useApi } from './api'

export function useMerchantOrders() {
  const { get, post, put, loading, error } = useApi()
  
  // Reactive data
  const orders = ref([])
  const products = ref([])
  const orderStats = ref([
    { label: 'Total commandes', value: '0', change: 0, color: 'bg-blue-500', icon: 'ShoppingBagIcon' },
    { label: 'Revenus total', value: '0 FCFA', change: 0, color: 'bg-green-500', icon: 'CurrencyDollarIcon' },
    { label: 'En attente', value: '0', change: 0, color: 'bg-yellow-500', icon: 'ClockIcon' },
    { label: 'Terminées', value: '0', change: 0, color: 'bg-green-500', icon: 'CheckIcon' }
  ])
  
  const recentOrders = ref([])
  const monthlyRevenue = ref([])
  
  // Load statistics
  const loadStats = async () => {
    try {
      const response = await get('/stats/merchant/orders')
      
      if (response && response.success) {
        const stats = response.data.stats
        orderStats.value = [
          { 
            label: 'Total commandes', 
            value: stats.total_orders.toString(), 
            change: stats.total_orders_change || 0, 
            color: 'bg-blue-500', 
            icon: 'ShoppingBagIcon' 
          },
          { 
            label: 'Revenus total', 
            value: formatCurrency(stats.total_revenue), 
            change: stats.revenue_change || 0, 
            color: 'bg-green-500', 
            icon: 'CurrencyDollarIcon' 
          },
          { 
            label: 'En attente', 
            value: stats.pending_orders.toString(), 
            change: stats.pending_change || 0, 
            color: 'bg-yellow-500', 
            icon: 'ClockIcon' 
          },
          { 
            label: 'Terminées', 
            value: stats.paid_orders.toString(), 
            change: stats.paid_change || 0, 
            color: 'bg-green-500', 
            icon: 'CheckIcon' 
          }
        ]
        recentOrders.value = response.data.recent_orders || []
        monthlyRevenue.value = response.data.monthly_revenue || []
      }
    } catch (err) {
      console.error('Erreur lors du chargement des statistiques de commandes:', err)
    }
  }
  
  // Load orders with filters
  const loadOrders = async (filters = {}) => {
    try {
      const params = { 
        page: 1,
        per_page: 50,
        ...filters 
      }
      const response = await get('/merchant/orders', { params })
      
      if (response && response.success) {
        orders.value = response.data || []
      }
    } catch (err) {
      console.error('Erreur lors du chargement des commandes:', err)
      orders.value = []
    }
  }
  
  // Load products for filter
  const loadProducts = async () => {
    try {
      const response = await get('/merchant/products', { params: { per_page: 100 } })
      
      if (response && response.success) {
        products.value = response.data.data || response.data || []
      }
    } catch (err) {
      console.error('Erreur lors du chargement des produits:', err)
      products.value = []
    }
  }
  
  // Order actions
  const confirmOrder = async (order) => {
    try {
      const response = await put(`/orders/${order.order_number}/confirm`)
      return response
    } catch (err) {
      console.error('Erreur lors de la confirmation:', err)
      throw err
    }
  }
  
  const cancelOrder = async (order) => {
    try {
      const response = await put(`/orders/${order.order_number}/cancel`)
      return response
    } catch (err) {
      console.error('Erreur lors de l\'annulation:', err)
      throw err
    }
  }
  
  const exportOrders = async (format = 'csv') => {
    try {
      const response = await get(`/merchant/orders/export?format=${format}`)
      return response
    } catch (err) {
      console.error('Erreur lors de l\'export:', err)
      throw err
    }
  }
  
  // Computed stats for cards compatibility
  const stats = computed(() => [
    {
      label: 'Total commandes',
      value: orderStats.value[0]?.value || '0',
      color: 'bg-blue-500',
      icon: 'ShoppingBagIcon'
    },
    {
      label: 'Revenus total',
      value: orderStats.value[1]?.value || '0 FCFA',
      color: 'bg-green-500',
      icon: 'CurrencyDollarIcon'
    },
    {
      label: 'En attente',
      value: orderStats.value[2]?.value || '0',
      color: 'bg-yellow-500',
      icon: 'ClockIcon'
    },
    {
      label: 'Terminées',
      value: orderStats.value[3]?.value || '0',
      color: 'bg-green-500',
      icon: 'CheckIcon'
    }
  ])
  
  // Status distribution for chart
  const statusDistribution = computed(() => [
    {
      name: 'Payées',
      value: parseInt(orderStats.value[3]?.value || '0'),
      color: '#10B981'
    },
    {
      name: 'En attente',
      value: parseInt(orderStats.value[2]?.value || '0'),
      color: '#F59E0B'
    },
    {
      name: 'Annulées',
      value: 0, // Add if available in stats
      color: '#EF4444'
    }
  ])
  
  // Load order stats (alias for compatibility)
  const loadOrderStats = loadStats
  
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
  
  const getStatusLabel = (status) => {
    const statusLabels = {
      'paid': 'Payée',
      'pending': 'En attente',
      'cancelled': 'Annulée',
      'failed': 'Échouée',
      'refunded': 'Remboursée'
    }
    return statusLabels[status] || status
  }
  
  const getStatusColor = (status) => {
    const statusColors = {
      'paid': 'bg-green-100 text-green-800',
      'pending': 'bg-yellow-100 text-yellow-800',
      'cancelled': 'bg-red-100 text-red-800',
      'failed': 'bg-red-100 text-red-800',
      'refunded': 'bg-gray-100 text-gray-800'
    }
    return statusColors[status] || 'bg-gray-100 text-gray-800'
  }
  
  const formatMonthYear = (month, year) => {
    const monthNames = [
      'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun',
      'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'
    ]
    return `${monthNames[month - 1]} ${year}`
  }
  
  return {
    // Data
    orders,
    products,
    orderStats,
    stats,
    recentOrders,
    monthlyRevenue,
    statusDistribution,
    
    // State
    loading,
    error,
    
    // Methods
    loadStats,
    loadOrders,
    loadProducts,
    loadOrderStats,
    confirmOrder,
    cancelOrder,
    exportOrders,
    
    // Utilities
    formatCurrency,
    formatDate,
    getStatusLabel,
    getStatusColor,
    formatMonthYear
  }
}