<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Gestion des produits</h1>
        <p class="mt-2 text-gray-600">Gérez les produits disponibles pour les tombolas</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
      >
        <PlusIcon class="w-5 h-5 mr-2" />
        Ajouter un produit
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
              {{ filteredProducts.length }} produit{{ filteredProducts.length > 1 ? 's' : '' }}
            </h3>
            <div class="flex space-x-2">
              <button
                @click="viewMode = 'table'"
                :class="[
                  'px-3 py-1 rounded-md text-sm font-medium',
                  viewMode === 'table'
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-500 hover:text-gray-700'
                ]"
              >
                Tableau
              </button>
              <button
                @click="viewMode = 'grid'"
                :class="[
                  'px-3 py-1 rounded-md text-sm font-medium',
                  viewMode === 'grid'
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-500 hover:text-gray-700'
                ]"
              >
                Grille
              </button>
            </div>
          </div>
        </div>

        <!-- Table View -->
        <div v-if="viewMode === 'table'" class="overflow-x-auto">
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
                  Prix / Ticket
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Progression
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="product in filteredProducts"
                :key="product.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <ProductImage
                      :src="product.image_url || product.main_image || product.image"
                      :alt="product.title"
                      container-class="w-12 h-12 rounded-lg"
                      image-class="w-full h-full object-cover rounded-lg"
                      fallback-class="w-full h-full rounded-lg"
                    />
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ product.title }}</div>
                      <div class="text-sm text-gray-500">{{ product.description }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ getCategoryName(product.category_id) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div>{{ product.price }} FCFA</div>
                  <div class="text-gray-500">{{ product.ticket_price }} FCFA/ticket</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ product.progress }}%</div>
                  <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                    <div 
                      class="bg-blue-600 h-2 rounded-full" 
                      :style="{ width: product.progress + '%' }"
                    ></div>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ product.sold_tickets }}/{{ product.total_tickets }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    product.status === 'active' ? 'bg-blue-100 text-blue-800' :
                    product.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-gray-100 text-gray-800'
                  ]">
                    {{ getStatusLabel(product.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editProduct(product)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="viewProduct(product.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteProduct(product.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Grid View -->
        <div v-else class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="product in filteredProducts"
              :key="product.id"
              class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
            >
              <div class="relative">
                <ProductImage
                  :src="product.image_url || product.main_image || product.image"
                  :alt="product.title"
                  container-class="w-full h-48"
                  image-class="w-full h-full object-cover"
                />
                <div class="absolute top-2 left-2">
                  <span :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    product.status === 'active' ? 'bg-blue-100 text-blue-800' :
                    product.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-gray-100 text-gray-800'
                  ]">
                    {{ getStatusLabel(product.status) }}
                  </span>
                </div>
              </div>
              <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ product.title }}</h3>
                <p class="text-sm text-gray-600 mb-3">{{ product.description }}</p>
                
                <div class="flex justify-between items-center mb-3">
                  <div class="text-sm">
                    <div class="font-semibold text-gray-900">{{ product.price }} FCFA</div>
                    <div class="text-gray-500">{{ product.ticket_price }} FCFA/ticket</div>
                  </div>
                  <div class="text-sm text-gray-600">
                    {{ getCategoryName(product.category_id) }}
                  </div>
                </div>

                <div class="mb-3">
                  <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progression</span>
                    <span>{{ product.progress }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-blue-600 h-2 rounded-full" 
                      :style="{ width: product.progress + '%' }"
                    ></div>
                  </div>
                </div>

                <div class="flex justify-between items-center">
                  <span class="text-xs text-gray-500">
                    {{ formatDate(product.draw_date) }}
                  </span>
                  <div class="flex space-x-1">
                    <button
                      @click="editProduct(product)"
                      class="p-1 text-blue-600 hover:text-blue-900"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="viewProduct(product.id)"
                      class="p-1 text-blue-600 hover:text-blue-900"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteProduct(product.id)"
                      class="p-1 text-red-600 hover:text-red-900"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Product Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ showCreateModal ? 'Ajouter un produit' : 'Modifier le produit' }}
          </h3>
        </div>

        <form @submit.prevent="showCreateModal ? createProduct() : updateProduct()">
          <div class="px-6 py-4 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                <input
                  v-model="productForm.title"
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prix par ticket (FCFA) *</label>
                <input
                  v-model.number="productForm.ticket_price"
                  type="number"
                  required
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre total de tickets *</label>
                <input
                  v-model.number="productForm.total_tickets"
                  type="number"
                  required
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de tirage *</label>
                <input
                  v-model="productForm.draw_date"
                  type="datetime-local"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select
                  v-model="productForm.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="draft">Brouillon</option>
                  <option value="active">Actif</option>
                  <option value="completed">Terminé</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Image du produit</label>
              <input
                type="file"
                accept="image/*"
                @change="handleImageUpload"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div class="flex items-center">
              <input
                v-model="productForm.featured"
                type="checkbox"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label class="ml-2 text-sm text-gray-700">Produit en vedette</label>
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
              <span v-else>{{ showCreateModal ? 'Créer' : 'Modifier' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import ProductImage from '@/components/common/ProductImage.vue'
import {
  PlusIcon,
  PencilIcon,
  EyeIcon,
  TrashIcon,
  ShoppingBagIcon,
  TrophyIcon,
  CurrencyDollarIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'
import api from '@/composables/api'

const loading = ref(false)
const submitting = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const viewMode = ref('table')
const editingProduct = ref(null)

const filters = ref({
  search: '',
  category: '',
  status: ''
})

const stats = ref([
  {
    label: 'Total produits',
    value: '28',
    color: 'bg-blue-500',
    icon: ShoppingBagIcon
  },
  {
    label: 'Actifs',
    value: '15',
    color: 'bg-blue-500',
    icon: TrophyIcon
  },
  {
    label: 'Revenus totaux',
    value: '2.4M FCFA',
    color: 'bg-yellow-500',
    icon: CurrencyDollarIcon
  },
  {
    label: 'Taux moyen',
    value: '78%',
    color: 'bg-purple-500',
    icon: ChartBarIcon
  }
])

const categories = ref([
  { id: 1, name: 'Électronique' },
  { id: 2, name: 'Mode' },
  { id: 3, name: 'Automobile' },
  { id: 4, name: 'Maison' },
  { id: 5, name: 'Sport' }
])

const products = ref([
  {
    id: 1,
    title: 'iPhone 15 Pro',
    description: 'Le dernier flagship d\'Apple avec une caméra révolutionnaire',
    price: 750000,
    ticket_price: 1000,
    category_id: 1,
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
    price: 1200000,
    ticket_price: 2000,
    category_id: 1,
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
    title: 'Nike Air Jordan',
    description: 'Sneakers exclusives en édition limitée',
    price: 85000,
    ticket_price: 200,
    category_id: 2,
    progress: 78,
    sold_tickets: 332,
    total_tickets: 425,
    status: 'draft',
    featured: false,
    draw_date: new Date(Date.now() + 10 * 24 * 60 * 60 * 1000),
    image: '/images/products/jordan.jpg'
  }
])

const productForm = reactive({
  title: '',
  description: '',
  price: '',
  ticket_price: '',
  category_id: '',
  total_tickets: '',
  draw_date: '',
  status: 'draft',
  featured: false
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

  if (filters.value.status) {
    filtered = filtered.filter(product => product.status === filters.value.status)
  }

  return filtered
})

const resetFilters = () => {
  filters.value = {
    search: '',
    category: '',
    status: ''
  }
}

const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => c.id === categoryId)
  return category ? category.name : 'Inconnue'
}

const getStatusLabel = (status) => {
  const labels = {
    'active': 'Actif',
    'draft': 'Brouillon',
    'completed': 'Terminé'
  }
  return labels[status] || status
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

const editProduct = (product) => {
  editingProduct.value = product
  Object.assign(productForm, {
    title: product.title,
    description: product.description,
    price: product.price,
    ticket_price: product.ticket_price,
    category_id: product.category_id,
    total_tickets: product.total_tickets,
    draw_date: new Date(product.draw_date).toISOString().slice(0, 16),
    status: product.status,
    featured: product.featured
  })
  showEditModal.value = true
}

const viewProduct = (productId) => {
  console.log('View product:', productId)
  // Navigate to product detail view
}

const deleteProduct = async (productId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
    try {
      products.value = products.value.filter(p => p.id !== productId)
      console.log('Product deleted:', productId)
    } catch (error) {
      console.error('Error deleting product:', error)
    }
  }
}

const createProduct = async () => {
  submitting.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    const newProduct = {
      id: Date.now(),
      ...productForm,
      price: parseInt(productForm.price),
      ticket_price: parseInt(productForm.ticket_price),
      total_tickets: parseInt(productForm.total_tickets),
      progress: 0,
      sold_tickets: 0,
      draw_date: new Date(productForm.draw_date),
      image: '/images/products/default.jpg'
    }
    
    products.value.unshift(newProduct)
    closeModal()
    console.log('Product created')
  } catch (error) {
    console.error('Error creating product:', error)
  } finally {
    submitting.value = false
  }
}

const updateProduct = async () => {
  submitting.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    const index = products.value.findIndex(p => p.id === editingProduct.value.id)
    if (index !== -1) {
      products.value[index] = {
        ...products.value[index],
        ...productForm,
        price: parseInt(productForm.price),
        ticket_price: parseInt(productForm.ticket_price),
        total_tickets: parseInt(productForm.total_tickets),
        draw_date: new Date(productForm.draw_date)
      }
    }
    
    closeModal()
    console.log('Product updated')
  } catch (error) {
    console.error('Error updating product:', error)
  } finally {
    submitting.value = false
  }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Handle image upload
    console.log('Image selected:', file.name)
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingProduct.value = null
  Object.assign(productForm, {
    title: '',
    description: '',
    price: '',
    ticket_price: '',
    category_id: '',
    total_tickets: '',
    draw_date: '',
    status: 'draft',
    featured: false
  })
}

onMounted(() => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
  }, 1000)
})
</script>