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
          <img
            :src="product.image_url || product.main_image || '/images/products/placeholder.jpg'"
            :alt="product.title || product.name"
            class="w-full h-96 object-cover rounded-xl"
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
        <div v-if="product.gallery && product.gallery.length > 0" class="grid grid-cols-4 gap-2 mt-4">
          <img
            v-for="(image, index) in product.gallery"
            :key="index"
            :src="image"
            :alt="`${product.title} ${index + 1}`"
            class="w-full h-20 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
            @click="currentImage = image"
          />
        </div>
      </div>

      <!-- Product Info -->
      <div>
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.title || product.name }}</h1>
          <p class="text-lg text-gray-600 mb-4">{{ product.description }}</p>

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
              :disabled="purchasing || product.status !== 'active'"
              class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              <span v-if="purchasing">Achat en cours...</span>
              <span v-else-if="product.status !== 'active'">Tombola terminée</span>
              <span v-else>Acheter {{ ticketQuantity }} ticket{{ ticketQuantity > 1 ? 's' : '' }}</span>
            </button>
          </div>

          <!-- Direct Purchase -->
          <div v-else>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Acheter ce produit</h3>

            <div class="mb-6 p-4 bg-green-50 rounded-lg border border-green-200">
              <div class="flex items-center mb-2">
                <CheckCircleIcon class="w-5 h-5 text-green-600 mr-2" />
                <span class="font-medium text-green-900">Achat direct disponible</span>
              </div>
              <p class="text-sm text-green-700">
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
              class="w-full bg-green-600 text-white py-3 rounded-lg font-medium hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
            >
              <span v-if="purchasing">Achat en cours...</span>
              <template v-else>
                <span>🛒</span>
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
          <li v-for="feature in product.features" :key="feature" class="flex items-center">
            <CheckCircleIcon class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0" />
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
import {
  ArrowLeftIcon,
  StarIcon,
  CalendarIcon,
  ClockIcon,
  MinusIcon,
  PlusIcon,
  CheckCircleIcon,
  UsersIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const { get, post, loading, error } = useApi()

const purchasing = ref(false)
const product = ref(null)
const ticketQuantity = ref(1)
const activeTab = ref('description')
const currentImage = ref('')
const participants = ref([])

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

const purchaseTickets = async () => {
  purchasing.value = true

  try {
    const response = await post('/tickets/purchase', {
      product_id: product.value.id,
      lottery_id: product.value.lottery?.id || product.value.active_lottery?.id,
      quantity: ticketQuantity.value
    })

    if (response && response.success) {
      alert('🎉 Tickets achetés avec succès ! Bonne chance pour le tirage !')

      // Refresh product data
      await loadProduct()

      // Reset quantity
      ticketQuantity.value = 1
    } else {
      throw new Error(response?.message || 'Erreur lors de l\'achat des tickets')
    }

  } catch (error) {
    console.error('Error purchasing tickets:', error)
    alert('❌ Erreur lors de l\'achat des tickets. Veuillez réessayer.')
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
      alert('🎉 Produit acheté avec succès ! Vous recevrez une confirmation par SMS.')

      // Refresh product data
      await loadProduct()
    } else {
      throw new Error(response?.message || 'Erreur lors de l\'achat')
    }

  } catch (error) {
    console.error('Error purchasing product directly:', error)
    alert('❌ Erreur lors de l\'achat. Veuillez réessayer.')
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
      if (response && response.data) {
        participants.value = response.data
      }
    }
  } catch (error) {
    console.error('Error loading participants:', error)
    participants.value = []
  }
}

onMounted(() => {
  loadProduct()
})
</script>
