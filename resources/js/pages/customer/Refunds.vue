<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">💸 Mes Remboursements</h1>
            <p class="text-gray-600 mt-1">Gérez vos demandes de remboursement</p>
          </div>
          <button
            @click="showCreateRefund = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            ➕ Nouvelle demande
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
              💰
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900">{{ stats.total_refunds || 0 }}</div>
              <div class="text-sm text-gray-600">Remboursements</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
              ✅
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900">{{ formatCurrencyShort(stats.total_amount_refunded) }}</div>
              <div class="text-sm text-gray-600">Montant remboursé</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
              ⏳
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900">{{ stats.pending_refunds || 0 }}</div>
              <div class="text-sm text-gray-600">En attente</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
              X
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900">{{ stats.rejected_refunds || 0 }}</div>
              <div class="text-sm text-gray-600">Rejetées</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="flex items-center space-x-4">
            <select
              v-model="selectedStatus"
              @change="loadRefunds"
              class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="">Tous les statuts</option>
              <option value="pending">En attente</option>
              <option value="approved">Approuvé</option>
              <option value="processed">Traité</option>
              <option value="completed">Terminé</option>
              <option value="rejected">Rejeté</option>
            </select>

            <button
              @click="resetFilters"
              class="px-3 py-2 text-gray-600 hover:text-gray-800 text-sm"
            >
              Réinitialiser
            </button>
          </div>

          <div class="text-sm text-gray-500">
            {{ refunds.length }} remboursement(s)
          </div>
        </div>
      </div>

      <!-- Refunds List -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Chargement des remboursements...</p>
      </div>

      <div v-else-if="refunds.length === 0" class="text-center py-12">
        <BanknotesIcon class="mx-auto h-16 w-16 text-gray-400 mb-4" />
        <h3 class="text-xl font-medium text-gray-900 mb-2">Aucun remboursement</h3>
        <p class="text-gray-600 mb-6">Vous n'avez pas encore de demande de remboursement.</p>
        <button
          @click="showCreateRefund = true"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Faire une demande
        </button>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="refund in refunds"
          :key="refund.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-2">
                <span class="font-mono text-sm text-gray-600">{{ refund.refund_number }}</span>
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="getStatusClass(refund.status)"
                >
                  {{ getStatusText(refund.status) }}
                </span>
                <span v-if="refund.auto_processed" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                  🤖 Automatique
                </span>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                  <div class="text-sm text-gray-600">Montant</div>
                  <div class="text-lg font-semibold text-gray-900">{{ formatCurrency(refund.amount) }}</div>
                </div>

                <div>
                  <div class="text-sm text-gray-600">Raison</div>
                  <div class="text-sm font-medium">{{ getReasonText(refund.reason) }}</div>
                </div>

                <div>
                  <div class="text-sm text-gray-600">Date de demande</div>
                  <div class="text-sm">{{ formatDate(refund.created_at) }}</div>
                </div>
              </div>

              <div v-if="refund.lottery" class="bg-gray-50 rounded-lg p-3 mb-4">
                <div class="text-sm text-gray-600">Tombola concernée</div>
                <div class="font-medium">{{ refund.lottery.lottery_number }} - {{ refund.lottery.product_title }}</div>
              </div>

              <div v-if="refund.rejection_reason" class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                <div class="text-sm font-medium text-red-800">Raison du rejet</div>
                <div class="text-sm text-red-700 mt-1">{{ refund.rejection_reason }}</div>
              </div>

              <div v-if="refund.notes" class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <div class="text-sm font-medium text-blue-800">Notes</div>
                <div class="text-sm text-blue-700 mt-1">{{ refund.notes }}</div>
              </div>
            </div>

            <div class="flex space-x-2 ml-4">
              <button
                @click="viewDetails(refund)"
                class="px-3 py-2 text-blue-600 hover:text-blue-700 text-sm font-medium"
              >
                Détails
              </button>

              <button
                v-if="refund.status === 'pending'"
                @click="cancelRefund(refund)"
                class="px-3 py-2 text-red-600 hover:text-red-700 text-sm font-medium"
              >
                Annuler
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Refund Modal -->
    <div v-if="showCreateRefund" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/20" @click="closeCreateRefund"></div>
        <div class="relative bg-white rounded-lg max-w-lg w-full">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold">Nouvelle demande de remboursement</h3>
              <button @click="closeCreateRefund" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>

            <form @submit.prevent="createRefund">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Transaction à rembourser</label>
                  <select
                    v-model="newRefund.transaction_id"
                    required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option value="">Sélectionnez une transaction</option>
                    <option v-for="transaction in eligibleTransactions" :key="transaction.id" :value="transaction.id">
                      {{ transaction.reference }} - {{ formatCurrency(transaction.amount) }}
                      {{ transaction.lottery ? `(${transaction.lottery.lottery_number})` : '' }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Raison du remboursement</label>
                  <select
                    v-model="newRefund.reason"
                    required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option value="">Sélectionnez une raison</option>
                    <option value="accidental_purchase">Achat accidentel</option>
                    <option value="changed_mind">Changement d'avis</option>
                    <option value="duplicate_purchase">Achat en double</option>
                    <option value="technical_issue">Problème technique</option>
                    <option value="other">Autre</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Explication (optionnel)</label>
                  <textarea
                    v-model="newRefund.notes"
                    rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Décrivez votre situation..."
                  ></textarea>
                </div>
              </div>

              <div class="flex justify-end space-x-3 mt-6">
                <button
                  type="button"
                  @click="closeCreateRefund"
                  class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  :disabled="creating"
                  class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                  {{ creating ? 'Création...' : 'Créer la demande' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="selectedRefund" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/20" @click="selectedRefund = null"></div>
        <div class="relative bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold">Détails du remboursement</h3>
              <button @click="selectedRefund = null" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>

            <div class="space-y-6">
              <!-- Status and Basic Info -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-600">Numéro</div>
                  <div class="font-mono font-medium">{{ selectedRefund.refund_number }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Statut</div>
                  <span
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                    :class="getStatusClass(selectedRefund.status)"
                  >
                    {{ getStatusText(selectedRefund.status) }}
                  </span>
                </div>
              </div>

              <!-- Amount and dates -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-600">Montant</div>
                  <div class="text-xl font-semibold text-blue-600">{{ formatCurrency(selectedRefund.amount) }}</div>
                </div>
                <div>
                  <div class="text-sm text-gray-600">Méthode de remboursement</div>
                  <div>{{ getRefundMethodText(selectedRefund.refund_method) }}</div>
                </div>
              </div>

              <!-- Transaction Info -->
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">Transaction originale</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <div class="text-sm text-gray-600">Référence</div>
                    <div class="font-mono">{{ selectedRefund.transaction.reference }}</div>
                  </div>
                  <div>
                    <div class="text-sm text-gray-600">Montant</div>
                    <div>{{ formatCurrency(selectedRefund.transaction.amount) }}</div>
                  </div>
                </div>
              </div>

              <!-- Lottery Info if available -->
              <div v-if="selectedRefund.lottery" class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-3">Tombola concernée</h4>
                <div>
                  <div class="text-sm text-gray-600">Numéro</div>
                  <div>{{ selectedRefund.lottery.lottery_number }}</div>
                </div>
                <div class="mt-2">
                  <div class="text-sm text-gray-600">Produit</div>
                  <div>{{ selectedRefund.lottery.product_title }}</div>
                </div>
              </div>

              <!-- Timeline -->
              <div>
                <h4 class="font-medium text-gray-900 mb-3">Chronologie</h4>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Demande créée</span>
                    <span class="text-sm">{{ formatDate(selectedRefund.created_at) }}</span>
                  </div>
                  <div v-if="selectedRefund.approved_at" class="flex justify-between">
                    <span class="text-sm text-gray-600">Approuvé</span>
                    <span class="text-sm">{{ formatDate(selectedRefund.approved_at) }}</span>
                  </div>
                  <div v-if="selectedRefund.processed_at" class="flex justify-between">
                    <span class="text-sm text-gray-600">Traité</span>
                    <span class="text-sm">{{ formatDate(selectedRefund.processed_at) }}</span>
                  </div>
                  <div v-if="selectedRefund.rejected_at" class="flex justify-between">
                    <span class="text-sm text-gray-600">Rejeté</span>
                    <span class="text-sm">{{ formatDate(selectedRefund.rejected_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import {
  BanknotesIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const { get, post } = useApi()

// Data
const refunds = ref([])
const stats = ref({})
const loading = ref(false)
const selectedStatus = ref('')
const showCreateRefund = ref(false)
const creating = ref(false)
const selectedRefund = ref(null)
const eligibleTransactions = ref([])

// New refund form
const newRefund = ref({
  transaction_id: '',
  reason: '',
  notes: ''
})

// Methods
const loadRefunds = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({ limit: 50 })
    if (selectedStatus.value) {
      params.append('status', selectedStatus.value)
    }

    const response = await get(`/refunds?${params}`)
    refunds.value = response.data.refunds || []
    stats.value = response.data.stats || {}
  } catch (error) {
    console.error('Error loading refunds:', error)
  } finally {
    loading.value = false
  }
}

const loadEligibleTransactions = async () => {
  try {
    // Load completed transactions that don't have refunds yet
    const response = await get('/transactions?status=completed&limit=100')
    eligibleTransactions.value = response.data.transactions?.filter(
      transaction => !transaction.has_refund
    ) || []
  } catch (error) {
    console.error('Error loading transactions:', error)
  }
}

const createRefund = async () => {
  creating.value = true
  try {
    const response = await post('/refunds', newRefund.value)

    // Add new refund to list
    refunds.value.unshift(response.data.refund)

    // Update stats
    stats.value.total_refunds = (stats.value.total_refunds || 0) + 1
    stats.value.pending_refunds = (stats.value.pending_refunds || 0) + 1

    // Reset form and close modal
    newRefund.value = { transaction_id: '', reason: '', notes: '' }
    showCreateRefund.value = false

    // Show success message
    if (window.$toast) {
      window.$toast.success('Demande de remboursement créée avec succès!', '✅ Demande créée')
    }

  } catch (error) {
    console.error('Error creating refund:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la création de la demande', '❌ Erreur')
    }
  } finally {
    creating.value = false
  }
}

const cancelRefund = async (refund) => {
  if (!confirm('Êtes-vous sûr de vouloir annuler cette demande ?')) return

  try {
    await post(`/refunds/${refund.id}/cancel`)

    // Update refund in list
    const index = refunds.value.findIndex(r => r.id === refund.id)
    if (index !== -1) {
      refunds.value[index].status = 'rejected'
      refunds.value[index].rejection_reason = 'Annulé par l\'utilisateur'
    }

    if (window.$toast) {
      window.$toast.success('Demande annulée avec succès', '✅ Annulation')
    }

  } catch (error) {
    console.error('Error cancelling refund:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de l\'annulation', '❌ Erreur')
    }
  }
}

const viewDetails = (refund) => {
  selectedRefund.value = refund
}

const closeCreateRefund = () => {
  showCreateRefund.value = false
  newRefund.value = { transaction_id: '', reason: '', notes: '' }
}

const resetFilters = () => {
  selectedStatus.value = ''
  loadRefunds()
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

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    processed: 'bg-purple-100 text-purple-800',
    completed: 'bg-blue-100 text-blue-800',
    rejected: 'bg-red-100 text-red-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: '⏳ En attente',
    approved: '✅ Approuvé',
    processed: '🔄 En traitement',
    completed: '✅ Terminé',
    rejected: '❌ Rejeté',
    failed: '❌ Échec'
  }
  return texts[status] || status
}

const getReasonText = (reason) => {
  const texts = {
    insufficient_participants: 'Participants insuffisants',
    lottery_cancelled: 'Tombola annulée',
    accidental_purchase: 'Achat accidentel',
    changed_mind: 'Changement d\'avis',
    duplicate_purchase: 'Achat en double',
    technical_issue: 'Problème technique',
    system_error: 'Erreur système',
    other: 'Autre'
  }
  return texts[reason] || reason
}

const getRefundMethodText = (method) => {
  const texts = {
    mobile_money: 'Mobile Money',
    bank_transfer: 'Virement bancaire',
    wallet_credit: 'Crédit portefeuille'
  }
  return texts[method] || method
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadRefunds(),
    loadEligibleTransactions()
  ])
})
</script>
