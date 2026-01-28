<template>
  <div class="bg-white rounded-lg border border-gray-200 p-4">
    <div class="flex items-start gap-4">
      <!-- Avatar -->
      <div class="flex-shrink-0">
        <div v-if="review.reviewer?.avatar" class="w-10 h-10 rounded-full overflow-hidden">
          <img :src="review.reviewer.avatar" :alt="review.reviewer.name" class="w-full h-full object-cover" />
        </div>
        <div v-else class="w-10 h-10 rounded-full bg-[#0099cc]/10 flex items-center justify-center">
          <UserIcon class="w-5 h-5 text-[#0099cc]" />
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
          <div>
            <h4 class="font-medium text-gray-900">
              {{ review.reviewer?.name || 'Utilisateur anonyme' }}
            </h4>
            <div class="flex items-center gap-2 mt-1">
              <RatingStars :rating="review.rating" size="sm" />
              <span v-if="review.is_verified" class="inline-flex items-center gap-1 text-xs text-green-600">
                <CheckBadgeIcon class="w-4 h-4" />
                Achat vérifié
              </span>
            </div>
          </div>
          <time class="text-sm text-gray-500 whitespace-nowrap">
            {{ formatDate(review.created_at) }}
          </time>
        </div>

        <!-- Comment -->
        <p v-if="review.comment" class="mt-3 text-gray-700 text-sm leading-relaxed">
          {{ review.comment }}
        </p>

        <!-- Product info -->
        <div v-if="review.product" class="mt-3 flex items-center gap-2 text-sm text-gray-500">
          <ShoppingBagIcon class="w-4 h-4" />
          <span>{{ review.product.name }}</span>
        </div>

        <!-- Actions -->
        <div v-if="showActions" class="mt-3 flex items-center gap-4">
          <button
            @click="$emit('helpful', review.id)"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1"
          >
            <HandThumbUpIcon class="w-4 h-4" />
            Utile ({{ review.helpful_count || 0 }})
          </button>
          <button
            v-if="canEdit"
            @click="$emit('edit', review)"
            class="text-sm text-[#0099cc] hover:text-[#0088bb] flex items-center gap-1"
          >
            <PencilIcon class="w-4 h-4" />
            Modifier
          </button>
          <button
            v-if="canDelete"
            @click="$emit('delete', review.id)"
            class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1"
          >
            <TrashIcon class="w-4 h-4" />
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import RatingStars from './RatingStars.vue'
import {
  UserIcon,
  CheckBadgeIcon,
  ShoppingBagIcon,
  HandThumbUpIcon,
  PencilIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

defineProps({
  review: {
    type: Object,
    required: true
  },
  showActions: {
    type: Boolean,
    default: false
  },
  canEdit: {
    type: Boolean,
    default: false
  },
  canDelete: {
    type: Boolean,
    default: false
  }
})

defineEmits(['helpful', 'edit', 'delete'])

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24))

  if (diffDays === 0) return 'Aujourd\'hui'
  if (diffDays === 1) return 'Hier'
  if (diffDays < 7) return `Il y a ${diffDays} jours`

  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}
</script>
