<template>
  <div class="min-h-screen bg-gradient-to-br from-koumbaya-primary/5 via-white to-koumbaya-primary/10 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center mb-8">
          <img class="h-20 w-auto" :src="logoUrl" alt="Koumbaya" />
      </div>
      <h2 class="text-center text-4xl font-bold text-gray-900 mb-3">
        Content de vous revoir !
      </h2>
      <p class="text-center text-lg text-gray-600 mb-8">
        Connectez-vous pour accéder à vos tombolas
      </p>
      <p class="text-center text-sm text-gray-500">
        Pas encore de compte ?
        <router-link to="/register" class="font-semibold text-koumbaya-primary hover:text-koumbaya-primary-dark transition-colors ml-1">
          Inscrivez-vous gratuitement →
        </router-link>
      </p>
    </div>

    <div class="mt-12 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="koumbaya-card bg-white/80 backdrop-blur-sm border-0 shadow-2xl">
        <div class="koumbaya-card-body p-8">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div v-if="errors.general" class="rounded-xl bg-red-50 border border-red-200 p-4">
              <div class="flex items-center">
                <ExclamationCircleIcon class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" />
                <div class="text-sm text-red-700 font-medium">{{ errors.general }}</div>
              </div>
            </div>

            <div class="koumbaya-form-group">
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                <div class="flex items-center">
                  <EnvelopeIcon class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" />
                  <span>Adresse e-mail</span>
                </div>
              </label>
              <input
                id="email"
                name="email"
                type="email"
                v-model="form.email"
                required
                class="koumbaya-input bg-white/50 border-gray-200 focus:border-koumbaya-primary focus:ring-4 focus:ring-koumbaya-primary/10 rounded-xl transition-all duration-200 text-black"
                :class="{ 'border-red-300 focus:border-red-500': errors.email }"
                placeholder="exemple@koumbaya.com"
              />
              <p v-if="errors.email" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                {{ errors.email }}
              </p>
            </div>

            <div class="koumbaya-form-group">
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                <div class="flex items-center">
                  <LockClosedIcon class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" />
                  <span>Mot de passe</span>
                </div>
              </label>
              <div class="relative">
                <input
                  id="password"
                  name="password"
                  :type="showPassword ? 'text' : 'password'"
                  v-model="form.password"
                  required
                  class="koumbaya-input bg-white/50 border-gray-200 focus:border-koumbaya-primary focus:ring-4 focus:ring-koumbaya-primary/10 rounded-xl transition-all duration-200 pr-12 text-black"
                  :class="{ 'border-red-300 focus:border-red-500': errors.password }"
                  placeholder="••••••••"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-4 flex items-center hover:bg-gray-100 rounded-r-xl transition-colors"
                >
                  <EyeIcon v-if="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                  <EyeSlashIcon v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                </button>
              </div>
              <p v-if="errors.password" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                {{ errors.password }}
              </p>
            </div>

            <div class="flex items-center justify-between">
              <label class="flex items-center cursor-pointer group">
                <input
                  id="remember-me"
                  name="remember-me"
                  type="checkbox"
                  v-model="form.rememberMe"
                  class="w-4 h-4 text-koumbaya-primary bg-white border-2 border-gray-300 rounded focus:ring-koumbaya-primary focus:ring-2 transition-colors"
                />
                <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900 transition-colors">
                  Se souvenir de moi
                </span>
              </label>

              <a href="#" class="text-sm font-semibold text-koumbaya-primary hover:text-koumbaya-primary-dark transition-colors">
                Mot de passe oublié ?
              </a>
            </div>

            <div class="pt-2">
              <button
                type="submit"
                :disabled="loading"
                class="koumbaya-btn koumbaya-btn-primary koumbaya-btn-full koumbaya-btn-lg relative overflow-hidden group disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transition-all duration-300"
              >
                <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-4">
                  <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                </span>
                <span class="flex items-center justify-center">
                  <ArrowRightOnRectangleIcon v-if="!loading" class="w-5 h-5 mr-2 group-hover:translate-x-1 transition-transform" />
                  {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
                </span>
              </button>
            </div>
          </form>

          <!-- Divider avec style moderne -->
          <div class="my-8">
            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200" />
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white text-gray-500 font-medium">Ou continuez avec</span>
              </div>
            </div>
          </div>

          <!-- Social login buttons avec style amélioré -->
          <div class="grid grid-cols-2 gap-4">
            <button class="flex items-center justify-center px-4 py-3 border border-gray-200 rounded-xl shadow-sm bg-white hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 group">
              <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-700" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              <span class="ml-2 text-sm font-medium text-gray-700">Google</span>
            </button>

            <button class="flex items-center justify-center px-4 py-3 border border-gray-200 rounded-xl shadow-sm bg-white hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 group">
              <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
              </svg>
              <span class="ml-2 text-sm font-medium text-gray-700">Twitter</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import logoUrl from '@/assets/logo.png'
import {
  EyeIcon,
  EyeSlashIcon,
  EnvelopeIcon,
  LockClosedIcon,
  ArrowRightOnRectangleIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: '',
  rememberMe: false
})

const errors = reactive({
  email: '',
  password: '',
  general: ''
})

const loading = ref(false)
const showPassword = ref(false)

const validateForm = () => {
  let isValid = true

  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  // Email validation
  if (!form.email) {
    errors.email = 'L\'adresse email est requise'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Adresse email invalide'
    isValid = false
  }

  // Password validation
  if (!form.password) {
    errors.password = 'Le mot de passe est requis'
    isValid = false
  } else if (form.password.length < 6) {
    errors.password = 'Le mot de passe doit contenir au moins 6 caractères'
    isValid = false
  }

  return isValid
}

const handleSubmit = async (event) => {
  // Assurer que le formulaire ne se soumet pas de manière standard
  if (event) {
    event.preventDefault()
    event.stopPropagation()
  }

  if (!validateForm()) {
    return
  }

  loading.value = true
  errors.general = ''

  try {
    const result = await authStore.login({
      email: form.email,
      password: form.password
    })

    if (result.success) {
      // Success toast
      if (window.$toast) {
        window.$toast.success('Connexion réussie ! Redirection en cours...', 'Bienvenue !')
      }

      // Debug: Log user data
      console.log('User data après connexion:', authStore.user)

      // Use the centralized redirect logic
      const redirectTo = authStore.getDefaultRedirect()

      console.log('Redirection logic:', {
        user: authStore.user,
        redirectTo,
        isAdmin: authStore.isAdmin,
        isMerchant: authStore.isMerchant,
        isCustomer: authStore.isCustomer
      })

      setTimeout(() => {
        router.push({ name: redirectTo }).catch(error => {
          console.error('Erreur de redirection vers', redirectTo, ':', error)
          // Fallback: try customer dashboard, then home
          router.push({ name: 'customer.dashboard' }).catch(() => {
            router.push('/')
          })
        })
      }, 100)
    } else {
      // Gestion des erreurs de connexion sans rafraîchissement
      errors.general = result.error || 'Identifiants incorrects. Veuillez vérifier votre email et mot de passe.'

      // Messages d'erreur plus spécifiques selon le type d'erreur
      if (result.error?.includes('401') || result.error?.includes('Unauthorized') || result.error?.includes('Invalid credentials')) {
        errors.general = 'Identifiants incorrects. Veuillez vérifier votre email et mot de passe.'
      } else if (result.error?.includes('429') || result.error?.includes('too many')) {
        errors.general = 'Trop de tentatives. Veuillez patienter quelques minutes.'
      } else if (result.error?.includes('network') || result.error?.includes('Network')) {
        errors.general = 'Problème de connexion. Vérifiez votre connexion internet.'
      }

      if (window.$toast) {
        window.$toast.error(errors.general, 'Erreur de connexion')
      }

      // Focus sur le champ email pour faciliter la correction
      document.getElementById('email')?.focus()
    }
  } catch (error) {
    console.error('Erreur lors de la connexion:', error)

    // Gestion d'erreurs plus détaillée
    let errorMessage = 'Une erreur est survenue. Veuillez réessayer.'

    if (error.response?.status === 401) {
      errorMessage = 'Identifiants incorrects. Veuillez vérifier votre email et mot de passe.'
    } else if (error.response?.status === 429) {
      errorMessage = 'Trop de tentatives. Veuillez patienter quelques minutes.'
    } else if (error.code === 'NETWORK_ERROR' || !error.response) {
      errorMessage = 'Problème de connexion. Vérifiez votre connexion internet.'
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }

    errors.general = errorMessage

    if (window.$toast) {
      window.$toast.error(errorMessage, 'Erreur de connexion')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Animation pour les champs d'erreur */
.border-red-300 {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

/* Animation d'apparition pour les messages d'erreur */
.rounded-xl.bg-red-50 {
  animation: fadeInScale 0.3s ease-out;
}

@keyframes fadeInScale {
  0% {
    opacity: 0;
    transform: scale(0.95) translateY(-10px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Empêcher tout comportement de formulaire par défaut */
form {
  contain: layout style;
}
</style>
