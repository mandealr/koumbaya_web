<template>
  <div :class="['bg-white rounded-xl border border-gray-200', containerClass]">
    <!-- Header -->
    <div class="p-4 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">{{ title }}</h3>
        <MerchantRatingBadge v-if="rating" :badge="rating.badge" />
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <div v-if="loading" class="animate-pulse space-y-3">
        <div class="h-8 bg-gray-200 rounded w-1/2"></div>
        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
      </div>

      <div v-else-if="rating">
        <!-- Score principal -->
        <div class="flex items-center gap-4 mb-4">
          <div class="text-4xl font-bold text-gray-900">
            {{ rating.avg_rating?.toFixed(1) || '0.0' }}
          </div>
          <div>
            <RatingStars :rating="rating.avg_rating || 0" size="lg" />
            <p class="text-sm text-gray-500 mt-1">
              {{ rating.total_reviews || 0 }} avis
            </p>
          </div>
        </div>

        <!-- Barres de distribution -->
        <div v-if="showDistribution" class="space-y-2 mb-4">
          <div v-for="n in 5" :key="n" class="flex items-center gap-2">
            <span class="text-sm text-gray-600 w-8">{{ 6 - n }}★</span>
            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
              <div
                class="h-full bg-yellow-400 rounded-full transition-all"
                :style="{ width: getDistributionPercentage(6 - n) + '%' }"
              ></div>
            </div>
            <span class="text-sm text-gray-500 w-10 text-right">
              {{ getDistributionCount(6 - n) }}
            </span>
          </div>
        </div>

        <!-- Scores détaillés -->
        <div v-if="showDetails" class="grid grid-cols-3 gap-3 pt-4 border-t border-gray-100">
          <div class="text-center">
            <div class="text-lg font-semibold text-[#0099cc]">
              {{ Math.round(rating.activity_score || 50) }}
            </div>
            <div class="text-xs text-gray-500">Activité</div>
          </div>
          <div class="text-center">
            <div class="text-lg font-semibold text-green-600">
              {{ Math.round(rating.quality_score || 50) }}
            </div>
            <div class="text-xs text-gray-500">Qualité</div>
          </div>
          <div class="text-center">
            <div class="text-lg font-semibold text-purple-600">
              {{ Math.round(rating.reliability_score || 50) }}
            </div>
            <div class="text-xs text-gray-500">Fiabilité</div>
          </div>
        </div>

        <!-- Statistiques -->
        <div v-if="showStats" class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100 mt-4">
          <div class="flex items-center gap-2">
            <CheckCircleIcon class="w-5 h-5 text-green-500" />
            <span class="text-sm text-gray-600">{{ rating.completed_sales || 0 }} ventes</span>
          </div>
          <div class="flex items-center gap-2">
            <ShieldCheckIcon class="w-5 h-5 text-blue-500" />
            <span class="text-sm text-gray-600">{{ rating.verified_reviews || 0 }} vérifiés</span>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-6">
        <StarIcon class="w-12 h-12 text-gray-300 mx-auto mb-2" />
        <p class="text-gray-500">Aucune notation disponible</p>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="showViewMore && rating" class="px-4 py-3 bg-gray-50 border-t border-gray-100 rounded-b-xl">
      <router-link
        v-if="merchantId"
        :to="`/merchants/${merchantId}/reviews`"
        class="text-sm text-[#0099cc] hover:text-[#0088bb] font-medium"
      >
        Voir tous les avis →
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import RatingStars from './RatingStars.vue'
import MerchantRatingBadge from './MerchantRatingBadge.vue'
import { StarIcon, CheckCircleIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  rating: {
    type: Object,
    default: null
  },
  merchantId: {
    type: [Number, String],
    default: null
  },
  title: {
    type: String,
    default: 'Notation vendeur'
  },
  loading: {
    type: Boolean,
    default: false
  },
  showDistribution: {
    type: Boolean,
    default: false
  },
  showDetails: {
    type: Boolean,
    default: false
  },
  showStats: {
    type: Boolean,
    default: false
  },
  showViewMore: {
    type: Boolean,
    default: false
  },
  containerClass: {
    type: String,
    default: ''
  }
})

const getDistributionPercentage = (stars) => {
  if (!props.rating || !props.rating.total_reviews) return 0
  const count = getDistributionCount(stars)
  return (count / props.rating.total_reviews) * 100
}

const getDistributionCount = (stars) => {
  if (!props.rating) return 0
  if (stars >= 4) return props.rating.positive_reviews || 0
  if (stars === 3) return props.rating.neutral_reviews || 0
  return props.rating.negative_reviews || 0
}
</script>
