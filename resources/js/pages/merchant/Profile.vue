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
            <AvatarUpload
              :currentAvatarUrl="user?.avatar_url || getDefaultAvatar()"
              uploadEndpoint="/user/avatar"
              fieldName="avatar"
              :altText="`Photo de profil de ${user?.first_name} ${user?.last_name}`"
              helpText="Cliquez pour modifier votre photo de profil"
              :maxSizeMB="5"
              @success="handleAvatarSuccess"
              @error="handleAvatarError"
            />
            
            <h3 class="text-lg font-semibold text-gray-900">
              {{ user?.first_name }} {{ user?.last_name }}
            </h3>
            <p class="text-sm text-gray-600">{{ user?.email }}</p>
            
            <div class="mt-4 flex items-center justify-center space-x-2">
              <div class="flex items-center text-sm text-gray-600">
                <div :class="[
                  'w-2 h-2 rounded-full mr-2',
                  user?.is_active ? 'bg-blue-400' : 'bg-red-400'
                ]"></div>
                {{ user?.is_active ? 'Compte actif' : 'Compte inactif' }}
              </div>
            </div>

            <div class="mt-4 text-xs text-gray-500">
              <p>Membre depuis</p>
              <p>{{ user?.created_at ? new Date(user.created_at).toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' }) : 'Non disponible' }}</p>
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
              <span class="font-semibold text-blue-600">{{ (stats.revenue || 0).toLocaleString('fr-FR') }} FCFA</span>
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
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="color: #5f5f5f"
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
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="color: #5f5f5f"
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
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="color: #5f5f5f"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Téléphone <span class="text-red-500">*</span>
                  </label>
                  <PhoneInput
                    v-model="profileForm.phone"
                    placeholder="Numéro de téléphone"
                    :initial-country="'ga'"
                  />
                </div>
              </div>

              <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                <input 
                  v-model="profileForm.city"
                  type="text" 
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="color: #5f5f5f"
                >
              </div>

              <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                <textarea 
                  v-model="profileForm.address"
                  rows="3"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="color: #5f5f5f"
                  placeholder="Votre adresse complète..."
                ></textarea>
              </div>
            </div>

            <!-- Informations business -->
            <div class="border-t border-gray-200 pt-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations business</h3>
              
              <div class="space-y-4">

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                  <div class="text-sm text-gray-600 bg-gray-100 rounded-lg p-3">
                    {{ displayUserRoles }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bio professionnelle</label>
                  <textarea 
                    v-model="profileForm.bio"
                    rows="4"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" style="color: #5f5f5f"
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
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
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
import PhoneInput from '@/components/PhoneInput.vue'
import AvatarUpload from '@/components/common/AvatarUpload.vue'

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

const displayUserRoles = computed(() => {
  if (!user.value) return 'Non défini'
  
  // Utiliser uniquement les rôles
  if (user.value.roles && Array.isArray(user.value.roles) && user.value.roles.length > 0) {
    const roleNames = user.value.roles.map(role => role.name)
    return roleNames.join(', ')
  }
  
  return 'Non défini'
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
    
    if (window.$toast) {
      window.$toast.success('Profil mis à jour avec succès', '✅ Profil')
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour du profil:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la mise à jour du profil', '❌ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  loadUserData()
}

const getDefaultAvatar = () => {
  if (!user.value) return '/default-avatar.png'
  const initials = userInitials.value
  return `https://ui-avatars.com/api/?name=${initials}&background=3b82f6&color=fff&size=128`
}

const handleAvatarSuccess = (response) => {
  if (response.data && response.data.avatar_url) {
    authStore.refreshUser()
    if (window.$toast) {
      window.$toast.success('Photo de profil mise à jour avec succès', '✅ Photo')
    }
  }
}

const handleAvatarError = (error) => {
  console.error('Erreur upload avatar:', error)
  if (window.$toast) {
    window.$toast.error('Erreur lors de la mise à jour de la photo', '❌ Erreur')
  }
}



// Lifecycle
onMounted(() => {
  loadUserData()
  loadStats()
})
</script>