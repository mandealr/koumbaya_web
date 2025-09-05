<template>
  <div class="max-w-4xl mx-auto space-y-6">
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
        <ArrowLeftIcon class="w-4 h-4 mr-2" />
        Retour aux produits
      </router-link>
    </div>

    <!-- Edit Form -->
    <template v-else-if="productLoaded && product">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Modifier le Produit</h1>
          <p class="mt-2 text-gray-600">Modifiez les informations de "{{ product.name }}"</p>
        </div>
        <router-link
          to="/merchant/products"
          class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-2" />
          Retour aux produits
        </router-link>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Step 1: Basic Information -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Informations de base</h2>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="lg:col-span-2">
              <label class="block text-sm font-medium text-gray-800 mb-2">
                Nom du produit *
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900"
                placeholder="Ex: iPhone 15 Pro Max 256GB"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div class="lg:col-span-2">
              <label class="block text-sm font-medium text-gray-800 mb-2">
                Description *
              </label>
              <textarea
                v-model="form.description"
                rows="4"
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900"
                placeholder="Décrivez votre produit en détail..."
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-800 mb-2">
                Catégorie *
              </label>
              <select
                v-model="form.category_id"
                required
                :disabled="apiLoading"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all disabled:opacity-50 text-gray-900"
              >
                <option value="">Sélectionner une catégorie</option>
                <option v-if="apiLoading" disabled>Chargement des catégories...</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
              <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-800 mb-2">
                Condition *
              </label>
              <select
                v-model="form.condition"
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900"
              >
                <option value="">État du produit</option>
                <option value="new">Neuf</option>
                <option value="used">Occasion</option>
                <option value="refurbished">Reconditionné</option>
              </select>
              <p v-if="errors.condition" class="mt-1 text-sm text-red-600">{{ errors.condition }}</p>
            </div>

            <div class="lg:col-span-2">
              <label class="block text-sm font-medium text-gray-800 mb-2">
                Valeur du produit (FCFA) *
              </label>
              <input
                v-model="form.price"
                type="number"
                required
                min="1000"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900"
                placeholder="Ex: 850000"
              />
              <p v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
              <p v-else-if="form.price && parseFloat(form.price) < 1000" class="mt-1 text-sm text-orange-600">Le prix minimum est de 1000 FCFA</p>
              <p v-else class="text-sm text-gray-700 mt-1">Prix de vente ou valeur estimée du produit</p>
            </div>

            <div v-if="product.sale_mode === 'lottery'">
              <label class="block text-sm font-medium text-gray-800 mb-2">
                Prix par ticket (FCFA) *
              </label>
              <input
                v-model="form.ticket_price"
                type="number"
                required
                min="100"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900"
                placeholder="Ex: 2500"
              />
              <p v-if="errors.ticket_price" class="mt-1 text-sm text-red-600">{{ errors.ticket_price }}</p>
              <p v-else class="text-sm text-gray-700 mt-1">Prix que paieront les participants</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-800 mb-2">
                Localisation
              </label>
              <input
                v-model="form.location"
                type="text"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-gray-900"
                placeholder="Ex: Abidjan, Cocody"
              />
              <p v-if="errors.location" class="mt-1 text-sm text-red-600">{{ errors.location }}</p>
            </div>
          </div>
        </div>

        <!-- Step 2: Images -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Images du produit</h2>

          <!-- Existing Images -->
          <div v-if="existingImages.length > 0" class="mb-6">
            <h3 class="text-sm font-medium text-gray-800 mb-3">Images actuelles</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div
                v-for="(image, index) in existingImages"
                :key="`existing-${index}`"
                class="relative group"
              >
                <img
                  :src="image.startsWith('data:') ? image : image"
                  :alt="`Image ${index + 1}`"
                  class="w-full h-32 object-cover rounded-xl border border-gray-200"
                />
                <button
                  @click="removeExistingImage(index)"
                  type="button"
                  class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Upload New Images -->
          <div class="text-center">
            <div
              @click="triggerFileInput"
              @dragover.prevent
              @drop.prevent="handleFileDrop"
              class="border-2 border-dashed border-gray-300 rounded-xl p-8 hover:border-[#0099cc] transition-colors cursor-pointer"
            >
              <CameraIcon class="w-16 h-16 text-gray-600 mx-auto mb-4" />
              <p class="text-lg font-medium text-gray-700 mb-2">Ajouter de nouvelles photos</p>
              <p class="text-sm text-gray-700 mb-4">Glissez-déposez ou cliquez pour sélectionner</p>
              <div class="flex justify-center">
                <span class="bg-[#0099cc] text-white px-6 py-2 rounded-lg font-medium hover:bg-[#0088bb] transition-colors">
                  Choisir des fichiers
                </span>
              </div>
            </div>
            <input
              ref="fileInput"
              type="file"
              multiple
              accept="image/jpeg,image/jpg,image/png,image/webp"
              @change="handleFileSelect"
              class="hidden"
            />
          </div>

          <!-- New Image Preview -->
          <div v-if="form.images.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
            <div
              v-for="(image, index) in form.images"
              :key="`new-${index}`"
              class="relative group"
            >
              <img
                :src="image.preview"
                :alt="`Nouvelle image ${index + 1}`"
                class="w-full h-32 object-cover rounded-xl border border-gray-200"
              />
              <button
                @click="removeImage(index)"
                type="button"
                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mt-6">
            <div class="flex items-start space-x-3">
              <InformationCircleIcon class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" />
              <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">Conseils pour de bonnes photos :</p>
                <ul class="space-y-1 text-sm">
                  <li>• Les images existantes seront conservées si vous n'ajoutez pas de nouvelles images</li>
                  <li>• Utilisez un bon éclairage naturel</li>
                  <li>• Prenez plusieurs angles du produit</li>
                  <li>• Maximum 10 images par produit</li>
                  <li>• Formats acceptés : JPG, PNG, WebP</li>
                  <li>• Taille maximum : 5MB par image</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-between items-center bg-white rounded-xl shadow-lg border border-gray-100 p-6">
          <router-link
            to="/merchant/products"
            class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
          >
            <ArrowLeftIcon class="w-4 h-4 mr-2" />
            Annuler
          </router-link>

          <button
            type="submit"
            :disabled="loading || !canSubmit || apiLoading"
            class="inline-flex items-center px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
          >
            <span v-if="loading || apiLoading" class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></div>
              Modification...
            </span>
            <span v-else class="flex items-center">
              <CheckIcon class="w-4 h-4 mr-2" />
              Enregistrer les modifications
            </span>
          </button>
        </div>
      </form>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useApi } from '@/composables/api'
import {
  ArrowLeftIcon,
  CameraIcon,
  XMarkIcon,
  InformationCircleIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const { get, put, loading: apiLoading } = useApi()

// Props from route
const productId = computed(() => route.params.id)

// State
const loading = ref(false)
const loadingProduct = ref(true)
const productLoaded = ref(false)
const productError = ref('')
const product = ref(null)
const fileInput = ref(null)

const categories = ref([])
const existingImages = ref([])

const form = reactive({
  name: '',
  description: '',
  category_id: '',
  condition: '',
  sale_mode: 'direct',
  price: '',
  ticket_price: '',
  location: '',
  images: []
})

const errors = reactive({
  name: '',
  description: '',
  category_id: '',
  condition: '',
  price: '',
  ticket_price: '',
  location: '',
  images: ''
})

// Computed
const canSubmit = computed(() => {
  return form.name && 
         form.description && 
         form.category_id && 
         form.condition && 
         form.price && 
         parseFloat(form.price) >= 1000 &&
         (!product.value || product.value.sale_mode !== 'lottery' || (form.ticket_price && parseFloat(form.ticket_price) >= 100))
})

// Methods
const loadProduct = async () => {
  try {
    loadingProduct.value = true
    productError.value = ''
    
    // Charger la liste des produits du marchand pour vérifier l'existence et la propriété
    const productsResponse = await get(`/products?my_products=1`)
    
    if (productsResponse && productsResponse.data && productsResponse.data.products) {
      const merchantProduct = productsResponse.data.products.find(p => p.id.toString() === productId.value.toString())
      
      if (merchantProduct) {
        product.value = merchantProduct
        
        // Fill form with product data
        form.name = merchantProduct.name || ''
        form.description = merchantProduct.description || ''
        form.category_id = merchantProduct.category_id || ''
        form.condition = merchantProduct.condition || ''
        form.sale_mode = merchantProduct.sale_mode || 'direct'
        form.price = merchantProduct.price || ''
        form.ticket_price = merchantProduct.ticket_price || ''
        form.location = merchantProduct.location || ''
        
        // Load existing images
        if (merchantProduct.images && Array.isArray(merchantProduct.images)) {
          existingImages.value = [...merchantProduct.images]
        } else if (merchantProduct.main_image) {
          existingImages.value = [merchantProduct.main_image]
        } else if (merchantProduct.image_url) {
          existingImages.value = [merchantProduct.image_url]
        }
        
        productLoaded.value = true
      } else {
        productError.value = 'Ce produit n\'existe pas ou ne vous appartient pas'
      }
    } else {
      productError.value = 'Erreur lors du chargement des produits'
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

const loadCategories = async () => {
  try {
    const response = await get('/categories')
    if (response.categories) {
      categories.value = response.categories
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files || [])
  handleFiles(files)
}

const handleFileDrop = (event) => {
  const files = Array.from(event.dataTransfer.files || [])
  handleFiles(files)
}

const handleFiles = (files) => {
  const validFiles = files.filter(file => {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      console.error(`Le fichier ${file.name} n'est pas une image valide`)
      return false
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      console.error(`Le fichier ${file.name} est trop volumineux (max 5MB)`)
      return false
    }
    
    return true
  })

  const totalImages = existingImages.value.length + form.images.length + validFiles.length
  if (totalImages > 10) {
    console.error('Maximum 10 images autorisées')
    return
  }

  validFiles.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => {
      form.images.push({
        file: file,
        preview: e.target?.result,
        name: file.name,
        size: file.size,
        type: file.type
      })
    }
    reader.onerror = () => {
      console.error(`Erreur lors de la lecture du fichier ${file.name}`)
    }
    reader.readAsDataURL(file)
  })

  // Clear the file input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeImage = (index) => {
  form.images.splice(index, 1)
}

const removeExistingImage = (index) => {
  existingImages.value.splice(index, 1)
}

const handleSubmit = async () => {
  loading.value = true
  clearErrors()

  try {
    // Prepare product data
    const productData = {
      name: form.name,
      description: form.description,
      category_id: parseInt(form.category_id),
      price: parseFloat(form.price),
      condition: form.condition,
      location: form.location
    }

    // Add ticket_price for lottery products
    if (product.value.sale_mode === 'lottery' && form.ticket_price) {
      productData.ticket_price = parseFloat(form.ticket_price)
    }

    // Add images (combine existing and new ones)
    const allImages = [
      ...existingImages.value,
      ...form.images.map(img => img.preview)
    ]
    
    if (allImages.length > 0) {
      productData.images = allImages
    }

    // Update product
    const response = await put(`/products/${productId.value}`, productData)
    
    if (response && (response.product || response.success)) {
      if (window.$toast) {
        window.$toast.success('Produit modifié avec succès !', '✅ Succès')
      }
      
      // Redirect to products list
      setTimeout(() => {
        router.push('/merchant/products')
      }, 1500)
    }

  } catch (error) {
    console.error('Error updating product:', error)
    handleApiError(error)
  } finally {
    loading.value = false
  }
}

// Helper functions
const clearErrors = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}

const handleApiError = (error) => {
  if (error.response?.data?.errors) {
    // Validation errors
    const validationErrors = error.response.data.errors
    Object.keys(validationErrors).forEach(key => {
      if (errors[key] !== undefined) {
        errors[key] = validationErrors[key][0]
      }
    })
    showErrorToast('Veuillez corriger les erreurs dans le formulaire')
  } else if (error.response?.data?.message) {
    showErrorToast(error.response.data.message)
  } else {
    showErrorToast('Une erreur est survenue. Veuillez réessayer.')
  }
}

const showErrorToast = (message) => {
  if (window.$toast) {
    window.$toast.error(message, '❌ Erreur')
  }
}

// Load data on mount
onMounted(async () => {
  await Promise.all([
    loadProduct(),
    loadCategories()
  ])
})
</script>