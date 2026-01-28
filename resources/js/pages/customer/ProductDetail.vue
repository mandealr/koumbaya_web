<template>
  <div v-if="loading" class="text-center py-12">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
    <p class="mt-4 text-gray-600">Chargement du produit...</p>
  </div>

  <div v-else-if="product" class="max-w-7xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
      <router-link
        to="/customer/products"
        class="inline-flex items-center text-gray-600 hover:text-gray-900"
      >
        <ArrowLeftIcon class="w-5 h-5 mr-2" />
        Retour aux produits
      </router-link>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Product Image -->
      <div>
        <div class="relative">
          <ProductImage
            :src="product.image_url || product.main_image || product.image"
            :alt="product.title || product.name"
            container-class="w-full h-96 rounded-xl overflow-hidden"
            image-class="w-full h-96 object-cover"
            fallback-text="Image non disponible"
          />
          <div class="absolute top-4 left-4">
            <span :class="[
              'px-3 py-1 text-sm font-medium rounded-full',
              product.status === 'active' ? 'bg-blue-100 text-blue-800' :
              product.status === 'ending_soon' ? 'bg-yellow-100 text-yellow-800' :
              'bg-gray-100 text-gray-800'
            ]">
              {{ getStatusLabel(product.status) }}
            </span>
          </div>
          <div v-if="product.featured" class="absolute top-4 right-4">
            <StarIcon class="w-8 h-8 text-yellow-500 fill-current" />
          </div>
        </div>

        <!-- Additional Images -->
        <div v-if="product.images && product.images.length > 1" class="grid grid-cols-4 gap-2 mt-4">
          <ProductImage
            v-for="(image, index) in product.images"
            :key="index"
            :src="image"
            :alt="`${product.title || product.name} ${index + 1}`"
            container-class="w-full h-20 rounded-lg overflow-hidden cursor-pointer hover:opacity-75 transition-opacity"
            image-class="w-full h-20 object-cover"
            @click="currentImage = image"
          />
        </div>
      </div>

      <!-- Product Info -->
      <div>
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.title || product.name }}</h1>
          <p class="text-lg text-gray-600 mb-4">{{ product.description }}</p>

          <!-- Merchant Info -->
          <div v-if="product.merchant" class="p-4 bg-gray-50 rounded-xl mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-600">Vendu par</p>
                <p class="font-semibold text-gray-900">{{ getMerchantName(product.merchant) }}</p>
              </div>
              <MerchantRatingBadge v-if="merchantRating?.badge" :badge="merchantRating.badge" />
            </div>
            <div v-if="merchantRating" class="mt-3 pt-3 border-t border-gray-200">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <RatingStars :rating="merchantRating.avg_rating || 0" size="sm" :show-value="true" />
                  <span class="text-sm text-gray-500">({{ merchantRating.total_reviews || 0 }} avis)</span>
                </div>
                <span class="text-xs text-gray-500">{{ merchantRating.completed_sales || 0 }} ventes</span>
              </div>
            </div>
            <div v-else-if="merchantRatingLoading" class="mt-3 pt-3 border-t border-gray-200">
              <div class="animate-pulse flex items-center gap-2">
                <div class="h-4 bg-gray-200 rounded w-24"></div>
                <div class="h-4 bg-gray-200 rounded w-16"></div>
              </div>
            </div>
          </div>

          <!-- Share Buttons -->
          <div class="flex items-center gap-2 mb-4">
            <span class="text-sm text-gray-600">Partager :</span>
            <button @click="shareOn('whatsapp')" class="p-2 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-lg transition-colors" title="WhatsApp">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </button>
            <button @click="shareOn('facebook')" class="p-2 bg-[#1877F2] hover:bg-[#166fe5] text-white rounded-lg transition-colors" title="Facebook">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </button>
            <button @click="shareOn('twitter')" class="p-2 bg-black hover:bg-gray-800 text-white rounded-lg transition-colors" title="X">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </button>
            <button @click="shareOn('copy')" class="p-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors" :title="linkCopied ? 'Copié !' : 'Copier le lien'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
            </button>
            <span v-if="linkCopied" class="text-sm text-green-600 ml-2">Copié !</span>
          </div>

          <div class="flex items-center space-x-4 mb-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-blue-600">{{ formatPrice(product.price) }} FCFA</div>
              <div class="text-sm text-gray-500">{{ hasActiveLottery ? 'Valeur du produit' : 'Prix' }}</div>
            </div>
            <div v-if="hasActiveLottery" class="text-center">
              <div class="text-xl font-semibold text-gray-900">{{ formatPrice((product.lottery?.ticket_price || product.active_lottery?.ticket_price || product.ticket_price || 0)) }} FCFA</div>
              <div class="text-sm text-gray-500">Prix par ticket</div>
            </div>
          </div>

          <!-- Progress Bar - Only for lottery -->
          <div v-if="hasActiveLottery" class="mb-6">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
              <span>Progression de la tombola</span>
              <span>{{ calculateProgress() }}% ({{ (product.lottery?.sold_tickets || product.active_lottery?.sold_tickets || 0) }}/{{ (product.lottery?.total_tickets || product.active_lottery?.total_tickets || 0) }})</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div
                class="bg-blue-600 h-3 rounded-full transition-all duration-300"
                :style="{ width: calculateProgress() + '%' }"
              ></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
              <span>0 tickets</span>
              <span>{{ (product.lottery?.total_tickets || product.active_lottery?.total_tickets || 0) }} tickets</span>
            </div>
          </div>

          <!-- Time Remaining - Only for lottery -->
          <div v-if="hasActiveLottery" class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center mb-2">
              <CalendarIcon class="w-5 h-5 text-gray-600 mr-2" />
              <span class="font-medium text-gray-900">Date de tirage: {{ formatDate(product.lottery?.draw_date || product.active_lottery?.draw_date) }}</span>
            </div>
            <div class="flex items-center">
              <ClockIcon class="w-5 h-5 text-gray-600 mr-2" />
              <span class="text-gray-600">{{ getRemainingTime(product.lottery?.draw_date || product.active_lottery?.draw_date) }}</span>
            </div>
          </div>
        </div>

        <!-- Purchase Section -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
          <!-- Lottery Purchase -->
          <div v-if="hasActiveLottery">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acheter des tickets</h3>

            <div class="flex items-center space-x-4 mb-4">
              <label class="block text-sm font-medium text-gray-700">Nombre de tickets:</label>
              <div class="flex items-center space-x-2">
                <button
                  @click="decreaseTickets"
                  class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                  :disabled="ticketQuantity <= 1"
                >
                  <MinusIcon class="w-4 h-4" />
                </button>
                <input
                  v-model="ticketQuantity"
                  type="number"
                  min="1"
                  :max="((product.lottery?.total_tickets || product.active_lottery?.total_tickets || 0) - (product.lottery?.sold_tickets || product.active_lottery?.sold_tickets || 0))"
                  class="w-20 text-center border border-gray-300 rounded-md py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                  @click="increaseTickets"
                  class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                  :disabled="ticketQuantity >= ((product.lottery?.total_tickets || 0) - (product.lottery?.sold_tickets || 0))"
                >
                  <PlusIcon class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div class="flex justify-between items-center mb-4">
              <span class="text-gray-600">Total à payer:</span>
              <span class="text-2xl font-bold text-blue-600">{{ formatPrice(totalPrice) }} FCFA</span>
            </div>

            <button
              @click="purchaseTickets"
              :disabled="purchasing || product.status !== 'active' || isLotterySoldOut"
              class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              <span v-if="purchasing">Achat en cours...</span>
              <span v-else-if="product.status !== 'active'">Tombola terminée</span>
              <span v-else-if="isLotterySoldOut">Tous les tickets sont vendus</span>
              <span v-else>Acheter {{ ticketQuantity }} ticket{{ ticketQuantity > 1 ? 's' : '' }}</span>
            </button>
          </div>

          <!-- Direct Purchase -->
          <div v-else>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acheter ce produit</h3>

            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
              <div class="flex items-center gap-2 mb-2">
                <CheckCircleIcon class="w-5 h-5 text-[#0099cc]" />
                <span class="font-medium text-blue-900">Achat direct disponible</span>
              </div>
              <p class="text-sm text-blue-700">
                Ce produit peut être acheté directement sans passer par une tombola.
              </p>
            </div>

            <div class="flex justify-between items-center mb-6">
              <span class="text-gray-600 text-lg">Prix:</span>
              <span class="text-3xl font-bold text-blue-600">{{ product.price }} FCFA</span>
            </div>

            <button
              @click="purchaseDirectly"
              :disabled="purchasing"
              class="w-full bg-[#0099cc] text-white py-3 rounded-lg font-medium hover:bg-[#0088bb] transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center gap-2 whitespace-nowrap"
            >
              <span v-if="purchasing">Achat en cours...</span>
              <template v-else>
                <ShoppingCartIcon class="w-5 h-5 flex-shrink-0" />
                <span>Acheter maintenant</span>
              </template>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="mb-8">
      <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.key
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <div v-if="activeTab === 'description'" class="prose max-w-none">
        <h3 class="text-lg font-semibold mb-3">Description détaillée</h3>
        <p class="text-gray-600 leading-relaxed">{{ product.detailed_description || product.description }}</p>

        <h4 class="text-md font-semibold mt-6 mb-3">Caractéristiques</h4>
        <ul v-if="product.features" class="space-y-2">
          <li v-for="feature in product.features" :key="feature" class="flex items-center gap-2">
            <CheckCircleIcon class="w-5 h-5 text-blue-500 flex-shrink-0" />
            <span class="text-gray-600">{{ feature }}</span>
          </li>
        </ul>
      </div>

      <div v-if="activeTab === 'rules'" class="prose max-w-none">
        <h3 class="text-lg font-semibold mb-3">Règles de la tombola</h3>
        <div class="space-y-4 text-gray-600">
          <p>• Chaque ticket acheté vous donne une chance de gagner ce produit</p>
          <p>• Le tirage aura lieu à la date indiquée une fois tous les tickets vendus</p>
          <p>• Le gagnant sera choisi de manière aléatoire et équitable</p>
          <p>• Vous serez notifié par email et SMS si vous gagnez</p>
          <p>• Les prix non réclamés dans les 30 jours seront remis en jeu</p>
        </div>
      </div>

      <div v-if="activeTab === 'participants'">
        <h3 class="text-lg font-semibold mb-3">Participants ({{ product.sold_tickets }})</h3>
        <div v-if="participants.length === 0" class="text-center py-8">
          <UsersIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <p class="text-gray-600">Soyez le premier à participer !</p>
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="participant in participants"
            :key="participant.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center">
              <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                {{ participant.name.charAt(0).toUpperCase() }}
              </div>
              <span class="ml-3 text-gray-900">{{ participant.name }}</span>
            </div>
            <span class="text-sm text-gray-600">{{ participant.tickets }} ticket{{ participant.tickets > 1 ? 's' : '' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="text-center py-12">
    <ExclamationTriangleIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
    <h3 class="text-lg font-medium text-gray-900 mb-2">Produit introuvable</h3>
    <p class="text-gray-600 mb-4">Le produit que vous cherchez n'existe pas ou a été supprimé</p>
    <router-link
      to="/customer/products"
      class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors"
    >
      Retour aux produits
    </router-link>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { useAuthStore } from '@/stores/auth'
import ProductImage from '@/components/common/ProductImage.vue'
import {
  ArrowLeftIcon,
  StarIcon,
  CalendarIcon,
  ClockIcon,
  ShoppingCartIcon,
  MinusIcon,
  PlusIcon,
  CheckCircleIcon,
  UsersIcon,
  ExclamationTriangleIcon,
  ShareIcon
} from '@heroicons/vue/24/outline'
import RatingStars from '@/components/rating/RatingStars.vue'
import MerchantRatingBadge from '@/components/rating/MerchantRatingBadge.vue'
import { useMerchantRating } from '@/composables/useMerchantRating'

const route = useRoute()
const router = useRouter()
const { get, post, loading, error } = useApi()
const authStore = useAuthStore()

const purchasing = ref(false)
const product = ref(null)
const ticketQuantity = ref(1)
const activeTab = ref('description')
const currentImage = ref('')
const participants = ref([])

// Merchant rating
const { rating: merchantRating, fetchRatingSummary } = useMerchantRating()
const merchantRatingLoading = ref(false)

// Share menu
const showShareMenu = ref(false)
const linkCopied = ref(false)

const tabs = [
  { key: 'description', label: 'Description' },
  { key: 'rules', label: 'Règles' },
  { key: 'participants', label: 'Participants' }
]

const hasActiveLottery = computed(() => {
  return product.value && (product.value.lottery || product.value.active_lottery) &&
         (product.value.lottery?.total_tickets > 0 || product.value.active_lottery?.total_tickets > 0)
})

const totalPrice = computed(() => {
  if (!product.value) return 0
  const lottery = product.value.lottery || product.value.active_lottery
  if (!lottery) return 0
  return (lottery.ticket_price || product.value.ticket_price || 0) * ticketQuantity.value
})

const calculateProgress = () => {
  if (!product.value) return 0
  const lottery = product.value.lottery || product.value.active_lottery
  if (!lottery) return 0
  const sold = lottery.sold_tickets || 0
  const total = lottery.total_tickets || 1
  return Math.round((sold / total) * 100)
}

const isLotterySoldOut = computed(() => {
  if (!product.value) return false
  const lottery = product.value.lottery || product.value.active_lottery
  if (!lottery) return false
  const sold = lottery.sold_tickets || 0
  const total = lottery.total_tickets || lottery.max_tickets || 0
  return sold >= total
})

const formatPrice = (price) => {
  if (price === null || price === undefined || isNaN(price)) {
    return '0'
  }
  return new Intl.NumberFormat('fr-FR').format(Number(price))
}

const increaseTickets = () => {
  const lottery = product.value.lottery || product.value.active_lottery
  const maxTickets = (lottery?.total_tickets || 0) - (lottery?.sold_tickets || 0)
  if (ticketQuantity.value < maxTickets) {
    ticketQuantity.value++
  }
}

const decreaseTickets = () => {
  if (ticketQuantity.value > 1) {
    ticketQuantity.value--
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const getRemainingTime = (dateString) => {
  if (!dateString) return ''
  const now = new Date()
  const drawDate = new Date(dateString)
  const diff = drawDate - now
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))

  if (days > 7) {
    return `Plus de ${days} jours restants`
  } else if (days > 0) {
    return `${days} jour${days > 1 ? 's' : ''} restant${days > 1 ? 's' : ''}`
  } else {
    const hours = Math.floor(diff / (1000 * 60 * 60))
    return hours > 0 ? `${hours}h restantes` : 'Se termine bientôt'
  }
}


const getStatusLabel = (status) => {
  const labels = {
    'active': 'Actif',
    'ending_soon': 'Bientôt terminé',
    'completed': 'Terminé'
  }
  return labels[status] || status
}

// Share functions
const getShareUrl = () => window.location.href

const shareOn = (platform) => {
  const shareUrl = encodeURIComponent(getShareUrl())
  const text = encodeURIComponent(`Découvrez ${product.value.name || product.value.title} sur Koumbaya Marketplace`)

  let url = ''
  switch (platform) {
    case 'whatsapp':
      url = `https://wa.me/?text=${text}%20${shareUrl}`
      break
    case 'facebook':
      url = `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`
      break
    case 'twitter':
      url = `https://twitter.com/intent/tweet?text=${text}&url=${shareUrl}`
      break
    case 'copy':
      navigator.clipboard.writeText(getShareUrl())
      linkCopied.value = true
      setTimeout(() => { linkCopied.value = false }, 2000)
      if (window.$toast) {
        window.$toast.success('Lien copié !', 'Partage')
      }
      return
  }

  if (url) {
    window.open(url, '_blank', 'width=600,height=400')
  }
}

const getMerchantName = (merchant) => {
  if (!merchant) return 'Vendeur'
  if (merchant.company?.company_name) return merchant.company.company_name
  return `${merchant.first_name || ''} ${merchant.last_name || ''}`.trim() || 'Vendeur'
}

const purchaseTickets = async () => {
  purchasing.value = true

  try {
    const lottery = product.value.lottery || product.value.active_lottery
    const ticketPrice = lottery?.ticket_price || product.value.ticket_price || 0
    const totalAmount = ticketPrice * ticketQuantity.value

    // Récupérer le numéro de téléphone de l'utilisateur connecté
    const phoneNumber = authStore.user?.phone

    // Vérifier que l'utilisateur a un numéro de téléphone
    if (!phoneNumber) {
      if (window.$toast) {
        window.$toast.error('Veuillez ajouter un numéro de téléphone à votre profil avant d\'effectuer un achat.', 'Téléphone requis')
      }
      return
    }

    const response = await post('/tickets/purchase', {
      lottery_id: lottery?.id,
      quantity: ticketQuantity.value,
      phone_number: phoneNumber,
      total_amount: totalAmount
    })

    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success(response.message || 'Commande créée avec succès !', 'Commande créée')
      }

      // Rediriger vers la page de sélection du moyen de paiement
      router.push({
        path: '/payment/method',
        query: {
          order_number: response.data.order_number,
          amount: totalAmount,
          lottery_id: lottery?.id,
          quantity: ticketQuantity.value,
          type: 'ticket'
        }
      })
    } else {
      throw new Error(response?.message || 'Erreur lors de la création de la commande')
    }

  } catch (error) {
    console.error('Error purchasing tickets:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de l\'achat des tickets'
    
    if (window.$toast) {
      window.$toast.error('' + errorMessage, ' Erreur d\'achat')
    }
  } finally {
    purchasing.value = false
  }
}

const purchaseDirectly = async () => {
  purchasing.value = true

  try {
    const response = await post('/payments/initiate', {
      product_id: product.value.id,
      type: 'product_purchase',
      amount: product.value.price
    })

    if (response && response.success) {
      // Si une redirection vers le paiement est nécessaire
      if (response.redirect_to_payment) {
        // Rediriger vers la sélection de méthode de paiement avec le numéro de commande
        router.push({
          name: 'payment.method',
          query: {
            order_number: response.data.order_number,
            amount: response.data.amount,
            type: response.data.type || 'product'
          }
        })
      } else {
        // Achat direct réussi
        if (window.$toast) {
          window.$toast.success('Produit acheté avec succès ! Vous recevrez une confirmation par SMS.', 'Achat confirmé')
        }

        // Refresh product data
        await loadProduct()
      }
    } else {
      throw new Error(response?.message || 'Erreur lors de l\'achat')
    }

  } catch (error) {
    console.error('Error purchasing product directly:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Erreur lors de l\'achat'
    
    if (window.$toast) {
      window.$toast.error(' ' + errorMessage, ' Erreur d\'achat')
    }
  } finally {
    purchasing.value = false
  }
}

const loadProduct = async () => {
  try {
    const productId = route.params.id
    console.log('Loading product ID:', productId)
    const response = await get(`/products/${productId}`)
    console.log('Product API response:', response)

    if (response && response.success && response.data) {
      product.value = response.data
      currentImage.value = product.value.image || '/images/products/placeholder.jpg'

      // Load participants if it's a lottery product
      if (product.value.lottery || product.value.active_lottery) {
        await loadParticipants()
      }

      // Load merchant rating
      if (product.value.merchant_id || product.value.merchant?.id) {
        merchantRatingLoading.value = true
        try {
          await fetchRatingSummary(product.value.merchant_id || product.value.merchant?.id)
        } catch (err) {
          console.warn('Erreur chargement notation marchand:', err)
        } finally {
          merchantRatingLoading.value = false
        }
      }
    } else if (response && response.data) {
      product.value = response.data
      currentImage.value = product.value.image || '/images/products/placeholder.jpg'
    } else {
      console.error('Product not found in response:', response)
      product.value = null
    }

  } catch (error) {
    console.error('Error loading product:', error)
    product.value = null
  }
}

const loadParticipants = async () => {
  try {
    const lottery = product.value.lottery || product.value.active_lottery
    if (lottery) {
      const response = await get(`/lotteries/${lottery.id}/participants`)
      if (response && response.success && response.data) {
        participants.value = response.data.participants || []
      } else {
        participants.value = []
      }
    }
  } catch (err) {
    console.error('Error loading participants:', err)
    participants.value = []
  }
}

onMounted(() => {
  loadProduct()
})
</script>
