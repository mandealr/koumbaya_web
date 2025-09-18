<template>
  <Teleport to="body">
    <transition
      enter-active-class="ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/40"></div>
        
        <div class="relative min-h-full flex items-center justify-center p-4">
          <transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div v-if="show" class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-4">
              <!-- Loading State -->
              <div v-if="isLoading" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-xl z-10">
                <div class="text-center">
                  <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto mb-4"></div>
                  <p class="text-gray-600">Annulation en cours...</p>
                </div>
              </div>

              <!-- Error State -->
              <div v-if="error" class="p-6">
                <div class="flex items-center mb-4">
                  <div class="flex-shrink-0">
                    <XCircleIcon class="h-6 w-6 text-red-600" />
                  </div>
                  <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Erreur</h3>
                  </div>
                </div>
                <div class="mb-4">
                  <p class="text-sm text-gray-600">{{ error }}</p>
                </div>
                <div class="flex justify-end space-x-3">
                  <button
                    @click="closeModal"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                  >
                    Fermer
                  </button>
                  <button
                    @click="retryCancel"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                  >
                    Réessayer
                  </button>
                </div>
              </div>

              <!-- Success State -->
              <div v-else-if="success" class="p-6">
                <div class="flex items-center mb-4">
                  <div class="flex-shrink-0">
                    <CheckCircleIcon class="h-6 w-6 text-green-600" />
                  </div>
                  <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Annulation réussie</h3>
                  </div>
                </div>
                <div class="mb-4">
                  <p class="text-sm text-gray-600">
                    La tombola a été annulée avec succès. Une demande de remboursement manuel a été créée et nécessite une validation par un administrateur.
                  </p>
                </div>
                <div class="flex justify-end">
                  <button
                    @click="closeModal"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                  >
                    Fermer
                  </button>
                </div>
              </div>

              <!-- Confirmation State -->
              <div v-else class="p-6">
                <div class="flex items-center mb-4">
                  <div class="flex-shrink-0">
                    <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
                  </div>
                  <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Annuler la tombola</h3>
                  </div>
                </div>

                <div class="mb-6">
                  <p class="text-sm text-gray-600 mb-4">
                    Êtes-vous sûr de vouloir annuler cette tombola ? Cette action est irréversible.
                  </p>
                  
                  <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400" />
                      </div>
                      <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800 mb-2">Conséquences de l'annulation :</h4>
                        <ul class="text-sm text-yellow-700 space-y-1">
                          <li>• La tombola sera marquée comme annulée</li>
                          <li>• Une demande de remboursement manuel sera créée</li>
                          <li>• Les participants seront remboursés après validation admin</li>
                          <li>• Aucun nouveau ticket ne pourra être vendu</li>
                        </ul>
                      </div>
                    </div>
                  </div>

                  <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Informations de la tombola :</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                      <p><span class="font-medium">Titre :</span> {{ lottery.title }}</p>
                      <p><span class="font-medium">Tickets vendus :</span> {{ lottery.sold_tickets || 0 }}</p>
                      <p><span class="font-medium">Participants :</span> {{ lottery.participants_count || 0 }}</p>
                      <p><span class="font-medium">Revenus générés :</span> {{ formatCurrency(lottery.total_revenue || 0) }}</p>
                    </div>
                  </div>
                </div>

                <div class="mb-6">
                  <label for="cancellation-reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Motif d'annulation <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="cancellation-reason"
                    v-model="cancellationReason"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    required
                  >
                    <option value="">Sélectionnez un motif</option>
                    <option value="technical_issue">Problème technique</option>
                    <option value="insufficient_participation">Participation insuffisante</option>
                    <option value="merchant_request">Demande du marchand</option>
                    <option value="product_unavailable">Produit non disponible</option>
                    <option value="other">Autre</option>
                  </select>
                </div>

                <div v-if="cancellationReason === 'other'" class="mb-6">
                  <label for="custom-reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Motif personnalisé <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    id="custom-reason"
                    v-model="customReason"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="Veuillez préciser le motif d'annulation..."
                    required
                  ></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                  <button
                    @click="closeModal"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"
                    :disabled="isLoading"
                  >
                    Annuler
                  </button>
                  <button
                    @click="confirmCancel"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
                    :disabled="isLoading || !isFormValid"
                  >
                    Confirmer l'annulation
                  </button>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useApi } from '@/composables/api'
import {
  ExclamationTriangleIcon,
  XCircleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  lottery: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'cancelled'])

const { post } = useApi()

const isLoading = ref(false)
const error = ref('')
const success = ref(false)
const cancellationReason = ref('')
const customReason = ref('')

const isFormValid = computed(() => {
  if (!cancellationReason.value) return false
  if (cancellationReason.value === 'other' && !customReason.value.trim()) return false
  return true
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const closeModal = () => {
  if (success.value) {
    emit('cancelled')
  } else {
    emit('close')
  }
  resetForm()
}

const resetForm = () => {
  cancellationReason.value = ''
  customReason.value = ''
  error.value = ''
  success.value = false
  isLoading.value = false
}

const confirmCancel = async () => {
  if (!isFormValid.value) return

  isLoading.value = true
  error.value = ''

  try {
    const reason = cancellationReason.value === 'other' ? customReason.value : cancellationReason.value

    const response = await post(`/lotteries/${props.lottery.id}/cancel`, {
      reason: reason,
      reason_type: cancellationReason.value
    })

    if (response.success) {
      success.value = true
      if (window.$toast) {
        window.$toast.success('Tombola annulée avec succès. Demande de remboursement créée.')
      }
    } else {
      throw new Error(response.message || 'Erreur lors de l\'annulation')
    }
  } catch (err) {
    console.error('Erreur lors de l\'annulation:', err)
    error.value = err.response?.data?.message || err.message || 'Une erreur est survenue lors de l\'annulation'
    if (window.$toast) {
      window.$toast.error(error.value)
    }
  } finally {
    isLoading.value = false
  }
}

const retryCancel = () => {
  error.value = ''
  confirmCancel()
}

watch(() => props.show, (newValue) => {
  if (newValue) {
    resetForm()
  }
})
</script>