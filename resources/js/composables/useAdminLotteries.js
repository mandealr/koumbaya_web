import { ref, reactive, computed } from 'vue'
import { useToast } from '@/composables/useToast'

export function useAdminLotteries() {
  const toast = useToast()
  
  // State
  const lotteries = ref([])
  const statistics = ref({
    total_lotteries: 0,
    active_lotteries: 0,
    completed_lotteries: 0,
    pending_draws: 0,
    total_revenue: 0,
    total_participants: 0,
    average_participation_rate: 0,
    recent_draws: [],
    monthly_revenue: []
  })
  const eligibleLotteries = ref([])
  const selectedLottery = ref(null)
  const loading = ref(false)
  const pagination = reactive({
    current_page: 1,
    per_page: 20,
    total: 0,
    last_page: 1
  })

  // API base URL
  const API_BASE = '/api/admin/lotteries'

  // Helper function for API calls
  const apiCall = async (url, options = {}) => {
    const token = localStorage.getItem('auth_token')
    const headers = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...options.headers
    }

    const response = await fetch(url, { ...options, headers })
    
    if (!response.ok) {
      const error = await response.json()
      throw new Error(error.message || 'API Error')
    }
    
    return response.json()
  }

  // Fetch functions
  const fetchLotteries = async (filters = {}) => {
    try {
      loading.value = true
      const params = new URLSearchParams(filters)
      const data = await apiCall(`${API_BASE}?${params}`)
      
      lotteries.value = data.data
      pagination.current_page = data.current_page
      pagination.per_page = data.per_page
      pagination.total = data.total
      pagination.last_page = data.last_page
      
      return { success: true, data }
    } catch (error) {
      toast.error('Erreur lors du chargement des tombolas')
      return { success: false, error: error.message }
    } finally {
      loading.value = false
    }
  }

  const fetchStatistics = async () => {
    try {
      const data = await apiCall(`${API_BASE}/statistics`)
      statistics.value = data
      return { success: true, data }
    } catch (error) {
      toast.error('Erreur lors du chargement des statistiques')
      return { success: false, error: error.message }
    }
  }

  const fetchEligibleLotteries = async () => {
    try {
      const data = await apiCall(`${API_BASE}/eligible-for-draw`)
      eligibleLotteries.value = data.lotteries
      return { success: true, data }
    } catch (error) {
      toast.error('Erreur lors du chargement des tirages éligibles')
      return { success: false, error: error.message }
    }
  }

  const fetchLotteryDetails = async (lotteryId) => {
    try {
      const data = await apiCall(`${API_BASE}/${lotteryId}`)
      selectedLottery.value = data
      return { success: true, data }
    } catch (error) {
      toast.error('Erreur lors du chargement des détails')
      return { success: false, error: error.message }
    }
  }

  // Draw functions
  const performDraw = async (lotteryId) => {
    try {
      const data = await apiCall(`${API_BASE}/${lotteryId}/draw`, {
        method: 'POST'
      })
      
      toast.success('Tirage effectué avec succès !')
      return { success: true, data }
    } catch (error) {
      toast.error('Erreur lors du tirage: ' + error.message)
      return { success: false, error: error.message }
    }
  }

  const performBatchDraw = async (lotteryIds) => {
    try {
      const data = await apiCall(`${API_BASE}/batch-draw`, {
        method: 'POST',
        body: JSON.stringify({ lottery_ids: lotteryIds })
      })
      
      const { success, failed } = data.summary
      toast.success(`${success} tirages effectués, ${failed} échecs`)
      return { success: true, data }
    } catch (error) {
      toast.error('Erreur lors des tirages groupés: ' + error.message)
      return { success: false, error: error.message }
    }
  }

  const verifyLotteryDraw = async (lotteryId) => {
    try {
      const data = await apiCall(`/api/lotteries/${lotteryId}/verify-draw`)
      return data
    } catch (error) {
      toast.error('Erreur lors de la vérification')
      return { valid: false, message: error.message }
    }
  }

  // Management functions
  const cancelLottery = async (lotteryId, reason) => {
    try {
      const data = await apiCall(`${API_BASE}/${lotteryId}/cancel`, {
        method: 'POST',
        body: JSON.stringify({ reason })
      })
      
      toast.success('Tombola annulée avec succès')
      return { success: true, data }
    } catch (error) {
      toast.error('Erreur lors de l\'annulation: ' + error.message)
      return { success: false, error: error.message }
    }
  }

  const updateLottery = async (lotteryId, updates) => {
    try {
      const data = await apiCall(`${API_BASE}/${lotteryId}`, {
        method: 'PUT',
        body: JSON.stringify(updates)
      })
      
      toast.success('Tombola mise à jour')
      return { success: true, data }
    } catch (error) {
      toast.error('Erreur lors de la mise à jour: ' + error.message)
      return { success: false, error: error.message }
    }
  }

  const exportLotteryData = async (lotteryId) => {
    try {
      const data = await apiCall(`${API_BASE}/${lotteryId}/export`)
      
      // Create and download file
      const blob = new Blob([JSON.stringify(data, null, 2)], { 
        type: 'application/json' 
      })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `lottery-${data.lottery.lottery_number}.json`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      URL.revokeObjectURL(url)
      
      toast.success('Export terminé')
      return { success: true }
    } catch (error) {
      toast.error('Erreur lors de l\'export: ' + error.message)
      return { success: false, error: error.message }
    }
  }

  // Computed properties
  const totalRevenue = computed(() => {
    return lotteries.value.reduce((total, lottery) => {
      return total + (lottery.sold_tickets * lottery.ticket_price)
    }, 0)
  })

  const averageParticipation = computed(() => {
    if (lotteries.value.length === 0) return 0
    const totalRate = lotteries.value.reduce((total, lottery) => {
      return total + ((lottery.sold_tickets / lottery.total_tickets) * 100)
    }, 0)
    return Math.round(totalRate / lotteries.value.length)
  })

  // Utility functions
  const getLotteryStatus = (lottery) => {
    if (lottery.is_drawn) return 'drawn'
    if (lottery.status === 'active' && new Date(lottery.end_date) <= new Date()) {
      return 'ready_for_draw'
    }
    return lottery.status
  }

  const canDraw = (lottery) => {
    return lottery.status === 'active' && 
           !lottery.is_drawn && 
           new Date(lottery.end_date) <= new Date() &&
           (lottery.participants_count || 0) >= (lottery.product?.min_participants || 300)
  }

  const getProgressPercentage = (lottery) => {
    if (!lottery.total_tickets || lottery.total_tickets === 0) return 0
    return Math.round((lottery.sold_tickets / lottery.total_tickets) * 100)
  }

  return {
    // State
    lotteries,
    statistics,
    eligibleLotteries,
    selectedLottery,
    loading,
    pagination,
    
    // Fetch functions
    fetchLotteries,
    fetchStatistics,
    fetchEligibleLotteries,
    fetchLotteryDetails,
    
    // Draw functions
    performDraw,
    performBatchDraw,
    verifyLotteryDraw,
    
    // Management functions
    cancelLottery,
    updateLottery,
    exportLotteryData,
    
    // Computed
    totalRevenue,
    averageParticipation,
    
    // Utilities
    getLotteryStatus,
    canDraw,
    getProgressPercentage
  }
}