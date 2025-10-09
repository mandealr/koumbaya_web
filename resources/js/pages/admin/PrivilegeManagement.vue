<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Gestion des privilèges</h1>
      <p class="text-gray-600 mt-1">Gérer les privilèges du système</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Total</div>
        <div class="text-2xl font-bold text-gray-900">{{ statistics.total || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Type Admin</div>
        <div class="text-2xl font-bold text-blue-600">{{ statistics.admin_type || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Type Customer</div>
        <div class="text-2xl font-bold text-green-600">{{ statistics.customer_type || 0 }}</div>
      </div>
    </div>

    <!-- Filter by User Type -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Filtrer par type d'utilisateur</label>
      <select
        v-model="selectedUserType"
        class="w-full md:w-64 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
        <option value="">Tous les types</option>
        <option value="admin">Admin</option>
        <option value="customer">Customer</option>
      </select>
    </div>

    <!-- Privileges Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <p class="text-red-600">{{ error }}</p>
        <button @click="fetchPrivileges" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
          Réessayer
        </button>
      </div>

      <div v-else-if="filteredPrivileges.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun privilège trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">Aucun privilège ne correspond aux filtres sélectionnés.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type utilisateur</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôles</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="privilege in filteredPrivileges" :key="privilege.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ privilege.name }}</div>
                    <div class="text-xs text-gray-500">ID: {{ privilege.id }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ privilege.description || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="privilege.user_type?.code === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                >
                  {{ privilege.user_type?.name || 'N/A' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="role in privilege.roles"
                    :key="role.id"
                    class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                  >
                    {{ formatRoleName(role.name) }}
                  </span>
                  <span v-if="!privilege.roles || privilege.roles.length === 0" class="text-xs text-gray-400">
                    Aucun rôle
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="getCategoryBadgeColor(privilege.name)"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                >
                  {{ getCategory(privilege.name) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/composables/api'

const privileges = ref([])
const statistics = ref({})
const loading = ref(false)
const error = ref('')
const selectedUserType = ref('')

const filteredPrivileges = computed(() => {
  if (!selectedUserType.value) {
    return privileges.value
  }
  return privileges.value.filter(p => p.user_type?.code === selectedUserType.value)
})

const fetchPrivileges = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await api.get('/admin/privileges')

    if (response.data.success) {
      privileges.value = response.data.data?.privileges || []
    } else {
      privileges.value = []
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors du chargement des privilèges'
    console.error('Error fetching privileges:', err)
  } finally {
    loading.value = false
  }
}

const fetchStatistics = async () => {
  try {
    const response = await api.get('/admin/privileges/statistics')
    statistics.value = response.data.stats || {}
  } catch (err) {
    console.error('Error fetching statistics:', err)
  }
}

const formatRoleName = (name) => {
  const nameMap = {
    'superadmin': 'Super Admin',
    'admin': 'Admin',
    'agent': 'Agent',
    'business_enterprise': 'Business Ent.',
    'business_individual': 'Business Ind.',
    'particulier': 'Client',
    'Super Admin': 'Super Admin',
    'Admin': 'Admin',
    'Agent': 'Agent',
    'Business Enterprise': 'Business Ent.',
    'Business Individual': 'Business Ind.',
    'Particulier': 'Client'
  }
  return nameMap[name] || name
}

const getCategory = (privilegeName) => {
  if (privilegeName.startsWith('users.')) return 'Utilisateurs'
  if (privilegeName.startsWith('products.')) return 'Articles'
  if (privilegeName.startsWith('lotteries.')) return 'Tirages'
  if (privilegeName.startsWith('orders.')) return 'Commandes'
  if (privilegeName.startsWith('payments.')) return 'Paiements'
  if (privilegeName.startsWith('refunds.')) return 'Remboursements'
  if (privilegeName.startsWith('analytics.')) return 'Statistiques'
  if (privilegeName.startsWith('reports.')) return 'Rapports'
  if (privilegeName.startsWith('system.')) return 'Système'
  if (privilegeName.startsWith('roles.')) return 'Rôles'
  if (privilegeName.startsWith('privileges.')) return 'Privilèges'
  if (privilegeName.startsWith('notifications.')) return 'Notifications'
  if (privilegeName.startsWith('support.')) return 'Support'
  if (privilegeName.startsWith('moderation.')) return 'Modération'
  if (privilegeName.startsWith('profile.')) return 'Profil'
  if (privilegeName.startsWith('finances.')) return 'Finances'
  return 'Autre'
}

const getCategoryBadgeColor = (privilegeName) => {
  const category = getCategory(privilegeName)
  const colors = {
    'Utilisateurs': 'bg-purple-100 text-purple-800',
    'Articles': 'bg-blue-100 text-blue-800',
    'Tirages': 'bg-yellow-100 text-yellow-800',
    'Commandes': 'bg-green-100 text-green-800',
    'Paiements': 'bg-emerald-100 text-emerald-800',
    'Remboursements': 'bg-red-100 text-red-800',
    'Statistiques': 'bg-indigo-100 text-indigo-800',
    'Rapports': 'bg-pink-100 text-pink-800',
    'Système': 'bg-orange-100 text-orange-800',
    'Rôles': 'bg-cyan-100 text-cyan-800',
    'Privilèges': 'bg-teal-100 text-teal-800',
    'Notifications': 'bg-lime-100 text-lime-800',
    'Support': 'bg-amber-100 text-amber-800',
    'Modération': 'bg-rose-100 text-rose-800',
    'Profil': 'bg-violet-100 text-violet-800',
    'Finances': 'bg-fuchsia-100 text-fuchsia-800',
    'Autre': 'bg-gray-100 text-gray-800'
  }
  return colors[category] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  fetchPrivileges()
  fetchStatistics()
})
</script>
