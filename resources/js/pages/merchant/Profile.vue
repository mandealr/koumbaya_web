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
              uploadEndpoint="/merchant/profile/avatar"
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

        <!-- Section Changement de mot de passe -->
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200">
          <form @submit.prevent="updatePassword" class="space-y-6 p-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Changer le mot de passe</h3>

              <div v-if="!user?.has_password" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                  </svg>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-blue-800">Compte connecté via réseau social</p>
                    <p class="text-sm text-blue-700 mt-1">
                      Vous vous êtes connecté via Google ou Facebook. Définissez un mot de passe pour pouvoir vous connecter directement avec votre email.
                    </p>
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <!-- Mot de passe actuel (seulement si l'utilisateur a déjà un mot de passe) -->
                <div v-if="user?.has_password || passwordForm.showCurrentPassword">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mot de passe actuel <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="passwordForm.current_password"
                    type="password"
                    :required="user?.has_password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    style="color: #5f5f5f"
                    placeholder="Entrez votre mot de passe actuel"
                  >
                  <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">
                    {{ passwordErrors.current_password }}
                  </p>
                </div>

                <!-- Nouveau mot de passe -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ user?.has_password ? 'Nouveau mot de passe' : 'Mot de passe' }} <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="passwordForm.new_password"
                    type="password"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    style="color: #5f5f5f"
                    placeholder="Minimum 8 caractères"
                  >
                  <p class="mt-1 text-xs text-gray-500">
                    Minimum 8 caractères, avec au moins une majuscule, une minuscule et un chiffre
                  </p>
                  <p v-if="passwordErrors.new_password" class="mt-1 text-sm text-red-600">
                    {{ passwordErrors.new_password }}
                  </p>
                </div>

                <!-- Confirmation du nouveau mot de passe -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Confirmer le mot de passe <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="passwordForm.new_password_confirmation"
                    type="password"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    style="color: #5f5f5f"
                    placeholder="Confirmez votre mot de passe"
                  >
                </div>
              </div>
            </div>

            <!-- Bouton de sauvegarde -->
            <div class="flex justify-end pt-6 border-t border-gray-200">
              <button
                type="submit"
                :disabled="loadingPassword"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
              >
                {{ loadingPassword ? 'Sauvegarde...' : (user?.has_password ? 'Changer le mot de passe' : 'Définir le mot de passe') }}
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
const loadingPassword = ref(false)
const stats = reactive({
  products: 0,
  lotteries: 0,
  orders: 0,
  revenue: 0
})

const passwordErrors = reactive({
  current_password: '',
  new_password: ''
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
  
  // Utiliser uniquement les rôles avec descriptions détaillées
  if (user.value.roles && Array.isArray(user.value.roles) && user.value.roles.length > 0) {
    const roleDescriptions = user.value.roles.map(role => {
      switch(role.name) {
        case 'Business Individual':
          return 'Vendeur Individuel (simple-dashboard)'
        case 'Business Enterprise':
          return 'Vendeur Entreprise (dashboard complet)'
        case 'Business':
          return 'Vendeur (legacy)'
        case 'Particulier':
          return 'Client'
        case 'Admin':
          return 'Administrateur'
        case 'Super Admin':
          return 'Super Administrateur'
        case 'Agent':
          return 'Agent'
        default:
          return role.name
      }
    })
    return roleDescriptions.join(', ')
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

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
  showCurrentPassword: false
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

const updatePassword = async () => {
  loadingPassword.value = true

  // Reset errors
  passwordErrors.current_password = ''
  passwordErrors.new_password = ''

  try {
    // Préparer les données selon que l'utilisateur a déjà un mot de passe ou non
    const data = {
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation
    }

    // Ajouter current_password seulement si l'utilisateur a déjà un mot de passe
    if (user.value?.has_password || passwordForm.current_password) {
      data.current_password = passwordForm.current_password
    }

    const response = await put('/user/password', data)

    // Reset form
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.new_password_confirmation = ''

    if (window.$toast) {
      const message = response.data?.is_first_password
        ? 'Mot de passe défini avec succès'
        : 'Mot de passe modifié avec succès'
      window.$toast.success(message, '✅ Sécurité')
    }

    // Refresh user data
    await authStore.refreshUser()
  } catch (error) {
    console.error('Erreur lors du changement de mot de passe:', error)

    // Gérer les erreurs de validation
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      if (errors.current_password) {
        passwordErrors.current_password = Array.isArray(errors.current_password)
          ? errors.current_password[0]
          : errors.current_password
      }
      if (errors.new_password) {
        passwordErrors.new_password = Array.isArray(errors.new_password)
          ? errors.new_password[0]
          : errors.new_password
      }
    }

    if (window.$toast) {
      const errorMessage = error.response?.data?.message || 'Erreur lors du changement de mot de passe'
      window.$toast.error(errorMessage, '❌ Erreur')
    }
  } finally {
    loadingPassword.value = false
  }
}

const getDefaultAvatar = () => {
  if (!user.value) return '/default-avatar.png'
  const initials = userInitials.value
  return `https://ui-avatars.com/api/?name=${initials}&background=3b82f6&color=fff&size=128`
}

const handleAvatarSuccess = (response) => {
  console.log('Avatar upload success:', response)
  
  // Rafraîchir les données utilisateur
  const avatarUrl = response?.avatar_url || response?.data?.avatar_url || response?.response?.avatar_url
  if (avatarUrl) {
    // Mettre à jour l'utilisateur local
    if (user.value) {
      user.value.avatar_url = avatarUrl
    }
    
    // Rafraîchir depuis le store
    authStore.refreshUser()
    
    if (window.$toast) {
      window.$toast.success('Photo de profil mise à jour avec succès', '✅ Photo')
    }
  } else {
    console.warn('No avatar URL in response:', response)
  }
}

const handleAvatarError = (error) => {
  console.error('Erreur upload avatar:', error)
  
  let message = 'Erreur lors de la mise à jour de la photo'
  
  // Message d'erreur plus précis si disponible
  if (error?.message) {
    message = error.message
  } else if (typeof error === 'string') {
    message = error
  }
  
  if (window.$toast) {
    window.$toast.error(message, '❌ Erreur')
  }
}



// Lifecycle
onMounted(() => {
  loadUserData()
  loadStats()
})
</script>