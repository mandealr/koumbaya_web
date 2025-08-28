<template>
  <span :class="[
    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium',
    getBadgeClasses()
  ]">
    <component :is="getIcon()" class="w-3.5 h-3.5 mr-1" />
    {{ getLabel() }}
  </span>
</template>

<script setup>
import { 
  TicketIcon,
  ShoppingBagIcon,
  CurrencyDollarIcon 
} from '@heroicons/vue/24/outline'

const props = defineProps({
  saleMode: {
    type: String,
    required: true,
    validator: (value) => ['lottery', 'direct'].includes(value)
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
    lottery: 'bg-purple-100 text-purple-800 border border-purple-200',
    direct: 'bg-blue-100 text-blue-800 border border-blue-200'
  }
  
  return `${sizeClasses[props.size]} ${colorClasses[props.saleMode]}`
}

const getIcon = () => {
  return props.saleMode === 'lottery' ? TicketIcon : ShoppingBagIcon
}

const getLabel = () => {
  return props.saleMode === 'lottery' ? 'Tombola' : 'Achat direct'
}
</script>
</template>