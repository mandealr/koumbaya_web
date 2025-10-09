<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Gestion des types d'utilisateurs</h1>
      <p class="text-gray-600 mt-1">Gérer les types d'utilisateurs (admin, customer)</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Total Types</div>
        <div class="text-2xl font-bold text-gray-900">{{ statistics.total || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Actifs</div>
        <div class="text-2xl font-bold text-green-600">{{ statistics.active || 0 }}</div>
      </div>
    </div>

    <!-- User Types Grid -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <p class="text-red-600">{{ error }}</p>
        <button @click="fetchUserTypes" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Réessayer
        </button>
      </div>

      <div v-else-if="userTypes.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun type d'utilisateur trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">Aucun type d'utilisateur n'est configuré dans le système.</p>
      </div>

      <div v-else class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="userType in userTypes"
            :key="userType.id"
            class="bg-gradient-to-br from-white to-gray-50 rounded-xl p-8 border-2 border-gray-200 hover:border-blue-400 hover:shadow-lg transition-all"
          >
            <!-- User Type Header -->
            <div class="flex items-start justify-between mb-6">
              <div class="flex items-center">
                <div
                  :class="userType.code === 'admin' ? 'bg-blue-500' : 'bg-green-500'"
                  class="h-12 w-12 rounded-lg flex items-center justify-center mr-4"
                >
                  <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="userType.code === 'admin'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-2xl font-bold text-gray-900">{{ userType.name }}</h3>
                  <p class="text-sm text-gray-500 uppercase tracking-wide">{{ userType.code }}</p>
                </div>
              </div>
              <span
                :class="userType.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                class="px-3 py-1 text-xs font-semibold rounded-full"
              >
                {{ userType.active ? 'Actif' : 'Inactif' }}
              </span>
            </div>

            <!-- Description -->
            <p class="text-gray-600 mb-6 text-sm leading-relaxed">
              {{ userType.description || 'Aucune description disponible' }}
            </p>

            <!-- Statistics -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white rounded-lg p-4 border border-gray-100">
                <div class="flex items-center mb-2">
                  <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                  </svg>
                  <span class="text-xs text-gray-500 font-medium">Rôles</span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ userType.roles_count || 0 }}</div>
              </div>

              <div class="bg-white rounded-lg p-4 border border-gray-100">
                <div class="flex items-center mb-2">
                  <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  <span class="text-xs text-gray-500 font-medium">Privilèges</span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ userType.privileges_count || 0 }}</div>
              </div>
            </div>

            <!-- Associated Roles -->
            <div v-if="userType.roles && userType.roles.length > 0" class="mt-6 pt-6 border-t border-gray-200">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">Rôles associés</h4>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="role in userType.roles"
                  :key="role.id"
                  :class="getRoleBadgeColor(role.name)"
                  class="px-3 py-1 text-xs font-medium rounded-full"
                >
                  {{ formatRoleName(role.name) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Card -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
      <div class="flex">
        <svg class="h-6 w-6 text-blue-600 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-blue-900 mb-1">Architecture à deux niveaux</h3>
          <p class="text-sm text-blue-800">
            Le système utilise une architecture UserType → Role → Privilege. Les types d'utilisateurs regroupent les rôles,
            et les privilèges sont attachés aux rôles. Cette structure permet une gestion fine des permissions.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/composables/api'

const userTypes = ref([])
const statistics = ref({})
const loading = ref(false)
const error = ref('')

const fetchUserTypes = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/admin/user-types')

    if (response.data.success) {
      userTypes.value = response.data.data?.user_types || []
    } else {
      userTypes.value = []
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors du chargement des types d\'utilisateurs'
    console.error('Error fetching user types:', err)
  } finally {
    loading.value = false
  }
}

const fetchStatistics = async () => {
  try {
    const response = await api.get('/admin/user-types/statistics')
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
  fetchUserTypes()
  fetchStatistics()
})
</script>
