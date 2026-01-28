<template>
  <div :class="['inline-flex items-center gap-2 px-3 py-1.5 rounded-full', badgeColors]">
    <component :is="badgeIcon" class="w-4 h-4" />
    <span class="text-sm font-medium">{{ badgeLabel }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  TrophyIcon,
  StarIcon,
  SparklesIcon,
  CheckBadgeIcon,
  UserIcon
} from '@heroicons/vue/24/solid'

const props = defineProps({
  badge: {
    type: String,
    default: 'average',
    validator: (v) => ['excellent', 'very_good', 'good', 'average', 'poor'].includes(v)
  }
})

const badgeConfig = {
  excellent: {
    label: 'Excellent',
    colors: 'bg-yellow-100 text-yellow-800',
    icon: TrophyIcon
  },
  very_good: {
    label: 'Très bien',
    colors: 'bg-green-100 text-green-800',
    icon: StarIcon
  },
  good: {
    label: 'Bien',
    colors: 'bg-blue-100 text-blue-800',
    icon: SparklesIcon
  },
  average: {
    label: 'Nouveau',
    colors: 'bg-gray-100 text-gray-800',
    icon: UserIcon
  },
  poor: {
    label: 'À améliorer',
    colors: 'bg-red-100 text-red-800',
    icon: CheckBadgeIcon
  }
}

const badgeColors = computed(() => badgeConfig[props.badge]?.colors || badgeConfig.average.colors)
const badgeLabel = computed(() => badgeConfig[props.badge]?.label || 'Nouveau')
const badgeIcon = computed(() => badgeConfig[props.badge]?.icon || UserIcon)
</script>
