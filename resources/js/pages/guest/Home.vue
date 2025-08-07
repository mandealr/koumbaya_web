<template>
  <div>
    <!-- Hero Section - Koumbaya Brand -->
    <section class="bg-gradient-to-br from-[#0099cc] via-[#0088bb] to-[#007799] text-white relative overflow-hidden">
      <div class="absolute inset-0 bg-black/10"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
        <div class="text-center koumbaya-fade-in">
          <h1 class="text-5xl md:text-7xl font-bold mb-8 leading-tight">
            Gagnez des
            <span class="text-cyan-200 bg-gradient-to-r from-cyan-200 to-white bg-clip-text text-transparent">produits incroyables</span>
          </h1>
          <p class="text-xl md:text-2xl mb-12 text-blue-100 max-w-4xl mx-auto leading-relaxed font-light">
            🎯 Koumbaya est la première plateforme de loteries premium au Cameroun.<br>
            Achetez vos billets et tentez de remporter des prix exceptionnels avec transparence totale.
          </p>
          <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
            <router-link
              to="/register"
              class="koumbaya-btn koumbaya-btn-secondary koumbaya-btn-lg shadow-2xl hover:shadow-cyan-500/25 transform hover:scale-105"
            >
              🚀 Commencer maintenant
            </router-link>
            <button
              @click="scrollToProducts"
              class="koumbaya-btn koumbaya-btn-outline koumbaya-btn-lg border-white text-white hover:bg-white hover:text-[#0099cc]"
            >
              👀 Voir les produits
            </button>
          </div>
          <div class="mt-16 flex justify-center items-center gap-8 text-blue-200">
            <div class="text-center">
              <div class="text-3xl font-bold">1000+</div>
              <div class="text-sm">Participants</div>
            </div>
            <div class="w-px h-12 bg-blue-300/30"></div>
            <div class="text-center">
              <div class="text-3xl font-bold">50+</div>
              <div class="text-sm">Produits gagnés</div>
            </div>
            <div class="w-px h-12 bg-blue-300/30"></div>
            <div class="text-center">
              <div class="text-3xl font-bold">100%</div>
              <div class="text-sm">Transparent</div>
            </div>
          </div>
        </div>
      </div>
      <!-- Forme décorative -->
      <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
    </section>

    <!-- Features Section - Design amélioré -->
    <section class="py-32 bg-gradient-to-b from-white to-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
          <h2 class="koumbaya-heading-2 text-4xl md:text-5xl text-gray-900 mb-6">
            Pourquoi choisir <span class="koumbaya-text-primary">Koumbaya</span> ?
          </h2>
          <p class="koumbaya-text-body text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed">
            Une plateforme moderne, sécurisée et transparente pour tous vos jeux de loterie.<br>
            Découvrez les avantages qui font la différence.
          </p>
        </div>
        
        <div class="koumbaya-grid-3 gap-12">
          <div v-for="feature in features" :key="feature.title" class="koumbaya-card text-center group hover:scale-105 transition-transform">
            <div class="koumbaya-card-body">
              <div class="w-20 h-20 bg-gradient-to-br from-[#0099cc] to-[#007799] rounded-2xl flex items-center justify-center mx-auto mb-8 group-hover:shadow-lg group-hover:shadow-cyan-500/25 transition-shadow">
                <component :is="feature.icon" class="w-10 h-10 text-white" />
              </div>
              <h3 class="koumbaya-heading-4 text-gray-900 mb-4">{{ feature.title }}</h3>
              <p class="koumbaya-text-body text-gray-600 leading-relaxed">{{ feature.description }}</p>
            </div>
          </div>
        </div>
        
        <!-- Badges de confiance -->
        <div class="mt-20 pt-12 border-t border-gray-200">
          <div class="flex flex-wrap justify-center items-center gap-8 text-gray-500">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </div>
              <span class="font-medium">100% Sécurisé</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <span class="font-medium">Paiements certifiés</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <span class="font-medium">Support 24/7</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Products Section - Design premium -->
    <section id="products" class="py-32 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
          <h2 class="koumbaya-heading-2 text-4xl md:text-5xl text-gray-900 mb-6">
            🎁 Produits en <span class="koumbaya-text-primary">loterie</span>
          </h2>
          <p class="koumbaya-text-body text-xl text-gray-600 max-w-3xl mx-auto">
            Découvrez les incroyables produits que vous pouvez gagner.<br>
            Des marques prestigieuses, des prix exceptionnels !
          </p>
        </div>
        
        <div class="koumbaya-grid-3 gap-8">
          <div
            v-for="product in featuredProducts"
            :key="product.id"
            class="koumbaya-card group hover:scale-105 transition-all duration-300 relative overflow-hidden"
          >
            <!-- Badge de popularité -->
            <div v-if="product.progress > 80" class="absolute top-4 left-4 z-10">
              <div class="koumbaya-badge koumbaya-badge-warning shadow-md">
                🔥 Populaire
              </div>
            </div>
            
            <!-- Image du produit avec overlay -->
            <div class="relative overflow-hidden">
              <img
                :src="product.image"
                :alt="product.title"
                class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </div>
            
            <div class="koumbaya-card-body">
              <h3 class="koumbaya-heading-4 text-gray-900 mb-3 group-hover:text-[#0099cc] transition-colors">
                {{ product.title }}
              </h3>
              <p class="koumbaya-text-small text-gray-600 mb-6 leading-relaxed">
                {{ product.description }}
              </p>
              
              <!-- Prix et informations -->
              <div class="flex justify-between items-center mb-6">
                <div class="text-3xl font-bold text-[#0099cc]">{{ product.price }} FCFA</div>
                <div class="koumbaya-badge koumbaya-badge-primary">
                  {{ product.ticket_price }} FCFA/billet
                </div>
              </div>
              
              <!-- Barre de progression stylisée -->
              <div class="mb-6">
                <div class="flex justify-between text-sm font-medium text-gray-700 mb-2">
                  <span>🎯 Progression</span>
                  <span>{{ product.progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 relative overflow-hidden">
                  <div 
                    class="bg-gradient-to-r from-[#0099cc] to-[#00aadd] h-full rounded-full transition-all duration-700 relative" 
                    :style="{ width: product.progress + '%' }"
                  >
                    <div class="absolute inset-0 bg-white/20 animate-pulse rounded-full"></div>
                  </div>
                </div>
              </div>
              
              <!-- Bouton d'action -->
              <router-link
                to="/register"
                class="koumbaya-btn koumbaya-btn-primary koumbaya-btn-full group-hover:shadow-lg"
              >
                🎫 Acheter des billets
              </router-link>
            </div>
          </div>
        </div>
        
        <!-- Call-to-action -->
        <div class="text-center mt-16">
          <router-link
            to="/register"
            class="koumbaya-btn koumbaya-btn-outline koumbaya-btn-lg"
          >
            Voir tous les produits
            <ArrowRightIcon class="ml-2 w-6 h-6" />
          </router-link>
        </div>
      </div>
    </section>

    <!-- How it Works Section -->
    <section id="how-it-works" class="py-24 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Comment ça marche ?
          </h2>
          <p class="text-xl text-gray-600">
            Participer aux loteries Koumbaya est simple et rapide
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div v-for="(step, index) in steps" :key="step.title" class="text-center">
            <div class="relative">
              <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="text-2xl font-bold text-white">{{ index + 1 }}</span>
              </div>
              <div v-if="index < steps.length - 1" class="hidden md:block absolute top-8 left-16 w-full h-0.5 bg-green-200"></div>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ step.title }}</h3>
            <p class="text-gray-600">{{ step.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 bg-green-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
          Prêt à tenter votre chance ?
        </h2>
        <p class="text-xl text-green-100 mb-8 max-w-2xl mx-auto">
          Rejoignez des milliers de participants et augmentez vos chances de gagner des produits exceptionnels
        </p>
        <router-link
          to="/register"
          class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-green-600 bg-white hover:bg-gray-100 transition-colors"
        >
          Créer mon compte gratuitement
          <ArrowRightIcon class="ml-2 w-6 h-6" />
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import {
  ShieldCheckIcon,
  GiftIcon,
  UserGroupIcon,
  ArrowRightIcon
} from '@heroicons/vue/24/outline'

const features = ref([
  {
    title: 'Sécurisé & Transparent',
    description: 'Tous les tirages sont équitables et vérifiables. Vos données personnelles sont protégées.',
    icon: ShieldCheckIcon
  },
  {
    title: 'Produits de Qualité',
    description: 'Une sélection rigoureuse de produits neufs et authentiques de grandes marques.',
    icon: GiftIcon
  },
  {
    title: 'Communauté Active',
    description: 'Rejoignez des milliers de joueurs satisfaits partout au Cameroun.',
    icon: UserGroupIcon
  }
])

const featuredProducts = ref([
  {
    id: 1,
    title: 'iPhone 15 Pro',
    description: 'Le dernier flagship d\'Apple avec une caméra révolutionnaire',
    price: '750,000',
    ticket_price: '1,000',
    progress: 85,
    image: '/images/products/iphone15.jpg'
  },
  {
    id: 2,
    title: 'MacBook Pro M3',
    description: 'Puissance et performance pour les créateurs',
    price: '1,200,000',
    ticket_price: '2,000',
    progress: 62,
    image: '/images/products/macbook.jpg'
  },
  {
    id: 3,
    title: 'PlayStation 5',
    description: 'Console de jeu nouvelle génération',
    price: '350,000',
    ticket_price: '500',
    progress: 95,
    image: '/images/products/ps5.jpg'
  }
])

const steps = ref([
  {
    title: 'Inscrivez-vous',
    description: 'Créez votre compte gratuitement en quelques clics'
  },
  {
    title: 'Choisissez un produit',
    description: 'Parcourez notre sélection et trouvez votre produit favori'
  },
  {
    title: 'Achetez vos billets',
    description: 'Sélectionnez le nombre de billets et effectuez le paiement'
  },
  {
    title: 'Gagnez !',
    description: 'Participez au tirage et remportez votre produit'
  }
])

const scrollToProducts = () => {
  document.getElementById('products').scrollIntoView({
    behavior: 'smooth'
  })
}
</script>