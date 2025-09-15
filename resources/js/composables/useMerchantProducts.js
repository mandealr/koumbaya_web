import { ref, computed } from 'vue'
import { useApi } from './api'

export function useMerchantProducts() {
  const { get, loading, error } = useApi()
  
  // Reactive data
  const productStats = ref([
    { label: 'Produits actifs', value: '0', change: 0, color: 'bg-blue-500', icon: 'GiftIcon' },
    { label: 'Total revenus', value: '0 FCFA', change: 0, color: 'bg-green-500', icon: 'CurrencyDollarIcon' },
    { label: 'Vues totales', value: '0', change: 0, color: 'bg-purple-500', icon: 'EyeIcon' },
    { label: 'Taux conversion', value: '0%', change: 0, color: 'bg-yellow-500', icon: 'ChartBarIcon' }
  ])
  
  const topProducts = ref([])
  const categories = ref([])
  
  // Load product statistics
  const loadProductStats = async () => {
    try {
      const response = await get('/stats/merchant/products')
      
      if (response && response.success) {
        const stats = response.data.stats
        productStats.value = [
          { 
            label: 'Produits actifs', 
            value: stats.active_products.toString(), 
            change: stats.active_products_change || 0, 
            color: 'bg-blue-500', 
            icon: 'GiftIcon' 
          },
          { 
            label: 'Total revenus', 
            value: formatCurrency(stats.total_revenue), 
            change: stats.revenue_change || 0, 
            color: 'bg-green-500', 
            icon: 'CurrencyDollarIcon' 
          },
          { 
            label: 'Vues totales', 
            value: stats.total_views.toString(), 
            change: stats.views_change || 0, 
            color: 'bg-purple-500', 
            icon: 'EyeIcon' 
          },
          { 
            label: 'Taux conversion', 
            value: (stats.conversion_rate || 0).toFixed(1) + '%', 
            change: stats.conversion_change || 0, 
            color: 'bg-yellow-500', 
            icon: 'ChartBarIcon' 
          }
        ]
        
        topProducts.value = response.data.top_products || []
      }
    } catch (err) {
      console.error('Erreur lors du chargement des statistiques de produits:', err)
    }
  }
  
  // Load categories
  const loadCategories = async () => {
    try {
      const response = await get('/categories')
      if (response && response.categories) {
        categories.value = response.categories
      }
    } catch (err) {
      console.error('Erreur lors du chargement des catégories:', err)
      categories.value = []
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
    return num.toLocaleString('fr-FR') + ' FCFA'
  }
  
  const formatNumber = (number) => {
    return new Intl.NumberFormat('fr-FR').format(number || 0)
  }
  
  return {
    // Data
    productStats,
    topProducts,
    categories,
    
    // State
    loading,
    error,
    
    // Methods
    loadProductStats,
    loadCategories,
    
    // Utilities
    formatCurrency,
    formatNumber
  }
}