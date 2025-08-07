<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out" 
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
      <div class="flex items-center justify-center h-16 bg-green-600">
        <h1 class="text-xl font-bold text-white">Koumbaya Admin</h1>
      </div>
      
      <nav class="mt-8">
        <div class="px-4 space-y-2">
          <router-link
            v-for="item in menuItems"
            :key="item.name"
            :to="item.to"
            :class="[
              'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors',
              $route.name === item.name 
                ? 'bg-green-50 text-green-700 border-r-2 border-green-700' 
                : 'text-gray-600 hover:bg-gray-50 hover:text-green-600'
            ]"
          >
            <component :is="item.icon" class="w-5 h-5 mr-3" />
            {{ item.label }}
          </router-link>
        </div>
      </nav>
    </aside>

    <!-- Mobile sidebar overlay -->
    <div 
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-gray-600 bg-opacity-50 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Main content -->
    <div class="lg:pl-64">
      <!-- Top header -->
      <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between px-6 py-4">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>

          <div class="flex items-center space-x-4">
            <!-- User dropdown -->
            <div class="relative">
              <button
                @click="userDropdownOpen = !userDropdownOpen"
                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50"
              >
                <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                  <span class="text-sm font-medium text-white">{{ userInitials }}</span>
                </div>
                <div class="hidden md:block text-left">
                  <p class="text-sm font-medium text-gray-700">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
                  <p class="text-xs text-gray-500">Administrateur</p>
                </div>
              </button>

              <!-- Dropdown menu -->
              <div
                v-if="userDropdownOpen"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                @click.outside="userDropdownOpen = false"
              >
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Mon Profil
                </a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Paramètres
                </a>
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Se déconnecter
                </button>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="py-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import {
  HomeIcon,
  UsersIcon,
  ShoppingBagIcon,
  GiftIcon,
  CogIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const router = useRouter()

const sidebarOpen = ref(false)
const userDropdownOpen = ref(false)

const menuItems = [
  {
    name: 'admin.dashboard',
    to: { name: 'admin.dashboard' },
    label: 'Dashboard',
    icon: HomeIcon
  },
  {
    name: 'admin.users',
    to: { name: 'admin.users' },
    label: 'Utilisateurs',
    icon: UsersIcon
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
    label: 'Loteries',
    icon: GiftIcon
  },
  {
    name: 'admin.settings',
    to: { name: 'admin.settings' },
    label: 'Paramètres',
    icon: CogIcon
  }
]

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