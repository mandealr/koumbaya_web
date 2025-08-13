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
            <option value="electronics">Électronique</option>
            <option value="fashion">Mode</option>
            <option value="automotive">Automobile</option>
            <option value="home">Maison</option>
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
            :src="product.image"
            :alt="product.title"
            class="w-full h-48 object-cover"
          />
          <div class="absolute top-4 left-4">
            <span :class="[
              'px-2 py-1 text-xs font-medium rounded-full',
              product.status === 'active' ? 'bg-blue-100 text-blue-800' :
              product.status === 'ending_soon' ? 'bg-yellow-100 text-yellow-800' :
              'bg-gray-100 text-gray-800'
            ]">
              {{ getStatusLabel(product.status) }}
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
              <span class="text-2xl font-bold text-blue-600">{{ product.price }} FCFA</span>
              <p class="text-sm text-gray-500">Valeur du produit</p>
            </div>
            <div class="text-right">
              <span class="text-lg font-semibold text-gray-900">{{ product.ticket_price }} FCFA</span>
              <p class="text-sm text-gray-500">Par ticket</p>
            </div>
          </div>

          <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
              <span>Progression</span>
              <span>{{ product.progress }}% ({{ product.sold_tickets }}/{{ product.total_tickets }})</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                :style="{ width: product.progress + '%' }"
              ></div>
            </div>
          </div>

          <div class="mb-4 text-sm text-gray-600">
            <div class="flex items-center mb-1">
              <CalendarIcon class="w-4 h-4 mr-2" />
              <span>Tirage le {{ formatDate(product.draw_date) }}</span>
            </div>
            <div class="flex items-center">
              <ClockIcon class="w-4 h-4 mr-2" />
              <span>{{ getRemainingTime(product.draw_date) }}</span>
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
import { ref, computed, onMounted } from 'vue'
import {
  ShoppingBagIcon,
  StarIcon,
  CalendarIcon,
  ClockIcon,
  ArrowDownIcon
} from '@heroicons/vue/24/outline'

const loading = ref(false)
const hasMore = ref(true)

const filters = ref({
  search: '',
  category: '',
  maxPrice: ''
})

const products = ref([
  {
    id: 1,
    title: 'iPhone 15 Pro',
    description: 'Le dernier flagship d\'Apple avec une caméra révolutionnaire',
    price: '750,000',
    ticket_price: '1,000',
    category: 'electronics',
    progress: 85,
    sold_tickets: 637,
    total_tickets: 750,
    status: 'active',
    featured: true,
    draw_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000),
    image: '/images/products/iphone15.jpg'
  },
  {
    id: 2,
    title: 'MacBook Pro M3',
    description: 'Puissance et performance pour les créateurs',
    price: '1,200,000',
    ticket_price: '2,000',
    category: 'electronics',
    progress: 62,
    sold_tickets: 372,
    total_tickets: 600,
    status: 'active',
    featured: false,
    draw_date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000),
    image: '/images/products/macbook.jpg'
  },
  {
    id: 3,
    title: 'PlayStation 5',
    description: 'Console de jeu nouvelle génération',
    price: '350,000',
    ticket_price: '500',
    category: 'electronics',
    progress: 95,
    sold_tickets: 665,
    total_tickets: 700,
    status: 'ending_soon',
    featured: true,
    draw_date: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000),
    image: '/images/products/ps5.jpg'
  },
  {
    id: 4,
    title: 'AirPods Pro',
    description: 'Audio haute qualité avec réduction de bruit',
    price: '150,000',
    ticket_price: '300',
    category: 'electronics',
    progress: 45,
    sold_tickets: 225,
    total_tickets: 500,
    status: 'active',
    featured: false,
    draw_date: new Date(Date.now() + 21 * 24 * 60 * 60 * 1000),
    image: '/images/products/airpods.jpg'
  },
  {
    id: 5,
    title: 'Tesla Model 3',
    description: 'Voiture électrique révolutionnaire',
    price: '15,000,000',
    ticket_price: '5,000',
    category: 'automotive',
    progress: 15,
    sold_tickets: 450,
    total_tickets: 3000,
    status: 'active',
    featured: true,
    draw_date: new Date(Date.now() + 45 * 24 * 60 * 60 * 1000),
    image: '/images/products/tesla.jpg'
  },
  {
    id: 6,
    title: 'Nike Air Jordan',
    description: 'Sneakers exclusives en édition limitée',
    price: '85,000',
    ticket_price: '200',
    category: 'fashion',
    progress: 78,
    sold_tickets: 332,
    total_tickets: 425,
    status: 'active',
    featured: false,
    draw_date: new Date(Date.now() + 10 * 24 * 60 * 60 * 1000),
    image: '/images/products/jordan.jpg'
  }
])

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
    filtered = filtered.filter(product => product.category === filters.value.category)
  }

  if (filters.value.maxPrice) {
    const maxPrice = parseInt(filters.value.maxPrice)
    filtered = filtered.filter(product => parseInt(product.price.replace(',', '')) <= maxPrice)
  }

  return filtered
})

const resetFilters = () => {
  filters.value = {
    search: '',
    category: '',
    maxPrice: ''
  }
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(date)
}

const getRemainingTime = (date) => {
  const now = new Date()
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

const getStatusLabel = (status) => {
  const labels = {
    'active': 'Actif',
    'ending_soon': 'Bientôt terminé',
    'completed': 'Terminé'
  }
  return labels[status] || status
}

const loadMore = () => {
  // Simulate loading more products
  console.log('Loading more products...')
  hasMore.value = false
}

onMounted(() => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
  }, 1000)
})
</script>