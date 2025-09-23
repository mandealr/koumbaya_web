<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Détails de la demande</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <div class="space-y-6">
        <!-- Informations générales -->
        <div class="bg-gray-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations générales</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Référence</label>
              <p class="mt-1 text-sm text-gray-900">{{ request.request_number }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Statut</label>
              <span class="mt-1" :class="getStatusClass(request.status)">
                {{ getStatusLabel(request.status) }}
              </span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Type de remboursement</label>
              <span class="mt-1 inline-flex px-2 py-1 text-xs font-medium rounded-full"
                    :class="getRefundTypeClass(request.refund_type)">
                {{ getRefundTypeLabel(request.refund_type) }}
              </span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Date de création</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(request.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Informations client -->
        <div class="bg-blue-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations client</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nom du client</label>
              <p class="mt-1 text-sm text-gray-900">{{ request.customer?.name || 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <p class="mt-1 text-sm text-gray-900">{{ request.customer?.email || 'N/A' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Téléphone</label>
              <p class="mt-1 text-sm text-gray-900">{{ request.customer_phone }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Opérateur</label>
              <p class="mt-1 text-sm text-gray-900">{{ getOperatorLabel(request.payment_operator) }}</p>
            </div>
          </div>
        </div>

        <!-- Informations commande/tombola -->
        <div v-if="request.order || request.lottery" class="bg-green-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">
            {{ request.order ? 'Informations commande' : 'Informations tombola' }}
          </h3>
          
          <div v-if="request.order" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Numéro de commande</label>
              <p class="mt-1 text-sm text-gray-900">#{{ request.order.order_number }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Montant total</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatCurrency(request.order.total_amount) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Date de commande</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(request.order.created_at) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Statut commande</label>
              <p class="mt-1 text-sm text-gray-900">{{ request.order.status }}</p>
            </div>
          </div>

          <div v-if="request.lottery" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Numéro de tombola</label>
              <p class="mt-1 text-sm text-gray-900">#{{ request.lottery.lottery_number }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Statut tombola</label>
              <p class="mt-1 text-sm text-gray-900">{{ request.lottery.status }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Date de tirage</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(request.lottery.draw_date) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Tickets vendus</label>
              <p class="mt-1 text-sm text-gray-900">{{ request.lottery.sold_tickets || 0 }}</p>
            </div>
          </div>

          <div v-if="request.product" class="mt-4">
            <label class="block text-sm font-medium text-gray-700">Produit</label>
            <p class="mt-1 text-sm text-gray-900">{{ request.product.name }}</p>
          </div>
        </div>

        <!-- Détails du remboursement -->
        <div class="bg-yellow-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Détails du remboursement</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Montant à rembourser</label>
              <p class="mt-1 text-lg font-bold text-gray-900">{{ formatCurrency(request.refund_amount) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Raison détaillée</label>
              <p class="mt-1 text-sm text-gray-900 bg-white p-3 rounded border">{{ request.reason }}</p>
            </div>
          </div>
        </div>

        <!-- Informations de traitement -->
        <div v-if="request.approved_at || request.rejected_at" class="bg-purple-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Traitement administratif</h3>
          <div class="grid grid-cols-2 gap-4">
            <div v-if="request.approver">
              <label class="block text-sm font-medium text-gray-700">Traité par</label>
              <p class="mt-1 text-sm text-gray-900">{{ request.approver.name }}</p>
            </div>
            <div v-if="request.approved_at">
              <label class="block text-sm font-medium text-gray-700">Date d'approbation</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(request.approved_at) }}</p>
            </div>
            <div v-if="request.rejected_at">
              <label class="block text-sm font-medium text-gray-700">Date de rejet</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(request.rejected_at) }}</p>
            </div>
          </div>
          
          <div v-if="request.admin_notes" class="mt-4">
            <label class="block text-sm font-medium text-gray-700">Notes de l'administrateur</label>
            <p class="mt-1 text-sm text-gray-900 bg-white p-3 rounded border">{{ request.admin_notes }}</p>
          </div>
          
          <div v-if="request.rejection_reason" class="mt-4">
            <label class="block text-sm font-medium text-gray-700">Raison du rejet</label>
            <p class="mt-1 text-sm text-red-700 bg-red-100 p-3 rounded border border-red-200">{{ request.rejection_reason }}</p>
          </div>
        </div>

        <!-- Informations de paiement -->
        <div v-if="request.payout" class="bg-green-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations de paiement</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">ID de transaction</label>
              <p class="mt-1 text-sm text-gray-900 font-mono">{{ request.payout.transaction_id }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Référence externe</label>
              <p class="mt-1 text-sm text-gray-900 font-mono">{{ request.payout.external_reference }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Montant payé</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatCurrency(request.payout.amount) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Statut paiement</label>
              <span class="mt-1 inline-flex px-2 py-1 text-xs font-medium rounded-full"
                    :class="request.payout.status === 5 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                {{ request.payout.status === 5 ? 'Terminé' : 'En cours' }}
              </span>
            </div>
          </div>
          
          <div v-if="request.payout.message" class="mt-4">
            <label class="block text-sm font-medium text-gray-700">Message du système</label>
            <p class="mt-1 text-sm text-gray-900 bg-white p-3 rounded border">{{ request.payout.message }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
          <button 
            @click="$emit('close')"
            class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
            Fermer
          </button>
          
          <button 
            v-if="request.status === 'pending'"
            @click="cancelRequest"
            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
            Annuler la demande
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { api } from '@/composables/api'
import { useToast } from '@/composables/useToast'

// Props
const props = defineProps({
  request: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['close', 'updated'])

// Composables
const toast = useToast()

// Methods
const cancelRequest = async () => {
  if (!confirm('Voulez-vous vraiment annuler cette demande ?')) return
  
  try {
    await api.delete(`/merchant/payout-requests/${props.request.id}`)
    toast.success('Demande annulée')
    emit('updated')
    emit('close')
  } catch (error) {
    console.error('Erreur annulation:', error)
    toast.error('Erreur lors de l\'annulation')
  }
}

// Helper functions
const getStatusClass = (status) => {
  const classes = {
    'pending': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800',
    'approved': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    'completed': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    'rejected': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800',
    'failed': 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800'
  }
  return classes[status] || 'inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    'pending': 'En attente',
    'approved': 'Approuvé',
    'completed': 'Terminé',
    'rejected': 'Rejeté',
    'failed': 'Échoué'
  }
  return labels[status] || status
}

const getRefundTypeClass = (type) => {
  const classes = {
    'order_cancellation': 'bg-blue-100 text-blue-800',
    'lottery_cancellation': 'bg-purple-100 text-purple-800',
    'product_defect': 'bg-red-100 text-red-800',
    'customer_request': 'bg-green-100 text-green-800',
    'other': 'bg-gray-100 text-gray-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getRefundTypeLabel = (type) => {
  const labels = {
    'order_cancellation': 'Annulation commande',
    'lottery_cancellation': 'Annulation tombola',
    'product_defect': 'Produit défectueux',
    'customer_request': 'Demande client',
    'other': 'Autre'
  }
  return labels[type] || type
}

const getOperatorLabel = (operator) => {
  const labels = {
    'airtelmoney': 'Airtel Money',
    'moovmoney4': 'Moov Money'
  }
  return labels[operator] || operator
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(amount || 0)
}

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  
  return new Date(dateString).toLocaleString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>