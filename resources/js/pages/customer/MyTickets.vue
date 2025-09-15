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
            <option value="reserved">Réservé</option>
            <option value="paid">Payé</option>
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

    <!-- Table Controls -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-700">Afficher</span>
          <select 
            :value="perPage"
            @change="changePerPage(parseInt($event.target.value))"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
          <span class="text-sm text-gray-700">entrées</span>
        </div>
        <div v-if="total > 0" class="text-sm text-gray-700">
          Affichage de {{ from }} à {{ to }} sur {{ total }} tickets
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Chargement de vos tickets...</p>
      </div>

      <!-- Empty State -->
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

      <!-- Data Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'achat</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tirage</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="ticket in filteredTickets" 
              :key="ticket.id"
              class="hover:bg-gray-50"
            >
              <!-- Product -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-12 w-12">
                    <img
                      class="h-12 w-12 rounded-lg object-cover"
                      :src="ticket.product.image_url || ticket.product.main_image || ticket.product.image"
                      :alt="ticket.product.title"
                    />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ ticket.product.title }}</div>
                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ ticket.product.description }}</div>
                  </div>
                </div>
              </td>
              
              <!-- Ticket Number -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-wrap gap-1">
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
              </td>
              
              <!-- Price -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(ticket.total_price) }}
              </td>
              
              <!-- Status -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  ticket.status === 'won' ? 'bg-blue-100 text-blue-800' :
                  ticket.status === 'lost' ? 'bg-red-100 text-red-800' :
                  ticket.status === 'active' ? 'bg-blue-100 text-blue-800' :
                  ticket.status === 'reserved' ? 'bg-orange-100 text-orange-800' :
                  ticket.status === 'paid' ? 'bg-green-100 text-green-800' :
                  'bg-yellow-100 text-yellow-800'
                ]">
                  {{ getStatusLabel(ticket.status) }}
                </span>
                
                <!-- Win/Loss Details -->
                <div v-if="ticket.status === 'won'" class="mt-1">
                  <div class="flex items-center text-xs text-blue-600">
                    <TrophyIcon class="w-3 h-3 mr-1" />
                    <span>Gagnant #{{ ticket.winning_number }}</span>
                  </div>
                  <div v-if="!ticket.prize_claimed" class="mt-1">
                    <button
                      @click="handleClaimPrize(ticket.id)"
                      :disabled="loading"
                      class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition-colors disabled:opacity-50"
                    >
                      Réclamer
                    </button>
                  </div>
                  <div v-else class="mt-1">
                    <span class="inline-flex items-center text-xs text-blue-600">
                      <CheckCircleIcon class="w-3 h-3 mr-1" />
                      Réclamé
                    </span>
                  </div>
                </div>
                
                <div v-if="ticket.status === 'lost'" class="mt-1 text-xs text-red-600">
                  Gagnant: #{{ ticket.winning_number }}
                </div>
              </td>
              
              <!-- Purchase Date -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(ticket.purchased_at) }}
              </td>
              
              <!-- Draw Date -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(ticket.lottery.draw_date) }}
                <div class="text-xs text-gray-500">
                  {{ getRemainingTime(ticket.lottery.draw_date, ticket.status) }}
                </div>
              </td>
              
              <!-- Actions -->
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <router-link
                    :to="`/customer/products/${ticket.product.id}`"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Voir
                  </router-link>
                  <button
                    @click="handleDownloadTicket(ticket.id)"
                    :disabled="loading"
                    class="text-gray-600 hover:text-gray-900 disabled:opacity-50"
                  >
                    PDF
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <div v-if="!loading && filteredTickets.length > 0 && lastPage > 1" class="px-6 py-4 border-t border-gray-200 bg-white">
        <div class="flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <!-- Mobile pagination -->
            <button
              @click="previousPage"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Précédent
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage === lastPage"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Suivant
            </button>
          </div>
          
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Affichage de
                <span class="font-medium">{{ from }}</span>
                à
                <span class="font-medium">{{ to }}</span>
                sur
                <span class="font-medium">{{ total }}</span>
                résultats
              </p>
            </div>
            
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Previous Button -->
                <button
                  @click="previousPage"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="sr-only">Précédent</span>
                  <ChevronLeftIcon class="h-5 w-5" />
                </button>
                
                <!-- Page Numbers -->
                <button
                  v-for="page in getPageNumbers()"
                  :key="page"
                  @click="goToPage(page)"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    page === currentPage
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
                
                <!-- Next Button -->
                <button
                  @click="nextPage"
                  :disabled="currentPage === lastPage"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="sr-only">Suivant</span>
                  <ChevronRightIcon class="h-5 w-5" />
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>\n    </div>\n  </div>\n</template>

<script setup>
import { onMounted } from 'vue'
import {
  TicketIcon,
  TrophyIcon,
  CurrencyDollarIcon,
  ClockIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  ArrowDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'
import { useMyTickets } from '@/composables/useMyTickets'

const {
  // Data
  filteredTickets,
  stats,
  filters,
  
  // Pagination
  currentPage,
  lastPage,
  total,
  from,
  to,
  perPage,
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
  goToPage,
  changePerPage,
  nextPage,
  previousPage,
  getPageNumbers,
  
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
      window.$toast.error('Erreur lors de la réclamation du prix. Veuillez réessayer.', 'Erreur')
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
      window.$toast.error('Erreur lors du téléchargement. Veuillez réessayer.', 'Erreur')
    }
  }
}

onMounted(() => {
  loadTickets()
})
</script>