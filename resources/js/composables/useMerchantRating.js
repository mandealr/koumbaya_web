import { ref } from 'vue'
import { useApi } from './api'

export function useMerchantRating() {
  const { get, post } = useApi()

  const rating = ref(null)
  const reviews = ref([])
  const pagination = ref(null)
  const loading = ref(false)
  const error = ref(null)

  /**
   * Récupère la notation complète d'un marchand
   */
  const fetchMerchantRating = async (merchantId) => {
    loading.value = true
    error.value = null

    try {
      const response = await get(`/merchants/${merchantId}/rating`)
      if (response.success) {
        rating.value = response.data?.rating || response.data
      } else {
        error.value = response.error || 'Erreur lors du chargement'
      }
    } catch (err) {
      console.error('Erreur fetchMerchantRating:', err)
      error.value = err.message || 'Erreur lors du chargement'
    } finally {
      loading.value = false
    }

    return rating.value
  }

  /**
   * Récupère le résumé de la notation d'un marchand
   */
  const fetchRatingSummary = async (merchantId) => {
    loading.value = true
    error.value = null

    try {
      const response = await get(`/merchants/${merchantId}/rating/summary`)
      if (response.success) {
        rating.value = response.data
      } else {
        error.value = response.error || 'Erreur lors du chargement'
      }
    } catch (err) {
      console.error('Erreur fetchRatingSummary:', err)
      error.value = err.message || 'Erreur lors du chargement'
    } finally {
      loading.value = false
    }

    return rating.value
  }

  /**
   * Récupère les avis d'un marchand
   */
  const fetchMerchantReviews = async (merchantId, page = 1, perPage = 10) => {
    loading.value = true
    error.value = null

    try {
      const response = await get(`/merchants/${merchantId}/reviews?page=${page}&per_page=${perPage}`)
      if (response.success) {
        reviews.value = response.data || []
        pagination.value = response.pagination || null
      } else {
        error.value = response.error || 'Erreur lors du chargement'
      }
    } catch (err) {
      console.error('Erreur fetchMerchantReviews:', err)
      error.value = err.message || 'Erreur lors du chargement'
    } finally {
      loading.value = false
    }

    return { reviews: reviews.value, pagination: pagination.value }
  }

  /**
   * Soumet un avis pour un marchand
   */
  const submitReview = async (merchantId, reviewData) => {
    loading.value = true
    error.value = null

    try {
      const response = await post(`/merchants/${merchantId}/reviews`, reviewData)
      if (response.success) {
        return { success: true, data: response.data }
      } else {
        error.value = response.error || 'Erreur lors de l\'envoi'
        return { success: false, error: error.value }
      }
    } catch (err) {
      console.error('Erreur submitReview:', err)
      error.value = err.response?.data?.error || err.message || 'Erreur lors de l\'envoi'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  /**
   * Récupère la notation du marchand connecté
   */
  const fetchMyRating = async () => {
    loading.value = true
    error.value = null

    try {
      const response = await get('/merchant/my-rating')
      if (response.success) {
        rating.value = response.data?.rating || response.data
        return response.data
      } else {
        error.value = response.error || 'Erreur lors du chargement'
      }
    } catch (err) {
      console.error('Erreur fetchMyRating:', err)
      error.value = err.message || 'Erreur lors du chargement'
    } finally {
      loading.value = false
    }

    return null
  }

  /**
   * Récupère les meilleurs marchands
   */
  const fetchTopMerchants = async (limit = 10) => {
    loading.value = true
    error.value = null

    try {
      const response = await get(`/merchants/top-rated?limit=${limit}`)
      if (response.success) {
        return response.data || []
      } else {
        error.value = response.error || 'Erreur lors du chargement'
        return []
      }
    } catch (err) {
      console.error('Erreur fetchTopMerchants:', err)
      error.value = err.message || 'Erreur lors du chargement'
      return []
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    rating,
    reviews,
    pagination,
    loading,
    error,

    // Methods
    fetchMerchantRating,
    fetchRatingSummary,
    fetchMerchantReviews,
    submitReview,
    fetchMyRating,
    fetchTopMerchants
  }
}
