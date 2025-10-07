<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Mes Articles</h1>
        <p class="mt-2 text-gray-600">Gérez vos articles et suivez vos tirages spéciaux en cours</p>
      </div>
      <div class="flex space-x-3">
        <router-link
          to="/merchant/products/create"
          class="inline-flex items-center gap-2 px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
        >
          <PlusIcon class="w-5 h-5" />
          Nouvel Article
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
            <component :is="getStatIcon(stat.icon)" class="w-6 h-6 text-white" />
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center text-xs">
            <span :class="[
              'inline-flex items-center px-2 py-1 rounded-full font-medium',
              stat.change >= 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
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
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black">
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
          <label class="block text-sm font-medium text-gray-700 mb-2">Mode de vente</label>
          <select
            v-model="filters.saleMode"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black"
          >
            <option value="">Tous les modes</option>
            <option value="direct">Vente directe</option>
            <option value="lottery">Tombola</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
          <select
            v-model="filters.sortBy"
            @change="applyFilters"
            class="w-full py-2 px-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-black">
            <option value="date_desc">Date de création (récent)</option>
            <option value="date_asc">Date de création (ancien)</option>
            <option value="price_desc">Prix (élevé à bas)</option>
            <option value="price_asc">Prix (bas à élevé)</option>
            <option value="name_asc">Nom (A-Z)</option>
            <option value="name_desc">Nom (Z-A)</option>
            <option value="popularity">Popularité</option>
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
      <p class="text-gray-600 mb-6">Vous n'avez pas encore de produits. Créez votre premier produit !</p>
      <router-link
        to="/merchant/products/create"
        class="inline-flex items-center gap-2 px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-xl font-semibold transition-colors"
      >
        <PlusIcon class="w-5 h-5" />
        Créer mon premier produit
      </router-link>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative">
      <div
        v-for="product in filteredProducts"
        :key="product.id"
        class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-200 hover:scale-[1.02] relative"
      >
        <!-- Product Image -->
        <div class="relative h-48 bg-gray-200">
          <ProductImage
            :src="getProductImageSrc(product)"
            :alt="product.name"
            container-class="w-full h-full"
            image-class="w-full h-full object-cover"
          />
          <div class="absolute top-3 left-3">
            <span :class="getStatusClass(product.status)">
              {{ getStatusLabel(product.status) }}
            </span>
          </div>
          <div class="absolute top-3 right-3 flex flex-col gap-2">
            <div class="bg-black/50 text-white px-2 py-1 rounded-lg text-xs font-medium">
              {{ product.category?.name || 'Sans catégorie' }}
            </div>
            <div :class="getSaleModeClass(product.sale_mode)">
              {{ getSaleModeLabel(product.sale_mode) }}
            </div>
          </div>
        </div>

        <!-- Product Content -->
        <div class="p-6 relative">
          <div class="flex items-start justify-between mb-3">
            <h3 class="text-lg font-semibold text-gray-900 line-clamp-2">{{ product.name }}</h3>
            <div class="text-right ml-2">
              <p class="text-lg font-bold text-[#0099cc]">{{ formatAmount(product.price) }} FCFA</p>
              <p class="text-xs text-gray-500">Valeur</p>
            </div>
          </div>

          <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ product.description }}</p>

          <!-- Product Info -->
          <div class="space-y-3 mb-4">
            <!-- Direct Sale Mode -->
            <div v-if="product.sale_mode === 'direct'" class="text-center py-2">
              <div class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Vente directe
              </div>
            </div>

            <!-- Lottery Mode -->
            <div v-else-if="product.sale_mode === 'lottery'">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Progression</span>
                <span class="font-medium">{{ getLotteryProgression(product).sold }}/{{ getLotteryProgression(product).max }}</span>
              </div>

              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all duration-500"
                  :style="{ width: getLotteryProgression(product).percentage + '%' }"
                ></div>
              </div>

              <div class="flex justify-between text-xs text-gray-500">
                <span>{{ getLotteryProgression(product).percentage }}% vendu</span>
                <span v-if="product.lottery?.draw_date">Fin: {{ formatDate(product.lottery.draw_date) }}</span>
              </div>
            </div>
          </div>

          <!-- Revenue Info -->
          <div class="grid grid-cols-2 gap-4 mb-4 text-center">
            <div class="bg-green-50 p-3 rounded-lg">
              <p class="text-green-700 font-semibold text-lg">{{ formatAmount(product.revenue || 0) }}</p>
              <p class="text-green-600 text-xs">Revenus</p>
            </div>
            <div class="bg-blue-50 p-3 rounded-lg">
              <p v-if="product.sale_mode === 'lottery'" class="text-blue-700 font-semibold text-lg">{{ formatAmount(product.lottery?.ticket_price || 0) }}</p>
              <p v-else class="text-blue-700 font-semibold text-lg">{{ product.stock_quantity || 0 }}</p>
              <p v-if="product.sale_mode === 'lottery'" class="text-blue-600 text-xs">Prix/ticket</p>
              <p v-else class="text-blue-600 text-xs">Stock</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex space-x-2">
            <button
              @click="viewProduct(product)"
              class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors inline-flex items-center justify-center gap-1"
            >
              <EyeIcon class="w-4 h-4" />
              Voir
            </button>

            <button
              v-if="product.status === 'draft'"
              @click="publishProduct(product)"
              class="flex-1 px-4 py-2 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-lg text-sm font-medium transition-colors inline-flex items-center justify-center gap-1"
            >
              <RocketLaunchIcon class="w-4 h-4" />
              Publier
            </button>

            <button
              v-if="product.status === 'active'"
              @click="editProduct(product)"
              class="flex-1 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-medium transition-colors inline-flex items-center justify-center gap-1"
            >
              <PencilIcon class="w-4 h-4" />
              Modifier
            </button>

            <div class="relative">
              <button
                @click="toggleProductMenu(product.id)"
                :data-product-id="product.id"
                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
              >
                <EllipsisVerticalIcon class="w-4 h-4" />
              </button>

              <div
                v-if="showProductMenu === product.id"
                data-dropdown-menu
                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200"
                style="z-index: 999999; position: fixed;"
              >
                <button
                  @click="duplicateProduct(product)"
                  class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 rounded-t-lg flex items-center gap-2"
                >
                  <DocumentDuplicateIcon class="w-4 h-4" />
                  Dupliquer
                </button>
                <button
                  @click="viewAnalytics(product)"
                  class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                >
                  <ChartBarIcon class="w-4 h-4" />
                  Analytiques
                </button>
                <button
                  v-if="canDeleteProduct(product)"
                  @click="deleteProduct(product)"
                  class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 rounded-b-lg flex items-center gap-2"
                >
                  <TrashIcon class="w-4 h-4" />
                  Supprimer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Details Modal -->
    <div v-if="showProductModal" class="fixed inset-0 bg-black/40 overflow-y-auto h-full w-full z-50">
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
              <ProductImage
                v-for="(image, index) in selectedProduct.images.slice(0, 6)"
                :key="index"
                :src="image"
                :alt="`Produit ${index + 1}`"
                container-class="w-full h-20 rounded-lg"
                image-class="w-full h-full object-cover rounded-lg"
                fallback-class="w-full h-full rounded-lg"
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
              <p class="text-sm text-gray-900">{{ selectedProduct.category?.name || selectedProduct.category || 'Sans catégorie' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Valeur</label>
              <p class="text-sm text-gray-900 font-semibold">{{ formatAmount(selectedProduct.price || selectedProduct.value) }} FCFA</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Condition</label>
              <p class="text-sm text-gray-900">{{ getConditionLabel(selectedProduct.condition) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Mode de vente</label>
              <span :class="getSaleModeClass(selectedProduct.sale_mode)">{{ getSaleModeLabel(selectedProduct.sale_mode) }}</span>
            </div>
            <div v-if="selectedProduct.sale_mode === 'lottery' && selectedProduct.ticket_price">
              <label class="block text-sm font-medium text-gray-700">Prix du ticket</label>
              <p class="text-sm text-gray-900 font-semibold">{{ formatAmount(selectedProduct.ticket_price) }} FCFA</p>
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <p class="text-sm text-gray-900 bg-gray-100 p-3 rounded-lg">{{ selectedProduct.description }}</p>
          </div>

          <!-- Lottery Stats -->
          <div v-if="selectedProduct.sale_mode === 'lottery'">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Statistiques de la tombola</h4>
            <div class="grid grid-cols-3 gap-4 text-center">
              <div class="bg-[#0099cc]/10 p-4 rounded-lg">
                <p class="text-2xl font-bold text-[#0099cc]">{{ selectedProduct.sold_tickets }}</p>
                <p class="text-sm text-gray-600">Tickets vendus</p>
              </div>
              <div class="bg-blue-100 p-4 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ formatAmount(selectedProduct.revenue) }}</p>
                <p class="text-sm text-gray-600">Koumbich (FCFA)</p>
              </div>
              <div class="bg-purple-100 p-4 rounded-lg">
                <p class="text-2xl font-bold text-purple-600">{{ selectedProduct.progress }}%</p>
                <p class="text-sm text-gray-600">Progression</p>
              </div>
            </div>
          </div>

          <!-- Direct Sale Info -->
          <div v-else-if="selectedProduct.sale_mode === 'direct'" class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <div>
                <h4 class="text-md font-semibold text-green-800">Vente directe</h4>
                <p class="text-sm text-green-600">Les clients peuvent acheter ce produit directement sans participer à une tombola.</p>
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
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import ProductImage from '@/components/common/ProductImage.vue'
import { useApi } from '@/composables/api'
import { useMerchantProducts } from '@/composables/useMerchantProducts'
import { useProductImage } from '@/composables/useProductImage'
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
const { get, post, put, delete: del, loading: apiLoading, error } = useApi()

// Composables
const {
  productStats,
  categories,
  loading: statsLoading,
  loadProductStats,
  loadCategories,
  formatCurrency
} = useMerchantProducts()

// State
const showProductModal = ref(false)
const selectedProduct = ref(null)
const showProductMenu = ref(null)

const filters = reactive({
  search: '',
  category: '',
  status: '',
  saleMode: '',
  sortBy: 'date_desc'
})

const products = ref([])

// Computed for loading state
const loading = computed(() => apiLoading.value || statsLoading.value)

// Computed
const filteredProducts = computed(() => {
  // Les filtres et tri sont gérés côté backend
  return products.value
})

// Methods
const applyFilters = async () => {
  await loadProducts()
}

const getStatusClass = (status) => {
  const classes = {
    'draft': 'bg-gray-100 text-gray-800 px-3 py-1 text-xs font-medium rounded-full',
    'active': 'bg-blue-100 text-blue-800 px-3 py-1 text-xs font-medium rounded-full',
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

const getSaleModeClass = (saleMode) => {
  const classes = {
    'direct': 'bg-green-100 text-green-800 px-2 py-1 text-xs font-medium rounded-lg',
    'lottery': 'bg-purple-100 text-purple-800 px-2 py-1 text-xs font-medium rounded-lg'
  }
  return classes[saleMode] || 'bg-gray-100 text-gray-800 px-2 py-1 text-xs font-medium rounded-lg'
}

const getSaleModeLabel = (saleMode) => {
  const labels = {
    'direct': 'Vente directe',
    'lottery': 'Tombola'
  }
  return labels[saleMode] || labels['direct'] // Par défaut 'Vente directe' si non défini
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

// API Functions
const loadProducts = async () => {
  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.category) params.append('category_id', filters.category)
    if (filters.status) params.append('status', filters.status)
    if (filters.saleMode) params.append('sale_mode', filters.saleMode)
    if (filters.sortBy) params.append('sort_by', filters.sortBy)

    const response = await get(`/merchant/products?${params.toString()}`)
    console.log('Products API Response:', response)
    if (response && response.data && Array.isArray(response.data)) {
      products.value = response.data.map(product => {
        console.log('Product mapping:', {
          id: product.id,
          name: product.name,
          lottery: product.lottery,
          lottery_progression: product.lottery_progression,
          sale_mode: product.sale_mode,
          revenue: product.revenue,
          can_delete: product.can_delete
        })
        
        return {
          ...product,
          lottery: product.lottery || null,
          lottery_progression: product.lottery_progression || null,
          ticket_price: parseFloat(product.ticket_price || 0),
          price: parseFloat(product.price || 0),
          revenue: product.revenue || 0,
          can_delete: product.can_delete,
          paid_orders_count: product.paid_orders_count || 0
        }
      })
    } else {
      products.value = []
    }
  } catch (error) {
    console.error('Error loading products:', error)
  }
}

// Categories and stats are now loaded via composables

const publishProduct = async (product) => {
  const actionText = product.sale_mode === 'lottery' ? 'tombola' : 'produit'
  const confirmText = `Publier ${actionText === 'tombola' ? 'la' : 'le'} ${actionText} "${product.title || product.name}" ?`

  if (confirm(confirmText)) {
    try {
      let response

      if (product.sale_mode === 'lottery') {
        // Pour les produits en mode tombola, créer la tombola
        if (!product.ticket_price || product.ticket_price < 100) {
          if (window.$toast) {
            window.$toast.error('Le prix du ticket doit être défini et supérieur à 100 FCFA', '❌ Erreur')
          }
          return
        }

        response = await post(`/products/${product.id}/create-lottery`, {
          duration_days: 7
        })
      } else {
        // Pour les produits en mode direct, juste changer le statut
        response = await put(`/products/${product.id}`, {
          status: 'active'
        })
      }

      if (response && (response.success || response.product)) {
        await loadProducts() // Refresh products
        if (window.$toast) {
          const successMessage = product.sale_mode === 'lottery' ?
            'Tombola publiée avec succès !' :
            'Produit publié avec succès !'
          window.$toast.success(successMessage, '✅ Publication')
        }
      } else {
        throw new Error(response?.message || 'Erreur lors de la publication')
      }
    } catch (error) {
      console.error('Error publishing product:', error)
      let errorMessage = 'Erreur lors de la publication'

      if (error.response?.data?.error) {
        errorMessage = error.response.data.error
      } else if (error.response?.data?.message) {
        errorMessage = error.response.data.message
      } else if (error.message) {
        errorMessage = error.message
      }

      if (window.$toast) {
        window.$toast.error(errorMessage, '❌ Erreur')
      }
    }
  }
}

const duplicateProduct = async (product) => {
  try {
    const response = await post('/products', {
      title: (product.title || product.name) + ' (Copie)',
      description: product.description,
      price: product.price || product.value,
      category_id: product.category_id,
      image: product.image
    })

    if (response && response.success) {
      await loadProducts() // Refresh products
      if (window.$toast) {
        window.$toast.success('Produit dupliqué avec succès !', '✅ Duplication')
      }
    } else {
      throw new Error(response?.message || 'Erreur lors de la duplication')
    }
  } catch (error) {
    console.error('Error duplicating product:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la duplication', '❌ Erreur')
    }
  }
  showProductMenu.value = null
}

const viewAnalytics = (product) => {
  router.push(`/merchant/analytics?product=${product.id}`)
  showProductMenu.value = null
}

const deleteProduct = async (product) => {
  if (confirm(`Supprimer définitivement "${product.title || product.name}" ? Cette action est irréversible.`)) {
    try {
      const response = await del(`/products/${product.id}`)
      if (response && response.success) {
        await loadProducts() // Refresh products
        if (window.$toast) {
          window.$toast.success('Produit supprimé', '✅ Suppression')
        }
      } else {
        throw new Error(response?.message || 'Erreur lors de la suppression')
      }
    } catch (error) {
      console.error('Error deleting product:', error)
      if (window.$toast) {
        window.$toast.error('Erreur lors de la suppression', '❌ Erreur')
      }
    }
    showProductMenu.value = null
  }
}

// formatCurrency is now provided by the composable

// Utiliser le composable pour les images
const { getProductImageUrlWithFallback } = useProductImage()

const getProductImageSrc = (product) => {
  return getProductImageUrlWithFallback(product, '/images/products/placeholder.jpg')
}

const toggleProductMenu = (productId) => {
  showProductMenu.value = showProductMenu.value === productId ? null : productId
}

// Helper pour les icônes des stats
const getStatIcon = (iconName) => {
  const icons = {
    GiftIcon,
    CurrencyDollarIcon,
    EyeIcon,
    ChartBarIcon,
    ShoppingBagIcon
  }
  return icons[iconName] || GiftIcon
}

// Vérifier si un produit peut être supprimé
const canDeleteProduct = (product) => {
  // Utiliser la logique du backend si disponible
  if (product.can_delete !== undefined) {
    return product.can_delete
  }
  
  // Fallback sur l'ancienne logique
  // Ne peut pas supprimer si le statut n'est pas draft ou active
  if (!['draft', 'active'].includes(product.status)) {
    return false
  }
  
  // Pour les produits tombola : vérifier s'il y a des tickets vendus
  if (product.sale_mode === 'lottery' && product.lottery) {
    const soldTickets = product.lottery.sold_tickets || 0
    if (soldTickets > 0) {
      return false
    }
  }
  
  // Pour les produits de vente directe : vérifier s'il y a des commandes payées
  if (product.sale_mode === 'direct') {
    const paidOrdersCount = product.paid_orders_count || 0
    if (paidOrdersCount > 0) {
      return false
    }
  }
  
  return true
}

// Obtenir la progression de la tombola
const getLotteryProgression = (product) => {
  if (product.sale_mode !== 'lottery') {
    return { sold: 0, max: 0, percentage: 0 }
  }
  
  // Utiliser lottery_progression si disponible, sinon lottery
  const progression = product.lottery_progression || product.lottery
  
  if (!progression) {
    return { sold: 0, max: 0, percentage: 0 }
  }
  
  const sold = progression.sold_tickets || 0
  const max = progression.max_tickets || 0
  const percentage = max > 0 ? Math.round((sold / max) * 100) : 0
  
  return { sold, max, percentage }
}

// Close menu when clicking outside
const handleClickOutside = (event) => {
  // Vérifier si le clic est en dehors du menu et du bouton
  const menuElement = event.target.closest('[data-dropdown-menu]')
  const buttonElement = event.target.closest('[data-product-id]')
  
  if (!menuElement && !buttonElement) {
    showProductMenu.value = null
  }
}

onMounted(async () => {
  document.addEventListener('click', handleClickOutside)
  await Promise.all([
    loadProducts(),
    loadCategories(),
    loadProductStats()
  ])
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
