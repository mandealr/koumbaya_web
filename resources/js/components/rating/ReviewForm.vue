<template>
  <div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
      {{ existingReview ? 'Modifier votre avis' : 'Laisser un avis' }}
    </h3>

    <form @submit.prevent="submitReview">
      <!-- Rating Stars -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Votre note <span class="text-red-500">*</span>
        </label>
        <RatingStars
          v-model="form.rating"
          :interactive="true"
          size="xl"
          :disabled="submitting"
        />
        <p v-if="errors.rating" class="mt-1 text-sm text-red-600">{{ errors.rating }}</p>
      </div>

      <!-- Comment -->
      <div class="mb-4">
        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
          Votre commentaire (optionnel)
        </label>
        <textarea
          id="comment"
          v-model="form.comment"
          rows="4"
          :disabled="submitting"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all resize-none disabled:bg-gray-100"
          placeholder="Partagez votre expérience avec ce vendeur..."
        ></textarea>
        <p class="text-xs text-gray-500 mt-1">{{ form.comment.length }}/1000 caractères</p>
        <p v-if="errors.comment" class="mt-1 text-sm text-red-600">{{ errors.comment }}</p>
      </div>

      <!-- Submit Button -->
      <div class="flex items-center justify-between">
        <p v-if="successMessage" class="text-sm text-green-600 flex items-center gap-1">
          <CheckCircleIcon class="w-4 h-4" />
          {{ successMessage }}
        </p>
        <p v-if="errorMessage" class="text-sm text-red-600 flex items-center gap-1">
          <ExclamationCircleIcon class="w-4 h-4" />
          {{ errorMessage }}
        </p>
        <div class="flex-1"></div>
        <button
          type="submit"
          :disabled="submitting || !form.rating"
          class="px-6 py-2 bg-[#0099cc] text-white rounded-lg hover:bg-[#0088bb] transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <span v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
          {{ submitting ? 'Envoi...' : (existingReview ? 'Modifier' : 'Envoyer') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useApi } from '@/composables/api'
import RatingStars from './RatingStars.vue'
import { CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  merchantId: {
    type: [Number, String],
    required: true
  },
  orderId: {
    type: [Number, String],
    default: null
  },
  productId: {
    type: [Number, String],
    default: null
  },
  existingReview: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['submitted', 'error'])

const { post, put } = useApi()

const form = reactive({
  rating: props.existingReview?.rating || 0,
  comment: props.existingReview?.comment || ''
})

const errors = reactive({
  rating: null,
  comment: null
})

const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Watch for existing review changes
watch(() => props.existingReview, (review) => {
  if (review) {
    form.rating = review.rating
    form.comment = review.comment || ''
  }
}, { immediate: true })

const validateForm = () => {
  errors.rating = null
  errors.comment = null

  if (!form.rating || form.rating < 1 || form.rating > 5) {
    errors.rating = 'Veuillez sélectionner une note entre 1 et 5 étoiles'
    return false
  }

  if (form.comment.length > 1000) {
    errors.comment = 'Le commentaire ne peut pas dépasser 1000 caractères'
    return false
  }

  return true
}

const submitReview = async () => {
  if (!validateForm()) return

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const payload = {
      rating: form.rating,
      comment: form.comment || null,
      order_id: props.orderId,
      product_id: props.productId
    }

    let response
    if (props.existingReview) {
      response = await put(`/reviews/${props.existingReview.id}`, payload)
    } else {
      response = await post(`/merchants/${props.merchantId}/reviews`, payload)
    }

    if (response.success) {
      successMessage.value = props.existingReview ? 'Avis modifié avec succès' : 'Avis envoyé avec succès'
      emit('submitted', response.data)

      // Reset form if new review
      if (!props.existingReview) {
        form.rating = 0
        form.comment = ''
      }
    } else {
      errorMessage.value = response.error || 'Une erreur est survenue'
      emit('error', response.error)
    }
  } catch (err) {
    console.error('Erreur lors de l\'envoi de l\'avis:', err)
    errorMessage.value = err.response?.data?.error || 'Une erreur est survenue'
    emit('error', errorMessage.value)
  } finally {
    submitting.value = false
  }
}
</script>
