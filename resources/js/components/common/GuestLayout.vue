<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <router-link to="/" class="flex items-center min-w-0 flex-shrink-0">
            <img 
              v-if="!logoError"
              src="/logo.png" 
              alt="Koumbaya" 
              class="h-8 sm:h-10 w-auto object-contain max-w-none"
              @error="handleImageError"
            >
            <div 
              v-else
              class="flex items-center space-x-1 sm:space-x-2"
            >
              <div class="w-6 h-6 sm:w-8 sm:h-8 bg-[#0099cc] rounded flex items-center justify-center flex-shrink-0">
                <span class="text-white font-bold text-sm sm:text-lg">K</span>
              </div>
              <span class="text-lg sm:text-xl font-bold text-[#0099cc] whitespace-nowrap">Koumbaya</span>
            </div>
          </router-link>

          <!-- Navigation Links -->
          <nav class="hidden md:flex space-x-8">
            <router-link
              to="/"
              class="text-gray-600 hover:text-[#0099cc] px-3 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Accueil
            </router-link>
            <router-link
              to="/products"
              class="text-gray-600 hover:text-[#0099cc] px-3 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Produits
            </router-link>
            <router-link
              to="/how-it-works"
              class="text-gray-600 hover:text-[#0099cc] px-3 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Comment ça marche
            </router-link>
            <router-link
              to="/contact"
              class="text-gray-600 hover:text-[#0099cc] px-3 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Contact
            </router-link>
          </nav>

          <!-- Auth Buttons / User Menu -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- Si utilisateur connecté -->
            <div v-if="authStore.isAuthenticated" class="flex items-center space-x-2 sm:space-x-4">
              <!-- Bouton Mon Espace -->
              <router-link
                :to="userDashboardPath"
                class="bg-[#0099cc] hover:bg-[#0088bb] text-white px-2 sm:px-4 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors flex items-center whitespace-nowrap"
              >
                <UserIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 flex-shrink-0" />
                <span class="hidden sm:inline">Mon Espace</span>
                <span class="sm:hidden">Espace</span>
              </router-link>

              <!-- Menu utilisateur rapide -->
              <div class="relative">
                <button
                  @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50"
                >
                  <div class="w-8 h-8 bg-[#0099cc] rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-white">{{ userInitials }}</span>
                  </div>
                  <ChevronDownIcon class="w-4 h-4 text-gray-500" />
                </button>

                <!-- Dropdown menu -->
                <div
                  v-if="userMenuOpen"
                  class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
                  @click.outside="userMenuOpen = false"
                >
                  <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                    <p class="font-medium">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
                    <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
                  </div>
                  <router-link
                    :to="userDashboardPath"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    @click="userMenuOpen = false"
                  >
                    <UserIcon class="w-4 h-4 inline mr-2" />
                    Mon Espace
                  </router-link>
                  <button
                    @click="handleLogout"
                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  >
                    <ArrowRightOnRectangleIcon class="w-4 h-4 inline mr-2" />
                    Se déconnecter
                  </button>
                </div>
              </div>
            </div>

            <!-- Si utilisateur non connecté -->
            <div v-else class="flex items-center space-x-2 sm:space-x-4">
              <router-link
                to="/login"
                class="text-gray-600 hover:text-[#0099cc] px-2 sm:px-3 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors whitespace-nowrap"
              >
                <span class="hidden sm:inline">Se connecter</span>
                <span class="sm:hidden">Connexion</span>
              </router-link>
              <router-link
                to="/register"
                class="bg-[#0099cc] hover:bg-[#0088bb] text-white px-2 sm:px-4 py-1.5 sm:py-2 rounded-md text-xs sm:text-sm font-medium transition-colors whitespace-nowrap"
              >
                S'inscrire
              </router-link>
            </div>

            <!-- Mobile menu button -->
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="md:hidden p-1 sm:p-2 rounded-md text-gray-400 hover:text-gray-600 flex-shrink-0"
            >
              <Bars3Icon class="w-5 h-5 sm:w-6 sm:h-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-1">
          <router-link
            to="/"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-[#0099cc] hover:bg-[#0099cc]/5"
            @click="mobileMenuOpen = false"
          >
            Accueil
          </router-link>
          <router-link
            to="/products"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-[#0099cc] hover:bg-[#0099cc]/5"
            @click="mobileMenuOpen = false"
          >
            Produits
          </router-link>
          <router-link
            to="/how-it-works"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-[#0099cc] hover:bg-[#0099cc]/5"
            @click="mobileMenuOpen = false"
          >
            Comment ça marche
          </router-link>
          <router-link
            to="/contact"
            class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-[#0099cc] hover:bg-[#0099cc]/5"
            @click="mobileMenuOpen = false"
          >
            Contact
          </router-link>
          <div class="border-t border-gray-200 pt-4 mt-4">
            <!-- Si utilisateur connecté -->
            <div v-if="authStore.isAuthenticated" class="space-y-2">
              <router-link
                :to="userDashboardPath"
                class="block px-3 py-2 rounded-md text-base font-medium bg-[#0099cc] text-white hover:bg-[#0088bb] flex items-center"
                @click="mobileMenuOpen = false"
              >
                <UserIcon class="w-5 h-5 mr-2" />
                Mon Espace
              </router-link>
              <div class="px-3 py-2 text-sm text-gray-600">
                {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}
              </div>
              <button
                @click="handleLogout"
                class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-[#0099cc] hover:bg-[#0099cc]/5"
              >
                <ArrowRightOnRectangleIcon class="w-5 h-5 inline mr-2" />
                Se déconnecter
              </button>
            </div>

            <!-- Si utilisateur non connecté -->
            <div v-else class="space-y-2">
              <router-link
                to="/login"
                class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-[#0099cc] hover:bg-[#0099cc]/5"
                @click="mobileMenuOpen = false"
              >
                Se connecter
              </router-link>
              <router-link
                to="/register"
                class="block px-3 py-2 rounded-md text-base font-medium bg-[#0099cc] text-white hover:bg-[#0088bb]"
                @click="mobileMenuOpen = false"
              >
                S'inscrire
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main>
      <router-view />
    </main>

    <!-- Footer -->
    <KoumbayaFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Bars3Icon,
  UserIcon,
  ArrowRightOnRectangleIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'
import KoumbayaFooter from './KoumbayaFooter.vue'

const router = useRouter()
const authStore = useAuthStore()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)
const logoError = ref(false)

// Computed
const userInitials = computed(() => {
  const user = authStore.user
  if (!user) return 'U'
  return (user.first_name?.[0] || '') + (user.last_name?.[0] || '')
})

const userDashboardPath = computed(() => {
  const redirectTo = authStore.getDefaultRedirect()

  // Convertir les noms de route en chemins
  const routePaths = {
    'admin.dashboard': '/admin/dashboard',
    'merchant.dashboard': '/merchant/dashboard',
    'customer.dashboard': '/customer/dashboard'
  }

  return routePaths[redirectTo] || '/customer/dashboard'
})

// Methods
const handleLogout = async () => {
  userMenuOpen.value = false
  await authStore.logout()
  router.push({ name: 'login' })
}

const handleImageError = () => {
  logoError.value = true
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    userMenuOpen.value = false
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
