<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Produits disponibles</h1>
      <p class="mt-2 text-gray-600">Découvrez tous les produits que vous pouvez gagner</p>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6 product-filter">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <input 
            v-model="filters.search"
            @input="debouncedSearch"
            type="text" 
            placeholder="Nom du produit..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder-gray-500 bg-white"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Type de vente</label>
          <select 
            v-model="filters.saleMode"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
          >
            <option value="">Tous les types</option>
            <option value="direct">Achat direct</option>
            <option value="lottery">Tombola</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
          <select 
            v-model="filters.category"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
          >
            <option value="">Toutes les catégories</option>
            <option v-if="categories.length === 0" disabled>Aucune catégorie disponible</option>
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
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 bg-white"
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
      <ProductCardV2
        v-for="product in paginatedProducts"
        :key="product.id"
        :product="product"
        @view-product="viewProduct"
      />
    </div>

    <!-- No Results -->
    <div v-if="!loading && products.length === 0" class="text-center py-12">
      <ShoppingBagIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun produit trouvé</h3>
      <p class="text-gray-600">Essayez de modifier vos filtres de recherche</p>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="mt-8 flex justify-center">
      <nav class="flex space-x-2">
        <button
          @click="currentPage = Math.max(1, currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
        >
          Précédent
        </button>
        <span class="px-4 py-2 text-gray-700">
          Page {{ currentPage }} sur {{ totalPages }}
        </span>
        <button
          @click="currentPage = Math.min(totalPages, currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
        >
          Suivant
        </button>
      </nav>
    </div>

    <!-- Load More -->
    <div v-if="false && !loading && paginatedProducts.length > 0 && hasMore" class="text-center mt-8">
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
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { ShoppingBagIcon, ArrowDownIcon } from '@heroicons/vue/24/outline'
import ProductCardV2 from '@/components/common/ProductCardV2.vue'
import { debounce } from 'lodash-es'

const { get, loading, error } = useApi()
const hasMore = ref(true)
const products = ref([])
const categories = ref([])

const router = useRouter()
const currentPage = ref(1)
const itemsPerPage = 9

const filters = ref({
  search: '',
  category: '',
  saleMode: '',
  maxPrice: ''
})

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return products.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(products.value.length / itemsPerPage)
})

// API Functions
const loadProducts = async () => {
  try {
    // Construire les paramètres de requête
    const params = new URLSearchParams()
    
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.category) params.append('category_id', filters.value.category)
    if (filters.value.saleMode) params.append('sale_mode', filters.value.saleMode)
    if (filters.value.maxPrice) params.append('max_price', filters.value.maxPrice)
    
    const url = params.toString() ? `/products?${params.toString()}` : '/products'
    const response = await get(url)
    
    if (response && response.success && response.data) {
      products.value = response.data.products || response.data || []
      currentPage.value = 1 // Reset pagination
    }
  } catch (err) {
    console.error('Erreur lors du chargement des produits:', err)
    products.value = []
  }
}

const loadCategories = async () => {
  try {
    console.log('Customer - Chargement des catégories...')
    const response = await get('/categories')
    console.log('Customer - Réponse API categories:', response)
    
    if (response && response.success) {
      categories.value = response.data || []
      console.log('Customer - Catégories chargées:', categories.value.length)
    } else {
      console.log('Customer - Structure de réponse inattendue:', response)
      // Fallback au cas où la structure serait différente
      categories.value = response?.data || response || []
    }
  } catch (err) {
    console.error('Customer - Erreur lors du chargement des catégories:', err)
    categories.value = []
  }
}

const resetFilters = () => {
  filters.value = {
    search: '',
    category: '',
    saleMode: '',
    maxPrice: ''
  }
  loadProducts()
}

// Debounced search
const debouncedSearch = debounce(() => {
  loadProducts()
}, 300)

// Apply filters
const applyFilters = () => {
  loadProducts()
}

// Navigation
const viewProduct = (product) => {
  router.push({ name: 'customer.product.detail', params: { id: product.id } })
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