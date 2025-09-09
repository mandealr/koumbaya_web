<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-gray-600 bg-opacity-40 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="koumbaya-card inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle bg-white shadow-xl transform transition-all">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">
            {{ product.id ? 'Modifier l\'article' : 'Nouvel article' }}
          </h3>
        </div>

        <form @submit.prevent="submit" class="koumbaya-card-body space-y-4">
          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Nom de l'article</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="koumbaya-input"
              placeholder="iPhone 15 Pro"
            />
          </div>

          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="koumbaya-input"
              placeholder="Description du produit..."
            ></textarea>
          </div>

          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Prix du produit (FCFA)</label>
            <input
              v-model.number="form.value"
              @input="calculateTicketPrice"
              type="number"
              required
              class="koumbaya-input"
              placeholder="750000"
            />
          </div>

          <!-- Calcul automatique du prix du ticket -->
          <div v-if="calculation" class="bg-[#0099cc]/5 border border-[#0099cc]/20 rounded-xl p-4 space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="font-semibold text-[#0099cc] flex items-center">
                <CurrencyDollarIcon class="w-4 h-4 mr-2" />
                Calcul automatique du ticket
              </h4>
              <span class="text-xs text-gray-500">
                {{ form.numberOfTickets || 1000 }} tickets
              </span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Prix article:</span>
                <span class="font-medium ml-2">{{ formatCurrency(calculation.product_price) }}</span>
              </div>
              <div>
                <span class="text-gray-600">Commission ({{ (calculation.commission_rate * 100) }}%):</span>
                <span class="font-medium ml-2 text-[#0099cc]">{{ formatCurrency(calculation.commission_amount) }}</span>
              </div>
              <div>
                <span class="text-gray-600">Marge ({{ (calculation.margin_rate * 100) }}%):</span>
                <span class="font-medium ml-2 text-[#0099cc]">{{ formatCurrency(calculation.margin_amount) }}</span>
              </div>
              <div>
                <span class="text-gray-600">Total à récolter:</span>
                <span class="font-medium ml-2">{{ formatCurrency(calculation.total_amount) }}</span>
              </div>
            </div>

            <div class="border-t pt-3 flex items-center justify-between">
              <div>
                <span class="text-gray-600">Prix du ticket:</span>
                <span class="text-xl font-bold text-[#0099cc] ml-2">
                  {{ formatCurrency(calculation.final_ticket_price) }}
                </span>
              </div>
              <div class="text-right text-xs text-gray-500">
                <div>Revenus potentiels: {{ formatCurrency(calculation.total_potential_revenue) }}</div>
                <div>Profit Koumbaya: {{ formatCurrency(calculation.koumbaya_profit) }}</div>
              </div>
            </div>

            <!-- Nombre de tickets personnalisable -->
            <div v-if="showAdvanced && canCustomizeTickets" class="border-t pt-3">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nombre de tickets (optionnel)
              </label>
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="ticketCount in [500, 1000, 1500]"
                  :key="ticketCount"
                  type="button"
                  @click="updateTicketCount(ticketCount)"
                  :class="[
                    'px-3 py-2 text-sm border rounded-lg transition-colors',
                    form.numberOfTickets === ticketCount
                      ? 'border-[#0099cc] bg-[#0099cc] text-white'
                      : 'border-gray-300 hover:border-[#0099cc]'
                  ]"
                >
                  {{ ticketCount }}
                </button>
              </div>
              <input
                v-model.number="form.numberOfTickets"
                @input="calculateTicketPrice"
                type="number"
                min="100"
                max="5000"
                class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                placeholder="Nombre personnalisé..."
              />
            </div>

            <!-- Message pour les vendeurs individuels -->
            <div v-else-if="showAdvanced && !canCustomizeTickets" class="border-t pt-3">
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-yellow-800">
                      <strong>Profil Vendeur Individuel:</strong> Le nombre de tickets est fixé à {{ form.numberOfTickets }} pour garantir un prix de ticket minimum de 200 FCFA.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <button
              v-if="canCustomizeTickets"
              type="button"
              @click="showAdvanced = !showAdvanced"
              class="text-xs text-[#0099cc] hover:text-[#0088bb] transition-colors"
            >
              {{ showAdvanced ? '▼ Masquer les options' : '▶ Options avancées' }}
            </button>
            <div v-else class="text-xs text-gray-500">
              Options limitées pour profil vendeur individuel
            </div>
          </div>

          <!-- Prix ticket (lecture seule, calculé automatiquement) -->
          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Prix du ticket (calculé automatiquement)</label>
            <input
              :value="calculation ? formatCurrency(calculation.final_ticket_price) : ''"
              type="text"
              readonly
              class="koumbaya-input bg-gray-100 cursor-not-allowed"
              placeholder="Saisissez d'abord le prix du produit"
            />
            <input
              v-model.number="form.ticketPrice"
              type="hidden"
            />
          </div>

          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Catégorie</label>
            <select v-model="form.category" required class="koumbaya-input">
              <option value="">Sélectionner une catégorie</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <p class="text-xs text-gray-500 mt-1">Debug Modal: {{ categories.length }} catégories</p>
          </div>

          <div class="koumbaya-card-footer">
            <div class="flex gap-4">
              <button
                type="button"
                @click="close"
                class="koumbaya-btn koumbaya-btn-outline flex-1"
              >
                Annuler
              </button>
              <button
                type="submit"
                class="koumbaya-btn koumbaya-btn-primary flex-1"
              >
                {{ product.id ? 'Modifier' : 'Créer' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, ref, computed, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import { useAuth } from '@/stores/auth'
import { CurrencyDollarIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  product: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'submit'])

const { get, post } = useApi()
const { user, isIndividualSeller } = useAuth()
const calculation = ref(null)
const categories = ref([])
const showAdvanced = ref(false)
// Supprimé car on utilise maintenant les rôles

const form = reactive({
  name: '',
  description: '',
  value: '',
  ticketPrice: '',
  category: '',
  numberOfTickets: 500  // Changé à 500 par défaut
})

watch(() => props.product, (newProduct) => {
  if (newProduct) {
    form.name = newProduct.name || ''
    form.description = newProduct.description || ''
    form.value = newProduct.value || ''
    form.ticketPrice = newProduct.ticketPrice || ''
    form.category = newProduct.category || ''
    form.numberOfTickets = newProduct.min_participants || getDefaultTicketCount()
  }
}, { immediate: true })

// Calcul automatique du prix du ticket
const calculateTicketPrice = async () => {
  if (!form.value || form.value <= 0) {
    calculation.value = null
    form.ticketPrice = ''
    return
  }

  try {
    const response = await post('/calculate-ticket-price', {
      product_price: form.value,
      number_of_tickets: form.numberOfTickets || getDefaultTicketCount()
    })

    if (response.success) {
      calculation.value = response.data
      form.ticketPrice = response.data.final_ticket_price
    }
  } catch (error) {
    console.error('Erreur lors du calcul du prix du ticket:', error)
    calculation.value = null
    form.ticketPrice = ''
  }
}

// Mettre à jour le nombre de tickets
const updateTicketCount = (count) => {
  form.numberOfTickets = count
  calculateTicketPrice()
}

// Formatage des devises
const formatCurrency = (amount) => {
  if (typeof amount !== 'number') return '0 FCFA'
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount) + ' FCFA'
}

const close = () => {
  emit('close')
}

const submit = () => {
  // S'assurer que le prix du ticket est inclus dans les données
  const submitData = {
    ...form,
    price: form.value, // Renommer 'value' en 'price' pour l'API
    ticket_price: form.ticketPrice,
    min_participants: form.numberOfTickets || getDefaultTicketCount()
  }

  emit('submit', submitData)
}

// Load categories
const loadCategories = async () => {
  try {
    console.log('Loading categories in modal...')
    const response = await get('/categories')
    console.log('Modal categories response:', response)
    if (response && response.categories) {
      categories.value = response.categories
      console.log('Modal categories loaded:', categories.value.length, 'items')
    } else if (response && response.data) {
      categories.value = response.data
      console.log('Modal categories loaded from data:', categories.value.length, 'items')
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

// Calcul initial si le produit a déjà un prix
watch(() => form.value, () => {
  if (form.value && form.value > 0) {
    calculateTicketPrice()
  }
}, { immediate: true })

// Helpers pour les profils vendeurs
const canCustomizeTickets = computed(() => {
  return !isIndividualSeller.value
})

const getDefaultTicketCount = () => {
  return 500 // 500 par défaut pour tous
}

const getMinProductPrice = () => {
  return isIndividualSeller.value ? 100000 : null
}

// Validation du prix minimum selon le profil
watch(() => form.value, (newValue) => {
  const minPrice = getMinProductPrice()
  if (minPrice && newValue && newValue < minPrice) {
    // Afficher un avertissement mais ne pas bloquer
    console.warn(`Prix minimum recommandé: ${formatCurrency(minPrice)} pour profil individuel`)
  }

  // Forcer les tickets à 500 pour les vendeurs individuels
  if (isIndividualSeller.value) {
    form.numberOfTickets = 500
  }

  if (newValue && newValue > 0) {
    calculateTicketPrice()
  }
})

// Initialisation du nombre de tickets selon le profil
watch(() => isIndividualSeller.value, (isIndividual) => {
  if (isIndividual) {
    form.numberOfTickets = 500
    calculateTicketPrice()
  }
}, { immediate: true })

onMounted(() => {
  loadCategories()

  // Configurer le nombre de tickets selon le profil au montage
  if (isIndividualSeller.value) {
    form.numberOfTickets = 500
  }
})
</script>
