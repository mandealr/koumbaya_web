<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="loading" class="animate-pulse">
      <div class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="h-96 bg-gray-200 rounded-2xl"></div>
            <div class="space-y-4">
              <div class="h-8 bg-gray-200 rounded"></div>
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-20 bg-gray-200 rounded"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="product" class="bg-white">
      <!-- Breadcrumb -->
      <div class="bg-gray-50 border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <nav class="flex items-center space-x-2 text-sm">
            <router-link to="/" class="text-gray-500 hover:text-gray-700">Accueil</router-link>
            <span class="text-gray-400">/</span>
            <router-link to="/products" class="text-gray-500 hover:text-gray-700">Produits</router-link>
            <span class="text-gray-400">/</span>
            <span class="text-gray-900 font-medium">{{ product.name }}</span>
          </nav>
        </div>
      </div>

      <!-- Product Details -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <!-- Product Images -->
          <div class="space-y-4">
            <div class="relative overflow-hidden rounded-2xl bg-gray-100">
              <ProductImage
                :src="product.image_url || product.main_image || product.image"
                :alt="product.name"
                container-class="w-full h-96 lg:h-[500px]"
                image-class="w-full h-full object-cover"
              />
              <div v-if="hasActiveLottery" class="absolute top-4 right-4">
                <span class="bg-[#0099cc] text-white px-4 py-2 rounded-full font-semibold">
                  {{ formatPrice(product.ticketPrice) }} / ticket
                </span>
              </div>
              <div v-else class="absolute top-4 right-4">
                <span class="bg-green-600 text-white px-4 py-2 rounded-full font-semibold">
                  Achat direct
                </span>
              </div>
              <div v-if="product.isNew" class="absolute top-4 left-4">
                <span class="bg-[#0099cc] text-white px-4 py-2 rounded-full font-semibold">
                  Nouveau
                </span>
              </div>
            </div>

            <!-- Thumbnail Gallery -->
            <div v-if="product.images && product.images.length > 1" class="grid grid-cols-4 gap-4">
              <div v-for="(image, index) in product.images.slice(0, 4)" :key="index" class="relative overflow-hidden rounded-xl bg-gray-100 cursor-pointer hover:ring-2 hover:ring-[#0099cc] transition-all">
                <ProductImage
                  :src="image"
                  :alt="`${product.name} - Vue ${index + 1}`"
                  container-class="w-full h-20"
                  image-class="w-full h-full object-cover"
                />
              </div>
            </div>
          </div>

          <!-- Product Info -->
          <div class="space-y-8">
            <div>
              <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                {{ product.name }}
              </h1>
              <div class="flex items-center gap-4 mb-6">
                <span class="bg-[#0099cc]/10 text-[#0099cc] px-4 py-2 rounded-full font-semibold">
                  {{ product.category }}
                </span>
                <div class="flex items-center text-yellow-500">
                  <StarIcon v-for="n in 5" :key="n" class="h-5 w-5 fill-current" />
                  <span class="ml-2 text-gray-600">(4.8)</span>
                </div>
              </div>

              <!-- Merchant Info -->
              <div v-if="product.merchant" class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                <div class="w-12 h-12 rounded-full bg-[#0099cc]/10 flex items-center justify-center">
                  <svg v-if="product.merchant.company_name" class="w-6 h-6 text-[#0099cc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <svg v-else class="w-6 h-6 text-[#0099cc]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Vendu par</p>
                  <p class="font-semibold text-gray-900">
                    {{ product.merchant.company_name || `${product.merchant.first_name} ${product.merchant.last_name}` }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Product Value -->
            <div class="bg-gradient-to-r from-[#0099cc]/5 to-[#0099cc]/10 rounded-2xl p-6">
              <div class="text-center">
                <div class="text-sm text-gray-600 mb-2">{{ hasActiveLottery ? 'Valeur du produit' : 'Prix' }}</div>
                <div class="text-4xl font-bold text-[#0099cc] mb-2">
                  {{ formatPrice(product.price) }}
                </div>
                <div v-if="hasActiveLottery" class="text-sm text-gray-600">
                  Prix d'un ticket : <span class="font-semibold text-[#0099cc]">{{ formatPrice(product.ticketPrice) }}</span>
                </div>
                <div v-else class="text-sm text-gray-600">
                  <span class="font-semibold text-green-600">Disponible à l'achat direct</span>
                </div>
              </div>
            </div>

            <!-- Progress - Only for lottery -->
            <div v-if="hasActiveLottery" class="space-y-4">
              <h3 class="text-lg font-semibold text-gray-900">Progression du tirage</h3>
              <div class="space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Tickets vendus</span>
                  <span class="font-semibold">{{ product.soldTickets }} / 1000</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                  <div 
                    class="bg-gradient-to-r from-[#0099cc] to-[#0088bb] h-4 rounded-full transition-all duration-500"
                    :style="{ width: Math.round((product.soldTickets / 1000) * 100) + '%' }"
                  ></div>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-[#0099cc] font-medium">{{ Math.round((product.soldTickets / 1000) * 100) }}% vendu</span>
                  <span class="text-gray-600">{{ 1000 - product.soldTickets }} tickets restants</span>
                </div>
              </div>
            </div>

            <!-- Lottery Info - Only for lottery -->
            <div v-if="hasActiveLottery" class="bg-gray-50 rounded-2xl p-6 space-y-4">
              <h3 class="text-lg font-semibold text-gray-900">Informations du tirage</h3>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Date limite :</span>
                  <p class="font-semibold">{{ formatDate(product.drawDate) }}</p>
                </div>
                <div>
                  <span class="text-gray-600">Participants :</span>
                  <p class="font-semibold">{{ product.participants || 0 }}</p>
                </div>
                <div>
                  <span class="text-gray-600">Chances de gagner :</span>
                  <p class="font-semibold text-[#0099cc]">1 sur 1000</p>
                </div>
                <div>
                  <span class="text-gray-600">Statut :</span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#0099cc]/10 text-[#0099cc]">
                    En cours
                  </span>
                </div>
              </div>
            </div>

            <!-- Direct Purchase Info - Only for direct purchase -->
            <div v-else class="bg-green-50 rounded-2xl p-6 space-y-4">
              <h3 class="text-lg font-semibold text-gray-900">Achat direct</h3>
              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <CheckIcon class="h-5 w-5 text-green-600" />
                  <span class="text-sm text-gray-700">Disponible immédiatement</span>
                </div>
                <div class="flex items-center gap-3">
                  <CheckIcon class="h-5 w-5 text-green-600" />
                  <span class="text-sm text-gray-700">Pas de tirage au sort</span>
                </div>
                <div class="flex items-center gap-3">
                  <CheckIcon class="h-5 w-5 text-green-600" />
                  <span class="text-sm text-gray-700">Livraison garantie</span>
                </div>
                <div class="flex items-center gap-3">
                  <CheckIcon class="h-5 w-5 text-green-600" />
                  <span class="text-sm text-gray-700">Paiement sécurisé</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-4">
              <!-- Lottery Button -->
              <button
                v-if="hasActiveLottery"
                @click="participateNow"
                class="w-full bg-[#0099cc] hover:bg-[#0088bb] text-white font-bold py-4 px-8 rounded-xl text-lg transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
              >
                <TicketIcon class="w-5 h-5 flex-shrink-0" />
                <div class="flex flex-col items-center">
                  <span>{{ authStore.isAuthenticated ? 'Participer maintenant' : 'Se connecter pour participer' }}</span>
                  <span class="text-sm font-medium opacity-90">{{ formatPrice(product.ticketPrice) }}</span>
                </div>
              </button>
              
              <!-- Direct Purchase Button -->
              <button
                v-else
                @click="buyDirectly"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-8 rounded-xl text-lg transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
              >
                <ShoppingCartIcon class="w-5 h-5 flex-shrink-0" />
                <div class="flex flex-col items-center">
                  <span>{{ authStore.isAuthenticated ? 'Acheter maintenant' : 'Se connecter pour acheter' }}</span>
                  <span class="text-sm font-medium opacity-90">{{ formatPrice(product.price) }}</span>
                </div>
              </button>
              
              <div class="grid grid-cols-2 gap-4">
                <button
                  @click="addToWishlist"
                  class="flex items-center justify-center gap-2 border-2 border-gray-200 hover:border-[#0099cc] text-gray-700 hover:text-[#0099cc] py-3 rounded-xl transition-all"
                  :title="authStore.isAuthenticated ? 'Ajouter aux favoris' : 'Se connecter pour ajouter aux favoris'"
                >
                  <HeartIcon class="h-5 w-5" />
                  {{ authStore.isAuthenticated ? 'Favoris' : 'Favoris' }}
                </button>
                <button
                  @click="shareProduct"
                  class="flex items-center justify-center gap-2 border-2 border-gray-200 hover:border-[#0099cc] text-gray-700 hover:text-[#0099cc] py-3 rounded-xl transition-all"
                >
                  <ShareIcon class="h-5 w-5" />
                  Partager
                </button>
              </div>
            </div>

            <!-- Trust Badges -->
            <div class="flex items-center gap-6 pt-6 border-t">
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <ShieldCheckIcon class="h-5 w-5 text-[#0099cc]" />
                <span>Produit garanti</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <TruckIcon class="h-5 w-5 text-[#0099cc]" />
                <span>Livraison gratuite</span>
              </div>
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <CheckBadgeIcon class="h-5 w-5 text-[#0099cc]" />
                <span>Authentique</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Description -->
        <div class="mt-16 grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Description</h2>
            <div class="prose max-w-none text-gray-700">
              <p class="text-lg leading-relaxed mb-6">
                {{ product.description }}
              </p>
              
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Caractéristiques principales</h3>
              <ul class="space-y-2 mb-6">
                <li class="flex items-start gap-3">
                  <CheckIcon class="h-5 w-5 text-[#0099cc] mt-0.5" />
                  <span>Produit neuf avec garantie constructeur</span>
                </li>
                <li class="flex items-start gap-3">
                  <CheckIcon class="h-5 w-5 text-[#0099cc] mt-0.5" />
                  <span>Livraison express partout au Cameroun</span>
                </li>
                <li class="flex items-start gap-3">
                  <CheckIcon class="h-5 w-5 text-[#0099cc] mt-0.5" />
                  <span>Service client disponible 24h/7j</span>
                </li>
                <li class="flex items-start gap-3">
                  <CheckIcon class="h-5 w-5 text-[#0099cc] mt-0.5" />
                  <span>Certification d'authenticité incluse</span>
                </li>
              </ul>

              <h3 class="text-xl font-semibold text-gray-900 mb-4">Comment participer ?</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="text-center p-4 bg-[#0099cc]/5 rounded-xl">
                  <div class="w-12 h-12 bg-[#0099cc] text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-lg">1</div>
                  <h4 class="font-semibold mb-2">Achetez vos tickets</h4>
                  <p class="text-sm text-gray-600">Choisissez le nombre de tickets que vous souhaitez</p>
                </div>
                <div class="text-center p-4 bg-[#0099cc]/5 rounded-xl">
                  <div class="w-12 h-12 bg-[#0099cc] text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-lg">2</div>
                  <h4 class="font-semibold mb-2">Attendez le tirage</h4>
                  <p class="text-sm text-gray-600">Le tirage se fait automatiquement à la date prévue</p>
                </div>
                <div class="text-center p-4 bg-[#0099cc]/5 rounded-xl">
                  <div class="w-12 h-12 bg-[#0099cc] text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold text-lg">3</div>
                  <h4 class="font-semibold mb-2">Récupérez votre lot</h4>
                  <p class="text-sm text-gray-600">Si vous gagnez, nous vous livrons gratuitement</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <div class="bg-gray-50 rounded-2xl p-6">
              <h3 class="font-semibold text-gray-900 mb-4">Statistiques</h3>
              <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Participants uniques</span>
                  <span class="font-medium">{{ product.participants || 0 }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Tickets moyens/personne</span>
                  <span class="font-medium">{{ Math.round(product.soldTickets / Math.max(product.participants || 1, 1)) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Temps restant</span>
                  <span class="font-medium text-[#0099cc]">{{ getTimeRemaining() }}</span>
                </div>
              </div>
            </div>

            <div class="bg-[#0099cc]/5 rounded-2xl p-6">
              <h3 class="font-semibold text-gray-900 mb-4">Pourquoi Koumbaya ?</h3>
              <div class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                  <CheckIcon class="h-4 w-4 text-[#0099cc] mt-0.5" />
                  <span>Tirages transparents et vérifiables</span>
                </div>
                <div class="flex items-start gap-3">
                  <CheckIcon class="h-4 w-4 text-[#0099cc] mt-0.5" />
                  <span>Paiement 100% sécurisé</span>
                </div>
                <div class="flex items-start gap-3">
                  <CheckIcon class="h-4 w-4 text-[#0099cc] mt-0.5" />
                  <span>Support client réactif</span>
                </div>
                <div class="flex items-start gap-3">
                  <CheckIcon class="h-4 w-4 text-[#0099cc] mt-0.5" />
                  <span>Plus de 10 000+ gagnants</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <section class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-8">Produits similaires</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
              v-for="relatedProduct in relatedProducts"
              :key="relatedProduct.id"
              @click="viewProduct(relatedProduct)"
              class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all cursor-pointer"
            >
              <div class="relative overflow-hidden rounded-xl mb-4">
                <ProductImage
                  :src="relatedProduct.image"
                  :alt="relatedProduct.name"
                  container-class="w-full h-40"
                  image-class="w-full h-full object-cover"
                />
                <div class="absolute top-2 right-2">
                  <span class="bg-[#0099cc] text-white px-2 py-1 rounded-full text-xs">
                    {{ formatPrice(relatedProduct.ticketPrice) }}
                  </span>
                </div>
              </div>
              <h3 class="font-semibold text-gray-900 mb-2">{{ relatedProduct.name }}</h3>
              <div class="text-lg font-bold text-[#0099cc]">{{ formatPrice(relatedProduct.price) }}</div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div v-else class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div class="w-32 h-32 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
          <ExclamationCircleIcon class="w-16 h-16 text-gray-400" />
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">
          {{ error ? 'Erreur de chargement' : 'Produit introuvable' }}
        </h3>
        <p class="text-gray-600 mb-6">
          {{ error || 'Ce produit n\'existe pas ou a été supprimé' }}
        </p>
        <div class="space-y-3">
          <button
            @click="loadProduct"
            class="bg-[#0099cc] hover:bg-[#0088bb] text-white px-6 py-3 rounded-xl transition-colors mr-4"
            v-if="error"
          >
            Réessayer
          </button>
          <router-link
            to="/products"
            class="bg-[#0099cc] hover:bg-[#0088bb] text-white px-6 py-3 rounded-xl transition-colors"
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
import { useRoute, useRouter } from 'vue-router'
import ProductImage from '@/components/common/ProductImage.vue'
import { useApi } from '@/composables/api'
import { useAuthStore } from '@/stores/auth'
import placeholderImg from '@/assets/placeholder.jpg'
import {
  StarIcon,
  HeartIcon,
  ShareIcon,
  ShoppingCartIcon,
  TicketIcon,
  ShieldCheckIcon,
  TruckIcon,
  CheckBadgeIcon,
  CheckIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const { get } = useApi()
const authStore = useAuthStore()

const loading = ref(true)
const product = ref(null)
const error = ref(null)

// Computed properties
const hasActiveLottery = computed(() => {
  return product.value && product.value.activeLottery && product.value.activeLottery.id
})

// Produits similaires (chargés depuis l'API)
const relatedProducts = ref([])

// Methods
const formatPrice = (price) => {
  const numPrice = Number(price) || 0
  if (isNaN(numPrice)) return '0 FCFA'
  
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(numPrice).replace('XAF', 'FCFA')
}

const formatDate = (date) => {
  if (!date) return 'Non définie'
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}


const getTimeRemaining = () => {
  if (!product.value?.activeLottery?.draw_date) return 'Non défini'
  
  const drawDate = new Date(product.value.activeLottery.draw_date)
  const now = new Date()
  const timeDiff = drawDate - now
  
  if (timeDiff <= 0) return 'Terminé'
  
  const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24))
  const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  
  if (days > 0) {
    return `${days}j ${hours}h`
  } else {
    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60))
    return `${hours}h ${minutes}min`
  }
}

const participateNow = () => {
  // Vérifier si l'utilisateur est connecté
  if (authStore.isAuthenticated) {
    // Utilisateur connecté - rediriger vers la page produit de son espace approprié
    // Les Business (marchands) ne participent pas aux tombolas, seuls les Particuliers (clients)
    if (authStore.isCustomer) {
      // Espace client pour participer
      router.push({ name: 'customer.product.detail', params: { id: route.params.id } })
    } else if (authStore.isMerchant) {
      // Les marchands ne peuvent pas participer, rediriger vers leur espace pour voir le produit
      router.push({ name: 'merchant.dashboard' })
    } else {
      // Fallback vers espace client
      router.push({ name: 'customer.product.detail', params: { id: route.params.id } })
    }
  } else {
    // Invité - rediriger vers la page de connexion avec redirection de retour
    router.push({ 
      name: 'login', 
      query: { 
        redirect: `/products/${route.params.id}`,
        action: 'participate'
      }
    })
  }
}

const buyDirectly = () => {
  // Vérifier si l'utilisateur est connecté
  if (authStore.isAuthenticated) {
    // Utilisateur connecté - rediriger vers la page produit de son espace approprié
    if (authStore.isCustomer) {
      // Espace client pour acheter directement
      router.push({ name: 'customer.product.detail', params: { id: route.params.id } })
    } else if (authStore.isMerchant) {
      // Les marchands ne peuvent pas acheter, rediriger vers leur espace
      router.push({ name: 'merchant.dashboard' })
    } else {
      // Fallback vers espace client
      router.push({ name: 'customer.product.detail', params: { id: route.params.id } })
    }
  } else {
    // Invité - rediriger vers la page de connexion avec redirection de retour
    router.push({ 
      name: 'login', 
      query: { 
        redirect: `/products/${route.params.id}`,
        action: 'buy'
      }
    })
  }
}

const addToWishlist = () => {
  if (!authStore.isAuthenticated) {
    // Invité - rediriger vers la page de connexion
    router.push({ 
      name: 'login', 
      query: { 
        redirect: `/products/${route.params.id}`,
        action: 'wishlist'
      }
    })
    return
  }
  
  // Utilisateur connecté - ajouter aux favoris
  if (window.$toast) {
    window.$toast.success('Produit ajouté aux favoris !', 'Favoris')
  }
}

const shareProduct = () => {
  if (navigator.share) {
    navigator.share({
      title: product.value.name,
      text: product.value.description,
      url: window.location.href,
    })
  } else {
    // Copy to clipboard fallback
    navigator.clipboard.writeText(window.location.href)
    if (window.$toast) {
      window.$toast.success('Lien copié dans le presse-papier !', 'Partage')
    }
  }
}

const viewProduct = (prod) => {
  router.push({ name: 'public.product.detail', params: { id: prod.id } })
}

const loadRelatedProducts = async (categoryId, currentProductId) => {
  try {
    const response = await get(`/products?category_id=${categoryId}&limit=4`)
    relatedProducts.value = (response.data || response || [])
      .filter(p => p.id !== currentProductId)
      .slice(0, 4)
      .map(p => ({
        id: p.id,
        name: p.name || p.title,
        price: p.price,
        ticketPrice: p.ticket_price || 0,
        image: p.image_url || p.main_image || p.image || placeholderImg
      }))
  } catch (err) {
    console.warn('Erreur lors du chargement des produits similaires:', err)
    // En cas d'erreur, laisser un tableau vide
    relatedProducts.value = []
  }
}

const loadProduct = async () => {
  try {
    loading.value = true
    error.value = null
    const productId = route.params.id
    
    const response = await get(`/products/${productId}`)
    console.log('🔍 Full response:', response)
    console.log('🔍 Response type:', typeof response)
    console.log('🔍 Response data field:', response.data)
    
    // Le composable useApi retourne response.data de axios
    // Donc si l'API retourne {success: true, data: {...}}, on a besoin de response.data
    let productData = response.data || response
    
    console.log('📦 Product data:', productData)
    
    if (!productData) {
      console.error('Product data is null/undefined')
      console.error('Full response:', JSON.stringify(response, null, 2))
      throw new Error('Produit non trouvé dans la réponse API')
    }
    
    // Déterminer le type de produit depuis les données API
    const isLotteryProduct = productData.sale_mode === 'lottery'
    
    // Utiliser les vraies données API
    product.value = {
      id: productData.id,
      name: productData.name || productData.title,
      description: productData.description,
      price: productData.price,
      ticketPrice: productData.ticket_price || 0,
      image: productData.image_url || productData.main_image || productData.image || placeholderImg,
      category: productData.category?.name || productData.category?.slug || 'Non catégorisé',
      soldTickets: productData.active_lottery?.sold_tickets || 0,
      participants: productData.active_lottery?.sold_tickets || 0,
      drawDate: productData.active_lottery?.draw_date || productData.active_lottery?.end_date,
      isNew: productData.created_at ? new Date(productData.created_at) > new Date(Date.now() - 30 * 24 * 60 * 60 * 1000) : false,
      activeLottery: productData.active_lottery || null,
      lotteries: productData.lotteries || []
    }
    
    console.log('Product loaded successfully:', product.value.name)
    console.log('Product data:', JSON.stringify(product.value, null, 2))
    console.log('hasActiveLottery:', hasActiveLottery.value)
    
    // Charger les produits similaires de la même catégorie
    if (productData.category_id) {
      await loadRelatedProducts(productData.category_id, productData.id)
    }
  } catch (err) {
    console.error('Erreur lors du chargement du produit:', err)
    error.value = err.response?.data?.message || 'Erreur lors du chargement du produit'
    product.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProduct()
})
</script>