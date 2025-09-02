import { ref, computed, watch } from 'vue'
import { useApi } from './api'

export function useMyTickets() {
  const { get, post, loading, error } = useApi()
  
  // Reactive data
  const tickets = ref([])
  const stats = ref({
    totalTickets: 0,
    prizesWon: 0,
    totalSpent: 0,
    activeTickets: 0
  })
  
  // Pagination
  const currentPage = ref(1)
  const hasMore = ref(true)
  const perPage = ref(10)
  const total = ref(0)
  
  // Filters
  const filters = ref({
    search: '',
    status: '',
    period: ''
  })
  
  // Computed properties
  const filteredTickets = computed(() => {
    let filtered = tickets.value
    
    if (filters.value.search) {
      const search = filters.value.search.toLowerCase()
      filtered = filtered.filter(ticket => 
        ticket.product.title.toLowerCase().includes(search) ||
        ticket.product.description.toLowerCase().includes(search)
      )
    }
    
    if (filters.value.status) {
      filtered = filtered.filter(ticket => ticket.status === filters.value.status)
    }
    
    if (filters.value.period) {
      const now = new Date()
      const period = filters.value.period
      
      filtered = filtered.filter(ticket => {
        const purchaseDate = new Date(ticket.purchased_at)
        
        if (period === 'week') {
          const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
          return purchaseDate >= weekAgo
        } else if (period === 'month') {
          const monthAgo = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate())
          return purchaseDate >= monthAgo
        } else if (period === 'quarter') {
          const quarterAgo = new Date(now.getFullYear(), now.getMonth() - 3, now.getDate())
          return purchaseDate >= quarterAgo
        }
        
        return true
      })
    }
    
    return filtered.sort((a, b) => new Date(b.purchased_at) - new Date(a.purchased_at))
  })
  
  const statsCards = computed(() => [
    {
      label: 'Total tickets',
      value: stats.value.totalTickets.toString(),
      color: 'bg-blue-500',
      icon: 'TicketIcon'
    },
    {
      label: 'Prix gagnés',
      value: stats.value.prizesWon.toString(),
      color: 'bg-blue-500',
      icon: 'TrophyIcon'
    },
    {
      label: 'Total dépensé',
      value: formatCurrency(stats.value.totalSpent),
      color: 'bg-yellow-500',
      icon: 'CurrencyDollarIcon'
    },
    {
      label: 'En cours',
      value: stats.value.activeTickets.toString(),
      color: 'bg-purple-500',
      icon: 'ClockIcon'
    }
  ])
  
  // API methods
  const loadTickets = async (page = 1, append = false) => {
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        per_page: perPage.value.toString()
      })
      
      // Add filter parameters if they exist
      if (filters.value.search) {
        params.append('search', filters.value.search)
      }
      if (filters.value.status) {
        params.append('status', filters.value.status)
      }
      if (filters.value.period) {
        params.append('period', filters.value.period)
      }
      
      const response = await get(`/tickets/my-tickets?${params.toString()}`)
      
      if (response && response.success) {
        const ticketsData = response.data || []
        const pagination = response.pagination || {}
        
        // Transform API response to match expected format
        const transformedTickets = ticketsData.map(ticket => ({
          id: ticket.id,
          product: {
            id: ticket.product?.id || ticket.lottery?.product?.id,
            title: ticket.product?.name || ticket.lottery?.product?.name || 'Produit inconnu',
            description: ticket.product?.description || ticket.lottery?.product?.description || '',
            image: ticket.product?.image_url || ticket.lottery?.product?.image_url || '/images/products/placeholder.jpg'
          },
          lottery: {
            id: ticket.lottery?.id,
            draw_date: new Date(ticket.lottery?.end_date || ticket.lottery?.draw_date),
            progress: Math.round((ticket.lottery?.sold_tickets / ticket.lottery?.total_tickets) * 100) || 0,
            winning_number: ticket.lottery?.winning_number || null
          },
          quantity: ticket.quantity || 1,
          total_price: ticket.amount || 0,
          status: ticket.status || 'active',
          purchased_at: new Date(ticket.created_at),
          ticket_numbers: ticket.ticket_numbers || [],
          winning_number: ticket.winning_number || null,
          prize_claimed: ticket.prize_claimed || false
        }))
        
        if (append) {
          tickets.value = [...tickets.value, ...transformedTickets]
        } else {
          tickets.value = transformedTickets
        }
        
        // Update pagination
        currentPage.value = pagination.current_page || page
        total.value = pagination.total || transformedTickets.length
        hasMore.value = pagination.current_page < pagination.last_page
        
        // Calculate stats
        calculateStats()
      }
    } catch (err) {
      console.error('Erreur lors du chargement des tickets:', err)
      // Keep existing tickets on error, don't clear them
    }
  }
  
  const loadMore = async () => {
    if (hasMore.value && !loading.value) {
      await loadTickets(currentPage.value + 1, true)
    }
  }
  
  const refreshTickets = async () => {
    currentPage.value = 1
    await loadTickets(1, false)
  }
  
  const calculateStats = () => {
    const totalTickets = tickets.value.length
    const prizesWon = tickets.value.filter(t => t.status === 'won').length
    const totalSpent = tickets.value.reduce((sum, ticket) => {
      const amount = parseFloat(ticket.total_price) || 0
      return sum + amount
    }, 0)
    const activeTickets = tickets.value.filter(t => t.status === 'active' || t.status === 'pending').length
    
    stats.value = {
      totalTickets,
      prizesWon,
      totalSpent,
      activeTickets
    }
  }
  
  const claimPrize = async (ticketId) => {
    try {
      const response = await post(`/tickets/${ticketId}/claim-prize`)
      
      if (response && response.success) {
        // Update local ticket state
        const ticketIndex = tickets.value.findIndex(t => t.id === ticketId)
        if (ticketIndex !== -1) {
          tickets.value[ticketIndex].prize_claimed = true
        }
        return response
      }
    } catch (err) {
      console.error('Erreur lors de la réclamation du prix:', err)
      throw err
    }
  }
  
  const downloadTicket = async (ticketId) => {
    try {
      // Use the API instance directly for blob downloads
      const response = await get(`/tickets/${ticketId}/download`)
      
      if (response) {
        // If the API returns a download URL
        if (typeof response === 'string' && response.startsWith('http')) {
          const link = document.createElement('a')
          link.href = response
          link.setAttribute('download', `ticket-${ticketId}.pdf`)
          link.setAttribute('target', '_blank')
          document.body.appendChild(link)
          link.click()
          link.remove()
        } else {
          // If the API returns blob data directly
          const url = window.URL.createObjectURL(new Blob([response]))
          const link = document.createElement('a')
          link.href = url
          link.setAttribute('download', `ticket-${ticketId}.pdf`)
          document.body.appendChild(link)
          link.click()
          link.remove()
          window.URL.revokeObjectURL(url)
        }
      }
      
    } catch (err) {
      console.error('Erreur lors du téléchargement du ticket:', err)
      throw err
    }
  }
  
  // Filter methods
  const resetFilters = () => {
    filters.value = {
      search: '',
      status: '',
      period: ''
    }
    refreshTickets()
  }
  
  const applyFilters = () => {
    refreshTickets()
  }
  
  // Watch for filter changes and auto-apply them
  watch(
    () => filters.value,
    () => {
      // Debounce the filter application
      clearTimeout(filterTimeout)
      filterTimeout = setTimeout(() => {
        applyFilters()
      }, 500)
    },
    { deep: true }
  )
  
  let filterTimeout = null
  
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
  
  const getRemainingTime = (date, status) => {
    if (status === 'lost' || status === 'won') {
      return 'Tirage terminé'
    }
    
    const now = new Date()
    const drawDate = new Date(date)
    const diff = drawDate - now
    
    if (diff <= 0) {
      return 'Tirage en cours'
    }
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    
    if (days > 0) {
      return `Tirage dans ${days} jour${days > 1 ? 's' : ''}`
    } else if (hours > 0) {
      return `Tirage dans ${hours}h`
    } else {
      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
      return `Tirage dans ${minutes}min`
    }
  }
  
  const getStatusLabel = (status) => {
    const labels = {
      'active': 'En cours',
      'won': 'Gagné',
      'lost': 'Perdu',
      'pending': 'Tirage imminent',
      'reserved': 'Réservé',
      'paid': 'Payé'
    }
    return labels[status] || status
  }
  
  return {
    // Data
    tickets,
    filteredTickets,
    stats: statsCards,
    filters,
    
    // Pagination
    currentPage,
    hasMore,
    total,
    
    // State
    loading,
    error,
    
    // Methods
    loadTickets,
    loadMore,
    refreshTickets,
    claimPrize,
    downloadTicket,
    resetFilters,
    applyFilters,
    
    // Utilities
    formatCurrency,
    formatDate,
    getRemainingTime,
    getStatusLabel
  }
}