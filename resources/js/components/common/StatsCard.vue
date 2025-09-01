<template>
  <div class="koumbaya-card group hover:scale-105 transition-all duration-300 cursor-pointer">
    <div class="koumbaya-card-body">
      <div v-if="loading" class="flex items-center justify-center h-24">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
      <div v-else class="flex items-center justify-between">
        <div class="flex-1">
          <!-- Titre et valeur -->
          <div class="space-y-1">
            <p class="text-sm font-medium text-gray-600 group-hover:text-gray-700 transition-colors">
              {{ title }}
            </p>
            <p class="text-3xl font-bold text-gray-900 group-hover:text-koumbaya-primary transition-colors">
              {{ formattedValue }}
            </p>
          </div>

          <!-- Variation -->
          <div class="flex items-center mt-2 space-x-1" v-if="change !== undefined">
            <component 
              :is="changeIcon" 
              :class="changeColorClass"
              class="w-4 h-4 flex-shrink-0"
            />
            <span 
              :class="changeColorClass"
              class="text-sm font-medium"
            >
              {{ Math.abs(change) }}%
            </span>
            <span class="text-sm text-gray-500">
              vs {{ period || 'mois dernier' }}
            </span>
          </div>
        </div>

        <!-- Icône avec background coloré -->
        <div 
          :class="iconBgClass"
          class="p-4 rounded-xl group-hover:scale-110 transition-transform duration-300"
        >
          <component 
            :is="icon" 
            :class="iconColorClass"
            class="w-8 h-8"
          />
        </div>
      </div>

      <!-- Graphique ou barre de progression (optionnel) -->
      <div v-if="showProgress && progressValue !== undefined" class="mt-4">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span>Objectif: {{ progressTarget }}</span>
          <span>{{ Math.round(progressPercentage) }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div 
            :class="progressBarClass"
            class="h-2 rounded-full transition-all duration-500"
            :style="{ width: progressPercentage + '%' }"
          ></div>
        </div>
      </div>

      <!-- Actions rapides -->
      <div v-if="actions?.length" class="flex gap-2 mt-4">
        <button
          v-for="action in actions"
          :key="action.label"
          @click="handleAction(action)"
          class="flex-1 text-xs px-3 py-1.5 rounded-lg font-medium transition-all duration-200"
          :class="action.primary ? 'bg-koumbaya-primary text-white hover:bg-koumbaya-primary-dark' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
        >
          {{ action.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { 
  ArrowUpIcon, 
  ArrowDownIcon,
  MinusIcon 
} from '@heroicons/vue/24/outline'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  icon: {
    type: [Object, Function],
    required: true
  },
  change: {
    type: Number,
    default: undefined
  },
  period: {
    type: String,
    default: 'mois dernier'
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'orange', 'red', 'purple', 'indigo'].includes(value)
  },
  format: {
    type: String,
    default: 'number',
    validator: (value) => ['number', 'currency', 'percentage'].includes(value)
  },
  showProgress: {
    type: Boolean,
    default: false
  },
  progressValue: {
    type: Number,
    default: undefined
  },
  progressTarget: {
    type: Number,
    default: 100
  },
  actions: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['action'])

const formattedValue = computed(() => {
  const val = props.value
  
  switch (props.format) {
    case 'currency':
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XAF',
        minimumFractionDigits: 0
      }).format(val).replace('XAF', 'FCFA')
    case 'percentage':
      return `${val}%`
    default:
      return new Intl.NumberFormat('fr-FR').format(val)
  }
})

const changeIcon = computed(() => {
  if (props.change === undefined) return MinusIcon
  return props.change > 0 ? ArrowUpIcon : props.change < 0 ? ArrowDownIcon : MinusIcon
})

const changeColorClass = computed(() => {
  if (props.change === undefined) return 'text-gray-400'
  return props.change > 0 ? 'text-blue-600' : props.change < 0 ? 'text-red-600' : 'text-gray-400'
})

const colorClasses = {
  blue: {
    iconBg: 'bg-blue-100 group-hover:bg-blue-200',
    iconColor: 'text-blue-600',
    progress: 'bg-gradient-to-r from-blue-500 to-blue-600'
  },
  green: {
    iconBg: 'bg-blue-100 group-hover:bg-blue-200',
    iconColor: 'text-blue-600',
    progress: 'bg-gradient-to-r from-blue-500 to-blue-600'
  },
  orange: {
    iconBg: 'bg-orange-100 group-hover:bg-orange-200',
    iconColor: 'text-orange-600',
    progress: 'bg-gradient-to-r from-orange-500 to-orange-600'
  },
  red: {
    iconBg: 'bg-red-100 group-hover:bg-red-200',
    iconColor: 'text-red-600',
    progress: 'bg-gradient-to-r from-red-500 to-red-600'
  },
  purple: {
    iconBg: 'bg-purple-100 group-hover:bg-purple-200',
    iconColor: 'text-purple-600',
    progress: 'bg-gradient-to-r from-purple-500 to-purple-600'
  },
  indigo: {
    iconBg: 'bg-indigo-100 group-hover:bg-indigo-200',
    iconColor: 'text-indigo-600',
    progress: 'bg-gradient-to-r from-indigo-500 to-indigo-600'
  }
}

const iconBgClass = computed(() => colorClasses[props.color]?.iconBg || colorClasses.blue.iconBg)
const iconColorClass = computed(() => colorClasses[props.color]?.iconColor || colorClasses.blue.iconColor)
const progressBarClass = computed(() => colorClasses[props.color]?.progress || colorClasses.blue.progress)

const progressPercentage = computed(() => {
  if (!props.showProgress || props.progressValue === undefined) return 0
  return Math.min((props.progressValue / props.progressTarget) * 100, 100)
})

const handleAction = (action) => {
  emit('action', action)
}
</script>