<template>
  <div>
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-green-600 to-green-800 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Gagnez des
            <span class="text-green-200">produits incroyables</span>
          </h1>
          <p class="text-xl md:text-2xl mb-8 text-green-100 max-w-3xl mx-auto">
            Koumbaya est la première plateforme de loteries premium au Cameroun. 
            Achetez vos billets et tentez de remporter des prix exceptionnels.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link
              to="/register"
              class="bg-white text-green-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-colors"
            >
              Commencer maintenant
            </router-link>
            <button
              @click="scrollToProducts"
              class="border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-green-600 transition-colors"
            >
              Voir les produits
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Pourquoi choisir Koumbaya ?
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Une plateforme moderne, sécurisée et transparente pour tous vos jeux de loterie
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="feature in features" :key="feature.title" class="text-center">
            <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
              <component :is="feature.icon" class="w-8 h-8 text-white" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ feature.title }}</h3>
            <p class="text-gray-600">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Produits en loterie
          </h2>
          <p class="text-xl text-gray-600">
            Découvrez les incroyables produits que vous pouvez gagner
          </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="product in featuredProducts"
            :key="product.id"
            class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
          >
            <img
              :src="product.image"
              :alt="product.title"
              class="w-full h-48 object-cover"
            />
            <div class="p-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ product.title }}</h3>
              <p class="text-gray-600 mb-4">{{ product.description }}</p>
              <div class="flex justify-between items-center mb-4">
                <span class="text-2xl font-bold text-green-600">{{ product.price }} FCFA</span>
                <span class="text-sm text-gray-500">{{ product.ticket_price }} FCFA/billet</span>
              </div>
              <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                  <span>Progression</span>
                  <span>{{ product.progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                    :style="{ width: product.progress + '%' }"
                  ></div>
                </div>
              </div>
              <router-link
                to="/register"
                class="block w-full text-center bg-green-600 text-white py-3 rounded-lg font-medium hover:bg-green-700 transition-colors"
              >
                Acheter des billets
              </router-link>
            </div>
          </div>
        </div>
        
        <div class="text-center mt-12">
          <router-link
            to="/register"
            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-green-600 bg-green-50 hover:bg-green-100"
          >
            Voir tous les produits
            <ArrowRightIcon class="ml-2 w-5 h-5" />
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