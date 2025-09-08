<template>
  <div class="min-h-screen admin-layout">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-72 admin-sidebar transform transition-transform duration-300 ease-in-out lg:translate-x-0"
           :class="{ '-translate-x-full lg:translate-x-0': !sidebarOpen, 'translate-x-0': sidebarOpen }">

      <!-- Logo Header -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-gray-700">
        <router-link to="/admin/dashboard" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
          <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center p-1">
            <img :src="logoIcon" alt="Koumbaya" class="w-full h-full object-contain" />
          </div>
          <div class="text-white">
            <h1 class="text-lg font-bold">Koumbaya</h1>
            <p class="text-xs text-gray-300">Administration</p>
          </div>
        </router-link>
      </div>

      <!-- Navigation -->
      <nav class="mt-6 flex-1">
        <div class="px-3 space-y-1">
          <router-link
            v-for="item in menuItems"
            :key="item.name"
            :to="item.to"
            :class="[
              'admin-sidebar-item',
              $route.name === item.name ? 'active' : ''
            ]"
          >
            <component :is="item.icon" class="w-5 h-5 mr-3" />
            <span class="font-medium">{{ item.label }}</span>
            <span v-if="item.badge" class="ml-auto bg-orange-500 text-white text-xs px-2 py-1 rounded-full">
              {{ item.badge }}
            </span>
          </router-link>
        </div>

        <!-- Sidebar Footer -->
        <div class="absolute bottom-4 left-0 right-0 px-3">
          <div class="bg-gray-700 rounded-lg p-4">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border border-gray-600">
                <img
                  v-if="authStore.user?.avatar_url"
                  :src="authStore.user.avatar_url"
                  :alt="`Photo de profil de ${authStore.user.first_name} ${authStore.user.last_name}`"
                  class="w-full h-full object-cover"
                  @error="onAvatarError"
                />
                <div v-else class="w-full h-full bg-gray-600 flex items-center justify-center">
                  <span class="text-sm font-medium text-gray-300">
                    {{ userInitials }}
                  </span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
                <p class="text-xs text-gray-300 truncate">Super Administrateur</p>
              </div>
              <button
                @click="handleLogout"
                class="text-gray-300 hover:text-white transition-colors"
                title="Se déconnecter"
              >
                <ArrowRightOnRectangleIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Mobile sidebar overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-black/40 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Main content -->
    <div class="lg:pl-72">
      <!-- Top header -->
      <header class="admin-header sticky top-0 z-30">
        <div class="flex items-center justify-between px-6 py-4">
          <!-- Mobile menu button -->
          <div class="flex items-center space-x-4">
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
            >
              <Bars3Icon class="w-6 h-6" />
            </button>

            <!-- Breadcrumb -->
            <nav class="hidden lg:flex items-center space-x-2 text-sm">
              <HomeIcon class="w-4 h-4 text-gray-400" />
              <ChevronRightIcon class="w-4 h-4 text-gray-400" />
              <span class="text-gray-600">{{ currentPageTitle }}</span>
            </nav>
          </div>

          <!-- Header Actions -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <NotificationIcon />

            <!-- Search -->
            <div class="hidden md:flex items-center">
              <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                  type="text"
                  placeholder="Rechercher..."
                  class="pl-10 pr-4 py-2 w-64 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0099cc] focus:border-transparent"
                />
              </div>
            </div>

            <!-- User Profile -->
            <div class="relative">
              <button
                @click="userDropdownOpen = !userDropdownOpen"
                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0 border border-gray-200">
                  <img
                    v-if="authStore.user?.avatar_url"
                    :src="authStore.user.avatar_url"
                    :alt="`Photo de profil de ${authStore.user.first_name} ${authStore.user.last_name}`"
                    class="w-full h-full object-cover"
                    @error="onAvatarError"
                  />
                  <div v-else class="w-full h-full bg-[#0099cc] flex items-center justify-center">
                    <span class="text-sm font-semibold text-white">{{ userInitials }}</span>
                  </div>
                </div>
                <div class="hidden md:block text-left">
                  <p class="text-sm font-semibold text-gray-800">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
                  <p class="text-xs text-gray-500">Super Admin</p>
                </div>
                <ChevronDownIcon class="w-4 h-4 text-gray-400" />
              </button>

              <!-- Dropdown menu -->
              <div
                v-if="userDropdownOpen"
                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50"
                @click.outside="userDropdownOpen = false"
              >
                <div class="px-4 py-3 border-b border-gray-100">
                  <p class="text-sm font-medium text-gray-800">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
                  <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
                </div>
                <router-link
                  :to="{ name: 'admin.profile' }"
                  @click="userDropdownOpen = false"
                  class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                >
                  <UserIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Mon Profil
                </router-link>
                <router-link
                  :to="{ name: 'admin.settings' }"
                  @click="userDropdownOpen = false"
                  class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                >
                  <Cog6ToothIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Paramètres
                </router-link>
                <div class="border-t border-gray-100 mt-2 pt-2">
                  <button
                    @click="handleLogout"
                    class="flex items-center w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors"
                  >
                    <ArrowRightOnRectangleIcon class="w-4 h-4 mr-3" />
                    Se déconnecter
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'
import { useApi } from '@/composables/api'
import logoIcon from '/icon.png'
import NotificationIcon from '@/components/common/NotificationIcon.vue'
import {
  HomeIcon,
  UsersIcon,
  ShoppingBagIcon,
  GiftIcon,
  Cog6ToothIcon,
  MagnifyingGlassIcon,
  UserIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  ChevronRightIcon,
  ChevronDownIcon,
  CurrencyDollarIcon,
  ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const { get } = useApi()

const sidebarOpen = ref(true) // Open by default on desktop
const userDropdownOpen = ref(false)
const badgeCounts = ref({
  users: 0,
  lotteries: 0,
  refunds: 0
})

const menuItems = computed(() => [
  {
    name: 'admin.dashboard',
    to: { name: 'admin.dashboard' },
    label: 'Tableau de bord',
    icon: HomeIcon
  },
  {
    name: 'admin.users',
    to: { name: 'admin.users' },
    label: 'Utilisateurs',
    icon: UsersIcon,
    badge: badgeCounts.value.users > 0 ? badgeCounts.value.users.toString() : null
  },
  {
    name: 'admin.products',
    to: { name: 'admin.products' },
    label: 'Produits',
    icon: ShoppingBagIcon
  },
  {
    name: 'admin.lotteries',
    to: { name: 'admin.lotteries' },
    label: 'Tombolas',
    icon: GiftIcon,
    badge: badgeCounts.value.lotteries > 0 ? badgeCounts.value.lotteries.toString() : null
  },
  {
    name: 'admin.orders',
    to: { name: 'admin.orders' },
    label: 'Commandes',
    icon: ClipboardDocumentListIcon
  },
  {
    name: 'admin.refunds',
    to: { name: 'admin.refunds' },
    label: 'Remboursements',
    icon: CurrencyDollarIcon,
    badge: badgeCounts.value.refunds > 0 ? badgeCounts.value.refunds.toString() : null
  },
  {
    name: 'admin.payments',
    to: { name: 'admin.payments' },
    label: 'Paiements',
    icon: CurrencyDollarIcon
  },
  {
    name: 'admin.settings',
    to: { name: 'admin.settings' },
    label: 'Paramètres',
    icon: Cog6ToothIcon
  }
])

const currentPageTitle = computed(() => {
  const currentItem = menuItems.value.find(item => item.name === route.name)
  return currentItem?.label || 'Page'
})

// Charger les compteurs de badges
const loadBadgeCounts = async () => {
  try {
    // Nombre d'utilisateurs récemment inscrits (dernières 24h)
    const usersResponse = await get('/admin/dashboard/stats')
    if (usersResponse?.data?.users) {
      // Afficher le badge seulement s'il y a une croissance positive
      badgeCounts.value.users = usersResponse.data.users.growth > 0 ?
        Math.ceil(usersResponse.data.users.total * (usersResponse.data.users.growth / 100)) : 0
    }

    // Nombre de tombolas en attente de tirage
    const lotteriesResponse = await get('/admin/lotteries?status=active')
    if (lotteriesResponse?.data?.lotteries) {
      // Compter les tombolas qui arrivent à échéance dans les 24h
      const endingSoon = lotteriesResponse.data.lotteries.filter(lottery => {
        const endDate = new Date(lottery.end_date)
        const now = new Date()
        const hoursLeft = (endDate - now) / (1000 * 60 * 60)
        return hoursLeft <= 24 && hoursLeft > 0
      })
      badgeCounts.value.lotteries = endingSoon.length
    }

    // Nombre de demandes de remboursement en attente
    const refundsResponse = await get('/admin/refunds?status=pending')
    if (refundsResponse?.data?.refunds) {
      badgeCounts.value.refunds = refundsResponse.data.refunds.length
    }
  } catch (error) {
    console.error('Erreur lors du chargement des compteurs de badges:', error)
  }
}

const userInitials = computed(() => {
  const user = authStore.user
  if (!user) return 'AD'
  return (user.first_name?.[0] || '') + (user.last_name?.[0] || '')
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

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    userDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  loadBadgeCounts()

  // Recharger les compteurs toutes les 5 minutes
  const interval = setInterval(loadBadgeCounts, 5 * 60 * 1000)

  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    clearInterval(interval)
  })
})
</script>
