<template>
  <MerchantLayout>
    <div class="px-6">
      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Mes Produits</h1>
          <p class="mt-2 text-gray-600">Gérez vos produits et créez des tombolas</p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Nouveau Produit
          </button>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nom du produit..."
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
            <select
              v-model="filters.category"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
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
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >
              <option value="">Tous les statuts</option>
              <option value="draft">Brouillon</option>
              <option value="active">Actif</option>
              <option value="inactive">Inactif</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Actions</label>
            <button
              @click="loadProducts()"
              class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
            >
              Filtrer
            </button>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <div v-else-if="products.length === 0" class="text-center py-12">
        <ShoppingBagIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit</h3>
        <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier produit.</p>
        <div class="mt-6">
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Nouveau Produit
          </button>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="product in products"
          :key="product.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
        >
          <!-- Product Image -->
          <div class="aspect-w-16 aspect-h-9 bg-gray-200">
            <img
              :src="product.image_url || '/images/products/placeholder.jpg'"
              :alt="product.title"
              class="w-full h-48 object-cover"
            />
          </div>

          <!-- Product Info -->
          <div class="p-4">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900 mb-1">{{ product.title }}</h3>
                <p class="text-sm text-gray-600 mb-2">{{ product.category?.name }}</p>
              </div>
              <span :class="[
                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                product.status === 'active' ? 'bg-green-100 text-green-800' :
                product.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                'bg-gray-100 text-gray-800'
              ]">
                {{ getStatusText(product.status) }}
              </span>
            </div>

            <div class="space-y-2 mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Prix produit:</span>
                <span class="font-medium">{{ formatCurrency(product.price) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Prix ticket:</span>
                <span class="font-medium">{{ formatCurrency(product.ticket_price) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Vues:</span>
                <span class="font-medium">{{ product.views_count || 0 }}</span>
              </div>
            </div>

            <!-- Lottery Status -->
            <div v-if="product.active_lottery" class="mb-4 p-3 bg-green-50 rounded-lg">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-green-800">Tombola Active</span>
                <span class="text-xs text-green-600">{{ product.active_lottery.lottery_number }}</span>
              </div>
              <div class="flex items-center">
                <div class="flex-1 bg-green-200 rounded-full h-2 mr-2">
                  <div 
                    class="bg-green-600 h-2 rounded-full" 
                    :style="{ width: product.active_lottery.completion_rate + '%' }"
                  ></div>
                </div>
                <span class="text-sm text-green-700">{{ product.active_lottery.completion_rate }}%</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
              <button
                @click="editProduct(product)"
                class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-md text-sm hover:bg-gray-200"
              >
                <PencilIcon class="w-4 h-4 inline mr-1" />
                Modifier
              </button>
              
              <button
                v-if="!product.active_lottery"
                @click="createLottery(product)"
                class="flex-1 px-3 py-2 bg-green-100 text-green-700 rounded-md text-sm hover:bg-green-200"
              >
                <GiftIcon class="w-4 h-4 inline mr-1" />
                Tombola
              </button>
              
              <button
                v-else
                @click="viewLottery(product.active_lottery)"
                class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 rounded-md text-sm hover:bg-blue-200"
              >
                <EyeIcon class="w-4 h-4 inline mr-1" />
                Voir
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="mt-8 flex justify-center">
        <nav class="flex items-center space-x-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Précédent
          </button>
          
          <span class="px-3 py-2 text-sm text-gray-700">
            Page {{ pagination.current_page }} sur {{ Math.ceil(pagination.total / pagination.per_page) }}
          </span>
          
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= Math.ceil(pagination.total / pagination.per_page)"
            class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Suivant
          </button>
        </nav>
      </div>
    </div>

    <!-- Create/Edit Product Modal -->
    <ProductFormModal
      v-if="showCreateModal || showEditModal"
      :product="selectedProduct"
      :categories="categories"
      @close="closeModals"
      @saved="onProductSaved"
    />

    <!-- Create Lottery Modal -->
    <LotteryCreateModal
      v-if="showLotteryModal"
      :product="selectedProduct"
      @close="showLotteryModal = false"
      @created="onLotteryCreated"
    />
  </MerchantLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import MerchantLayout from '@/components/common/MerchantLayout.vue'
import ProductFormModal from '@/components/merchant/ProductFormModal.vue'
import LotteryCreateModal from '@/components/merchant/LotteryCreateModal.vue'
import {
  PlusIcon,
  ShoppingBagIcon,
  PencilIcon,
  GiftIcon,
  EyeIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const { get, post } = useApi()

// Data
const products = ref([])
const categories = ref([])
const loading = ref(false)
const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0
})

// Modals
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showLotteryModal = ref(false)
const selectedProduct = ref(null)

// Filters
const filters = ref({
  search: '',
  category: '',
  status: ''
})

// Methods
const loadProducts = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pagination.value.per_page.toString(),
      merchant_only: 'true',
      ...filters.value
    })

    const response = await get(`/products?${params}`)
    products.value = response.data.data || response.data.products || []
    
    if (response.data.current_page !== undefined) {
      pagination.value = {
        current_page: response.data.current_page,
        per_page: response.data.per_page,
        total: response.data.total
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des produits:', error)
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await get('/categories')
    categories.value = response.data.categories || []
  } catch (error) {
    console.error('Erreur lors du chargement des catégories:', error)
  }
}

const editProduct = (product) => {
  selectedProduct.value = product
  showEditModal.value = true
}

const createLottery = (product) => {
  selectedProduct.value = product
  showLotteryModal.value = true
}

const viewLottery = (lottery) => {
  router.push(`/merchant/lotteries/${lottery.id}`)
}

const closeModals = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedProduct.value = null
}

const onProductSaved = () => {
  closeModals()
  loadProducts()
}

const onLotteryCreated = () => {
  showLotteryModal.value = false
  selectedProduct.value = null
  loadProducts()
}

const changePage = (page) => {
  if (page > 0 && page <= Math.ceil(pagination.value.total / pagination.value.per_page)) {
    pagination.value.current_page = page
    loadProducts(page)
  }
}

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const getStatusText = (status) => {
  const statusMap = {
    'active': 'Actif',
    'draft': 'Brouillon',
    'inactive': 'Inactif'
  }
  return statusMap[status] || status
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProducts(),
    loadCategories()
  ])
})
</script>