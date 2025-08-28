<template>
  <div 
    class="group cursor-pointer overflow-hidden bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100"
    :class="{'ring-2 ring-purple-500': product.sale_mode === 'lottery', 'ring-2 ring-[#0099cc]': product.sale_mode === 'direct'}"
  >
    <!-- Image avec badges -->
    <div class="relative overflow-hidden h-48">
      <ProductImage 
        :src="product.image_url || product.main_image || product.image" 
        :alt="product.name || product.title"
        container-class="w-full h-full"
        image-class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
      />
      
      <!-- Gradient overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      
      <!-- Mode de vente - Badge principal -->
      <div class="absolute top-3 left-3">
        <div v-if="product.sale_mode === 'lottery'" class="flex items-center bg-purple-600 text-white px-3 py-1.5 rounded-full shadow-lg">
          <TicketIcon class="w-4 h-4 mr-1.5" />
          <span class="text-sm font-semibold">Tombola</span>
        </div>
        <div v-else class="flex items-center bg-[#0099cc] text-white px-3 py-1.5 rounded-full shadow-lg">
          <ShoppingBagIcon class="w-4 h-4 mr-1.5" />
          <span class="text-sm font-semibold">Achat direct</span>
        </div>
      </div>

      <!-- Status -->
      <div class="absolute top-3 right-3">
        <span v-if="product.featured" class="bg-yellow-500 text-white px-2 py-1 rounded-md text-xs font-medium shadow-sm flex items-center">
          <StarIcon class="w-3.5 h-3.5 mr-1" />
          Vedette
        </span>
      </div>

      <!-- Catégorie -->
      <div class="absolute bottom-3 left-3">
        <span class="bg-black/70 backdrop-blur text-white px-2 py-1 rounded-md text-xs font-medium">
          {{ product.category?.name || 'Non catégorisé' }}
        </span>
      </div>
    </div>

    <!-- Contenu -->
    <div class="p-5 space-y-4">
      <!-- Titre -->
      <h3 class="font-bold text-lg text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
        {{ product.name || product.title }}
      </h3>

      <!-- Description -->
      <p class="text-gray-600 text-sm line-clamp-2">
        {{ product.description || 'Aucune description disponible' }}
      </p>

      <!-- Prix selon le mode -->
      <div v-if="product.sale_mode === 'lottery'" class="space-y-3">
        <!-- Prix du ticket -->
        <div class="flex justify-between items-center bg-purple-50 rounded-lg p-3">
          <div>
            <p class="text-xs text-gray-600">Prix du ticket</p>
            <p class="text-xl font-bold text-purple-700">{{ formatPrice(product.active_lottery?.ticket_price || product.ticket_price || 1000) }} FCFA</p>
          </div>
          <div class="text-right">
            <p class="text-xs text-gray-600">Valeur du lot</p>
            <p class="text-lg font-semibold text-gray-900">{{ formatPrice(product.price) }} FCFA</p>
          </div>
        </div>

        <!-- Progression -->
        <div v-if="product.active_lottery" class="space-y-1">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Progression</span>
            <span class="font-medium text-purple-700">{{ calculateProgress(product) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div 
              class="bg-gradient-to-r from-purple-500 to-purple-700 h-2.5 rounded-full transition-all duration-500"
              :style="{ width: calculateProgress(product) + '%' }"
            ></div>
          </div>
          <div class="flex justify-between text-xs text-gray-500">
            <span>{{ product.active_lottery.sold_tickets || 0 }} tickets vendus</span>
            <span>{{ product.active_lottery.total_tickets || 1000 }} au total</span>
          </div>
        </div>

        <!-- Date de tirage -->
        <div v-if="product.active_lottery?.draw_date" class="flex items-center text-sm text-gray-600">
          <CalendarIcon class="w-4 h-4 mr-2 text-purple-600" />
          <span>Tirage le {{ formatDate(product.active_lottery.draw_date) }}</span>
        </div>
      </div>

      <!-- Prix achat direct -->
      <div v-else class="space-y-3">
        <div class="bg-blue-50 rounded-lg p-3">
          <p class="text-xs text-gray-600 mb-1">Prix</p>
          <p class="text-2xl font-bold text-[#0099cc]">{{ formatPrice(product.price) }} FCFA</p>
        </div>
        
        <!-- Stock -->
        <div v-if="product.stock !== undefined" class="flex items-center text-sm">
          <span class="text-gray-600">Stock disponible:</span>
          <span class="ml-2 font-medium" :class="product.stock > 0 ? 'text-[#0099cc]' : 'text-red-600'">
            {{ product.stock > 0 ? product.stock + ' unités' : 'Épuisé' }}
          </span>
        </div>
      </div>

      <!-- Bouton d'action -->
      <button 
        @click="$emit('view-product', product)"
        class="w-full py-3 px-4 rounded-lg font-medium transition-all duration-200 flex items-center justify-center group-hover:shadow-md"
        :class="product.sale_mode === 'lottery' 
          ? 'bg-purple-600 hover:bg-purple-700 text-white' 
          : 'bg-[#0099cc] hover:bg-[#0088bb] text-white'"
      >
        <ShoppingBagIcon class="w-5 h-5 mr-2" />
        {{ product.sale_mode === 'lottery' ? 'Participer à la tombola' : 'Acheter maintenant' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { CalendarIcon, ShoppingBagIcon, TicketIcon, StarIcon } from '@heroicons/vue/24/outline'
import ProductImage from './ProductImage.vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

defineEmits(['view-product'])

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR').format(price || 0)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const calculateProgress = (product) => {
  if (!product.active_lottery) return 0
  const sold = product.active_lottery.sold_tickets || 0
  const total = product.active_lottery.total_tickets || 1
  return Math.round((sold / total) * 100)
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>