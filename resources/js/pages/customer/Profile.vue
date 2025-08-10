<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Mon profil</h1>
      <p class="mt-2 text-gray-600">Gérez vos informations personnelles et préférences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Navigation -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="text-center mb-6">
            <div class="relative inline-block">
              <img
                :src="user.avatar || '/images/default-avatar.jpg'"
                :alt="user.first_name + ' ' + user.last_name"
                class="w-24 h-24 rounded-full object-cover"
              />
              <button
                @click="showAvatarUpload = true"
                class="absolute bottom-0 right-0 w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center hover:bg-green-700 transition-colors"
              >
                <CameraIcon class="w-4 h-4" />
              </button>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">
              {{ user.first_name }} {{ user.last_name }}
            </h3>
            <p class="text-sm text-gray-600">{{ user.email }}</p>
          </div>

          <nav class="space-y-1">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="activeTab = tab.key"
              :class="[
                'w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors',
                activeTab === tab.key
                  ? 'bg-green-50 text-green-600 border-r-2 border-green-600'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
              ]"
            >
              <component :is="tab.icon" class="w-5 h-5 mr-3" />
              {{ tab.label }}
            </button>
          </nav>
        </div>
      </div>

      <!-- Profile Content -->
      <div class="lg:col-span-2">
        <!-- Personal Information -->
        <div v-if="activeTab === 'personal'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Informations personnelles</h2>
          
          <form @submit.prevent="updatePersonalInfo">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                <input
                  v-model="personalForm.first_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                <input
                  v-model="personalForm.last_name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  v-model="personalForm.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                <input
                  v-model="personalForm.phone"
                  type="tel"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de naissance</label>
                <input
                  v-model="personalForm.birth_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                <select
                  v-model="personalForm.gender"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                >
                  <option value="">Sélectionner...</option>
                  <option value="male">Homme</option>
                  <option value="female">Femme</option>
                  <option value="other">Autre</option>
                </select>
              </div>
            </div>

            <div class="mt-6 flex justify-end">
              <button
                type="submit"
                :disabled="updatingPersonal"
                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:bg-gray-400"
              >
                <span v-if="updatingPersonal">Mise à jour...</span>
                <span v-else>Mettre à jour</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Address Information -->
        <div v-if="activeTab === 'address'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Adresse</h2>
          
          <form @submit.prevent="updateAddress">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                <input
                  v-model="addressForm.address"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                />
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                  <input
                    v-model="addressForm.city"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Code postal</label>
                  <input
                    v-model="addressForm.postal_code"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Pays</label>
                  <select
                    v-model="addressForm.country_id"
                    @change="loadStates"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                  >
                    <option value="">Sélectionner un pays...</option>
                    <option
                      v-for="country in countries"
                      :key="country.id"
                      :value="country.id"
                    >
                      {{ country.name }}
                    </option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">État/Province</label>
                  <select
                    v-model="addressForm.state"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                  >
                    <option value="">Sélectionner un état...</option>
                    <option
                      v-for="state in states"
                      :key="state"
                      :value="state"
                    >
                      {{ state }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <div class="mt-6 flex justify-end">
              <button
                type="submit"
                :disabled="updatingAddress"
                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:bg-gray-400"
              >
                <span v-if="updatingAddress">Mise à jour...</span>
                <span v-else>Mettre à jour</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Security Settings -->
        <div v-if="activeTab === 'security'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Sécurité</h2>
          
          <div class="space-y-6">
            <!-- Change Password -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Changer le mot de passe</h3>
              
              <form @submit.prevent="updatePassword" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe actuel</label>
                  <input
                    v-model="passwordForm.current_password"
                    type="password"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau mot de passe</label>
                  <input
                    v-model="passwordForm.new_password"
                    type="password"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le nouveau mot de passe</label>
                  <input
                    v-model="passwordForm.confirm_password"
                    type="password"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                  />
                </div>
                
                <button
                  type="submit"
                  :disabled="updatingPassword"
                  class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:bg-gray-400"
                >
                  <span v-if="updatingPassword">Mise à jour...</span>
                  <span v-else>Changer le mot de passe</span>
                </button>
              </form>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="border-b border-gray-200 pb-6">
              <div class="flex justify-between items-start">
                <div>
                  <h3 class="text-lg font-medium text-gray-900">Authentification à deux facteurs</h3>
                  <p class="text-sm text-gray-600 mt-1">Ajoutez une couche de sécurité supplémentaire à votre compte</p>
                </div>
                <button
                  @click="toggleTwoFactor"
                  :class="[
                    'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2',
                    user.two_factor_enabled ? 'bg-green-600' : 'bg-gray-200'
                  ]"
                >
                  <span
                    :class="[
                      'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                      user.two_factor_enabled ? 'translate-x-5' : 'translate-x-0'
                    ]"
                  />
                </button>
              </div>
            </div>

            <!-- Login Sessions -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Sessions de connexion</h3>
              <div class="space-y-3">
                <div
                  v-for="session in loginSessions"
                  :key="session.id"
                  class="flex justify-between items-center p-4 border border-gray-200 rounded-lg"
                >
                  <div class="flex items-center">
                    <DevicePhoneMobileIcon
                      v-if="session.device_type === 'mobile'"
                      class="w-6 h-6 text-gray-600 mr-3"
                    />
                    <ComputerDesktopIcon
                      v-else
                      class="w-6 h-6 text-gray-600 mr-3"
                    />
                    <div>
                      <p class="font-medium text-gray-900">{{ session.device_name }}</p>
                      <p class="text-sm text-gray-600">{{ session.location }} • {{ formatDate(session.last_active) }}</p>
                    </div>
                  </div>
                  <div class="flex items-center">
                    <span
                      v-if="session.is_current"
                      class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full mr-3"
                    >
                      Session actuelle
                    </span>
                    <button
                      v-if="!session.is_current"
                      @click="revokeSession(session.id)"
                      class="text-sm text-red-600 hover:text-red-700"
                    >
                      Révoquer
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notifications -->
        <div v-if="activeTab === 'notifications'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Préférences de notification</h2>
          
          <form @submit.prevent="updateNotifications">
            <div class="space-y-6">
              <div v-for="category in notificationCategories" :key="category.key" class="border-b border-gray-200 pb-6 last:border-b-0 last:pb-0">
                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ category.title }}</h3>
                <div class="space-y-3">
                  <div
                    v-for="notification in category.notifications"
                    :key="notification.key"
                    class="flex justify-between items-start"
                  >
                    <div class="flex-1">
                      <p class="font-medium text-gray-900">{{ notification.title }}</p>
                      <p class="text-sm text-gray-600">{{ notification.description }}</p>
                    </div>
                    <div class="ml-4 flex space-x-4">
                      <label class="flex items-center">
                        <input
                          v-model="notificationForm[notification.key].email"
                          type="checkbox"
                          class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">Email</span>
                      </label>
                      <label class="flex items-center">
                        <input
                          v-model="notificationForm[notification.key].sms"
                          type="checkbox"
                          class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">SMS</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 flex justify-end">
              <button
                type="submit"
                :disabled="updatingNotifications"
                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:bg-gray-400"
              >
                <span v-if="updatingNotifications">Mise à jour...</span>
                <span v-else>Sauvegarder les préférences</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Avatar Upload Modal -->
    <div v-if="showAvatarUpload" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Changer la photo de profil</h3>
        <div class="mb-4">
          <input
            ref="avatarInput"
            type="file"
            accept="image/*"
            @change="handleAvatarUpload"
            class="hidden"
          />
          <button
            @click="$refs.avatarInput.click()"
            class="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-green-500 transition-colors"
          >
            <CameraIcon class="w-8 h-8 text-gray-400 mx-auto mb-2" />
            <p class="text-sm text-gray-600">Cliquez pour choisir une image</p>
          </button>
        </div>
        <div class="flex justify-end space-x-3">
          <button
            @click="showAvatarUpload = false"
            class="px-4 py-2 text-gray-600 hover:text-gray-900"
          >
            Annuler
          </button>
          <button
            @click="uploadAvatar"
            :disabled="!selectedAvatar"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400"
          >
            Sauvegarder
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import {
  UserIcon,
  MapPinIcon,
  ShieldCheckIcon,
  BellIcon,
  CameraIcon,
  DevicePhoneMobileIcon,
  ComputerDesktopIcon
} from '@heroicons/vue/24/outline'
import api from '@/composables/api'

const authStore = useAuthStore()

const activeTab = ref('personal')
const updatingPersonal = ref(false)
const updatingAddress = ref(false)
const updatingPassword = ref(false)
const updatingNotifications = ref(false)
const showAvatarUpload = ref(false)
const selectedAvatar = ref(null)

const tabs = [
  { key: 'personal', label: 'Informations personnelles', icon: UserIcon },
  { key: 'address', label: 'Adresse', icon: MapPinIcon },
  { key: 'security', label: 'Sécurité', icon: ShieldCheckIcon },
  { key: 'notifications', label: 'Notifications', icon: BellIcon }
]

const user = reactive({
  id: 1,
  first_name: 'Jean',
  last_name: 'Dupont',
  email: 'jean.dupont@example.com',
  phone: '+33 6 12 34 56 78',
  birth_date: '1990-01-15',
  gender: 'male',
  avatar: null,
  two_factor_enabled: false,
  address: {
    address: '123 Rue de la Paix',
    city: 'Paris',
    postal_code: '75001',
    country_id: 1,
    state: 'Île-de-France'
  }
})

const personalForm = reactive({
  first_name: user.first_name,
  last_name: user.last_name,
  email: user.email,
  phone: user.phone,
  birth_date: user.birth_date,
  gender: user.gender
})

const addressForm = reactive({
  address: user.address.address,
  city: user.address.city,
  postal_code: user.address.postal_code,
  country_id: user.address.country_id,
  state: user.address.state
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const countries = ref([
  { id: 1, name: 'France' },
  { id: 2, name: 'Sénégal' },
  { id: 3, name: 'Côte d\'Ivoire' },
  { id: 4, name: 'Mali' }
])

const states = ref(['Île-de-France', 'Provence-Alpes-Côte d\'Azur', 'Nouvelle-Aquitaine', 'Occitanie'])

const loginSessions = ref([
  {
    id: 1,
    device_name: 'Chrome sur Windows',
    device_type: 'desktop',
    location: 'Paris, France',
    last_active: new Date(),
    is_current: true
  },
  {
    id: 2,
    device_name: 'Safari sur iPhone',
    device_type: 'mobile',
    location: 'Lyon, France',
    last_active: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000),
    is_current: false
  }
])

const notificationCategories = [
  {
    key: 'lottery',
    title: 'Tombolas',
    notifications: [
      {
        key: 'draw_results',
        title: 'Résultats des tirages',
        description: 'Être notifié des résultats des tombolas auxquelles vous participez'
      },
      {
        key: 'new_lottery',
        title: 'Nouvelles tombolas',
        description: 'Être informé des nouveaux produits mis en tombola'
      },
      {
        key: 'ending_soon',
        title: 'Fin de tombola',
        description: 'Rappel quand une tombola se termine bientôt'
      }
    ]
  },
  {
    key: 'account',
    title: 'Compte',
    notifications: [
      {
        key: 'login',
        title: 'Nouvelle connexion',
        description: 'Être alerté des nouvelles connexions à votre compte'
      },
      {
        key: 'profile_update',
        title: 'Modification du profil',
        description: 'Confirmation des modifications de votre profil'
      }
    ]
  }
]

const notificationForm = reactive({
  draw_results: { email: true, sms: true },
  new_lottery: { email: true, sms: false },
  ending_soon: { email: true, sms: true },
  login: { email: true, sms: false },
  profile_update: { email: true, sms: false }
})

const updatePersonalInfo = async () => {
  updatingPersonal.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    Object.assign(user, personalForm)
    console.log('Personal info updated')
  } catch (error) {
    console.error('Error updating personal info:', error)
  } finally {
    updatingPersonal.value = false
  }
}

const updateAddress = async () => {
  updatingAddress.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    Object.assign(user.address, addressForm)
    console.log('Address updated')
  } catch (error) {
    console.error('Error updating address:', error)
  } finally {
    updatingAddress.value = false
  }
}

const updatePassword = async () => {
  if (passwordForm.new_password !== passwordForm.confirm_password) {
    alert('Les mots de passe ne correspondent pas')
    return
  }

  updatingPassword.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    Object.assign(passwordForm, {
      current_password: '',
      new_password: '',
      confirm_password: ''
    })
    console.log('Password updated')
  } catch (error) {
    console.error('Error updating password:', error)
  } finally {
    updatingPassword.value = false
  }
}

const updateNotifications = async () => {
  updatingNotifications.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    console.log('Notifications updated')
  } catch (error) {
    console.error('Error updating notifications:', error)
  } finally {
    updatingNotifications.value = false
  }
}

const toggleTwoFactor = async () => {
  try {
    user.two_factor_enabled = !user.two_factor_enabled
    console.log('Two-factor authentication toggled:', user.two_factor_enabled)
  } catch (error) {
    console.error('Error toggling two-factor authentication:', error)
  }
}

const revokeSession = async (sessionId) => {
  try {
    loginSessions.value = loginSessions.value.filter(session => session.id !== sessionId)
    console.log('Session revoked:', sessionId)
  } catch (error) {
    console.error('Error revoking session:', error)
  }
}

const handleAvatarUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedAvatar.value = file
  }
}

const uploadAvatar = async () => {
  if (!selectedAvatar.value) return

  try {
    const formData = new FormData()
    formData.append('avatar', selectedAvatar.value)
    
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    user.avatar = URL.createObjectURL(selectedAvatar.value)
    showAvatarUpload.value = false
    selectedAvatar.value = null
    
    console.log('Avatar uploaded')
  } catch (error) {
    console.error('Error uploading avatar:', error)
  }
}

const loadStates = () => {
  // Mock loading states based on country
  if (addressForm.country_id === 1) {
    states.value = ['Île-de-France', 'Provence-Alpes-Côte d\'Azur', 'Nouvelle-Aquitaine', 'Occitanie']
  } else {
    states.value = []
  }
}

const formatDate = (date) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

onMounted(() => {
  // Load user profile data
  console.log('Profile page mounted')
})
</script>