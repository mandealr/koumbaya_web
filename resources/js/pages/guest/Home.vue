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
                    :src="latestLotteryProduct?.image || placeholderImg"
                    :alt="latestLotteryProduct?.name || 'Produit tombola'"
                    class="w-full h-48 object-cover rounded-2xl bg-gray-100"
                  />
                  <div class="absolute -top-2 -right-2 bg-[#0099cc] text-white px-3 py-1 rounded-full text-sm font-bold">
                    {{ formatPrice(latestLotteryProduct?.ticketPrice || 1500) }}
                  </div>
                </div>
                <div class="space-y-4">
                  <div>
                    <h3 class="text-xl font-bold text-black">{{ latestLotteryProduct?.name || 'iPhone 15 Pro Max' }}</h3>
                    <p class="text-gray-600">Valeur: {{ formatPrice(latestLotteryProduct?.value || 1299000) }}</p>
                  </div>
                  <div class="space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                      <span>Progression</span>
                      <span class="font-semibold">{{ latestLotteryProduct?.progress || 75 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                      <div 
                        class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full animate-pulse transition-all duration-500"
                        :style="{ width: (latestLotteryProduct?.progress || 75) + '%' }"
                      ></div>
                    </div>
                    <div class="text-center text-sm text-gray-500">
                      {{ latestLotteryProduct?.soldTickets || 750 }}/{{ latestLotteryProduct?.totalTickets || 1000 }} tickets
                    </div>
                  </div>
                  <button class="w-full bg-purple-600 text-white font-semibold py-3 rounded-xl hover:bg-purple-700 transition-colors flex items-center justify-center gap-2 whitespace-nowrap">
                    <TicketIcon class="h-5 w-5 flex-shrink-0" />
                    Participer maintenant
                  </button>
                </div>
              </div>

              <!-- Floating Elements -->
              <div class="absolute inline-flex items-center -top-8 -left-8 bg-[#0099cc]/10 text-[#0099cc] px-4 py-2 rounded-full text-sm font-medium z-20 animate-float">
                <SparklesIcon class="h-4 w-4" /> Nouvelle tombola
              </div>
              <div 
                v-if="latestLotteryProduct?.isEndingSoon" 
                class="absolute -bottom-4 -right-8 bg-red-100 text-red-600 px-4 py-2 rounded-full text-sm font-medium z-20 animate-float-delayed flex items-center gap-1"
              >
                <ClockIcon class="h-4 w-4" />
                Se termine bientôt !
              </div>
              <div 
                v-else
                class="absolute -bottom-4 -right-8 bg-yellow-100 text-yellow-600 px-4 py-2 rounded-full text-sm font-medium z-20 animate-float-delayed flex items-center gap-1"
              >
                <FireIcon class="h-4 w-4" />
                Populaire
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
              Découvrez les derniers produits - tombolas et achat direct
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
            class="bg-white rounded-3xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer group border border-gray-100 relative overflow-hidden"
          >
            <!-- Badge mode de vente -->
            <div class="absolute top-4 left-4 z-10">
              <span 
                v-if="product.sale_mode === 'lottery'" 
                class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg flex items-center gap-1"
              >
                <TicketIcon class="h-4 w-4" />
                Tombola
              </span>
              <span 
                v-else 
                class="bg-gradient-to-r from-[#0099cc] to-cyan-500 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg flex items-center gap-1"
              >
                <ShoppingCartIcon class="h-4 w-4" />
                Achat direct
              </span>
            </div>
            
            <!-- Badge statut spécial -->
            <div class="absolute top-4 right-4 z-10">
              <span 
                v-if="product.lottery_ends_soon && product.sale_mode === 'lottery'" 
                class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg animate-pulse flex items-center gap-1"
              >
                <ClockIcon class="h-4 w-4" />
                Fin proche !
              </span>
              <span 
                v-else-if="product.isNew" 
                class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg flex items-center gap-1"
              >
                <SparklesIcon class="h-4 w-4" />
                Nouveau
              </span>
            </div>

            <div class="relative mb-6 mt-4">
              <img
                :src="product.image_url || product.main_image || product.image || '/images/products/placeholder.jpg'"
                :alt="product.name"
                class="w-full h-48 object-cover rounded-2xl bg-gray-100 group-hover:scale-105 transition-transform duration-300"
              />
              <div class="absolute -bottom-3 -right-3">
                <div 
                  v-if="product.sale_mode === 'lottery'" 
                  class="bg-purple-600 text-white px-4 py-2 rounded-full font-semibold shadow-lg"
                >
                  {{ formatPrice(product.ticketPrice || product.ticket_price) }}/ticket
                </div>
                <div 
                  v-else 
                  class="bg-[#0099cc] text-white px-4 py-2 rounded-full font-semibold shadow-lg"
                >
                  {{ formatPrice(product.price || product.value) }}
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <h3 
                  class="text-xl font-bold text-black group-hover:transition-colors"
                  :class="product.sale_mode === 'lottery' ? 'group-hover:text-purple-600' : 'group-hover:text-[#0099cc]'"
                >
                  {{ product.name }}
                </h3>
                <p v-if="product.sale_mode === 'lottery'" class="text-gray-600">
                  Valeur totale: {{ formatPrice(product.value || product.price) }}
                </p>
                <p v-else class="text-gray-600">
                  {{ product.category?.name || 'Catégorie' }}
                </p>
              </div>

              <!-- Section spécifique tombola -->
              <div v-if="product.sale_mode === 'lottery'" class="space-y-2">
                <div class="flex justify-between text-sm text-gray-600">
                  <span>Progression</span>
                  <span class="font-semibold">{{ Math.round(((product.soldTickets || 0) / (product.totalTickets || 1000)) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div
                    class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-500"
                    :style="{ width: Math.round(((product.soldTickets || 0) / (product.totalTickets || 1000)) * 100) + '%' }"
                  ></div>
                </div>
                <div class="text-center text-sm text-gray-500">
                  {{ product.soldTickets || 0 }}/{{ product.totalTickets || 1000 }} tickets vendus
                </div>
              </div>

              <!-- Section spécifique achat direct -->
              <div v-else class="flex items-center justify-between py-2">
                <span class="text-sm text-gray-500">Statut</span>
                <span class="text-sm font-medium text-green-600 flex items-center gap-1">
                  <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                  Disponible
                </span>
              </div>

              <button 
                class="w-full font-semibold py-3 rounded-xl transition-all duration-200 group-hover:scale-105 group-hover:shadow-lg flex items-center justify-center gap-2 whitespace-nowrap"
                :class="product.sale_mode === 'lottery' 
                  ? 'bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white' 
                  : 'bg-gradient-to-r from-[#0099cc] to-cyan-500 hover:from-[#0088bb] hover:to-cyan-600 text-white'"
              >
                <TicketIcon v-if="product.sale_mode === 'lottery'" class="h-5 w-5 flex-shrink-0" />
                <CreditCardIcon v-else class="h-5 w-5 flex-shrink-0" />
                {{ product.sale_mode === 'lottery' ? 'Participer maintenant' : 'Acheter maintenant' }}
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
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
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
  EyeIcon,
  ClockIcon,
  FireIcon,
  TicketIcon,
  ShoppingCartIcon,
  CreditCardIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const { get, loading, error } = useApi()

// State for dynamic data
const featuredProducts = ref([])
const latestLotteryProduct = ref(null)
const stats = ref({
  participants: '12 000+',
  productsWon: '250+',
  amountDistributed: '500M+',
  rating: '4.9/5'
})

// API Functions
const loadFeaturedProducts = async () => {
  try {
    console.log('Chargement des derniers produits mixtes...')
    
    // Charger à la fois les produits tombola et achat direct
    const [lotteryResponse, directResponse] = await Promise.all([
      get('/products/latest-lottery-only?limit=3'),
      get('/products/latest-direct?limit=3')
    ])
    
    console.log('Réponses API:', { lottery: lotteryResponse, direct: directResponse })
    
    let allProducts = []
    
    // Ajouter les produits tombola
    if (lotteryResponse && lotteryResponse.success && lotteryResponse.data) {
      const lotteryProducts = (lotteryResponse.data.products || lotteryResponse.data).slice(0, 2).map(product => ({
        id: product.id,
        name: product.name || product.title,
        value: product.price || 0,
        price: product.price || 0,
        ticketPrice: product.ticket_price || product.active_lottery?.ticket_price || 1000,
        ticket_price: product.ticket_price || product.active_lottery?.ticket_price || 1000,
        image_url: product.image_url || product.main_image,
        image: product.image_url || product.main_image || placeholderImg,
        soldTickets: product.active_lottery?.sold_tickets || 0,
        totalTickets: product.active_lottery?.total_tickets || 1000,
        isNew: isNewProduct(product.created_at),
        sale_mode: 'lottery',
        has_active_lottery: product.has_active_lottery || false,
        lottery_ends_soon: product.lottery_ends_soon || false,
        category: product.category
      }))
      allProducts.push(...lotteryProducts)
    }
    
    // Ajouter les produits achat direct
    if (directResponse && directResponse.success && directResponse.data) {
      const directProducts = (directResponse.data.products || directResponse.data).slice(0, 2).map(product => ({
        id: product.id,
        name: product.name || product.title,
        value: product.price || 0,
        price: product.price || 0,
        image_url: product.image_url || product.main_image,
        image: product.image_url || product.main_image || placeholderImg,
        isNew: isNewProduct(product.created_at),
        sale_mode: 'direct',
        category: product.category
      }))
      allProducts.push(...directProducts)
    }
    
    // Mélanger les produits et en prendre 6 max
    featuredProducts.value = allProducts
      .sort(() => Math.random() - 0.5) // Mélanger aléatoirement
      .slice(0, 6)
    
    // Si pas de produits, utiliser le fallback
    if (featuredProducts.value.length === 0) {
      setFallbackProducts()
    }
    
  } catch (error) {
    console.error('Erreur lors du chargement des produits mixtes:', error)
    setFallbackProducts()
  }
}

const loadLatestLotteryProduct = async () => {
  try {
    console.log('Chargement du dernier produit tombola...')
    const response = await get('/products/latest-lottery')
    console.log('Réponse API dernier produit tombola:', response)
    
    if (response && response.success && response.data) {
      const product = response.data.product
      const lottery = response.data.lottery
      
      console.log('Données produit:', product)
      console.log('Données lottery:', lottery)
      
      const soldTickets = parseInt(lottery?.sold_tickets || 0)
      const totalTickets = parseInt(lottery?.total_tickets || 1000)
      const calculatedProgress = totalTickets > 0 ? Math.round((soldTickets / totalTickets) * 100) : 0
      
      console.log('Tickets vendus (brut):', lottery?.sold_tickets)
      console.log('Total tickets (brut):', lottery?.total_tickets)
      console.log('Tickets vendus (parseInt):', soldTickets)
      console.log('Total tickets (parseInt):', totalTickets)
      console.log('Progression calculée:', calculatedProgress)
      
      // Vérification supplémentaire
      if (totalTickets < 100) {
        console.warn('⚠️ Total tickets semble faible:', totalTickets, '- vérifier les données API')
      }
      
      latestLotteryProduct.value = {
        id: product.id,
        name: product.name || product.title,
        value: product.price || 0,
        ticketPrice: lottery?.ticket_price || product.ticket_price || 1500,
        image: product.image_url || product.main_image || placeholderImg,
        soldTickets: soldTickets,
        totalTickets: totalTickets,
        progress: calculatedProgress,
        timeRemaining: lottery?.time_remaining,
        isEndingSoon: lottery?.is_ending_soon || false
      }
      
      console.log('Produit tombola final:', latestLotteryProduct.value)
    } else {
      // Si pas de produit trouvé, utiliser un fallback
      latestLotteryProduct.value = {
        id: 'fallback',
        name: 'iPhone 15 Pro Max',
        value: 1299000,
        ticketPrice: 1500,
        image: placeholderImg,
        soldTickets: 750,
        totalTickets: 1000,
        progress: 75
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement du dernier produit tombola:', error)
    // Fallback
    latestLotteryProduct.value = {
      id: 'fallback',
      name: 'iPhone 15 Pro Max',
      value: 1299000,
      ticketPrice: 1500,
      image: placeholderImg,
      soldTickets: 750,
      totalTickets: 1000,
      progress: 75
    }
  }
}

const setFallbackProducts = () => {
  featuredProducts.value = [
    {
      id: 'fallback-1',
      name: 'iPhone 15 Pro Max',
      value: 1299000,
      price: 1299000,
      ticketPrice: 1500,
      ticket_price: 1500,
      image: placeholderImg,
      soldTickets: 750,
      totalTickets: 1000,
      isNew: true,
      sale_mode: 'lottery',
      lottery_ends_soon: false,
      category: { name: 'Smartphones' }
    },
    {
      id: 'fallback-2',
      name: 'MacBook Pro M3',
      value: 2500000,
      price: 2500000,
      image: placeholderImg,
      isNew: true,
      sale_mode: 'direct',
      category: { name: 'Ordinateurs' }
    },
    {
      id: 'fallback-3',
      name: 'Samsung Galaxy S24',
      value: 950000,
      price: 950000,
      ticketPrice: 1200,
      ticket_price: 1200,
      image: placeholderImg,
      soldTickets: 320,
      totalTickets: 800,
      isNew: false,
      sale_mode: 'lottery',
      lottery_ends_soon: true,
      category: { name: 'Smartphones' }
    },
    {
      id: 'fallback-4',
      name: 'AirPods Pro',
      value: 380000,
      price: 380000,
      image: placeholderImg,
      isNew: false,
      sale_mode: 'direct',
      category: { name: 'Audio' }
    }
  ]
}

const isNewProduct = (createdAt) => {
  if (!createdAt) return false
  const now = new Date()
  const created = new Date(createdAt)
  const diffInDays = (now - created) / (1000 * 60 * 60 * 24)
  return diffInDays <= 7 // Nouveau si créé dans les 7 derniers jours
}

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

// Watcher pour surveiller les changements
watch(latestLotteryProduct, (newValue) => {
  if (newValue) {
    console.log('🔄 latestLotteryProduct mis à jour:', newValue)
  }
}, { deep: true })

// Initialize data on component mount
onMounted(async () => {
  console.log('🚀 Montage du composant Home - chargement des données...')
  await Promise.all([
    loadFeaturedProducts(),
    loadLatestLotteryProduct()
  ])
  console.log('✅ Chargement terminé')
})
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
