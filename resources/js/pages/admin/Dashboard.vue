<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Tableau de bord</h1>
      <p class="mt-2 text-gray-600">Vue d'ensemble des activités de la plateforme Koumbaya</p>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Loading skeleton -->
      <div v-if="loading" v-for="n in 4" :key="n" class="admin-card animate-pulse">
        <div class="flex items-center">
          <div class="p-3 rounded-lg bg-gray-200"></div>
          <div class="ml-4 flex-1">
            <div class="h-4 bg-gray-200 rounded w-20 mb-2"></div>
            <div class="h-6 bg-gray-200 rounded w-16"></div>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="h-4 bg-gray-200 rounded w-24"></div>
        </div>
      </div>

      <!-- Stats cards -->
      <div 
        v-else
        v-for="stat in dashboardStats" 
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
              stat.change >= 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
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
                      <ProductImage 
                        :src="lottery.product.image" 
                        :alt="lottery.product.name"
                        container-class="h-10 w-10 rounded-lg"
                        image-class="h-full w-full object-cover rounded-lg"
                        fallback-class="h-full w-full rounded-lg"
                      />
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
          
          <!-- Loading skeleton -->
          <div v-if="loading" class="space-y-4">
            <div v-for="n in 4" :key="n" class="flex items-start space-x-3 p-3 animate-pulse">
              <div class="w-8 h-8 bg-gray-200 rounded-full flex-shrink-0"></div>
              <div class="flex-1 min-w-0 space-y-2">
                <div class="h-4 bg-gray-200 rounded w-32"></div>
                <div class="h-3 bg-gray-200 rounded w-48"></div>
                <div class="h-3 bg-gray-200 rounded w-16"></div>
              </div>
            </div>
          </div>
          
          <!-- Activities list -->
          <div v-else-if="activityIconComponents.length > 0" class="space-y-4">
            <div 
              v-for="activity in activityIconComponents" 
              :key="activity.id"
              class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div :class="[
                'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                activity.type === 'user' ? 'bg-[#0099cc]/10 text-[#0099cc]' :
                activity.type === 'product' ? 'bg-blue-100 text-blue-600' :
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
          
          <!-- Empty state -->
          <div v-else class="text-center py-6 text-gray-500">
            <InformationCircleIcon class="w-8 h-8 mx-auto mb-2 text-gray-400" />
            <p class="text-sm">Aucune activité récente</p>
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
              <ProductImage 
                :src="product.image" 
                :alt="product.title"
                container-class="w-10 h-10 rounded-lg flex-shrink-0"
                image-class="w-full h-full object-cover rounded-lg"
                fallback-class="w-full h-full rounded-lg"
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ product.title }}</p>
                <div class="flex items-center space-x-2 mt-1">
                  <p class="text-xs text-gray-500">{{ product.sales }} tickets</p>
                  <span :class="[
                    'text-xs font-medium',
                    product.growth > 0 ? 'text-blue-600' : 'text-red-600'
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
import { ref, onMounted, computed } from 'vue'
import ProductImage from '@/components/common/ProductImage.vue'
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
  DocumentTextIcon,
  BanknotesIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'
import { useAdminStats } from '@/composables/useAdminStats'

// Composables
const {
  stats,
  recentLotteries,
  recentActivities,
  topProducts,
  loading,
  error,
  loadDashboardData
} = useAdminStats()

// Icon mapping for dynamic components
const iconComponents = {
  UsersIcon,
  ShoppingBagIcon,
  GiftIcon,
  CurrencyDollarIcon,
  BanknotesIcon,
  InformationCircleIcon
}

// Computed stats with resolved icons
const dashboardStats = computed(() => {
  return stats.value.map(stat => ({
    ...stat,
    icon: iconComponents[stat.icon] || InformationCircleIcon
  }))
})

// Activity icons mapping
const activityIconComponents = computed(() => {
  return recentActivities.value.map(activity => ({
    ...activity,
    icon: iconComponents[activity.icon] || InformationCircleIcon
  }))
})

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

// Refresh data
const refreshDashboard = async () => {
  await loadDashboardData()
}

onMounted(async () => {
  await loadDashboardData()
})
</script>