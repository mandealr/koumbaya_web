<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Mes tickets</h1>
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
            <component :is="iconMap[stat.icon]" class="w-6 h-6 text-white" />
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
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select 
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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

    <!-- Error Message -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex">
        <ExclamationCircleIcon class="h-5 w-5 text-red-400" />
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">
            Erreur de chargement
          </h3>
          <div class="mt-2 text-sm text-red-700">
            <p>{{ error }}</p>
          </div>
          <div class="mt-4">
            <button
              @click="loadTickets()"
              class="text-sm bg-red-100 text-red-800 px-3 py-1 rounded-md hover:bg-red-200 transition-colors"
            >
              Réessayer
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tickets List -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Chargement de vos tickets...</p>
    </div>

    <div v-else-if="filteredTickets.length === 0" class="text-center py-12">
      <TicketIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ filteredTickets.length === 0 && !filters.search && !filters.status && !filters.period ? 'Aucun ticket acheté' : 'Aucun résultat trouvé' }}
      </h3>
      <p class="text-gray-600 mb-4">
        {{ filteredTickets.length === 0 && !filters.search && !filters.status && !filters.period
          ? 'Vous n\'avez pas encore participé à des tombolas'
          : 'Essayez de modifier vos filtres de recherche'
        }}
      </p>
      <router-link
        v-if="filteredTickets.length === 0 && !filters.search && !filters.status && !filters.period"
        to="/customer/products"
        class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors"
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
                  ticket.status === 'won' ? 'bg-blue-100 text-blue-800' :
                  ticket.status === 'lost' ? 'bg-red-100 text-red-800' :
                  ticket.status === 'active' ? 'bg-blue-100 text-blue-800' :
                  'bg-yellow-100 text-yellow-800'
                ]">
                  {{ getStatusLabel(ticket.status) }}
                </span>
              </div>

              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                  <p class="text-xs text-gray-500">Tickets achetés</p>
                  <p class="text-sm font-semibold text-gray-900">{{ ticket.quantity }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Total payé</p>
                  <p class="text-sm font-semibold text-gray-900">{{ formatCurrency(ticket.total_price) }}</p>
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
                <p class="text-xs text-gray-500 mb-2">Vos numéros de tickets</p>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="number in ticket.ticket_numbers"
                    :key="number"
                    :class="[
                      'px-2 py-1 text-xs font-mono rounded border',
                      ticket.winning_number === number ? 
                        'bg-blue-100 text-blue-800 border-blue-300' :
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
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                    :style="{ width: ticket.lottery.progress + '%' }"
                  ></div>
                </div>
              </div>

              <!-- Winning Details -->
              <div v-if="ticket.status === 'won'" class="bg-blue-50 p-4 rounded-lg mb-4">
                <div class="flex items-center mb-2">
                  <TrophyIcon class="w-5 h-5 text-blue-600 mr-2" />
                  <p class="font-semibold text-blue-900">Félicitations ! Vous avez gagné !</p>
                </div>
                <p class="text-sm text-blue-800">Numéro gagnant: #{{ ticket.winning_number }}</p>
                <p class="text-sm text-blue-800">Tirage effectué le {{ formatDate(ticket.lottery.draw_date) }}</p>
                <div v-if="!ticket.prize_claimed" class="mt-3">
                  <button
                    @click="handleClaimPrize(ticket.id)"
                    :disabled="loading"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{ loading ? 'Réclamation...' : 'Réclamer mon prix' }}
                  </button>
                </div>
                <div v-else class="mt-3">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
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
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                  >
                    Voir le produit
                  </router-link>
                  <button
                    @click="handleDownloadTicket(ticket.id)"
                    :disabled="loading"
                    class="text-sm text-gray-600 hover:text-gray-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{ loading ? 'Téléchargement...' : 'Télécharger' }}
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
        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100"
      >
        Charger plus de tickets
        <ArrowDownIcon class="ml-2 w-5 h-5" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import {
  TicketIcon,
  TrophyIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  ArrowDownIcon
} from '@heroicons/vue/24/outline'
import { useMyTickets } from '@/composables/useMyTickets'

const {
  // Data
  filteredTickets,
  stats,
  filters,
  
  // Pagination
  hasMore,
  
  // State
  loading,
  error,
  
  // Methods
  loadTickets,
  loadMore,
  claimPrize,
  downloadTicket,
  resetFilters,
  
  // Utilities
  formatCurrency,
  formatDate,
  getRemainingTime,
  getStatusLabel
} = useMyTickets()

// Map icon names to actual components for stats
const iconMap = {
  'TicketIcon': TicketIcon,
  'TrophyIcon': TrophyIcon,
  'CurrencyDollarIcon': CurrencyDollarIcon,
  'ClockIcon': ClockIcon
}

// Error handling wrappers
const handleClaimPrize = async (ticketId) => {
  try {
    await claimPrize(ticketId)
    // Show success message or notification here
    console.log('Prix réclamé avec succès')
  } catch (err) {
    // Show error message or notification here
    console.error('Erreur lors de la réclamation du prix:', err)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la réclamation du prix. Veuillez réessayer.', '❌ Erreur')
    }
  }
}

const handleDownloadTicket = async (ticketId) => {
  try {
    await downloadTicket(ticketId)
  } catch (err) {
    // Show error message or notification here
    console.error('Erreur lors du téléchargement:', err)
    if (window.$toast) {
      window.$toast.error('Erreur lors du téléchargement. Veuillez réessayer.', '❌ Erreur')
    }
  }
}

onMounted(() => {
  loadTickets()
})
</script>