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

      // Utiliser la route spécialisée pour les merchants
      const response = await get('/lotteries/my-lotteries', { params: queryParams })
      
      if (response.success) {
        lotteries.value = response.data.data || response.data
        
        // Mettre à jour la pagination si présente
        if (response.data.current_page) {
          pagination.value = {
            current_page: response.data.current_page,
            per_page: response.data.per_page,
            total: response.data.total,
            last_page: response.data.last_page
          }
        }

        // Mettre à jour les statistiques du serveur si disponibles
        if (response.stats) {
          stats.value = response.stats
        } else {
          // Calculer les statistiques localement en fallback
          calculateStats()
        }
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
    return lottery.status === 'active' && 
           !lottery.is_drawn && 
           lottery.can_draw === true &&
           new Date(lottery.end_date) <= new Date()
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
    getTimeRemaining
  }
}