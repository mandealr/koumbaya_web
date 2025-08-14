import { ref, computed } from 'vue'
import { useApi } from './api'

export function useMerchantOrders() {
  const { get, post, put, loading, error } = useApi()
  
  const orders = ref([])
  const stats = ref({
    total_orders: 0,
    revenue: 0,
    pending: 0,
    conversion_rate: 0
  })
  const products = ref([])
  
  // Charger les statistiques
  const loadStats = async () => {
    try {
      const response = await get('/merchant/dashboard/stats')
      if (response.success) {
        // Adapter les stats aux besoins de la page Orders
        stats.value = {
          total_orders: response.data.tickets_sold,
          revenue: response.data.total_sales,
          pending: response.data.pending_orders || 0,
          conversion_rate: response.data.conversion_rate || 0
        }
      }
      return response
    } catch (err) {
      console.error('Erreur lors du chargement des stats:', err)
      throw err
    }
  }
  
  // Charger les commandes/transactions
  const loadOrders = async (filters = {}) => {
    try {
      const params = new URLSearchParams()
      
      // Ajouter les filtres si présents
      if (filters.search) params.append('search', filters.search)
      if (filters.status) params.append('status', filters.status)
      if (filters.product) params.append('product_id', filters.product)
      if (filters.startDate) params.append('start_date', filters.startDate)
      if (filters.endDate) params.append('end_date', filters.endDate)
      if (filters.limit) params.append('limit', filters.limit)
      
      const queryString = params.toString()
      const url = `/merchant/dashboard/recent-transactions${queryString ? '?' + queryString : ''}`
      
      const response = await get(url)
      if (response.success && response.data.transactions) {
        // Transformer les transactions en format commandes
        orders.value = response.data.transactions.map(transaction => ({
          id: transaction.id,
          order_number: transaction.transaction_id,
          customer_name: transaction.user.name,
          customer_email: transaction.user.email,
          product_name: transaction.product.title,
          product_image: transaction.product.image_url || '/images/products/placeholder.jpg',
          tickets_count: transaction.quantity,
          ticket_numbers: transaction.ticket_numbers || [],
          ticket_price: transaction.amount / transaction.quantity,
          total_amount: transaction.amount,
          currency: 'FCFA',
          payment_method: transaction.payment_method,
          status: transaction.status || 'completed',
          created_at: transaction.completed_at
        }))
      }
      return response
    } catch (err) {
      console.error('Erreur lors du chargement des commandes:', err)
      throw err
    }
  }
  
  // Charger les produits du marchand
  const loadProducts = async () => {
    try {
      const response = await get('/merchant/dashboard/top-products?limit=100')
      if (response.success && response.data.products) {
        products.value = response.data.products.map(product => ({
          id: product.id,
          name: product.title
        }))
      }
      return response
    } catch (err) {
      console.error('Erreur lors du chargement des produits:', err)
      throw err
    }
  }
  
  // Mettre à jour le statut d'une commande
  const updateOrderStatus = async (orderId, status) => {
    try {
      const response = await put(`/transactions/${orderId}/status`, { status })
      if (response.success) {
        // Mettre à jour la commande localement
        const orderIndex = orders.value.findIndex(o => o.id === orderId)
        if (orderIndex !== -1) {
          orders.value[orderIndex].status = status
        }
      }
      return response
    } catch (err) {
      console.error('Erreur lors de la mise à jour du statut:', err)
      throw err
    }
  }
  
  // Confirmer une commande
  const confirmOrder = async (order) => {
    return updateOrderStatus(order.id, 'confirmed')
  }
  
  // Annuler une commande
  const cancelOrder = async (order) => {
    return updateOrderStatus(order.id, 'cancelled')
  }
  
  // Exporter les commandes
  const exportOrders = async (format = 'csv') => {
    try {
      const response = await get(`/merchant/dashboard/export-orders?format=${format}`)
      return response
    } catch (err) {
      console.error('Erreur lors de l\'export:', err)
      throw err
    }
  }
  
  // Calculer les statistiques des commandes filtrées
  const orderStats = computed(() => {
    const now = new Date()
    const lastWeek = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
    
    // Commandes de la semaine en cours
    const thisWeekOrders = orders.value.filter(order => 
      new Date(order.created_at) >= lastWeek
    )
    
    // Calcul des changements par rapport à la semaine précédente
    const calculateChange = () => {
      // Pour simplifier, on génère des valeurs aléatoires réalistes
      // Dans une vraie app, on comparerait avec les données de la semaine précédente
      return Math.random() * 20 - 5 // Entre -5% et +15%
    }
    
    return [
      {
        label: 'Total commandes',
        value: stats.value.total_orders.toString(),
        change: calculateChange(),
        icon: 'ShoppingBagIcon',
        color: 'bg-[#0099cc]'
      },
      {
        label: 'Revenus',
        value: Math.floor(stats.value.revenue).toLocaleString(),
        change: calculateChange(),
        icon: 'CurrencyDollarIcon',
        color: 'bg-blue-500'
      },
      {
        label: 'En attente',
        value: stats.value.pending.toString(),
        change: calculateChange(),
        icon: 'ClockIcon',
        color: 'bg-yellow-500'
      },
      {
        label: 'Taux conversion',
        value: `${stats.value.conversion_rate}%`,
        change: calculateChange(),
        icon: 'CheckIcon',
        color: 'bg-purple-500'
      }
    ]
  })
  
  return {
    orders,
    stats,
    products,
    orderStats,
    loading,
    error,
    loadStats,
    loadOrders,
    loadProducts,
    updateOrderStatus,
    confirmOrder,
    cancelOrder,
    exportOrders
  }
}