<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Gestion des produits</h1>
        <p class="mt-2 text-gray-600">Gérez les produits disponibles pour les tombolas</p>
      </div>
      <button
        @click="loadProducts"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
      >
        <ArrowPathIcon class="w-5 h-5 mr-2" :class="loading ? 'animate-spin' : ''" />
        Actualiser
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white p-6 rounded-lg shadow-sm border border-gray-200"
      >
        <div class="flex items-center">
          <div :class="['p-3 rounded-lg', stat.color]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
      </div>
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
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les statuts</option>
            <option value="active">Actif</option>
            <option value="draft">Brouillon</option>
            <option value="completed">Terminé</option>
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

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Chargement des produits...</p>
      </div>

      <div v-else>
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ totalProducts }} produit{{ totalProducts > 1 ? 's' : '' }}
            </h3>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produit
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Catégorie
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Prix
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Marchand
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tombola
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-16 h-16 flex-shrink-0">
                      <ProductImage
                        :src="product.image_url || product.main_image"
                        :alt="product.name"
                        container-class="w-16 h-16 rounded-lg"
                        image-class="w-full h-full object-cover rounded-lg"
                      />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                      <div class="text-sm text-gray-500 line-clamp-2">{{ product.description }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900">{{ product.category?.name || 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatAmount(product.price) }} FCFA</div>
                  <div v-if="product.sale_mode === 'lottery'" class="text-xs text-gray-500">
                    {{ formatAmount(product.ticket_price) }} FCFA/ticket
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="product.merchant" class="text-sm text-gray-900">{{ product.merchant.name }}</div>
                  <div v-if="product.merchant" class="text-xs text-gray-500">{{ product.merchant.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    getStatusClass(product.status)
                  ]">
                    {{ getStatusLabel(product.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="product.lottery">
                    <div class="flex items-center">
                      <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                        <div 
                          class="bg-blue-600 h-2 rounded-full" 
                          :style="{ width: product.lottery.progress + '%' }"
                        ></div>
                      </div>
                      <span class="text-xs text-gray-600">{{ product.lottery.progress }}%</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                      {{ product.lottery.sold_tickets }}/{{ product.lottery.total_tickets }} tickets
                    </div>
                  </div>
                  <span v-else class="text-xs text-gray-500">{{ getSaleModeLabel(product.sale_mode) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editProduct(product)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Modifier"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="viewProduct(product.id)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Voir"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteProduct(product.id)"
                      class="text-red-600 hover:text-red-900"
                      title="Supprimer"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-3 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Affichage de {{ (currentPage - 1) * itemsPerPage + 1 }} à 
              {{ Math.min(currentPage * itemsPerPage, totalProducts) }} sur 
              {{ totalProducts }} résultats
            </div>
            <div class="flex space-x-2">
              <button 
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded disabled:opacity-50"
              >
                Précédent
              </button>
              <button 
                @click="currentPage++"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded disabled:opacity-50"
              >
                Suivant
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Product Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            Modifier le produit
          </h3>
        </div>

        <form @submit.prevent="updateProduct()">
          <div class="px-6 py-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                <input
                  v-model="productForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie *</label>
                <select
                  v-model="productForm.category_id"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Sélectionner une catégorie</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="productForm.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prix du produit (FCFA) *</label>
                <input
                  v-model.number="productForm.price"
                  type="number"
                  required
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div v-if="productForm.sale_mode === 'lottery'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Prix par ticket (FCFA)</label>
                <input
                  v-model.number="productForm.ticket_price"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select
                  v-model="productForm.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="draft">Brouillon</option>
                  <option value="active">Actif</option>
                  <option value="completed">Terminé</option>
                  <option value="cancelled">Annulé</option>
                </select>
              </div>
              
              <div class="flex items-center pt-6">
                <input
                  v-model="productForm.is_featured"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <label class="ml-2 text-sm text-gray-700">Produit en vedette</label>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-gray-600 hover:text-gray-900"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
            >
              <span v-if="submitting">Enregistrement...</span>
              <span v-else>Modifier</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import ProductImage from '@/components/common/ProductImage.vue'
import {
  ArrowPathIcon,
  PencilIcon,
  EyeIcon,
  TrashIcon,
  ShoppingBagIcon,
  TrophyIcon,
  CurrencyDollarIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'
import { useApi } from '@/composables/api'
import { useRouter } from 'vue-router'

const { get, put, delete: del } = useApi()
const router = useRouter()

const loading = ref(false)
const submitting = ref(false)
const showEditModal = ref(false)
const editingProduct = ref(null)
const products = ref([])
const categories = ref([])
const currentPage = ref(1)
const totalProducts = ref(0)
const itemsPerPage = ref(20)

const filters = reactive({
  search: '',
  category: '',
  status: ''
})

const stats = ref([
  {
    label: 'Total produits',
    value: '0',
    color: 'bg-blue-500',
    icon: ShoppingBagIcon
  },
  {
    label: 'Actifs',
    value: '0',
    color: 'bg-green-500',
    icon: TrophyIcon
  },
  {
    label: 'Valeur totale',
    value: '0 FCFA',
    color: 'bg-yellow-500',
    icon: CurrencyDollarIcon
  },
  {
    label: 'En tombola',
    value: '0 FCFA',
    color: 'bg-purple-500',
    icon: ChartBarIcon
  }
])

const productForm = reactive({
  name: '',
  description: '',
  price: '',
  ticket_price: '',
  category_id: '',
  sale_mode: 'direct',
  status: 'draft',
  is_featured: false
})

// Watch filters for changes
watch(
  () => filters,
  () => {
    currentPage.value = 1
    loadProducts()
  },
  { deep: true }
)

// Watch page changes
watch(currentPage, () => {
  loadProducts()
})

const totalPages = computed(() => {
  return Math.ceil(totalProducts.value / itemsPerPage.value)
})

const loadProducts = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', currentPage.value)
    params.append('per_page', itemsPerPage.value)
    
    if (filters.search) params.append('search', filters.search)
    if (filters.category) params.append('category_id', filters.category)
    if (filters.status) params.append('status', filters.status)
    
    const response = await get(`/admin/products?${params.toString()}`)
    
    if (response && response.data) {
      products.value = response.data.products || []
      totalProducts.value = response.data.pagination?.total || 0
      categories.value = response.data.categories || []
      
      // Update stats
      if (response.data.stats) {
        stats.value[0].value = response.data.stats.total?.toString() || '0'
        stats.value[1].value = response.data.stats.active?.toString() || '0'
        stats.value[2].value = formatAmount(response.data.stats.total_value || 0) + ' FCFA'
        stats.value[3].value = formatAmount(response.data.stats.total_lottery_value || 0) + ' FCFA'
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des produits:', error)
    products.value = []
  } finally {
    loading.value = false
  }
}

const updateProduct = async () => {
  submitting.value = true
  try {
    const response = await put(`/admin/products/${editingProduct.value.id}`, productForm)
    if (response && response.success) {
      await loadProducts()
      showEditModal.value = false
      resetForm()
      if (window.$toast) {
        window.$toast.success('Produit mis à jour avec succès', '✓ Succès')
      }
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la mise à jour', '✗ Erreur')
    }
  } finally {
    submitting.value = false
  }
}

const deleteProduct = async (id) => {
  if (confirm('Voulez-vous vraiment supprimer ce produit ? Cette action est irréversible.')) {
    try {
      const response = await del(`/admin/products/${id}`)
      if (response && response.success) {
        await loadProducts()
        if (window.$toast) {
          window.$toast.success('Produit supprimé avec succès', '✓ Succès')
        }
      }
    } catch (error) {
      console.error('Erreur lors de la suppression:', error)
      if (window.$toast) {
        const message = error.response?.data?.message || 'Erreur lors de la suppression'
        window.$toast.error(message, '✗ Erreur')
      }
    }
  }
}

const editProduct = (product) => {
  editingProduct.value = product
  Object.assign(productForm, {
    name: product.name,
    description: product.description,
    price: product.price,
    ticket_price: product.ticket_price,
    category_id: product.category_id,
    sale_mode: product.sale_mode,
    status: product.status,
    is_featured: product.is_featured || false
  })
  showEditModal.value = true
}

const viewProduct = (id) => {
  router.push(`/admin/products/${id}`)
}

const closeModal = () => {
  showEditModal.value = false
  resetForm()
}

const resetForm = () => {
  Object.assign(productForm, {
    name: '',
    description: '',
    price: '',
    ticket_price: '',
    category_id: '',
    sale_mode: 'direct',
    status: 'draft',
    is_featured: false
  })
  editingProduct.value = null
}

const resetFilters = () => {
  filters.search = ''
  filters.category = ''
  filters.status = ''
  currentPage.value = 1
}

const formatAmount = (amount) => {
  return new Intl.NumberFormat('fr-FR').format(amount || 0)
}

const getStatusLabel = (status) => {
  const labels = {
    'active': 'Actif',
    'draft': 'Brouillon',
    'completed': 'Terminé',
    'cancelled': 'Annulé'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    'active': 'bg-green-100 text-green-800',
    'draft': 'bg-yellow-100 text-yellow-800',
    'completed': 'bg-blue-100 text-blue-800',
    'cancelled': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getSaleModeLabel = (saleMode) => {
  const labels = {
    'direct': 'Vente directe',
    'lottery': 'Tombola'
  }
  return labels[saleMode] || saleMode
}

onMounted(() => {
  loadProducts()
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