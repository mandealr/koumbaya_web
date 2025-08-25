<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-20 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="koumbaya-card inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle bg-white shadow-xl transform transition-all">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">Détails de la tombola</h3>
        </div>
        
        <div class="koumbaya-card-body space-y-6">
          <div v-if="lottery" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informations principales -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-gray-900">Informations</h4>
              
              <div class="space-y-3">
                <div>
                  <label class="text-sm text-gray-600">Produit</label>
                  <p class="font-medium">{{ lottery.productName }}</p>
                </div>
                
                <div>
                  <label class="text-sm text-gray-600">Statut</label>
                  <span class="koumbaya-badge koumbaya-badge-success">{{ lottery.status }}</span>
                </div>
                
                <div>
                  <label class="text-sm text-gray-600">Date de tirage</label>
                  <p class="font-medium">{{ formatDate(lottery.drawDate) }}</p>
                </div>
                
                <div>
                  <label class="text-sm text-gray-600">Description</label>
                  <p class="text-gray-700">{{ lottery.description }}</p>
                </div>
              </div>
            </div>

            <!-- Statistiques -->
            <div class="space-y-4">
              <h4 class="text-lg font-semibold text-gray-900">Statistiques</h4>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-100 p-4 rounded-lg">
                  <div class="text-2xl font-bold text-blue-600">{{ lottery.soldTickets || 0 }}</div>
                  <div class="text-sm text-gray-600">Tickets vendus</div>
                </div>
                
                <div class="bg-gray-100 p-4 rounded-lg">
                  <div class="text-2xl font-bold text-gray-900">{{ lottery.totalTickets || 1000 }}</div>
                  <div class="text-sm text-gray-600">Total tickets</div>
                </div>
                
                <div class="bg-gray-100 p-4 rounded-lg">
                  <div class="text-2xl font-bold text-blue-600">{{ formatPrice(lottery.revenue) }}</div>
                  <div class="text-sm text-gray-600">Revenus</div>
                </div>
                
                <div class="bg-gray-100 p-4 rounded-lg">
                  <div class="text-2xl font-bold text-purple-600">{{ lottery.participants || 0 }}</div>
                  <div class="text-sm text-gray-600">Participants</div>
                </div>
              </div>
              
              <!-- Barre de progression -->
              <div class="mt-4">
                <div class="flex justify-between text-sm mb-2">
                  <span>Progression</span>
                  <span>{{ Math.round(((lottery.soldTickets || 0) / (lottery.totalTickets || 1000)) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-gradient-to-r from-koumbaya-primary to-blue-600 h-2 rounded-full"
                    :style="{ width: Math.round(((lottery.soldTickets || 0) / (lottery.totalTickets || 1000)) * 100) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="koumbaya-card-footer">
          <div class="flex justify-end">
            <button
              @click="close"
              class="koumbaya-btn koumbaya-btn-outline"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
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

const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}

const formatDate = (date) => {
  if (!date) return 'Non défini'
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price || 0).replace('XAF', 'FCFA')
}
</script>