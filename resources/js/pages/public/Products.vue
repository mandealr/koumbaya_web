<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-[#0099cc] via-[#0088bb] to-[#0077aa] text-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl font-bold mb-4">
            Découvrez nos <span class="text-white/80">produits incroyables</span>
          </h1>
          <p class="text-xl text-white/80 max-w-3xl mx-auto mb-8">
            Des smartphones dernière génération aux gadgets high-tech, tous nos produits sont garantis et à gagner facilement
          </p>
          <div class="flex flex-wrap justify-center gap-4">
            <div class="bg-white/10 backdrop-blur-sm rounded-full px-6 py-2">
              <span class="text-sm font-medium">✓ Produits authentiques</span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-full px-6 py-2">
              <span class="text-sm font-medium">✓ Livraison gratuite</span>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-full px-6 py-2">
              <span class="text-sm font-medium">✓ Garantie constructeur</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Filters & Search -->
    <section class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 product-filter">
        <div class="flex flex-col lg:flex-row gap-6">
          <!-- Search -->
          <div class="flex-1">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                v-model="searchQuery"
                @input="debouncedSearch"
                type="text"
                placeholder="Rechercher un produit..."
                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900 placeholder-gray-500 bg-white"
              />
            </div>
          </div>

          <!-- Sale Mode Filter -->
          <div class="lg:w-48">
            <select
              v-model="selectedSaleMode"
              @change="applyFilters"
              class="w-full py-3 px-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900 bg-white"
            >
              <option value="">Tous les types</option>
              <option value="direct">Achat direct</option>
              <option value="lottery">Tombola</option>
            </select>
          </div>

          <!-- Category Filter -->
          <div class="lg:w-64">
            <select
              v-model="selectedCategory"
              @change="applyFilters"
              class="w-full py-3 px-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900 bg-white"
            >
              <option value="">Toutes catégories</option>
              <option v-if="categories.length === 0" disabled>Aucune catégorie disponible</option>
              <option 
                v-for="category in categories" 
                :key="category.id" 
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <!-- Debug info -->
            <div v-if="categories.length === 0" class="mt-2 text-sm text-red-600">
              Aucune catégorie trouvée ({{ categories.length }})
            </div>
          </div>

          <!-- Price Range -->
          <div class="lg:w-48">
            <select
              v-model="selectedPriceRange"
              @change="applyFilters"
              class="w-full py-3 px-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900 bg-white"
            >
              <option value="">Tous les prix</option>
              <option value="0-50000">0 - 50,000 FCFA</option>
              <option value="50000-100000">50,000 - 100,000 FCFA</option>
              <option value="100000-500000">100,000 - 500,000 FCFA</option>
              <option value="500000+">Plus de 500,000 FCFA</option>
            </select>
          </div>

          <!-- Sort -->
          <div class="lg:w-48">
            <select
              v-model="sortBy"
              @change="applyFilters"
              class="w-full py-3 px-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900 bg-white"
            >
              <option value="newest">Plus récents</option>
              <option value="price-asc">Prix croissant</option>
              <option value="price-desc">Prix décroissant</option>
              <option value="popular">Plus populaires</option>
            </select>
          </div>
        </div>
      </div>
    </section>

    <!-- Products Grid -->
    <section class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="n in 12" :key="n" class="animate-pulse">
            <div class="bg-white rounded-2xl p-6 shadow-sm">
              <div class="w-full h-48 bg-gray-200 rounded-xl mb-4"></div>
              <div class="h-4 bg-gray-200 rounded mb-2"></div>
              <div class="h-4 bg-gray-200 rounded w-2/3 mb-4"></div>
              <div class="h-6 bg-gray-200 rounded w-1/3"></div>
            </div>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <ProductCardV2
            v-for="product in paginatedProducts"
            :key="product.id"
            :product="product"
            @view-product="viewProduct"
          />
        </div>

        <!-- No Results -->
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

        <div v-if="!loading && sortedProducts.length === 0" class="text-center py-16">
          <div class="w-32 h-32 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
            <MagnifyingGlassIcon class="w-16 h-16 text-gray-400" />
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun produit trouvé</h3>
          <p class="text-gray-600 mb-6">Essayez de modifier vos critères de recherche</p>
          <button
            @click="clearFilters"
            class="bg-[#0099cc] hover:bg-[#0088bb] text-white px-6 py-3 rounded-xl transition-colors"
          >
            Réinitialiser les filtres
          </button>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-gradient-to-r from-[#0099cc] to-cyan-600 py-16">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
          Prêt à tenter votre chance ?
        </h2>
        <p class="text-xl text-blue-100 mb-8">
          Inscrivez-vous gratuitement et participez à vos premiers tirages
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/register"
            class="bg-white text-[#0099cc] px-8 py-4 rounded-xl font-semibold hover:bg-gray-50 transition-colors"
          >
            S'inscrire gratuitement
          </router-link>
          <router-link
            to="/login"
            class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-[#0099cc] transition-all"
          >
            Se connecter
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import ProductCardV2 from '@/components/common/ProductCardV2.vue'
import { debounce } from 'lodash-es'

const router = useRouter()
const { get, loading, error } = useApi()

// Reactive data
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedSaleMode = ref('')
const selectedPriceRange = ref('')
const sortBy = ref('newest')
const products = ref([])
const categories = ref([])
const currentPage = ref(1)
const itemsPerPage = 12

// API Functions
const loadProducts = async () => {
  try {
    // Construire les paramètres de requête
    const params = new URLSearchParams()
    
    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedCategory.value) params.append('category_id', selectedCategory.value)
    if (selectedSaleMode.value) params.append('sale_mode', selectedSaleMode.value)
    
    // Gérer la plage de prix
    if (selectedPriceRange.value) {
      const [min, max] = selectedPriceRange.value.split('-')
      if (min) params.append('min_price', min)
      if (max && max !== '+') params.append('max_price', max)
    }
    
    const url = params.toString() ? `/products?${params.toString()}` : '/products'
    const response = await get(url)
    
    if (response && response.success && response.data) {
      products.value = response.data.products || response.data || []
      currentPage.value = 1 // Reset pagination when filters change
    }
  } catch (err) {
    console.error('Erreur lors du chargement des produits:', err)
    products.value = []
  }
}

const loadCategories = async () => {
  try {
    console.log('Chargement des catégories...')
    const response = await get('/categories')
    console.log('Réponse API categories:', response)
    
    if (response && response.success) {
      categories.value = response.data || []
      console.log('Catégories chargées:', categories.value.length)
    } else {
      console.log('Structure de réponse inattendue:', response)
      // Fallback au cas où la structure serait différente
      categories.value = response?.data || response || []
    }
  } catch (err) {
    console.error('Erreur lors du chargement des catégories:', err)
    categories.value = []
  }
}

// Computed properties
const sortedProducts = computed(() => {
  let sorted = [...products.value]

  // Sort products
  if (sortBy.value === 'price-asc') {
    sorted.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'price-desc') {
    sorted.sort((a, b) => b.price - a.price)
  } else if (sortBy.value === 'popular') {
    sorted.sort((a, b) => {
      const aPopularity = a.sale_mode === 'lottery' 
        ? (a.active_lottery?.sold_tickets || 0) 
        : (a.views_count || 0)
      const bPopularity = b.sale_mode === 'lottery' 
        ? (b.active_lottery?.sold_tickets || 0) 
        : (b.views_count || 0)
      return bPopularity - aPopularity
    })
  } else if (sortBy.value === 'newest') {
    sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  }

  return sorted
})

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return sortedProducts.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(sortedProducts.value.length / itemsPerPage)
})

// Methods
const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR').format(price)
}

const calculateProgress = (product) => {
  if (!product.active_lottery) return 0
  const sold = product.active_lottery.sold_tickets || 0
  const total = product.active_lottery.total_tickets || 1
  return Math.round((sold / total) * 100)
}

const viewProduct = (product) => {
  // Bloquer la navigation si c'est un produit fallback
  if (typeof product.id === 'string' && product.id.startsWith('fallback')) {
    console.warn('Navigation bloquée vers produit fallback:', product.id)
    return
  }

  // Utiliser le slug si disponible, sinon utiliser l'ID
  const identifier = product.slug || product.id
  router.push({ name: 'public.product.detail', params: { id: identifier } })
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  selectedSaleMode.value = ''
  selectedPriceRange.value = ''
  sortBy.value = 'newest'
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

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProducts(),
    loadCategories()
  ])
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>