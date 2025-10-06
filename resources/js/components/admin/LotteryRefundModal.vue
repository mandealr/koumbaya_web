<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Détails du remboursement</h2>
            <p class="text-sm text-gray-500 mt-1">{{ lottery.lottery_number }} - {{ lottery.product_title }}</p>
          </div>
          <button
            @click="close"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
          <!-- Summary Card -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-6 border border-blue-200">
            <div class="grid grid-cols-3 gap-4">
              <div>
                <p class="text-sm text-blue-700 font-medium mb-1">Total participants</p>
                <p class="text-3xl font-bold text-blue-900">{{ payments.length }}</p>
              </div>
              <div>
                <p class="text-sm text-blue-700 font-medium mb-1">Montant total collecté</p>
                <p class="text-3xl font-bold text-blue-900">{{ formatCurrency(totalCollected) }}</p>
              </div>
              <div>
                <p class="text-sm text-blue-700 font-medium mb-1">Montant à rembourser</p>
                <p class="text-3xl font-bold text-green-700">{{ formatCurrency(totalToRefund) }}</p>
              </div>
            </div>
          </div>

          <!-- Info Alert -->
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
            <div class="flex">
              <ExclamationTriangleIcon class="h-5 w-5 text-amber-400 mr-3 flex-shrink-0 mt-0.5" />
              <div class="text-sm text-amber-800">
                <p class="font-medium mb-1">Calcul du remboursement</p>
                <p>Le montant remboursé correspond au prix du ticket payé par le client, <strong>sans les frais Koumbaya et la marge du marchand</strong>.</p>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loadingPayments" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Chargement des paiements...</p>
          </div>

          <!-- Payments List -->
          <div v-else-if="payments.length > 0" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Liste des paiements à rembourser</h3>

            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant payé</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">À rembourser</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                      <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                          <UserIcon class="h-4 w-4 text-blue-600" />
                        </div>
                        <div>
                          <p class="text-sm font-medium text-gray-900">
                            {{ payment.user?.first_name }} {{ payment.user?.last_name }}
                          </p>
                          <p class="text-xs text-gray-500">{{ payment.user?.email }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <p class="text-sm font-mono text-gray-900">{{ payment.reference || 'N/A' }}</p>
                      <p class="text-xs text-gray-500">{{ formatDate(payment.created_at) }}</p>
                    </td>
                    <td class="px-4 py-3">
                      <p class="text-sm text-gray-900">{{ payment.user?.phone || 'N/A' }}</p>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <p class="text-sm font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</p>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <p class="text-sm font-bold text-green-700">{{ formatCurrency(calculateRefundAmount(payment)) }}</p>
                      <p v-if="payment.amount !== calculateRefundAmount(payment)" class="text-xs text-gray-500">
                        -{{ formatCurrency(payment.amount - calculateRefundAmount(payment)) }} frais
                      </p>
                    </td>
                  </tr>
                </tbody>
                <tfoot class="bg-gray-100">
                  <tr>
                    <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-900">Total</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ formatCurrency(totalCollected) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-green-700">{{ formatCurrency(totalToRefund) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <BanknotesIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
            <p class="text-gray-600">Aucun paiement trouvé pour cette tombola</p>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
              <ExclamationCircleIcon class="h-5 w-5 text-red-400 mr-2" />
              <p class="text-sm text-red-800">{{ errorMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3">
          <button
            @click="close"
            class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-medium transition-colors"
          >
            Annuler
          </button>
          <button
            @click="confirmRefund"
            :disabled="processing || payments.length === 0"
            class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <span v-if="processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            <BanknotesIcon v-else class="h-5 w-5" />
            {{ processing ? 'Traitement en cours...' : `Rembourser ${payments.length} paiement(s)` }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import {
  XMarkIcon,
  UserIcon,
  BanknotesIcon,
  ExclamationTriangleIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

// Props & Emits
const props = defineProps({
  lottery: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'success'])

// Composables
const { get, post } = useApi()

// State
const payments = ref([])
const loadingPayments = ref(false)
const processing = ref(false)
const errorMessage = ref('')

// Computed
const totalCollected = computed(() => {
  return payments.value.reduce((sum, payment) => sum + parseFloat(payment.amount || 0), 0)
})

const totalToRefund = computed(() => {
  return payments.value.reduce((sum, payment) => sum + calculateRefundAmount(payment), 0)
})

// Methods
const loadPayments = async () => {
  loadingPayments.value = true
  errorMessage.value = ''

  try {
    // Charger tous les paiements complétés pour cette tombola
    const response = await get(`/admin/lotteries/${props.lottery.id}/payments`)

    if (response && response.success) {
      payments.value = response.payments || response.data || []
    } else {
      throw new Error(response.message || 'Erreur lors du chargement des paiements')
    }
  } catch (error) {
    console.error('Error loading payments:', error)
    errorMessage.value = error.response?.data?.message || 'Erreur lors du chargement des paiements'
  } finally {
    loadingPayments.value = false
  }
}

const calculateRefundAmount = (payment) => {
  // Le montant à rembourser = montant payé - (frais Koumbaya + marge marchand)
  // Pour simplifier, on rembourse le prix du ticket sans les frais additionnels
  // Normalement le ticket_price devrait être stocké dans le payment ou calculé
  const ticketPrice = parseFloat(props.lottery.ticket_price || 0)
  return ticketPrice
}

const confirmRefund = async () => {
  if (!confirm(`Confirmer le remboursement de ${payments.value.length} paiement(s) pour un total de ${formatCurrency(totalToRefund.value)} ?`)) {
    return
  }

  processing.value = true
  errorMessage.value = ''

  try {
    const response = await post('/admin/refunds/process-automatic', {
      lottery_id: props.lottery.id,
      dry_run: false
    })

    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success(
          `${payments.value.length} remboursement(s) traité(s) avec succès`,
          '✅ Remboursement'
        )
      }
      emit('success', response)
      close()
    } else {
      throw new Error(response.message || 'Erreur lors du traitement')
    }
  } catch (error) {
    console.error('Error processing refunds:', error)
    errorMessage.value = error.response?.data?.message || 'Erreur lors du traitement des remboursements'

    if (window.$toast) {
      window.$toast.error(errorMessage.value, '✗ Erreur')
    }
  } finally {
    processing.value = false
  }
}

const close = () => {
  emit('close')
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
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
  loadPayments()
})
</script>
