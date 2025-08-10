<template>
  <footer class="bg-white border-t border-gray-200 mt-12">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

        <!-- Logo et description -->
        <div class="col-span-1 md:col-span-2 lg:col-span-1">
          <div class="flex items-center">
            <img :src="logoUrl" alt="Koumbaya" class="h-15 w-auto">
          </div>
          <p class="mt-4 text-gray-600 text-sm leading-relaxed">
            La marketplace innovante qui vous permet d'acquérir des produits premium à prix imbattables grâce aux tombolas équitables.
          </p>
          <div class="mt-6 flex space-x-4">
            <a href="#" class="text-gray-400 hover:text-[#0099cc] transition-colors">
              <span class="sr-only">Facebook</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-[#0099cc] transition-colors">
              <span class="sr-only">Instagram</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.618 5.367 11.986 11.988 11.986s11.987-5.368 11.987-11.986C24.004 5.367 18.635.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.68 13.729 3.68 12.417s.518-2.478 1.446-3.274c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.796 1.446 1.962 1.446 3.274s-.518 2.478-1.446 3.274c-.875.807-2.026 1.297-3.323 1.297zm7.718 0c-1.297 0-2.448-.49-3.323-1.297-.928-.796-1.446-1.962-1.446-3.274s.518-2.478 1.446-3.274c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.796 1.446 1.962 1.446 3.274s-.518 2.478-1.446 3.274c-.875.807-2.026 1.297-3.323 1.297z" clip-rule="evenodd"/>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-[#0099cc] transition-colors">
              <span class="sr-only">Twitter</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-[#0099cc] transition-colors">
              <span class="sr-only">LinkedIn</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Informations sur l'entreprise -->
        <div>
          <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
            Informations sur l'entreprise
          </h3>
          <ul class="space-y-3">
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">À propos de Koumbaya</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Affiliés & Influenceurs</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Gagnez des Gombos</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Nous contacter</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Carrières</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Media & Presse</a></li>
          </ul>
        </div>

        <!-- Service client & Participation -->
        <div>
          <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
            Service client
          </h3>
          <ul class="space-y-3">
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Participation aux tombolas</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Politique de propriété intellectuelle</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Informations sur la réception des commandes</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Signaler une activité suspecte</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Centre d'assistance</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Centre de sécurité</a></li>
          </ul>
        </div>

        <!-- Responsabilité & Autres -->
        <div>
          <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
            Centre d'Assistance
          </h3>
          <ul class="space-y-3">
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Centre de Sécurité</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Avoir la paix sur Koumbaya</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors">Plan du site</a></li>
            <li><a href="#" class="text-sm text-gray-600 hover:text-[#0099cc] transition-colors font-medium">Vendre sur Koumbaya</a></li>
          </ul>
        </div>

      </div>

      <!-- Section bottom avec copyright et liens légaux -->
      <div class="mt-12 pt-8 border-t border-gray-200">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="flex flex-wrap items-center space-x-6 mb-4 md:mb-0">
            <a href="#" class="text-sm text-gray-500 hover:text-[#0099cc] transition-colors">Conditions d'utilisation</a>
            <a href="#" class="text-sm text-gray-500 hover:text-[#0099cc] transition-colors">Politique de confidentialité</a>
            <a href="#" class="text-sm text-gray-500 hover:text-[#0099cc] transition-colors">Mentions légales</a>
            <a href="#" class="text-sm text-gray-500 hover:text-[#0099cc] transition-colors">Cookies</a>
          </div>
          <div class="text-sm text-gray-500">
            &copy; 2025 Koumbaya. Tous droits réservés.
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import logoUrl from '@/assets/logo.png'
// Ce composant est statique, pas de logique JavaScript nécessaire
</script>

<style scoped>
/* Styles pour améliorer l'accessibilité et les interactions */
a:focus {
  outline: 2px solid #0099cc;
  outline-offset: 2px;
  border-radius: 4px;
}

h3 {
  font-weight: 600;
  letter-spacing: 0.05em;
}

ul li {
  transition: all 0.2s ease-in-out;
}

ul li:hover {
  transform: translateX(2px);
}
</style>
