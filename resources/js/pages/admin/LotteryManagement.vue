<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Gestion des tombolas</h1>
      <p class="mt-2 text-gray-600">Gérez les tirages et suivez les résultats des tombolas</p>
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
            <span v-if="tab.count" class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
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
            Tirages disponibles ({{ statistics.pending_draws }})
          </button>
          
          <button
            @click="refreshData"
            :disabled="loading"
            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 disabled:opacity-50"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Actualiser
          </button>
        </div>
        
        <div class="text-sm text-gray-500">
          Dernière mise à jour : {{ new Date().toLocaleTimeString('fr-FR') }}
        </div>
      </div>
    </div>

    <!-- Pending Draws Tab -->
    <div v-if="activeTab === 'pending'">
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
                  :src="lottery.product.image"
                  :alt="lottery.product.title"
                  class="w-16 h-16 rounded-lg object-cover"
                />
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">{{ lottery.product.title }}</h4>
                  <p class="text-sm text-gray-600">{{ lottery.product.description }}</p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span class="text-sm font-medium text-blue-600">
                      {{ lottery.sold_tickets }}/{{ lottery.total_tickets }} tickets vendus
                    </span>
                    <span class="text-sm text-gray-500">
                      Tirage prévu: {{ formatDate(lottery.draw_date) }}
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
    <div v-if="activeTab === 'active'">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Tombolas actives</h3>
            <p class="text-sm text-gray-600">Tombolas en cours de vente</p>
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
                  Actions
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
                      :src="lottery.product.image"
                      :alt="lottery.product.title"
                      class="w-12 h-12 rounded-lg object-cover"
                    />
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ lottery.product.title }}</div>
                      <div class="text-sm text-gray-500">{{ lottery.product.price }} FCFA</div>
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
                    {{ lottery.sold_tickets }}/{{ lottery.total_tickets }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ (lottery.sold_tickets * lottery.ticket_price).toLocaleString() }} FCFA
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(lottery.draw_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editLottery(lottery)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="viewParticipants(lottery)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      <UsersIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Completed Draws Tab -->
    <div v-if="activeTab === 'completed'">
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
                  :src="lottery.product.image"
                  :alt="lottery.product.title"
                  class="w-16 h-16 rounded-lg object-cover"
                />
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">{{ lottery.product.title }}</h4>
                  <p class="text-sm text-gray-600">{{ lottery.product.description }}</p>
                  <div class="flex items-center space-x-4 mt-2">
                    <span class="text-sm text-gray-500">
                      Tirage effectué: {{ formatDate(lottery.draw_date) }}
                    </span>
                    <span class="text-sm font-medium text-blue-600">
                      Revenus: {{ (lottery.sold_tickets * lottery.ticket_price).toLocaleString() }} FCFA
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="text-right">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                  <CheckCircleIcon class="w-4 h-4 mr-1" />
                  Terminé
                </span>
                <div class="mt-2 text-sm text-gray-600">
                  Gagnant: {{ lottery.winner?.name || 'Non réclamé' }}
                </div>
              </div>
            </div>
            
            <!-- Winner Details -->
            <div v-if="lottery.winner" class="mt-4 bg-blue-50 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <TrophyIcon class="w-5 h-5 text-blue-600 mr-2" />
                  <div>
                    <p class="font-semibold text-blue-900">{{ lottery.winner.name }}</p>
                    <p class="text-sm text-blue-800">Ticket gagnant: #{{ lottery.winning_ticket }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm text-blue-800">{{ lottery.winner.email }}</p>
                  <p class="text-sm text-blue-600">{{ lottery.winner.phone }}</p>
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
    <div v-if="showParticipantsModal" class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">
            Participants - {{ selectedLottery?.product.title }}
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
                  :key="participant.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ participant.name }}</div>
                      <div class="text-sm text-gray-500">{{ participant.email }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ participant.tickets_count }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ participant.total_spent }} FCFA
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(participant.purchase_date) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Draw Confirmation Modal -->
    <div v-if="showDrawModal" class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Confirmer le tirage</h3>
        </div>

        <div class="px-6 py-4">
          <p class="text-gray-600 mb-4">
            Êtes-vous sûr de vouloir effectuer le tirage pour 
            <span class="font-semibold">{{ selectedLottery?.product.title }}</span> ?
          </p>
          <div class="bg-gray-50 p-4 rounded-lg mb-4">
            <p class="text-sm text-gray-600">
              • {{ selectedLottery?.sold_tickets }} tickets vendus<br>
              • {{ selectedLottery?.participants?.length }} participants<br>
              • Revenus: {{ (selectedLottery?.sold_tickets * selectedLottery?.ticket_price || 0).toLocaleString() }} FCFA
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
import { ref, computed, reactive, onMounted } from 'vue'
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
import api from '@/composables/api'

const activeTab = ref('pending')
const conductingDraw = ref(false)
const showParticipantsModal = ref(false)
const showDrawModal = ref(false)
const selectedLottery = ref(null)
const participantsSearch = ref('')

const tabs = [
  { key: 'pending', label: 'Tirages en attente' },
  { key: 'active', label: 'Tombolas actives' },
  { key: 'completed', label: 'Historique' }
]

const stats = ref([
  {
    label: 'Tirages en attente',
    value: '3',
    color: 'bg-yellow-500',
    icon: ClockIcon
  },
  {
    label: 'Actives',
    value: '12',
    color: 'bg-blue-500',
    icon: TrophyIcon
  },
  {
    label: 'Revenus du mois',
    value: '850K FCFA',
    color: 'bg-blue-500',
    icon: CurrencyDollarIcon
  },
  {
    label: 'Taux de participation',
    value: '85%',
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

const pendingDraws = ref([
  {
    id: 1,
    product: {
      id: 1,
      title: 'iPhone 15 Pro',
      description: 'Le dernier flagship d\'Apple',
      price: 750000,
      image: '/images/products/iphone15.jpg'
    },
    total_tickets: 750,
    sold_tickets: 750,
    ticket_price: 1000,
    progress: 100,
    draw_date: new Date(),
    participants: Array.from({ length: 150 }, (_, i) => ({
      id: i + 1,
      name: `Utilisateur ${i + 1}`,
      email: `user${i + 1}@example.com`,
      tickets_count: Math.floor(Math.random() * 5) + 1,
      total_spent: (Math.floor(Math.random() * 5) + 1) * 1000,
      purchase_date: new Date(Date.now() - Math.random() * 30 * 24 * 60 * 60 * 1000)
    }))
  },
  {
    id: 2,
    product: {
      id: 2,
      title: 'PlayStation 5',
      description: 'Console de jeu nouvelle génération',
      price: 350000,
      image: '/images/products/ps5.jpg'
    },
    total_tickets: 700,
    sold_tickets: 700,
    ticket_price: 500,
    progress: 100,
    draw_date: new Date(Date.now() + 24 * 60 * 60 * 1000),
    participants: Array.from({ length: 200 }, (_, i) => ({
      id: i + 1,
      name: `Participant ${i + 1}`,
      email: `participant${i + 1}@example.com`,
      tickets_count: Math.floor(Math.random() * 3) + 1,
      total_spent: (Math.floor(Math.random() * 3) + 1) * 500,
      purchase_date: new Date(Date.now() - Math.random() * 15 * 24 * 60 * 60 * 1000)
    }))
  }
])

const activeLotteries = ref([
  {
    id: 3,
    product: {
      id: 3,
      title: 'MacBook Pro M3',
      description: 'Puissance et performance pour les créateurs',
      price: 1200000,
      image: '/images/products/macbook.jpg'
    },
    total_tickets: 600,
    sold_tickets: 372,
    ticket_price: 2000,
    progress: 62,
    draw_date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000)
  },
  {
    id: 4,
    product: {
      id: 4,
      title: 'AirPods Pro',
      description: 'Audio haute qualité avec réduction de bruit',
      price: 150000,
      image: '/images/products/airpods.jpg'
    },
    total_tickets: 500,
    sold_tickets: 225,
    ticket_price: 300,
    progress: 45,
    draw_date: new Date(Date.now() + 21 * 24 * 60 * 60 * 1000)
  }
])

const completedLotteries = ref([
  {
    id: 5,
    product: {
      id: 5,
      title: 'Samsung Galaxy S24',
      description: 'Smartphone Android premium',
      price: 650000,
      image: '/images/products/samsung.jpg'
    },
    total_tickets: 650,
    sold_tickets: 650,
    ticket_price: 1000,
    progress: 100,
    draw_date: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000),
    winner: {
      name: 'Marie Dubois',
      email: 'marie.dubois@example.com',
      phone: '+33 6 12 34 56 78'
    },
    winning_ticket: '001234'
  },
  {
    id: 6,
    product: {
      id: 6,
      title: 'Nintendo Switch OLED',
      description: 'Console de jeu portable',
      price: 200000,
      image: '/images/products/switch.jpg'
    },
    total_tickets: 400,
    sold_tickets: 400,
    ticket_price: 500,
    progress: 100,
    draw_date: new Date(Date.now() - 14 * 24 * 60 * 60 * 1000),
    winner: {
      name: 'Pierre Martin',
      email: 'pierre.martin@example.com',
      phone: '+33 6 98 76 54 32'
    },
    winning_ticket: '000987'
  }
])

const filteredActiveLotteries = computed(() => {
  let filtered = activeLotteries.value

  if (activeFilters.search) {
    const search = activeFilters.search.toLowerCase()
    filtered = filtered.filter(lottery => 
      lottery.product.title.toLowerCase().includes(search) ||
      lottery.product.description.toLowerCase().includes(search)
    )
  }

  return filtered
})

const filteredCompletedLotteries = computed(() => {
  let filtered = completedLotteries.value

  if (completedFilters.search) {
    const search = completedFilters.search.toLowerCase()
    filtered = filtered.filter(lottery => 
      lottery.product.title.toLowerCase().includes(search) ||
      lottery.winner?.name.toLowerCase().includes(search)
    )
  }

  if (completedFilters.period) {
    const now = new Date()
    const period = completedFilters.period
    
    filtered = filtered.filter(lottery => {
      const drawDate = new Date(lottery.draw_date)
      
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
  if (!selectedLottery.value?.participants) return []

  let filtered = selectedLottery.value.participants

  if (participantsSearch.value) {
    const search = participantsSearch.value.toLowerCase()
    filtered = filtered.filter(participant => 
      participant.name.toLowerCase().includes(search) ||
      participant.email.toLowerCase().includes(search)
    )
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

const viewParticipants = (lottery) => {
  selectedLottery.value = lottery
  showParticipantsModal.value = true
}

const conductDraw = (lottery) => {
  selectedLottery.value = lottery
  showDrawModal.value = true
}

const confirmDraw = async () => {
  conductingDraw.value = true
  
  try {
    await new Promise(resolve => setTimeout(resolve, 3000))
    
    // Simulate draw result
    const randomParticipant = selectedLottery.value.participants[
      Math.floor(Math.random() * selectedLottery.value.participants.length)
    ]
    
    const completedLottery = {
      ...selectedLottery.value,
      winner: randomParticipant,
      winning_ticket: String(Math.floor(Math.random() * 999999)).padStart(6, '0')
    }
    
    // Move to completed
    completedLotteries.value.unshift(completedLottery)
    
    // Remove from pending
    const index = pendingDraws.value.findIndex(l => l.id === selectedLottery.value.id)
    if (index !== -1) {
      pendingDraws.value.splice(index, 1)
    }
    
    console.log('Draw completed:', completedLottery)
    
  } catch (error) {
    console.error('Error conducting draw:', error)
  } finally {
    conductingDraw.value = false
    showDrawModal.value = false
    selectedLottery.value = null
  }
}

const editLottery = (lottery) => {
  console.log('Edit lottery:', lottery.id)
}

const viewDrawDetails = (lottery) => {
  console.log('View draw details:', lottery.id)
}

const exportResults = (lottery) => {
  console.log('Export results:', lottery.id)
}

onMounted(() => {
  // Load lottery data
  console.log('Lottery management mounted')
})
</script>