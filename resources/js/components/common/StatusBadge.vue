<template>
  <span :class="[
    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
    getBadgeClasses()
  ]">
    <component :is="getIcon()" class="w-3.5 h-3.5 mr-1" />
    {{ label }}
  </span>
</template>

<script setup>
import { 
  CheckCircleIcon,
  TrophyIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  StarIcon,
  GiftIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['success', 'winner', 'warning', 'info', 'featured', 'gift'].includes(value)
  },
  label: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'sm',
    validator: (value) => ['xs', 'sm', 'md'].includes(value)
  }
})

const getBadgeClasses = () => {
  const sizeClasses = {
    xs: 'px-2 py-0.5 text-xs',
    sm: 'px-2.5 py-1 text-xs',
    md: 'px-3 py-1.5 text-sm'
  }
  
  const colorClasses = {
    success: 'bg-green-100 text-green-800 border border-green-200',
    winner: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    warning: 'bg-orange-100 text-orange-800 border border-orange-200',
    info: 'bg-blue-100 text-blue-800 border border-blue-200',
    featured: 'bg-pink-100 text-pink-800 border border-pink-200',
    gift: 'bg-purple-100 text-purple-800 border border-purple-200'
  }
  
  return `${sizeClasses[props.size]} ${colorClasses[props.type]}`
}

const getIcon = () => {
  const icons = {
    success: CheckCircleIcon,
    winner: TrophyIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon,
    featured: StarIcon,
    gift: GiftIcon
  }
  
  return icons[props.type]
}
</script>
</template>