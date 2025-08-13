<template>
  <div class="px-6">
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
          <p class="mt-2 text-gray-600">Gérez tous les utilisateurs de la plateforme</p>
        </div>
        <button 
          @click="showCreateModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
        >
          <PlusIcon class="w-5 h-5 inline mr-2" />
          Nouvel Utilisateur
        </button>
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
            <option value="CUSTOMER">Client</option>
            <option value="MERCHANT">Marchand</option>
            <option value="ADMIN">Administrateur</option>
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
                  user.role === 'ADMIN' ? 'bg-red-100 text-red-800' :
                  user.role === 'MERCHANT' ? 'bg-blue-100 text-blue-800' :
                  'bg-blue-100 text-blue-800'
                ]">
                  {{ user.role === 'ADMIN' ? 'Administrateur' :
                     user.role === 'MERCHANT' ? 'Marchand' : 'Client' }}
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
                    @click="editUser(user)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Modifier
                  </button>
                  <button 
                    @click="toggleUserStatus(user)"
                    :class="user.is_active ? 'text-red-600 hover:text-red-900' : 'text-blue-600 hover:text-blue-900'"
                  >
                    {{ user.is_active ? 'Désactiver' : 'Activer' }}
                  </button>
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
            {{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }} sur 
            {{ filteredUsers.length }} résultats
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { PlusIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
import api from '@/composables/api'

const users = ref([])
const loading = ref(false)
const showCreateModal = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10

const filters = ref({
  search: '',
  role: '',
  status: ''
})

// Mock data for now
const mockUsers = [
  {
    id: 1,
    first_name: 'Jean',
    last_name: 'Dupont',
    email: 'jean.dupont@email.com',
    phone: '+237 123 456 789',
    role: 'CUSTOMER',
    is_active: true,
    verified_at: new Date(),
    created_at: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
  },
  {
    id: 2,
    first_name: 'Marie',
    last_name: 'Claire',
    email: 'marie.claire@email.com',
    phone: '+237 987 654 321',
    role: 'MERCHANT',
    is_active: true,
    verified_at: null,
    created_at: new Date(Date.now() - 15 * 24 * 60 * 60 * 1000)
  },
  {
    id: 3,
    first_name: 'Paul',
    last_name: 'Martin',
    email: 'paul.martin@email.com',
    phone: '+237 555 123 456',
    role: 'ADMIN',
    is_active: false,
    verified_at: new Date(),
    created_at: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)
  }
]

const filteredUsers = computed(() => {
  let filtered = users.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(user => 
      user.first_name.toLowerCase().includes(search) ||
      user.last_name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search)
    )
  }

  if (filters.value.role) {
    filtered = filtered.filter(user => user.role === filters.value.role)
  }

  if (filters.value.status) {
    filtered = filtered.filter(user => {
      switch (filters.value.status) {
        case 'active':
          return user.is_active
        case 'inactive':
          return !user.is_active
        case 'verified':
          return user.verified_at !== null
        case 'unverified':
          return user.verified_at === null
        default:
          return true
      }
    })
  }

  return filtered
})

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredUsers.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage)
})

const loadUsers = async () => {
  loading.value = true
  try {
    // const response = await api.get('/users')
    // users.value = response.data.users
    
    // Using mock data for now
    setTimeout(() => {
      users.value = mockUsers
      loading.value = false
    }, 1000)
  } catch (error) {
    console.error('Erreur lors du chargement des utilisateurs:', error)
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    search: '',
    role: '',
    status: ''
  }
  currentPage.value = 1
}

const editUser = (user) => {
  console.log('Éditer utilisateur:', user)
}

const toggleUserStatus = async (user) => {
  try {
    // await api.patch(`/users/${user.id}/toggle-status`)
    user.is_active = !user.is_active
    console.log('Statut modifié pour:', user)
  } catch (error) {
    console.error('Erreur lors de la modification du statut:', error)
  }
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(new Date(date))
}

onMounted(() => {
  loadUsers()
})
</script>