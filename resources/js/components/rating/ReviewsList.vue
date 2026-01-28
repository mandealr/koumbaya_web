<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      <div v-if="showFilter" class="flex items-center gap-2">
        <select
          v-model="filter"
          class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
        >
          <option value="all">Tous les avis</option>
          <option value="positive">Positifs (4-5★)</option>
          <option value="neutral">Neutres (3★)</option>
          <option value="negative">Négatifs (1-2★)</option>
          <option value="verified">Vérifiés uniquement</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <div v-for="n in 3" :key="n" class="animate-pulse bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            <div class="h-12 bg-gray-200 rounded w-full"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="!reviews || reviews.length === 0" class="text-center py-12 bg-gray-50 rounded-xl">
      <ChatBubbleLeftRightIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
      <h4 class="text-lg font-medium text-gray-900 mb-2">Aucun avis</h4>
      <p class="text-gray-500">{{ emptyMessage }}</p>
    </div>

    <!-- Reviews list -->
    <div v-else class="space-y-4">
      <ReviewCard
        v-for="review in filteredReviews"
        :key="review.id"
        :review="review"
        :show-actions="showActions"
        :can-edit="currentUserId === review.reviewer?.id"
        :can-delete="currentUserId === review.reviewer?.id"
        @helpful="$emit('helpful', $event)"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex items-center justify-center gap-2">
      <button
        :disabled="pagination.current_page === 1"
        @click="$emit('page-change', pagination.current_page - 1)"
        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Précédent
      </button>
      <span class="text-sm text-gray-600">
        Page {{ pagination.current_page }} sur {{ pagination.last_page }}
      </span>
      <button
        :disabled="pagination.current_page === pagination.last_page"
        @click="$emit('page-change', pagination.current_page + 1)"
        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Suivant
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ReviewCard from './ReviewCard.vue'
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  reviews: {
    type: Array,
    default: () => []
  },
  pagination: {
    type: Object,
    default: null
  },
  title: {
    type: String,
    default: 'Avis clients'
  },
  emptyMessage: {
    type: String,
    default: 'Soyez le premier à laisser un avis !'
  },
  loading: {
    type: Boolean,
    default: false
  },
  showFilter: {
    type: Boolean,
    default: false
  },
  showActions: {
    type: Boolean,
    default: false
  },
  currentUserId: {
    type: [Number, String],
    default: null
  }
})

defineEmits(['helpful', 'edit', 'delete', 'page-change'])

const filter = ref('all')

const filteredReviews = computed(() => {
  if (!props.reviews) return []

  switch (filter.value) {
    case 'positive':
      return props.reviews.filter(r => r.rating >= 4)
    case 'neutral':
      return props.reviews.filter(r => r.rating === 3)
    case 'negative':
      return props.reviews.filter(r => r.rating <= 2)
    case 'verified':
      return props.reviews.filter(r => r.is_verified)
    default:
      return props.reviews
  }
})
</script>
