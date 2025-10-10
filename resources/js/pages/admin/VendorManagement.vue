<template>
  <div class="px-6">
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestion des Vendeurs Pro</h1>
          <p class="mt-2 text-gray-600">Gérez les comptes vendeurs professionnels (Business)</p>
        </div>
        <div class="flex gap-3">
          <button
            @click="showCreateModal = true"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Créer un Vendeur Pro
          </button>
          <button
            @click="loadVendors"
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
            placeholder="Nom, email, entreprise..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
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

    <!-- Vendors Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-semibold text-gray-900">
            Vendeurs Pro ({{ filteredVendors.length }})
          </h3>
        </div>
      </div>

      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
      </div>

      <div v-else-if="vendors.length === 0" class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun vendeur</h3>
        <p class="mt-1 text-sm text-gray-500">Commencez par créer un nouveau vendeur professionnel.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entreprise</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="filteredVendors.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                Aucun vendeur ne correspond aux filtres sélectionnés
              </td>
            </tr>
            <tr v-for="vendor in filteredVendors" :key="vendor.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div v-if="vendor.avatar_url" class="h-10 w-10 rounded-full overflow-hidden">
                      <img :src="vendor.avatar_url" :alt="vendor.first_name" class="h-full w-full object-cover" />
                    </div>
                    <div v-else class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                      <span class="text-white font-semibold">{{ getInitials(vendor) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ vendor.first_name }} {{ vendor.last_name }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ vendor.business_name || vendor.company?.business_name || 'N/A' }}</div>
                <div v-if="vendor.company?.company_type" class="text-xs text-gray-500">
                  {{ vendor.company.company_type === 'enterprise' ? 'Entreprise' : 'Individuel' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ vendor.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-500">{{ vendor.phone || 'N/A' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    vendor.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ vendor.email_verified_at ? 'Vérifié' : 'Non vérifié' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(vendor.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click="viewVendor(vendor)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Voir les détails"
                  >
                    <EyeIcon class="w-5 h-5" />
                  </button>
                  <button
                    @click="editVendor(vendor)"
                    class="text-green-600 hover:text-green-900"
                    title="Modifier"
                  >
                    <PencilIcon class="w-5 h-5" />
                  </button>
                  <button
                    @click="deleteVendor(vendor)"
                    class="text-red-600 hover:text-red-900"
                    title="Supprimer"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Vendor Modal -->
    <div
      v-if="showCreateModal || showEditModal"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
      @click.self="closeModals"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">
              {{ showCreateModal ? 'Créer un Vendeur Pro' : 'Modifier le Vendeur Pro' }}
            </h2>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <!-- Informations personnelles -->
          <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Informations personnelles</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                <input
                  v-model="form.first_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                <input
                  v-model="form.last_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Informations de l'entreprise -->
          <div class="bg-blue-50 rounded-lg p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Informations de l'entreprise</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom de l'entreprise *</label>
                <input
                  v-model="form.business_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p class="text-xs text-gray-500 mt-1">Ce champ est obligatoire pour les vendeurs professionnels</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email de l'entreprise</label>
                <input
                  v-model="form.business_email"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description de l'entreprise</label>
                <textarea
                  v-model="form.business_description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Décrivez brièvement l'activité de l'entreprise..."
                ></textarea>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Numéro fiscal / SIRET</label>
                  <input
                    v-model="form.tax_id"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Ex: 123456789"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Type d'entreprise</label>
                  <select
                    v-model="form.company_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="enterprise">Entreprise</option>
                    <option value="individual">Individuel</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Informations de contact -->
          <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Informations de contact</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <input
                  v-model="form.phone"
                  type="tel"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Information sur l'envoi de l'email de reset password -->
          <div v-if="showCreateModal" class="bg-blue-50 border-l-4 border-blue-400 rounded-lg p-4 mb-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-semibold text-blue-800 mb-1">Création de mot de passe</h3>
                <p class="text-sm text-blue-700">
                  Un email sera automatiquement envoyé au vendeur avec un lien pour créer son mot de passe.
                  Il pourra se connecter après avoir défini son mot de passe.
                </p>
              </div>
            </div>
          </div>

          <!-- Error message -->
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModals"
              class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              {{ submitting ? 'Enregistrement...' : (showCreateModal ? 'Créer' : 'Enregistrer') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Vendor Modal -->
    <div
      v-if="showViewModal && selectedVendor"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
      @click.self="closeModals"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4">
          <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Détails du Vendeur Pro</h2>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
        </div>

        <div class="p-6 space-y-6">
          <div class="flex items-center space-x-4">
            <div v-if="selectedVendor.avatar_url" class="h-20 w-20 rounded-full overflow-hidden">
              <img :src="selectedVendor.avatar_url" :alt="selectedVendor.first_name" class="h-full w-full object-cover" />
            </div>
            <div v-else class="h-20 w-20 rounded-full bg-blue-500 flex items-center justify-center">
              <span class="text-white text-2xl font-semibold">{{ getInitials(selectedVendor) }}</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">{{ selectedVendor.first_name }} {{ selectedVendor.last_name }}</h3>
              <p class="text-gray-600">{{ selectedVendor.business_name || selectedVendor.company?.business_name || 'N/A' }}</p>
            </div>
          </div>

          <!-- Company Info Section -->
          <div v-if="selectedVendor.company" class="bg-blue-50 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-3">Informations de l'entreprise</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Nom</p>
                <p class="font-medium">{{ selectedVendor.company.business_name }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Type</p>
                <p class="font-medium">{{ selectedVendor.company.company_type === 'enterprise' ? 'Entreprise' : 'Individuel' }}</p>
              </div>
              <div v-if="selectedVendor.company.business_email">
                <p class="text-sm text-gray-500">Email entreprise</p>
                <p class="font-medium">{{ selectedVendor.company.business_email }}</p>
              </div>
              <div v-if="selectedVendor.company.tax_id">
                <p class="text-sm text-gray-500">Numéro fiscal</p>
                <p class="font-medium">{{ selectedVendor.company.tax_id }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Statut entreprise</p>
                <span
                  :class="[
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                    selectedVendor.company.is_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ selectedVendor.company.is_verified ? 'Vérifiée' : 'Non vérifiée' }}
                </span>
              </div>
            </div>
          </div>

          <!-- User Info Section -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Email</p>
              <p class="font-medium">{{ selectedVendor.email }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Téléphone</p>
              <p class="font-medium">{{ selectedVendor.phone || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Statut compte</p>
              <span
                :class="[
                  'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                  selectedVendor.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                ]"
              >
                {{ selectedVendor.email_verified_at ? 'Vérifié' : 'Non vérifié' }}
              </span>
            </div>
            <div>
              <p class="text-sm text-gray-500">Créé le</p>
              <p class="font-medium">{{ formatDate(selectedVendor.created_at) }}</p>
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t">
            <button
              @click="closeModals"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
            >
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import {
  PlusIcon,
  ArrowPathIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const { get, post, put, del } = useApi()

const loading = ref(false)
const submitting = ref(false)
const vendors = ref([])
const selectedVendor = ref(null)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const error = ref('')

const filters = ref({
  search: '',
  status: ''
})

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  business_name: '',
  business_email: '',
  business_description: '',
  tax_id: '',
  company_type: 'enterprise'
})

const filteredVendors = computed(() => {
  let result = vendors.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    result = result.filter(v =>
      v.first_name?.toLowerCase().includes(search) ||
      v.last_name?.toLowerCase().includes(search) ||
      v.email?.toLowerCase().includes(search) ||
      v.business_name?.toLowerCase().includes(search) ||
      v.company?.business_name?.toLowerCase().includes(search)
    )
  }

  if (filters.value.status) {
    if (filters.value.status === 'active') {
      result = result.filter(v => v.is_active)
    } else if (filters.value.status === 'inactive') {
      result = result.filter(v => !v.is_active)
    } else if (filters.value.status === 'verified') {
      result = result.filter(v => v.email_verified_at)
    } else if (filters.value.status === 'unverified') {
      result = result.filter(v => !v.email_verified_at)
    }
  }

  return result
})

const loadVendors = async () => {
  loading.value = true
  try {
    const response = await get('/admin/vendors')
    console.log('Vendors API response:', response)
    if (response?.vendors) {
      vendors.value = response.vendors
      console.log('Vendors loaded:', vendors.value.length, vendors.value)
    } else {
      console.log('No vendors in response')
    }
  } catch (err) {
    console.error('Error loading vendors:', err)
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    search: '',
    status: ''
  }
}

const resetForm = () => {
  form.value = {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    business_name: '',
    business_email: '',
    business_description: '',
    tax_id: '',
    company_type: 'enterprise'
  }
  error.value = ''
}

const closeModals = () => {
  showCreateModal.value = false
  showEditModal.value = false
  showViewModal.value = false
  selectedVendor.value = null
  resetForm()
}

const submitForm = async () => {
  error.value = ''

  // Validation
  if (!form.value.business_name?.trim()) {
    error.value = 'Le nom de l\'entreprise est obligatoire pour les vendeurs professionnels'
    return
  }

  submitting.value = true

  try {
    const payload = {
      first_name: form.value.first_name,
      last_name: form.value.last_name,
      email: form.value.email,
      phone: form.value.phone,
      business_name: form.value.business_name,
      business_email: form.value.business_email,
      business_description: form.value.business_description,
      tax_id: form.value.tax_id,
      company_type: form.value.company_type
    }

    if (showCreateModal.value) {
      // Pas de mot de passe lors de la création - un email de reset sera envoyé
      await post('/admin/vendors', payload)
    } else {
      await put(`/admin/vendors/${selectedVendor.value.id}`, payload)
    }

    closeModals()
    loadVendors()
  } catch (err) {
    error.value = err.response?.data?.message || 'Une erreur est survenue'
  } finally {
    submitting.value = false
  }
}

const viewVendor = (vendor) => {
  selectedVendor.value = vendor
  showViewModal.value = true
}

const editVendor = (vendor) => {
  selectedVendor.value = vendor
  form.value = {
    first_name: vendor.first_name,
    last_name: vendor.last_name,
    email: vendor.email,
    phone: vendor.phone || '',
    business_name: vendor.business_name || vendor.company?.business_name || '',
    business_email: vendor.company?.business_email || '',
    business_description: vendor.company?.business_description || '',
    tax_id: vendor.company?.tax_id || '',
    company_type: vendor.company?.company_type || 'enterprise'
  }
  showEditModal.value = true
}

const deleteVendor = async (vendor) => {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer le vendeur ${vendor.first_name} ${vendor.last_name} ?`)) {
    return
  }

  try {
    await del(`/admin/vendors/${vendor.id}`)
    loadVendors()
  } catch (err) {
    alert('Erreur lors de la suppression')
  }
}

const getInitials = (vendor) => {
  return (vendor.first_name?.[0] || '') + (vendor.last_name?.[0] || '')
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  loadVendors()
})
</script>
