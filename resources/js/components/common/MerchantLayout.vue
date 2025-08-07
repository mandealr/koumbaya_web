<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo and main nav -->
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <router-link to="/merchant/dashboard" class="flex items-center">
                <img class="h-8 w-auto" src="/images/logo.png" alt="Koumbaya" />
                <span class="ml-2 text-xl font-bold text-gray-900">Koumbaya</span>
                <span class="ml-1 text-sm text-green-600 font-medium">Marchand</span>
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
                    ? 'border-green-500 text-gray-900' 
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                <component :is="item.icon" class="w-4 h-4 mr-2" />
                {{ item.name }}
              </router-link>
            </div>
          </div>

          <!-- Right side -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button class="relative p-2 text-gray-400 hover:text-gray-500">
              <BellIcon class="h-6 w-6" />
              <span v-if="unreadNotifications > 0" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ unreadNotifications }}
              </span>
            </button>

            <!-- User menu -->
            <div class="relative ml-3">
              <div>
                <button 
                  @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center max-w-xs bg-white rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <span class="sr-only">Open user menu</span>
                  <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                    <span class="text-sm font-medium text-green-600">
                      {{ userInitials }}
                    </span>
                  </div>
                  <span class="ml-3 text-gray-700 text-sm font-medium hidden lg:block">
                    {{ user?.first_name }} {{ user?.last_name }}
                  </span>
                  <ChevronDownIcon class="ml-2 h-4 w-4 text-gray-500" />
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
              ? 'bg-green-50 border-green-500 text-green-700'
              : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'
          ]"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3 inline" />
          {{ item.name }}
        </router-link>
      </div>
    </div>

    <!-- Page content -->
    <main class="py-10">
      <slot />
    </main>

    <!-- Quick Stats Footer -->
    <div v-if="showQuickStats" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 lg:hidden z-40">
      <div class="flex justify-around text-center">
        <div>
          <div class="text-lg font-semibold text-gray-900">{{ quickStats.products }}</div>
          <div class="text-xs text-gray-600">Produits</div>
        </div>
        <div>
          <div class="text-lg font-semibold text-green-600">{{ quickStats.lotteries }}</div>
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/api'
import {
  HomeIcon,
  ShoppingBagIcon,
  GiftIcon,
  ChartBarIcon,
  CogIcon,
  UserIcon,
  BellIcon,
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
const unreadNotifications = ref(0)
const quickStats = ref({
  products: '0',
  lotteries: '0',
  revenue: '0 FCFA'
})

// Navigation items
const navigation = [
  { name: 'Dashboard', href: '/merchant/dashboard', icon: HomeIcon },
  { name: 'Produits', href: '/merchant/products', icon: ShoppingBagIcon },
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
  router.push('/auth/login')
}

const loadQuickStats = async () => {
  try {
    const response = await get('/merchant/dashboard/stats')
    const data = response.data
    
    quickStats.value = {
      products: data.total_products.toString(),
      lotteries: data.active_lotteries.toString(),
      revenue: formatCurrency(data.revenue_this_month)
    }
  } catch (error) {
    console.error('Erreur lors du chargement des stats rapides:', error)
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
  
  // Close menus when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
      userMenuOpen.value = false
    }
  })
})
</script>