<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Gestion des rôles</h1>
      <p class="text-gray-600 mt-1">Gérer les rôles et leurs privilèges associés</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Total</div>
        <div class="text-2xl font-bold text-gray-900">{{ statistics.total || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Actifs</div>
        <div class="text-2xl font-bold text-green-600">{{ statistics.active || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Inactifs</div>
        <div class="text-2xl font-bold text-red-600">{{ statistics.inactive || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Utilisateurs</div>
        <div class="text-2xl font-bold text-blue-600">{{ totalUsers || 0 }}</div>
      </div>
    </div>

    <!-- Roles Grid -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <p class="text-red-600">{{ error }}</p>
        <button @click="fetchRoles" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Réessayer
        </button>
      </div>

      <div v-else-if="roles.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun rôle trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">Aucun rôle n'est configuré dans le système.</p>
      </div>

      <div v-else class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="role in roles"
            :key="role.id"
            class="bg-gray-50 rounded-lg p-6 border-2 border-gray-200 hover:border-blue-400 transition-colors"
          >
            <!-- Role Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ formatRoleName(role.name) }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ role.description || 'Aucune description' }}</p>
              </div>
              <span
                :class="role.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                class="px-2 py-1 text-xs font-semibold rounded-full ml-2"
              >
                {{ role.active ? 'Actif' : 'Inactif' }}
              </span>
            </div>

            <!-- Role Details -->
            <div class="space-y-3">
              <div class="flex items-center text-sm">
                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span class="text-gray-600">Type:</span>
                <span class="ml-2 font-medium text-gray-900">{{ role.user_type?.name || 'N/A' }}</span>
              </div>

              <div class="flex items-center text-sm">
                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-gray-600">Utilisateurs:</span>
                <span class="ml-2 font-medium text-gray-900">{{ role.users_count || 0 }}</span>
              </div>

              <div class="flex items-center text-sm">
                <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="text-gray-600">Privilèges:</span>
                <span class="ml-2 font-medium text-gray-900">{{ role.privileges_count || 0 }}</span>
              </div>
            </div>

            <!-- Role Badge -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <span
                :class="getRoleBadgeColor(role.name)"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
              >
                {{ formatRoleName(role.name) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/composables/api'

const roles = ref([])
const statistics = ref({})
const loading = ref(false)
const error = ref('')

const totalUsers = computed(() => {
  return roles.value.reduce((sum, role) => sum + (role.users_count || 0), 0)
})

const fetchRoles = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/admin/roles')

    if (response.data.success) {
      roles.value = response.data.data?.roles || []
    } else {
      roles.value = []
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors du chargement des rôles'
    console.error('Error fetching roles:', err)
  } finally {
    loading.value = false
  }
}

const fetchStatistics = async () => {
  try {
    const response = await api.get('/admin/roles/statistics')
    statistics.value = response.data.stats || {}
  } catch (err) {
    console.error('Error fetching statistics:', err)
  }
}

const formatRoleName = (name) => {
  const nameMap = {
    'superadmin': 'Super Admin',
    'admin': 'Administrateur',
    'agent': 'Agent de Support',
    'business_enterprise': 'Marchand Entreprise',
    'business_individual': 'Marchand Particulier',
    'particulier': 'Client',
    'Super Admin': 'Super Admin',
    'Admin': 'Administrateur',
    'Agent': 'Agent de Support',
    'Business Enterprise': 'Marchand Entreprise',
    'Business Individual': 'Marchand Particulier',
    'Particulier': 'Client'
  }
  return nameMap[name] || name
}

const getRoleBadgeColor = (name) => {
  const lowerName = name.toLowerCase()
  if (lowerName.includes('superadmin') || lowerName.includes('super admin')) {
    return 'bg-purple-100 text-purple-800'
  }
  if (lowerName.includes('admin')) {
    return 'bg-blue-100 text-blue-800'
  }
  if (lowerName.includes('agent')) {
    return 'bg-green-100 text-green-800'
  }
  if (lowerName.includes('business') || lowerName.includes('marchand')) {
    return 'bg-orange-100 text-orange-800'
  }
  if (lowerName.includes('particulier') || lowerName.includes('client')) {
    return 'bg-gray-100 text-gray-800'
  }
  return 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchRoles()
  fetchStatistics()
})
</script>
