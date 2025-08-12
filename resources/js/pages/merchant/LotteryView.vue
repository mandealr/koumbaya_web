<template>
  <div class="space-y-6">
    <!-- Header avec actions -->
    <div class="flex justify-between items-start">
      <div>
        <div class="flex items-center space-x-3 mb-2">
          <router-link 
            to="/merchant/lotteries" 
            class="text-gray-500 hover:text-gray-700"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </router-link>
          <h1 class="text-3xl font-bold text-gray-900">{{ lottery.title }}</h1>
          <span :class="[
            'px-3 py-1 rounded-full text-sm font-medium',
            lottery.status === 'active' ? 'bg-green-100 text-green-800' :
            lottery.status === 'completed' ? 'bg-blue-100 text-blue-800' :
            lottery.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
            'bg-gray-100 text-gray-800'
          ]">
            {{ getStatusText(lottery.status) }}
          </span>
        </div>
        <p class="text-gray-600">Gérez votre tombola et suivez les participations</p>
      </div>

      <div class="flex space-x-3">
        <button 
          v-if="lottery.status === 'active'"
          @click="showExtendModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center"
        >
          <ClockIcon class="w-4 h-4 mr-2" />
          Prolonger
        </button>
        <button 
          v-if="lottery.status === 'active' && canDraw"
          @click="showDrawModal = true"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center"
        >
          <GiftIcon class="w-4 h-4 mr-2" />
          Effectuer le tirage
        </button>
        <button 
          @click="showEditModal = true"
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center"
        >
          <PencilIcon class="w-4 h-4 mr-2" />
          Modifier
        </button>
      </div>
    </div>

    <!-- Stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-xl shadow-sm border">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <UsersIcon class="w-6 h-6 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Participants</p>
            <p class="text-2xl font-bold text-gray-900">{{ lottery.participants_count }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <CurrencyDollarIcon class="w-6 h-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Revenus générés</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(lottery.total_revenue) }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border">
        <div class="flex items-center">
          <div class="p-3 bg-purple-100 rounded-lg">
            <TicketIcon class="w-6 h-6 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Tickets vendus</p>
            <p class="text-2xl font-bold text-gray-900">{{ lottery.tickets_sold }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border">
        <div class="flex items-center">
          <div class="p-3 bg-yellow-100 rounded-lg">
            <CalendarIcon class="w-6 h-6 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Temps restant</p>
            <p class="text-2xl font-bold text-gray-900">{{ timeRemaining }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Informations de la tombola -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Détails du produit -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Informations du produit</h3>
          </div>
          
          <div class="p-6">
            <div class="flex space-x-6">
              <div class="flex-shrink-0">
                <img 
                  :src="lottery.product_image || '/images/products/placeholder.jpg'" 
                  :alt="lottery.title"
                  class="w-32 h-32 object-cover rounded-lg"
                />
              </div>
              <div class="flex-1">
                <h4 class="text-xl font-semibold text-gray-900 mb-2">{{ lottery.title }}</h4>
                <p class="text-gray-600 mb-4">{{ lottery.description }}</p>
                
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm font-medium text-gray-500">Valeur du produit</p>
                    <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(lottery.product_value) }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Prix du ticket</p>
                    <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(lottery.ticket_price) }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Catégorie</p>
                    <p class="text-lg font-semibold text-gray-900">{{ lottery.category }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Date de fin</p>
                    <p class="text-lg font-semibold text-gray-900">{{ formatDate(lottery.end_date) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Progression de la tombola -->
        <div class="bg-white rounded-xl shadow-sm border">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Progression</h3>
          </div>
          
          <div class="p-6">
            <div class="space-y-4">
              <div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Objectif de participation</span>
                  <span class="text-sm text-gray-500">{{ lottery.participants_count }} / {{ lottery.max_participants || '∞' }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: participationProgress + '%' }"
                  ></div>
                </div>
              </div>

              <div v-if="lottery.revenue_target">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Objectif de revenus</span>
                  <span class="text-sm text-gray-500">{{ formatCurrency(lottery.total_revenue) }} / {{ formatCurrency(lottery.revenue_target) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-green-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: revenueProgress + '%' }"
                  ></div>
                </div>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4 text-center">
              <div class="p-4 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ participationProgress }}%</p>
                <p class="text-sm text-blue-600">Participation</p>
              </div>
              <div class="p-4 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">{{ conversionRate }}%</p>
                <p class="text-sm text-green-600">Conversion</p>
              </div>
              <div class="p-4 bg-yellow-50 rounded-lg">
                <p class="text-2xl font-bold text-yellow-600">{{ daysRemaining }}</p>
                <p class="text-sm text-yellow-600">Jours restants</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Gagnant (si tirage effectué) -->
        <div v-if="lottery.winner" class="bg-white rounded-xl shadow-sm border">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
              <TrophyIcon class="w-5 h-5 text-yellow-500 mr-2" />
              Gagnant
            </h3>
          </div>
          
          <div class="p-6">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <UserIcon class="w-6 h-6 text-yellow-600" />
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ lottery.winner.name }}</p>
                <p class="text-sm text-gray-600">Tirage effectué le {{ formatDate(lottery.draw_date) }}</p>
              </div>
            </div>
            
            <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
              <p class="text-sm text-yellow-800">
                🎉 Félicitations ! Le gagnant a été contacté automatiquement par email.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Actions rapides -->
        <div class="bg-white rounded-xl shadow-sm border">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Actions rapides</h3>
          </div>
          
          <div class="p-6 space-y-3">
            <button 
              @click="shareLottery"
              class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              <ShareIcon class="w-4 h-4 mr-2" />
              Partager la tombola
            </button>
            
            <button 
              @click="downloadReport"
              class="w-full flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
            >
              <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
              Télécharger le rapport
            </button>
            
            <button 
              v-if="lottery.status === 'active'"
              @click="pauseLottery"
              class="w-full flex items-center justify-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
            >
              <PauseIcon class="w-4 h-4 mr-2" />
              Mettre en pause
            </button>
          </div>
        </div>

        <!-- Participants récents -->
        <div class="bg-white rounded-xl shadow-sm border">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold text-gray-900">Participants récents</h3>
              <router-link 
                :to="`/merchant/lotteries/${lottery.id}/participants`"
                class="text-sm text-blue-600 hover:text-blue-700"
              >
                Voir tout
              </router-link>
            </div>
          </div>
          
          <div class="p-6">
            <div class="space-y-3">
              <div 
                v-for="participant in recentParticipants" 
                :key="participant.id"
                class="flex items-center space-x-3"
              >
                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                  <span class="text-sm font-medium text-gray-600">
                    {{ participant.name.charAt(0) }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ participant.name }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(participant.participated_at) }}</p>
                </div>
                <div class="text-sm text-gray-500">
                  {{ participant.tickets_count }} ticket{{ participant.tickets_count > 1 ? 's' : '' }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="bg-white rounded-xl shadow-sm border">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Statistiques</h3>
          </div>
          
          <div class="p-6">
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Vues de la tombola</span>
                <span class="text-sm font-medium text-gray-900">{{ lottery.views_count || 'N/A' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Taux de conversion</span>
                <span class="text-sm font-medium text-gray-900">{{ conversionRate }}%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Panier moyen</span>
                <span class="text-sm font-medium text-gray-900">{{ formatCurrency(averageBasket) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Retour sur investissement</span>
                <span class="text-sm font-medium text-green-600">+{{ roi }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <LotteryDrawModal
      v-if="showDrawModal"
      :lottery="lottery"
      @close="showDrawModal = false"
      @drawn="onLotteryDrawn"
    />

    <LotteryExtendModal
      v-if="showExtendModal"
      :lottery="lottery"
      @close="showExtendModal = false"
      @extended="onLotteryExtended"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApi } from '@/composables/api'
import LotteryDrawModal from '@/components/merchant/LotteryDrawModal.vue'
import LotteryExtendModal from '@/components/merchant/LotteryExtendModal.vue'
import {
  ArrowLeftIcon,
  UsersIcon,
  CurrencyDollarIcon,
  TicketIcon,
  CalendarIcon,
  GiftIcon,
  ClockIcon,
  PencilIcon,
  ShareIcon,
  DocumentArrowDownIcon,
  PauseIcon,
  TrophyIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const { get, post } = useApi()

// Data
const lottery = ref({
  id: route.params.id,
  title: 'iPhone 15 Pro Max 256GB',
  description: 'Tentez de gagner le dernier iPhone 15 Pro Max dans sa version 256GB, couleur Titane Naturel. Un smartphone révolutionnaire avec une caméra professionnelle.',
  status: 'active',
  product_value: 1599000,
  ticket_price: 5000,
  category: 'Électronique',
  participants_count: 156,
  tickets_sold: 203,
  total_revenue: 1015000,
  max_participants: 300,
  revenue_target: 1500000,
  end_date: '2024-12-25T23:59:59',
  created_at: '2024-11-01T10:00:00',
  views_count: 1250,
  product_image: '/images/products/iphone-15-pro.jpg',
  winner: null,
  draw_date: null
})

const recentParticipants = ref([
  { id: 1, name: 'Jean Dupont', participated_at: '2024-11-15T14:30:00', tickets_count: 2 },
  { id: 2, name: 'Marie Martin', participated_at: '2024-11-15T13:45:00', tickets_count: 1 },
  { id: 3, name: 'Paul Durant', participated_at: '2024-11-15T12:20:00', tickets_count: 3 },
  { id: 4, name: 'Sophie Leroy', participated_at: '2024-11-15T11:15:00', tickets_count: 1 },
  { id: 5, name: 'Pierre Moreau', participated_at: '2024-11-15T10:30:00', tickets_count: 2 }
])

const showDrawModal = ref(false)
const showExtendModal = ref(false)
const showEditModal = ref(false)

// Computed
const participationProgress = computed(() => {
  if (!lottery.value.max_participants) return 0
  return Math.min((lottery.value.participants_count / lottery.value.max_participants) * 100, 100)
})

const revenueProgress = computed(() => {
  if (!lottery.value.revenue_target) return 0
  return Math.min((lottery.value.total_revenue / lottery.value.revenue_target) * 100, 100)
})

const conversionRate = computed(() => {
  if (!lottery.value.views_count || lottery.value.views_count === 0) return 0
  return ((lottery.value.participants_count / lottery.value.views_count) * 100).toFixed(1)
})

const averageBasket = computed(() => {
  if (lottery.value.participants_count === 0) return 0
  return lottery.value.total_revenue / lottery.value.participants_count
})

const roi = computed(() => {
  const investment = lottery.value.product_value
  const profit = lottery.value.total_revenue - investment
  return ((profit / investment) * 100).toFixed(1)
})

const timeRemaining = computed(() => {
  const now = new Date()
  const endDate = new Date(lottery.value.end_date)
  const diffMs = endDate - now
  
  if (diffMs <= 0) return 'Terminée'
  
  const days = Math.floor(diffMs / (1000 * 60 * 60 * 24))
  const hours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  
  if (days > 0) return `${days}j ${hours}h`
  return `${hours}h`
})

const daysRemaining = computed(() => {
  const now = new Date()
  const endDate = new Date(lottery.value.end_date)
  const diffMs = endDate - now
  
  if (diffMs <= 0) return 0
  return Math.ceil(diffMs / (1000 * 60 * 60 * 24))
})

const canDraw = computed(() => {
  return lottery.value.participants_count >= 10 // Minimum 10 participants pour effectuer un tirage
})

// Methods
const getStatusText = (status) => {
  const statusMap = {
    'active': 'Active',
    'completed': 'Terminée',
    'pending': 'En attente',
    'paused': 'En pause',
    'cancelled': 'Annulée'
  }
  return statusMap[status] || status
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const formatDate = (dateString) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(dateString))
}

const shareOrLottery = () => {
  const url = `${window.location.origin}/lottery/${lottery.value.id}`
  navigator.clipboard.writeText(url)
  alert('Lien de partage copié dans le presse-papiers!')
}

const downloadReport = () => {
  // TODO: Implémenter le téléchargement de rapport
  alert('Fonctionnalité de téléchargement en cours de développement')
}

const pauseLottery = () => {
  // TODO: Implémenter la mise en pause
  alert('Mise en pause de la tombola...')
}

const onLotteryDrawn = () => {
  showDrawModal.value = false
  // Recharger les données de la tombola
  loadLotteryData()
}

const onLotteryExtended = () => {
  showExtendModal.value = false
  // Recharger les données de la tombola
  loadLotteryData()
}

const loadLotteryData = async () => {
  try {
    // TODO: Charger les vraies données depuis l'API
    const response = await get(`/merchant/lotteries/${route.params.id}`)
    if (response?.data) {
      lottery.value = { ...lottery.value, ...response.data }
    }
  } catch (error) {
    console.error('Erreur lors du chargement de la tombola:', error)
  }
}

// Lifecycle
onMounted(() => {
  loadLotteryData()
})
</script>