<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Mes Produits</h1>
        <p class="mt-2 text-gray-600">Gérez vos produits et suivez vos tombolas en cours</p>
      </div>
      <div class="flex space-x-3">
        <router-link
          to="/merchant/products/create"
          class="inline-flex items-center px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
        >
          <PlusIcon class="w-5 h-5 mr-2" />
          Nouveau Produit
        </router-link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div
        v-for="stat in productStats"
        :key="stat.label"
        class="bg-white p-6 rounded-xl shadow-lg border border-gray-100"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ stat.value }}</p>
          </div>
          <div :class="[
            'p-3 rounded-xl',
            stat.color
          ]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center text-xs">
            <span :class="[
              'inline-flex items-center px-2 py-1 rounded-full font-medium',
              stat.change >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            ]">
              <component
                :is="stat.change >= 0 ? ArrowUpIcon : ArrowDownIcon"
                class="w-3 h-3 mr-1"
              />
              {{ Math.abs(stat.change) }}%
            </span>
            <span class="ml-2 text-gray-500">ce mois</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nom du produit..."
              class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
              @input="applyFilters"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
          <select
            v-model="filters.category"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          >
            <option value="">Toutes les catégories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select
            v-model="filters.status"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          >
            <option value="">Tous les statuts</option>
            <option value="draft">Brouillon</option>
            <option value="active">Actif</option>
            <option value="completed">Terminé</option>
            <option value="cancelled">Annulé</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
          <select
            v-model="filters.sortBy"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          >
            <option value="created_at">Date de création</option>
            <option value="sales">Ventes</option>
            <option value="end_date">Date de fin</option>
            <option value="value">Valeur</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Products Grid -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0099cc]"></div>
    </div>

    <div v-else-if="filteredProducts.length === 0" class="text-center py-12">
      <GiftIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun produit</h3>
      <p class="text-gray-600 mb-6">Vous n'avez pas encore de produits. Créez votre première tombola !</p>
      <router-link
        to="/merchant/products/create"
        class="inline-flex items-center px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-xl font-semibold transition-colors"
      >
        <PlusIcon class="w-5 h-5 mr-2" />
        Créer mon premier produit
      </router-link>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="product in filteredProducts"
        :key="product.id"
        class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-200 hover:scale-[1.02]"
      >
        <!-- Product Image -->
        <div class="relative h-48 bg-gray-200">
          <img
            :src="product.image"
            :alt="product.name"
            class="w-full h-full object-cover"
          />
          <div class="absolute top-3 left-3">
            <span :class="getStatusClass(product.status)">
              {{ getStatusLabel(product.status) }}
            </span>
          </div>
          <div class="absolute top-3 right-3">
            <div class="bg-black/50 text-white px-2 py-1 rounded-lg text-xs font-medium">
              {{ product.category }}
            </div>
          </div>
        </div>

        <!-- Product Content -->
        <div class="p-6">
          <div class="flex items-start justify-between mb-3">
            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ product.name }}</h3>
            <div class="text-right ml-2">
              <p class="text-lg font-bold text-[#0099cc]">{{ formatAmount(product.value) }} FCFA</p>
              <p class="text-xs text-gray-500">Valeur</p>
            </div>
          </div>

          <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ product.description }}</p>

          <!-- Lottery Progress -->
          <div class="space-y-3 mb-4">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Progression</span>
              <span class="font-medium">{{ product.sold_tickets }}/{{ product.total_tickets }}</span>
            </div>

            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-gradient-to-r from-[#0099cc] to-cyan-500 h-2 rounded-full transition-all duration-500"
                :style="{ width: product.progress + '%' }"
              ></div>
            </div>

            <div class="flex justify-between text-xs text-gray-500">
              <span>{{ product.progress }}% vendu</span>
              <span v-if="product.end_date">Fin: {{ formatDate(product.end_date) }}</span>
            </div>
          </div>

          <!-- Revenue Info -->
          <div class="grid grid-cols-2 gap-4 mb-4 text-center">
            <div class="bg-green-50 p-3 rounded-lg">
              <p class="text-green-700 font-semibold text-lg">{{ formatAmount(product.revenue) }}</p>
              <p class="text-green-600 text-xs">Revenus</p>
            </div>
            <div class="bg-blue-50 p-3 rounded-lg">
              <p class="text-blue-700 font-semibold text-lg">{{ product.ticket_price.toLocaleString() }}</p>
              <p class="text-blue-600 text-xs">Prix/billet</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex space-x-2">
            <button
              @click="viewProduct(product)"
              class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors"
            >
              <EyeIcon class="w-4 h-4 inline mr-1" />
              Voir
            </button>

            <button
              v-if="product.status === 'draft'"
              @click="publishProduct(product)"
              class="flex-1 px-4 py-2 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-lg text-sm font-medium transition-colors"
            >
              <RocketLaunchIcon class="w-4 h-4 inline mr-1" />
              Publier
            </button>

            <button
              v-if="product.status === 'active'"
              @click="editProduct(product)"
              class="flex-1 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-medium transition-colors"
            >
              <PencilIcon class="w-4 h-4 inline mr-1" />
              Modifier
            </button>

            <div class="relative">
              <button
                @click="toggleProductMenu(product.id)"
                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
              >
                <EllipsisVerticalIcon class="w-4 h-4" />
              </button>

              <div
                v-if="showProductMenu === product.id"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
              >
                <button
                  @click="duplicateProduct(product)"
                  class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg"
                >
                  <DocumentDuplicateIcon class="w-4 h-4 inline mr-2" />
                  Dupliquer
                </button>
                <button
                  @click="viewAnalytics(product)"
                  class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                >
                  <ChartBarIcon class="w-4 h-4 inline mr-2" />
                  Analytiques
                </button>
                <button
                  v-if="['draft', 'active'].includes(product.status)"
                  @click="deleteProduct(product)"
                  class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 rounded-b-lg"
                >
                  <TrashIcon class="w-4 h-4 inline mr-2" />
                  Supprimer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Details Modal -->
    <div v-if="showProductModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">Détails du produit</h3>
          <button @click="closeProductModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <div v-if="selectedProduct" class="space-y-6">
          <!-- Product Images -->
          <div v-if="selectedProduct.images && selectedProduct.images.length > 0">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Images</h4>
            <div class="grid grid-cols-3 gap-2">
              <img
                v-for="(image, index) in selectedProduct.images.slice(0, 6)"
                :key="index"
                :src="image"
                :alt="`Produit ${index + 1}`"
                class="w-full h-20 object-cover rounded-lg"
              />
            </div>
          </div>

          <!-- Product Info -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nom</label>
              <p class="text-sm text-gray-900">{{ selectedProduct.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Catégorie</label>
              <p class="text-sm text-gray-900">{{ selectedProduct.category }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Valeur</label>
              <p class="text-sm text-gray-900 font-semibold">{{ formatAmount(selectedProduct.value) }} FCFA</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Condition</label>
              <p class="text-sm text-gray-900">{{ getConditionLabel(selectedProduct.condition) }}</p>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ selectedProduct.description }}</p>
          </div>

          <!-- Lottery Stats -->
          <div>
            <h4 class="text-md font-semibold text-gray-900 mb-3">Statistiques de la tombola</h4>
            <div class="grid grid-cols-3 gap-4 text-center">
              <div class="bg-[#0099cc]/10 p-4 rounded-lg">
                <p class="text-2xl font-bold text-[#0099cc]">{{ selectedProduct.sold_tickets }}</p>
                <p class="text-sm text-gray-600">Billets vendus</p>
              </div>
              <div class="bg-green-100 p-4 rounded-lg">
                <p class="text-2xl font-bold text-green-600">{{ formatAmount(selectedProduct.revenue) }}</p>
                <p class="text-sm text-gray-600">Revenus (FCFA)</p>
              </div>
              <div class="bg-purple-100 p-4 rounded-lg">
                <p class="text-2xl font-bold text-purple-600">{{ selectedProduct.progress }}%</p>
                <p class="text-sm text-gray-600">Progression</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <button @click="closeProductModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
            Fermer
          </button>
          <router-link
            v-if="selectedProduct"
            :to="`/merchant/products/${selectedProduct.id}/edit`"
            class="px-4 py-2 bg-[#0099cc] text-white rounded-lg hover:bg-[#0088bb]"
          >
            Modifier
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  GiftIcon,
  EyeIcon,
  PencilIcon,
  RocketLaunchIcon,
  EllipsisVerticalIcon,
  DocumentDuplicateIcon,
  ChartBarIcon,
  TrashIcon,
  XMarkIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ShoppingBagIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()

// State
const loading = ref(false)
const showProductModal = ref(false)
const selectedProduct = ref(null)
const showProductMenu = ref(null)

const filters = reactive({
  search: '',
  category: '',
  status: '',
  sortBy: 'created_at'
})

const categories = ref([
  { id: 1, name: 'Électronique' },
  { id: 2, name: 'Mode & Accessoires' },
  { id: 3, name: 'Maison & Jardin' },
  { id: 4, name: 'Sport & Loisirs' },
  { id: 5, name: 'Automobile' }
])

const productStats = ref([
  {
    label: 'Produits actifs',
    value: '8',
    change: 12.5,
    icon: GiftIcon,
    color: 'bg-[#0099cc]'
  },
  {
    label: 'Total revenus',
    value: '45.2K',
    change: 18.7,
    icon: CurrencyDollarIcon,
    color: 'bg-green-500'
  },
  {
    label: 'Billets vendus',
    value: '1,247',
    change: 8.3,
    icon: ShoppingBagIcon,
    color: 'bg-purple-500'
  },
  {
    label: 'Taux succès',
    value: '89%',
    change: 5.2,
    icon: CheckCircleIcon,
    color: 'bg-yellow-500'
  }
])

const products = ref([
  {
    id: 1,
    name: 'iPhone 15 Pro Max 256GB',
    description: 'Smartphone Apple dernière génération, état impeccable avec tous les accessoires d\'origine.',
    category: 'Électronique',
    condition: 'new',
    value: 850000,
    ticket_price: 2500,
    total_tickets: 400,
    sold_tickets: 287,
    progress: 72,
    revenue: 717500,
    status: 'active',
    end_date: '2025-01-15T18:00:00Z',
    created_at: '2025-01-01T10:00:00Z',
    image: '/images/products/placeholder.jpg',
    images: [
      '/images/products/placeholder.jpg',
      '/images/products/placeholder.jpg',
      '/images/products/placeholder.jpg'
    ]
  },
  {
    id: 2,
    name: 'MacBook Pro M3 14"',
    description: 'Ordinateur portable professionnel Apple avec processeur M3, parfait pour le travail et la créativité.',
    category: 'Électronique',
    condition: 'like_new',
    value: 1200000,
    ticket_price: 5000,
    total_tickets: 300,
    sold_tickets: 156,
    progress: 52,
    revenue: 780000,
    status: 'active',
    end_date: '2025-01-20T20:00:00Z',
    created_at: '2025-01-05T14:30:00Z',
    image: '/images/products/placeholder.jpg',
    images: [
      '/images/products/placeholder.jpg',
      '/images/products/placeholder.jpg'
    ]
  },
  {
    id: 3,
    name: 'PlayStation 5 + Accessoires',
    description: 'Console de jeu nouvelle génération avec manette supplémentaire et 3 jeux inclus.',
    category: 'Électronique',
    condition: 'good',
    value: 450000,
    ticket_price: 1500,
    total_tickets: 350,
    sold_tickets: 298,
    progress: 85,
    revenue: 447000,
    status: 'active',
    end_date: '2025-01-12T16:00:00Z',
    created_at: '2025-01-03T09:15:00Z',
    image: '/images/products/placeholder.jpg',
    images: [
      '/images/products/placeholder.jpg'
    ]
  },
  {
    id: 4,
    name: 'Apple Watch Series 9',
    description: 'Montre connectée Apple avec toutes les fonctionnalités de santé et sport.',
    category: 'Électronique',
    condition: 'new',
    value: 280000,
    ticket_price: 1000,
    total_tickets: 350,
    sold_tickets: 89,
    progress: 25,
    revenue: 89000,
    status: 'draft',
    end_date: null,
    created_at: '2025-01-08T11:20:00Z',
    image: '/images/products/placeholder.jpg',
    images: []
  }
])

// Computed
const filteredProducts = computed(() => {
  let filtered = products.value

  if (filters.search) {
    const search = filters.search.toLowerCase()
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(search) ||
      product.description.toLowerCase().includes(search)
    )
  }

  if (filters.category) {
    filtered = filtered.filter(product => product.category === categories.value.find(c => c.id == filters.category)?.name)
  }

  if (filters.status) {
    filtered = filtered.filter(product => product.status === filters.status)
  }

  // Sort
  filtered.sort((a, b) => {
    switch (filters.sortBy) {
      case 'sales':
        return b.sold_tickets - a.sold_tickets
      case 'end_date':
        return new Date(b.end_date || 0) - new Date(a.end_date || 0)
      case 'value':
        return b.value - a.value
      default:
        return new Date(b.created_at) - new Date(a.created_at)
    }
  })

  return filtered
})

// Methods
const applyFilters = () => {
  // Triggers reactivity
}

const getStatusClass = (status) => {
  const classes = {
    'draft': 'bg-gray-100 text-gray-800 px-3 py-1 text-xs font-medium rounded-full',
    'active': 'bg-green-100 text-green-800 px-3 py-1 text-xs font-medium rounded-full',
    'completed': 'bg-blue-100 text-blue-800 px-3 py-1 text-xs font-medium rounded-full',
    'cancelled': 'bg-red-100 text-red-800 px-3 py-1 text-xs font-medium rounded-full'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 px-3 py-1 text-xs font-medium rounded-full'
}

const getStatusLabel = (status) => {
  const labels = {
    'draft': 'Brouillon',
    'active': 'Actif',
    'completed': 'Terminé',
    'cancelled': 'Annulé'
  }
  return labels[status] || status
}

const getConditionLabel = (condition) => {
  const labels = {
    'new': 'Neuf',
    'like_new': 'Comme neuf',
    'good': 'Bon état',
    'fair': 'État correct'
  }
  return labels[condition] || condition
}

const formatAmount = (amount) => {
  return new Intl.NumberFormat('fr-FR').format(amount || 0)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const viewProduct = (product) => {
  selectedProduct.value = product
  showProductModal.value = true
  showProductMenu.value = null
}

const closeProductModal = () => {
  showProductModal.value = false
  selectedProduct.value = null
}

const editProduct = (product) => {
  router.push(`/merchant/products/${product.id}/edit`)
}

const publishProduct = async (product) => {
  if (confirm(`Publier la tombola pour "${product.name}" ?`)) {
    try {
      product.status = 'active'
      product.end_date = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString()
      alert('Produit publié avec succès !')
    } catch (error) {
      alert('Erreur lors de la publication')
    }
  }
}

const duplicateProduct = (product) => {
  const duplicate = {
    ...product,
    id: Date.now(),
    name: product.name + ' (Copie)',
    status: 'draft',
    sold_tickets: 0,
    progress: 0,
    revenue: 0,
    end_date: null,
    created_at: new Date().toISOString()
  }
  products.value.unshift(duplicate)
  showProductMenu.value = null
  alert('Produit dupliqué avec succès !')
}

const viewAnalytics = (product) => {
  router.push(`/merchant/analytics?product=${product.id}`)
  showProductMenu.value = null
}

const deleteProduct = async (product) => {
  if (confirm(`Supprimer définitivement "${product.name}" ? Cette action est irréversible.`)) {
    try {
      const index = products.value.findIndex(p => p.id === product.id)
      if (index !== -1) {
        products.value.splice(index, 1)
        alert('Produit supprimé')
      }
    } catch (error) {
      alert('Erreur lors de la suppression')
    }
    showProductMenu.value = null
  }
}

const toggleProductMenu = (productId) => {
  showProductMenu.value = showProductMenu.value === productId ? null : productId
}

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showProductMenu.value = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  console.log('Products page loaded')
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
