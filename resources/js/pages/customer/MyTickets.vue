<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Mes billets</h1>
      <p class="mt-2 text-gray-600">Suivez vos participations aux tombolas</p>
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
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            <option value="">Tous les statuts</option>
            <option value="active">En cours</option>
            <option value="won">Gagné</option>
            <option value="lost">Perdu</option>
            <option value="pending">En attente</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
          <select 
            v-model="filters.period"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            <option value="">Toutes les périodes</option>
            <option value="week">Cette semaine</option>
            <option value="month">Ce mois</option>
            <option value="quarter">Ce trimestre</option>
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

    <!-- Tickets List -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Chargement de vos billets...</p>
    </div>

    <div v-else-if="filteredTickets.length === 0" class="text-center py-12">
      <TicketIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ tickets.length === 0 ? 'Aucun billet acheté' : 'Aucun résultat trouvé' }}
      </h3>
      <p class="text-gray-600 mb-4">
        {{ tickets.length === 0 
          ? 'Vous n\'avez pas encore participé à des tombolas'
          : 'Essayez de modifier vos filtres de recherche'
        }}
      </p>
      <router-link
        v-if="tickets.length === 0"
        to="/customer/products"
        class="inline-block bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors"
      >
        Découvrir les produits
      </router-link>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="ticket in filteredTickets"
        :key="ticket.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
      >
        <div class="p-6">
          <div class="flex items-start space-x-4">
            <!-- Product Image -->
            <div class="flex-shrink-0">
              <img
                :src="ticket.product.image"
                :alt="ticket.product.title"
                class="w-20 h-20 rounded-lg object-cover"
              />
            </div>

            <!-- Ticket Info -->
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ ticket.product.title }}</h3>
                  <p class="text-sm text-gray-600">{{ ticket.product.description }}</p>
                </div>
                <span :class="[
                  'px-3 py-1 text-sm font-medium rounded-full',
                  ticket.status === 'won' ? 'bg-green-100 text-green-800' :
                  ticket.status === 'lost' ? 'bg-red-100 text-red-800' :
                  ticket.status === 'active' ? 'bg-blue-100 text-blue-800' :
                  'bg-yellow-100 text-yellow-800'
                ]">
                  {{ getStatusLabel(ticket.status) }}
                </span>
              </div>

              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                  <p class="text-xs text-gray-500">Billets achetés</p>
                  <p class="text-sm font-semibold text-gray-900">{{ ticket.quantity }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Total payé</p>
                  <p class="text-sm font-semibold text-gray-900">{{ ticket.total_price }} FCFA</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Date d'achat</p>
                  <p class="text-sm font-semibold text-gray-900">{{ formatDate(ticket.purchased_at) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Tirage</p>
                  <p class="text-sm font-semibold text-gray-900">{{ formatDate(ticket.lottery.draw_date) }}</p>
                </div>
              </div>

              <!-- Ticket Numbers -->
              <div class="mb-4">
                <p class="text-xs text-gray-500 mb-2">Vos numéros de billets</p>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="number in ticket.ticket_numbers"
                    :key="number"
                    :class="[
                      'px-2 py-1 text-xs font-mono rounded border',
                      ticket.winning_number === number ? 
                        'bg-green-100 text-green-800 border-green-300' :
                        'bg-gray-50 text-gray-700 border-gray-300'
                    ]"
                  >
                    #{{ number }}
                  </span>
                </div>
              </div>

              <!-- Progress Bar -->
              <div v-if="ticket.status === 'active'" class="mb-4">
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                  <span>Progression de la tombola</span>
                  <span>{{ ticket.lottery.progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                    :style="{ width: ticket.lottery.progress + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Winning Details -->
              <div v-if="ticket.status === 'won'" class="bg-green-50 p-4 rounded-lg mb-4">
                <div class="flex items-center mb-2">
                  <TrophyIcon class="w-5 h-5 text-green-600 mr-2" />
                  <p class="font-semibold text-green-900">Félicitations ! Vous avez gagné !</p>
                </div>
                <p class="text-sm text-green-800">Numéro gagnant: #{{ ticket.winning_number }}</p>
                <p class="text-sm text-green-800">Tirage effectué le {{ formatDate(ticket.lottery.draw_date) }}</p>
                <div v-if="!ticket.prize_claimed" class="mt-3">
                  <button
                    @click="claimPrize(ticket.id)"
                    class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 transition-colors"
                  >
                    Réclamer mon prix
                  </button>
                </div>
                <div v-else class="mt-3">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                    <CheckCircleIcon class="w-4 h-4 mr-1" />
                    Prix réclamé
                  </span>
                </div>
              </div>

              <!-- Loss Details -->
              <div v-if="ticket.status === 'lost'" class="bg-red-50 p-4 rounded-lg mb-4">
                <div class="flex items-center mb-2">
                  <ExclamationCircleIcon class="w-5 h-5 text-red-600 mr-2" />
                  <p class="font-semibold text-red-900">Pas de chance cette fois-ci</p>
                </div>
                <p class="text-sm text-red-800">Numéro gagnant: #{{ ticket.winning_number }}</p>
                <p class="text-sm text-red-800">Tirage effectué le {{ formatDate(ticket.lottery.draw_date) }}</p>
              </div>

              <!-- Action Buttons -->
              <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                  {{ getRemainingTime(ticket.lottery.draw_date, ticket.status) }}
                </div>
                <div class="flex space-x-2">
                  <router-link
                    :to="`/customer/products/${ticket.product.id}`"
                    class="text-sm text-green-600 hover:text-green-700 font-medium"
                  >
                    Voir le produit
                  </router-link>
                  <button
                    @click="downloadTicket(ticket.id)"
                    class="text-sm text-gray-600 hover:text-gray-700 font-medium"
                  >
                    Télécharger
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More -->
    <div v-if="!loading && filteredTickets.length > 0 && hasMore" class="text-center mt-8">
      <button
        @click="loadMore"
        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-green-600 bg-green-50 hover:bg-green-100"
      >
        Charger plus de billets
        <ArrowDownIcon class="ml-2 w-5 h-5" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  TicketIcon,
  TrophyIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  ArrowDownIcon
} from '@heroicons/vue/24/outline'
import api from '@/composables/api'

const loading = ref(false)
const hasMore = ref(true)

const filters = ref({
  search: '',
  status: '',
  period: ''
})

const stats = ref([
  {
    label: 'Total billets',
    value: '24',
    color: 'bg-blue-500',
    icon: TicketIcon
  },
  {
    label: 'Prix gagnés',
    value: '1',
    color: 'bg-green-500',
    icon: TrophyIcon
  },
  {
    label: 'Total dépensé',
    value: '36,500 FCFA',
    color: 'bg-yellow-500',
    icon: CurrencyDollarIcon
  },
  {
    label: 'En cours',
    value: '8',
    color: 'bg-purple-500',
    icon: ClockIcon
  }
])

const tickets = ref([
  {
    id: 1,
    product: {
      id: 1,
      title: 'iPhone 15 Pro',
      description: 'Le dernier flagship d\'Apple',
      image: '/images/products/iphone15.jpg'
    },
    lottery: {
      id: 1,
      draw_date: new Date(Date.now() + 3 * 24 * 60 * 60 * 1000),
      progress: 85,
      winning_number: null
    },
    quantity: 3,
    total_price: '3,000',
    status: 'active',
    purchased_at: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000),
    ticket_numbers: ['000123', '000124', '000125'],
    winning_number: null,
    prize_claimed: false
  },
  {
    id: 2,
    product: {
      id: 2,
      title: 'MacBook Pro M3',
      description: 'Puissance et performance',
      image: '/images/products/macbook.jpg'
    },
    lottery: {
      id: 2,
      draw_date: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000),
      progress: 100,
      winning_number: '000501'
    },
    quantity: 2,
    total_price: '4,000',
    status: 'won',
    purchased_at: new Date(Date.now() - 10 * 24 * 60 * 60 * 1000),
    ticket_numbers: ['000456', '000501'],
    winning_number: '000501',
    prize_claimed: false
  },
  {
    id: 3,
    product: {
      id: 3,
      title: 'PlayStation 5',
      description: 'Console de jeu nouvelle génération',
      image: '/images/products/ps5.jpg'
    },
    lottery: {
      id: 3,
      draw_date: new Date(Date.now() - 15 * 24 * 60 * 60 * 1000),
      progress: 100,
      winning_number: '000789'
    },
    quantity: 5,
    total_price: '2,500',
    status: 'lost',
    purchased_at: new Date(Date.now() - 20 * 24 * 60 * 60 * 1000),
    ticket_numbers: ['000234', '000235', '000236', '000237', '000238'],
    winning_number: '000789',
    prize_claimed: false
  },
  {
    id: 4,
    product: {
      id: 4,
      title: 'AirPods Pro',
      description: 'Audio haute qualité',
      image: '/images/products/airpods.jpg'
    },
    lottery: {
      id: 4,
      draw_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000),
      progress: 65,
      winning_number: null
    },
    quantity: 2,
    total_price: '600',
    status: 'active',
    purchased_at: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000),
    ticket_numbers: ['000345', '000346'],
    winning_number: null,
    prize_claimed: false
  },
  {
    id: 5,
    product: {
      id: 5,
      title: 'Samsung Galaxy S24',
      description: 'Smartphone Android premium',
      image: '/images/products/samsung.jpg'
    },
    lottery: {
      id: 5,
      draw_date: new Date(Date.now() + 1 * 24 * 60 * 60 * 1000),
      progress: 98,
      winning_number: null
    },
    quantity: 4,
    total_price: '3,200',
    status: 'pending',
    purchased_at: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000),
    ticket_numbers: ['000567', '000568', '000569', '000570'],
    winning_number: null,
    prize_claimed: false
  }
])

const filteredTickets = computed(() => {
  let filtered = tickets.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(ticket => 
      ticket.product.title.toLowerCase().includes(search) ||
      ticket.product.description.toLowerCase().includes(search)
    )
  }

  if (filters.value.status) {
    filtered = filtered.filter(ticket => ticket.status === filters.value.status)
  }

  if (filters.value.period) {
    const now = new Date()
    const period = filters.value.period
    
    filtered = filtered.filter(ticket => {
      const purchaseDate = new Date(ticket.purchased_at)
      
      if (period === 'week') {
        const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
        return purchaseDate >= weekAgo
      } else if (period === 'month') {
        const monthAgo = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate())
        return purchaseDate >= monthAgo
      } else if (period === 'quarter') {
        const quarterAgo = new Date(now.getFullYear(), now.getMonth() - 3, now.getDate())
        return purchaseDate >= quarterAgo
      }
      
      return true
    })
  }

  return filtered.sort((a, b) => new Date(b.purchased_at) - new Date(a.purchased_at))
})

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    period: ''
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

const getRemainingTime = (date, status) => {
  if (status === 'lost' || status === 'won') {
    return 'Tirage terminé'
  }
  
  const now = new Date()
  const drawDate = new Date(date)
  const diff = drawDate - now
  
  if (diff <= 0) {
    return 'Tirage en cours'
  }
  
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  
  if (days > 0) {
    return `Tirage dans ${days} jour${days > 1 ? 's' : ''}`
  } else if (hours > 0) {
    return `Tirage dans ${hours}h`
  } else {
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
    return `Tirage dans ${minutes}min`
  }
}

const getStatusLabel = (status) => {
  const labels = {
    'active': 'En cours',
    'won': 'Gagné',
    'lost': 'Perdu',
    'pending': 'Tirage imminent'
  }
  return labels[status] || status
}

const claimPrize = async (ticketId) => {
  try {
    const ticket = tickets.value.find(t => t.id === ticketId)
    if (ticket) {
      ticket.prize_claimed = true
    }
    console.log(`Prize claimed for ticket ${ticketId}`)
  } catch (error) {
    console.error('Error claiming prize:', error)
  }
}

const downloadTicket = async (ticketId) => {
  try {
    console.log(`Downloading ticket ${ticketId}`)
    // Implement ticket download logic
  } catch (error) {
    console.error('Error downloading ticket:', error)
  }
}

const loadMore = () => {
  // Simulate loading more tickets
  console.log('Loading more tickets...')
  hasMore.value = false
}

onMounted(() => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
  }, 1000)
})
</script>