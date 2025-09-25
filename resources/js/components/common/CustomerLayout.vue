<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo -->
          <div class="flex items-center min-w-0 flex-shrink-0">
            <router-link to="/" class="flex items-center">
              <img
                v-if="!logoError"
                class="h-8 sm:h-10 w-auto object-contain max-w-none"
                src="/logo.png"
                alt="Koumbaya Marketplace"
                @error="handleImageError"
              />
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
                  : 'text-gray-600 hover:text-[#0099cc] hover:bg-[#0099cc]/5'
              ]"
            >
              {{ item.label }}
            </router-link>
          </nav>

          <!-- User Menu -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- Notifications -->
            <NotificationIcon />

            <!-- User Dropdown -->
            <div class="relative">
              <button
                @click="userMenuOpen = !userMenuOpen"
                class="flex items-center space-x-1 sm:space-x-3 p-1 sm:p-2 rounded-lg hover:bg-gray-100"
              >
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden flex-shrink-0 border border-gray-200">
                  <img 
                    v-if="authStore.user?.avatar_url"
                    :src="authStore.user.avatar_url"
                    :alt="`Photo de profil de ${authStore.user.first_name} ${authStore.user.last_name}`"
                    class="w-full h-full object-cover"
                    @error="onAvatarError"
                  />
                  <div v-else class="w-full h-full bg-[#0099cc] flex items-center justify-center">
                    <span class="text-xs sm:text-sm font-medium text-white">{{ userInitials }}</span>
                  </div>
                </div>
                <div class="hidden md:block text-left min-w-0">
                  <p class="text-sm font-medium text-gray-700 truncate">{{ authStore.user?.first_name }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ authStore.user?.email }}</p>
                </div>
                <ChevronDownIcon class="w-3 h-3 sm:w-4 sm:h-4 text-gray-500 flex-shrink-0" />
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
                  Mes Tickets
                </router-link>
                <router-link
                  to="/customer/payments"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="userMenuOpen = false"
                >
                  <CreditCardIcon class="w-4 h-4 inline mr-2" />
                  Mes Paiements
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

        <!-- Bannière de vérification email -->
        <VerificationBanner v-if="shouldShowVerificationBanner" />

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
import { useApi } from '@/composables/api'
import KoumbayaFooter from './KoumbayaFooter.vue'
import VerificationBanner from './VerificationRequiredBanner.vue'
import NotificationIcon from '@/components/common/NotificationIcon.vue'
import {
  UserIcon,
  TicketIcon,
  CreditCardIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const router = useRouter()
const { get } = useApi()

const userMenuOpen = ref(false)
const mobileMenuOpen = ref(false)
const logoError = ref(false)

const navigationItems = [
  { name: 'customer.dashboard', to: { name: 'customer.dashboard' }, label: 'Accueil' },
  { name: 'customer.products', to: { name: 'customer.products' }, label: 'Produits' },
  { name: 'customer.tickets', to: { name: 'customer.tickets' }, label: 'Mes Tickets' },
  { name: 'customer.orders', to: { name: 'customer.orders' }, label: 'Mes Commandes' }
]

const userInitials = computed(() => {
  const user = authStore.user
  if (!user) return 'U'
  return (user.first_name?.[0] || '') + (user.last_name?.[0] || '')
})

// Logique pour la bannière de vérification
const shouldShowVerificationBanner = computed(() => {
  return authStore.isAuthenticated &&
         authStore.user &&
         !authStore.user.verified_at
})

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

const onAvatarError = (event) => {
  console.log('Avatar image failed to load, hiding image element')
  // Cache l'image et force l'affichage des initiales
  event.target.style.display = 'none'
}

const handleImageError = () => {
  logoError.value = true
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    userMenuOpen.value = false
  }
}


onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
  })
})
</script>
