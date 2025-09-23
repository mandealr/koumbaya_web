<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Nouvelle demande de remboursement</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <form @submit.prevent="submitRequest" class="space-y-6">
        <!-- Type de remboursement -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
          <select 
            v-model="form.type" 
            @change="onTypeChange"
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
            required>
            <option value="">Sélectionner le type</option>
            <option value="order">Commande</option>
            <option value="lottery">Tombola</option>
          </select>
        </div>

        <!-- Sélection de l'entité -->
        <div v-if="form.type">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ form.type === 'order' ? 'Commande' : 'Tombola' }}
          </label>
          <select 
            v-model="form.entity_id" 
            @change="onEntityChange"
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
            required>
            <option value="">Sélectionner {{ form.type === 'order' ? 'une commande' : 'une tombola' }}</option>
            <option v-for="item in entities" :key="item.id" :value="item.id">
              {{ form.type === 'order' ? `#${item.order_number}` : `#${item.lottery_number}` }} - 
              {{ item.customer_name || item.product_name }} - 
              {{ formatCurrency(item.total_amount || item.total_collected) }}
            </option>
          </select>
          
          <div v-if="loading.entities" class="text-sm text-gray-500 mt-2">
            Chargement...
          </div>
        </div>

        <!-- Informations client (auto-remplies) -->
        <div v-if="selectedEntity" class="bg-gray-50 p-4 rounded-lg">
          <h4 class="font-medium text-gray-900 mb-3">Informations client</h4>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-gray-600">Client</label>
              <p class="font-medium">{{ selectedEntity.customer_name }}</p>
            </div>
            <div>
              <label class="block text-sm text-gray-600">Produit</label>
              <p class="font-medium">{{ selectedEntity.product_name }}</p>
            </div>
            <div>
              <label class="block text-sm text-gray-600">Montant</label>
              <p class="font-medium">{{ formatCurrency(selectedEntity.total_amount || selectedEntity.total_collected) }}</p>
            </div>
            <div>
              <label class="block text-sm text-gray-600">Date</label>
              <p class="font-medium">{{ formatDate(selectedEntity.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Type de remboursement -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Raison du remboursement</label>
          <select 
            v-model="form.refund_type" 
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
            required>
            <option value="">Sélectionner la raison</option>
            <option value="order_cancellation">Annulation de commande</option>
            <option value="lottery_cancellation">Annulation de tombola</option>
            <option value="product_defect">Produit défectueux</option>
            <option value="customer_request">Demande du client</option>
            <option value="other">Autre raison</option>
          </select>
        </div>

        <!-- Description détaillée -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description détaillée</label>
          <textarea 
            v-model="form.reason" 
            rows="4"
            placeholder="Expliquez en détail la raison du remboursement..."
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
            required
            minlength="10"
            maxlength="1000"></textarea>
          <div class="text-sm text-gray-500 mt-1">
            {{ form.reason.length }}/1000 caractères (minimum 10)
          </div>
        </div>

        <!-- Montant à rembourser -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Montant à rembourser (FCFA)</label>
          <input 
            type="number" 
            v-model="form.refund_amount"
            :max="selectedEntity ? (selectedEntity.total_amount || selectedEntity.total_collected) : 500000"
            min="500"
            step="1"
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
            required />
          <div class="text-sm text-gray-500 mt-1">
            Montant maximum : {{ selectedEntity ? formatCurrency(selectedEntity.total_amount || selectedEntity.total_collected) : 'N/A' }}
          </div>
        </div>

        <!-- Opérateur de paiement -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Opérateur Mobile Money</label>
          <select 
            v-model="form.payment_operator" 
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
            required>
            <option value="">Sélectionner l'opérateur</option>
            <option value="airtelmoney">Airtel Money</option>
            <option value="moovmoney4">Moov Money</option>
          </select>
        </div>

        <!-- Numéro de téléphone -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de téléphone du client</label>
          <input 
            type="tel" 
            v-model="form.customer_phone"
            placeholder="Ex: 07123456 ou 06123456"
            pattern="[0-9]{8}"
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
            required />
          <div class="text-sm text-gray-500 mt-1">
            Format: 8 chiffres (ex: 07123456 pour Airtel, 06123456 pour Moov)
          </div>
        </div>

        <!-- Validation du numéro selon l'opérateur -->
        <div v-if="phoneValidationMessage" class="text-sm" :class="phoneValidationClass">
          {{ phoneValidationMessage }}
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
          <button 
            type="button" 
            @click="$emit('close')"
            class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
            Annuler
          </button>
          <button 
            type="submit"
            :disabled="submitting || !isFormValid"
            class="px-6 py-3 bg-[#0099cc] text-white rounded-lg hover:bg-[#0088bb] disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            {{ submitting ? 'Envoi en cours...' : 'Soumettre la demande' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { api } from '@/composables/api'
import { useToast } from '@/composables/useToast'

// Emits
const emit = defineEmits(['close', 'created'])

// Composables
const toast = useToast()

// State
const submitting = ref(false)
const entities = ref([])
const selectedEntity = ref(null)

const loading = reactive({
  entities: false
})

const form = reactive({
  type: '',
  entity_id: '',
  refund_type: '',
  reason: '',
  refund_amount: 0,
  customer_id: '',
  customer_phone: '',
  payment_operator: ''
})

// Computed
const isFormValid = computed(() => {
  return form.type && 
         form.entity_id && 
         form.refund_type && 
         form.reason.length >= 10 && 
         form.refund_amount >= 500 && 
         form.payment_operator && 
         form.customer_phone.length === 8 &&
         phoneValidation.value.isValid
})

const phoneValidation = computed(() => {
  if (!form.customer_phone || !form.payment_operator) {
    return { isValid: true, message: '' }
  }

  const phone = form.customer_phone.replace(/\D/g, '')
  
  if (phone.length !== 8) {
    return { isValid: false, message: 'Le numéro doit contenir exactement 8 chiffres' }
  }

  if (form.payment_operator === 'airtelmoney') {
    if (!phone.match(/^(04|05|07)/)) {
      return { isValid: false, message: 'Numéro Airtel Money invalide (doit commencer par 04, 05 ou 07)' }
    }
  } else if (form.payment_operator === 'moovmoney4') {
    if (!phone.match(/^(06|62|65|66)/)) {
      return { isValid: false, message: 'Numéro Moov Money invalide (doit commencer par 06, 62, 65 ou 66)' }
    }
  }

  return { isValid: true, message: 'Numéro valide ✓' }
})

const phoneValidationMessage = computed(() => phoneValidation.value.message)
const phoneValidationClass = computed(() => 
  phoneValidation.value.isValid ? 'text-green-600' : 'text-red-600'
)

// Watchers
watch(() => form.entity_id, (newValue) => {
  if (newValue && entities.value.length > 0) {
    selectedEntity.value = entities.value.find(e => e.id == newValue)
    if (selectedEntity.value) {
      form.customer_id = selectedEntity.value.customer_id || selectedEntity.value.user_id
      form.refund_amount = selectedEntity.value.total_amount || selectedEntity.value.total_collected || 0
    }
  } else {
    selectedEntity.value = null
  }
})

// Methods
const onTypeChange = async () => {
  form.entity_id = ''
  selectedEntity.value = null
  entities.value = []
  
  if (form.type) {
    await loadEntities()
  }
}

const onEntityChange = () => {
  // Logic handled by watcher
}

const loadEntities = async () => {
  try {
    loading.entities = true
    const endpoint = form.type === 'order' ? 'eligible-orders' : 'eligible-lotteries'
    const response = await api.get(`/merchant/${endpoint}`)
    entities.value = response.data.data
  } catch (error) {
    console.error('Erreur chargement entités:', error)
    toast.error('Erreur lors du chargement des données')
  } finally {
    loading.entities = false
  }
}

const submitRequest = async () => {
  if (!isFormValid.value) return
  
  try {
    submitting.value = true
    
    const payload = {
      refund_type: form.refund_type,
      reason: form.reason,
      refund_amount: parseFloat(form.refund_amount),
      customer_id: form.customer_id,
      customer_phone: form.customer_phone,
      payment_operator: form.payment_operator
    }
    
    if (form.type === 'order') {
      payload.order_id = parseInt(form.entity_id)
    } else {
      payload.lottery_id = parseInt(form.entity_id)
    }
    
    if (selectedEntity.value?.product_id) {
      payload.product_id = selectedEntity.value.product_id
    }
    
    await api.post('/merchant/payout-requests', payload)
    
    emit('created')
    
  } catch (error) {
    console.error('Erreur création demande:', error)
    const message = error.response?.data?.message || 'Erreur lors de la création de la demande'
    toast.error(message)
  } finally {
    submitting.value = false
  }
}

// Helper functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(amount || 0)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}
</script>