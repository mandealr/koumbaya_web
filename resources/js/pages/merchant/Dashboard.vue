<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Tableau de Bord Vendeur</h1>
      <p class="mt-2 text-gray-600">Suivez vos ventes, gérez vos produits et analysez vos performances</p>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <router-link
        to="/merchant/products/create"
        class="bg-gradient-to-r from-[#0099cc] to-[#0088bb] text-white p-6 rounded-xl hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
      >
        <div class="flex items-center">
          <PlusIcon class="w-8 h-8 mr-4" />
          <div>
            <h3 class="text-lg font-semibold">Nouveau Produit</h3>
            <p class="text-blue-100 text-sm">Publier un produit</p>
          </div>
        </div>
      </router-link>

      <router-link
        to="/merchant/orders"
        class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-xl hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
      >
        <div class="flex items-center">
          <ShoppingBagIcon class="w-8 h-8 mr-4" />
          <div>
            <h3 class="text-lg font-semibold">Commandes</h3>
            <p class="text-blue-100 text-sm">Suivre les ventes</p>
          </div>
        </div>
      </router-link>

      <router-link
        to="/merchant/analytics"
        class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-xl hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl"
      >
        <div class="flex items-center">
          <ChartBarIcon class="w-8 h-8 mr-4" />
          <div>
            <h3 class="text-lg font-semibold">Analytiques</h3>
            <p class="text-purple-100 text-sm">Voir les stats</p>
          </div>
        </div>
      </router-link>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-200"
      >
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-600">{{ stat.label }}</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stat.value }}</p>
          </div>
          <div :class="[
            'p-4 rounded-xl',
            stat.color
          ]">
            <component :is="stat.icon" class="w-8 h-8 text-white" />
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center text-sm">
            <span :class="[
              'font-medium flex items-center px-2 py-1 rounded-full text-xs',
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

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Sales Chart -->
      <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-lg border border-gray-100">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">Évolution des Ventes</h3>
          <div class="flex space-x-2">
            <button
              v-for="period in ['7j', '30j', '90j']"
              :key="period"
              @click="selectedPeriod = period"
              :class="[
                'px-3 py-1 rounded-lg text-sm font-medium transition-all',
                selectedPeriod === period
                  ? 'bg-[#0099cc] text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              ]"
            >
              {{ period }}
            </button>
          </div>
        </div>

        <!-- Chart Placeholder -->
        <div class="h-64 bg-gradient-to-br from-[#0099cc]/10 to-cyan-100 rounded-lg flex items-center justify-center">
          <div class="text-center">
            <ChartBarIcon class="w-16 h-16 text-[#0099cc] mx-auto mb-4" />
            <p class="text-gray-600 font-medium">Graphique des ventes</p>
            <p class="text-gray-400 text-sm">Données des derniers {{ selectedPeriod }}</p>
          </div>
        </div>

        <!-- Sales Summary -->
        <div class="mt-6 grid grid-cols-3 gap-4">
          <div class="text-center">
            <p class="text-2xl font-bold text-[#0099cc]">{{ salesSummary.totalSales.toLocaleString() }}</p>
            <p class="text-sm text-gray-600">Ventes totales (FCFA)</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold text-blue-600">{{ salesSummary.totalOrders }}</p>
            <p class="text-sm text-gray-600">Commandes</p>
          </div>
          <div class="text-center">
            <p class="text-2xl font-bold text-purple-600">{{ salesSummary.avgOrder.toLocaleString() }}</p>
            <p class="text-sm text-gray-600">Panier moyen (FCFA)</p>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="space-y-6">
        <!-- Recent Orders -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Commandes récentes</h3>
            <router-link to="/merchant/orders" class="text-[#0099cc] hover:text-[#0088bb] text-sm font-medium">
              Voir tout →
            </router-link>
          </div>

          <div class="space-y-3">
            <div
              v-for="order in recentOrders"
              :key="order.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-[#0099cc]/10 rounded-full flex items-center justify-center">
                  <ShoppingBagIcon class="w-5 h-5 text-[#0099cc]" />
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ order.product_name }}</p>
                  <p class="text-sm text-gray-500">{{ order.customer_name }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="font-semibold text-gray-900">{{ order.amount.toLocaleString() }} FCFA</p>
                <span :class="[
                  'inline-block px-2 py-1 text-xs font-medium rounded-full',
                  order.status === 'completed' ? 'bg-blue-100 text-blue-800' :
                  order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-gray-100 text-gray-800'
                ]">
                  {{ getOrderStatusLabel(order.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Produits populaires</h3>
            <router-link to="/merchant/products" class="text-[#0099cc] hover:text-[#0088bb] text-sm font-medium">
              Gérer →
            </router-link>
          </div>

          <div class="space-y-3">
            <div
              v-for="(product, index) in topProducts"
              :key="product.id"
              class="flex items-center space-x-3"
            >
              <div class="flex-shrink-0 w-6 h-6 bg-[#0099cc] text-white text-xs font-bold rounded-full flex items-center justify-center">
                {{ index + 1 }}
              </div>
              <img :src="product.image" :alt="product.name" class="w-12 h-12 rounded-lg object-cover" />
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 truncate">{{ product.name }}</p>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                  <span>{{ product.sales }} ventes</span>
                  <span :class="[
                    'font-medium',
                    product.growth >= 0 ? 'text-blue-600' : 'text-red-600'
                  ]">
                    {{ product.growth >= 0 ? '+' : '' }}{{ product.growth }}%
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
import { ref, reactive, computed, onMounted } from 'vue'
import {
  PlusIcon,
  ShoppingBagIcon,
  ChartBarIcon,
  CurrencyDollarIcon,
  UsersIcon,
  GiftIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  TruckIcon
} from '@heroicons/vue/24/outline'

// Reactive data
const selectedPeriod = ref('30j')
const loading = ref(false)

const stats = ref([
  {
    label: 'Revenus du mois',
    value: '125,400',
    change: 12.5,
    icon: CurrencyDollarIcon,
    color: 'bg-[#0099cc]'
  },
  {
    label: 'Commandes',
    value: '89',
    change: 8.2,
    icon: ShoppingBagIcon,
    color: 'bg-blue-500'
  },
  {
    label: 'Produits actifs',
    value: '24',
    change: -2.1,
    icon: GiftIcon,
    color: 'bg-yellow-500'
  },
  {
    label: 'Clients',
    value: '156',
    change: 15.3,
    icon: UsersIcon,
    color: 'bg-purple-500'
  }
])

const salesSummary = ref({
  totalSales: 1254000,
  totalOrders: 89,
  avgOrder: 14090
})

const recentOrders = ref([
  {
    id: 1,
    product_name: 'iPhone 15 Pro',
    customer_name: 'Jean Dupont',
    amount: 5000,
    status: 'completed',
    created_at: new Date()
  },
  {
    id: 2,
    product_name: 'MacBook Pro M3',
    customer_name: 'Marie Martin',
    amount: 15000,
    status: 'pending',
    created_at: new Date()
  },
  {
    id: 3,
    product_name: 'AirPods Pro',
    customer_name: 'Paul Durant',
    amount: 2500,
    status: 'completed',
    created_at: new Date()
  }
])

const topProducts = ref([
  {
    id: 1,
    name: 'iPhone 15 Pro Max',
    sales: 45,
    growth: 23.5,
    image: '/images/products/placeholder.jpg'
  },
  {
    id: 2,
    name: 'MacBook Pro M3',
    sales: 28,
    growth: 18.2,
    image: '/images/products/placeholder.jpg'
  },
  {
    id: 3,
    name: 'AirPods Pro 2',
    sales: 21,
    growth: -5.1,
    image: '/images/products/placeholder.jpg'
  }
])

// Methods
const getOrderStatusLabel = (status) => {
  const labels = {
    'completed': 'Terminé',
    'pending': 'En attente',
    'processing': 'En cours',
    'cancelled': 'Annulé'
  }
  return labels[status] || status
}

onMounted(() => {
  // Load dashboard data
  console.log('Merchant dashboard loaded')
})
</script>
