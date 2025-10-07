<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black/40 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Détails du remboursement</h2>
            <p class="text-sm text-gray-500 mt-1">{{ refund.refund_number }}</p>
          </div>
          <button
            @click="close"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-180px)] space-y-6">
          <!-- Status & Amount Card -->
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-blue-700 font-medium mb-1">Montant du remboursement</p>
                <p class="text-4xl font-bold text-blue-900">{{ formatCurrency(refund.amount) }}</p>
              </div>
              <div class="flex flex-col items-end gap-2">
                <span :class="getStatusBadgeClass(refund.status)" class="px-4 py-2 text-sm font-medium rounded-full">
                  {{ getStatusText(refund.status) }}
                </span>
                <span v-if="refund.auto_processed" class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                  Automatique
                </span>
              </div>
            </div>
          </div>

          <!-- Client Information -->
          <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <UserIcon class="h-5 w-5 text-blue-600" />
              Informations du client
            </h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-gray-500">Nom complet</p>
                <p class="font-medium text-gray-900">
                  {{ refund.user?.first_name }} {{ refund.user?.last_name }}
                </p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Email</p>
                <p class="text-sm text-gray-900">{{ refund.user?.email || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Téléphone</p>
                <p class="text-sm text-gray-900">{{ refund.user?.phone || 'N/A' }}</p>
              </div>
              <div v-if="refund.meta?.order_number">
                <p class="text-xs text-gray-500">Numéro de commande</p>
                <p class="text-sm font-mono text-gray-900">{{ refund.meta.order_number }}</p>
              </div>
            </div>
          </div>

          <!-- Lottery & Transaction Information -->
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
              <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <TicketIcon class="h-5 w-5 text-blue-600" />
                Tombola
              </h3>
              <div class="space-y-2">
                <div>
                  <p class="text-xs text-gray-500">Numéro de tombola</p>
                  <p class="font-medium text-gray-900">{{ refund.lottery?.lottery_number || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Produit</p>
                  <p class="text-sm text-gray-900">{{ refund.lottery?.product?.name || 'N/A' }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-4">
              <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <CreditCardIcon class="h-5 w-5 text-blue-600" />
                Transaction
              </h3>
              <div class="space-y-2">
                <div>
                  <p class="text-xs text-gray-500">Référence</p>
                  <p class="font-medium text-gray-900 font-mono">{{ refund.transaction?.reference || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Montant original</p>
                  <p class="text-sm text-gray-900">{{ formatCurrency(refund.meta?.original_amount || refund.amount) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Refund Method & Details -->
          <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <ArrowPathIcon class="h-5 w-5 text-blue-600" />
              Méthode de remboursement
            </h3>
            <div class="grid grid-cols-3 gap-4">
              <div>
                <p class="text-xs text-gray-500">Méthode</p>
                <p class="font-medium text-gray-900">{{ getRefundMethodText(refund.refund_method) }}</p>
              </div>
              <div v-if="refund.external_refund_id">
                <p class="text-xs text-gray-500">ID SHAP Payout</p>
                <p class="text-sm font-mono text-gray-900">{{ refund.external_refund_id }}</p>
              </div>
              <div v-if="refund.meta?.operator">
                <p class="text-xs text-gray-500">Opérateur</p>
                <p class="text-sm text-gray-900">{{ refund.meta.operator }}</p>
              </div>
            </div>
          </div>

          <!-- Koumbaya Fees (if present) -->
          <div v-if="refund.meta?.koumbaya_fees" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-amber-900 mb-3 flex items-center gap-2">
              <BanknotesIcon class="h-5 w-5 text-amber-600" />
              Frais Koumbaya
            </h3>
            <div class="grid grid-cols-3 gap-4">
              <div>
                <p class="text-xs text-amber-700">Montant payé</p>
                <p class="font-medium text-amber-900">{{ formatCurrency(refund.meta.original_amount) }}</p>
              </div>
              <div>
                <p class="text-xs text-amber-700">Frais retenus</p>
                <p class="font-medium text-amber-900">{{ formatCurrency(refund.meta.koumbaya_fees) }}</p>
              </div>
              <div>
                <p class="text-xs text-amber-700">Montant remboursé</p>
                <p class="font-medium text-green-700">{{ formatCurrency(refund.amount) }}</p>
              </div>
            </div>
          </div>

          <!-- Reason -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
              <InformationCircleIcon class="h-5 w-5 text-blue-600" />
              Raison du remboursement
            </h3>
            <p class="text-sm text-blue-800">{{ refund.reason || 'N/A' }}</p>
          </div>

          <!-- Rejection Reason (if rejected) -->
          <div v-if="refund.status === 'rejected' && refund.rejection_reason" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-red-900 mb-2 flex items-center gap-2">
              <ExclamationCircleIcon class="h-5 w-5 text-red-600" />
              Raison du rejet
            </h3>
            <p class="text-sm text-red-800 font-mono whitespace-pre-wrap">{{ refund.rejection_reason }}</p>
            <p v-if="refund.rejected_at" class="text-xs text-red-600 mt-2">
              Rejeté le {{ formatDate(refund.rejected_at) }}
            </p>
          </div>

          <!-- SHAP API Response (if present) -->
          <div v-if="refund.meta?.shap_payout_id" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <CodeBracketIcon class="h-5 w-5 text-gray-600" />
              Réponse API SHAP
            </h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-xs text-gray-500">Payout ID</p>
                <p class="font-mono text-gray-900">{{ refund.meta.shap_payout_id }}</p>
              </div>
              <div v-if="refund.meta.shap_transaction_id">
                <p class="text-xs text-gray-500">Transaction ID</p>
                <p class="font-mono text-gray-900">{{ refund.meta.shap_transaction_id }}</p>
              </div>
              <div v-if="refund.meta.state">
                <p class="text-xs text-gray-500">État SHAP</p>
                <p class="text-gray-900">{{ refund.meta.state }}</p>
              </div>
              <div v-if="refund.meta.timestamp">
                <p class="text-xs text-gray-500">Horodatage</p>
                <p class="text-gray-900">{{ formatDate(refund.meta.timestamp) }}</p>
              </div>
            </div>
          </div>

          <!-- Timeline -->
          <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <ClockIcon class="h-5 w-5 text-gray-600" />
              Chronologie
            </h3>
            <div class="space-y-3">
              <!-- Created -->
              <div class="flex items-start gap-3">
                <div class="p-1.5 bg-blue-100 rounded-full mt-0.5">
                  <div class="h-2 w-2 bg-blue-600 rounded-full"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">Demande créée</p>
                  <p class="text-xs text-gray-500">{{ formatDate(refund.created_at) }}</p>
                </div>
              </div>

              <!-- Approved -->
              <div v-if="refund.approved_at" class="flex items-start gap-3">
                <div class="p-1.5 bg-green-100 rounded-full mt-0.5">
                  <div class="h-2 w-2 bg-green-600 rounded-full"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">Approuvée</p>
                  <p class="text-xs text-gray-500">{{ formatDate(refund.approved_at) }}</p>
                </div>
              </div>

              <!-- Processed -->
              <div v-if="refund.processed_at" class="flex items-start gap-3">
                <div class="p-1.5 bg-purple-100 rounded-full mt-0.5">
                  <div class="h-2 w-2 bg-purple-600 rounded-full"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">Traitée</p>
                  <p class="text-xs text-gray-500">{{ formatDate(refund.processed_at) }}</p>
                </div>
              </div>

              <!-- Completed -->
              <div v-if="refund.completed_at" class="flex items-start gap-3">
                <div class="p-1.5 bg-green-100 rounded-full mt-0.5">
                  <div class="h-2 w-2 bg-green-600 rounded-full"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">Complétée</p>
                  <p class="text-xs text-gray-500">{{ formatDate(refund.completed_at) }}</p>
                </div>
              </div>

              <!-- Rejected -->
              <div v-if="refund.rejected_at" class="flex items-start gap-3">
                <div class="p-1.5 bg-red-100 rounded-full mt-0.5">
                  <div class="h-2 w-2 bg-red-600 rounded-full"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">Rejetée</p>
                  <p class="text-xs text-gray-500">{{ formatDate(refund.rejected_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="refund.notes" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
              <DocumentTextIcon class="h-5 w-5 text-gray-600" />
              Notes
            </h3>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ refund.notes }}</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3">
          <!-- Retry button for rejected/failed refunds -->
          <button
            v-if="['rejected', 'failed'].includes(refund.status)"
            @click="retryRefund"
            :disabled="retrying"
            class="flex-1 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 font-medium transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
          >
            <ArrowPathIcon v-if="!retrying" class="h-5 w-5" />
            <div v-else class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
            <span>{{ retrying ? 'Relance...' : 'Relancer le remboursement' }}</span>
          </button>

          <button
            @click="close"
            class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-medium transition-colors"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useApi } from '@/composables/api'
import {
  XMarkIcon,
  UserIcon,
  TicketIcon,
  CreditCardIcon,
  ArrowPathIcon,
  BanknotesIcon,
  InformationCircleIcon,
  ExclamationCircleIcon,
  CodeBracketIcon,
  ClockIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

// Props & Emits
const props = defineProps({
  refund: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'refund-updated'])

// State
const retrying = ref(false)

// Methods
const close = () => {
  emit('close')
}

const retryRefund = async () => {
  if (!confirm('Êtes-vous sûr de vouloir relancer ce remboursement ?')) {
    return
  }

  retrying.value = true

  try {
    const { post } = useApi()
    const response = await post(`/admin/refunds/${props.refund.id}/retry`)

    if (response.success) {
      if (window.$toast) {
        window.$toast.success(
          response.message || 'Remboursement relancé avec succès',
          '✅ Succès'
        )
      }

      // Emit event to parent to refresh data
      emit('refund-updated', response.refund)

      // Close modal after a short delay
      setTimeout(() => {
        close()
      }, 1500)
    } else {
      throw new Error(response.message || 'Erreur lors de la relance')
    }
  } catch (error) {
    console.error('Error retrying refund:', error)

    if (window.$toast) {
      window.$toast.error(
        error.response?.data?.message || error.message || 'Erreur lors de la relance du remboursement',
        '🚫 Erreur'
      )
    }
  } finally {
    retrying.value = false
  }
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
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    processed: 'bg-purple-100 text-purple-800',
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'En attente',
    approved: 'Approuvé',
    processed: 'En cours',
    completed: 'Terminé',
    rejected: 'Rejeté'
  }
  return texts[status] || status
}

const getRefundMethodText = (method) => {
  const methods = {
    mobile_money: 'Mobile Money',
    wallet_credit: 'Crédit Portefeuille',
    bank_transfer: 'Virement Bancaire',
    cash: 'Espèces'
  }
  return methods[method] || method
}
</script>
