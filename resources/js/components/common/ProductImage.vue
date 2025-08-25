<template>
  <div class="relative overflow-hidden bg-gray-100 flex items-center justify-center" :class="containerClass">
    <img 
      v-if="!imageError && imageUrl"
      :src="imageUrl" 
      :alt="alt || 'Image du produit'" 
      :class="imageClass"
      @error="handleImageError"
      @load="handleImageLoad"
    />
    
    <!-- Fallback: Image non disponible -->
    <div 
      v-if="imageError || !imageUrl"
      :class="fallbackClass"
      class="flex flex-col items-center justify-center text-gray-600 bg-gray-100"
    >
      <svg class="w-8 h-8 mb-2" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
      </svg>
      <span class="text-xs font-medium">{{ fallbackText }}</span>
    </div>

    <!-- Loading spinner -->
    <div 
      v-if="loading && !imageError"
      class="absolute inset-0 flex items-center justify-center bg-gray-100"
    >
      <svg class="animate-spin h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: ''
  },
  alt: {
    type: String,
    default: ''
  },
  fallbackText: {
    type: String,
    default: 'Image non disponible'
  },
  containerClass: {
    type: String,
    default: 'w-full h-48'
  },
  imageClass: {
    type: String,
    default: 'w-full h-full object-cover'
  },
  fallbackClass: {
    type: String,
    default: 'w-full h-full'
  }
})

const imageError = ref(false)
const loading = ref(false)

const imageUrl = computed(() => {
  if (!props.src) return ''
  
  // Si c'est déjà une URL complète, la retourner telle quelle
  if (props.src.startsWith('http')) {
    return props.src
  }
  
  // Si c'est un chemin relatif qui commence par /, l'utiliser tel quel
  if (props.src.startsWith('/')) {
    return props.src
  }
  
  // Sinon, ajouter le préfixe pour les images uploadées
  return `/storage/products/${props.src}`
})

const handleImageError = () => {
  imageError.value = true
  loading.value = false
}

const handleImageLoad = () => {
  imageError.value = false
  loading.value = false
}

// Reset error state when src changes
const { src } = toRefs(props)
watch(src, () => {
  imageError.value = false
  loading.value = true
})
</script>

<script>
import { toRefs, watch } from 'vue'
export default {
  name: 'ProductImage'
}
</script>