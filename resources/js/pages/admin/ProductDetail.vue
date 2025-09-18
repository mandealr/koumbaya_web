<template>
  <div class="product-detail">
    <!-- Loading State -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-4 text-lg">Chargement du produit...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="min-h-screen flex items-center justify-center">
      <div class="text-center max-w-md mx-auto px-4">
        <div class="mb-6">
          <ExclamationTriangleIcon class="w-20 h-20 text-red-500 mx-auto mb-4" />
          <h2 class="text-3xl font-bold text-gray-900 mb-2">Produit introuvable</h2>
          <p class="text-gray-600 mb-6">{{ error }}</p>
        </div>
        
        <div class="space-y-3">
          <router-link 
            to="/admin/products" 
            class="block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors"
          >
            <ArrowLeftIcon class="w-5 h-5 inline mr-2" />
            Retour aux produits
          </router-link>
          <button 
            @click="loadProduct" 
            class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-300 transition-colors"
          >
            <ArrowPathIcon class="w-5 h-5 inline mr-2" />
            Réessayer
          </button>
        </div>
      </div>
    </div>

    <!-- Product Details -->
    <div v-else-if="product" class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center space-x-3 mb-2">
            <router-link
              to="/admin/products"
              class="text-gray-500 hover:text-gray-700"
            >
              <ArrowLeftIcon class="w-5 h-5" />
            </router-link>
            <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>
            <span :class="getStatusBadge(product.status)">
              {{ getStatusText(product.status) }}
            </span>
          </div>
          <p class="text-gray-600">Gérer et visualiser les détails de ce produit</p>
        </div>
        
        <div class="flex space-x-3">
          <button
            v-if="product.status === 'active'"
            @click="toggleProductStatus"
            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
          >
            <PauseIcon class="w-4 h-4 mr-2 inline" />
            Désactiver
          </button>
          <button
            v-if="product.status === 'inactive'"
            @click="toggleProductStatus"
            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
          >
            <PlayIcon class="w-4 h-4 mr-2 inline" />
            Activer
          </button>
          <button
            @click="editProduct"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            <PencilIcon class="w-4 h-4 mr-2 inline" />
            Modifier
          </button>
        </div>
      </div>

      <!-- Product Information Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Product Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Information -->
          <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Informations du produit</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <div class="flex space-x-6">
                    <div class="flex-shrink-0">
                      <img
                        :src="product.image_url || product.main_image || '/images/products/placeholder.jpg'"
                        :alt="product.name"
                        class="w-48 h-48 object-cover rounded-lg border"
                      />
                    </div>
                    <div class="flex-1">
                      <h4 class="text-xl font-semibold text-gray-900 mb-2">{{ product.name }}</h4>
                      <p class="text-gray-600 mb-4">{{ product.description || 'Aucune description disponible' }}</p>
                      
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <p class="text-sm font-medium text-gray-500">Prix</p>
                          <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(product.price) }}</p>
                        </div>
                        <div>
                          <p class="text-sm font-medium text-gray-500">Catégorie</p>
                          <p class="text-lg font-semibold text-gray-900">{{ product.category?.name || 'Sans catégorie' }}</p>
                        </div>
                        <div>
                          <p class="text-sm font-medium text-gray-500">Quantité</p>
                          <p class="text-lg font-semibold text-gray-900">{{ product.quantity || 0 }}</p>
                        </div>
                        <div>
                          <p class="text-sm font-medium text-gray-500">Type</p>
                          <p class="text-lg font-semibold text-gray-900">{{ getProductType(product.type) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Merchant Information -->
          <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Informations du marchand</h3>
            </div>
            <div class="p-6">
              <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                  <UserIcon class="w-6 h-6 text-gray-600" />
                </div>
                <div>
                  <p class="font-semibold text-gray-900">{{ product.user?.first_name }} {{ product.user?.last_name }}</p>
                  <p class="text-sm text-gray-600">{{ product.user?.email }}</p>
                  <p class="text-sm text-gray-500">{{ product.user?.phone || 'Téléphone non renseigné' }}</p>
                </div>
              </div>
              
              <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm font-medium text-gray-500">Statut du marchand</p>
                  <p class="text-sm text-gray-900">{{ product.user?.is_active ? 'Actif' : 'Inactif' }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Rôle</p>
                  <p class="text-sm text-gray-900">{{ product.user?.roles?.[0]?.name || 'Client' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Lotteries -->
          <div v-if="product.lotteries && product.lotteries.length > 0" class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Tombolas associées</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div
                  v-for="lottery in product.lotteries"
                  :key="lottery.id"
                  class="border rounded-lg p-4 hover:bg-gray-50"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <h4 class="font-semibold text-gray-900">{{ lottery.title }}</h4>
                      <p class="text-sm text-gray-600 mt-1">{{ lottery.description }}</p>
                      <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                        <span>{{ lottery.sold_tickets || 0 }}/{{ lottery.max_tickets }} tickets</span>
                        <span>{{ formatCurrency(lottery.ticket_price) }}/ticket</span>
                        <span>{{ formatDate(lottery.draw_date) }}</span>
                      </div>
                    </div>
                    <div class="text-right">
                      <span :class="getStatusBadge(lottery.status)">
                        {{ getStatusText(lottery.status) }}
                      </span>
                      <router-link
                        :to="`/admin/lotteries/${lottery.id}`"
                        class="block mt-2 text-blue-600 hover:text-blue-800 text-sm"
                      >
                        Voir détails →
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quick Stats -->
          <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Statistiques</h3>
            </div>
            <div class="p-6 space-y-4">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Vues</span>
                <span class="text-sm font-medium text-gray-900">{{ product.views_count || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Tombolas créées</span>
                <span class="text-sm font-medium text-gray-900">{{ product.lotteries?.length || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Tickets vendus</span>
                <span class="text-sm font-medium text-gray-900">{{ totalTicketsSold }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Revenus générés</span>
                <span class="text-sm font-medium text-gray-900">{{ formatCurrency(totalRevenue) }}</span>
              </div>
            </div>
          </div>

          <!-- Product Metadata -->
          <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Métadonnées</h3>
            </div>
            <div class="p-6 space-y-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Date de création</p>
                <p class="text-sm text-gray-900">{{ formatDate(product.created_at) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Dernière modification</p>
                <p class="text-sm text-gray-900">{{ formatDate(product.updated_at) }}</p>
              </div>
              <div v-if="product.deleted_at">
                <p class="text-sm font-medium text-gray-500">Date de suppression</p>
                <p class="text-sm text-red-600">{{ formatDate(product.deleted_at) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">ID Produit</p>
                <p class="text-sm font-mono text-gray-900">#{{ product.id }}</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-white rounded-xl shadow-sm border">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
            </div>
            <div class="p-6 space-y-3">
              <button
                @click="createLottery"
                class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                <GiftIcon class="w-4 h-4 mr-2" />
                Créer une tombola
              </button>
              
              <button
                @click="viewOrders"
                class="w-full flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
              >
                <DocumentTextIcon class="w-4 h-4 mr-2" />
                Voir les commandes
              </button>
              
              <button
                v-if="!product.deleted_at"
                @click="deleteProduct"
                class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
              >
                <TrashIcon class="w-4 h-4 mr-2" />
                Supprimer le produit
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Product Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/40 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Modifier le produit</h3>
            <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
          
          <form @submit.prevent="updateProduct">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du produit</label>
                <input
                  v-model="editForm.name"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                />
              </div>
              
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                  v-model="editForm.description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                ></textarea>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prix</label>
                <input
                  v-model="editForm.price"
                  type="number"
                  step="0.01"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantité</label>
                <input
                  v-model="editForm.quantity"
                  type="number"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select
                  v-model="editForm.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="active">Actif</option>
                  <option value="inactive">Inactif</option>
                  <option value="out_of_stock">Rupture de stock</option>
                </select>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="showEditModal = false"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
              >
                Annuler
              </button>
              <button
                type="submit"
                :disabled="updating"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                {{ updating ? 'Mise à jour...' : 'Mettre à jour' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { useToast } from '@/composables/useToast'
import {
  ArrowLeftIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon,
  PencilIcon,
  UserIcon,
  GiftIcon,
  DocumentTextIcon,
  TrashIcon,
  XMarkIcon,
  PauseIcon,
  PlayIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const { get, put, patch, delete: apiDelete } = useApi()
const { showSuccess, showError } = useToast()

// Data
const product = ref(null)
const loading = ref(false)
const error = ref('')
const showEditModal = ref(false)
const updating = ref(false)

// Edit form
const editForm = ref({
  name: '',
  description: '',
  price: 0,
  quantity: 0,
  status: 'active'
})

// Computed
const totalTicketsSold = computed(() => {
  if (!product.value?.lotteries) return 0
  return product.value.lotteries.reduce((total, lottery) => total + (lottery.sold_tickets || 0), 0)
})

const totalRevenue = computed(() => {
  if (!product.value?.lotteries) return 0
  return product.value.lotteries.reduce((total, lottery) => {
    return total + ((lottery.sold_tickets || 0) * (lottery.ticket_price || 0))
  }, 0)
})

// Methods
const loadProduct = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const response = await get(`/admin/products/${route.params.id}`)
    if (response.success) {
      product.value = response.data.product
      // Populate edit form
      editForm.value = {
        name: product.value.name,
        description: product.value.description || '',
        price: product.value.price,
        quantity: product.value.quantity || 0,
        status: product.value.status
      }
    } else {
      error.value = response.message || 'Erreur lors du chargement du produit'
    }
  } catch (err) {
    console.error('Erreur lors du chargement du produit:', err)
    error.value = err.response?.data?.message || 'Erreur lors du chargement du produit'
  } finally {
    loading.value = false
  }
}

const editProduct = () => {
  showEditModal.value = true
}

const updateProduct = async () => {
  updating.value = true
  
  try {
    const response = await put(`/admin/products/${product.value.id}`, editForm.value)
    if (response.success) {
      showSuccess('Produit mis à jour avec succès')
      showEditModal.value = false
      await loadProduct()
    } else {
      showError(response.message || 'Erreur lors de la mise à jour')
    }
  } catch (err) {
    console.error('Erreur lors de la mise à jour:', err)
    showError(err.response?.data?.message || 'Erreur lors de la mise à jour')
  } finally {
    updating.value = false
  }
}

const toggleProductStatus = async () => {
  const newStatus = product.value.status === 'active' ? 'inactive' : 'active'
  const action = newStatus === 'active' ? 'activer' : 'désactiver'
  
  if (!confirm(`Êtes-vous sûr de vouloir ${action} ce produit ?`)) {
    return
  }
  
  try {
    const response = await patch(`/admin/products/${product.value.id}/status`, {
      status: newStatus
    })
    
    if (response.success) {
      showSuccess(`Produit ${action === 'activer' ? 'activé' : 'désactivé'} avec succès`)
      product.value.status = newStatus
    } else {
      showError(response.message || `Erreur lors de la ${action}`)
    }
  } catch (err) {
    console.error('Erreur lors du changement de statut:', err)
    showError(err.response?.data?.message || `Erreur lors de la ${action}`)
  }
}

const deleteProduct = async () => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.')) {
    return
  }
  
  try {
    const response = await apiDelete(`/admin/products/${product.value.id}`)
    if (response.success) {
      showSuccess('Produit supprimé avec succès')
      router.push('/admin/products')
    } else {
      showError(response.message || 'Erreur lors de la suppression')
    }
  } catch (err) {
    console.error('Erreur lors de la suppression:', err)
    showError(err.response?.data?.message || 'Erreur lors de la suppression')
  }
}

const createLottery = () => {
  router.push(`/admin/lotteries/create?product_id=${product.value.id}`)
}

const viewOrders = () => {
  router.push(`/admin/orders?product_id=${product.value.id}`)
}

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const formatDate = (dateString) => {
  if (!dateString) return 'Non définie'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusText = (status) => {
  const statusMap = {
    'active': 'Actif',
    'inactive': 'Inactif',
    'out_of_stock': 'Rupture de stock',
    'completed': 'Terminé',
    'cancelled': 'Annulé',
    'pending': 'En attente'
  }
  return statusMap[status] || status
}

const getStatusBadge = (status) => {
  const baseClass = 'px-3 py-1 rounded-full text-sm font-medium'
  const statusClasses = {
    'active': 'bg-green-100 text-green-800',
    'inactive': 'bg-gray-100 text-gray-800',
    'out_of_stock': 'bg-red-100 text-red-800',
    'completed': 'bg-blue-100 text-blue-800',
    'cancelled': 'bg-red-100 text-red-800',
    'pending': 'bg-yellow-100 text-yellow-800'
  }
  return `${baseClass} ${statusClasses[status] || 'bg-gray-100 text-gray-800'}`
}

const getProductType = (type) => {
  const typeMap = {
    'physical': 'Produit physique',
    'digital': 'Produit numérique',
    'service': 'Service',
    'lottery': 'Tombola'
  }
  return typeMap[type] || type || 'Non spécifié'
}

// Lifecycle
onMounted(() => {
  loadProduct()
})
</script>

<style scoped>
.product-detail {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>