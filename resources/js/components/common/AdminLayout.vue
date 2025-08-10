<template>
  <div class="min-h-screen admin-layout">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-72 admin-sidebar transform transition-transform duration-300 ease-in-out lg:translate-x-0" 
           :class="{ '-translate-x-full lg:translate-x-0': !sidebarOpen, 'translate-x-0': sidebarOpen }">
      
      <!-- Logo Header -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-gray-700">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center p-1">
            <img src="/icon.png" alt="Koumbaya" class="w-full h-full object-contain" />
          </div>
          <div class="text-white">
            <h1 class="text-lg font-bold">Koumbaya</h1>
            <p class="text-xs text-gray-300">Administration</p>
          </div>
        </div>
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
              <div class="w-10 h-10 bg-gray-600 rounded-full flex items-center justify-center">
                <UserIcon class="w-5 h-5 text-gray-300" />
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
      class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 lg:hidden"
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
            <button class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
              <BellIcon class="w-6 h-6" />
              <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

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
                <div class="w-8 h-8 bg-[#0099cc] rounded-lg flex items-center justify-center">
                  <span class="text-sm font-semibold text-white">{{ userInitials }}</span>
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
                <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                  <UserIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Mon Profil
                </a>
                <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                  <Cog6ToothIcon class="w-4 h-4 mr-3 text-gray-400" />
                  Paramètres
                </a>
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
import {
  HomeIcon,
  UsersIcon,
  ShoppingBagIcon,
  GiftIcon,
  Cog6ToothIcon,
  BellIcon,
  MagnifyingGlassIcon,
  UserIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  ChevronRightIcon,
  ChevronDownIcon,
  CurrencyDollarIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const sidebarOpen = ref(true) // Open by default on desktop
const userDropdownOpen = ref(false)

const menuItems = [
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
    badge: '12'
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
    badge: '3'
  },
  {
    name: 'admin.refunds',
    to: { name: 'admin.refunds' },
    label: 'Remboursements',
    icon: CurrencyDollarIcon
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
]

const currentPageTitle = computed(() => {
  const currentItem = menuItems.find(item => item.name === route.name)
  return currentItem?.label || 'Page'
})

const userInitials = computed(() => {
  const user = authStore.user
  if (!user) return 'AD'
  return (user.first_name?.[0] || '') + (user.last_name?.[0] || '')
})

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    userDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>