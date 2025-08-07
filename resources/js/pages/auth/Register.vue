<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="flex justify-center">
          <div class="w-16 h-16 bg-green-600 rounded-xl flex items-center justify-center">
            <span class="text-white font-bold text-2xl">K</span>
          </div>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Créez votre compte
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Ou
          <router-link to="/login" class="font-medium text-green-600 hover:text-green-500">
            connectez-vous à votre compte existant
          </router-link>
        </p>
      </div>
      
      <form @submit.prevent="handleSubmit" class="mt-8 space-y-6">
        <div class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700">
                Prénom
              </label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': errors.first_name }"
              />
              <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
            </div>
            
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700">
                Nom
              </label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                :class="{ 'border-red-500': errors.last_name }"
              />
              <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
            </div>
          </div>
          
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Adresse email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
              :class="{ 'border-red-500': errors.email }"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
          </div>
          
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">
              Téléphone (optionnel)
            </label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              placeholder="+237 6XX XXX XXX"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
              :class="{ 'border-red-500': errors.phone }"
            />
            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Mot de passe
            </label>
            <div class="mt-1 relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 pr-10"
                :class="{ 'border-red-500': errors.password }"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <EyeIcon v-if="!showPassword" class="h-5 w-5 text-gray-400" />
                <EyeSlashIcon v-else class="h-5 w-5 text-gray-400" />
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>
          
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirmer le mot de passe
            </label>
            <div class="mt-1 relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                required
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 pr-10"
                :class="{ 'border-red-500': errors.password_confirmation }"
              />
              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <EyeIcon v-if="!showPasswordConfirmation" class="h-5 w-5 text-gray-400" />
                <EyeSlashIcon v-else class="h-5 w-5 text-gray-400" />
              </button>
            </div>
            <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
          </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="flex items-center">
          <input
            id="terms"
            v-model="form.terms"
            type="checkbox"
            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
            :class="{ 'border-red-500': errors.terms }"
          />
          <label for="terms" class="ml-2 block text-sm text-gray-900">
            J'accepte les 
            <a href="#" class="text-green-600 hover:text-green-500">conditions d'utilisation</a>
            et la 
            <a href="#" class="text-green-600 hover:text-green-500">politique de confidentialité</a>
          </label>
        </div>
        <p v-if="errors.terms" class="mt-1 text-sm text-red-600">{{ errors.terms }}</p>

        <!-- Error Message -->
        <div v-if="authStore.error" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Erreur d'inscription
              </h3>
              <div class="mt-2 text-sm text-red-700">
                {{ authStore.error }}
              </div>
            </div>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="authStore.loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="authStore.loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            </span>
            {{ authStore.loading ? 'Création du compte...' : 'Créer mon compte' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { EyeIcon, EyeSlashIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  terms: false
})

const errors = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  terms: ''
})

const validateForm = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  
  let isValid = true
  
  if (!form.first_name.trim()) {
    errors.first_name = 'Le prénom est requis'
    isValid = false
  }
  
  if (!form.last_name.trim()) {
    errors.last_name = 'Le nom est requis'
    isValid = false
  }
  
  if (!form.email) {
    errors.email = 'L\'email est requis'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Format d\'email invalide'
    isValid = false
  }
  
  if (form.phone && !/^\+?[0-9\s-()]+$/.test(form.phone)) {
    errors.phone = 'Format de téléphone invalide'
    isValid = false
  }
  
  if (!form.password) {
    errors.password = 'Le mot de passe est requis'
    isValid = false
  } else if (form.password.length < 8) {
    errors.password = 'Le mot de passe doit contenir au moins 8 caractères'
    isValid = false
  }
  
  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Les mots de passe ne correspondent pas'
    isValid = false
  }
  
  if (!form.terms) {
    errors.terms = 'Vous devez accepter les conditions d\'utilisation'
    isValid = false
  }
  
  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) return
  
  authStore.clearError()
  
  const result = await authStore.register({
    first_name: form.first_name.trim(),
    last_name: form.last_name.trim(),
    email: form.email.trim(),
    phone: form.phone.trim() || null,
    password: form.password,
    password_confirmation: form.password_confirmation
  })
  
  if (result.success) {
    router.push({ name: 'customer.dashboard' })
  }
}
</script>