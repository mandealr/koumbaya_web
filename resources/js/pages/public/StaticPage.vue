<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-[#0099cc] via-[#0088bb] to-[#0077aa] text-white py-20">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
          {{ pageTitle }}
        </h1>
        <p class="text-xl md:text-2xl text-blue-100 leading-relaxed" v-if="pageDescription">
          {{ pageDescription }}
        </p>
      </div>
    </section>

    <!-- Main Content Section -->
    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
          <div class="p-8 md:p-12">
            <div class="prose prose-lg max-w-none" v-html="pageContent"></div>
          </div>
        </div>

        <!-- Call to Action si nécessaire -->
        <div v-if="showCTA" class="mt-12 text-center">
          <router-link
            :to="ctaLink"
            class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-xl text-white bg-[#0099cc] hover:bg-[#0088bb] transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl"
          >
            {{ ctaText }}
          </router-link>
        </div>
      </div>
    </section>

    <!-- Related Links Section si nécessaire -->
    <section v-if="relatedLinks && relatedLinks.length > 0" class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center mb-12">
          Pages connexes
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <router-link
            v-for="link in relatedLinks"
            :key="link.path"
            :to="link.path"
            class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-200 hover:scale-[1.02]"
          >
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ link.title }}</h3>
            <p class="text-gray-600">{{ link.description }}</p>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// Extraire le nom de la page depuis le chemin
const pageName = computed(() => {
  const path = route.path.replace(/^\//, '')
  return path
})

const pageDescription = computed(() => {
  const descriptions = {
    'about': 'Découvrez notre mission et nos valeurs',
    'terms': 'Conditions générales d\'utilisation de la plateforme Koumbaya',
    'privacy': 'Comment nous protégeons vos données personnelles',
    'legal': 'Informations légales et réglementaires',
    'cookies': 'Notre politique d\'utilisation des cookies'
  }
  return descriptions[pageName.value] || ''
})

const pageContent = computed(() => {
  // TODO: Ajouter ici le contenu fourni par l'utilisateur
  const contents = {
    'about': `
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour la page À propos...
        </p>
      </div>
    `,
    'terms': `
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour les conditions d'utilisation...
        </p>
      </div>
    `,
    'privacy': `
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour la politique de confidentialité...
        </p>
      </div>
    `,
    'legal': `
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour les mentions légales...
        </p>
      </div>
    `,
    'cookies': `
      <div class="space-y-8">
        <p class="text-gray-700 text-lg leading-relaxed">
          Contenu à fournir pour la politique de cookies...
        </p>
      </div>
    `
  }

  // Pour toutes les autres pages, afficher un message temporaire
  return contents[pageName.value] || `
    <div class="text-center py-12">
      <p class="text-gray-600 text-lg mb-6">
        Cette page sera bientôt disponible avec le contenu approprié.
      </p>
      <p class="text-gray-500">
        Veuillez fournir le contenu spécifique pour cette page.
      </p>
    </div>
  `
})

const pageTitle = computed(() => {
  const titles = {
    'about': 'À propos de Koumbaya',
    'affiliates': 'Programme d\'Affiliation',
    'earn-gombos': 'Gagnez des Gombos',
    'careers': 'Carrières',
    'media-press': 'Médias & Presse',
    'lottery-participation': 'Participation aux tirages spéciaux',
    'intellectual-property': 'Propriété Intellectuelle',
    'order-delivery': 'Livraison des commandes',
    'report-suspicious': 'Signaler une activité suspecte',
    'support-center': 'Centre d\'assistance',
    'security-center': 'Centre de sécurité',
    'peace-on-koumbaya': 'Avoir la paix sur Koumbaya',
    'sitemap': 'Plan du site',
    'sell-on-koumbaya': 'Vendre sur Koumbaya',
    'terms': 'Conditions d\'utilisation',
    'privacy': 'Politique de confidentialité',
    'legal': 'Mentions légales',
    'cookies': 'Politique de cookies'
  }

  return titles[pageName.value] || 'Page Koumbaya'
})

// Configuration des CTA pour certaines pages
const showCTA = computed(() => {
  return ['sell-on-koumbaya', 'affiliates', 'careers'].includes(pageName.value)
})

const ctaText = computed(() => {
  const ctas = {
    'sell-on-koumbaya': 'Commencer à vendre',
    'affiliates': 'Rejoindre le programme',
    'careers': 'Voir les offres d\'emploi'
  }
  return ctas[pageName.value] || 'En savoir plus'
})

const ctaLink = computed(() => {
  const links = {
    'sell-on-koumbaya': '/register',
    'affiliates': '/register',
    'careers': '/contact'
  }
  return links[pageName.value] || '/contact'
})

// Liens connexes pour certaines pages
const relatedLinks = computed(() => {
  const links = {
    'about': [
      { path: '/how-it-works', title: 'Comment ça marche', description: 'Découvrez le fonctionnement de Koumbaya' },
      { path: '/contact', title: 'Contactez-nous', description: 'Une question ? Nous sommes là pour vous' },
      { path: '/careers', title: 'Carrières', description: 'Rejoignez notre équipe' }
    ],
    'support-center': [
      { path: '/how-it-works', title: 'FAQ', description: 'Questions fréquemment posées' },
      { path: '/contact', title: 'Contact', description: 'Parlez à notre équipe support' },
      { path: '/report-suspicious', title: 'Signaler un problème', description: 'Signalez une activité suspecte' }
    ],
    'legal': [
      { path: '/terms', title: 'CGU', description: 'Conditions générales d\'utilisation' },
      { path: '/privacy', title: 'Confidentialité', description: 'Protection de vos données' },
      { path: '/cookies', title: 'Cookies', description: 'Politique de cookies' }
    ]
  }
  return links[pageName.value] || []
})
</script>

<style scoped>
/* Styles pour la prose avec le même design que Contact */
:deep(.prose) {
  color: #374151;
}

:deep(.prose h2) {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin-top: 3rem;
  margin-bottom: 1.5rem;
}

:deep(.prose h3) {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

:deep(.prose p) {
  font-size: 1.125rem;
  line-height: 1.75;
  margin-bottom: 1.5rem;
}

:deep(.prose ul) {
  list-style-type: disc;
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
}

:deep(.prose ul li) {
  margin-bottom: 0.5rem;
  line-height: 1.75;
}

:deep(.prose ol) {
  list-style-type: decimal;
  padding-left: 1.5rem;
  margin-bottom: 1.5rem;
}

:deep(.prose ol li) {
  margin-bottom: 0.5rem;
  line-height: 1.75;
}

:deep(.prose a) {
  color: #0099cc;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.15s ease-in-out;
}

:deep(.prose a:hover) {
  color: #0088bb;
  text-decoration: underline;
}

:deep(.prose strong) {
  font-weight: 600;
  color: #111827;
}

:deep(.prose blockquote) {
  border-left: 4px solid #0099cc;
  padding-left: 1.5rem;
  margin: 2rem 0;
  font-style: italic;
  color: #4b5563;
}
</style>