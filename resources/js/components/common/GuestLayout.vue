<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <router-link to="/" class="flex items-center">
            <img src="/build/assets/logo.png" alt="Koumbaya" class="h-8 w-auto" onerror="this.src='/logo.png'">
          </router-link>

          <!-- Navigation Links -->
          <nav class="hidden md:flex space-x-8">
            <router-link
              to="/"
              class="text-gray-600 hover:text-koumbaya-primary px-3 py-2 rounded-md text-sm font-medium"
            >
              Accueil
            </router-link>
            <a href="#products" class="text-gray-600 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">
              Produits
            </a>
            <a href="#how-it-works" class="text-gray-600 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">
              Comment ça marche
            </a>
            <a href="#contact" class="text-gray-600 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium">
              Contact
            </a>
          </nav>

          <!-- Auth Buttons -->
          <div class="flex items-center space-x-4">
            <router-link
              to="/login"
              class="text-gray-600 hover:text-koumbaya-primary px-3 py-2 rounded-md text-sm font-medium"
            >
              Se connecter
            </router-link>
            <router-link
              to="/register"
              class="bg-koumbaya-primary hover:bg-koumbaya-primary-dark text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
            >
              S'inscrire
            </router-link>

            <!-- Mobile menu button -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-600"
            >
              <Bars3Icon class="w-6 h-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-1">
          <router-link
            to="/"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-green-600 hover:bg-green-50"
            @click="mobileMenuOpen = false"
          >
            Accueil
          </router-link>
          <a
            href="#products"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-green-600 hover:bg-green-50"
            @click="mobileMenuOpen = false"
          >
            Produits
          </a>
          <a
            href="#how-it-works"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-green-600 hover:bg-green-50"
            @click="mobileMenuOpen = false"
          >
            Comment ça marche
          </a>
          <a
            href="#contact"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-green-600 hover:bg-green-50"
            @click="mobileMenuOpen = false"
          >
            Contact
          </a>
          <div class="border-t border-gray-200 pt-4 mt-4">
            <router-link
              to="/login"
              class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-green-600 hover:bg-green-50"
              @click="mobileMenuOpen = false"
            >
              Se connecter
            </router-link>
            <router-link
              to="/register"
              class="block px-3 py-2 mt-2 rounded-md text-base font-medium bg-koumbaya-primary text-white hover:bg-koumbaya-primary-dark"
              @click="mobileMenuOpen = false"
            >
              S'inscrire
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main>
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div class="col-span-1">
            <div class="flex items-center">
              <img src="/build/assets/logo.png" alt="Koumbaya" class="h-8 w-auto" onerror="this.src='/logo.png'">
            </div>
            <p class="mt-4 text-gray-600 text-sm">
              La plateforme de loteries premium qui transforme vos rêves en réalité.
            </p>
          </div>
          
          <div>
            <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Produits</h3>
            <ul class="mt-4 space-y-4">
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Électronique</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Mode</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Automobiles</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Immobilier</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Entreprise</h3>
            <ul class="mt-4 space-y-4">
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">À propos</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Carrières</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Partenaires</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Blog</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Support</h3>
            <ul class="mt-4 space-y-4">
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Centre d'aide</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Contact</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">CGU</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Confidentialité</a></li>
            </ul>
          </div>
        </div>
        
        <div class="mt-8 border-t border-gray-200 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p class="text-base text-gray-400">
            &copy; 2024 Koumbaya. Tous droits réservés.
          </p>
          <div class="mt-4 md:mt-0 flex space-x-6">
            <a href="#" class="text-gray-400 hover:text-green-600">
              <span class="sr-only">Facebook</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-green-600">
              <span class="sr-only">Instagram</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.618 5.367 11.986 11.988 11.986s11.987-5.368 11.987-11.986C24.004 5.367 18.635.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.68 13.729 3.68 12.417s.518-2.478 1.446-3.274c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.796 1.446 1.962 1.446 3.274s-.518 2.478-1.446 3.274c-.875.807-2.026 1.297-3.323 1.297zm7.718 0c-1.297 0-2.448-.49-3.323-1.297-.928-.796-1.446-1.962-1.446-3.274s.518-2.478 1.446-3.274c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.796 1.446 1.962 1.446 3.274s-.518 2.478-1.446 3.274c-.875.807-2.026 1.297-3.323 1.297z" clip-rule="evenodd"/>
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-green-600">
              <span class="sr-only">Twitter</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Bars3Icon } from '@heroicons/vue/24/outline'

const mobileMenuOpen = ref(false)
</script>