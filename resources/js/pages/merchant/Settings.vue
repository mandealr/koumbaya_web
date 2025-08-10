<template>
  <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Paramètres</h1>
      <p class="mt-2 text-gray-600">Gérez les paramètres de votre compte marchand</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Navigation des paramètres -->
      <div class="lg:col-span-1">
        <nav class="space-y-2">
          <button
            v-for="section in settingSections"
            :key="section.id"
            @click="activeSection = section.id"
            :class="[
              'w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors',
              activeSection === section.id 
                ? 'bg-green-100 text-green-800 border border-green-200' 
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
          >
            <div class="flex items-center">
              <component :is="section.icon" class="w-5 h-5 mr-3" />
              {{ section.name }}
            </div>
          </button>
        </nav>
      </div>

      <!-- Contenu des paramètres -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
          <!-- Informations du compte -->
          <div v-if="activeSection === 'account'" class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Informations du compte</h3>
            
            <form @submit.prevent="updateAccountInfo" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Prénom</label>
                  <input 
                    v-model="accountForm.first_name"
                    type="text" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                  <input 
                    v-model="accountForm.last_name"
                    type="text" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  >
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input 
                  v-model="accountForm.email"
                  type="email" 
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                <input 
                  v-model="accountForm.phone"
                  type="tel" 
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
              </div>

              <div class="flex justify-end">
                <button 
                  type="submit"
                  class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
                >
                  Sauvegarder
                </button>
              </div>
            </form>
          </div>

          <!-- Paramètres de sécurité -->
          <div v-if="activeSection === 'security'" class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Sécurité</h3>
            
            <div class="space-y-6">
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Changer le mot de passe</h4>
                <p class="text-sm text-gray-600 mb-4">Assurez-vous d'utiliser un mot de passe fort et unique.</p>
                
                <form @submit.prevent="updatePassword" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe actuel</label>
                    <input 
                      v-model="passwordForm.current_password"
                      type="password" 
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau mot de passe</label>
                    <input 
                      v-model="passwordForm.new_password"
                      type="password" 
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmer le nouveau mot de passe</label>
                    <input 
                      v-model="passwordForm.confirm_password"
                      type="password" 
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                  </div>

                  <div class="flex justify-end">
                    <button 
                      type="submit"
                      class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
                    >
                      Mettre à jour le mot de passe
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Préférences -->
          <div v-if="activeSection === 'preferences'" class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Préférences</h3>
            
            <div class="space-y-6">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="font-medium text-gray-900">Notifications par email</h4>
                  <p class="text-sm text-gray-600">Recevoir des notifications pour les nouvelles commandes</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input 
                    v-model="preferences.email_notifications" 
                    type="checkbox" 
                    class="sr-only peer"
                  >
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                </label>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <h4 class="font-medium text-gray-900">Notifications push</h4>
                  <p class="text-sm text-gray-600">Recevoir des notifications en temps réel</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input 
                    v-model="preferences.push_notifications" 
                    type="checkbox" 
                    class="sr-only peer"
                  >
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                </label>
              </div>
            </div>

            <div class="mt-8 flex justify-end">
              <button 
                @click="updatePreferences"
                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
              >
                Sauvegarder les préférences
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
  ShieldCheckIcon,
  BellIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()
const { get, put } = useApi()

// State
const activeSection = ref('account')

const settingSections = [
  { id: 'account', name: 'Compte', icon: UserIcon },
  { id: 'security', name: 'Sécurité', icon: ShieldCheckIcon },
  { id: 'preferences', name: 'Préférences', icon: BellIcon }
]

// Forms
const accountForm = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: ''
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const preferences = reactive({
  email_notifications: true,
  push_notifications: false
})

// Methods
const loadUserData = () => {
  const user = authStore.user
  if (user) {
    accountForm.first_name = user.first_name || ''
    accountForm.last_name = user.last_name || ''
    accountForm.email = user.email || ''
    accountForm.phone = user.phone || ''
  }
}

const updateAccountInfo = async () => {
  try {
    await put('/user/profile', accountForm)
    // Update auth store
    await authStore.refreshUser()
    alert('Informations mises à jour avec succès')
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error)
    alert('Erreur lors de la mise à jour')
  }
}

const updatePassword = async () => {
  if (passwordForm.new_password !== passwordForm.confirm_password) {
    alert('Les mots de passe ne correspondent pas')
    return
  }
  
  try {
    await put('/user/password', {
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password
    })
    
    // Reset form
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.confirm_password = ''
    
    alert('Mot de passe mis à jour avec succès')
  } catch (error) {
    console.error('Erreur lors du changement de mot de passe:', error)
    alert('Erreur lors du changement de mot de passe')
  }
}

const updatePreferences = async () => {
  try {
    await put('/user/preferences', preferences)
    alert('Préférences sauvegardées avec succès')
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
    alert('Erreur lors de la sauvegarde des préférences')
  }
}

// Lifecycle
onMounted(() => {
  loadUserData()
})
</script>