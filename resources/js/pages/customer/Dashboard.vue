<template>
  <div>
    <!-- Welcome Section -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">
        Bonjour {{ authStore.user?.first_name }} !
      </h1>
      <p class="mt-2 text-gray-600">
        Voici un aperçu de vos activités sur la plateforme
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Loading skeleton -->
      <div v-if="loading" v-for="n in 4" :key="n" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 animate-pulse">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-gray-200"></div>
          <div class="ml-4 flex-1">
            <div class="h-4 bg-gray-200 rounded w-20 mb-2"></div>
            <div class="h-6 bg-gray-200 rounded w-16"></div>
          </div>
        </div>
      </div>

      <!-- Stats cards -->
      <div
        v-else
        v-for="stat in dashboardStats"
        :key="stat.label"
        class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex items-center">
          <div :class="['p-3 rounded-lg', stat.color]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Error message -->
    <div v-if="error && !loading" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Erreur de chargement</h3>
          <p class="mt-1 text-sm text-red-700">{{ error }}</p>
          <button 
            @click="refreshDashboard"
            class="mt-2 text-sm bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded"
          >
            Réessayer
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <router-link
          to="/customer/products"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-[#0099cc] hover:bg-blue-50 transition-colors"
        >
          <ShoppingBagIcon class="w-8 h-8 text-[#0099cc] mr-3" />
          <div>
            <p class="font-medium text-gray-900">Acheter des tickets</p>
            <p class="text-sm text-gray-600">Découvrir les produits disponibles</p>
          </div>
        </router-link>

        <router-link
          to="/customer/tickets"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-black hover:bg-gray-50 transition-colors"
        >
          <TicketIcon class="w-8 h-8 text-black mr-3" />
          <div>
            <p class="font-medium text-gray-900">Mes tickets</p>
            <p class="text-sm text-gray-600">Voir mes participations</p>
          </div>
        </router-link>

        <router-link
          to="/customer/profile"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-[#0099cc] hover:bg-blue-50 transition-colors"
        >
          <UserIcon class="w-8 h-8 text-[#0099cc] mr-3" />
          <div>
            <p class="font-medium text-gray-900">Mon profil</p>
            <p class="text-sm text-gray-600">Gérer mes informations</p>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Recent Activity & Current Lotteries -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- My Recent Tickets -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Mes derniers tickets</h3>
          <router-link
            to="/customer/tickets"
            class="text-sm text-[#0099cc] hover:text-blue-700"
          >
            Voir tout
          </router-link>
        </div>

        <div v-if="recentTickets.length === 0" class="text-center py-8">
          <TicketIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <p class="text-gray-600">Aucun ticket acheté pour le moment</p>
          <router-link
            to="/customer/products"
            class="inline-block mt-2 text-[#0099cc] hover:text-blue-700"
          >
            Acheter des tickets
          </router-link>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="ticket in recentTickets"
            :key="ticket.id"
            class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg"
          >
            <img
              :src="ticket.product.image_url || ticket.product.main_image || ticket.product.image"
              :alt="ticket.product.title"
              class="w-12 h-12 rounded-lg object-cover"
            />
            <div class="flex-1">
              <p class="font-medium text-gray-900">{{ ticket.product.title }}</p>
              <p class="text-sm text-gray-600">
                {{ ticket.quantity }} ticket{{ ticket.quantity > 1 ? 's' : '' }}
                - {{ ticket.total_price }} FCFA
              </p>
              <p class="text-xs text-gray-500">{{ formatDate(ticket.purchased_at) }}</p>
            </div>
            <span :class="[
              'px-2 py-1 text-xs font-medium rounded-full',
              ticket.status === 'active' ? 'bg-[#0099cc] text-white' :
              ticket.status === 'won' ? 'bg-yellow-100 text-yellow-800' :
              'bg-gray-100 text-gray-800'
            ]">
              {{ getStatusLabel(ticket.status) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Active Lotteries -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Tombolas populaires</h3>
          <router-link
            to="/customer/products"
            class="text-sm text-[#0099cc] hover:text-blue-700"
          >
            Voir tout
          </router-link>
        </div>

        <div class="space-y-4">
          <div
            v-for="lottery in activeLotteries"
            :key="lottery.id"
            class="border border-gray-200 rounded-lg p-4 hover:border-[#0099cc] transition-colors"
          >
            <div class="flex items-start space-x-4">
              <img
                :src="lottery.product.image_url || lottery.product.main_image || lottery.product.image"
                :alt="lottery.product.title"
                class="w-16 h-16 rounded-lg object-cover"
              />
              <div class="flex-1">
                <h4 class="font-medium text-gray-900 mb-1">{{ lottery.product.title }}</h4>
                <p class="text-sm text-gray-600 mb-2">{{ lottery.product.price }} FCFA</p>

                <div class="mb-3">
                  <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progression</span>
                    <span>{{ lottery.progress }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-[#0099cc] h-2 rounded-full transition-all duration-300"
                      :style="{ width: lottery.progress + '%' }"
                    ></div>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ lottery.sold_tickets }} / {{ lottery.total_tickets }} tickets vendus
                  </p>
                </div>

                <div class="flex justify-between items-center">
                  <span class="text-xs text-gray-500">
                    Tirage le {{ formatDate(lottery.draw_date) }}
                  </span>
                  <router-link
                    v-if="lottery.sold_tickets < lottery.total_tickets"
                    :to="`/customer/products/${lottery.product.id}`"
                    class="text-sm bg-[#0099cc] text-white px-3 py-1 rounded-md hover:bg-blue-700 transition-colors"
                  >
                    Participer
                  </router-link>
                  <span
                    v-else
                    class="text-sm bg-gray-400 text-white px-3 py-1 rounded-md"
                  >
                    Complet
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useCustomerDashboard } from '@/composables/useCustomerDashboard'
import {
  TicketIcon,
  GiftIcon,
  CurrencyDollarIcon,
  TrophyIcon,
  ShoppingBagIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()

// Composables
const {
  stats,
  recentTickets,
  activeLotteries,
  loading,
  error,
  loadDashboardData,
  formatDate
} = useCustomerDashboard()

// Icon mapping for dynamic components
const iconComponents = {
  TicketIcon,
  GiftIcon,
  CurrencyDollarIcon,
  TrophyIcon
}

// Computed stats with resolved icons
const dashboardStats = computed(() => {
  return stats.value.map(stat => ({
    ...stat,
    icon: iconComponents[stat.icon] || TicketIcon
  }))
})

const getStatusLabel = (status) => {
  const labels = {
    'active': 'Actif',
    'won': 'Gagné',
    'lost': 'Perdu',
    'pending': 'En attente'
  }
  return labels[status] || status
}

// Refresh data
const refreshDashboard = async () => {
  await loadDashboardData()
}

onMounted(async () => {
  await loadDashboardData()
})
</script>
