<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo and main nav -->
          <div class="flex">
            <div class="flex-shrink-0 flex items-center min-w-0">
              <router-link to="/merchant/dashboard" class="flex items-center">
                <img 
                  v-if="!logoError"
                  class="h-6 sm:h-8 w-auto object-contain max-w-none" 
                  src="/logo.png" 
                  alt="Koumbaya"
                  @error="handleImageError"
                />
                <div 
                  v-else
                  class="flex items-center space-x-1"
                >
                  <div class="w-5 h-5 sm:w-6 sm:h-6 bg-[#0099cc] rounded flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-xs sm:text-sm">K</span>
                  </div>
                  <span class="text-sm sm:text-lg font-bold text-[#0099cc] whitespace-nowrap">Koumbaya</span>
                </div>
                <span class="ml-1 sm:ml-2 text-xs sm:text-sm text-[#0099cc] font-medium whitespace-nowrap">Marchand</span>
              </router-link>
            </div>

            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <router-link
                v-for="item in navigation"
                :key="item.name"
                :to="item.href"
                :class="[
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                  $route.path.startsWith(item.href)
                    ? 'border-[#0099cc] text-gray-900'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                <component :is="item.icon" class="w-4 h-4 mr-2" />
                {{ item.name }}
              </router-link>
            </div>
          </div>

          <!-- Right side -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- Notifications -->
            <NotificationIcon />

            <!-- User menu -->
            <div class="relative ml-1 sm:ml-3">
              <div>
                <button
                  @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center max-w-xs bg-white rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0099cc]"
                >
                  <span class="sr-only">Open user menu</span>
                  <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-full overflow-hidden flex-shrink-0 border border-gray-200">
                    <img 
                      v-if="user?.avatar_url"
                      :src="user.avatar_url"
                      :alt="`Photo de profil de ${user.first_name} ${user.last_name}`"
                      class="w-full h-full object-cover"
                      @error="onAvatarError"
                    />
                    <div v-else class="w-full h-full bg-blue-100 flex items-center justify-center">
                      <span class="text-xs sm:text-sm font-medium text-blue-600">
                        {{ userInitials }}
                      </span>
                    </div>
                  </div>
                  <span class="ml-2 sm:ml-3 text-gray-700 text-xs sm:text-sm font-medium hidden lg:block min-w-0 truncate">
                    {{ user?.first_name }} {{ user?.last_name }}
                  </span>
                  <ChevronDownIcon class="ml-1 sm:ml-2 h-3 w-3 sm:h-4 sm:w-4 text-gray-500 flex-shrink-0" />
                </button>
              </div>

              <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div v-show="userMenuOpen" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                  <div class="py-1">
                    <router-link
                      v-for="item in userNavigation"
                      :key="item.name"
                      :to="item.href"
                      @click="userMenuOpen = false"
                      class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      <component :is="item.icon" class="w-4 h-4 mr-3" />
                      {{ item.name }}
                    </router-link>
                    <button
                      @click="logout"
                      class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      <ArrowRightOnRectangleIcon class="w-4 h-4 mr-3" />
                      Déconnexion
                    </button>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Mobile menu -->
    <div v-show="mobileMenuOpen" class="sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <router-link
          v-for="item in navigation"
          :key="item.name"
          :to="item.href"
          @click="mobileMenuOpen = false"
          :class="[
            'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
            $route.path.startsWith(item.href)
              ? 'bg-blue-50 border-blue-500 text-blue-700'
              : 'border-transparent text-gray-500 hover:bg-gray-100 hover:border-gray-300 hover:text-gray-700'
          ]"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3 inline" />
          {{ item.name }}
        </router-link>
      </div>
    </div>

    <!-- Page content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <router-view />
    </main>

    <!-- Footer -->
    <KoumbayaFooter />

    <!-- Quick Stats Footer -->
    <div v-if="showQuickStats" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 lg:hidden z-40">
      <div class="flex justify-around text-center">
        <div>
          <div class="text-lg font-semibold text-gray-900">{{ quickStats.products }}</div>
          <div class="text-xs text-gray-600">Produits</div>
        </div>
        <div>
          <div class="text-lg font-semibold text-blue-600">{{ quickStats.lotteries }}</div>
          <div class="text-xs text-gray-600">Tombolas</div>
        </div>
        <div>
          <div class="text-lg font-semibold text-purple-600">{{ quickStats.revenue }}</div>
          <div class="text-xs text-gray-600">Revenus</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/api'
import KoumbayaFooter from './KoumbayaFooter.vue'
import NotificationIcon from '@/components/common/NotificationIcon.vue'
import {
  HomeIcon,
  ShoppingBagIcon,
  ClipboardDocumentListIcon,
  GiftIcon,
  ChartBarIcon,
  CogIcon,
  UserIcon,
  ChevronDownIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()
const { get } = useApi()

// Data
const userMenuOpen = ref(false)
const mobileMenuOpen = ref(false)
const logoError = ref(false)
const quickStats = ref({
  products: '0',
  lotteries: '0',
  revenue: '0 FCFA'
})

// Navigation items
const navigation = [
  { name: 'Dashboard', href: '/merchant/dashboard', icon: HomeIcon },
  { name: 'Produits', href: '/merchant/products', icon: ShoppingBagIcon },
  { name: 'Commandes', href: '/merchant/orders', icon: ClipboardDocumentListIcon },
  { name: 'Tombolas', href: '/merchant/lotteries', icon: GiftIcon },
  { name: 'Statistiques', href: '/merchant/analytics', icon: ChartBarIcon },
  { name: 'Paramètres', href: '/merchant/settings', icon: CogIcon }
]

const userNavigation = [
  { name: 'Profil', href: '/merchant/profile', icon: UserIcon },
  { name: 'Paramètres', href: '/merchant/settings', icon: CogIcon }
]

// Computed
const user = computed(() => authStore.user)
const userInitials = computed(() => {
  if (!user.value) return 'U'
  const first = user.value.first_name?.[0] || ''
  const last = user.value.last_name?.[0] || ''
  return (first + last).toUpperCase()
})

const showQuickStats = computed(() => {
  return window.innerWidth < 1024 // Show on mobile/tablet
})

// Methods
const logout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

const handleImageError = () => {
  logoError.value = true
}

const onAvatarError = (event) => {
  console.log('Avatar image failed to load, hiding image element')
  // Cache l'image et force l'affichage des initiales
  event.target.style.display = 'none'
}

const loadQuickStats = async () => {
  try {
    const response = await get('/merchant/dashboard/stats')
    const data = response.data

    quickStats.value = {
      products: data.total_products?.toString() || '0',
      lotteries: data.active_lotteries?.toString() || '0',
      revenue: formatCurrency(data.revenue_this_month || 0)
    }
  } catch (error) {
    console.error('Erreur lors du chargement des stats rapides:', error)
    // Set default values on error to prevent display issues
    quickStats.value = {
      products: '0',
      lotteries: '0',
      revenue: '0 FCFA'
    }
  }
}

const loadNotifications = async () => {
  try {
    const response = await get('/notifications/unread-count')
    if (response && response.data && response.data.unread_count !== undefined) {
      unreadNotifications.value = response.data.unread_count
    }
  } catch (error) {
    console.error('Erreur lors du chargement des notifications:', error)
    unreadNotifications.value = 0
  }
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

// Lifecycle
onMounted(() => {
  loadQuickStats()
  loadNotifications()
  
  // Recharger les notifications toutes les 2 minutes
  const notificationInterval = setInterval(loadNotifications, 2 * 60 * 1000)
  
  // Recharger les stats toutes les 5 minutes
  const statsInterval = setInterval(loadQuickStats, 5 * 60 * 1000)

  // Close menus when clicking outside
  const handleClickOutside = (e) => {
    if (!e.target.closest('.relative')) {
      userMenuOpen.value = false
    }
  }
  
  document.addEventListener('click', handleClickOutside)
  
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    clearInterval(notificationInterval)
    clearInterval(statsInterval)
  })
})
</script>
