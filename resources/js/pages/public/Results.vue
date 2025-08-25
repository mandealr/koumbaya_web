<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-600 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
          <h1 class="text-4xl font-bold mb-4">🏆 Résultats des Tombolas</h1>
          <p class="text-xl opacity-90 mb-8">
            Découvrez les gagnants et vérifiez les résultats officiels
          </p>
          
          <!-- Quick Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <div class="bg-white/20 rounded-lg p-4">
              <div class="text-2xl font-bold">{{ stats.total_lotteries || 0 }}</div>
              <div class="text-sm opacity-90">Tombolas terminées</div>
            </div>
            <div class="bg-white/20 rounded-lg p-4">
              <div class="text-2xl font-bold">{{ stats.total_winners || 0 }}</div>
              <div class="text-sm opacity-90">Gagnants heureux</div>
            </div>
            <div class="bg-white/20 rounded-lg p-4">
              <div class="text-2xl font-bold">{{ formatCurrencyShort(stats.total_prizes_value) }}</div>
              <div class="text-sm opacity-90">Valeur des prix</div>
            </div>
            <div class="bg-white/20 rounded-lg p-4">
              <div class="text-2xl font-bold">{{ stats.total_tickets_sold || 0 }}</div>
              <div class="text-sm opacity-90">Tickets vendus</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Period Selector -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Filtrer par période</h2>
            <p class="text-gray-600">Choisissez la période à afficher</p>
          </div>
          <div class="flex space-x-2">
            <button
              v-for="period in periods"
              :key="period.value"
              @click="selectedPeriod = period.value; loadResults()"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                selectedPeriod === period.value
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
              ]"
            >
              {{ period.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Verification Tool -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Vérifier un Résultat</h3>
        <div class="flex gap-4">
          <input
            v-model="verificationCode"
            type="text"
            placeholder="Entrez le code de vérification..."
            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            @keyup.enter="verifyCode"
          />
          <button
            @click="verifyCode"
            :disabled="!verificationCode || verifying"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ verifying ? 'Vérification...' : 'Vérifier' }}
          </button>
        </div>
        
        <!-- Verification Result -->
        <div v-if="verificationResult" class="mt-4 p-4 rounded-lg" :class="[
          verificationResult.valid ? 'bg-blue-50 border border-blue-200' : 'bg-red-50 border border-red-200'
        ]">
          <div v-if="verificationResult.valid" class="flex items-center text-blue-800">
            <CheckCircleIcon class="w-5 h-5 mr-2" />
            <div>
              <div class="font-medium">✅ Résultat vérifié</div>
              <div class="text-sm mt-1">
                Tombola {{ verificationResult.lottery_number }} - {{ verificationResult.product_title }}
                <br>Gagnant: {{ verificationResult.winner_initial }}**** le {{ formatDate(verificationResult.draw_date) }}
              </div>
            </div>
          </div>
          <div v-else class="flex items-center text-red-800">
            <XCircleIcon class="w-5 h-5 mr-2" />
            <div class="font-medium">Code invalide</div>
          </div>
        </div>
      </div>

      <!-- Recent Winners -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">🎉 Gagnants Récents</h3>
        </div>
        <div v-if="loadingRecentWinners" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        </div>
        <div v-else class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="winner in recentWinners"
              :key="winner.lottery_number"
              class="bg-gradient-to-r from-blue-50 to-blue-50 rounded-lg p-4 border border-blue-100"
            >
              <div class="flex items-center space-x-4">
                <img
                  :src="winner.product_image || '/images/products/placeholder.jpg'"
                  :alt="winner.product_title"
                  class="w-16 h-16 rounded-lg object-cover"
                />
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ winner.product_title }}</div>
                  <div class="text-sm text-gray-600 mb-1">{{ formatCurrency(winner.product_value) }}</div>
                  <div class="text-sm font-medium text-blue-600">
                    Gagnant: {{ winner.winner_initial }}**** ({{ winner.winner_city }})
                  </div>
                  <div class="text-xs text-gray-500">
                    Il y a {{ winner.days_ago }} jour{{ winner.days_ago > 1 ? 's' : '' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Results Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">📋 Historique Complet des Résultats</h3>
        </div>
        
        <div v-if="loading" class="p-8 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Chargement des résultats...</p>
        </div>

        <div v-else-if="results.length === 0" class="p-8 text-center text-gray-500">
          <GiftIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun résultat</h3>
          <p>Aucune tombola terminée pour cette période.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produit
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Gagnant
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ticket Gagnant
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Participation
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date de Tirage
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Vérification
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="result in results" :key="result.lottery_id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <ProductImage 
                      :src="result.product.image_url || result.product.image" 
                      :alt="result.product.title"
                      container-class="w-12 h-12 rounded-lg mr-4"
                      image-class="w-12 h-12 rounded-lg object-cover"
                      fallback-class="w-12 h-12 rounded-lg"
                      fallback-text="Photo"
                    />
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ result.product.title }}</div>
                      <div class="text-sm text-gray-500">
                        {{ result.product.category?.name || result.product.category || 'Sans catégorie' }} • {{ formatCurrency(result.product.price) }}
                      </div>
                      <div class="text-xs text-gray-600">{{ result.lottery_number }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                      {{ result.winner.initial }}
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ result.winner.name }}</div>
                      <div class="text-sm text-gray-500">{{ result.winner.city }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-mono font-medium text-gray-900">
                    {{ result.winning_ticket }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ result.sold_tickets }}/{{ result.total_tickets }} tickets
                  </div>
                  <div class="w-16 bg-gray-200 rounded-full h-1.5 mt-1">
                    <div
                      class="bg-blue-600 h-1.5 rounded-full"
                      :style="{ width: result.participation_rate + '%' }"
                    ></div>
                  </div>
                  <div class="text-xs text-gray-500">{{ result.participation_rate }}%</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(result.draw_date) }}</div>
                  <div class="text-xs text-gray-500">
                    {{ formatCurrency(result.total_revenue) }} collectés
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-xs font-mono text-gray-600 bg-gray-100 rounded px-2 py-1">
                    {{ result.verification_code }}
                  </div>
                  <button
                    @click="copyVerificationCode(result.verification_code)"
                    class="text-xs text-blue-600 hover:text-blue-700 mt-1"
                  >
                    Copier
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import ProductImage from '@/components/common/ProductImage.vue'
import {
  GiftIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const { get } = useApi()

// Data
const results = ref([])
const recentWinners = ref([])
const stats = ref({})
const loading = ref(false)
const loadingRecentWinners = ref(false)
const selectedPeriod = ref('month')

// Verification
const verificationCode = ref('')
const verifying = ref(false)
const verificationResult = ref(null)

// Periods
const periods = [
  { value: 'week', label: 'Cette semaine' },
  { value: 'month', label: 'Ce mois' },
  { value: 'year', label: 'Cette année' }
]

// Methods
const loadResults = async () => {
  loading.value = true
  try {
    const response = await get(`/public/results?period=${selectedPeriod.value}&limit=50`)
    results.value = response.data.results || []
    stats.value = response.data.stats || {}
  } catch (error) {
    console.error('Erreur lors du chargement des résultats:', error)
  } finally {
    loading.value = false
  }
}

const loadRecentWinners = async () => {
  loadingRecentWinners.value = true
  try {
    const response = await get('/public/results/recent-winners?limit=6')
    recentWinners.value = response.data.winners || []
  } catch (error) {
    console.error('Erreur lors du chargement des gagnants récents:', error)
  } finally {
    loadingRecentWinners.value = false
  }
}

const verifyCode = async () => {
  if (!verificationCode.value.trim()) return
  
  verifying.value = true
  verificationResult.value = null
  
  try {
    const response = await get(`/public/results/verify/${verificationCode.value.trim()}`)
    verificationResult.value = response.data
    verificationResult.value.valid = true
  } catch (error) {
    verificationResult.value = { valid: false }
  } finally {
    verifying.value = false
  }
}

const copyVerificationCode = async (code) => {
  try {
    await navigator.clipboard.writeText(code)
    // Vous pourriez ajouter une notification toast ici
    if (window.$toast) {
      window.$toast.success('Code copié dans le presse-papiers!', '✅ Copié')
    }
  } catch (error) {
    console.error('Erreur lors de la copie:', error)
  }
}

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const formatCurrencyShort = (amount) => {
  if (!amount) return '0'
  
  if (amount >= 1000000) {
    return (amount / 1000000).toFixed(1) + 'M'
  } else if (amount >= 1000) {
    return (amount / 1000).toFixed(0) + 'K'
  }
  return amount.toString()
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

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadResults(),
    loadRecentWinners()
  ])
})
</script>