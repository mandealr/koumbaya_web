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
      // Utiliser le nouvel endpoint dédié aux statistiques
      const response = await get('/stats/customer/dashboard')
      
      if (response && response.success) {
        const stats = response.data
        
        rawStats.value = {
          ticketsBought: stats.total_tickets || 0,
          lotteriesParticipated: stats.lotteries_participated || 0,
          totalSpent: stats.total_spent || 0,
          prizesWon: stats.prizes_won || 0
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
      // L'API de tickets supporte la pagination, donc on récupère la première page avec 5 éléments
      const response = await get('/tickets/my-tickets')
      
      if (response && response.success && Array.isArray(response.data)) {
        // Prendre seulement les 5 premiers tickets (les plus récents)
        const ticketsData = response.data.slice(0, 5)
        
        recentTickets.value = ticketsData.map(ticket => ({
          id: ticket.id,
          ticket_number: ticket.ticket_number,
          product: {
            id: ticket.lottery?.product?.id,
            title: ticket.lottery?.product?.name || 'Produit inconnu',
            image: ticket.lottery?.product?.image_url || ticket.lottery?.product?.image || '/images/products/placeholder.jpg'
          },
          quantity: 1, // Un ticket = une unité
          total_price: formatCurrency(ticket.price || 0),
          status: ticket.status || 'reserved',
          purchased_at: new Date(ticket.purchased_at || ticket.created_at),
          lottery: {
            id: ticket.lottery?.id,
            lottery_number: ticket.lottery?.lottery_number,
            title: ticket.lottery?.title,
            draw_date: ticket.lottery?.draw_date
          }
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