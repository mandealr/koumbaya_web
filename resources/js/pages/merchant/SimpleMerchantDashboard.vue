<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 space-y-8">
      <!-- Header avec message de bienvenue -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Espace Vendeur Simplifié</h1>
            <p class="mt-2 text-gray-600">Interface dédiée aux vendeurs individuels - Simple et efficace</p>
            <div class="mt-4 inline-flex items-center px-4 py-2 bg-blue-50 text-blue-800 rounded-lg text-sm font-medium">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              Profil : Vendeur Individuel
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500">Dernière connexion</p>
            <p class="text-lg font-semibold text-gray-900">{{ formatDate(new Date()) }}</p>
          </div>
        </div>
      </div>

      <!-- Statistiques essentielles -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Produits actifs</p>
              <p class="text-2xl font-bold text-[#0099cc] mt-1">{{ stats.activeProducts || 0 }}</p>
            </div>
            <div class="p-3 bg-[#0099cc] rounded-xl">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Ventes ce mois</p>
              <p class="text-2xl font-bold text-green-600 mt-1">{{ formatAmount(stats.monthlyRevenue || 0) }}</p>
              <p class="text-xs text-gray-500">FCFA</p>
            </div>
            <div class="p-3 bg-green-500 rounded-xl">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Commandes totales</p>
              <p class="text-2xl font-bold text-blue-600 mt-1">{{ stats.totalOrders || 0 }}</p>
            </div>
            <div class="p-3 bg-blue-500 rounded-xl">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Taux de réussite</p>
              <p class="text-2xl font-bold text-purple-600 mt-1">{{ stats.successRate || 0 }}%</p>
            </div>
            <div class="p-3 bg-purple-500 rounded-xl">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions rapides -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Actions Rapides</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <button
            @click="showCreateProductModal = true"
            class="flex flex-col items-center justify-center p-8 bg-gradient-to-br from-[#0099cc] to-[#0088bb] text-white rounded-2xl hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <h3 class="text-lg font-semibold mb-2">Créer un Produit</h3>
            <p class="text-blue-100 text-sm text-center">Publier un nouveau produit avec 500 tickets fixes</p>
          </button>

          <router-link
            to="/merchant/products"
            class="flex flex-col items-center justify-center p-8 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="text-lg font-semibold mb-2">Mes Produits</h3>
            <p class="text-blue-100 text-sm text-center">Gérer mes {{ products.length }} produit(s)</p>
          </router-link>

          <router-link
            to="/merchant/orders"
            class="flex flex-col items-center justify-center p-8 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
          >
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h3 class="text-lg font-semibold mb-2">Mes Ventes</h3>
            <p class="text-green-100 text-sm text-center">{{ recentOrders.length }} commande(s) récente(s)</p>
          </router-link>
        </div>
      </div>

      <!-- Mes Produits (Aperçu) -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-900">Mes Produits</h2>
          <router-link
            to="/merchant/products"
            class="text-[#0099cc] hover:text-[#0088bb] font-medium text-sm flex items-center"
          >
            Voir tout
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </router-link>
        </div>

        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0099cc]"></div>
        </div>

        <div v-else-if="products.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun produit</h3>
          <p class="text-gray-600 mb-4">Commencez par créer votre premier produit</p>
          <button
            @click="showCreateProductModal = true"
            class="px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-lg font-medium transition-colors"
          >
            Créer mon premier produit
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="product in products.slice(0, 6)"
            :key="product.id"
            class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2">{{ product.name || product.title }}</h3>
                <p class="text-sm text-gray-600 mb-2">{{ formatAmount(product.price) }} FCFA</p>
                <span :class="getStatusClass(product.status)">
                  {{ getStatusLabel(product.status) }}
                </span>
              </div>
              <div class="w-16 h-16 bg-gray-200 rounded-lg flex-shrink-0 ml-4">
                <img
                  v-if="product.main_image || product.image"
                  :src="product.main_image || product.image"
                  :alt="product.name"
                  class="w-full h-full object-cover rounded-lg"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-500">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Progression pour les tombolas -->
            <div v-if="product.sale_mode === 'lottery'" class="mb-4">
              <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Progression</span>
                <span>{{ product.sold_tickets || 0 }}/500</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-purple-500 h-2 rounded-full transition-all"
                  :style="{ width: Math.min(((product.sold_tickets || 0) / 500) * 100, 100) + '%' }"
                ></div>
              </div>
              <p class="text-xs text-gray-500 mt-1">
                {{ Math.round(((product.sold_tickets || 0) / 500) * 100) }}% vendu
              </p>
            </div>

            <div class="text-sm text-gray-600">
              <p><strong>Revenus:</strong> {{ formatAmount(product.revenue || 0) }} FCFA</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Commandes récentes -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-900">Commandes Récentes</h2>
          <router-link
            to="/merchant/orders"
            class="text-[#0099cc] hover:text-[#0088bb] font-medium text-sm flex items-center"
          >
            Voir toutes
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </router-link>
        </div>

        <div v-if="recentOrders.length === 0" class="text-center py-8">
          <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
          </svg>
          <p class="text-gray-600">Aucune commande récente</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="order in recentOrders.slice(0, 5)"
            :key="order.id"
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <div class="flex items-center space-x-4">
              <div class="w-10 h-10 bg-[#0099cc] rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
              </div>
              <div>
                <p class="font-medium text-gray-900">{{ order.product_name || 'Produit inconnu' }}</p>
                <p class="text-sm text-gray-600">{{ order.customer_name || 'Client inconnu' }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(order.created_at) }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-semibold text-gray-900">{{ formatAmount(order.amount || 0) }} FCFA</p>
              <span :class="getOrderStatusClass(order.status)">
                {{ getOrderStatusLabel(order.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de création de produit simplifié -->
    <div v-if="showCreateProductModal" class="fixed inset-0 bg-black/40 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-8 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-2xl bg-white max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold text-gray-900">Créer un Nouveau Produit</h3>
          <button @click="showCreateProductModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form @submit.prevent="createProduct" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nom du produit *
            </label>
            <input
              v-model="newProduct.name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
              style="color: #5f5f5f"
              placeholder="Ex: iPhone 15 Pro Max 256GB"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Description *
            </label>
            <textarea
              v-model="newProduct.description"
              rows="3"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
              style="color: #5f5f5f"
              placeholder="Décrivez votre produit..."
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Valeur du produit (FCFA) *
              </label>
              <input
                v-model="newProduct.price"
                type="number"
                required
                min="100000"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
                style="color: #5f5f5f"
                placeholder="100000"
              />
              <p class="text-xs text-gray-500 mt-1">Minimum recommandé: 100,000 FCFA</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Localisation *
              </label>
              <input
                v-model="newProduct.location"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
                style="color: #5f5f5f"
                placeholder="Libreville, Gabon"
              />
            </div>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-start space-x-3">
              <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div class="text-sm text-blue-800">
                <h4 class="font-semibold mb-2">Configuration automatique pour vendeur individuel :</h4>
                <ul class="space-y-1">
                  <li>• <strong>Mode:</strong> Tombola automatique</li>
                  <li>• <strong>Nombre de tickets:</strong> 500 (fixe)</li>
                  <li>• <strong>Prix par ticket:</strong> {{ formatAmount(Math.ceil((newProduct.price || 100000) / 500)) }} FCFA</li>
                  <li>• <strong>Revenus potentiels:</strong> {{ formatAmount((newProduct.price || 100000)) }} FCFA</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-4 pt-6 border-t">
            <button
              @click="showCreateProductModal = false"
              type="button"
              class="px-6 py-3 bg-gray-200 text-gray-800 rounded-xl hover:bg-gray-300 font-medium transition-colors"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="createProductLoading"
              class="px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-xl font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
            >
              <span v-if="createProductLoading" class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></div>
                Création...
              </span>
              <span v-else>Créer le produit</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/api'

const router = useRouter()
const { get, post, loading, error } = useApi()

// State
const stats = ref({
  activeProducts: 0,
  monthlyRevenue: 0,
  totalOrders: 0,
  successRate: 0
})

const products = ref([])
const recentOrders = ref([])
const showCreateProductModal = ref(false)
const createProductLoading = ref(false)

const newProduct = reactive({
  name: '',
  description: '',
  price: '',
  location: ''
})

// Methods
const formatAmount = (amount) => {
  return new Intl.NumberFormat('fr-FR').format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status) => {
  const classes = {
    'draft': 'px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800',
    'active': 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    'completed': 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    'cancelled': 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800'
  }
  return classes[status] || classes.draft
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

const getOrderStatusClass = (status) => {
  const classes = {
    'pending': 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800',
    'paid': 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    'completed': 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    'cancelled': 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800'
  }
  return classes[status] || classes.pending
}

const getOrderStatusLabel = (status) => {
  const labels = {
    'pending': 'En attente',
    'paid': 'Payé',
    'completed': 'Terminé',
    'cancelled': 'Annulé'
  }
  return labels[status] || status
}

// API Functions
const loadStats = async () => {
  try {
    const response = await get('/merchant/dashboard/stats')
    if (response && response.data) {
      stats.value = {
        activeProducts: response.data.active_products || 0,
        monthlyRevenue: response.data.monthly_revenue || 0,
        totalOrders: response.data.total_orders || 0,
        successRate: response.data.success_rate || 0
      }
    }
  } catch (err) {
    console.error('Erreur lors du chargement des statistiques:', err)
  }
}

const loadProducts = async () => {
  try {
    const response = await get('/products?my_products=1&limit=6')
    if (response && response.data && response.data.products && Array.isArray(response.data.products)) {
      products.value = response.data.products.map(product => ({
        ...product,
        price: parseFloat(product.price || 0),
        revenue: parseFloat(product.revenue || 0),
        sold_tickets: parseInt(product.sold_tickets || 0)
      }))
    }
  } catch (err) {
    console.error('Erreur lors du chargement des produits:', err)
  }
}

const loadRecentOrders = async () => {
  try {
    const response = await get('/merchant/dashboard/recent-transactions')
    if (response && response.data && Array.isArray(response.data)) {
      recentOrders.value = response.data.map(order => ({
        id: order.id,
        product_name: order.product?.title || order.product?.name || 'Produit inconnu',
        customer_name: (order.user?.name || 
                       (order.user?.first_name + ' ' + order.user?.last_name) || 
                       'Client inconnu').trim(),
        amount: parseFloat(order.amount || 0),
        status: order.status || 'pending',
        created_at: order.created_at || new Date().toISOString()
      }))
    }
  } catch (err) {
    console.error('Erreur lors du chargement des commandes:', err)
  }
}

const createProduct = async () => {
  createProductLoading.value = true
  
  try {
    // Calculer automatiquement le prix par ticket (toujours 500 tickets pour vendeur individuel)
    const ticketPrice = Math.ceil(parseFloat(newProduct.price) / 500)
    
    const productData = {
      name: newProduct.name,
      description: newProduct.description,
      price: parseFloat(newProduct.price),
      location: newProduct.location,
      sale_mode: 'lottery',
      ticket_price: ticketPrice,
      total_tickets: 500, // Fixe pour vendeur individuel
      min_participants: 250, // 50% des tickets minimum
      category_id: 1, // Catégorie par défaut
      condition: 'new' // Par défaut
    }

    const response = await post('/products', productData)
    
    if (response && response.product) {
      // Créer automatiquement la tombola
      try {
        await post(`/products/${response.product.id}/create-lottery`, {
          duration_days: 7
        })
        
        if (window.$toast) {
          window.$toast.success('Produit et tombola créés avec succès !', '✅ Succès')
        }
        
        // Fermer le modal et recharger les données
        showCreateProductModal.value = false
        resetNewProduct()
        await Promise.all([loadProducts(), loadStats()])
        
      } catch (lotteryError) {
        console.error('Error creating lottery:', lotteryError)
        if (window.$toast) {
          window.$toast.warning('Produit créé mais erreur lors de la création de la tombola', '⚠️ Attention')
        }
      }
    }
    
  } catch (err) {
    console.error('Erreur lors de la création du produit:', err)
    let errorMessage = 'Erreur lors de la création du produit'
    
    if (err.response?.data?.message) {
      errorMessage = err.response.data.message
    } else if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      errorMessage = Object.values(errors).flat().join(', ')
    }
    
    if (window.$toast) {
      window.$toast.error(errorMessage, '❌ Erreur')
    }
  } finally {
    createProductLoading.value = false
  }
}

const resetNewProduct = () => {
  newProduct.name = ''
  newProduct.description = ''
  newProduct.price = ''
  newProduct.location = ''
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadStats(),
    loadProducts(),
    loadRecentOrders()
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