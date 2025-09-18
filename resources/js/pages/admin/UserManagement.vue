<template>
  <div class="px-6">
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
          <p class="mt-2 text-gray-600">Gérez tous les utilisateurs de la plateforme</p>
        </div>
        <div class="flex gap-3">
          <button
            v-if="canCreateUsers"
            @click="showCreateModal = true"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            {{ isSuperAdmin ? 'Créer un Admin' : 'Créer un Utilisateur' }}
          </button>
          <button
            @click="loadUsers"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
          >
            <ArrowPathIcon class="w-5 h-5 inline mr-2" :class="loading ? 'animate-spin' : ''" />
            Recharger
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nom, email..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
          <select
            v-model="filters.role"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les rôles</option>
            <option v-for="role in availableRoles" :key="role.value" :value="role.value">
              {{ role.label }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Tous les statuts</option>
            <option value="active">Actif</option>
            <option value="inactive">Inactif</option>
            <option value="verified">Vérifié</option>
            <option value="unverified">Non vérifié</option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="w-full px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
          >
            Réinitialiser
          </button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">
            Utilisateurs ({{ filteredUsers.length }})
          </h3>
          <div class="flex space-x-2">
            <button class="p-2 text-gray-400 hover:text-gray-600">
              <ArrowPathIcon class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>

      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Utilisateur
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rôle
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Statut
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Inscription
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-gray-700">
                      {{ user.first_name[0] }}{{ user.last_name[0] }}
                    </span>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ user.first_name }} {{ user.last_name }}
                    </div>
                    <div v-if="user.phone" class="text-sm text-gray-500">
                      {{ user.phone }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ user.email }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  getRoleClass(user.primary_role)
                ]">
                  {{ getRoleLabel(user.primary_role) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-2">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    user.is_active ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ user.is_active ? 'Actif' : 'Inactif' }}
                  </span>
                  <span v-if="user.verified_at" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    Vérifié
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    v-if="canEditUser(user)"
                    @click="editUser(user)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Modifier
                  </button>
                  <span v-else class="text-gray-400 cursor-not-allowed">Modifier</span>
                  
                  <button
                    v-if="canToggleUserStatus(user)"
                    @click="toggleUserStatus(user)"
                    :class="user.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
                  >
                    {{ user.is_active ? 'Désactiver' : 'Activer' }}
                  </button>
                  <span v-else class="text-gray-400 cursor-not-allowed">
                    {{ user.is_active ? 'Désactiver' : 'Activer' }}
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-6 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Affichage de {{ (currentPage - 1) * itemsPerPage + 1 }} à
            {{ Math.min(currentPage * itemsPerPage, totalUsers) }} sur
            {{ totalUsers }} résultats
          </div>
          <div class="flex space-x-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded disabled:opacity-50"
            >
              Précédent
            </button>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 text-sm bg-gray-100 text-gray-600 rounded disabled:opacity-50"
            >
              Suivant
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de création d'admin -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg w-full max-w-2xl shadow-xl">
        <div class="flex items-center justify-between p-6 border-b">
          <h2 class="text-xl font-semibold text-gray-900">{{ isSuperAdmin ? 'Créer un Administrateur' : 'Créer un Utilisateur' }}</h2>
          <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <form @submit.prevent="createAdmin" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
              <input
                v-model="newAdmin.first_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Jean"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
              <input
                v-model="newAdmin.last_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Dupont"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input
              v-model="newAdmin.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="admin@koumbaya.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone *</label>
            <PhoneInput
              v-model="newAdmin.phone"
              placeholder="Numéro de téléphone"
              @phone-change="onPhoneChange"
              class="w-full"
            />
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="text-sm font-medium text-blue-800">Mot de passe automatique</p>
                <p class="text-xs text-blue-600">Un mot de passe temporaire sera généré et envoyé par email à l'utilisateur pour qu'il puisse l'initialiser.</p>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rôle *</label>
            <select
              v-model="newAdmin.role"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Sélectionner un rôle</option>
              <option v-for="role in adminRoles" :key="role.value" :value="role.value">
                {{ role.label }}
              </option>
            </select>
            <p class="mt-1 text-sm text-gray-500">{{ getSelectedRoleDescription() }}</p>
          </div>

          <div>
            <label class="flex items-center">
              <input
                v-model="newAdmin.is_active"
                type="checkbox"
                class="mr-2 rounded text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Activer immédiatement le compte</span>
            </label>
          </div>

          <!-- Messages d'erreur -->
          <div v-if="createError" class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-sm text-red-800">{{ createError }}</p>
          </div>
        </form>

        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg">
          <button
            @click="closeCreateModal"
            type="button"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
          >
            Annuler
          </button>
          <button
            @click="createAdmin"
            :disabled="creatingAdmin"
            type="button"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
          >
            <span v-if="!creatingAdmin">{{ isSuperAdmin ? 'Créer l\'administrateur' : 'Créer l\'utilisateur' }}</span>
            <span v-else class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></div>
              Création...
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de modification d'utilisateur -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg w-full max-w-2xl shadow-xl">
        <div class="flex items-center justify-between p-6 border-b">
          <h2 class="text-xl font-semibold text-gray-900">Modifier l'utilisateur</h2>
          <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <form @submit.prevent="updateUser" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Prénom *</label>
              <input
                v-model="editUserForm.first_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Jean"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
              <input
                v-model="editUserForm.last_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Dupont"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input
              v-model="editUserForm.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="user@koumbaya.com"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone *</label>
            <PhoneInput
              v-model="editUserForm.phone"
              placeholder="Numéro de téléphone"
              @phone-change="onEditPhoneChange"
              class="w-full"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rôle *</label>
            <select
              v-model="editUserForm.role"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Sélectionner un rôle</option>
              <option v-for="role in adminRoles" :key="role.value" :value="role.value">
                {{ role.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="flex items-center">
              <input
                v-model="editUserForm.is_active"
                type="checkbox"
                class="mr-2 rounded text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">Compte actif</span>
            </label>
          </div>

          <!-- Messages d'erreur -->
          <div v-if="editError" class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-sm text-red-800">{{ editError }}</p>
          </div>
        </form>

        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg">
          <button
            @click="closeEditModal"
            type="button"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
          >
            Annuler
          </button>
          <button
            @click="updateUser"
            :disabled="updatingUser"
            type="button"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
          >
            <span v-if="!updatingUser">Modifier l'utilisateur</span>
            <span v-else class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></div>
              Modification...
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { PlusIcon, ArrowPathIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { useApi } from '@/composables/api'
import { useAuthStore } from '@/stores/auth'
import PhoneInput from '@/components/PhoneInput.vue'

const { get, post } = useApi()
const authStore = useAuthStore()

// Vérifier si l'utilisateur est SuperAdmin
const isSuperAdmin = computed(() => {
  return authStore.hasRole('Super Admin')
})

// Vérifier si l'utilisateur peut créer d'autres utilisateurs (Admin ou Super Admin)
const canCreateUsers = computed(() => {
  return authStore.hasRole('Super Admin') || authStore.hasRole('Admin')
})

// Vérifier si l'utilisateur peut être modifié
const canEditUser = (user) => {
  const currentUser = authStore.user
  if (!currentUser) return false
  
  // Super Admin peut tout modifier
  if (authStore.hasRole('Super Admin')) return true
  
  // Admin régulier ne peut pas modifier Super Admin ou autres Admin
  if (authStore.hasRole('Admin')) {
    const userRole = user.primary_role
    return !['Super Admin', 'Admin'].includes(userRole)
  }
  
  return false
}

// Vérifier si le statut de l'utilisateur peut être modifié
const canToggleUserStatus = (user) => {
  const currentUser = authStore.user
  if (!currentUser) return false
  
  // Ne peut pas modifier son propre statut
  if (user.id === currentUser.id) return false
  
  // Super Admin peut tout modifier
  if (authStore.hasRole('Super Admin')) return true
  
  // Admin régulier ne peut pas modifier Super Admin ou autres Admin
  if (authStore.hasRole('Admin')) {
    const userRole = user.primary_role
    return !['Super Admin', 'Admin'].includes(userRole)
  }
  
  return false
}

const users = ref([])
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const currentPage = ref(1)
const itemsPerPage = ref(20)
const totalUsers = ref(0)
const availableRoles = ref([])
const adminRoles = ref([])
const creatingAdmin = ref(false)
const updatingUser = ref(false)
const createError = ref('')
const editError = ref('')
const selectedUser = ref(null)

const filters = reactive({
  search: '',
  role: '',
  status: ''
})

const newAdmin = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  role: '',
  is_active: true
})

const editUserForm = reactive({
  id: null,
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  role: '',
  is_active: true
})

// Watch for filter changes and reload
watch(
  () => filters,
  () => {
    currentPage.value = 1
    loadUsers()
  },
  { deep: true }
)

// No need for client-side filtering, API handles it
const filteredUsers = computed(() => users.value)

// Users are already paginated from API
const paginatedUsers = computed(() => users.value)

const totalPages = computed(() => {
  return Math.ceil(totalUsers.value / itemsPerPage.value)
})

const loadUsers = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', currentPage.value)
    params.append('per_page', itemsPerPage.value)

    if (filters.search) params.append('search', filters.search)
    if (filters.role) params.append('role', filters.role)
    if (filters.status) params.append('status', filters.status)

    const response = await get(`/admin/users?${params.toString()}`)

    if (response && response.data) {
      users.value = response.data.users || []
      totalUsers.value = response.data.pagination?.total || 0
      availableRoles.value = response.data.available_roles || []
    }
  } catch (error) {
    console.error('Erreur lors du chargement des utilisateurs:', error)
    users.value = []
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.search = ''
  filters.role = ''
  filters.status = ''
  currentPage.value = 1
}

const editUser = (user) => {
  selectedUser.value = user
  
  // Pré-remplir le formulaire avec les données de l'utilisateur
  editUserForm.id = user.id
  editUserForm.first_name = user.first_name
  editUserForm.last_name = user.last_name
  editUserForm.email = user.email
  editUserForm.phone = user.phone
  editUserForm.role = user.primary_role || (user.roles && user.roles.length > 0 ? user.roles[0] : '')
  editUserForm.is_active = user.is_active
  
  // Effacer les erreurs précédentes
  editError.value = ''
  
  // Charger les rôles si pas encore fait
  if (canCreateUsers.value && adminRoles.value.length === 0) {
    loadAdminRoles()
  }
  
  // Afficher le modal
  showEditModal.value = true
}

const toggleUserStatus = async (user) => {
  try {
    const response = await post(`/admin/users/${user.id}/toggle-status`)
    if (response && response.success) {
      // Reload users to get updated data
      await loadUsers()
      if (window.$toast) {
        window.$toast.success(response.message || 'Statut modifié', '✓ Succès')
      }
    }
  } catch (error) {
    console.error('Erreur lors de la modification du statut:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la modification du statut', '✘ Erreur')
    }
  }
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(new Date(date))
}

const getRoleLabel = (role) => {
  const roleMap = {
    'Super Admin': 'Super Admin',
    'Admin': 'Administrateur',
    'Agent': 'Agent de Support',
    'Agent Back Office': 'Agent Back Office',
    'Business Enterprise': 'Marchand Entreprise',
    'Business Individual': 'Marchand Particulier',
    'Business': 'Marchand',
    'Particulier': 'Client'
  }
  return roleMap[role] || role
}

const getRoleClass = (role) => {
  const classMap = {
    'Super Admin': 'bg-purple-100 text-purple-800',
    'Admin': 'bg-red-100 text-red-800',
    'Agent': 'bg-yellow-100 text-yellow-800',
    'Agent Back Office': 'bg-orange-100 text-orange-800',
    'Business Enterprise': 'bg-blue-100 text-blue-800',
    'Business Individual': 'bg-indigo-100 text-indigo-800',
    'Business': 'bg-blue-100 text-blue-800',
    'Particulier': 'bg-green-100 text-green-800'
  }
  return classMap[role] || 'bg-gray-100 text-gray-800'
}

// Watch for page changes
watch(currentPage, () => {
  loadUsers()
})

// Charger les rôles admin disponibles
const loadAdminRoles = async () => {
  try {
    const response = await get('/admin/users/admin-roles')
    if (response && response.data) {
      adminRoles.value = response.data.roles || []
    }
  } catch (error) {
    console.error('Erreur lors du chargement des rôles admin:', error)
  }
}

// Gérer le changement de téléphone pour création
const onPhoneChange = (phoneData) => {
  console.log('Phone changed:', phoneData)
  newAdmin.phone = phoneData.fullNumber // Utilise le numéro complet international
}

// Gérer le changement de téléphone pour modification
const onEditPhoneChange = (phoneData) => {
  console.log('Edit phone changed:', phoneData)
  editUserForm.phone = phoneData.fullNumber // Utilise le numéro complet international
}

// Créer un nouvel admin
const createAdmin = async () => {
  // Validation
  if (!newAdmin.first_name || !newAdmin.last_name || !newAdmin.email ||
      !newAdmin.phone || !newAdmin.role) {
    createError.value = 'Veuillez remplir tous les champs obligatoires'
    return
  }

  creatingAdmin.value = true
  createError.value = ''

  try {
    const response = await post('/admin/users', newAdmin)
    if (response && response.success) {
      showCreateModal.value = false
      await loadUsers() // Recharger la liste
      resetNewAdminForm()

      if (window.$toast) {
        window.$toast.success('Administrateur créé avec succès', '✓ Succès')
      }
    }
  } catch (error) {
    console.error('Erreur lors de la création:', error)
    createError.value = error.response?.data?.message || 'Erreur lors de la création de l\'administrateur'
  } finally {
    creatingAdmin.value = false
  }
}

// Mettre à jour un utilisateur
const updateUser = async () => {
  // Validation
  if (!editUserForm.first_name || !editUserForm.last_name || !editUserForm.email ||
      !editUserForm.phone || !editUserForm.role) {
    editError.value = 'Veuillez remplir tous les champs obligatoires'
    return
  }

  updatingUser.value = true
  editError.value = ''

  try {
    const response = await post(`/admin/users/${editUserForm.id}`, {
      ...editUserForm,
      _method: 'PUT' // Pour Laravel, utiliser PUT via POST
    })
    
    if (response && response.success) {
      showEditModal.value = false
      await loadUsers() // Recharger la liste
      resetEditUserForm()

      if (window.$toast) {
        window.$toast.success('Utilisateur modifié avec succès', 'Succès')
      }
    }
  } catch (error) {
    console.error('Erreur lors de la modification:', error)
    editError.value = error.response?.data?.message || 'Erreur lors de la modification de l\'utilisateur'
  } finally {
    updatingUser.value = false
  }
}

// Fermer le modal de création
const closeCreateModal = () => {
  showCreateModal.value = false
  resetNewAdminForm()
  createError.value = ''
}

// Fermer le modal de modification
const closeEditModal = () => {
  showEditModal.value = false
  resetEditUserForm()
  editError.value = ''
}

// Réinitialiser le formulaire
const resetNewAdminForm = () => {
  newAdmin.first_name = ''
  newAdmin.last_name = ''
  newAdmin.email = ''
  newAdmin.phone = ''
  newAdmin.role = ''
  newAdmin.is_active = true
}

// Réinitialiser le formulaire de modification
const resetEditUserForm = () => {
  editUserForm.id = null
  editUserForm.first_name = ''
  editUserForm.last_name = ''
  editUserForm.email = ''
  editUserForm.phone = ''
  editUserForm.role = ''
  editUserForm.is_active = true
  selectedUser.value = null
}

// Obtenir la description du rôle sélectionné
const getSelectedRoleDescription = () => {
  if (!newAdmin.role) return ''
  const role = adminRoles.value.find(r => r.value === newAdmin.role)
  return role?.description || ''
}

onMounted(() => {
  loadUsers()
  if (canCreateUsers.value) {
    loadAdminRoles()
  }
})
</script>
