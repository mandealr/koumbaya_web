<template>
  <div class="flex items-center" :class="containerClass">
    <!-- Interactive mode -->
    <template v-if="interactive">
      <button
        v-for="n in 5"
        :key="n"
        type="button"
        @click="$emit('update:modelValue', n)"
        @mouseenter="hoverValue = n"
        @mouseleave="hoverValue = 0"
        class="focus:outline-none transition-transform hover:scale-110"
        :disabled="disabled"
      >
        <StarIcon
          :class="[
            starSize,
            (hoverValue || modelValue) >= n ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'
          ]"
        />
      </button>
    </template>

    <!-- Display mode -->
    <template v-else>
      <div class="flex items-center">
        <template v-for="n in 5" :key="n">
          <StarIcon
            v-if="n <= Math.floor(rating)"
            :class="[starSize, 'text-yellow-400 fill-yellow-400']"
          />
          <div v-else-if="n === Math.ceil(rating) && rating % 1 !== 0" class="relative">
            <StarIcon :class="[starSize, 'text-gray-300']" />
            <div class="absolute inset-0 overflow-hidden" :style="{ width: `${(rating % 1) * 100}%` }">
              <StarIcon :class="[starSize, 'text-yellow-400 fill-yellow-400']" />
            </div>
          </div>
          <StarIcon v-else :class="[starSize, 'text-gray-300']" />
        </template>
      </div>
    </template>

    <!-- Rating text -->
    <span v-if="showValue && !interactive" :class="['ml-2 font-medium', textSize, textColor]">
      {{ rating.toFixed(1) }}
    </span>
    <span v-if="showCount && reviewCount !== null" :class="['ml-1', textSize, 'text-gray-500']">
      ({{ reviewCount }} avis)
    </span>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { StarIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  rating: {
    type: Number,
    default: 0
  },
  modelValue: {
    type: Number,
    default: 0
  },
  reviewCount: {
    type: Number,
    default: null
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v)
  },
  showValue: {
    type: Boolean,
    default: false
  },
  showCount: {
    type: Boolean,
    default: false
  },
  interactive: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  containerClass: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])

const hoverValue = ref(0)

const starSize = {
  sm: 'w-4 h-4',
  md: 'w-5 h-5',
  lg: 'w-6 h-6',
  xl: 'w-8 h-8'
}[props.size]

const textSize = {
  sm: 'text-sm',
  md: 'text-base',
  lg: 'text-lg',
  xl: 'text-xl'
}[props.size]

const textColor = 'text-gray-700'
</script>
