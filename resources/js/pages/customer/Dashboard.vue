<template>
  <div>
    <!-- Welcome Section -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">
        Bonjour {{ authStore.user?.first_name }} ! 👋
      </h1>
      <p class="mt-2 text-gray-600">
        Voici un aperçu de vos activités sur la plateforme
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white p-6 rounded-lg shadow-sm border border-gray-200"
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

    <!-- Quick Actions -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions rapides</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <router-link
          to="/customer/products"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors"
        >
          <ShoppingBagIcon class="w-8 h-8 text-green-600 mr-3" />
          <div>
            <p class="font-medium text-gray-900">Acheter des billets</p>
            <p class="text-sm text-gray-600">Découvrir les produits disponibles</p>
          </div>
        </router-link>
        
        <router-link
          to="/customer/tickets"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors"
        >
          <TicketIcon class="w-8 h-8 text-blue-600 mr-3" />
          <div>
            <p class="font-medium text-gray-900">Mes billets</p>
            <p class="text-sm text-gray-600">Voir mes participations</p>
          </div>
        </router-link>
        
        <router-link
          to="/customer/profile"
          class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors"
        >
          <UserIcon class="w-8 h-8 text-purple-600 mr-3" />
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
          <h3 class="text-lg font-semibold text-gray-900">Mes derniers billets</h3>
          <router-link
            to="/customer/tickets"
            class="text-sm text-green-600 hover:text-green-700"
          >
            Voir tout
          </router-link>
        </div>
        
        <div v-if="recentTickets.length === 0" class="text-center py-8">
          <TicketIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <p class="text-gray-600">Aucun billet acheté pour le moment</p>
          <router-link
            to="/customer/products"
            class="inline-block mt-2 text-green-600 hover:text-green-700"
          >
            Acheter des billets
          </router-link>
        </div>
        
        <div v-else class="space-y-4">
          <div
            v-for="ticket in recentTickets"
            :key="ticket.id"
            class="flex items-center space-x-4 p-3 border border-gray-200 rounded-lg"
          >
            <img
              :src="ticket.product.image"
              :alt="ticket.product.title"
              class="w-12 h-12 rounded-lg object-cover"
            />
            <div class="flex-1">
              <p class="font-medium text-gray-900">{{ ticket.product.title }}</p>
              <p class="text-sm text-gray-600">
                {{ ticket.quantity }} billet{{ ticket.quantity > 1 ? 's' : '' }}
                - {{ ticket.total_price }} FCFA
              </p>
              <p class="text-xs text-gray-500">{{ formatDate(ticket.purchased_at) }}</p>
            </div>
            <span :class="[
              'px-2 py-1 text-xs font-medium rounded-full',
              ticket.status === 'active' ? 'bg-green-100 text-green-800' :
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
          <h3 class="text-lg font-semibold text-gray-900">Loteries populaires</h3>
          <router-link
            to="/customer/products"
            class="text-sm text-green-600 hover:text-green-700"
          >
            Voir tout
          </router-link>
        </div>
        
        <div class="space-y-4">
          <div
            v-for="lottery in activeLotteries"
            :key="lottery.id"
            class="border border-gray-200 rounded-lg p-4 hover:border-green-500 transition-colors"
          >
            <div class="flex items-start space-x-4">
              <img
                :src="lottery.product.image"
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
                      class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                      :style="{ width: lottery.progress + '%' }"
                    ></div>
                  </div>
                </div>
                
                <div class="flex justify-between items-center">
                  <span class="text-xs text-gray-500">
                    Tirage le {{ formatDate(lottery.draw_date) }}
                  </span>
                  <router-link
                    :to="`/customer/products/${lottery.product.id}`"
                    class="text-sm bg-green-600 text-white px-3 py-1 rounded-md hover:bg-green-700 transition-colors"
                  >
                    Participer
                  </router-link>
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
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import {
  TicketIcon,
  GiftIcon,
  CurrencyDollarIcon,
  TrophyIcon,
  ShoppingBagIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()

const stats = ref([
  {
    label: 'Billets achetés',
    value: '12',
    color: 'bg-blue-500',
    icon: TicketIcon
  },
  {
    label: 'Loteries participées',
    value: '5',
    color: 'bg-green-500',
    icon: GiftIcon
  },
  {
    label: 'Total dépensé',
    value: '15,000 FCFA',
    color: 'bg-yellow-500',
    icon: CurrencyDollarIcon
  },
  {
    label: 'Prix gagnés',
    value: '0',
    color: 'bg-purple-500',
    icon: TrophyIcon
  }
])

const recentTickets = ref([
  {
    id: 1,
    product: {
      id: 1,
      title: 'iPhone 15 Pro',
      image: '/images/products/iphone.jpg'
    },
    quantity: 3,
    total_price: '3,000',
    status: 'active',
    purchased_at: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000)
  },
  {
    id: 2,
    product: {
      id: 2,
      title: 'MacBook Pro M3',
      image: '/images/products/macbook.jpg'
    },
    quantity: 1,
    total_price: '2,000',
    status: 'active',
    purchased_at: new Date(Date.now() - 5 * 24 * 60 * 60 * 1000)
  }
])

const activeLotteries = ref([
  {
    id: 1,
    product: {
      id: 1,
      title: 'iPhone 15 Pro',
      price: '750,000',
      image: '/images/products/iphone.jpg'
    },
    progress: 85,
    draw_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)
  },
  {
    id: 2,
    product: {
      id: 3,
      title: 'PlayStation 5',
      price: '350,000',
      image: '/images/products/ps5.jpg'
    },
    progress: 95,
    draw_date: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000)
  },
  {
    id: 3,
    product: {
      id: 4,
      title: 'AirPods Pro',
      price: '150,000',
      image: '/images/products/airpods.jpg'
    },
    progress: 45,
    draw_date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000)
  }
])

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(date)
}

const getStatusLabel = (status) => {
  const labels = {
    'active': 'Actif',
    'won': 'Gagné',
    'lost': 'Perdu',
    'pending': 'En attente'
  }
  return labels[status] || status
}

onMounted(() => {
  // Load user dashboard data
  console.log('Customer dashboard mounted')
})
</script>