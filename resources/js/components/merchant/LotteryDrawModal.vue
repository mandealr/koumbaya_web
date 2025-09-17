<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-gray-600 bg-opacity-50 transition-opacity" @click="!isDrawing && close()"></div>

      <!-- Modal -->
      <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle bg-white shadow-xl transform transition-all rounded-xl">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <GiftIcon class="w-6 h-6 text-purple-600 mr-2" />
            {{ isDrawing ? 'Tirage en cours...' : 'Effectuer le tirage' }}
          </h3>
          <button v-if="!isDrawing" @click="close" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="isDrawing" class="text-center py-8">
          <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-600 mx-auto mb-4"></div>
          <p class="text-gray-600 mb-2">Sélection du gagnant en cours...</p>
          <p class="text-sm text-gray-500">{{ drawingMessage }}</p>
        </div>

        <!-- Success State -->
        <div v-else-if="drawResult && drawResult.success" class="text-center py-6">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <TrophyIcon class="w-8 h-8 text-green-600" />
          </div>
          <h4 class="text-lg font-semibold text-gray-900 mb-2">🎉 Tirage terminé !</h4>
          <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <p class="text-green-800 font-medium">Gagnant :</p>
            <p class="text-lg font-bold text-green-900">{{ drawResult.winning_ticket.user.first_name }} {{ drawResult.winning_ticket.user.last_name }}</p>
            <p class="text-sm text-green-700">Ticket N° {{ drawResult.winning_ticket.ticket_number }}</p>
          </div>
          <p class="text-sm text-gray-600 mb-4">Le gagnant a été automatiquement notifié par email.</p>
        </div>

        <!-- Error State -->
        <div v-else-if="drawResult && !drawResult.success" class="text-center py-6">
          <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <ExclamationTriangleIcon class="w-8 h-8 text-red-600" />
          </div>
          <h4 class="text-lg font-semibold text-gray-900 mb-2">Erreur lors du tirage</h4>
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <p class="text-red-800">{{ drawResult.error || 'Une erreur inattendue s\'est produite' }}</p>
          </div>
        </div>

        <!-- Initial State -->
        <div v-else class="space-y-4">
          <!-- Lottery Info -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">{{ lottery.title }}</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Participants :</span>
                <span class="font-medium ml-1">{{ lottery.participants_count }}</span>
              </div>
              <div>
                <span class="text-gray-600">Tickets vendus :</span>
                <span class="font-medium ml-1">{{ lottery.sold_tickets }}/{{ lottery.max_tickets }}</span>
              </div>
              <div>
                <span class="text-gray-600">Valeur du lot :</span>
                <span class="font-medium ml-1">{{ formatCurrency(lottery.product_value) }}</span>
              </div>
              <div>
                <span class="text-gray-600">Revenus générés :</span>
                <span class="font-medium ml-1">{{ formatCurrency(lottery.total_revenue) }}</span>
              </div>
            </div>
          </div>

          <!-- Draw Conditions -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h5 class="font-medium text-blue-900 mb-2 flex items-center">
              <InformationCircleIcon class="w-5 h-5 mr-2" />
              Conditions de tirage
            </h5>
            <ul class="text-sm text-blue-800 space-y-1">
              <li v-if="canDrawNow" class="flex items-center">
                <CheckCircleIcon class="w-4 h-4 text-green-600 mr-2" />
                Tous les tickets sont vendus
              </li>
              <li v-if="lottery.participants_count >= minParticipants" class="flex items-center">
                <CheckCircleIcon class="w-4 h-4 text-green-600 mr-2" />
                Minimum de participants atteint ({{ lottery.participants_count }}/{{ minParticipants }})
              </li>
              <li v-else class="flex items-center">
                <XCircleIcon class="w-4 h-4 text-red-600 mr-2" />
                Participants insuffisants ({{ lottery.participants_count }}/{{ minParticipants }})
              </li>
            </ul>
          </div>

          <!-- Warning -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
              <ExclamationTriangleIcon class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" />
              <div class="text-sm text-yellow-800">
                <p class="font-medium mb-1">⚠️ Action irréversible</p>
                <p>Une fois le tirage effectué, il ne pourra plus être modifié. Le gagnant sera automatiquement notifié et le produit sera marqué comme vendu.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="!isDrawing" class="flex justify-end gap-3 mt-6">
          <button
            @click="close"
            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            {{ drawResult ? 'Fermer' : 'Annuler' }}
          </button>
          <button
            v-if="!drawResult"
            @click="performDraw"
            :disabled="!canPerformDraw"
            class="px-6 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center"
          >
            <GiftIcon class="w-4 h-4 mr-2" />
            Effectuer le tirage
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useApi } from '@/composables/api'
import {
  GiftIcon,
  XMarkIcon,
  TrophyIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  lottery: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'drawn'])

const { post } = useApi()

// State
const isDrawing = ref(false)
const drawResult = ref(null)
const drawingMessage = ref('')

// Constants
const minParticipants = 1 // Minimum participants required

// Computed
const canDrawNow = computed(() => {
  return props.lottery.sold_tickets >= props.lottery.max_tickets
})

const canPerformDraw = computed(() => {
  return canDrawNow.value && props.lottery.participants_count >= minParticipants
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

const close = () => {
  if (!isDrawing.value) {
    emit('close')
  }
}

const performDraw = async () => {
  if (!canPerformDraw.value) return

  isDrawing.value = true
  drawResult.value = null
  
  try {
    // Messages de progression
    drawingMessage.value = 'Vérification des conditions...'
    await new Promise(resolve => setTimeout(resolve, 500))
    
    drawingMessage.value = 'Collecte des tickets participants...'
    await new Promise(resolve => setTimeout(resolve, 800))
    
    drawingMessage.value = 'Génération du tirage aléatoire vérifiable...'
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    drawingMessage.value = 'Sélection du gagnant...'
    
    // Appel à l'API
    const response = await post(`/lotteries/${props.lottery.id}/draw`)
    
    if (response.success !== false) {
      drawResult.value = {
        success: true,
        lottery: response.lottery,
        winning_ticket: response.winning_ticket,
        verification_hash: response.verification_hash
      }
      
      // Notifier le parent que le tirage est terminé
      emit('drawn', drawResult.value)
      
      if (window.$toast) {
        window.$toast.success('Tirage effectué avec succès !', '🎉 Gagnant sélectionné')
      }
    } else {
      throw new Error(response.error || 'Erreur lors du tirage')
    }
    
  } catch (error) {
    console.error('Erreur lors du tirage:', error)
    
    drawResult.value = {
      success: false,
      error: error.response?.data?.error || error.message || 'Une erreur inattendue s\'est produite'
    }
    
    if (window.$toast) {
      window.$toast.error(drawResult.value.error, '❌ Erreur de tirage')
    }
  } finally {
    isDrawing.value = false
    drawingMessage.value = ''
  }
}

// Reset state when modal opens/closes
watch(() => props.show, (newValue) => {
  if (!newValue) {
    // Reset state when modal closes
    setTimeout(() => {
      isDrawing.value = false
      drawResult.value = null
      drawingMessage.value = ''
    }, 300)
  }
})
</script>
