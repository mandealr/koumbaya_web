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
      if (response && response.data) {
        // Adapter les stats aux besoins de la page Orders
        stats.value = {
          total_orders: parseInt(response.data.tickets_sold || 0),
          revenue: parseFloat(response.data.total_sales || 0),
          pending: parseInt(response.data.pending_orders || 0),
          conversion_rate: parseFloat(response.data.conversion_rate || 0)
        }
      }
      return response
    } catch (err) {
      console.error('Erreur lors du chargement des stats:', err)
      // Valeurs par défaut en cas d'erreur
      stats.value = {
        total_orders: 0,
        revenue: 0,
        pending: 0,
        conversion_rate: 0
      }
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
      if (filters.startDate) params.append('date_from', filters.startDate)
      if (filters.endDate) params.append('date_to', filters.endDate)
      if (filters.limit) params.append('per_page', filters.limit)
      
      const queryString = params.toString()
      const url = `/merchant/orders${queryString ? '?' + queryString : ''}`
      
      const response = await get(url)
      if (response && response.data && response.data.data && Array.isArray(response.data.data)) {
        // Utiliser les vraies commandes avec has_winning_ticket
        orders.value = response.data.data.map(order => ({
          id: order.id,
          order_number: order.order_number,
          customer_name: order.customer_name,
          customer_email: order.customer_email,
          product_name: order.product_name,
          product_image: order.product_image,
          tickets_count: order.tickets_count,
          ticket_numbers: order.ticket_numbers || [],
          ticket_price: order.ticket_price,
          total_amount: order.total_amount,
          currency: 'FCFA',
          payment_method: order.payment_method,
          status: order.status,
          type: order.type,
          has_winning_ticket: order.has_winning_ticket || false,
          created_at: order.created_at,
          updated_at: order.updated_at,
          lottery: order.lottery
        }))
      } else {
        orders.value = []
      }
      return response
    } catch (err) {
      console.error('Erreur lors du chargement des commandes:', err)
      orders.value = []
      throw err
    }
  }
  
  // Charger les produits du marchand
  const loadProducts = async () => {
    try {
      const response = await get('/merchant/dashboard/top-products?limit=100')
      if (response && response.data && Array.isArray(response.data)) {
        products.value = response.data.map(product => ({
          id: product.id,
          name: product.title || product.name || 'Produit sans nom'
        }))
      } else {
        products.value = []
      }
      return response
    } catch (err) {
      console.error('Erreur lors du chargement des produits:', err)
      products.value = []
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
    const calculateChange = (currentValue) => {
      // Si pas de données actuelles, retourner 0
      if (!currentValue || currentValue === 0) {
        return 0
      }
      // TODO: Implémenter une vraie comparaison avec les données de la semaine précédente
      // Pour l'instant, retourner 0 au lieu de valeurs aléatoires
      return 0
    }
    
    return [
      {
        label: 'Total commandes',
        value: (stats.value.total_orders || 0).toString(),
        change: calculateChange(stats.value.total_orders),
        icon: 'ShoppingBagIcon',
        color: 'bg-[#0099cc]'
      },
      {
        label: 'Revenus',
        value: Math.floor(stats.value.revenue || 0).toLocaleString(),
        change: calculateChange(stats.value.revenue),
        icon: 'CurrencyDollarIcon',
        color: 'bg-blue-500'
      },
      {
        label: 'En attente',
        value: (stats.value.pending || 0).toString(),
        change: calculateChange(stats.value.pending),
        icon: 'ClockIcon',
        color: 'bg-yellow-500'
      },
      {
        label: 'Taux conversion',
        value: `${stats.value.conversion_rate || 0}%`,
        change: calculateChange(stats.value.conversion_rate),
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