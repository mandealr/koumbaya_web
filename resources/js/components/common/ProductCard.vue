<template>
  <div class="koumbaya-card group cursor-pointer overflow-hidden bg-white hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
    <!-- Image avec gradient overlay -->
    <div class="relative overflow-hidden">
      <div class="aspect-w-16 aspect-h-10 bg-gradient-to-br from-koumbaya-primary/5 to-koumbaya-primary/20">
        <ProductImage 
          :src="product.image_url || product.main_image || product.image" 
          :alt="product.name"
          container-class="w-full h-48"
          image-class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        />
      </div>
      
      <!-- Badge de statut -->
      <div class="absolute top-4 right-4">
        <span class="koumbaya-badge koumbaya-badge-success" v-if="product.status === 'active'">
          <CheckCircleIcon class="w-3 h-3 mr-1 flex-shrink-0" />
          Actif
        </span>
        <span class="koumbaya-badge koumbaya-badge-warning" v-else-if="product.status === 'pending'">
          <ClockIcon class="w-3 h-3 mr-1 flex-shrink-0" />
          En attente
        </span>
        <span class="koumbaya-badge koumbaya-badge-error" v-else>
          <XCircleIcon class="w-3 h-3 mr-1 flex-shrink-0" />
          Inactif
        </span>
      </div>

      <!-- Badge de popularité -->
      <div class="absolute top-4 left-4" v-if="product.isPopular">
        <span class="koumbaya-badge bg-gradient-to-r from-amber-400 to-orange-500 text-white border-0">
          <StarIcon class="w-3 h-3 mr-1 fill-current flex-shrink-0" />
          Populaire
        </span>
      </div>
    </div>

    <!-- Contenu -->
    <div class="koumbaya-card-body space-y-4">
      <!-- Catégorie et Mode de vente -->
      <div class="flex items-center justify-between">
        <div class="flex items-center text-koumbaya-primary text-sm font-medium">
          <TagIcon class="w-4 h-4 mr-2 flex-shrink-0" />
          {{ product.category?.name || product.category || 'Électronique' }}
        </div>
        <div :class="getSaleModeClass(product.sale_mode)" class="flex items-center">
          <TicketIcon v-if="product.sale_mode === 'lottery'" class="w-3.5 h-3.5 mr-1 flex-shrink-0" />
          <ShoppingBagIcon v-else class="w-3.5 h-3.5 mr-1 flex-shrink-0" />
          {{ getSaleModeLabel(product.sale_mode) }}
        </div>
      </div>

      <!-- Vendeur -->
      <div v-if="product.merchant" class="flex items-center text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
        <UserCircleIcon class="w-4 h-4 mr-2 flex-shrink-0 text-gray-500" />
        <span class="text-gray-700 font-medium">{{ getMerchantName(product.merchant) }}</span>
      </div>

      <!-- Titre et description -->
      <div class="space-y-2">
        <h3 class="koumbaya-heading-4 line-clamp-2 group-hover:text-koumbaya-primary transition-colors">
          {{ product.name }}
        </h3>
        <p class="koumbaya-text-small text-gray-600 line-clamp-2">
          {{ product.description }}
        </p>
      </div>

      <!-- Prix et tickets -->
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <div class="text-2xl font-bold text-koumbaya-primary">
            {{ formatPrice(product.value) }}
          </div>
          <div class="text-sm text-gray-500">
            Valeur du lot
          </div>
        </div>
        <div class="text-right space-y-1">
          <div class="font-semibold text-gray-900">
            {{ formatPrice(product.ticketPrice || 500) }} FCFA
          </div>
          <div class="text-sm text-gray-500">
            Prix du ticket
          </div>
        </div>
      </div>

      <!-- Progression des ventes -->
      <div class="space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Progression</span>
          <span class="font-medium">{{ product.soldTickets || 0 }}/{{ product.totalTickets || 1000 }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div 
            class="bg-gradient-to-r from-koumbaya-primary to-blue-600 h-2 rounded-full transition-all duration-300"
            :style="{ width: progressPercentage + '%' }"
          ></div>
        </div>
        <div class="text-xs text-gray-500">
          {{ progressPercentage }}% vendu
        </div>
      </div>

      <!-- Date de tirage -->
      <div class="flex items-center text-sm text-gray-600">
        <CalendarIcon class="w-4 h-4 mr-2 flex-shrink-0" />
        Tirage le {{ formatDate(product.drawDate || new Date()) }}
      </div>
    </div>

    <!-- Actions -->
    <div class="koumbaya-card-footer">
      <div class="flex gap-3">
        <button 
          @click="onViewDetails"
          class="flex-1 koumbaya-btn koumbaya-btn-primary group-hover:shadow-lg transition-all duration-300"
        >
          <EyeIcon class="w-4 h-4" />
          Voir détails
        </button>
        <button 
          @click="onQuickBuy"
          class="koumbaya-btn koumbaya-btn-outline hover:bg-koumbaya-primary hover:text-white border-koumbaya-primary text-koumbaya-primary"
          :disabled="product.status !== 'active'"
        >
          <ShoppingCartIcon class="w-4 h-4" />
          Acheter
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  CheckCircleIcon,
  ClockIcon,
  XCircleIcon,
  StarIcon,
  TagIcon,
  CalendarIcon,
  EyeIcon,
  ShoppingCartIcon,
  TicketIcon,
  ShoppingBagIcon,
  UserCircleIcon
} from '@heroicons/vue/24/outline'
import ProductImage from './ProductImage.vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['view-details', 'quick-buy'])

const progressPercentage = computed(() => {
  const sold = props.product.soldTickets || 0
  const total = props.product.totalTickets || 1000
  return Math.round((sold / total) * 100)
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price || 0).replace('XAF', 'FCFA')
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const getSaleModeClass = (saleMode) => {
  const classes = {
    'direct': 'bg-green-100 text-green-800 px-2 py-1 text-xs font-medium rounded-lg',
    'lottery': 'bg-purple-100 text-purple-800 px-2 py-1 text-xs font-medium rounded-lg'
  }
  return classes[saleMode] || 'bg-gray-100 text-gray-800 px-2 py-1 text-xs font-medium rounded-lg'
}

const getSaleModeLabel = (saleMode) => {
  const labels = {
    'direct': 'Direct',
    'lottery': 'Tombola'
  }
  return labels[saleMode] || labels['direct'] // Par défaut 'Direct' si non défini
}

const getMerchantName = (merchant) => {
  if (!merchant) return 'Vendeur non spécifié'

  // Si c'est un objet avec les propriétés first_name et last_name
  if (merchant.first_name && merchant.last_name) {
    return `${merchant.first_name} ${merchant.last_name}`
  }

  // Si c'est un objet avec company_name
  if (merchant.company_name) {
    return merchant.company_name
  }

  // Si c'est un objet avec name
  if (merchant.name) {
    return merchant.name
  }

  // Sinon retourner une valeur par défaut
  return 'Vendeur'
}

const onViewDetails = () => {
  emit('view-details', props.product)
}

const onQuickBuy = () => {
  emit('quick-buy', props.product)
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.aspect-w-16 {
  position: relative;
  padding-bottom: 62.5%; /* 16:10 */
}

.aspect-w-16 > * {
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
</style>