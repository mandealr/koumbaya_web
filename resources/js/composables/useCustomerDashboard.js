import { ref, computed } from 'vue'
import { useApi } from './api'

export function useCustomerDashboard() {
  const { get, loading, error } = useApi()
  
  // Reactive data
  const rawStats = ref({
    ticketsBought: 0,
    lotteriesParticipated: 0,
    totalSpent: 0,
    prizesWon: 0
  })
  
  const recentTickets = ref([])
  const activeLotteries = ref([])
  
  // Computed stats for dashboard cards
  const stats = computed(() => [
    {
      label: 'Tickets achetés',
      value: rawStats.value.ticketsBought.toString(),
      color: 'bg-blue-500',
      icon: 'TicketIcon'
    },
    {
      label: 'Tombolas participées',
      value: rawStats.value.lotteriesParticipated.toString(),
      color: 'bg-[#0099cc]',
      icon: 'GiftIcon'
    },
    {
      label: 'Total dépensé',
      value: formatCurrency(rawStats.value.totalSpent),
      color: 'bg-yellow-500',
      icon: 'CurrencyDollarIcon'
    },
    {
      label: 'Prix gagnés',
      value: rawStats.value.prizesWon.toString(),
      color: 'bg-purple-500',
      icon: 'TrophyIcon'
    }
  ])
  
  // API calls
  const loadDashboardStats = async () => {
    try {
      const response = await get('/tickets/my-tickets')
      
      if (response && response.success) {
        const tickets = Array.isArray(response.data) ? response.data : []
        
        // Calculate stats from tickets
        const uniqueLotteries = new Set()
        let totalSpent = 0
        let prizesWon = 0
        
        tickets.forEach(ticket => {
          if (ticket.lottery_id) {
            uniqueLotteries.add(ticket.lottery_id)
          }
          totalSpent += parseFloat(ticket.amount || 0)
          if (ticket.status === 'won') {
            prizesWon++
          }
        })
        
        rawStats.value = {
          ticketsBought: tickets.length,
          lotteriesParticipated: uniqueLotteries.size,
          totalSpent,
          prizesWon
        }
      }
    } catch (err) {
      console.error('Erreur lors du chargement des statistiques:', err)
      // Fallback with empty data for new users
      rawStats.value = {
        ticketsBought: 0,
        lotteriesParticipated: 0,
        totalSpent: 0,
        prizesWon: 0
      }
    }
  }
  
  const loadRecentTickets = async () => {
    try {
      const response = await get('/tickets/my-tickets?limit=5&order=desc')
      
      if (response && response.success && Array.isArray(response.data)) {
        recentTickets.value = response.data.map(ticket => ({
          id: ticket.id,
          product: {
            id: ticket.product?.id || ticket.lottery?.product?.id,
            title: ticket.product?.name || ticket.lottery?.product?.name || 'Produit inconnu',
            image: ticket.product?.image_url || ticket.lottery?.product?.image_url || '/images/products/placeholder.jpg'
          },
          quantity: ticket.quantity || 1,
          total_price: formatCurrency(ticket.amount || 0),
          status: ticket.status || 'active',
          purchased_at: new Date(ticket.created_at)
        }))
      } else {
        // Si pas de données valides, initialiser avec un tableau vide
        recentTickets.value = []
      }
    } catch (err) {
      console.error('Erreur lors du chargement des tickets récents:', err)
      // Fallback data
      recentTickets.value = []
    }
  }
  
  const loadActiveLotteries = async () => {
    try {
      const response = await get('/lotteries/active?limit=6')
      
      if (response && response.success && Array.isArray(response.data)) {
        activeLotteries.value = response.data.map(lottery => ({
          id: lottery.id,
          product: {
            id: lottery.product?.id,
            title: lottery.product?.name || 'Produit inconnu',
            price: formatCurrency(lottery.product?.price || 0),
            image: lottery.product?.image_url || lottery.product?.main_image || '/images/products/placeholder.jpg'
          },
          progress: Math.round((lottery.sold_tickets / lottery.total_tickets) * 100) || 0,
          draw_date: new Date(lottery.end_date)
        }))
      } else {
        // Si pas de données valides, initialiser avec un tableau vide
        activeLotteries.value = []
      }
    } catch (err) {
      console.error('Erreur lors du chargement des tombolas actives:', err)
      // Fallback data
      activeLotteries.value = []
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
    return num.toLocaleString() + ' FCFA'
  }
  
  const formatDate = (date) => {
    return new Intl.DateTimeFormat('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    }).format(date)
  }
  
  // Load all dashboard data
  const loadDashboardData = async () => {
    await Promise.all([
      loadDashboardStats(),
      loadRecentTickets(),
      loadActiveLotteries()
    ])
  }
  
  return {
    // Data
    stats,
    recentTickets,
    activeLotteries,
    
    // State
    loading,
    error,
    
    // Methods
    loadDashboardData,
    loadDashboardStats,
    loadRecentTickets,
    loadActiveLotteries,
    
    // Utilities
    formatCurrency,
    formatDate
  }
}