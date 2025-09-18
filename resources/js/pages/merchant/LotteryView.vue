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
            lottery.status === 'active' ? 'bg-blue-100 text-blue-800' :
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
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center whitespace-nowrap"
        >
          <ClockIcon class="w-4 h-4 mr-2 flex-shrink-0" />
          Prolonger
        </button>
        <button
          v-if="lottery.status === 'active' && canDraw"
          @click="showDrawModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center whitespace-nowrap"
        >
          <GiftIcon class="w-4 h-4 mr-2 flex-shrink-0" />
          Effectuer le tirage
        </button>
        <button
          v-if="lottery.status !== 'completed'"
          @click="showEditModal = true"
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center whitespace-nowrap"
        >
          <PencilIcon class="w-4 h-4 mr-2 flex-shrink-0" />
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
          <div class="p-3 bg-blue-100 rounded-lg">
            <CurrencyDollarIcon class="w-6 h-6 text-blue-600" />
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
                    <p class="text-lg font-semibold text-gray-900">{{ lottery.category?.name || lottery.category || 'Sans catégorie' }}</p>
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
                  <span class="text-sm font-medium text-gray-700">Objectif de tickets</span>
                  <span class="text-sm text-gray-500">{{ lottery.sold_tickets }} / {{ lottery.max_tickets }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: participationProgress + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Participants uniques -->
              <div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Participants uniques</span>
                  <span class="text-sm text-gray-500">{{ lottery.participants_count }} personnes</span>
                </div>
                <div class="text-xs text-gray-600">
                  Moyenne: {{ lottery.participants_count > 0 ? (lottery.sold_tickets / lottery.participants_count).toFixed(1) : 0 }} tickets/personne
                </div>
              </div>

              <div v-if="lottery.revenue_target">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-sm font-medium text-gray-700">Objectif de revenus</span>
                  <span class="text-sm text-gray-500">{{ formatCurrency(lottery.total_revenue) }} / {{ formatCurrency(lottery.revenue_target) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: revenueProgress + '%' }"
                  ></div>
                </div>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4 text-center">
              <div class="p-4 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ participationProgress }}%</p>
                <p class="text-sm text-blue-600">Tickets vendus</p>
              </div>
              <div class="p-4 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">{{ conversionRate }}%</p>
                <p class="text-sm text-blue-600">Conversion</p>
              </div>
              <div class="p-4 bg-yellow-50 rounded-lg">
                <p class="text-2xl font-bold text-yellow-600">{{ daysRemaining }}</p>
                <p class="text-sm text-yellow-600">Jours restants</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Alerte tirage anticipé -->
        <div v-if="canDrawManually" class="bg-green-50 border border-green-200 rounded-xl p-4">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <GiftIcon class="w-5 h-5 text-green-600" />
            </div>
            <div>
              <h4 class="text-md font-semibold text-green-800">Tirage possible !</h4>
              <p class="text-sm text-green-600">
                Tous les tickets sont vendus ({{ lottery.sold_tickets }}/{{ lottery.max_tickets }}). 
                Vous pouvez effectuer le tirage maintenant dans "Actions rapides".
              </p>
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
              <div class="flex-1">
                <p class="font-semibold text-gray-900">{{ lottery.winner.first_name }} {{ lottery.winner.last_name }}</p>
                <p class="text-sm font-medium text-green-600">Ticket N° {{ lottery.winning_ticket_number }}</p>
                <p class="text-sm text-gray-600">Tirage effectué le {{ formatDate(lottery.draw_date) }}</p>
              </div>
            </div>

            <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
              <p class="text-sm text-yellow-800">
                Félicitations ! Le gagnant a été contacté automatiquement par email.
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
              @click="shareOrLottery"
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
              v-if="canDrawManually"
              @click="initiateManualDraw"
              class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
            >
              <GiftIcon class="w-4 h-4 mr-2" />
              Effectuer le tirage maintenant
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
                <span class="text-sm font-medium text-blue-600">+{{ roi }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <LotteryDrawModal
      :show="showDrawModal"
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
  title: '',
  description: '',
  status: '',
  product_value: 0,
  ticket_price: 0,
  category: '',
  participants_count: 0,
  tickets_sold: 0,
  total_revenue: 0,
  max_participants: 0,
  revenue_target: 0,
  end_date: '',
  created_at: '',
  views_count: 0,
  product_image: '',
  winner: null,
  draw_date: null,
  product: null,
  lottery_number: '',
  max_tickets: 0,
  sold_tickets: 0,
  progress_percentage: 0
})

const recentParticipants = ref([])

const showDrawModal = ref(false)
const showExtendModal = ref(false)
const showEditModal = ref(false)

// Computed
const participationProgress = computed(() => {
  if (!lottery.value.max_tickets) return 0
  return Math.min((lottery.value.sold_tickets / lottery.value.max_tickets) * 100, 100)
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
  if (!lottery.value.end_date) return 'Non définie'
  
  try {
    const now = new Date()
    const endDate = new Date(lottery.value.end_date)
    
    // Vérifier si la date est valide
    if (isNaN(endDate.getTime())) return 'Date invalide'
    
    const diffMs = endDate - now

    if (diffMs <= 0) return 'Terminée'

    const days = Math.floor(diffMs / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))

    if (days > 0) return `${days}j ${hours}h`
    return `${hours}h`
  } catch (error) {
    console.error('Error calculating time remaining:', error)
    return 'Erreur de calcul'
  }
})

const daysRemaining = computed(() => {
  if (!lottery.value.end_date) return 0
  
  try {
    const now = new Date()
    const endDate = new Date(lottery.value.end_date)
    
    // Vérifier si la date est valide
    if (isNaN(endDate.getTime())) return 0
    
    const diffMs = endDate - now

    if (diffMs <= 0) return 0
    return Math.ceil(diffMs / (1000 * 60 * 60 * 24))
  } catch (error) {
    console.error('Error calculating days remaining:', error)
    return 0
  }
})

const canDraw = computed(() => {
  try {
    // Peut tirer si : status active, pas de gagnant encore, et soit tous les tickets vendus soit la date de tirage atteinte
    const isActive = lottery.value.status === 'active'
    const hasNoWinner = !lottery.value.winning_ticket_number
    const allTicketsSold = lottery.value.sold_tickets >= lottery.value.max_tickets
    
    let drawDateReached = false
    if (lottery.value.draw_date) {
      const drawDate = new Date(lottery.value.draw_date)
      if (!isNaN(drawDate.getTime())) {
        drawDateReached = drawDate <= new Date()
      }
    }
    
    return isActive && hasNoWinner && (allTicketsSold || drawDateReached)
  } catch (error) {
    console.error('Error calculating canDraw:', error)
    return false
  }
})

const canDrawManually = computed(() => {
  try {
    // Peut faire un tirage manuel anticipé si : status active, pas de gagnant, tous les tickets vendus MAIS la date n'est pas encore atteinte
    const isActive = lottery.value.status === 'active'
    const hasNoWinner = !lottery.value.winning_ticket_number
    const allTicketsSold = lottery.value.sold_tickets >= lottery.value.max_tickets
    
    let dateNotReached = false
    if (lottery.value.draw_date) {
      const drawDate = new Date(lottery.value.draw_date)
      if (!isNaN(drawDate.getTime())) {
        dateNotReached = drawDate > new Date()
      }
    }
    
    return isActive && hasNoWinner && allTicketsSold && dateNotReached
  } catch (error) {
    console.error('Error calculating canDrawManually:', error)
    return false
  }
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
  if (!dateString) return 'Non définie'
  
  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return 'Date invalide'
    
    return new Intl.DateTimeFormat('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date)
  } catch (error) {
    console.error('Error formatting date:', error)
    return 'Erreur de format'
  }
}

const shareOrLottery = () => {
  const url = `${window.location.origin}/lottery/${lottery.value.id}`
  navigator.clipboard.writeText(url)
  if (window.$toast) {
    window.$toast.success('Lien de partage copié dans le presse-papiers!', '✅ Partage')
  }
}

const downloadReport = () => {
  // TODO: Implémenter le téléchargement de rapport
  if (window.$toast) {
    window.$toast.info('Fonctionnalité de téléchargement en cours de développement', '🚀 En développement')
  }
}

const pauseLottery = () => {
  // TODO: Implémenter la mise en pause
  if (window.$toast) {
    window.$toast.info('Mise en pause de la tombola...', '⏸️ Pause')
  }
}

const initiateManualDraw = () => {
  // Ouvrir directement le modal sans confirmation
  showDrawModal.value = true
}

const onLotteryDrawn = (drawResult) => {
  console.log('Tirage terminé:', drawResult)
  
  // Mettre à jour les données de la tombola
  if (drawResult && drawResult.success && drawResult.lottery) {
    // Mettre à jour l'objet lottery avec les nouvelles données
    lottery.value = {
      ...lottery.value,
      ...drawResult.lottery,
      status: 'completed',
      is_drawn: true,
      winner_user_id: drawResult.winning_ticket?.user_id,
      winning_ticket_number: drawResult.winning_ticket?.ticket_number,
      winner: drawResult.winning_ticket?.user,
      draw_date: new Date().toISOString()
    }
  }
  
  // Recharger les données complètes de la tombola pour être sûr
  setTimeout(() => {
    loadLotteryData()
  }, 1000)
  
  // Fermer le modal après un délai pour laisser voir le résultat
  setTimeout(() => {
    showDrawModal.value = false
  }, 3000)
}

const onLotteryExtended = () => {
  showExtendModal.value = false
  // Recharger les données de la tombola
  loadLotteryData()
}

const loadLotteryData = async () => {
  try {
    // Charger les vraies données depuis l'API
    const response = await get(`/lotteries/${route.params.id}`)
    if (response?.lottery) {
      const lotteryData = response.lottery
      
      // Debug: Log des données pour identifier le problème
      console.log('LotteryData:', lotteryData)
      console.log('Available keys:', Object.keys(lotteryData))
      
      // Calculer les participants uniques à partir des tickets payés
      const uniqueParticipants = new Set()
      const paidTickets = lotteryData.paid_tickets || lotteryData.paidTickets || lotteryData.tickets || []
      
      console.log('PaidTickets:', paidTickets)
      console.log('PaidTickets length:', paidTickets.length)
      
      paidTickets.forEach(ticket => {
        if (ticket.user?.id) {
          uniqueParticipants.add(ticket.user.id)
        }
      })
      
      const participantsCount = uniqueParticipants.size
      const totalRevenue = (lotteryData.sold_tickets || 0) * (lotteryData.ticket_price || 0)
      
      // Créer la liste des participants récents avec leurs tickets
      const participantsMap = new Map()
      paidTickets.forEach(ticket => {
        if (ticket.user?.id) {
          const userId = ticket.user.id
          const userName = `${ticket.user.first_name || ''} ${ticket.user.last_name || ''}`.trim()
          
          if (participantsMap.has(userId)) {
            participantsMap.get(userId).tickets_count++
          } else {
            participantsMap.set(userId, {
              id: userId,
              name: userName || 'Utilisateur anonyme',
              participated_at: ticket.created_at || new Date().toISOString(),
              tickets_count: 1
            })
          }
        }
      })
      
      // Trier par date de participation (plus récents en premier) et prendre les 5 premiers
      const sortedParticipants = Array.from(participantsMap.values())
        .sort((a, b) => new Date(b.participated_at) - new Date(a.participated_at))
        .slice(0, 5)
      
      // Mapper les données de l'API vers notre structure
      lottery.value = {
        ...lottery.value,
        id: lotteryData.id,
        title: lotteryData.title,
        description: lotteryData.description || '',
        status: lotteryData.status,
        lottery_number: lotteryData.lottery_number,
        ticket_price: lotteryData.ticket_price || 0,
        max_tickets: lotteryData.max_tickets || 0,
        sold_tickets: lotteryData.sold_tickets || 0,
        progress_percentage: lotteryData.progress_percentage || 0,
        draw_date: lotteryData.draw_date,
        created_at: lotteryData.created_at,
        
        // Données calculées corrigées
        tickets_sold: lotteryData.sold_tickets || 0,
        participants_count: participantsCount,
        total_revenue: totalRevenue,
        max_participants: lotteryData.max_tickets || 0,
        end_date: lotteryData.draw_date,
        revenue_target: lotteryData.revenue_target || null,
        views_count: lotteryData.views_count || 0,
        
        // Données du produit
        product: lotteryData.product,
        product_value: lotteryData.product?.price || 0,
        category: lotteryData.product?.category?.name || '',
        product_image: lotteryData.product?.image_url || lotteryData.product?.image || '',
        
        // Données gagnant
        winner: lotteryData.winner || null,
        winning_ticket_number: lotteryData.winning_ticket_number || null
      }
      
      // Mettre à jour la liste des participants récents avec les vraies données
      recentParticipants.value = sortedParticipants
    }
  } catch (error) {
    console.error('Erreur lors du chargement de la tombola:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du chargement des données de la tombola')
    }
  }
}

// Lifecycle
onMounted(() => {
  loadLotteryData()
})
</script>
