<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black/40 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="koumbaya-card inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle bg-white shadow-xl transform transition-all">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">Prolonger la tombola</h3>
        </div>
        
        <form @submit.prevent="submit" class="koumbaya-card-body space-y-4">
          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Nouvelle date de tirage</label>
            <input
              v-model="form.newDrawDate"
              type="datetime-local"
              required
              class="koumbaya-input"
            />
          </div>

          <div class="koumbaya-form-group">
            <label class="koumbaya-label">Raison de la prolongation</label>
            <textarea
              v-model="form.reason"
              rows="3"
              class="koumbaya-input"
              placeholder="Expliquez pourquoi vous prolongez cette tombola..."
              required
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
                Prolonger
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
  lottery: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'submit'])

const form = reactive({
  newDrawDate: '',
  reason: ''
})

const close = () => {
  emit('close')
}

const submit = () => {
  emit('submit', { 
    lotteryId: props.lottery.id,
    ...form 
  })
}
</script>