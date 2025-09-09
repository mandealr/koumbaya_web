<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestion des tirages spéciaux</h1>
          <p class="mt-2 text-gray-600">Gérez les tirages et suivez les résultats des tirages spéciaux</p>
        </div>
        <button
          @click="refreshData"
          :disabled="loading"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center disabled:bg-gray-400"
        >
          <svg class="w-4 h-4 mr-2" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Actualiser
        </button>
      </div>
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

    <!-- Tabs -->
    <div class="mb-8">
      <div class="border-b border-gray-200">
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
            <span v-if="tab.count !== undefined" class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Actions rapides -->
      <div class="mt-4 flex justify-between items-center">
        <div class="flex space-x-3">
          <button
            @click="showEligibleDraws"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            Tirages disponibles ({{ statistics.pending_draws || 0 }})
          </button>
        </div>

        <div class="text-sm text-gray-500">
          Dernière mise à jour : {{ new Date().toLocaleTimeString('fr-FR') }}
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Chargement des tombolas...</p>
    </div>

    <!-- Pending Draws Tab -->
    <div v-else-if="activeTab === 'pending'">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Tirages en attente</h3>
          <p class="text-sm text-gray-600">Tombolas prêtes pour le tirage</p>
        </div>

        <div v-if="pendingDraws.length === 0" class="text-center py-12">
          <ClockIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun tirage en attente</h3>
          <p class="text-gray-600">Toutes les tombolas sont à jour</p>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="lottery in pendingDraws"
            :key="lottery.id"
            class="p-6 hover:bg-gray-50"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <img
                  :src="lottery.product?.image_url || lottery.product?.main_image || '/images/placeholder.jpg'"
                  :alt="lottery.product?.title || lottery.product?.name"
                  class="w-16 h-16 rounded-lg object-cover"
                />
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">{{ lottery.product?.title || lottery.product?.name }}</h4>
                  <p class="text-sm text-gray-600">{{ lottery.product?.description }}</p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span class="text-sm font-medium text-blue-600">
                      {{ lottery.sold_tickets || 0 }}/{{ lottery.total_tickets }} tickets vendus
                    </span>
                    <span class="text-sm text-gray-500">
                      Tirage prévu: {{ formatDate(lottery.draw_date || lottery.end_date) }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex space-x-3">
                <button
                  @click="viewParticipants(lottery)"
                  class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                >
                  Voir participants
                </button>
                <button
                  @click="conductDraw(lottery)"
                  :disabled="conductingDraw"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:bg-gray-400"
                >
                  <span v-if="conductingDraw">Tirage...</span>
                  <span v-else>Effectuer le tirage</span>
                </button>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                  :style="{ width: lottery.progress + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Draws Tab -->
    <div v-else-if="activeTab === 'active'">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Tirages spéciaux actifs</h3>
            <p class="text-sm text-gray-600">Tirages spéciaux en cours de vente</p>
          </div>
          <div class="flex space-x-2">
            <input
              v-model="activeFilters.search"
              type="text"
              placeholder="Rechercher..."
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produit
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Progression
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Revenus
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date de tirage
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Détails
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="lottery in filteredActiveLotteries"
                :key="lottery.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <img
                      :src="lottery.product?.image_url || lottery.product?.main_image || '/images/placeholder.jpg'"
                      :alt="lottery.product?.title || lottery.product?.name"
                      class="w-12 h-12 rounded-lg object-cover"
                    />
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ lottery.product?.title || lottery.product?.name }}</div>
                      <div class="text-sm text-gray-500">{{ formatAmount(lottery.product?.price || 0) }} FCFA</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ lottery.progress }}%</div>
                  <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                    <div
                      class="bg-blue-600 h-2 rounded-full"
                      :style="{ width: lottery.progress + '%' }"
                    ></div>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ lottery.sold_tickets || 0 }}/{{ lottery.total_tickets }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatAmount((lottery.sold_tickets || 0) * (lottery.ticket_price || 0)) }} FCFA
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(lottery.draw_date || lottery.end_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="viewParticipants(lottery)"
                    class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors"
                    title="Voir participants"
                  >
                    <UsersIcon class="w-4 h-4 mr-1" />
                    Participants
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Completed Draws Tab -->
    <div v-else-if="activeTab === 'completed'">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Tirages terminés</h3>
            <p class="text-sm text-gray-600">Historique des tombolas terminées</p>
          </div>
          <div class="flex space-x-2">
            <input
              v-model="completedFilters.search"
              type="text"
              placeholder="Rechercher..."
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <select
              v-model="completedFilters.period"
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Toutes les périodes</option>
              <option value="week">Cette semaine</option>
              <option value="month">Ce mois</option>
              <option value="quarter">Ce trimestre</option>
            </select>
          </div>
        </div>

        <div class="divide-y divide-gray-200">
          <div
            v-for="lottery in filteredCompletedLotteries"
            :key="lottery.id"
            class="p-6"
          >
            <div class="flex items-start justify-between">
              <div class="flex items-start space-x-4">
                <img
                  :src="lottery.product?.image_url || lottery.product?.main_image || '/images/placeholder.jpg'"
                  :alt="lottery.product?.title || lottery.product?.name"
                  class="w-16 h-16 rounded-lg object-cover"
                />
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">{{ lottery.product?.title || lottery.product?.name }}</h4>
                  <p class="text-sm text-gray-600">{{ lottery.product?.description }}</p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span class="text-sm text-gray-500">
                      Tirage effectué: {{ formatDate(lottery.draw_date || lottery.updated_at) }}
                    </span>
                    <span class="text-sm font-medium text-blue-600">
                      Revenus: {{ formatAmount((lottery.sold_tickets || 0) * (lottery.ticket_price || 0)) }} FCFA
                    </span>
                  </div>
                </div>
              </div>

              <div class="text-right">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                  <CheckCircleIcon class="w-4 h-4 mr-1" />
                  Terminé
                </span>
                <div class="mt-2 text-sm text-gray-600">
                  Gagnant: {{ lottery.winner ? `${lottery.winner.first_name} ${lottery.winner.last_name}` : 'Non réclamé' }}
                </div>
              </div>
            </div>

            <!-- Winner Details -->
            <div v-if="lottery.winner" class="mt-4 bg-green-50 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <TrophyIcon class="w-5 h-5 text-green-600 mr-2" />
                  <div>
                    <p class="font-semibold text-green-900">{{ lottery.winner.first_name }} {{ lottery.winner.last_name }}</p>
                    <p class="text-sm text-green-800">Ticket gagnant: #{{ lottery.winning_ticket_number || 'N/A' }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm text-green-800">{{ lottery.winner.email }}</p>
                  <p class="text-sm text-green-600">{{ lottery.winner.phone }}</p>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-2 mt-4">
              <button
                @click="viewDrawDetails(lottery)"
                class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800"
              >
                Voir détails
              </button>
              <button
                @click="exportResults(lottery)"
                class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800"
              >
                Exporter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Participants Modal -->
    <div v-if="showParticipantsModal" class="fixed inset-0 bg-gray-600 bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">
            Participants - {{ selectedLottery?.product?.title || selectedLottery?.product?.name }}
          </h3>
          <button
            @click="showParticipantsModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <div class="px-6 py-4">
          <div class="mb-4">
            <input
              v-model="participantsSearch"
              type="text"
              placeholder="Rechercher un participant..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Participant
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tickets
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total payé
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date d'achat
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="participant in filteredParticipants"
                  :key="participant.user?.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ participant.user?.first_name }} {{ participant.user?.last_name }}</div>
                      <div class="text-sm text-gray-500">{{ participant.user?.email }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ participant.tickets_count }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatAmount(participant.total_spent) }} FCFA
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(participant.tickets?.[0]?.purchased_at || participant.tickets?.[0]?.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Draw Confirmation Modal -->
    <div v-if="showDrawModal" class="fixed inset-0 bg-gray-600 bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Confirmer le tirage</h3>
        </div>

        <div class="px-6 py-4">
          <p class="text-gray-600 mb-4">
            Êtes-vous sûr de vouloir effectuer le tirage pour
            <span class="font-semibold">{{ selectedLottery?.product?.title || selectedLottery?.product?.name }}</span> ?
          </p>
          <div class="bg-gray-50 p-4 rounded-lg mb-4">
            <p class="text-sm text-gray-600">
              • {{ selectedLottery?.sold_tickets || 0 }} tickets vendus<br>
              • {{ selectedLottery?.participants_list?.length || 0 }} participants<br>
              • Revenus: {{ formatAmount((selectedLottery?.sold_tickets || 0) * (selectedLottery?.ticket_price || 0)) }} FCFA
            </p>
          </div>
          <p class="text-sm text-red-600">
            ⚠️ Cette action est irréversible. Le gagnant sera choisi aléatoirement.
          </p>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
          <button
            @click="showDrawModal = false"
            class="px-4 py-2 text-gray-600 hover:text-gray-900"
          >
            Annuler
          </button>
          <button
            @click="confirmDraw"
            :disabled="conductingDraw"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:bg-gray-400"
          >
            <span v-if="conductingDraw">Tirage en cours...</span>
            <span v-else>Effectuer le tirage</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue'
import {
  ClockIcon,
  TrophyIcon,
  CurrencyDollarIcon,
  ChartBarIcon,
  PencilIcon,
  UsersIcon,
  CheckCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import { useApi } from '@/composables/api'

const { get, post, put, delete: del } = useApi()

const loading = ref(false)
const activeTab = ref('pending')
const conductingDraw = ref(false)
const showParticipantsModal = ref(false)
const showDrawModal = ref(false)
const selectedLottery = ref(null)
const participantsSearch = ref('')
const statistics = ref({})
const lotteries = ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const itemsPerPage = ref(20)

const tabs = computed(() => [
  {
    key: 'pending',
    label: 'Tirages en attente',
    count: statistics.value.pending_draws || 0
  },
  {
    key: 'active',
    label: 'Tirages spéciaux actifs',
    count: statistics.value.active_lotteries || 0
  },
  {
    key: 'completed',
    label: 'Historique',
    count: statistics.value.completed_lotteries || 0
  }
])

const stats = computed(() => [
  {
    label: 'Tirages en attente',
    value: statistics.value.pending_draws || '0',
    color: 'bg-yellow-500',
    icon: ClockIcon
  },
  {
    label: 'Actives',
    value: statistics.value.active_lotteries || '0',
    color: 'bg-blue-500',
    icon: TrophyIcon
  },
  {
    label: 'Revenus totaux',
    value: formatAmount(statistics.value.total_revenue || 0) + ' FCFA',
    color: 'bg-green-500',
    icon: CurrencyDollarIcon
  },
  {
    label: 'Taux de participation',
    value: Math.round(statistics.value.average_participation_rate || 0) + '%',
    color: 'bg-purple-500',
    icon: ChartBarIcon
  }
])

const activeFilters = reactive({
  search: ''
})

const completedFilters = reactive({
  search: '',
  period: ''
})

// Computed values for different lottery states
const pendingDraws = computed(() => {
  return lotteries.value.filter(lottery =>
    lottery.status === 'active' &&
    !lottery.is_drawn &&
    new Date(lottery.end_date) <= new Date()
  )
})

const activeLotteries = computed(() => {
  return lotteries.value.filter(lottery =>
    lottery.status === 'active' &&
    new Date(lottery.end_date) > new Date()
  )
})

const completedLotteries = computed(() => {
  return lotteries.value.filter(lottery =>
    lottery.status === 'completed' || lottery.is_drawn
  )
})

const filteredActiveLotteries = computed(() => {
  let filtered = activeLotteries.value

  if (activeFilters.search) {
    const search = activeFilters.search.toLowerCase()
    filtered = filtered.filter(lottery =>
      lottery.product?.title?.toLowerCase().includes(search) ||
      lottery.product?.name?.toLowerCase().includes(search) ||
      lottery.product?.description?.toLowerCase().includes(search) ||
      lottery.lottery_number?.toLowerCase().includes(search)
    )
  }

  return filtered
})

const filteredCompletedLotteries = computed(() => {
  let filtered = completedLotteries.value

  if (completedFilters.search) {
    const search = completedFilters.search.toLowerCase()
    filtered = filtered.filter(lottery =>
      lottery.product?.title?.toLowerCase().includes(search) ||
      lottery.product?.name?.toLowerCase().includes(search) ||
      lottery.winner?.first_name?.toLowerCase().includes(search) ||
      lottery.winner?.last_name?.toLowerCase().includes(search) ||
      lottery.winner?.email?.toLowerCase().includes(search) ||
      lottery.lottery_number?.toLowerCase().includes(search)
    )
  }

  if (completedFilters.period) {
    const now = new Date()
    const period = completedFilters.period

    filtered = filtered.filter(lottery => {
      const drawDate = new Date(lottery.draw_date || lottery.created_at)

      if (period === 'week') {
        const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
        return drawDate >= weekAgo
      } else if (period === 'month') {
        const monthAgo = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate())
        return drawDate >= monthAgo
      } else if (period === 'quarter') {
        const quarterAgo = new Date(now.getFullYear(), now.getMonth() - 3, now.getDate())
        return drawDate >= quarterAgo
      }

      return true
    })
  }

  return filtered
})

const filteredParticipants = computed(() => {
  if (!selectedLottery.value?.participants_list) return []

  let filtered = selectedLottery.value.participants_list

  if (participantsSearch.value) {
    const search = participantsSearch.value.toLowerCase()
    filtered = filtered.filter(participant => {
      const user = participant.user
      const fullName = `${user?.first_name || ''} ${user?.last_name || ''}`.toLowerCase()
      return fullName.includes(search) ||
             user?.email?.toLowerCase().includes(search)
    })
  }

  return filtered
})

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

const formatAmount = (amount) => {
  return new Intl.NumberFormat('fr-FR').format(amount || 0)
}

// Watch activeTab to load appropriate data
watch(activeTab, () => {
  loadLotteries()
})

// API Functions
const loadStatistics = async () => {
  try {
    const response = await get('/admin/lotteries/statistics')
    if (response) {
      statistics.value = response
    }
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques:', error)
  }
}

const loadLotteries = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('per_page', itemsPerPage.value)
    params.append('page', currentPage.value)

    // Filter by tab
    if (activeTab.value === 'active') {
      params.append('status', 'active')
      params.append('is_drawn', 'false')
    } else if (activeTab.value === 'pending') {
      params.append('status', 'active')
      params.append('is_drawn', 'false')
      // Additional filter for pending draws can be added
    } else if (activeTab.value === 'completed') {
      params.append('is_drawn', 'true')
    }

    const response = await get(`/admin/lotteries?${params.toString()}`)

    if (response && response.data) {
      lotteries.value = response.data.map(lottery => ({
        ...lottery,
        progress: lottery.sold_tickets && lottery.total_tickets
          ? Math.round((lottery.sold_tickets / lottery.total_tickets) * 100)
          : 0
      }))
      currentPage.value = response.current_page || 1
      totalPages.value = response.last_page || 1
    }
  } catch (error) {
    console.error('Erreur lors du chargement des tombolas:', error)
    lotteries.value = []
  } finally {
    loading.value = false
  }
}

const refreshData = async () => {
  await Promise.all([
    loadStatistics(),
    loadLotteries()
  ])
}

const viewParticipants = async (lottery) => {
  try {
    const response = await get(`/admin/lotteries/${lottery.id}`)
    if (response) {
      selectedLottery.value = response
      showParticipantsModal.value = true
    }
  } catch (error) {
    console.error('Erreur lors du chargement des participants:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du chargement des participants', '✗ Erreur')
    }
  }
}

const conductDraw = (lottery) => {
  selectedLottery.value = lottery
  showDrawModal.value = true
}

const confirmDraw = async () => {
  conductingDraw.value = true

  try {
    const response = await post(`/admin/lotteries/${selectedLottery.value.id}/draw`)

    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Tirage effectué avec succès', '✓ Succès')
      }

      // Refresh data to update the lists
      await refreshData()

      // Switch to completed tab to show the result
      activeTab.value = 'completed'
    }
  } catch (error) {
    console.error('Erreur lors du tirage:', error)
    if (window.$toast) {
      const message = error.response?.data?.message || 'Erreur lors du tirage'
      window.$toast.error(message, '✗ Erreur')
    }
  } finally {
    conductingDraw.value = false
    showDrawModal.value = false
    selectedLottery.value = null
  }
}

const editLottery = (lottery) => {
  // TODO: Implement lottery editing modal
  console.log('Edit lottery:', lottery.id)
}

const viewDrawDetails = async (lottery) => {
  try {
    const response = await get(`/admin/lotteries/${lottery.id}`)
    if (response) {
      // TODO: Show detailed draw information modal
      console.log('Draw details:', response)
    }
  } catch (error) {
    console.error('Erreur lors du chargement des détails:', error)
  }
}

const exportResults = async (lottery) => {
  try {
    const response = await get(`/admin/lotteries/${lottery.id}/export`)
    if (response) {
      // TODO: Trigger file download
      const blob = new Blob([JSON.stringify(response, null, 2)], {
        type: 'application/json'
      })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `lottery-${lottery.lottery_number}-export.json`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      URL.revokeObjectURL(url)

      if (window.$toast) {
        window.$toast.success('Export téléchargé avec succès', '✓ Succès')
      }
    }
  } catch (error) {
    console.error('Erreur lors de l\'export:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de l\'export', '✗ Erreur')
    }
  }
}

const showEligibleDraws = async () => {
  try {
    const response = await get('/admin/lotteries/eligible-for-draw')
    if (response) {
      // TODO: Show eligible draws modal
      console.log('Eligible draws:', response)
      if (window.$toast) {
        window.$toast.info(`${response.count} tirages disponibles`, 'ℹ Info')
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des tirages disponibles:', error)
  }
}

onMounted(() => {
  loadStatistics()
  loadLotteries()
})
</script>
