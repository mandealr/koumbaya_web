<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Debug Info -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
      <h3 class="font-medium text-yellow-800">Debug Info</h3>
      <p class="text-sm text-yellow-700">Product ID: {{ productId }}</p>
      <p class="text-sm text-yellow-700">Loading Product: {{ loadingProduct }}</p>
      <p class="text-sm text-yellow-700">Product Loaded: {{ productLoaded }}</p>
      <p class="text-sm text-yellow-700">Error: {{ productError }}</p>
      <p class="text-sm text-yellow-700">API Loading: {{ apiLoading }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="loadingProduct" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#0099cc]"></div>
      <span class="ml-3 text-gray-600">Chargement du produit...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="productError" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
      <h3 class="text-lg font-medium text-red-800 mb-2">Erreur</h3>
      <p class="text-red-600">{{ productError }}</p>
      <router-link
        to="/merchant/products"
        class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors mt-4"
      >
        Retour aux produits
      </router-link>
    </div>

    <!-- Success State -->
    <div v-else class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-4">Édition du Produit</h1>
      
      <div v-if="product" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">ID</label>
            <p class="text-gray-900">{{ product.id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom</label>
            <p class="text-gray-900">{{ product.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Prix</label>
            <p class="text-gray-900">{{ product.price }} FCFA</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Statut</label>
            <p class="text-gray-900">{{ product.status }}</p>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">{{ product.description }}</p>
        </div>

        <div class="mt-6">
          <router-link
            to="/merchant/products"
            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
          >
            Retour aux produits
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useApi } from '@/composables/api'

const router = useRouter()
const route = useRoute()
const { get, loading: apiLoading } = useApi()

// Props from route
const productId = computed(() => route.params.id)

// State
const loadingProduct = ref(true)
const productLoaded = ref(false)
const productError = ref('')
const product = ref(null)

const loadProduct = async () => {
  try {
    console.log('Loading product with ID:', productId.value)
    loadingProduct.value = true
    productError.value = ''
    
    // Charger d'abord la liste des produits du marchand pour vérifier l'existence et la propriété
    const productsResponse = await get(`/products?my_products=1`)
    console.log('Merchant products response:', productsResponse)
    
    if (productsResponse && productsResponse.data && productsResponse.data.products) {
      const merchantProduct = productsResponse.data.products.find(p => p.id.toString() === productId.value.toString())
      
      if (merchantProduct) {
        product.value = merchantProduct
        productLoaded.value = true
        console.log('Product loaded:', product.value)
      } else {
        productError.value = 'Ce produit n\'existe pas ou ne vous appartient pas'
        console.error('Product not found in merchant products')
      }
    } else {
      productError.value = 'Erreur lors du chargement des produits'
      console.error('Invalid merchant products response:', productsResponse)
    }
  } catch (error) {
    console.error('Error loading product:', error)
    if (error.response?.status === 404) {
      productError.value = 'Produit non trouvé'
    } else if (error.response?.status === 403) {
      productError.value = 'Vous n\'êtes pas autorisé à modifier ce produit'
    } else {
      productError.value = `Erreur lors du chargement du produit: ${error.message}`
    }
  } finally {
    loadingProduct.value = false
  }
}

// Load data on mount
onMounted(() => {
  console.log('Component mounted, loading product...')
  loadProduct()
})
</script>