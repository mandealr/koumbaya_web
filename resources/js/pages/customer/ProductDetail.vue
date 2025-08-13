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
            :src="product.image"
            :alt="product.title"
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
          <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.title }}</h1>
          <p class="text-lg text-gray-600 mb-4">{{ product.description }}</p>
          
          <div class="flex items-center space-x-4 mb-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-blue-600">{{ product.price }} FCFA</div>
              <div class="text-sm text-gray-500">Valeur du produit</div>
            </div>
            <div class="text-center">
              <div class="text-xl font-semibold text-gray-900">{{ product.ticket_price }} FCFA</div>
              <div class="text-sm text-gray-500">Prix par ticket</div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mb-6">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
              <span>Progression de la tombola</span>
              <span>{{ product.progress }}% ({{ product.sold_tickets }}/{{ product.total_tickets }})</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div 
                class="bg-blue-600 h-3 rounded-full transition-all duration-300" 
                :style="{ width: product.progress + '%' }"
              ></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
              <span>0 tickets</span>
              <span>{{ product.total_tickets }} tickets</span>
            </div>
          </div>

          <!-- Time Remaining -->
          <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center mb-2">
              <CalendarIcon class="w-5 h-5 text-gray-600 mr-2" />
              <span class="font-medium text-gray-900">Date de tirage: {{ formatDate(product.draw_date) }}</span>
            </div>
            <div class="flex items-center">
              <ClockIcon class="w-5 h-5 text-gray-600 mr-2" />
              <span class="text-gray-600">{{ getRemainingTime(product.draw_date) }}</span>
            </div>
          </div>
        </div>

        <!-- Ticket Purchase -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
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
                :max="product.total_tickets - product.sold_tickets"
                class="w-20 text-center border border-gray-300 rounded-md py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
                @click="increaseTickets"
                class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                :disabled="ticketQuantity >= (product.total_tickets - product.sold_tickets)"
              >
                <PlusIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="flex justify-between items-center mb-4">
            <span class="text-gray-600">Total à payer:</span>
            <span class="text-2xl font-bold text-blue-600">{{ totalPrice }} FCFA</span>
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
import api from '@/composables/api'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
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

const totalPrice = computed(() => {
  if (!product.value) return 0
  return (parseInt(product.value.ticket_price.replace(',', '')) * ticketQuantity.value).toLocaleString()
})

const increaseTickets = () => {
  const maxTickets = product.value.total_tickets - product.value.sold_tickets
  if (ticketQuantity.value < maxTickets) {
    ticketQuantity.value++
  }
}

const decreaseTickets = () => {
  if (ticketQuantity.value > 1) {
    ticketQuantity.value--
  }
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

const getRemainingTime = (date) => {
  const now = new Date()
  const drawDate = new Date(date)
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
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // Add success notification logic here
    console.log(`Purchased ${ticketQuantity.value} tickets for product ${product.value.id}`)
    
    // Refresh product data
    await loadProduct()
    
    // Reset quantity
    ticketQuantity.value = 1
    
  } catch (error) {
    console.error('Error purchasing tickets:', error)
    // Add error handling here
  } finally {
    purchasing.value = false
  }
}

const loadProduct = async () => {
  loading.value = true
  
  try {
    // Simulate API call - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Mock product data
    const mockProduct = {
      id: parseInt(route.params.id),
      title: 'iPhone 15 Pro',
      description: 'Le dernier flagship d\'Apple avec une caméra révolutionnaire et la puce A17 Pro',
      detailed_description: 'L\'iPhone 15 Pro redéfinit ce qui est possible avec un smartphone. Doté de la puce A17 Pro révolutionnaire et d\'un système de caméra professionnel, il offre des performances exceptionnelles pour la photographie, la vidéo et les jeux.',
      price: '750,000',
      ticket_price: '1,000',
      category: 'electronics',
      progress: 85,
      sold_tickets: 637,
      total_tickets: 750,
      status: 'active',
      featured: true,
      draw_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000),
      image: '/images/products/iphone15.jpg',
      gallery: [
        '/images/products/iphone15-2.jpg',
        '/images/products/iphone15-3.jpg',
        '/images/products/iphone15-4.jpg',
        '/images/products/iphone15-5.jpg'
      ],
      features: [
        'Écran Super Retina XDR 6,1 pouces',
        'Puce A17 Pro avec GPU 6 cœurs',
        'Système de caméra Pro avec téléobjectif 3x',
        'Action Button personnalisable',
        'Connecteur USB-C',
        'Résistant aux éclaboussures, à l\'eau et à la poussière'
      ]
    }
    
    product.value = mockProduct
    currentImage.value = mockProduct.image
    
    // Mock participants data
    participants.value = [
      { id: 1, name: 'Marie Dubois', tickets: 5 },
      { id: 2, name: 'Jean Martin', tickets: 3 },
      { id: 3, name: 'Sophie Laurent', tickets: 8 },
      { id: 4, name: 'Pierre Durand', tickets: 2 },
      { id: 5, name: 'Anne Moreau', tickets: 4 }
    ]
    
  } catch (error) {
    console.error('Error loading product:', error)
    product.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadProduct()
})
</script>