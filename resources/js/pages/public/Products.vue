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
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row gap-6">
          <!-- Search -->
          <div class="flex-1">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Rechercher un produit..."
                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all"
              />
            </div>
          </div>

          <!-- Category Filter -->
          <div class="lg:w-64">
            <select
              v-model="selectedCategory"
              class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all"
            >
              <option value="">Toutes catégories</option>
              <option value="electronics">Électronique</option>
              <option value="fashion">Mode</option>
              <option value="automotive">Automobile</option>
              <option value="home">Maison</option>
            </select>
          </div>

          <!-- Sort -->
          <div class="lg:w-48">
            <select
              v-model="sortBy"
              class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all"
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
          <div
            v-for="product in filteredProducts"
            :key="product.id"
            @click="viewProduct(product)"
            class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all duration-300 cursor-pointer group"
          >
            <!-- Product Image -->
            <div class="relative overflow-hidden rounded-xl mb-4">
              <img
                :src="product.image || placeholderImg"
                :alt="product.name"
                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
              />
              <div class="absolute top-3 right-3">
                <span class="bg-[#0099cc] text-white px-3 py-1 rounded-full text-xs font-medium">
                  {{ product.ticketPrice }} FCFA
                </span>
              </div>
              <div v-if="product.isNew" class="absolute top-3 left-3">
                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                  Nouveau
                </span>
              </div>
            </div>

            <!-- Product Info -->
            <div class="space-y-2">
              <h3 class="font-bold text-lg text-black group-hover:text-[#0099cc] transition-colors">
                {{ product.name }}
              </h3>
              <p class="text-gray-600 text-sm line-clamp-2">
                {{ product.description }}
              </p>
              
              <!-- Product Value -->
              <div class="flex items-center justify-between">
                <div class="text-2xl font-bold text-[#0099cc]">
                  {{ formatPrice(product.value) }}
                </div>
                <div class="text-sm text-gray-500">
                  Valeur du lot
                </div>
              </div>

              <!-- Progress -->
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Progression</span>
                  <span class="font-medium">{{ Math.round((product.soldTickets / 1000) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-gradient-to-r from-[#0099cc] to-cyan-500 h-2 rounded-full transition-all duration-500"
                    :style="{ width: Math.round((product.soldTickets / 1000) * 100) + '%' }"
                  ></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500">
                  <span>{{ product.soldTickets }} vendus</span>
                  <span>1000 tickets total</span>
                </div>
              </div>

              <!-- CTA Button -->
              <button class="w-full mt-4 bg-[#0099cc] hover:bg-[#0088bb] text-white font-medium py-3 rounded-xl transition-all duration-200 group-hover:scale-[1.02]">
                Participer maintenant
              </button>
            </div>
          </div>
        </div>

        <!-- No Results -->
        <div v-if="!loading && filteredProducts.length === 0" class="text-center py-16">
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import placeholderImg from '@/assets/placeholder.jpg'

const router = useRouter()

// Reactive data
const loading = ref(true)
const searchQuery = ref('')
const selectedCategory = ref('')
const sortBy = ref('newest')

// Mock products data
const products = ref([
  {
    id: 1,
    name: 'iPhone 15 Pro Max',
    description: 'Le dernier smartphone Apple avec puce A17 Pro et appareil photo révolutionnaire',
    value: 1299000,
    ticketPrice: 1500,
    image: placeholderImg,
    category: 'electronics',
    soldTickets: 750,
    isNew: true
  },
  {
    id: 2,
    name: 'MacBook Pro M3',
    description: 'Ordinateur portable professionnel avec puce M3 et écran Liquid Retina XDR',
    value: 2500000,
    ticketPrice: 2500,
    image: placeholderImg,
    category: 'electronics',
    soldTickets: 420,
    isNew: true
  },
  {
    id: 3,
    name: 'PlayStation 5',
    description: 'Console de jeu nouvelle génération avec SSD ultra-rapide',
    value: 650000,
    ticketPrice: 1000,
    image: placeholderImg,
    category: 'electronics',
    soldTickets: 890,
    isNew: false
  },
  {
    id: 4,
    name: 'AirPods Pro 2',
    description: 'Écouteurs sans fil avec réduction de bruit active et audio spatial',
    value: 350000,
    ticketPrice: 500,
    image: placeholderImg,
    category: 'electronics',
    soldTickets: 600,
    isNew: false
  },
  {
    id: 5,
    name: 'Tesla Model Y',
    description: 'SUV électrique premium avec pilotage automatique et autonomie 500km',
    value: 45000000,
    ticketPrice: 50000,
    image: placeholderImg,
    category: 'automotive',
    soldTickets: 320,
    isNew: true
  },
  {
    id: 6,
    name: 'Rolex Submariner',
    description: 'Montre de luxe suisse étanche avec mouvement automatique',
    value: 8500000,
    ticketPrice: 10000,
    image: placeholderImg,
    category: 'fashion',
    soldTickets: 150,
    isNew: false
  }
])

// Computed properties
const filteredProducts = computed(() => {
  let filtered = products.value

  // Filter by category
  if (selectedCategory.value) {
    filtered = filtered.filter(p => p.category === selectedCategory.value)
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(p => 
      p.name.toLowerCase().includes(query) || 
      p.description.toLowerCase().includes(query)
    )
  }

  // Sort products
  if (sortBy.value === 'price-asc') {
    filtered.sort((a, b) => a.value - b.value)
  } else if (sortBy.value === 'price-desc') {
    filtered.sort((a, b) => b.value - a.value)
  } else if (sortBy.value === 'popular') {
    filtered.sort((a, b) => b.soldTickets - a.soldTickets)
  } else if (sortBy.value === 'newest') {
    filtered.sort((a, b) => b.isNew - a.isNew)
  }

  return filtered
})

// Methods
const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price).replace('XAF', 'FCFA')
}

const viewProduct = (product) => {
  router.push({ name: 'public.product.detail', params: { id: product.id } })
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  sortBy.value = 'newest'
}

// Lifecycle
onMounted(() => {
  // Simulate loading
  setTimeout(() => {
    loading.value = false
  }, 1000)
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