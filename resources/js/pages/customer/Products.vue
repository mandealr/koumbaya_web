<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Produits disponibles</h1>
      <p class="mt-2 text-gray-600">Découvrez tous les produits que vous pouvez gagner</p>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="Nom du produit..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
          <select 
            v-model="filters.category"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Toutes les catégories</option>
            <option 
              v-for="category in categories" 
              :key="category.id" 
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Prix max</label>
          <select 
            v-model="filters.maxPrice"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les prix</option>
            <option value="100000">100,000 FCFA</option>
            <option value="500000">500,000 FCFA</option>
            <option value="1000000">1,000,000 FCFA</option>
            <option value="2000000">2,000,000+ FCFA</option>
          </select>
        </div>
        <div class="flex items-end">
          <button 
            @click="resetFilters"
            class="w-full px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
          >
            Réinitialiser
          </button>
        </div>
      </div>
    </div>

    <!-- Products Grid -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Chargement des produits...</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="product in filteredProducts"
        :key="product.id"
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300"
      >
        <div class="relative">
          <img
            :src="product.image || '/images/products/placeholder.jpg'"
            :alt="product.title"
            class="w-full h-48 object-cover"
          />
          <div class="absolute top-4 left-4">
            <span :class="[
              'px-2 py-1 text-xs font-medium rounded-full',
              'bg-blue-100 text-blue-800'
            ]">
              Actif
            </span>
          </div>
          <div v-if="product.featured" class="absolute top-4 right-4">
            <StarIcon class="w-6 h-6 text-yellow-500 fill-current" />
          </div>
        </div>

        <div class="p-6">
          <div class="mb-4">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ product.title }}</h3>
            <p class="text-gray-600 text-sm">{{ product.description }}</p>
          </div>

          <div class="flex justify-between items-center mb-4">
            <div>
              <span class="text-2xl font-bold text-blue-600">{{ formatPrice(product.price) }} FCFA</span>
              <p class="text-sm text-gray-500">Valeur du produit</p>
            </div>
            <div class="text-right">
              <span class="text-lg font-semibold text-gray-900">{{ formatPrice(product.lottery?.ticket_price || 1000) }} FCFA</span>
              <p class="text-sm text-gray-500">Par ticket</p>
            </div>
          </div>

          <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
              <span>Progression</span>
              <span>{{ calculateProgress(product) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                :style="{ width: calculateProgress(product) + '%' }"
              ></div>
            </div>
          </div>

          <div class="mb-4 text-sm text-gray-600" v-if="product.lottery">
            <div class="flex items-center mb-1">
              <CalendarIcon class="w-4 h-4 mr-2" />
              <span>Tirage le {{ formatDate(product.lottery.draw_date) }}</span>
            </div>
            <div class="flex items-center">
              <ClockIcon class="w-4 h-4 mr-2" />
              <span>{{ getRemainingTime(product.lottery.draw_date) }}</span>
            </div>
          </div>

          <router-link
            :to="`/customer/products/${product.id}`"
            class="block w-full text-center bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors"
          >
            Voir le produit
          </router-link>
        </div>
      </div>
    </div>

    <!-- No Results -->
    <div v-if="!loading && filteredProducts.length === 0" class="text-center py-12">
      <ShoppingBagIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun produit trouvé</h3>
      <p class="text-gray-600">Essayez de modifier vos filtres de recherche</p>
    </div>

    <!-- Load More -->
    <div v-if="!loading && filteredProducts.length > 0 && hasMore" class="text-center mt-8">
      <button
        @click="loadMore"
        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100"
      >
        Charger plus de produits
        <ArrowDownIcon class="ml-2 w-5 h-5" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useApi } from '@/composables/api'
import {
  ShoppingBagIcon,
  StarIcon,
  CalendarIcon,
  ClockIcon,
  ArrowDownIcon
} from '@heroicons/vue/24/outline'

const { get, loading, error } = useApi()
const hasMore = ref(true)
const products = ref([])
const categories = ref([])

const filters = ref({
  search: '',
  category: '',
  maxPrice: ''
})

const filteredProducts = computed(() => {
  let filtered = products.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(product => 
      product.title.toLowerCase().includes(search) ||
      product.description.toLowerCase().includes(search)
    )
  }

  if (filters.value.category) {
    filtered = filtered.filter(product => product.category_id == filters.value.category)
  }

  if (filters.value.maxPrice) {
    const maxPrice = parseInt(filters.value.maxPrice)
    filtered = filtered.filter(product => product.price <= maxPrice)
  }

  return filtered
})

// API Functions
const loadProducts = async () => {
  try {
    const response = await get('/products')
    console.log('Products API response:', response)
    if (response && response.success && response.data && response.data.products) {
      products.value = response.data.products || []
    } else {
      products.value = response.data || []
    }
  } catch (err) {
    console.error('Erreur lors du chargement des produits:', err)
    products.value = []
  }
}

const loadCategories = async () => {
  try {
    const response = await get('/categories')
    categories.value = response.data || []
  } catch (err) {
    console.error('Erreur lors du chargement des catégories:', err)
  }
}

const resetFilters = () => {
  filters.value = {
    search: '',
    category: '',
    maxPrice: ''
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(date)
}

const getRemainingTime = (dateString) => {
  if (!dateString) return ''
  const now = new Date()
  const date = new Date(dateString)
  const diff = date - now
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (days > 7) {
    return `Plus de ${days} jours restants`
  } else if (days > 0) {
    return `${days} jour${days > 1 ? 's' : ''} restant${days > 1 ? 's' : ''}`
  } else {
    const hours = Math.floor(diff / (1000 * 60 * 60))
    return hours > 0 ? `${hours}h restantes` : 'Se termine bientôt'
  }
}

const formatPrice = (price) => {
  if (price === null || price === undefined || isNaN(price)) {
    return '0'
  }
  return new Intl.NumberFormat('fr-FR').format(Number(price))
}

const calculateProgress = (product) => {
  if (!product.lottery) return 0
  const sold = product.lottery.sold_tickets || 0
  const total = product.lottery.total_tickets || 1
  return Math.round((sold / total) * 100)
}

const loadMore = async () => {
  // Implémenter la pagination si nécessaire
  console.log('Loading more products...')
  hasMore.value = false
}

onMounted(async () => {
  await Promise.all([
    loadProducts(),
    loadCategories()
  ])
})
</script>