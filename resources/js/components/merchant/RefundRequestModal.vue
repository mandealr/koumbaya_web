<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
    <div class="flex min-h-screen items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 transform transition-all">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Demander un Remboursement</h2>
          <button
            @click="close"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitRefund" class="space-y-6">
          <!-- Transaction Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Transaction à rembourser <span class="text-red-500">*</span>
            </label>

            <!-- Loading state -->
            <div v-if="loadingTransactions" class="text-center py-4">
              <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
              <p class="mt-2 text-sm text-gray-500">Chargement des transactions...</p>
            </div>

            <!-- Transaction list -->
            <div v-else-if="eligibleTransactions.length > 0" class="space-y-2">
              <div
                v-for="transaction in eligibleTransactions"
                :key="transaction.id"
                @click="selectTransaction(transaction)"
                :class="[
                  'border rounded-lg p-4 cursor-pointer transition-all',
                  selectedTransaction?.id === transaction.id
                    ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500'
                    : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50'
                ]"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <input
                        type="radio"
                        :checked="selectedTransaction?.id === transaction.id"
                        class="text-blue-600 focus:ring-blue-500"
                        @click.stop="selectTransaction(transaction)"
                      />
                      <div>
                        <p class="font-medium text-gray-900">
                          {{ transaction.lottery?.product?.name || 'Produit inconnu' }}
                        </p>
                        <p class="text-sm text-gray-500">
                          Tombola: {{ transaction.lottery?.lottery_number || 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-500">
                          Client: {{ transaction.user?.first_name }} {{ transaction.user?.last_name }}
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-gray-900">
                      {{ transaction.amount.toLocaleString('fr-FR') }} FCFA
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ formatDate(transaction.created_at) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Pagination for transactions -->
              <div v-if="eligibleTransactions.length >= 10" class="text-center pt-2">
                <button
                  type="button"
                  @click="loadMoreTransactions"
                  class="text-sm text-blue-600 hover:text-blue-700"
                >
                  Charger plus de transactions
                </button>
              </div>
            </div>

            <!-- Empty state -->
            <div v-else class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
              <CurrencyDollarIcon class="mx-auto h-12 w-12 text-gray-400" />
              <p class="mt-2 text-sm text-gray-600">Aucune transaction éligible au remboursement</p>
            </div>

            <p v-if="errors.transaction_id" class="mt-1 text-sm text-red-600">
              {{ errors.transaction_id }}
            </p>
          </div>

          <!-- Reason -->
          <div>
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
              Raison du remboursement <span class="text-red-500">*</span>
            </label>
            <select
              id="reason"
              v-model="formData.reason"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Sélectionner une raison</option>
              <option value="lottery_cancelled">Tombola annulée</option>
              <option value="insufficient_participants">Participation insuffisante</option>
              <option value="technical_issue">Problème technique</option>
              <option value="product_unavailable">Produit non disponible</option>
              <option value="customer_request">Demande du client</option>
              <option value="merchant_decision">Décision du marchand</option>
              <option value="other">Autre</option>
            </select>
            <p v-if="errors.reason" class="mt-1 text-sm text-red-600">
              {{ errors.reason }}
            </p>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
              Notes additionnelles
            </label>
            <textarea
              id="notes"
              v-model="formData.notes"
              rows="4"
              maxlength="1000"
              placeholder="Expliquez en détail la raison du remboursement..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            ></textarea>
            <div class="flex justify-between mt-1">
              <p class="text-xs text-gray-500">Optionnel mais recommandé</p>
              <p class="text-xs text-gray-500">{{ formData.notes.length }}/1000</p>
            </div>
          </div>

          <!-- Summary -->
          <div v-if="selectedTransaction" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="font-medium text-gray-900 mb-3">Récapitulatif</h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Transaction:</span>
                <span class="font-medium">{{ selectedTransaction.reference || selectedTransaction.id }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Client:</span>
                <span class="font-medium">
                  {{ selectedTransaction.user?.first_name }} {{ selectedTransaction.user?.last_name }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Montant à rembourser:</span>
                <span class="font-bold text-lg text-blue-600">
                  {{ selectedTransaction.amount.toLocaleString('fr-FR') }} FCFA
                </span>
              </div>
            </div>
          </div>

          <!-- Error message -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
              <ExclamationCircleIcon class="h-5 w-5 text-red-400 mr-2" />
              <p class="text-sm text-red-800">{{ errorMessage }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="close"
              class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="submitting || !selectedTransaction || !formData.reason"
              class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <span v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
              {{ submitting ? 'Envoi en cours...' : 'Soumettre la demande' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import {
  XMarkIcon,
  CurrencyDollarIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

// Props & Emits
const emit = defineEmits(['close', 'success'])

// Composables
const { get, post } = useApi()

// State
const eligibleTransactions = ref([])
const selectedTransaction = ref(null)
const loadingTransactions = ref(false)
const submitting = ref(false)
const errorMessage = ref('')
const errors = ref({})

const formData = ref({
  transaction_id: null,
  reason: '',
  notes: ''
})

// Methods
const loadEligibleTransactions = async () => {
  loadingTransactions.value = true
  try {
    const response = await get('/merchant/refunds/eligible-transactions')
    eligibleTransactions.value = response.transactions || []
  } catch (error) {
    console.error('Error loading eligible transactions:', error)
    errorMessage.value = 'Erreur lors du chargement des transactions éligibles'
  } finally {
    loadingTransactions.value = false
  }
}

const selectTransaction = (transaction) => {
  selectedTransaction.value = transaction
  formData.value.transaction_id = transaction.id
  errors.value.transaction_id = ''
}

const loadMoreTransactions = () => {
  // TODO: Implement pagination if needed
  console.log('Load more transactions')
}

const validateForm = () => {
  errors.value = {}

  if (!formData.value.transaction_id) {
    errors.value.transaction_id = 'Veuillez sélectionner une transaction'
    return false
  }

  if (!formData.value.reason) {
    errors.value.reason = 'Veuillez sélectionner une raison'
    return false
  }

  return true
}

const submitRefund = async () => {
  if (!validateForm()) {
    return
  }

  submitting.value = true
  errorMessage.value = ''

  try {
    const response = await post('/merchant/refunds', {
      transaction_id: formData.value.transaction_id,
      reason: formData.value.reason,
      notes: formData.value.notes
    })

    emit('success', response.refund)
  } catch (error) {
    console.error('Error creating refund request:', error)

    if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      errorMessage.value = 'Veuillez corriger les erreurs du formulaire'
    } else {
      errorMessage.value = 'Erreur lors de la création de la demande de remboursement'
    }
  } finally {
    submitting.value = false
  }
}

const close = () => {
  emit('close')
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadEligibleTransactions()
})
</script>
