<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-gray-600 bg-opacity-40 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="koumbaya-card inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-white shadow-xl transform transition-all">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">Lancer le tirage</h3>
        </div>

        <div class="koumbaya-card-body">
          <p class="koumbaya-text-body">
            Êtes-vous sûr de vouloir lancer le tirage pour cette tombola ?
            Cette action est irréversible.
          </p>
        </div>

        <div class="koumbaya-card-footer">
          <div class="flex gap-4">
            <button
              @click="close"
              class="koumbaya-btn koumbaya-btn-outline flex-1"
            >
              Annuler
            </button>
            <button
              @click="confirm"
              class="koumbaya-btn koumbaya-btn-primary flex-1"
            >
              Confirmer
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

const emit = defineEmits(['close', 'confirm'])

const close = () => {
  emit('close')
}

const confirm = () => {
  emit('confirm', props.lottery)
}
</script>
