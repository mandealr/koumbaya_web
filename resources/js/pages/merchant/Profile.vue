<template>
  <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Mon Profil</h1>
      <p class="mt-2 text-gray-600">Gérez vos informations personnelles et professionnelles</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Photo de profil et informations de base -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="text-center">
            <!-- Avatar -->
            <div class="mx-auto h-24 w-24 rounded-full bg-green-100 flex items-center justify-center mb-4">
              <span class="text-2xl font-bold text-green-600">
                {{ userInitials }}
              </span>
            </div>
            
            <h3 class="text-lg font-semibold text-gray-900">
              {{ user?.first_name }} {{ user?.last_name }}
            </h3>
            <p class="text-sm text-gray-600">{{ user?.email }}</p>
            
            <div class="mt-4 flex items-center justify-center space-x-2">
              <div class="flex items-center text-sm text-gray-600">
                <div :class="[
                  'w-2 h-2 rounded-full mr-2',
                  user?.is_active ? 'bg-green-400' : 'bg-red-400'
                ]"></div>
                {{ user?.is_active ? 'Compte actif' : 'Compte inactif' }}
              </div>
            </div>

            <div class="mt-4 text-xs text-gray-500">
              <p>Membre depuis</p>
              <p>{{ formatDate(user?.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h4 class="font-semibold text-gray-900 mb-4">Mes statistiques</h4>
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Produits</span>
              <span class="font-semibold text-gray-900">{{ stats.products || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Tombolas actives</span>
              <span class="font-semibold text-purple-600">{{ stats.lotteries || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Commandes</span>
              <span class="font-semibold text-blue-600">{{ stats.orders || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Revenus ce mois</span>
              <span class="font-semibold text-green-600">{{ formatCurrency(stats.revenue || 0) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulaire de profil -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
          <form @submit.prevent="updateProfile" class="space-y-6 p-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations personnelles</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Prénom <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="profileForm.first_name"
                    type="text" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nom <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="profileForm.last_name"
                    type="text" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="profileForm.email"
                    type="email" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Téléphone <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="profileForm.phone"
                    type="tel" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                </div>
              </div>

              <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                <input 
                  v-model="profileForm.city"
                  type="text" 
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
              </div>

              <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                <textarea 
                  v-model="profileForm.address"
                  rows="3"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="Votre adresse complète..."
                ></textarea>
              </div>
            </div>

            <!-- Informations business -->
            <div class="border-t border-gray-200 pt-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations business</h3>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Type de compte</label>
                  <div class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
                    {{ user?.account_type === 'business' ? 'Compte Business' : 'Compte Personnel' }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                  <div class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
                    {{ user?.role || 'Non défini' }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bio professionnelle</label>
                  <textarea 
                    v-model="profileForm.bio"
                    rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Décrivez votre activité, vos produits, votre expertise..."
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
              <button 
                type="button"
                @click="resetForm"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                Annuler
              </button>
              <button 
                type="submit"
                :disabled="loading"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
              >
                {{ loading ? 'Sauvegarde...' : 'Sauvegarder' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/api'

const authStore = useAuthStore()
const { get, put } = useApi()

// State
const loading = ref(false)
const stats = reactive({
  products: 0,
  lotteries: 0,
  orders: 0,
  revenue: 0
})

// Computed
const user = computed(() => authStore.user)

const userInitials = computed(() => {
  if (!user.value) return 'U'
  const first = user.value.first_name?.[0] || ''
  const last = user.value.last_name?.[0] || ''
  return (first + last).toUpperCase()
})

// Form data
const profileForm = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  city: '',
  address: '',
  bio: ''
})

// Methods
const loadUserData = () => {
  if (user.value) {
    profileForm.first_name = user.value.first_name || ''
    profileForm.last_name = user.value.last_name || ''
    profileForm.email = user.value.email || ''
    profileForm.phone = user.value.phone || ''
    profileForm.city = user.value.city || ''
    profileForm.address = user.value.address || ''
    profileForm.bio = user.value.bio || ''
  }
}

const loadStats = async () => {
  try {
    const response = await get('/merchant/dashboard/stats')
    if (response.data) {
      stats.products = response.data.total_products || 0
      stats.lotteries = response.data.active_lotteries || 0
      stats.orders = response.data.total_orders || 0
      stats.revenue = response.data.revenue_this_month || 0
    }
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques:', error)
  }
}

const updateProfile = async () => {
  loading.value = true
  
  try {
    await put('/user/profile', profileForm)
    
    // Refresh user data in store
    await authStore.refreshUser()
    
    alert('Profil mis à jour avec succès')
  } catch (error) {
    console.error('Erreur lors de la mise à jour du profil:', error)
    alert('Erreur lors de la mise à jour du profil')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  loadUserData()
}

const formatDate = (dateString) => {
  if (!dateString) return 'Non disponible'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount || 0) + ' FCFA'
}

// Lifecycle
onMounted(() => {
  loadUserData()
  loadStats()
})
</script>