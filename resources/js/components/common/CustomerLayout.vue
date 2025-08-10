<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center">
            <router-link to="/customer" class="flex items-center">
              <img class="h-8 w-auto" :src="logoUrl" alt="Koumbaya" />
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
                  ? 'text-white bg-[#0099cc]'
                  : 'text-gray-600 hover:text-[#0099cc] hover:bg-blue-50'
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
                <div class="w-8 h-8 bg-[#0099cc] rounded-full flex items-center justify-center">
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
                ? 'text-[#0099cc] bg-blue-50'
                : 'text-gray-600 hover:text-[#0099cc] hover:bg-blue-50'
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
    <KoumbayaFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import KoumbayaFooter from './KoumbayaFooter.vue'
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
