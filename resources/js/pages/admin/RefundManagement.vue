<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Gestion des Remboursements</h1>
        <p class="mt-2 text-gray-600">Administration des remboursements automatiques et manuels</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="checkEligibleLotteries"
          class="admin-btn-secondary flex items-center gap-2"
        >
          <MagnifyingGlassIcon class="w-4 h-4" />
          Vérifier les tombolas
        </button>
        <button
          @click="showProcessAutomatic = true"
          class="admin-btn-primary flex items-center gap-2"
        >
          <CogIcon class="w-4 h-4" />
          Traiter automatique
        </button>
      </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="admin-card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-green-100">
            <BanknotesIcon class="w-6 h-6 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total remboursements</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_refunds || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="admin-card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-blue-100">
            <CheckCircleIcon class="w-6 h-6 text-[#0099cc]" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Montant remboursé</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrencyShort(stats.total_amount_refunded) }}</p>
          </div>
        </div>
      </div>

      <div class="admin-card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-yellow-100">
            <ClockIcon class="w-6 h-6 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">En attente</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.pending_refunds || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="admin-card hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-purple-100">
            <CogIcon class="w-6 h-6 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Auto-traités</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.auto_processed || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Eligible Lotteries Panel -->
    <div v-if="eligibleLotteries.expired_insufficient?.length || eligibleLotteries.cancelled?.length" class="admin-card">
      <div class="admin-card-header">
        <h3 class="text-lg font-semibold text-gray-900">Tombolas éligibles aux remboursements</h3>
      </div>
      <div class="p-6">
        <!-- Expired with Insufficient Participants -->
        <div v-if="eligibleLotteries.expired_insufficient?.length" class="mb-6">
          <h4 class="text-md font-medium text-orange-700 mb-3 flex items-center gap-1">
            <ClockIcon class="w-4 h-4" />
            Expirées - Participants insuffisants
          </h4>
          <div class="space-y-3">
            <div
              v-for="lottery in eligibleLotteries.expired_insufficient"
              :key="lottery.id"
              class="flex items-center justify-between bg-orange-50 border border-orange-200 rounded-lg p-4"
            >
              <div>
                <div class="font-medium">{{ lottery.lottery_number }}</div>
                <div class="text-sm text-gray-600">{{ lottery.product_title }}</div>
                <div class="text-sm">{{ lottery.participants }}/{{ lottery.min_participants }} participants</div>
              </div>
              <div class="text-right">
                <div class="font-semibold text-orange-700 mb-2">{{ formatCurrency(lottery.estimated_refund) }}</div>
                <button
                  @click="showRefundDetailsModal(lottery)"
                  class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2 text-sm font-medium"
                >
                  <BanknotesIcon class="w-4 h-4" />
                  Voir les détails & Rembourser
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Cancelled Lotteries -->
        <div v-if="eligibleLotteries.cancelled?.length">
          <h4 class="text-md font-medium text-red-700 mb-3 flex items-center gap-1">
            <XCircleIcon class="w-4 h-4" />
            Tombolas annulées
          </h4>
          <div class="space-y-3">
            <div
              v-for="lottery in eligibleLotteries.cancelled"
              :key="lottery.id"
              class="flex items-center justify-between bg-red-50 border border-red-200 rounded-lg p-4"
            >
              <div>
                <div class="font-medium">{{ lottery.lottery_number }}</div>
                <div class="text-sm text-gray-600">{{ lottery.product_title }}</div>
                <div class="text-sm">{{ lottery.participants }} participants</div>
              </div>
              <div class="text-right">
                <div class="font-semibold text-red-700 mb-2">{{ formatCurrency(lottery.estimated_refund) }}</div>
                <button
                  @click="showRefundDetailsModal(lottery)"
                  class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2 text-sm font-medium"
                >
                  <BanknotesIcon class="w-4 h-4" />
                  Voir les détails & Rembourser
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <input
            v-model="filters.search"
            @input="loadRefunds"
            type="text"
            placeholder="N° remboursement, utilisateur..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select
            v-model="filters.status"
            @change="loadRefunds"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="approved">Approuvé</option>
            <option value="processed">Traité</option>
            <option value="completed">Terminé</option>
            <option value="rejected">Rejeté</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
          <select
            v-model="filters.type"
            @change="loadRefunds"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les types</option>
            <option value="automatic">Automatique</option>
            <option value="manual">Manuel</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="w-full px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
          >
            Réinitialiser
          </button>
        </div>
      </div>
      <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-500">
          {{ refunds.length }} remboursement(s) trouvé(s)
        </div>
        <div>
          <select
            v-model="filters.reason"
            @change="loadRefunds"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
          >
            <option value="">Toutes les raisons</option>
            <option value="insufficient_participants">Participants insuffisants</option>
            <option value="lottery_cancelled">Tombola annulée</option>
            <option value="accidental_purchase">Achat accidentel</option>
            <option value="technical_issue">Problème technique</option>
          </select>
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
      <p class="text-gray-600">Aucun remboursement ne correspond aux filtres sélectionnés.</p>
    </div>

    <div v-else class="admin-card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remboursement</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raison</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Détails</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="refund in refunds" :key="refund.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="font-mono text-sm font-medium text-gray-900">{{ refund.refund_number }}</div>
                  <div class="text-sm text-gray-500">{{ refund.type }}</div>
                  <div v-if="refund.auto_processed" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                    <CogIcon class="w-3 h-3" />
                    Auto
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ refund.user?.full_name || `${refund.user?.first_name || ''} ${refund.user?.last_name || ''}`.trim() || 'N/A' }}
                  </div>
                  <div class="text-sm text-gray-500">{{ refund.user?.email || 'N/A' }}</div>
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-gray-900">{{ formatCurrency(refund.amount) }}</div>
                <div class="text-xs text-gray-500">{{ refund.currency }}</div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ getReasonText(refund.reason) }}</div>
                <div v-if="refund.lottery" class="text-xs text-gray-500">
                  {{ refund.lottery.lottery_number }}
                </div>
                <div v-else-if="refund.ticket" class="text-xs text-gray-500">
                  Ticket #{{ refund.ticket.ticket_number }}
                </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  :class="getStatusClass(refund.status)"
                >
                  {{ getStatusText(refund.status) }}
                </span>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(refund.created_at) }}
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="viewRefundDetails(refund)"
                  class="inline-flex items-center gap-1 px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors"
                  title="Voir détails"
                >
                  <EyeIcon class="w-4 h-4" />
                  Voir
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Process Automatic Modal -->
    <div v-if="showProcessAutomatic" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-40" @click="showProcessAutomatic = false"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold flex items-center gap-2">
                <CogIcon class="w-5 h-5" />
                Traitement Automatique
              </h3>
              <button @click="showProcessAutomatic = false" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>

            <div class="space-y-4">
              <div>
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="processOptions.dryRun"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  />
                  <span class="ml-2 text-sm text-gray-700">Mode test (pas de changements)</span>
                </label>
              </div>

              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <div class="text-sm text-yellow-800">
                  <p class="font-medium mb-1 flex items-center gap-1">
                    <ExclamationTriangleIcon class="w-4 h-4" />
                    Attention
                  </p>
                  <p>Cette action va traiter automatiquement tous les remboursements éligibles.</p>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button
                @click="showProcessAutomatic = false"
                class="admin-btn-secondary"
              >
                Annuler
              </button>
              <button
                @click="processAutomatic"
                :disabled="processing"
                class="admin-btn-primary disabled:opacity-50"
              >
                {{ processing ? 'Traitement...' : 'Traiter' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="refundToReject" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-40" @click="refundToReject = null"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold">Rejeter le remboursement</h3>
              <button @click="refundToReject = null" class="text-gray-400 hover:text-gray-600">
                <XMarkIcon class="w-6 h-6" />
              </button>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Raison du rejet *</label>
                <textarea
                  v-model="rejectReason"
                  rows="3"
                  required
                  class="admin-input focus:border-red-500 focus:ring-red-500"
                  placeholder="Expliquez pourquoi ce remboursement est rejeté..."
                ></textarea>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button
                @click="refundToReject = null; rejectReason = ''"
                class="admin-btn-secondary"
              >
                Annuler
              </button>
              <button
                @click="rejectRefund"
                :disabled="processing || !rejectReason.trim()"
                class="admin-btn-danger disabled:opacity-50"
              >
                {{ processing ? 'Rejet...' : 'Rejeter' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lottery Refund Details Modal -->
    <LotteryRefundModal
      v-if="showRefundModal && selectedLottery"
      :lottery="selectedLottery"
      @close="showRefundModal = false"
      @success="handleRefundSuccess"
    />

    <!-- Refund Detail Modal -->
    <RefundDetailModal
      v-if="showRefundDetailModal && selectedRefund"
      :refund="selectedRefund"
      @close="showRefundDetailModal = false"
      @refund-updated="handleRefundUpdated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import LotteryRefundModal from '@/components/admin/LotteryRefundModal.vue'
import RefundDetailModal from '@/components/admin/RefundDetailModal.vue'
import {
  BanknotesIcon,
  XMarkIcon,
  ClockIcon,
  CheckCircleIcon,
  CogIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  XCircleIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const { get, post } = useApi()

// Data
const refunds = ref([])
const stats = ref({})
const loading = ref(false)
const processing = ref(false)
const eligibleLotteries = ref({})
const showProcessAutomatic = ref(false)
const refundToReject = ref(null)
const rejectReason = ref('')
const showRefundModal = ref(false)
const selectedLottery = ref(null)
const showRefundDetailModal = ref(false)
const selectedRefund = ref(null)

const filters = ref({
  status: '',
  type: '',
  reason: ''
})

const processOptions = ref({
  dryRun: false
})

// Methods
const loadRefunds = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({ limit: 100 })

    Object.keys(filters.value).forEach(key => {
      if (filters.value[key]) {
        params.append(key, filters.value[key])
      }
    })

    const response = await get(`/admin/refunds?${params}`)
    if (response && response.success && response.data) {
      refunds.value = response.data.refunds || []
    }
  } catch (error) {
    console.error('Error loading refunds:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du chargement des remboursements', '✗ Erreur')
    }
    refunds.value = []
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await get('/admin/refunds/stats')
    if (response && response.success && response.data) {
      stats.value = response.data
    }
  } catch (error) {
    console.error('Error loading stats:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du chargement des statistiques', '✗ Erreur')
    }
    stats.value = {
      total_refunds: 0,
      total_amount_refunded: 0,
      pending_refunds: 0,
      auto_processed: 0
    }
  }
}

const checkEligibleLotteries = async () => {
  try {
    const response = await get('/admin/refunds/eligible-lotteries')
    if (response && response.success && response.data) {
      eligibleLotteries.value = response.data
    }
  } catch (error) {
    console.error('Error loading eligible lotteries:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du chargement des tombolas éligibles', '✗ Erreur')
    }
    eligibleLotteries.value = {
      expired_insufficient: [],
      cancelled: [],
      summary: {
        expired_count: 0,
        expired_estimated_refund: 0,
        cancelled_count: 0,
        cancelled_estimated_refund: 0
      }
    }
  }
}

const processAutomatic = async () => {
  processing.value = true
  try {
    const response = await post('/admin/refunds/process-automatic', {
      dry_run: processOptions.value.dryRun
    })

    if (response && response.success) {
      if (window.$toast) {
        const message = response.data.message || 'Traitement automatique terminé'
        window.$toast.success(message, '✅ Traitement')
      }
    }

    // Reload data
    await Promise.all([
      loadRefunds(),
      loadStats(),
      checkEligibleLotteries()
    ])

    showProcessAutomatic.value = false
  } catch (error) {
    console.error('Error processing automatic refunds:', error)
    if (window.$toast) {
      const message = error.response?.data?.message || 'Erreur lors du traitement automatique'
      window.$toast.error(message, '✗ Erreur')
    }
  } finally {
    processing.value = false
  }
}

const showRefundDetailsModal = (lottery) => {
  selectedLottery.value = lottery
  showRefundModal.value = true
}

const handleRefundSuccess = async () => {
  showRefundModal.value = false
  selectedLottery.value = null

  await Promise.all([
    loadRefunds(),
    loadStats(),
    checkEligibleLotteries()
  ])
}

const handleRefundUpdated = async (updatedRefund) => {
  // Refresh the refund list
  await Promise.all([
    loadRefunds(),
    loadStats()
  ])

  // Update the selected refund if it's still showing
  if (selectedRefund.value && selectedRefund.value.id === updatedRefund.id) {
    selectedRefund.value = updatedRefund
  }
}

const processLottery = async (lotteryId, dryRun = false) => {
  if (!confirm('Traiter les remboursements pour cette tombola ?')) return

  processing.value = true
  try {
    const response = await post('/admin/refunds/process-automatic', {
      lottery_id: lotteryId,
      dry_run: dryRun
    })

    if (response && response.success) {
      if (window.$toast) {
        const message = response.data.message || 'Traitement terminé'
        window.$toast.success(message, '✅ Traitement')
      }
    }

    await Promise.all([
      loadRefunds(),
      loadStats(),
      checkEligibleLotteries()
    ])
  } catch (error) {
    console.error('Error processing lottery refunds:', error)
    if (window.$toast) {
      const message = error.response?.data?.message || 'Erreur lors du traitement'
      window.$toast.error(message, '✗ Erreur')
    }
  } finally {
    processing.value = false
  }
}

const approveRefund = async (refund) => {
  if (!confirm('Approuver ce remboursement ?')) return

  processing.value = true
  try {
    const response = await post(`/admin/refunds/${refund.id}/approve`)

    if (response && response.success) {
      if (window.$toast) {
        const message = response.data.message || 'Remboursement approuvé et traité'
        window.$toast.success(message, '✅ Approbation')
      }

      // Update refund in list
      const index = refunds.value.findIndex(r => r.id === refund.id)
      if (index !== -1 && response.data.refund) {
        refunds.value[index] = response.data.refund
      }

      // Reload stats
      await loadStats()
    }
  } catch (error) {
    console.error('Error approving refund:', error)
    if (window.$toast) {
      const message = error.response?.data?.message || 'Erreur lors de l\'approbation'
      window.$toast.error(message, '✗ Erreur')
    }
  } finally {
    processing.value = false
  }
}

const showRejectModal = (refund) => {
  refundToReject.value = refund
  rejectReason.value = ''
}

const rejectRefund = async () => {
  if (!rejectReason.value.trim()) return

  processing.value = true
  try {
    const response = await post(`/admin/refunds/${refundToReject.value.id}/reject`, {
      reason: rejectReason.value
    })

    if (response && response.success) {
      if (window.$toast) {
        const message = response.data.message || 'Remboursement rejeté'
        window.$toast.warning(message, '⚠️ Rejet')
      }

      // Update refund in list
      const index = refunds.value.findIndex(r => r.id === refundToReject.value.id)
      if (index !== -1 && response.data.refund) {
        refunds.value[index] = response.data.refund
      }

      // Reload stats
      await loadStats()
    }

    refundToReject.value = null
    rejectReason.value = ''
  } catch (error) {
    console.error('Error rejecting refund:', error)
    if (window.$toast) {
      const message = error.response?.data?.message || 'Erreur lors du rejet'
      window.$toast.error(message, '✗ Erreur')
    }

    refundToReject.value = null
    rejectReason.value = ''
  } finally {
    processing.value = false
  }
}

const viewRefundDetails = async (refund) => {
  // Charger les détails complets du remboursement
  try {
    const response = await get(`/admin/refunds/${refund.id}`)
    if (response && response.success) {
      selectedRefund.value = response.data?.refund || response.refund || response.data
      showRefundDetailModal.value = true
    } else {
      if (window.$toast) {
        window.$toast.error('Erreur lors du chargement des détails', '✗ Erreur')
      }
    }
  } catch (error) {
    console.error('Error loading refund details:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du chargement des détails', '✗ Erreur')
    }
  }
}

const resetFilters = () => {
  filters.value = { status: '', type: '', reason: '' }
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
    pending: 'En attente',
    approved: 'Approuvé',
    processed: 'Traité',
    completed: 'Terminé',
    rejected: 'Rejeté',
    failed: 'Échec'
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

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadRefunds(),
    loadStats(),
    checkEligibleLotteries()
  ])
})
</script>
