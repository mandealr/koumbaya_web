<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Gestion des rôles</h1>
        <p class="text-gray-600 mt-1">Gérer les rôles et leurs privilèges associés</p>
      </div>
      <button
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Créer un rôle
      </button>
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

    <!-- Create Role Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Créer un nouveau rôle</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <form @submit.prevent="createRole" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom du rôle *</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Ex: Modérateur"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Description du rôle..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type d'utilisateur *</label>
            <select
              v-model="form.user_type_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Sélectionner un type</option>
              <option v-for="type in userTypes" :key="type.id" :value="type.id">
                {{ type.name }}
              </option>
            </select>
          </div>

          <div class="flex items-center">
            <input
              v-model="form.active"
              type="checkbox"
              id="active"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="active" class="ml-2 block text-sm text-gray-700">Rôle actif</label>
          </div>

          <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ formError }}
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              {{ submitting ? 'Création...' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '@/composables/api'

const { get, post } = useApi()

const roles = ref([])
const userTypes = ref([])
const statistics = ref({})
const loading = ref(false)
const error = ref('')
const showCreateModal = ref(false)
const submitting = ref(false)
const formError = ref('')

const form = ref({
  name: '',
  description: '',
  user_type_id: '',
  active: true
})

const totalUsers = computed(() => {
  return roles.value.reduce((sum, role) => sum + (role.users_count || 0), 0)
})

const fetchRoles = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await get('/admin/roles')

    if (response.success) {
      roles.value = response.data?.roles || []
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

const fetchUserTypes = async () => {
  try {
    const response = await get('/admin/roles/user-types')
    if (response.success) {
      userTypes.value = response.data?.user_types || []
    }
  } catch (err) {
    console.error('Error fetching user types:', err)
  }
}

const fetchStatistics = async () => {
  try {
    const response = await get('/admin/roles/statistics')
    statistics.value = response.stats || {}
  } catch (err) {
    console.error('Error fetching statistics:', err)
  }
}

const openCreateModal = () => {
  showCreateModal.value = true
  if (userTypes.value.length === 0) {
    fetchUserTypes()
  }
}

const closeModal = () => {
  showCreateModal.value = false
  form.value = {
    name: '',
    description: '',
    user_type_id: '',
    active: true
  }
  formError.value = ''
}

const createRole = async () => {
  formError.value = ''
  submitting.value = true

  try {
    const response = await post('/admin/roles', form.value)

    if (response.success) {
      closeModal()
      fetchRoles()
      fetchStatistics()
    }
  } catch (err) {
    formError.value = err.response?.data?.message || 'Erreur lors de la création du rôle'
    console.error('Error creating role:', err)
  } finally {
    submitting.value = false
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
