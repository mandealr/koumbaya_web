<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Mes Tombolas</h1>
        <p class="mt-2 text-gray-600">Gérez vos tombolas et effectuez les tirages</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <router-link 
          to="/merchant/products/create"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
        >
          Nouvelle tombola
        </router-link>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white p-4 rounded-lg shadow">
      <div class="flex flex-col sm:flex-row gap-4">
        <!-- Search Bar -->
        <div class="flex-1">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Rechercher par numéro, nom ou description..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Sort By -->
        <div class="sm:w-48">
          <select
            v-model="sortBy"
            @change="updateSort"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="end_date_asc">Date de fin (croissant)</option>
            <option value="end_date_desc">Date de fin (décroissant)</option>
            <option value="popularity">Popularité</option>
            <option value="ticket_price_asc">Prix ticket (croissant)</option>
            <option value="ticket_price_desc">Prix ticket (décroissant)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
        <div class="text-sm text-gray-600">Total</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-green-600">{{ stats.active }}</div>
        <div class="text-sm text-gray-600">Actives</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</div>
        <div class="text-sm text-gray-600">En attente</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-blue-600">{{ stats.completed }}</div>
        <div class="text-sm text-gray-600">Terminées</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-red-600">{{ stats.cancelled }}</div>
        <div class="text-sm text-gray-600">Annulées</div>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="changeTab(tab.key)"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === tab.key
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          {{ tab.label }}
          <span v-if="tab.count !== null" class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
            {{ tab.count }}
          </span>
        </button>
      </nav>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
      <div class="p-6">
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Chargement des tombolas...</p>
        </div>

        <div v-else-if="lotteries.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune tombola</h3>
          <p class="text-gray-600 mb-4">Vous n'avez pas encore créé de tombolas.</p>
          <router-link 
            to="/merchant/products/create"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
          >
            Créer ma première tombola
          </router-link>
        </div>

        <div v-else>
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div
              v-for="lottery in lotteries"
              :key="lottery.id"
              class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
            >
              <!-- Image du produit -->
              <div class="h-48 bg-gray-200 relative">
                <ProductImage 
                  :src="lottery.product?.main_image || lottery.product?.image_url || lottery.product?.image" 
                  :alt="lottery.product?.name"
                  container-class="h-48 w-full"
                  image-class="w-full h-full object-cover"
                  fallback-class="h-48 w-full"
                  fallback-text="Photo"
                />
                
                <!-- Badge de statut -->
                <div class="absolute top-2 right-2">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getFormattedStatus(lottery.status).class]">
                    {{ getFormattedStatus(lottery.status).label }}
                  </span>
                </div>
              </div>
              
              <!-- Contenu de la carte -->
              <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                  <h4 class="font-semibold text-gray-900 truncate">{{ lottery.product?.name || 'Produit sans nom' }}</h4>
                  <span class="text-sm text-gray-600 ml-2">#{{ lottery.lottery_number }}</span>
                </div>
                
                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ lottery.product?.description }}</p>
                
                <!-- Informations de la tombola -->
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Prix du ticket:</span>
                    <span class="font-medium">{{ lottery.ticket_price }} XAF</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-gray-600">Tickets vendus:</span>
                    <span class="font-medium">{{ lottery.sold_tickets || 0 }}/{{ lottery.total_tickets }}</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-gray-600">Temps restant:</span>
                    <span class="font-medium" :class="{ 'text-red-600': getTimeRemaining(lottery.end_date) === 'Terminée' }">
                      {{ getTimeRemaining(lottery.end_date) }}
                    </span>
                  </div>
                  
                  <!-- Barre de progression -->
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      class="bg-blue-600 h-2 rounded-full" 
                      :style="{ width: `${Math.min(((lottery.sold_tickets || 0) / lottery.total_tickets) * 100, 100)}%` }"
                    ></div>
                  </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2 mt-4">
                  <router-link 
                    :to="{ name: 'merchant.lottery.view', params: { id: lottery.id } }"
                    class="flex-1 text-center px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-100 transition-colors"
                  >
                    Détails
                  </router-link>
                  
                  <button
                    v-if="canDrawLottery(lottery)"
                    @click="performDraw(lottery)"
                    :disabled="drawingLottery === lottery.id"
                    class="flex-1 px-3 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400 transition-colors"
                  >
                    {{ drawingLottery === lottery.id ? 'Tirage...' : 'Effectuer le tirage' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Pagination -->
          <div v-if="pagination.last_page > 1" class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-700">
              Page {{ pagination.current_page }} sur {{ pagination.last_page }} 
              ({{ pagination.total }} résultats)
            </div>
            <div class="flex gap-2">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                Précédent
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                Suivant
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmation de tirage -->
    <div v-if="showDrawModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmer le tirage</h3>
        <p class="text-sm text-gray-600 mb-6">
          Êtes-vous sûr de vouloir effectuer le tirage pour la tombola 
          <strong>{{ selectedLottery?.product?.name }}</strong> ?
          Cette action est irréversible.
        </p>
        <div class="flex gap-3 justify-end">
          <button
            @click="showDrawModal = false"
            class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-100"
          >
            Annuler
          </button>
          <button
            @click="confirmDraw"
            :disabled="drawingLottery"
            class="px-4 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400"
          >
            {{ drawingLottery ? 'Tirage...' : 'Confirmer le tirage' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast notifications -->
    <div v-if="showToast" class="fixed bottom-4 right-4 z-50">
      <div :class="['rounded-md p-4 shadow-lg', toastType === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200']">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg v-if="toastType === 'success'" class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <svg v-else class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <div class="ml-3">
            <p :class="['text-sm', toastType === 'success' ? 'text-green-800' : 'text-red-800']">
              {{ toastMessage }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useMerchantLotteries } from '../../composables/useMerchantLotteries.js'
import ProductImage from '@/components/common/ProductImage.vue'

// Composable pour la gestion des tombolas
const {
  lotteries,
  stats,
  tabs,
  pagination,
  loading,
  error,
  fetchLotteries,
  drawLottery,
  updateFilters,
  searchLotteries,
  changePage,
  canDrawLottery,
  getFormattedStatus,
  getTimeRemaining
} = useMerchantLotteries()

// État local pour l'interface
const activeTab = ref('all')
const searchQuery = ref('')
const sortBy = ref('end_date_asc')
const showDrawModal = ref(false)
const selectedLottery = ref(null)
const drawingLottery = ref(null)

// État pour les notifications toast
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success') // 'success' ou 'error'

// Fonction de debounce pour la recherche
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchLotteries(searchQuery.value)
  }, 500)
}

// Changer d'onglet
const changeTab = (tabKey) => {
  activeTab.value = tabKey
  updateFilters({ 
    status: tabKey === 'all' ? null : tabKey,
    page: 1 
  })
}

// Mettre à jour le tri
const updateSort = () => {
  updateFilters({ 
    sortBy: sortBy.value,
    page: 1 
  })
}

// Afficher le modal de confirmation de tirage
const performDraw = (lottery) => {
  selectedLottery.value = lottery
  showDrawModal.value = true
}

// Confirmer et effectuer le tirage
const confirmDraw = async () => {
  if (!selectedLottery.value) return
  
  drawingLottery.value = selectedLottery.value.id
  
  try {
    const result = await drawLottery(selectedLottery.value.id)
    
    showToast.value = true
    toastType.value = 'success'
    toastMessage.value = `Tirage effectué avec succès ! Gagnant : ${result.winning_ticket?.user?.first_name} ${result.winning_ticket?.user?.last_name}`
    
    // Fermer le modal
    showDrawModal.value = false
    selectedLottery.value = null
    
    // Masquer le toast après 5 secondes
    setTimeout(() => {
      showToast.value = false
    }, 5000)
    
  } catch (err) {
    console.error('Erreur lors du tirage:', err)
    
    showToast.value = true
    toastType.value = 'error'
    toastMessage.value = err.response?.data?.error || 'Erreur lors du tirage'
    
    // Masquer le toast après 5 secondes
    setTimeout(() => {
      showToast.value = false
    }, 5000)
  } finally {
    drawingLottery.value = null
  }
}

// Watcher pour synchroniser l'onglet actif avec les filtres
watch(() => activeTab.value, (newTab) => {
  if (newTab !== 'all') {
    updateFilters({ status: newTab })
  } else {
    updateFilters({ status: null })
  }
})

// Charger les données au montage
onMounted(async () => {
  try {
    await fetchLotteries()
  } catch (err) {
    console.error('Erreur lors du chargement initial:', err)
    showToast.value = true
    toastType.value = 'error'
    toastMessage.value = 'Erreur lors du chargement des tombolas'
    
    setTimeout(() => {
      showToast.value = false
    }, 5000)
  }
})
</script>