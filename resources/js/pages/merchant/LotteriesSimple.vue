<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Mes Tombolas</h1>
        <p class="mt-2 text-gray-600">Gérez vos tombolas et effectuez les tirages</p>
      </div>
    </div>

    <!-- Status Tabs -->
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === tab.key
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          {{ tab.label }}
          <span v-if="tab.count" class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
            {{ tab.count }}
          </span>
        </button>
      </nav>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-lg shadow">
      <div class="p-6">
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4 text-gray-600">Chargement des tombolas...</p>
        </div>

        <div v-else-if="lotteries.length === 0" class="text-center py-12">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune tombola</h3>
          <p class="text-gray-600">Vous n'avez pas encore créé de tombolas.</p>
        </div>

        <div v-else>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="lottery in lotteries"
              :key="lottery.id"
              class="border rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <h4 class="font-semibold text-gray-900">{{ lottery.title }}</h4>
              <p class="text-sm text-gray-600 mt-1">{{ lottery.description }}</p>
              <div class="mt-4 flex justify-between items-center">
                <span class="text-sm font-medium text-blue-600">{{ lottery.status }}</span>
                <router-link 
                  :to="{ name: 'merchant.lottery.view', params: { id: lottery.id } }"
                  class="text-sm text-blue-600 hover:text-blue-700"
                >
                  Voir détails
                </router-link>
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

// Data
const loading = ref(false)
const activeTab = ref('all')
const lotteries = ref([
  {
    id: 1,
    title: 'iPhone 15 Pro',
    description: 'Tentez de gagner le dernier iPhone',
    status: 'Active'
  },
  {
    id: 2,
    title: 'MacBook Pro M3',
    description: 'Un MacBook Pro pour les créatifs',
    status: 'En cours'
  }
])

const tabs = ref([
  { key: 'all', label: 'Toutes', count: 2 },
  { key: 'active', label: 'Actives', count: 1 },
  { key: 'completed', label: 'Terminées', count: 1 }
])

onMounted(() => {
  console.log('Lotteries Simple page loaded')
})
</script>