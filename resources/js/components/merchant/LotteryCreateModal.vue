<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="koumbaya-card inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle bg-white shadow-xl transform transition-all">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">Créer une tombola</h3>
        </div>
        
        <form @submit.prevent="submit" class="koumbaya-card-body space-y-4">
          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Produit</label>
            <select v-model="form.productId" required class="koumbaya-input">
              <option value="">Sélectionner un produit</option>
              <option value="1">iPhone 15 Pro</option>
              <option value="2">MacBook Pro M3</option>
              <option value="3">PlayStation 5</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="koumbaya-form-group">
              <label class="koumbaya-label">Nombre total de billets</label>
              <input
                v-model.number="form.totalTickets"
                type="number"
                required
                class="koumbaya-input"
                placeholder="1000"
                min="1"
              />
            </div>

            <div class="koumbaya-form-group">
              <label class="koumbaya-label">Date de tirage</label>
              <input
                v-model="form.drawDate"
                type="datetime-local"
                required
                class="koumbaya-input"
              />
            </div>
          </div>

          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Description de la tombola</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="koumbaya-input"
              placeholder="Description de cette tombola..."
            ></textarea>
          </div>

          <div class="koumbaya-card-footer">
            <div class="flex gap-4">
              <button
                type="button"
                @click="close"
                class="koumbaya-btn koumbaya-btn-outline flex-1"
              >
                Annuler
              </button>
              <button
                type="submit"
                class="koumbaya-btn koumbaya-btn-primary flex-1"
              >
                Créer la tombola
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  product: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'submit'])

const form = reactive({
  productId: '',
  totalTickets: 1000,
  drawDate: '',
  description: ''
})

const close = () => {
  emit('close')
}

const submit = () => {
  emit('submit', { ...form })
}
</script>