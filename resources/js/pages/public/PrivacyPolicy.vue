<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-[#0099cc] to-[#0088bb] text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
          <h1 class="text-4xl font-bold mb-4">Politique de confidentialité</h1>
          <p class="text-xl opacity-90">
            Comment nous protégeons et utilisons vos données personnelles
          </p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        
        <!-- Introduction -->
        <div class="mb-8">
          <p class="text-lg text-gray-700 leading-relaxed">
            Le contenu de cette page sera fourni ultérieurement.
          </p>
        </div>

        <!-- Section Placeholder -->
        <div class="space-y-8">
          <div class="border-l-4 border-[#0099cc] pl-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Collecte de données</h2>
            <p class="text-gray-600">
              [Contenu à venir]
            </p>
          </div>

          <div class="border-l-4 border-[#0099cc] pl-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Utilisation des données</h2>
            <p class="text-gray-600">
              [Contenu à venir]
            </p>
          </div>

          <div class="border-l-4 border-[#0099cc] pl-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Protection des données</h2>
            <p class="text-gray-600">
              [Contenu à venir]
            </p>
          </div>

          <div class="border-l-4 border-[#0099cc] pl-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Vos droits</h2>
            <p class="text-gray-600">
              [Contenu à venir]
            </p>
          </div>

          <div class="border-l-4 border-[#0099cc] pl-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact</h2>
            <p class="text-gray-600">
              [Contenu à venir]
            </p>
          </div>
        </div>

      </div>

      <!-- Contact -->
      <div class="mt-12 bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Questions sur la confidentialité ?</h2>
        <p class="text-gray-600 mb-6">
          Contactez notre délégué à la protection des données
        </p>
        <router-link
          to="/contact"
          class="inline-flex items-center justify-center px-8 py-3 bg-[#0099cc] text-white font-semibold rounded-lg hover:bg-[#0088bb] transition-colors"
        >
          Nous Contacter
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// Page statique - pas de logique particulière nécessaire
</script>