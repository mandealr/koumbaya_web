<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
    <div class="flex min-h-screen items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="relative bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Détails du Remboursement</h2>
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
        <div class="p-6 space-y-6">
          <!-- Status Badge -->
          <div class="flex items-center justify-between">
            <span :class="getStatusBadgeClass(refund.status)" class="px-4 py-2 text-sm font-medium rounded-full">
              {{ getStatusText(refund.status) }}
            </span>
            <div class="flex items-center gap-2">
              <span v-if="refund.auto_processed" class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                Automatique
              </span>
              <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                {{ refund.type }}
              </span>
            </div>
          </div>

          <!-- Amount Card -->
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-blue-700 font-medium mb-1">Montant du remboursement</p>
                <p class="text-4xl font-bold text-blue-900">
                  {{ refund.amount.toLocaleString('fr-FR') }} <span class="text-2xl">{{ refund.currency }}</span>
                </p>
              </div>
              <div class="p-4 bg-white rounded-full">
                <BanknotesIcon class="h-10 w-10 text-blue-600" />
              </div>
            </div>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Customer Info -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
              <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                <UserIcon class="h-4 w-4" />
                Client
              </h3>
              <div class="space-y-2">
                <div>
                  <p class="text-xs text-gray-500">Nom complet</p>
                  <p class="font-medium text-gray-900">
                    {{ refund.user?.first_name }} {{ refund.user?.last_name }}
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Email</p>
                  <p class="text-sm text-gray-900">{{ refund.user?.email }}</p>
                </div>
                <div v-if="refund.user?.phone">
                  <p class="text-xs text-gray-500">Téléphone</p>
                  <p class="text-sm text-gray-900">{{ refund.user?.phone }}</p>
                </div>
              </div>
            </div>

            <!-- Lottery Info -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
              <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                <TicketIcon class="h-4 w-4" />
                Tombola
              </h3>
              <div class="space-y-2">
                <div>
                  <p class="text-xs text-gray-500">Numéro de tombola</p>
                  <p class="font-medium text-gray-900">
                    {{ refund.lottery?.lottery_number || 'N/A' }}
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Produit</p>
                  <p class="text-sm text-gray-900">
                    {{ refund.lottery?.product?.name || 'Produit supprimé' }}
                  </p>
                </div>
                <div v-if="refund.lottery?.status">
                  <p class="text-xs text-gray-500">Statut tombola</p>
                  <p class="text-sm text-gray-900 capitalize">{{ refund.lottery.status }}</p>
                </div>
              </div>
            </div>

            <!-- Transaction Info -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
              <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                <CreditCardIcon class="h-4 w-4" />
                Transaction
              </h3>
              <div class="space-y-2">
                <div>
                  <p class="text-xs text-gray-500">Référence</p>
                  <p class="font-medium text-gray-900">{{ refund.transaction?.reference || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Montant payé</p>
                  <p class="text-sm text-gray-900">
                    {{ refund.transaction?.amount?.toLocaleString('fr-FR') }} FCFA
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Statut</p>
                  <p class="text-sm text-gray-900 capitalize">{{ refund.transaction?.status }}</p>
                </div>
              </div>
            </div>

            <!-- Refund Method -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
              <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                <ArrowPathIcon class="h-4 w-4" />
                Méthode de remboursement
              </h3>
              <div class="space-y-2">
                <div>
                  <p class="text-xs text-gray-500">Méthode</p>
                  <p class="font-medium text-gray-900">{{ getRefundMethodText(refund.refund_method) }}</p>
                </div>
                <div v-if="refund.external_refund_id">
                  <p class="text-xs text-gray-500">ID Externe</p>
                  <p class="text-sm text-gray-900 font-mono">{{ refund.external_refund_id }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Reason -->
          <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
            <h3 class="text-sm font-medium text-yellow-900 mb-2 flex items-center gap-2">
              <ExclamationTriangleIcon class="h-4 w-4" />
              Raison du remboursement
            </h3>
            <p class="text-sm text-yellow-800">{{ refund.reason }}</p>
          </div>

          <!-- Notes -->
          <div v-if="refund.notes" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h3 class="text-sm font-medium text-blue-900 mb-2 flex items-center gap-2">
              <DocumentTextIcon class="h-4 w-4" />
              Notes
            </h3>
            <p class="text-sm text-blue-800 whitespace-pre-wrap">{{ refund.notes }}</p>
          </div>

          <!-- Rejection Reason -->
          <div v-if="refund.status === 'rejected' && refund.rejection_reason" class="bg-red-50 rounded-lg p-4 border border-red-200">
            <h3 class="text-sm font-medium text-red-900 mb-2 flex items-center gap-2">
              <XCircleIcon class="h-4 w-4" />
              Raison du rejet
            </h3>
            <p class="text-sm text-red-800">{{ refund.rejection_reason }}</p>
          </div>

          <!-- Timeline -->
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
              <ClockIcon class="h-4 w-4" />
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
                  <p v-if="refund.approved_by" class="text-xs text-gray-500">
                    Par: {{ refund.approved_by.name || refund.approved_by.email }}
                  </p>
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
                  <p v-if="refund.processed_by" class="text-xs text-gray-500">
                    Par: {{ refund.processed_by.name || refund.processed_by.email }}
                  </p>
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
                  <p v-if="refund.rejected_by" class="text-xs text-gray-500">
                    Par: {{ refund.rejected_by.name || refund.rejected_by.email }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Callback Data (if exists) -->
          <div v-if="refund.callback_data && Object.keys(refund.callback_data).length > 0" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
              <CodeBracketIcon class="h-4 w-4" />
              Données de callback
            </h3>
            <pre class="text-xs text-gray-600 bg-white p-3 rounded border border-gray-200 overflow-x-auto">{{ JSON.stringify(refund.callback_data, null, 2) }}</pre>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex gap-3">
          <button
            v-if="refund.status === 'pending'"
            @click="confirmCancel"
            :disabled="canceling"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <span v-if="canceling" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            <XMarkIcon v-else class="h-4 w-4" />
            {{ canceling ? 'Annulation...' : 'Annuler la demande' }}
          </button>

          <button
            @click="close"
            class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-medium transition-colors"
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
  BanknotesIcon,
  UserIcon,
  TicketIcon,
  CreditCardIcon,
  ArrowPathIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  XCircleIcon,
  ClockIcon,
  CodeBracketIcon
} from '@heroicons/vue/24/outline'

// Props & Emits
const props = defineProps({
  refund: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])

// Composables
const { post } = useApi()

// State
const canceling = ref(false)

// Methods
const confirmCancel = async () => {
  if (!confirm('Êtes-vous sûr de vouloir annuler cette demande de remboursement ?')) {
    return
  }

  canceling.value = true

  try {
    await post(`/merchant/refunds/${props.refund.id}/cancel`)

    if (window.$toast) {
      window.$toast.success('Demande de remboursement annulée')
    }

    emit('updated')
    emit('close')
  } catch (error) {
    console.error('Error canceling refund:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de l\'annulation')
    }
  } finally {
    canceling.value = false
  }
}

const close = () => {
  emit('close')
}

// Utility functions
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
</script>
