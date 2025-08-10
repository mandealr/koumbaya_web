<template>
  <div class="px-6">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Analytiques</h1>
      <p class="mt-2 text-gray-600">Analyse de vos performances de vente</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <StatsCard
        title="Revenus Totaux"
        :value="2850000"
        format="currency"
        :change="12.5"
        color="green"
        :icon="CurrencyDollarIcon"
        :actions="[{label: 'Détails', primary: true}]"
      />
      
      <StatsCard
        title="Ventes ce mois"
        :value="156"
        :change="8.2"
        color="blue"
        :icon="ShoppingBagIcon"
        :show-progress="true"
        :progress-value="156"
        :progress-target="200"
      />
      
      <StatsCard
        title="Produits Actifs"
        :value="24"
        :change="-2.1"
        color="purple"
        :icon="GiftIcon"
      />
      
      <StatsCard
        title="Taux Conversion"
        :value="24.8"
        format="percentage"
        :change="5.4"
        color="orange"
        :icon="ChartBarIcon"
      />
    </div>

    <!-- Charts Placeholder -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="koumbaya-card">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">Évolution des Revenus</h3>
        </div>
        <div class="koumbaya-card-body">
          <div class="h-64 bg-gradient-to-br from-green-50 to-blue-50 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <ChartBarIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-500">Graphique des revenus</p>
              <p class="text-sm text-gray-400">Chart.js sera intégré prochainement</p>
            </div>
          </div>
        </div>
      </div>

      <div class="koumbaya-card">
        <div class="koumbaya-card-header">
          <h3 class="koumbaya-heading-4">Répartition par Catégorie</h3>
        </div>
        <div class="koumbaya-card-body">
          <div class="h-64 bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <ChartPieIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-500">Graphique en camembert</p>
              <p class="text-sm text-gray-400">Chart.js sera intégré prochainement</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Performance Table -->
    <div class="koumbaya-card">
      <div class="koumbaya-card-header">
        <h3 class="koumbaya-heading-4">Top Produits</h3>
      </div>
      <div class="koumbaya-card-body">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produit
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ventes
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Revenus
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tendance
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in topProducts" :key="product.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <img :src="product.image" :alt="product.name" class="h-10 w-10 rounded-lg object-cover">
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                      <div class="text-sm text-gray-500">{{ product.category }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ product.sales }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatPrice(product.revenue) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <ArrowTrendingUpIcon v-if="product.trend > 0" class="w-4 h-4 text-green-500 mr-1" />
                    <ArrowTrendingDownIcon v-else class="w-4 h-4 text-red-500 mr-1" />
                    <span :class="product.trend > 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                      {{ Math.abs(product.trend) }}%
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import StatsCard from '@/components/common/StatsCard.vue'
import {
  CurrencyDollarIcon,
  GiftIcon,
  ChartBarIcon,
  ChartPieIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon
} from '@heroicons/vue/24/outline'
import { ShoppingBagIcon } from '@heroicons/vue/24/solid'

// Données mockées
const topProducts = ref([
  {
    id: 1,
    name: 'iPhone 15 Pro',
    category: 'Électronique',
    sales: 45,
    revenue: 850000,
    trend: 12.5,
    image: '/images/products/placeholder.jpg'
  },
  {
    id: 2,
    name: 'MacBook Pro M3',
    category: 'Informatique',
    sales: 28,
    revenue: 1200000,
    trend: 8.2,
    image: '/images/products/placeholder.jpg'
  },
  {
    id: 3,
    name: 'PlayStation 5',
    category: 'Gaming',
    sales: 62,
    revenue: 750000,
    trend: -2.3,
    image: '/images/products/placeholder.jpg'
  },
  {
    id: 4,
    name: 'Samsung Galaxy S24',
    category: 'Électronique',
    sales: 21,
    revenue: 450000,
    trend: 15.8,
    image: '/images/products/placeholder.jpg'
  }
])

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price || 0).replace('XAF', 'FCFA')
}
</script>