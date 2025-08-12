<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Tableau de bord</h1>
      <p class="mt-2 text-gray-600">Vue d'ensemble des activités de la plateforme Koumbaya</p>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div 
        v-for="stat in stats" 
        :key="stat.label"
        class="admin-card hover:shadow-md transition-shadow duration-200"
      >
        <div class="flex items-center">
          <div :class="[
            'p-3 rounded-lg',
            stat.bgColor
          ]">
            <component :is="stat.icon" :class="['w-6 h-6', stat.iconColor]" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center text-sm">
            <span :class="[
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
              stat.change >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            ]">
              <component 
                :is="stat.change >= 0 ? ArrowUpIcon : ArrowDownIcon" 
                class="w-3 h-3 mr-1" 
              />
              {{ Math.abs(stat.change) }}%
            </span>
            <span class="ml-2 text-gray-500">vs mois dernier</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Charts Column -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Revenue Chart -->
        <div class="admin-card">
          <div class="admin-card-header">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Revenus mensuels</h3>
              <div class="flex items-center space-x-2">
                <button class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1 rounded-md hover:bg-gray-100">30j</button>
                <button class="text-sm bg-[#0099cc]/10 text-[#0099cc] px-3 py-1 rounded-md">90j</button>
                <button class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1 rounded-md hover:bg-gray-100">1an</button>
              </div>
            </div>
          </div>
          <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <div class="text-center">
              <ChartBarIcon class="w-12 h-12 text-gray-400 mx-auto mb-2" />
              <p class="text-gray-500">Graphique des revenus</p>
              <p class="text-xs text-gray-400">Données en cours de chargement...</p>
            </div>
          </div>
        </div>

        <!-- Recent Tombolas Table -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900">Tombolas récentes</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tickets</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de fin</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="lottery in recentLotteries" :key="lottery.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <img class="h-10 w-10 rounded-lg object-cover" :src="lottery.product.image" :alt="lottery.product.name" />
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ lottery.product.name }}</div>
                        <div class="text-sm text-gray-500">{{ lottery.lottery_number }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                      lottery.status === 'active' ? 'admin-badge-success' :
                      lottery.status === 'completed' ? 'bg-gray-100 text-gray-800' :
                      'admin-badge-warning'
                    ]">
                      {{ lottery.statusLabel }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ lottery.sold_tickets }}/{{ lottery.total_tickets }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(lottery.end_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-[#0099cc] hover:text-[#0088bb] mr-3">Voir</button>
                    <button v-if="lottery.status === 'active'" class="text-orange-600 hover:text-orange-900">Modifier</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Sidebar -->
      <div class="space-y-6">
        <!-- Recent Activities -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900">Activités récentes</h3>
          </div>
          <div class="space-y-4">
            <div 
              v-for="activity in recentActivities" 
              :key="activity.id"
              class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div :class="[
                'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                activity.type === 'user' ? 'bg-[#0099cc]/10 text-[#0099cc]' :
                activity.type === 'product' ? 'bg-green-100 text-green-600' :
                activity.type === 'payment' ? 'bg-yellow-100 text-yellow-600' :
                'bg-purple-100 text-purple-600'
              ]">
                <component :is="activity.icon" class="w-4 h-4" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                <p class="text-sm text-gray-600">{{ activity.description }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatTime(activity.time) }}</p>
              </div>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-100">
            <button class="admin-btn-secondary w-full text-sm">
              Voir toutes les activités
            </button>
          </div>
        </div>

        <!-- Top Performers -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900">Produits populaires</h3>
          </div>
          <div class="space-y-4">
            <div 
              v-for="(product, index) in topProducts" 
              :key="product.id"
              class="flex items-center space-x-3"
            >
              <div class="flex-shrink-0">
                <span class="inline-flex items-center justify-center w-6 h-6 text-sm font-bold text-gray-600">
                  {{ index + 1 }}
                </span>
              </div>
              <img 
                :src="product.image" 
                :alt="product.title"
                class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ product.title }}</p>
                <div class="flex items-center space-x-2 mt-1">
                  <p class="text-xs text-gray-500">{{ product.sales }} tickets</p>
                  <span :class="[
                    'text-xs font-medium',
                    product.growth > 0 ? 'text-green-600' : 'text-red-600'
                  ]">
                    {{ product.growth > 0 ? '+' : '' }}{{ product.growth }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900">Actions rapides</h3>
          </div>
          <div class="space-y-3">
            <button class="admin-btn-primary w-full justify-center">
              <PlusIcon class="w-4 h-4 mr-2" />
              Nouveau produit
            </button>
            <button class="admin-btn-secondary w-full justify-center">
              <UserPlusIcon class="w-4 h-4 mr-2" />
              Ajouter utilisateur
            </button>
            <button class="admin-btn-accent w-full justify-center">
              <DocumentTextIcon class="w-4 h-4 mr-2" />
              Générer rapport
            </button>
          </div>
        </div>
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
  ArrowUpIcon,
  ArrowDownIcon,
  ChartBarIcon,
  PlusIcon,
  UserPlusIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

// Data
const stats = ref([
  {
    label: 'Utilisateurs actifs',
    value: '2,543',
    change: 12.5,
    icon: UsersIcon,
    bgColor: 'bg-[#0099cc]/10',
    iconColor: 'text-[#0099cc]'
  },
  {
    label: 'Produits en vente',
    value: '186',
    change: 8.2,
    icon: ShoppingBagIcon,
    bgColor: 'bg-green-100',
    iconColor: 'text-green-600'
  },
  {
    label: 'Tombolas actives',
    value: '24',
    change: -2.1,
    icon: GiftIcon,
    bgColor: 'bg-yellow-100',
    iconColor: 'text-yellow-600'
  },
  {
    label: 'Revenus mensuel',
    value: '45,2M',
    change: 15.3,
    icon: CurrencyDollarIcon,
    bgColor: 'bg-purple-100',
    iconColor: 'text-purple-600'
  }
])

const recentActivities = ref([
  {
    id: 1,
    type: 'user',
    icon: UsersIcon,
    title: 'Nouvel utilisateur inscrit',
    description: 'Marie Dubois a créé un compte',
    time: new Date(Date.now() - 5 * 60 * 1000)
  },
  {
    id: 2,
    type: 'product',
    icon: ShoppingBagIcon,
    title: 'Produit ajouté',
    description: 'iPhone 15 Pro a été publié',
    time: new Date(Date.now() - 15 * 60 * 1000)
  },
  {
    id: 3,
    type: 'payment',
    icon: CurrencyDollarIcon,
    title: 'Paiement reçu',
    description: '2500 FCFA pour un ticket de tombola',
    time: new Date(Date.now() - 30 * 60 * 1000)
  },
  {
    id: 4,
    type: 'lottery',
    icon: GiftIcon,
    title: 'Tombola terminée',
    description: 'MacBook Pro - Gagnant sélectionné',
    time: new Date(Date.now() - 45 * 60 * 1000)
  }
])

const topProducts = ref([
  {
    id: 1,
    title: 'iPhone 15 Pro Max',
    sales: 156,
    growth: 23.5,
    image: '/images/products/placeholder.jpg'
  },
  {
    id: 2,
    title: 'MacBook Pro M3',
    sales: 89,
    growth: 18.2,
    image: '/images/products/placeholder.jpg'
  },
  {
    id: 3,
    title: 'PlayStation 5',
    sales: 67,
    growth: -5.1,
    image: '/images/products/placeholder.jpg'
  },
  {
    id: 4,
    title: 'AirPods Pro 2',
    sales: 45,
    growth: 12.8,
    image: '/images/products/placeholder.jpg'
  }
])

const recentLotteries = ref([
  {
    id: 1,
    lottery_number: 'KMB-2025-000001',
    product: {
      name: 'iPhone 15 Pro Max',
      image: '/images/products/placeholder.jpg'
    },
    status: 'active',
    statusLabel: 'Active',
    sold_tickets: 145,
    total_tickets: 200,
    end_date: '2025-01-15T15:00:00'
  },
  {
    id: 2,
    lottery_number: 'KMB-2025-000002',
    product: {
      name: 'MacBook Pro M3',
      image: '/images/products/placeholder.jpg'
    },
    status: 'completed',
    statusLabel: 'Terminée',
    sold_tickets: 180,
    total_tickets: 180,
    end_date: '2025-01-10T18:00:00'
  },
  {
    id: 3,
    lottery_number: 'KMB-2025-000003',
    product: {
      name: 'PlayStation 5',
      image: '/images/products/placeholder.jpg'
    },
    status: 'pending',
    statusLabel: 'En attente',
    sold_tickets: 67,
    total_tickets: 150,
    end_date: '2025-01-20T20:00:00'
  }
])

// Methods
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatTime = (date) => {
  const now = new Date()
  const diff = Math.floor((now - date) / 1000)
  
  if (diff < 60) return `il y a ${diff}s`
  if (diff < 3600) return `il y a ${Math.floor(diff / 60)}min`
  if (diff < 86400) return `il y a ${Math.floor(diff / 3600)}h`
  return `il y a ${Math.floor(diff / 86400)}j`
}

onMounted(() => {
  // Load dashboard data
  console.log('Dashboard admin chargé')
})
</script>