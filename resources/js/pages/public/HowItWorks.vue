<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-[#0099cc] via-[#0088bb] to-[#0099cc] text-white py-20">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
          Comment ça marche ?
        </h1>
        <p class="text-xl md:text-2xl text-[#0099cc]/70 leading-relaxed max-w-3xl mx-auto">
          Découvrez comment obtenir des produits incroyables à prix réduit en quelques clics seulement. 
          C'est simple, transparent et malin !
        </p>
      </div>
    </section>

    <!-- Main Steps -->
    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            3 étapes pour acheter malin
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Notre système de tombola est conçu pour être simple et équitable pour tous les participants
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
          <!-- Step 1 -->
          <div class="relative text-center group">
            <div class="relative mx-auto w-32 h-32 mb-8">
              <div class="absolute inset-0 bg-[#0099cc]/10 rounded-full"></div>
              <div class="absolute inset-2 bg-[#0099cc] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <span class="text-4xl font-bold text-white">1</span>
              </div>
              <!-- Arrow -->
              <div class="hidden lg:block absolute -right-16 top-1/2 transform -translate-y-1/2">
                <ArrowRightIcon class="h-8 w-8 text-[#0099cc]/30" />
              </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Choisissez votre produit</h3>
            <p class="text-gray-600 leading-relaxed">
              Parcourez notre catalogue de produits authentiques : smartphones, ordinateurs, 
              voitures, montres de luxe et bien plus encore.
            </p>
            <div class="mt-6 bg-[#0099cc]/5 rounded-xl p-4">
              <div class="flex items-center justify-center gap-2 text-[#0099cc] font-medium">
                <ShoppingBagIcon class="h-5 w-5" />
                <span>Plus de 100 produits disponibles</span>
              </div>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="relative text-center group">
            <div class="relative mx-auto w-32 h-32 mb-8">
              <div class="absolute inset-0 bg-[#0099cc]/10 rounded-full"></div>
              <div class="absolute inset-2 bg-[#0099cc] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <span class="text-4xl font-bold text-white">2</span>
              </div>
              <!-- Arrow -->
              <div class="hidden lg:block absolute -right-16 top-1/2 transform -translate-y-1/2">
                <ArrowRightIcon class="h-8 w-8 text-[#0099cc]/30" />
              </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Achetez vos tickets</h3>
            <p class="text-gray-600 leading-relaxed">
              Chaque produit a un prix de ticket unique. Plus vous achetez de tickets, 
              plus vous augmentez vos chances d'obtenir le produit à prix réduit.
            </p>
            <div class="mt-6 bg-[#0099cc]/5 rounded-xl p-4">
              <div class="flex items-center justify-center gap-2 text-[#0099cc] font-medium">
                <TicketIcon class="h-5 w-5" />
                <span>Tickets à partir de 500 FCFA</span>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="relative text-center group">
            <div class="relative mx-auto w-32 h-32 mb-8">
              <div class="absolute inset-0 bg-yellow-100 rounded-full"></div>
              <div class="absolute inset-2 bg-yellow-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <span class="text-4xl font-bold text-white">3</span>
              </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Obtenez votre produit</h3>
            <p class="text-gray-600 leading-relaxed">
              Le tirage se fait automatiquement quand tous les tickets sont vendus. 
              Si vous obtenez le produit, nous vous livrons gratuitement !
            </p>
            <div class="mt-6 bg-yellow-50 rounded-xl p-4">
              <div class="flex items-center justify-center gap-2 text-yellow-700 font-medium">
                <GiftIcon class="h-5 w-5" />
                <span>Livraison gratuite partout</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How Lottery Works -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Comment fonctionne notre tombola ?
          </h2>
          <p class="text-xl text-gray-600">
            Notre système de tombola est 100% équitable et transparent
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
          <div class="space-y-6">
            <div class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-[#0099cc] rounded-full flex items-center justify-center">
                  <CheckIcon class="h-6 w-6 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Tirage automatique</h3>
                <p class="text-gray-600">
                  Dès que tous les tickets sont vendus, le tirage se lance automatiquement. 
                  Aucune intervention humaine possible.
                </p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-[#0099cc] rounded-full flex items-center justify-center">
                  <EyeIcon class="h-6 w-6 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Transparence totale</h3>
                <p class="text-gray-600">
                  Tous les tirages sont enregistrés et vérifiables. 
                  Vous pouvez consulter l'historique à tout moment.
                </p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-[#0099cc] rounded-full flex items-center justify-center">
                  <ShieldCheckIcon class="h-6 w-6 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sécurité garantie</h3>
                <p class="text-gray-600">
                  Notre algorithme de tirage est certifié et audité régulièrement 
                  pour assurer une équité parfaite.
                </p>
              </div>
            </div>

            <div class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-[#0099cc] rounded-full flex items-center justify-center">
                  <BellIcon class="h-6 w-6 text-white" />
                </div>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Notification immédiate</h3>
                <p class="text-gray-600">
                  Les participants sélectionnés sont notifiés instantanément par email et SMS. 
                  Plus besoin d'attendre !
                </p>
              </div>
            </div>
          </div>

          <div class="relative">
            <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100">
              <div class="text-center mb-6">
                <div class="w-20 h-20 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-4">
                  <CpuChipIcon class="h-10 w-10 text-[#0099cc]" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900">Algorithme RNG</h3>
                <p class="text-gray-600 text-sm mt-2">Générateur de nombres aléatoires certifié</p>
              </div>
              
              <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Étape 1</span>
                    <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                  </div>
                  <div class="text-sm font-medium text-gray-900">Collecte des tickets vendus</div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Étape 2</span>
                    <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                  </div>
                  <div class="text-sm font-medium text-gray-900">Génération nombre aléatoire</div>
                </div>
                
                <div class="bg-[#0099cc]/5 rounded-lg p-4 border border-[#0099cc]/20">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-[#0099cc]">Étape 3</span>
                    <SparklesIcon class="h-4 w-4 text-[#0099cc]" />
                  </div>
                  <div class="text-sm font-medium text-gray-900">Désignation du participant sélectionné</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Payment & Security -->
    <section class="py-20">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Paiement sécurisé et livraison
          </h2>
          <p class="text-xl text-gray-600">
            Vos transactions sont protégées par les meilleurs systèmes de sécurité
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <!-- Payment Methods -->
          <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
            <div class="w-16 h-16 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-6">
              <CreditCardIcon class="h-8 w-8 text-[#0099cc]" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-4">Moyens de paiement</h3>
            <ul class="space-y-3 text-gray-600">
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Mobile Money (MTN, Orange)</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Carte bancaire Visa/Mastercard</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Virement bancaire</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Portefeuille électronique</span>
              </li>
            </ul>
          </div>

          <!-- Security -->
          <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
            <div class="w-16 h-16 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-6">
              <LockClosedIcon class="h-8 w-8 text-[#0099cc]" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-4">Sécurité des données</h3>
            <ul class="space-y-3 text-gray-600">
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Chiffrement SSL 256 bits</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Conformité PCI DSS</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Authentification 2 facteurs</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Protection anti-fraude</span>
              </li>
            </ul>
          </div>

          <!-- Delivery -->
          <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <TruckIcon class="h-8 w-8 text-yellow-500" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-4">Livraison gratuite</h3>
            <ul class="space-y-3 text-gray-600">
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Livraison partout au Gabon</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Suivi en temps réel</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Délai 24-48h en ville</span>
              </li>
              <li class="flex items-center gap-3">
                <CheckIcon class="h-4 w-4 text-[#0099cc]" />
                <span>Assurance colis incluse</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Questions fréquentes
          </h2>
          <p class="text-xl text-gray-600">
            Trouvez rapidement les réponses à vos questions
          </p>
        </div>

        <div class="space-y-6">
          <div
            v-for="(faq, index) in faqs"
            :key="index"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
          >
            <button
              @click="toggleFaq(index)"
              class="w-full px-8 py-6 text-left flex items-center justify-between hover:bg-gray-50 transition-colors"
            >
              <span class="text-lg font-semibold text-gray-900">{{ faq.question }}</span>
              <ChevronDownIcon 
                :class="['h-6 w-6 text-gray-400 transition-transform', openFaqIndex === index ? 'rotate-180' : '']"
              />
            </button>
            <div 
              v-show="openFaqIndex === index"
              class="px-8 pb-6 text-gray-600 leading-relaxed"
            >
              {{ faq.answer }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 bg-gradient-to-r from-[#0099cc] to-[#0088bb]">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
          Prêt à acheter malin ?
        </h2>
        <p class="text-xl text-[#0099cc]/70 mb-8">
          Rejoignez les milliers de participants qui ont déjà acheté malin
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/products"
            class="bg-white text-[#0099cc] px-8 py-4 rounded-xl font-semibold hover:bg-gray-50 transition-colors shadow-lg"
          >
            Voir les produits
          </router-link>
          <router-link
            to="/register"
            class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold hover:bg-white hover:text-[#0099cc] transition-all"
          >
            S'inscrire gratuitement
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import {
  ArrowRightIcon,
  ShoppingBagIcon,
  TicketIcon,
  GiftIcon,
  CheckIcon,
  EyeIcon,
  ShieldCheckIcon,
  BellIcon,
  CpuChipIcon,
  SparklesIcon,
  CreditCardIcon,
  LockClosedIcon,
  TruckIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'

const openFaqIndex = ref(null)

const faqs = ref([
  {
    question: "Comment puis-je être sûr que le tirage est équitable ?",
    answer: "Notre système utilise un générateur de nombres aléatoires (RNG) certifié et audité régulièrement par des organismes indépendants. Tous les tirages sont enregistrés et vérifiables publiquement."
  },
  {
    question: "Que se passe-t-il si je gagne ?",
    answer: "Vous êtes immédiatement notifié par email et SMS. Notre équipe vous contacte dans les 24h pour organiser la livraison gratuite de votre lot. Vous recevez aussi un certificat d'authenticité."
  },
  {
    question: "Puis-je acheter plusieurs tickets pour le même produit ?",
    answer: "Oui, absolument ! Plus vous achetez de tickets, plus vous augmentez vos chances de gagner. Il n'y a pas de limite au nombre de tickets que vous pouvez acheter."
  },
  {
    question: "Les produits sont-ils vraiment authentiques ?",
    answer: "Tous nos produits sont 100% authentiques et neufs. Nous travaillons directement avec les distributeurs officiels et fournissons un certificat d'authenticité avec chaque lot."
  },
  {
    question: "Combien de temps ai-je pour réclamer mon lot ?",
    answer: "Vous avez 30 jours pour réclamer votre lot après le tirage. Passé ce délai, un nouveau tirage est organisé avec les tickets non gagnants."
  },
  {
    question: "Y a-t-il des frais cachés ?",
    answer: "Aucun frais caché ! Le prix affiché est le prix final. La livraison est gratuite partout au Gabon, et il n'y a aucun frais supplémentaire."
  }
])

const toggleFaq = (index) => {
  openFaqIndex.value = openFaqIndex.value === index ? null : index
}
</script>