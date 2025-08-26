<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Profil Administrateur</h1>
        <p class="mt-2 text-gray-600">Gérez vos informations personnelles et paramètres de compte</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="refreshProfile"
          class="admin-btn-secondary"
        >
          <ArrowPathIcon class="w-4 h-4 mr-2" />
          Actualiser
        </button>
        <button
          @click="downloadActivityLog"
          class="admin-btn-accent"
        >
          <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
          Journal d'activité
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Card -->
      <div class="lg:col-span-1">
        <div class="admin-card text-center">
          <div class="p-6">
            <AvatarUpload
              :current-avatar-url="profileData.avatar_url"
              upload-endpoint="/admin/profile/avatar"
              field-name="avatar"
              alt-text="Photo de profil administrateur"
              help-text="Cliquez pour modifier votre photo de profil"
              @success="handleAvatarUploadSuccess"
              @error="handleAvatarUploadError"
            />
            
            <h2 class="text-xl font-bold text-gray-900 mb-1">
              {{ profileData.first_name }} {{ profileData.last_name }}
            </h2>
            <p class="text-gray-600 mb-2">{{ profileData.email }}</p>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
              <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
              Administrateur actif
            </span>
          </div>
          
          <div class="border-t border-gray-200 px-6 py-4">
            <div class="grid grid-cols-2 gap-4 text-center">
              <div>
                <p class="text-2xl font-bold text-gray-900">{{ adminStats.actions_count || 0 }}</p>
                <p class="text-sm text-gray-600">Actions ce mois</p>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">{{ adminStats.login_streak || 0 }}</p>
                <p class="text-sm text-gray-600">Jours consécutifs</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="admin-card mt-6">
          <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900">Statistiques rapides</h3>
          </div>
          <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <ClockIcon class="w-5 h-5 text-blue-500 mr-3" />
                <span class="text-sm text-gray-700">Dernière connexion</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ formatLastLogin() }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <ShieldCheckIcon class="w-5 h-5 text-green-500 mr-3" />
                <span class="text-sm text-gray-700">Privilèges</span>
              </div>
              <span class="text-sm font-medium text-gray-900">Super Admin</span>
            </div>
            
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <CalendarIcon class="w-5 h-5 text-purple-500 mr-3" />
                <span class="text-sm text-gray-700">Membre depuis</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ formatMemberSince() }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Settings -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900">Informations personnelles</h3>
          </div>
          <div class="p-6">
            <form @submit.prevent="updatePersonalInfo" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                  <input 
                    v-model="profileData.first_name"
                    type="text" 
                    class="admin-input"
                    required
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                  <input 
                    v-model="profileData.last_name"
                    type="text" 
                    class="admin-input"
                    required
                  >
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input 
                  v-model="profileData.email"
                  type="email" 
                  class="admin-input"
                  required
                >
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                  <PhoneInputAdmin
                    v-model="profileData.phone"
                    placeholder="Numéro de téléphone"
                    :initial-country="'ga'"
                  />
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Poste/Fonction</label>
                  <input 
                    v-model="profileData.position"
                    type="text" 
                    class="admin-input"
                    placeholder="Administrateur système"
                  >
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                <textarea 
                  v-model="profileData.bio"
                  rows="3"
                  class="admin-input"
                  placeholder="Présentez-vous en quelques mots..."
                ></textarea>
              </div>

              <div class="flex justify-end">
                <button 
                  type="submit"
                  :disabled="loading"
                  class="admin-btn-primary disabled:opacity-50"
                >
                  {{ loading ? 'Sauvegarde...' : 'Sauvegarder les modifications' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Security Settings -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900">Sécurité et confidentialité</h3>
          </div>
          <div class="p-6 space-y-6">
            <!-- Change Password -->
            <div class="border border-gray-200 rounded-lg p-4">
              <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                <LockClosedIcon class="w-5 h-5 mr-2 text-red-500" />
                Changer le mot de passe
              </h4>
              <form @submit.prevent="updatePassword" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe actuel</label>
                  <input 
                    v-model="passwordForm.current_password"
                    type="password" 
                    class="admin-input"
                    required
                  >
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau mot de passe</label>
                    <input 
                      v-model="passwordForm.new_password"
                      type="password" 
                      class="admin-input"
                      minlength="8"
                      required
                    >
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
                    <input 
                      v-model="passwordForm.confirm_password"
                      type="password" 
                      class="admin-input"
                      required
                    >
                  </div>
                </div>

                <div class="flex justify-end">
                  <button 
                    type="submit"
                    :disabled="loading"
                    class="admin-btn-danger disabled:opacity-50"
                  >
                    {{ loading ? 'Mise à jour...' : 'Mettre à jour le mot de passe' }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="border border-gray-200 rounded-lg p-4">
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900 flex items-center">
                  <DevicePhoneMobileIcon class="w-5 h-5 mr-2 text-blue-500" />
                  Authentification à deux facteurs (2FA)
                </h4>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input 
                    v-model="profileData.two_factor_enabled" 
                    type="checkbox" 
                    class="sr-only peer"
                    @change="toggle2FA"
                  >
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#0099cc]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0099cc]"></div>
                </label>
              </div>
              <p class="text-sm text-gray-600">
                {{ profileData.two_factor_enabled 
                  ? 'L\'authentification à deux facteurs est activée pour votre compte.' 
                  : 'Activez l\'authentification à deux facteurs pour renforcer la sécurité de votre compte.' 
                }}
              </p>
            </div>

            <!-- Session Management -->
            <div class="border border-gray-200 rounded-lg p-4">
              <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                <ComputerDesktopIcon class="w-5 h-5 mr-2 text-yellow-500" />
                Sessions actives
              </h4>
              <div class="space-y-3">
                <div v-for="session in activeSessions" :key="session.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ session.device }}</p>
                      <p class="text-xs text-gray-600">{{ session.location }} • {{ formatSessionTime(session.last_activity) }}</p>
                    </div>
                  </div>
                  <button
                    v-if="!session.current"
                    @click="terminateSession(session.id)"
                    class="text-red-600 hover:text-red-900 text-sm"
                  >
                    Déconnecter
                  </button>
                  <span v-else class="text-xs text-blue-600 font-medium">Session actuelle</span>
                </div>
              </div>
              
              <div class="mt-4 flex justify-end">
                <button 
                  @click="terminateAllSessions"
                  class="admin-btn-danger text-sm"
                >
                  Déconnecter toutes les autres sessions
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Admin Preferences -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="text-lg font-semibold text-gray-900">Préférences d'administration</h3>
          </div>
          <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fuseau horaire</label>
                <select v-model="profileData.timezone" class="admin-input">
                  <option value="Africa/Libreville">Afrique/Libreville (WAT)</option>
                  <option value="UTC">UTC (Temps universel)</option>
                  <option value="Africa/Abidjan">Afrique/Abidjan (GMT)</option>
                  <option value="Europe/Paris">Europe/Paris (CET)</option>
                  <option value="America/New_York">Amérique/New York (EST)</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Langue</label>
                <select v-model="profileData.language" class="admin-input">
                  <option value="fr">Français</option>
                  <option value="en">English</option>
                </select>
              </div>
            </div>

            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Notifications d'administration</h4>
              
              <div class="space-y-3">
                <div v-for="notification in adminNotifications" :key="notification.id" class="flex items-center justify-between">
                  <div>
                    <p class="font-medium text-gray-900">{{ notification.name }}</p>
                    <p class="text-sm text-gray-600">{{ notification.description }}</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input 
                      v-model="notification.enabled" 
                      type="checkbox" 
                      class="sr-only peer"
                    >
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#0099cc]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0099cc]"></div>
                  </label>
                </div>
              </div>
            </div>

            <div class="flex justify-end">
              <button 
                @click="updatePreferences"
                :disabled="loading"
                class="admin-btn-primary disabled:opacity-50"
              >
                {{ loading ? 'Sauvegarde...' : 'Sauvegarder les préférences' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useApi } from '@/composables/api'
import {
  UserIcon,
  CameraIcon,
  ArrowPathIcon,
  DocumentArrowDownIcon,
  ClockIcon,
  ShieldCheckIcon,
  CalendarIcon,
  LockClosedIcon,
  DevicePhoneMobileIcon,
  ComputerDesktopIcon,
  XMarkIcon,
  PhotoIcon
} from '@heroicons/vue/24/outline'
import PhoneInputAdmin from '@/components/PhoneInputAdmin.vue'
import AvatarUpload from '@/components/common/AvatarUpload.vue'

const authStore = useAuthStore()
const { get, post, put } = useApi()

// State
const loading = ref(false)
const uploading = ref(false)
const showImageUpload = ref(false)
const selectedImage = ref(null)

// Profile data
const profileData = reactive({
  first_name: authStore.user?.first_name || '',
  last_name: authStore.user?.last_name || '',
  email: authStore.user?.email || '',
  phone: '',
  position: '',
  bio: '',
  timezone: 'Africa/Libreville',
  language: 'fr',
  two_factor_enabled: false,
  avatar_url: authStore.user?.avatar_url || null
})

// Password form
const passwordForm = reactive({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

// Admin stats
const adminStats = ref({
  actions_count: 0,
  login_streak: 0,
  last_login: null,
  created_at: null
})

// Active sessions - loaded from API
const activeSessions = ref([])

// Admin notifications - loaded from API
const adminNotifications = ref([])

// Utility function to handle API errors gracefully during development
const handleApiError = (error, action, successMessage) => {
  if (error.response?.status === 404) {
    // API pas encore implémentée, simuler le succès
    console.info(`API ${action} pas encore implémentée, simulation du succès`)
    if (window.$toast) {
      window.$toast.info(`${successMessage} (API en développement)`, 'ℹ️ Développement')
    }
  } else {
    if (window.$toast) {
      window.$toast.error(`Erreur lors de ${action.toLowerCase()}`, '✗ Erreur')
    }
  }
}

// Methods
const loadProfile = async () => {
  loading.value = true
  try {
    const response = await get('/admin/profile')
    if (response && response.success && response.data) {
      Object.assign(profileData, response.data.profile)
      adminStats.value = response.data.stats || {}
      
      if (response.data.sessions) {
        activeSessions.value = response.data.sessions
      }
    }
  } catch (error) {
    console.error('Error loading profile:', error)
    // En mode développement, utiliser les données par défaut sans afficher d'erreur
    if (error.response?.status !== 404) {
      if (window.$toast) {
        window.$toast.error('Erreur lors du chargement du profil', '✗ Erreur')
      }
    } else {
      // API pas encore implémentée, utiliser les données par défaut
      console.info('API /admin/profile pas encore implémentée, utilisation des données par défaut')
      // Utiliser les données du store d'authentification comme fallback
      if (authStore.user) {
        Object.assign(profileData, {
          first_name: authStore.user.first_name || '',
          last_name: authStore.user.last_name || '',
          email: authStore.user.email || '',
          phone: profileData.phone,
          position: profileData.position,
          bio: profileData.bio,
          timezone: profileData.timezone,
          language: profileData.language,
          two_factor_enabled: profileData.two_factor_enabled,
          avatar_url: authStore.user.avatar_url || null
        })
      }
      // Données par défaut pour les stats admin (développement)
      adminStats.value = {
        actions_count: 127,
        login_streak: 12,
        last_login: new Date(Date.now() - 1000 * 60 * 30).toISOString(),
        created_at: new Date(Date.now() - 1000 * 60 * 60 * 24 * 90).toISOString()
      }
      
      // Données par défaut pour les sessions actives (développement)
      activeSessions.value = [
        {
          id: 1,
          device: 'Chrome sur Mac',
          location: 'Libreville, Gabon',
          last_activity: new Date(),
          current: true
        }
      ]
      
      // Données par défaut pour les notifications admin (développement)
      adminNotifications.value = [
        {
          id: 'new_user_registration',
          name: 'Nouveaux utilisateurs',
          description: 'Notification lors de l\'inscription d\'un nouvel utilisateur',
          enabled: true
        },
        {
          id: 'suspicious_activity',
          name: 'Activité suspecte',
          description: 'Alertes de sécurité et activités inhabituelles',
          enabled: true
        }
      ]
    }
  } finally {
    loading.value = false
  }
}

const updatePersonalInfo = async () => {
  loading.value = true
  try {
    const response = await put('/admin/profile', profileData)
    if (response && response.success) {
      // Update auth store
      await authStore.refreshUser()
      if (window.$toast) {
        window.$toast.success('Informations mises à jour avec succès', '✅ Profil')
      }
    }
  } catch (error) {
    console.error('Error updating profile:', error)
    handleApiError(error, '/admin/profile', 'Informations mises à jour avec succès')
  } finally {
    loading.value = false
  }
}

const updatePassword = async () => {
  if (passwordForm.new_password !== passwordForm.confirm_password) {
    if (window.$toast) {
      window.$toast.error('Les mots de passe ne correspondent pas', '✗ Validation')
    }
    return
  }
  
  if (passwordForm.new_password.length < 8) {
    if (window.$toast) {
      window.$toast.error('Le mot de passe doit contenir au moins 8 caractères', '✗ Validation')
    }
    return
  }
  
  loading.value = true
  try {
    const response = await put('/admin/profile/password', {
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password
    })
    
    if (response && response.success) {
      // Reset form
      passwordForm.current_password = ''
      passwordForm.new_password = ''
      passwordForm.confirm_password = ''
      
      if (window.$toast) {
        window.$toast.success('Mot de passe mis à jour avec succès', '✅ Sécurité')
      }
    }
  } catch (error) {
    console.error('Error updating password:', error)
    if (window.$toast) {
      const message = error.response?.data?.message || 'Erreur lors du changement de mot de passe'
      window.$toast.error(message, '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const updatePreferences = async () => {
  loading.value = true
  try {
    const response = await put('/admin/profile/preferences', {
      timezone: profileData.timezone,
      language: profileData.language,
      notifications: adminNotifications.value
    })
    
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Préférences sauvegardées avec succès', '✅ Préférences')
      }
    }
  } catch (error) {
    console.error('Error updating preferences:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la sauvegarde des préférences', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const toggle2FA = async () => {
  loading.value = true
  try {
    const response = await post('/admin/profile/2fa/toggle', {
      enabled: profileData.two_factor_enabled
    })
    
    if (response && response.success) {
      const message = profileData.two_factor_enabled 
        ? 'Authentification 2FA activée' 
        : 'Authentification 2FA désactivée'
      if (window.$toast) {
        window.$toast.success(message, '✅ Sécurité')
      }
    }
  } catch (error) {
    console.error('Error toggling 2FA:', error)
    // Revert the toggle
    profileData.two_factor_enabled = !profileData.two_factor_enabled
    if (window.$toast) {
      window.$toast.error('Erreur lors de la modification 2FA', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const terminateSession = async (sessionId) => {
  if (!confirm('Voulez-vous vraiment déconnecter cette session ?')) return
  
  try {
    const response = await post('/admin/profile/sessions/terminate', { session_id: sessionId })
    if (response && response.success) {
      activeSessions.value = activeSessions.value.filter(s => s.id !== sessionId)
      if (window.$toast) {
        window.$toast.success('Session déconnectée', '✅ Sécurité')
      }
    }
  } catch (error) {
    console.error('Error terminating session:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la déconnexion', '✗ Erreur')
    }
  }
}

const terminateAllSessions = async () => {
  if (!confirm('Voulez-vous vraiment déconnecter toutes les autres sessions ?')) return
  
  try {
    const response = await post('/admin/profile/sessions/terminate-all')
    if (response && response.success) {
      activeSessions.value = activeSessions.value.filter(s => s.current)
      if (window.$toast) {
        window.$toast.success('Toutes les sessions ont été déconnectées', '✅ Sécurité')
      }
    }
  } catch (error) {
    console.error('Error terminating sessions:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la déconnexion', '✗ Erreur')
    }
  }
}

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      if (window.$toast) {
        window.$toast.error('L\'image ne doit pas dépasser 2MB', '✗ Taille')
      }
      return
    }
    selectedImage.value = file
  }
}

const uploadProfileImage = async () => {
  if (!selectedImage.value) return
  
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('profile_image', selectedImage.value)
    
    const response = await post('/admin/profile/image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (response && response.success) {
      showImageUpload.value = false
      selectedImage.value = null
      if (window.$toast) {
        window.$toast.success('Photo de profil mise à jour', '✅ Profil')
      }
      // Reload profile to get updated image URL
      await loadProfile()
    }
  } catch (error) {
    console.error('Error uploading image:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de l\'upload de l\'image', '✗ Erreur')
    }
  } finally {
    uploading.value = false
  }
}

const handleAvatarUploadSuccess = (data) => {
  profileData.avatar_url = data.avatar_url
  authStore.updateUser({ avatar_url: data.avatar_url })
  if (window.$toast) {
    window.$toast.success('Photo de profil mise à jour avec succès', '✅ Profil')
  }
}

const handleAvatarUploadError = (error) => {
  console.error('Avatar upload error:', error)
  if (window.$toast) {
    window.$toast.error('Erreur lors de l\'upload de l\'avatar', '✗ Erreur')
  }
}

const refreshProfile = async () => {
  await loadProfile()
  if (window.$toast) {
    window.$toast.info('Profil rechargé', 'ℹ️ Information')
  }
}

const downloadActivityLog = async () => {
  try {
    const response = await get('/admin/profile/activity-log/export')
    if (response && response.success) {
      // Create and download file
      const blob = new Blob([JSON.stringify(response.data, null, 2)], {
        type: 'application/json'
      })
      const url = URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `admin-activity-log-${new Date().toISOString().split('T')[0]}.json`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      URL.revokeObjectURL(url)
      
      if (window.$toast) {
        window.$toast.success('Journal d\'activité téléchargé', '✅ Export')
      }
    }
  } catch (error) {
    console.error('Error downloading activity log:', error)
    handleApiError(error, '/admin/profile/activity-log/export', 'Journal d\'activité téléchargé')
  }
}

// Utility methods
const formatLastLogin = () => {
  if (!adminStats.value.last_login) return 'Jamais'
  return new Date(adminStats.value.last_login).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatMemberSince = () => {
  if (!adminStats.value.created_at) return 'N/A'
  return new Date(adminStats.value.created_at).toLocaleDateString('fr-FR', {
    month: 'long',
    year: 'numeric'
  })
}

const formatSessionTime = (date) => {
  const now = new Date()
  const diff = Math.floor((now - date) / 1000)
  
  if (diff < 60) return 'À l\'instant'
  if (diff < 3600) return `il y a ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `il y a ${Math.floor(diff / 3600)} h`
  return `il y a ${Math.floor(diff / 86400)} j`
}

// Lifecycle
onMounted(() => {
  loadProfile()
})
</script>