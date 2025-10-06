import { ref, computed } from 'vue'
import { useApi } from './api.js'

export function useMerchantLotteries() {
  const { get, post, loading, error } = useApi()
  
  // État réactif
  const lotteries = ref([])
  const stats = ref({
    total: 0,
    active: 0,
    completed: 0,
    pending: 0,
    cancelled: 0
  })
  const filters = ref({
    status: 'all',
    search: '',
    sortBy: 'end_date_asc'
  })
  const pagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    last_page: 1
  })

  // Charger les tombolas du merchant connecté
  const fetchLotteries = async (params = {}) => {
    try {
      // Construire les paramètres de requête
      const queryParams = {
        per_page: filters.value.per_page || 15,
        page: params.page || 1,
        sort_by: filters.value.sortBy,
        ...params
      }

      // Ajouter les filtres si définis
      if (filters.value.status && filters.value.status !== 'all') {
        queryParams.status = filters.value.status
      }

      if (filters.value.search) {
        queryParams.search = filters.value.search
      }

      // Utiliser la route lottery-performance qui existe et fonctionne déjà (avec pagination elle retourne la liste)
      const response = await get('/merchant/dashboard/lottery-performance', { params: queryParams })

      if (response) {
        // Gérer différents formats de réponse
        // L'API peut retourner: response.lotteries, response.data.data, response.data.lotteries, ou response.data
        const lotteriesData = response.lotteries || response.data?.data || response.data?.lotteries || response.data || []
        lotteries.value = Array.isArray(lotteriesData) ? lotteriesData : []

        // Mettre à jour la pagination si présente
        const paginationData = response.pagination || response.data?.pagination || response.meta
        if (paginationData) {
          pagination.value = {
            current_page: paginationData.current_page || 1,
            per_page: paginationData.per_page || 15,
            total: paginationData.total || 0,
            last_page: paginationData.last_page || 1
          }
        }

        // Mettre à jour les statistiques du serveur si disponibles
        const statsData = response.stats || response.data?.stats
        if (statsData) {
          stats.value = {
            total: statsData.total || 0,
            active: statsData.active || 0,
            pending: statsData.pending || 0,
            completed: statsData.completed || 0,
            cancelled: statsData.cancelled || 0
          }
        } else {
          // Calculer les statistiques localement en fallback
          calculateStats()
        }
      } else {
        // Réponse vide ou erreur
        lotteries.value = []
        calculateStats()
      }

      return response
    } catch (err) {
      console.error('Erreur lors du chargement des tombolas:', err)
      throw err
    }
  }

  // Calculer les statistiques à partir des tombolas chargées
  const calculateStats = () => {
    const total = lotteries.value.length
    const active = lotteries.value.filter(l => l.status === 'active').length
    const completed = lotteries.value.filter(l => l.status === 'completed').length
    const pending = lotteries.value.filter(l => l.status === 'pending').length
    const cancelled = lotteries.value.filter(l => l.status === 'cancelled').length

    stats.value = {
      total,
      active,
      completed,
      pending,
      cancelled
    }
  }

  // Effectuer un tirage de tombola
  const drawLottery = async (lotteryId) => {
    try {
      const response = await post(`/lotteries/${lotteryId}/draw`)
      
      if (response.success || response.message) {
        // Mettre à jour la tombola dans la liste locale
        const index = lotteries.value.findIndex(l => l.id === lotteryId)
        if (index !== -1) {
          lotteries.value[index] = {
            ...lotteries.value[index],
            status: 'completed',
            is_drawn: true,
            winner_user_id: response.lottery?.winner_user_id,
            winner_ticket_number: response.lottery?.winner_ticket_number,
            draw_date: response.lottery?.draw_date
          }
        }
        
        // Recalculer les stats
        calculateStats()
      }
      
      return response
    } catch (err) {
      console.error('Erreur lors du tirage:', err)
      throw err
    }
  }

  // Obtenir les détails d'une tombola
  const getLotteryDetails = async (lotteryId) => {
    try {
      const response = await get(`/lotteries/${lotteryId}`)
      return response.lottery || response.data
    } catch (err) {
      console.error('Erreur lors du chargement des détails:', err)
      throw err
    }
  }

  // Mettre à jour les filtres et recharger
  const updateFilters = async (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    await fetchLotteries()
  }

  // Rechercher des tombolas
  const searchLotteries = async (searchTerm) => {
    filters.value.search = searchTerm
    await fetchLotteries()
  }

  // Changer de page
  const changePage = async (page) => {
    await fetchLotteries({ page })
  }

  // Computed pour les onglets avec compteurs
  const tabs = computed(() => [
    { 
      key: 'all', 
      label: 'Toutes', 
      count: stats.value.total 
    },
    { 
      key: 'active', 
      label: 'Actives', 
      count: stats.value.active 
    },
    { 
      key: 'pending', 
      label: 'En attente', 
      count: stats.value.pending 
    },
    { 
      key: 'completed', 
      label: 'Terminées', 
      count: stats.value.completed 
    },
    { 
      key: 'cancelled', 
      label: 'Annulées', 
      count: stats.value.cancelled 
    }
  ])

  // Computed pour les tombolas filtrées localement (pour la recherche instantanée)
  const filteredLotteries = computed(() => {
    let filtered = [...lotteries.value]

    // Filtrage par recherche locale (en plus de la recherche serveur)
    if (filters.value.search) {
      const search = filters.value.search.toLowerCase()
      filtered = filtered.filter(lottery => 
        lottery.lottery_number?.toLowerCase().includes(search) ||
        lottery.product?.name?.toLowerCase().includes(search) ||
        lottery.product?.description?.toLowerCase().includes(search)
      )
    }

    return filtered
  })

  // Vérifier si une tombola peut être tirée
  const canDrawLottery = (lottery) => {
    // Vérifications de base
    if (lottery.status !== 'active' || lottery.is_drawn) {
      return false
    }
    
    // Calcul des tickets vendus vs total
    const soldTickets = lottery.sold_tickets || 0
    const totalTickets = lottery.total_tickets || lottery.max_tickets || 0
    const allTicketsSold = soldTickets >= totalTickets && totalTickets > 0
    
    // Date de fin atteinte
    const endDateReached = new Date(lottery.end_date) <= new Date()
    
    // Peut tirer si : tous les tickets sont vendus OU date de fin atteinte
    return allTicketsSold || endDateReached
  }

  // Obtenir le statut formaté d'une tombola
  const getFormattedStatus = (status) => {
    const statusMap = {
      'pending': { label: 'En attente', class: 'bg-yellow-100 text-yellow-800' },
      'active': { label: 'Active', class: 'bg-green-100 text-green-800' },
      'completed': { label: 'Terminée', class: 'bg-blue-100 text-blue-800' },
      'cancelled': { label: 'Annulée', class: 'bg-red-100 text-red-800' }
    }
    return statusMap[status] || { label: status, class: 'bg-gray-100 text-gray-800' }
  }

  // Obtenir le temps restant formaté
  const getTimeRemaining = (endDate) => {
    const now = new Date()
    const end = new Date(endDate)
    const diff = end - now

    if (diff <= 0) return 'Terminée'

    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

    if (days > 0) return `${days}j ${hours}h`
    if (hours > 0) return `${hours}h ${minutes}min`
    return `${minutes}min`
  }

  // Déterminer le type de tirage disponible
  const getDrawType = (lottery) => {
    if (lottery.status !== 'active' || lottery.is_drawn) {
      return null
    }
    
    const soldTickets = lottery.sold_tickets || 0
    const totalTickets = lottery.total_tickets || lottery.max_tickets || 0
    const allTicketsSold = soldTickets >= totalTickets && totalTickets > 0
    const endDateReached = new Date(lottery.end_date) <= new Date()
    
    if (allTicketsSold && !endDateReached) {
      return 'manual' // Tirage manuel disponible (quota atteint)
    } else if (endDateReached) {
      return 'automatic' // Tirage automatique (date atteinte)
    }
    
    return null // Pas encore de tirage possible
  }

  // Obtenir le message de tirage
  const getDrawMessage = (lottery) => {
    const drawType = getDrawType(lottery)
    
    if (drawType === 'manual') {
      return 'Tirage manuel disponible (quota atteint)'
    } else if (drawType === 'automatic') {
      return 'Tirage automatique (date atteinte)'
    }
    
    return null
  }

  return {
    // État
    lotteries: filteredLotteries,
    stats,
    filters,
    pagination,
    tabs,
    loading,
    error,

    // Actions
    fetchLotteries,
    drawLottery,
    getLotteryDetails,
    updateFilters,
    searchLotteries,
    changePage,

    // Utilitaires
    canDrawLottery,
    getFormattedStatus,
    getTimeRemaining,
    getDrawType,
    getDrawMessage,
    
    // Stats
    loadLotteryStats: async () => {
      try {
        const response = await get('/stats/merchant/lotteries')
        if (response && response.success) {
          // Update stats if needed
          const lotteryStats = response.data.stats
          stats.value = {
            total: lotteryStats.total_lotteries,
            active: lotteryStats.active_lotteries,
            pending: lotteryStats.pending_lotteries,
            completed: lotteryStats.completed_lotteries,
            cancelled: lotteryStats.cancelled_lotteries
          }
          return response.data
        }
      } catch (err) {
        console.error('Erreur lors du chargement des stats tombolas:', err)
      }
    }
  }
}