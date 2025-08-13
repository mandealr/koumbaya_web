<template>
  <div class="bg-white">
    <!-- Hero Section - Back Market Style -->
    <section class="relative bg-gradient-to-br from-[#0099cc]/5 via-white to-[#0099cc]/10 overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center min-h-[90vh] py-20">
          <!-- Left Content -->
          <div class="space-y-8">
            <div class="space-y-4">
              <div class="inline-flex items-center gap-2 bg-[#0099cc]/10 text-[#0099cc] px-4 py-2 rounded-full text-sm font-medium">
                <SparklesIcon class="h-4 w-4" />
                <span>Nouveau au Gabon</span>
              </div>
              <h1 class="text-5xl lg:text-6xl xl:text-7xl font-bold text-black leading-none">
                Rendre l'impossible
                <span class="text-[#0099cc]">accessible</span>
              </h1>
              <p class="text-xl lg:text-2xl text-gray-600 leading-relaxed">
                Des smartphones aux voitures, profitez de nos tombolas pour
                <strong class="text-[#0099cc]">acheter malin</strong> et obtenir des produits
                <strong class="text-[#0099cc]">à prix réduit</strong>.
              </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
              <router-link
                to="/products"
                class="inline-flex items-center justify-center gap-2 bg-[#0099cc] hover:bg-[#0088bb] text-white font-semibold px-8 py-4 rounded-2xl transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
              >
                <ShoppingBagIcon class="h-5 w-5" />
                <span>Découvrir les produits</span>
              </router-link>
              <router-link
                to="/how-it-works"
                class="inline-flex items-center justify-center gap-2 border-2 border-[#0099cc] text-[#0099cc] hover:bg-[#0099cc]/5 font-semibold px-8 py-4 rounded-2xl transition-all duration-200"
              >
                <QuestionMarkCircleIcon class="h-5 w-5" />
                <span>Comment ça marche</span>
              </router-link>
            </div>

            <!-- Trust Indicators -->
            <div class="flex items-center gap-8 pt-8">
              <div class="flex items-center gap-2 text-gray-600">
                <ShieldCheckIcon class="h-6 w-6 text-[#0099cc]" />
                <span class="font-medium">100% sécurisé</span>
              </div>
              <div class="flex items-center gap-2 text-gray-600">
                <CheckBadgeIcon class="h-6 w-6 text-[#0099cc]" />
                <span class="font-medium">Produits authentiques</span>
              </div>
              <div class="flex items-center gap-2 text-gray-600">
                <TruckIcon class="h-6 w-6 text-[#0099cc]" />
                <span class="font-medium">Livraison gratuite</span>
              </div>
            </div>
          </div>

          <!-- Right Visual -->
          <div class="relative lg:h-[600px] flex items-center justify-center">
            <div class="relative">
              <!-- Main Product Card -->
              <div class="bg-white rounded-3xl shadow-2xl p-8 max-w-sm relative z-10 border border-gray-100">
                <div class="relative mb-6">
                  <img
                    :src="placeholderImg"
                    alt="iPhone 15 Pro"
                    class="w-full h-48 object-cover rounded-2xl bg-gray-100"
                  />
                  <div class="absolute -top-2 -right-2 bg-[#0099cc] text-white px-3 py-1 rounded-full text-sm font-bold">
                    1500 FCFA
                  </div>
                </div>
                <div class="space-y-4">
                  <div>
                    <h3 class="text-xl font-bold text-black">iPhone 15 Pro Max</h3>
                    <p class="text-gray-600">Valeur: 1 299 000 FCFA</p>
                  </div>
                  <div class="space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                      <span>Progression</span>
                      <span class="font-semibold">75%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                      <div class="bg-gradient-to-r from-[#0099cc] to-[#0088bb] h-3 rounded-full w-3/4 animate-pulse"></div>
                    </div>
                    <div class="text-center text-sm text-gray-500">750/1000 tickets</div>
                  </div>
                  <button class="w-full bg-[#0099cc] text-white font-semibold py-3 rounded-xl hover:bg-[#0088bb] transition-colors">
                    Acheter maintenant
                  </button>
                </div>
              </div>

              <!-- Floating Elements -->
              <div class="absolute inline-flex items-center -top-8 -left-8 bg-[#0099cc]/10 text-[#0099cc] px-4 py-2 rounded-full text-sm font-medium z-20 animate-float">
                <SparklesIcon class="h-4 w-4" /> Nouvelle tombola
              </div>
              <div class="absolute -bottom-4 -right-8 bg-yellow-100 text-yellow-600 px-4 py-2 rounded-full text-sm font-medium z-20 animate-float-delayed">
                🔥 Populaire
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-black mb-4">
            Koumbaya en chiffres
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Rejoignez des milliers de participants qui ont déjà fait confiance à notre plateforme
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="w-20 h-20 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <UsersIcon class="h-10 w-10 text-[#0099cc]" />
            </div>
            <div class="text-4xl font-bold text-black mb-2">12 000+</div>
            <div class="text-gray-600">Participants actifs</div>
          </div>

          <div class="text-center">
            <div class="w-20 h-20 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <GiftIcon class="h-10 w-10 text-[#0099cc]" />
            </div>
            <div class="text-4xl font-bold text-black mb-2">250+</div>
            <div class="text-gray-600">Produits gagnés</div>
          </div>

          <div class="text-center">
            <div class="w-20 h-20 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <CurrencyDollarIcon class="h-10 w-10 text-[#0099cc]" />
            </div>
            <div class="text-4xl font-bold text-black mb-2">500M+</div>
            <div class="text-gray-600">FCFA distribués</div>
          </div>

          <div class="text-center">
            <div class="w-20 h-20 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <StarIcon class="h-10 w-10 text-[#0099cc]" />
            </div>
            <div class="text-4xl font-bold text-black mb-2">4.9/5</div>
            <div class="text-gray-600">Note utilisateurs</div>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-black mb-4">
            Simple comme bonjour
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            3 étapes suffisent pour obtenir vos produits à prix cassés
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
          <div class="text-center group">
            <div class="relative mx-auto w-24 h-24 mb-6">
              <div class="absolute inset-0 bg-[#0099cc]/10 rounded-full group-hover:bg-[#0099cc]/20 transition-colors"></div>
              <div class="absolute inset-3 bg-[#0099cc] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl font-bold text-white">1</span>
              </div>
            </div>
            <h3 class="text-xl font-bold text-black mb-4">Choisissez votre produit</h3>
            <p class="text-gray-600 leading-relaxed">
              Parcourez notre catalogue de produits authentiques et sélectionnez celui que vous souhaitez acquérir.
            </p>
          </div>

          <div class="text-center group">
            <div class="relative mx-auto w-24 h-24 mb-6">
              <div class="absolute inset-0 bg-[#0099cc]/10 rounded-full group-hover:bg-[#0099cc]/20 transition-colors"></div>
              <div class="absolute inset-3 bg-[#0099cc] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl font-bold text-white">2</span>
              </div>
            </div>
            <h3 class="text-xl font-bold text-black mb-4">Achetez vos tickets</h3>
            <p class="text-gray-600 leading-relaxed">
              Participez à la tombola avec un petit budget. Plus vous prenez de tickets, meilleures sont vos chances d'obtenir le produit.
            </p>
          </div>

          <div class="text-center group">
            <div class="relative mx-auto w-24 h-24 mb-6">
              <div class="absolute inset-0 bg-[#0099cc]/10 rounded-full group-hover:bg-[#0099cc]/20 transition-colors"></div>
              <div class="absolute inset-3 bg-[#0099cc] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <span class="text-2xl font-bold text-white">3</span>
              </div>
            </div>
            <h3 class="text-xl font-bold text-black mb-4">Obtenez votre produit</h3>
            <p class="text-gray-600 leading-relaxed">
              Le tirage automatique désigne l'acheteur. Nous livrons gratuitement partout au Gabon !
            </p>
          </div>
        </div>

        <div class="text-center mt-12">
          <router-link
            to="/how-it-works"
            class="inline-flex items-center gap-2 bg-black hover:bg-gray-800 text-white font-semibold px-8 py-4 rounded-2xl transition-all duration-200"
          >
            <span>En savoir plus</span>
            <ArrowRightIcon class="h-5 w-5" />
          </router-link>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
          <div>
            <h2 class="text-3xl md:text-4xl font-bold text-black mb-4">
              Produits en vedette
            </h2>
            <p class="text-xl text-gray-600">
              Découvrez les lots les plus convoités du moment
            </p>
          </div>
          <router-link
            to="/products"
            class="hidden md:inline-flex items-center gap-2 text-[#0099cc] hover:text-[#0088bb] font-semibold group"
          >
            <span>Voir tout</span>
            <ArrowRightIcon class="h-5 w-5 group-hover:translate-x-1 transition-transform" />
          </router-link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="product in featuredProducts"
            :key="product.id"
            @click="viewProduct(product)"
            class="bg-white rounded-3xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer group border border-gray-100"
          >
            <div class="relative mb-6">
              <img
                :src="product.image || '/images/products/placeholder.jpg'"
                :alt="product.name"
                class="w-full h-48 object-cover rounded-2xl bg-gray-100 group-hover:scale-105 transition-transform duration-300"
              />
              <div class="absolute -top-2 -right-2">
                <span class="bg-[#0099cc] text-white px-4 py-2 rounded-full font-semibold shadow-lg">
                  {{ formatPrice(product.ticketPrice) }}
                </span>
              </div>
              <div v-if="product.isNew" class="absolute top-4 left-4">
                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                  Nouveau
                </span>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <h3 class="text-xl font-bold text-black group-hover:text-[#0099cc] transition-colors">
                  {{ product.name }}
                </h3>
                <p class="text-gray-600">Valeur: {{ formatPrice(product.value) }}</p>
              </div>

              <div class="space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Progression</span>
                  <span class="font-semibold">{{ Math.round((product.soldTickets / 1000) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-gradient-to-r from-[#0099cc] to-cyan-500 h-3 rounded-full transition-all duration-500"
                    :style="{ width: Math.round((product.soldTickets / 1000) * 100) + '%' }"
                  ></div>
                </div>
                <div class="text-center text-sm text-gray-500">
                  {{ product.soldTickets }}/1000 tickets
                </div>
              </div>

              <button class="w-full bg-[#0099cc] hover:bg-[#0088bb] text-white font-semibold py-3 rounded-xl transition-colors group-hover:scale-105 group-hover:shadow-lg">
                Acheter maintenant
              </button>
            </div>
          </div>
        </div>

        <div class="text-center mt-12 md:hidden">
          <router-link
            to="/products"
            class="inline-flex items-center gap-2 bg-[#0099cc] hover:bg-[#0088bb] text-white font-semibold px-8 py-4 rounded-2xl transition-all"
          >
            <span>Voir tous les produits</span>
            <ArrowRightIcon class="h-5 w-5" />
          </router-link>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-black mb-4">
            Ils ont acheté malin avec Koumbaya
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Découvrez les témoignages de nos clients satisfaits
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div
            v-for="testimonial in testimonials"
            :key="testimonial.id"
            class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100"
          >
            <div class="flex items-center mb-6">
              <img
                :src="testimonial.avatar"
                :alt="testimonial.name"
                class="w-12 h-12 rounded-full bg-gray-200"
              />
              <div class="ml-4">
                <div class="font-semibold text-gray-900">{{ testimonial.name }}</div>
                <div class="text-sm text-gray-600">{{ testimonial.location }}</div>
              </div>
            </div>
            <div class="flex items-center mb-4">
              <div class="flex text-yellow-400">
                <StarIcon v-for="n in 5" :key="n" class="h-5 w-5 fill-current" />
              </div>
            </div>
            <blockquote class="text-gray-700 italic leading-relaxed">
              "{{ testimonial.review }}"
            </blockquote>
            <div class="mt-4 text-sm text-[#0099cc] font-medium">
              A obtenu: {{ testimonial.won }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-[#0099cc] to-cyan-600">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
          Votre prochain lot vous attend
        </h2>
        <p class="text-xl text-blue-100 mb-8 leading-relaxed">
          Rejoignez la communauté Koumbaya et tentez votre chance dès aujourd'hui.
          L'inscription est gratuite et ne prend que quelques minutes.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/register"
            class="inline-flex items-center justify-center gap-2 bg-white text-[#0099cc] hover:text-[#0088bb] font-bold px-10 py-5 rounded-2xl transition-all duration-200 hover:scale-105 shadow-xl"
          >
            <UserPlusIcon class="h-6 w-6" />
            <span>S'inscrire gratuitement</span>
          </router-link>
          <router-link
            to="/products"
            class="inline-flex items-center justify-center gap-2 border-2 border-white text-white hover:bg-white hover:text-[#0099cc] font-bold px-10 py-5 rounded-2xl transition-all duration-200"
          >
            <EyeIcon class="h-6 w-6" />
            <span>Voir les produits</span>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import placeholderImg from '@/assets/placeholder.jpg'
import avatarPlaceholder from '@/assets/avatar-placeholder.jpg'
import {
  SparklesIcon,
  ShoppingBagIcon,
  QuestionMarkCircleIcon,
  ShieldCheckIcon,
  CheckBadgeIcon,
  TruckIcon,
  UsersIcon,
  GiftIcon,
  CurrencyDollarIcon,
  StarIcon,
  ArrowRightIcon,
  UserPlusIcon,
  EyeIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()

const featuredProducts = ref([
  {
    id: 1,
    name: 'iPhone 15 Pro Max',
    value: 1299000,
    ticketPrice: 1500,
    image: placeholderImg,
    soldTickets: 750,
    isNew: true
  },
  {
    id: 2,
    name: 'MacBook Pro M3',
    value: 2500000,
    ticketPrice: 2500,
    image: placeholderImg,
    soldTickets: 420,
    isNew: true
  },
  {
    id: 3,
    name: 'Tesla Model Y',
    value: 45000000,
    ticketPrice: 50000,
    image: placeholderImg,
    soldTickets: 320,
    isNew: false
  }
])

const testimonials = ref([
  {
    id: 1,
    name: 'Marie Dubois',
    location: 'Libreville',
    avatar: avatarPlaceholder,
    review: 'J\'ai acheté un iPhone 14 Pro avec seulement 5 tickets ! Le processus était transparent et la livraison rapide.',
    won: 'iPhone 14 Pro'
  },
  {
    id: 2,
    name: 'Jean Kamga',
    location: 'Port-Gentil',
    avatar: avatarPlaceholder,
    review: 'Koumbaya a changé ma vie ! J\'ai obtenu une moto à prix réduit et maintenant je participe à toutes les tombolas.',
    won: 'Yamaha MT-03'
  },
  {
    id: 3,
    name: 'Fatou Ndongo',
    location: 'Franceville',
    avatar: avatarPlaceholder,
    review: 'Platform très sérieuse, paiement sécurisé et service client réactif. Je recommande vivement !',
    won: 'MacBook Air M2'
  }
])

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price).replace('XAF', 'FCFA')
}

const viewProduct = (product) => {
  router.push({ name: 'public.product.detail', params: { id: product.id } })
}
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

@keyframes float-delayed {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-15px); }
}

@keyframes float-slow {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float-delayed 8s ease-in-out infinite;
  animation-delay: -2s;
}

.animate-float-slow {
  animation: float-slow 10s ease-in-out infinite;
  animation-delay: -4s;
}
</style>
