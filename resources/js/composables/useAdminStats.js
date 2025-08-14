import { ref, computed } from 'vue'
import { useApi } from './api'

export function useAdminStats() {
  const { get, loading, error } = useApi()
  
  // Reactive data
  const rawStats = ref({
    users: { total: 0, active: 0, growth: 0 },
    products: { total: 0, active: 0, growth: 0 },
    lotteries: { total: 0, active: 0, growth: 0 },
    revenue: { total: 0, monthly: 0, growth: 0 }
  })
  
  const recentLotteries = ref([])
  const recentActivities = ref([])
  const topProducts = ref([])
  
  // Computed stats for dashboard cards
  const stats = computed(() => [
    {
      label: 'Utilisateurs actifs',
      value: formatNumber(rawStats.value.users.active),
      change: rawStats.value.users.growth,
      icon: 'UsersIcon',
      bgColor: 'bg-[#0099cc]/10',
      iconColor: 'text-[#0099cc]'
    },
    {
      label: 'Produits en vente',
      value: formatNumber(rawStats.value.products.active),
      change: rawStats.value.products.growth,
      icon: 'ShoppingBagIcon',
      bgColor: 'bg-blue-100',
      iconColor: 'text-blue-600'
    },
    {
      label: 'Tombolas actives',
      value: formatNumber(rawStats.value.lotteries.active),
      change: rawStats.value.lotteries.growth,
      icon: 'GiftIcon',
      bgColor: 'bg-yellow-100',
      iconColor: 'text-yellow-600'
    },
    {
      label: 'Revenus mensuel',
      value: formatCurrency(rawStats.value.revenue.monthly),
      change: rawStats.value.revenue.growth,
      icon: 'CurrencyDollarIcon',
      bgColor: 'bg-purple-100',
      iconColor: 'text-purple-600'
    }
  ])
  
  // API calls
  const loadDashboardStats = async () => {
    try {
      const response = await get('/admin/dashboard/stats')
      
      if (response && response.success) {
        rawStats.value = {
          users: {
            total: response.data.users?.total || 0,
            active: response.data.users?.active || 0,
            growth: response.data.users?.growth || 0
          },
          products: {
            total: response.data.products?.total || 0,
            active: response.data.products?.active || 0,
            growth: response.data.products?.growth || 0
          },
          lotteries: {
            total: response.data.lotteries?.total || 0,
            active: response.data.lotteries?.active || 0,
            growth: response.data.lotteries?.growth || 0
          },
          revenue: {
            total: response.data.revenue?.total || 0,
            monthly: response.data.revenue?.monthly || 0,
            growth: response.data.revenue?.growth || 0
          }
        }
      }
    } catch (err) {
      console.error('Erreur lors du chargement des statistiques:', err)
      // Fallback with dummy data for development
      rawStats.value = {
        users: { total: 2543, active: 1876, growth: 12.5 },
        products: { total: 186, active: 134, growth: 8.2 },
        lotteries: { total: 45, active: 24, growth: -2.1 },
        revenue: { total: 125000000, monthly: 45200000, growth: 15.3 }
      }
    }
  }
  
  const loadRecentLotteries = async () => {
    try {
      const response = await get('/admin/lotteries/recent?limit=5')
      
      if (response && response.success) {
        recentLotteries.value = response.data.map(lottery => ({
          id: lottery.id,
          lottery_number: lottery.lottery_number,
          product: {
            name: lottery.product?.name || 'Produit inconnu',
            image: lottery.product?.image_url || lottery.product?.main_image || '/images/products/placeholder.jpg'
          },
          status: lottery.status,
          statusLabel: getStatusLabel(lottery.status),
          sold_tickets: lottery.sold_tickets || 0,
          total_tickets: lottery.total_tickets || 0,
          end_date: lottery.end_date
        }))
      }
    } catch (err) {
      console.error('Erreur lors du chargement des tombolas récentes:', err)
      // Fallback data
      recentLotteries.value = []
    }
  }
  
  const loadRecentActivities = async () => {
    try {
      const response = await get('/admin/activities/recent?limit=10')
      
      if (response && response.success) {
        recentActivities.value = response.data.map(activity => ({
          id: activity.id,
          type: activity.type,
          icon: getActivityIcon(activity.type),
          title: activity.title,
          description: activity.description,
          time: new Date(activity.created_at)
        }))
      }
    } catch (err) {
      console.error('Erreur lors du chargement des activités récentes:', err)
      // Fallback data
      recentActivities.value = []
    }
  }
  
  const loadTopProducts = async () => {
    try {
      const response = await get('/admin/products/top?limit=5')
      
      if (response && response.success) {
        topProducts.value = response.data.map(product => ({
          id: product.id,
          title: product.name,
          sales: product.total_tickets_sold || 0,
          growth: product.growth_percentage || 0,
          image: product.image_url || product.main_image || '/images/products/placeholder.jpg'
        }))
      }
    } catch (err) {
      console.error('Erreur lors du chargement des produits populaires:', err)
      // Fallback data
      topProducts.value = []
    }
  }
  
  // Helper functions
  const formatNumber = (num) => {
    if (num >= 1000000) {
      return (num / 1000000).toFixed(1) + 'M'
    }
    if (num >= 1000) {
      return (num / 1000).toFixed(1) + 'k'
    }
    return num.toLocaleString()
  }
  
  const formatCurrency = (amount) => {
    if (amount >= 1000000) {
      return (amount / 1000000).toFixed(1) + 'M FCFA'
    }
    if (amount >= 1000) {
      return (amount / 1000).toFixed(0) + 'k FCFA'
    }
    return amount.toLocaleString() + ' FCFA'
  }
  
  const getStatusLabel = (status) => {
    const labels = {
      'active': 'Active',
      'completed': 'Terminée',
      'pending': 'En attente',
      'cancelled': 'Annulée',
      'draft': 'Brouillon'
    }
    return labels[status] || status
  }
  
  const getActivityIcon = (type) => {
    const icons = {
      'user': 'UsersIcon',
      'product': 'ShoppingBagIcon',
      'payment': 'CurrencyDollarIcon',
      'lottery': 'GiftIcon',
      'transaction': 'BanknotesIcon'
    }
    return icons[type] || 'InformationCircleIcon'
  }
  
  // Load all dashboard data
  const loadDashboardData = async () => {
    await Promise.all([
      loadDashboardStats(),
      loadRecentLotteries(),
      loadRecentActivities(),
      loadTopProducts()
    ])
  }
  
  return {
    // Data
    stats,
    recentLotteries,
    recentActivities,
    topProducts,
    
    // State
    loading,
    error,
    
    // Methods
    loadDashboardData,
    loadDashboardStats,
    loadRecentLotteries,
    loadRecentActivities,
    loadTopProducts,
    
    // Utilities
    formatNumber,
    formatCurrency,
    getStatusLabel,
    getActivityIcon
  }
}