<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center">
            <router-link to="/customer" class="flex items-center">
              <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">K</span>
              </div>
              <span class="ml-2 text-xl font-bold text-gray-900">Koumbaya</span>
            </router-link>
          </div>

          <!-- Navigation Links -->
          <nav class="hidden md:flex space-x-8">
            <router-link
              v-for="item in navigationItems"
              :key="item.name"
              :to="item.to"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                $route.name === item.name
                  ? 'text-green-600 bg-green-50'
                  : 'text-gray-600 hover:text-green-600 hover:bg-green-50'
              ]"
            >
              {{ item.label }}
            </router-link>
          </nav>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button class="p-2 text-gray-400 hover:text-gray-600 relative">
              <BellIcon class="w-6 h-6" />
              <span class="absolute top-0 right-0 block h-2 w-2 bg-red-400 rounded-full"></span>
            </button>

            <!-- User Dropdown -->
            <div class="relative">
              <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50"
              >
                <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                  <span class="text-sm font-medium text-white">{{ userInitials }}</span>
                </div>
                <div class="hidden md:block text-left">
                  <p class="text-sm font-medium text-gray-700">{{ authStore.user?.first_name }}</p>
                  <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
                </div>
                <ChevronDownIcon class="w-4 h-4 text-gray-500" />
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
                @click.outside="userMenuOpen = false"
              >
                <router-link
                  to="/customer/profile"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="userMenuOpen = false"
                >
                  <UserIcon class="w-4 h-4 inline mr-2" />
                  Mon Profil
                </router-link>
                <router-link
                  to="/customer/tickets"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="userMenuOpen = false"
                >
                  <TicketIcon class="w-4 h-4 inline mr-2" />
                  Mes Billets
                </router-link>
                <div class="border-t border-gray-100 my-1"></div>
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  <ArrowRightOnRectangleIcon class="w-4 h-4 inline mr-2" />
                  Se déconnecter
                </button>
              </div>
            </div>

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
            v-for="item in navigationItems"
            :key="item.name"
            :to="item.to"
            :class="[
              'block px-3 py-2 rounded-md text-base font-medium',
              $route.name === item.name
                ? 'text-green-600 bg-green-50'
                : 'text-gray-600 hover:text-green-600 hover:bg-green-50'
            ]"
            @click="mobileMenuOpen = false"
          >
            {{ item.label }}
          </router-link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div class="col-span-1">
            <div class="flex items-center">
              <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">K</span>
              </div>
              <span class="ml-2 text-xl font-bold text-gray-900">Koumbaya</span>
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
            </ul>
          </div>
          
          <div>
            <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Support</h3>
            <ul class="mt-4 space-y-4">
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Centre d'aide</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">Contact</a></li>
              <li><a href="#" class="text-sm text-gray-600 hover:text-green-600">CGU</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">Suivez-nous</h3>
            <div class="mt-4 flex space-x-6">
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
            </div>
          </div>
        </div>
        
        <div class="mt-8 border-t border-gray-200 pt-8">
          <p class="text-base text-gray-400 xl:text-center">
            &copy; 2024 Koumbaya. Tous droits réservés.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import {
  BellIcon,
  UserIcon,
  TicketIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const router = useRouter()

const userMenuOpen = ref(false)
const mobileMenuOpen = ref(false)

const navigationItems = [
  { name: 'customer.dashboard', to: { name: 'customer.dashboard' }, label: 'Accueil' },
  { name: 'customer.products', to: { name: 'customer.products' }, label: 'Produits' },
  { name: 'customer.tickets', to: { name: 'customer.tickets' }, label: 'Mes Billets' }
]

const userInitials = computed(() => {
  const user = authStore.user
  if (!user) return 'U'
  return (user.first_name?.[0] || '') + (user.last_name?.[0] || '')
})

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>