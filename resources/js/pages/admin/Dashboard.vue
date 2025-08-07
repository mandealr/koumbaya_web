<template>
  <div class="px-6">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Dashboard Administrateur</h1>
      <p class="mt-2 text-gray-600">Vue d'ensemble des activités de la plateforme</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div 
        v-for="stat in stats" 
        :key="stat.label"
        class="bg-white p-6 rounded-lg shadow-sm border border-gray-200"
      >
        <div class="flex items-center">
          <div :class="[
            'p-2 rounded-md',
            stat.color
          ]">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
        <div class="mt-4">
          <div class="flex items-center text-sm">
            <span :class="[
              'font-medium',
              stat.change >= 0 ? 'text-green-600' : 'text-red-600'
            ]">
              {{ stat.change >= 0 ? '+' : '' }}{{ stat.change }}%
            </span>
            <span class="ml-2 text-gray-600">vs mois dernier</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Recent Activities -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Activités Récentes</h3>
        <div class="space-y-4">
          <div 
            v-for="activity in recentActivities" 
            :key="activity.id"
            class="flex items-center space-x-4 p-3 hover:bg-gray-50 rounded-lg"
          >
            <div :class="[
              'w-10 h-10 rounded-full flex items-center justify-center',
              activity.type === 'user' ? 'bg-blue-100 text-blue-600' :
              activity.type === 'product' ? 'bg-green-100 text-green-600' :
              'bg-yellow-100 text-yellow-600'
            ]">
              <component :is="activity.icon" class="w-5 h-5" />
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
              <p class="text-sm text-gray-600">{{ activity.description }}</p>
            </div>
            <div class="text-xs text-gray-500">
              {{ formatTime(activity.time) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Top Products -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Produits Populaires</h3>
        <div class="space-y-4">
          <div 
            v-for="product in topProducts" 
            :key="product.id"
            class="flex items-center space-x-4"
          >
            <img 
              :src="product.image" 
              :alt="product.title"
              class="w-12 h-12 rounded-lg object-cover"
            />
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ product.title }}</p>
              <p class="text-sm text-gray-600">{{ product.sales }} billets vendus</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">{{ product.price }} FCFA</p>
              <p class="text-xs text-green-600">+{{ product.growth }}%</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Lotteries -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Loteries Récentes</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Produit
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Progression
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Date de tirage
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="lottery in recentLotteries" :key="lottery.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <img :src="lottery.product.image" :alt="lottery.product.title" 
                       class="w-10 h-10 rounded-lg object-cover" />
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ lottery.product.title }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  lottery.status === 'active' ? 'bg-green-100 text-green-800' :
                  lottery.status === 'completed' ? 'bg-blue-100 text-blue-800' :
                  'bg-gray-100 text-gray-800'
                ]">
                  {{ lottery.status === 'active' ? 'Active' : 
                     lottery.status === 'completed' ? 'Terminée' : 'En attente' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div 
                      class="bg-green-600 h-2 rounded-full" 
                      :style="{ width: lottery.progress + '%' }"
                    ></div>
                  </div>
                  <span class="text-sm text-gray-600">{{ lottery.progress }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(lottery.draw_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button class="text-green-600 hover:text-green-900 mr-3">
                  Voir
                </button>
                <button v-if="lottery.status === 'active'" class="text-blue-600 hover:text-blue-900">
                  Tirer
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
  UsersIcon,
  ShoppingBagIcon,
  GiftIcon,
  CurrencyDollarIcon,
  UserPlusIcon,
  PlusIcon,
  TicketIcon
} from '@heroicons/vue/24/outline'

const stats = ref([
  {
    label: 'Utilisateurs Total',
    value: '2,847',
    change: 12,
    color: 'bg-blue-500',
    icon: UsersIcon
  },
  {
    label: 'Produits Actifs',
    value: '156',
    change: 8,
    color: 'bg-green-500',
    icon: ShoppingBagIcon
  },
  {
    label: 'Loteries en Cours',
    value: '23',
    change: -2,
    color: 'bg-yellow-500',
    icon: GiftIcon
  },
  {
    label: 'Revenus ce Mois',
    value: '2.4M FCFA',
    change: 15,
    color: 'bg-purple-500',
    icon: CurrencyDollarIcon
  }
])

const recentActivities = ref([
  {
    id: 1,
    type: 'user',
    icon: UserPlusIcon,
    title: 'Nouvel utilisateur',
    description: 'Jean Dupont s\'est inscrit',
    time: new Date(Date.now() - 10 * 60 * 1000) // 10 minutes ago
  },
  {
    id: 2,
    type: 'product',
    icon: PlusIcon,
    title: 'Nouveau produit',
    description: 'iPhone 15 Pro ajouté par Merchant1',
    time: new Date(Date.now() - 30 * 60 * 1000) // 30 minutes ago
  },
  {
    id: 3,
    type: 'lottery',
    icon: TicketIcon,
    title: 'Billet acheté',
    description: 'Marie Claire a acheté 3 billets',
    time: new Date(Date.now() - 60 * 60 * 1000) // 1 hour ago
  }
])

const topProducts = ref([
  {
    id: 1,
    title: 'iPhone 15 Pro',
    sales: 234,
    price: '500,000',
    growth: 25,
    image: '/images/products/iphone.jpg'
  },
  {
    id: 2,
    title: 'MacBook Pro M3',
    sales: 189,
    price: '1,200,000',
    growth: 18,
    image: '/images/products/macbook.jpg'
  },
  {
    id: 3,
    title: 'AirPods Pro',
    sales: 156,
    price: '150,000',
    growth: 32,
    image: '/images/products/airpods.jpg'
  }
])

const recentLotteries = ref([
  {
    id: 1,
    product: {
      title: 'iPhone 15 Pro',
      image: '/images/products/iphone.jpg'
    },
    status: 'active',
    progress: 85,
    draw_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000) // 7 days from now
  },
  {
    id: 2,
    product: {
      title: 'MacBook Pro M3',
      image: '/images/products/macbook.jpg'
    },
    status: 'active',
    progress: 62,
    draw_date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000) // 14 days from now
  },
  {
    id: 3,
    product: {
      title: 'PlayStation 5',
      image: '/images/products/ps5.jpg'
    },
    status: 'completed',
    progress: 100,
    draw_date: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000) // 2 days ago
  }
])

const formatTime = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / (1000 * 60))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  
  if (minutes < 60) {
    return `il y a ${minutes}m`
  } else if (hours < 24) {
    return `il y a ${hours}h`
  } else {
    const days = Math.floor(hours / 24)
    return `il y a ${days}j`
  }
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(date)
}

onMounted(() => {
  // Load dashboard data
  console.log('Dashboard mounted')
})
</script>