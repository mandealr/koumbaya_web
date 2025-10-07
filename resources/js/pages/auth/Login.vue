<template>
  <div class="min-h-screen bg-gradient-to-br from-koumbaya-primary/5 via-white to-koumbaya-primary/10 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center mb-8">
          <router-link to="/">
            <img class="h-20 w-auto hover:opacity-80 transition-opacity" :src="logoUrl" alt="Koumbaya" />
          </router-link>
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
          <!-- Message de succès d'inscription -->
          <div v-if="registrationSuccess" class="mb-6 rounded-xl bg-gradient-to-r from-green-50 to-blue-50 border-2 border-green-200 p-4 shadow-lg">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3 flex-1">
                <div class="text-sm font-semibold text-green-800 mb-2">
                    Inscription réussie ! Bienvenue {{ registrationSuccess.first_name }}
                </div>
                <div class="text-sm text-green-700 leading-relaxed">
                  {{ registrationSuccess.verification_message }}
                </div>
                <div v-if="registrationSuccess.email" class="mt-2 text-xs text-green-600 bg-green-100 rounded-lg p-2">
                  <div class="font-medium mb-1">Que faire maintenant :</div>
                  <ul class="space-y-1 list-disc list-inside ml-2">
                    <li>Consultez votre boîte email : <strong>{{ registrationSuccess.email }}</strong></li>
                    <li>Cliquez sur le lien de vérification dans l'email</li>
                    <li>Revenez ici pour vous connecter avec vos identifiants</li>
                  </ul>
                </div>
              </div>
              <button
                @click="clearRegistrationMessage"
                class="flex-shrink-0 ml-2 text-green-400 hover:text-green-600 transition-colors"
                title="Fermer ce message"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Toggle entre email et téléphone -->
            <div class="flex justify-center space-x-2 p-1 bg-gray-100 rounded-lg mb-6">
              <button
                type="button"
                @click="loginMethod = 'email'"
                :class="[
                  'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200',
                  loginMethod === 'email'
                    ? 'bg-white text-koumbaya-primary shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                <EnvelopeIcon class="w-4 h-4 inline-block mr-2" />
                Email
              </button>
              <button
                type="button"
                @click="loginMethod = 'phone'"
                :class="[
                  'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200',
                  loginMethod === 'phone'
                    ? 'bg-white text-koumbaya-primary shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                <PhoneIcon class="w-4 h-4 inline-block mr-2" />
                Téléphone
              </button>
            </div>
            <!-- Zone d'erreur améliorée -->
            <div v-if="errors.general" class="rounded-xl bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-200 p-4 shadow-lg">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <ExclamationCircleIcon class="w-6 h-6 text-red-500 animate-pulse" />
                </div>
                <div class="ml-3 flex-1">
                  <div class="text-sm text-red-800 font-semibold leading-relaxed">
                    {{ errors.general }}
                  </div>
                  <!-- Conseils selon le type d'erreur -->
                  <div v-if="errors.general.includes('Identifiants incorrects')" class="mt-2 text-xs text-red-600 bg-red-100 rounded-lg p-2">
                    <div class="font-medium mb-1">💡 Conseils :</div>
                    <ul class="space-y-1 list-disc list-inside ml-2">
                      <li>Vérifiez que votre adresse email est correcte</li>
                      <li>Assurez-vous que les majuscules/minuscules sont respectées</li>
                      <li>Essayez de réinitialiser votre mot de passe si nécessaire</li>
                    </ul>
                  </div>
                  <div v-else-if="errors.general.includes('connexion internet')" class="mt-2 text-xs text-red-600 bg-red-100 rounded-lg p-2">
                    <div class="font-medium mb-1">💡 Conseils :</div>
                    <ul class="space-y-1 list-disc list-inside ml-2">
                      <li>Vérifiez votre connexion WiFi ou données mobiles</li>
                      <li>Essayez de rafraîchir la page</li>
                      <li>Contactez votre fournisseur internet si le problème persiste</li>
                    </ul>
                  </div>
                  <div v-else-if="errors.general.includes('Trop de tentatives')" class="mt-2 text-xs text-red-600 bg-red-100 rounded-lg p-2">
                    <div class="font-medium mb-1">💡 Que faire :</div>
                    <ul class="space-y-1 list-disc list-inside ml-2">
                      <li>Attendez 15 minutes avant de réessayer</li>
                      <li>Utilisez ce temps pour vérifier vos identifiants</li>
                      <li>Réinitialisez votre mot de passe si nécessaire</li>
                    </ul>
                  </div>
                </div>
                <button
                  @click="errors.general = ''"
                  class="flex-shrink-0 ml-2 text-red-400 hover:text-red-600 transition-colors"
                  title="Fermer ce message"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Email input -->
            <div v-if="loginMethod === 'email'" class="koumbaya-form-group">
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
                class="koumbaya-input bg-white/50 border-gray-200 focus:border-koumbaya-primary focus:ring-4 focus:ring-koumbaya-primary/10 rounded-xl transition-all duration-200" style="color: #5f5f5f"
                :class="{
                  'border-red-300 focus:border-red-500 focus:ring-red-500/10': errors.email,
                  'border-blue-300 focus:border-blue-500': !errors.email && form.email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
                }"
                placeholder="exemple@koumbaya.com"
              />
              <p v-if="errors.email" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                {{ errors.email }}
              </p>
              <p v-else-if="!errors.email && form.email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)" class="mt-2 text-sm text-blue-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                ✓ Adresse email valide
              </p>
            </div>

            <!-- Phone input -->
            <div v-if="loginMethod === 'phone'" class="koumbaya-form-group">
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                <div class="flex items-center">
                  <PhoneIcon class="w-4 h-4 mr-2 text-gray-500 flex-shrink-0" />
                  <span>Numéro de téléphone</span>
                </div>
              </label>
              <PhoneInput
                ref="phoneInputRef"
                v-model="form.phone"
                :preferred-countries="['ga', 'cm', 'ci', 'cg', 'cf', 'td', 'gq', 'bf', 'bj', 'tg', 'fr', 'ca']"
                initial-country="ga"
                @phone-change="onPhoneChange"
              />
              <p v-if="errors.phone" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                {{ errors.phone }}
              </p>
              <p v-else-if="phoneValid" class="mt-2 text-sm text-blue-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                ✓ Numéro de téléphone valide
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
                  class="koumbaya-input bg-white/50 border-gray-200 focus:border-koumbaya-primary focus:ring-4 focus:ring-koumbaya-primary/10 rounded-xl transition-all duration-200 pr-12" style="color: #5f5f5f"
                  :class="{
                    'border-red-300 focus:border-red-500 focus:ring-red-500/10': errors.password,
                    'border-blue-300 focus:border-blue-500': !errors.password && form.password && form.password.length >= 6
                  }"
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
              <p v-else-if="!errors.password && form.password && form.password.length >= 6" class="mt-2 text-sm text-blue-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                ✓ Mot de passe valide
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

              <router-link
                to="/forgot-password"
                class="text-sm font-semibold text-koumbaya-primary hover:text-koumbaya-primary-dark transition-colors"
              >
                Mot de passe oublié ?
              </router-link>
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
            <button
              @click="loginWithGoogle"
              type="button"
              :disabled="loading"
              class="flex items-center justify-center px-4 py-3 border border-gray-200 rounded-xl shadow-sm bg-white hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 group whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5 text-gray-600 group-hover:text-gray-700 flex-shrink-0" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              <span class="ml-2 text-sm font-medium text-gray-700">Google</span>
            </button>

            <button
              @click="loginWithFacebook"
              type="button"
              :disabled="loading"
              class="flex items-center justify-center px-4 py-3 border border-gray-200 rounded-xl shadow-sm bg-white hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 group whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5 text-blue-600 group-hover:text-blue-700 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
              <span class="ml-2 text-sm font-medium text-gray-700">Facebook</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import logoUrl from '@/assets/logo.png'
import {
  EyeIcon,
  EyeSlashIcon,
  EnvelopeIcon,
  LockClosedIcon,
  ArrowRightOnRectangleIcon,
  ExclamationCircleIcon,
  PhoneIcon
} from '@heroicons/vue/24/outline'
import PhoneInput from '@/components/PhoneInput.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  phone: '',
  password: '',
  rememberMe: false
})

const loginMethod = ref('email') // 'email' or 'phone'
const phoneInputRef = ref(null)
const phoneValid = ref(false)

const errors = reactive({
  email: '',
  phone: '',
  password: '',
  general: ''
})

// Gestion du changement de téléphone
const onPhoneChange = (phoneData) => {
  console.log('Login phone change:', phoneData) // Debug
  phoneValid.value = phoneData.isValid
  form.phone = phoneData.fullNumber

  // Clear error if phone is valid
  if (phoneData.isValid && phoneData.fullNumber) {
    errors.phone = ''
  }
}

const loading = ref(false)
const showPassword = ref(false)
const registrationSuccess = ref(null)

const validateForm = () => {
  let isValid = true

  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  if (loginMethod.value === 'email') {
    // Email validation avec messages détaillés
    if (!form.email) {
      errors.email = 'L\'adresse email est obligatoire'
      isValid = false
    } else if (!form.email.includes('@')) {
      errors.email = 'L\'adresse email doit contenir le symbole @'
      isValid = false
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
      errors.email = 'Format d\'email invalide (exemple: nom@domaine.com)'
      isValid = false
    } else if (form.email.length > 100) {
      errors.email = 'L\'adresse email est trop longue (max 100 caractères)'
      isValid = false
    }
  } else {
    // Phone validation
    if (!form.phone || form.phone.trim() === '') {
      errors.phone = '📱 Le numéro de téléphone est obligatoire'
      isValid = false
    } else if (!phoneValid.value && form.phone.length > 3) {
      errors.phone = 'Format de téléphone invalide'
      isValid = false
    }
  }

  // Password validation avec messages détaillés
  if (!form.password) {
    errors.password = 'Le mot de passe est obligatoire'
    isValid = false
  } else if (form.password.length < 6) {
    errors.password = 'Le mot de passe doit contenir au moins 6 caractères'
    isValid = false
  } else if (form.password.length > 50) {
    errors.password = 'Le mot de passe est trop long (max 50 caractères)'
    isValid = false
  } else if (form.password.includes(' ')) {
    errors.password = 'Le mot de passe ne doit pas contenir d\'espaces'
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
    const credentials = {
      password: form.password
    }

    if (loginMethod.value === 'email') {
      credentials.email = form.email
    } else {
      credentials.phone = form.phone
    }

    const result = await authStore.login(credentials)

    if (result.success) {
      // Success toast avec informations personnalisées
      if (window.$toast) {
        const userName = authStore.user?.name || authStore.user?.first_name || 'utilisateur'
        window.$toast.success(`🎉 Bienvenue ${userName} ! Redirection en cours...`, '✅ Connexion réussie')
      }

      // Debug: Log user data
      console.log('User data après connexion:', authStore.user)

      // Gérer la redirection - priorité au paramètre redirect, sinon logique par défaut
      const redirectParam = route.query.redirect
      const action = route.query.action

      setTimeout(() => {
        if (redirectParam && (action === 'participate' || action === 'wishlist')) {
          // L'utilisateur vient de la page publique d'un produit pour participer ou ajouter aux favoris
          // Rediriger vers la page produit de l'espace client
          const productId = redirectParam.match(/\/products\/(\d+)/)?.[1]
          if (productId) {
            router.push({ name: 'customer.product.detail', params: { id: productId } })
            return
          }
        }

        if (redirectParam) {
          // Redirection personnalisée
          router.push(redirectParam)
          return
        }

        // Use the centralized redirect logic
        const redirectTo = authStore.getDefaultRedirect()

        console.log('Redirection logic:', {
          user: authStore.user,
          redirectTo,
          isAdmin: authStore.isAdmin,
          isMerchant: authStore.isMerchant,
          isCustomer: authStore.isCustomer
        })

        router.push({ name: redirectTo }).catch(error => {
          console.error('Erreur de redirection vers', redirectTo, ':', error)
          // Fallback: try customer dashboard, then home
          router.push({ name: 'customer.dashboard' }).catch(() => {
            router.push('/')
          })
        })
      }, 100)
    } else {
      // Gestion des erreurs de connexion avec messages spécifiques et clairs
      const errorMsg = result.error || 'Erreur de connexion inconnue'

      // Messages d'erreur plus spécifiques et informatifs
      if (errorMsg.toLowerCase().includes('identifiants incorrects') ||
          errorMsg.toLowerCase().includes('invalid credentials') ||
          errorMsg.includes('401')) {
        errors.general = 'Identifiants incorrects. Vérifiez votre adresse email et votre mot de passe.'
        errors.email = 'Adresse email ou mot de passe incorrect'
        errors.password = 'Adresse email ou mot de passe incorrect'
      } else if (errorMsg.toLowerCase().includes('trop de tentatives') ||
                 errorMsg.toLowerCase().includes('too many') ||
                 errorMsg.includes('429')) {
        errors.general = 'Trop de tentatives de connexion. Veuillez patienter 15 minutes avant de réessayer.'
      } else if (errorMsg.toLowerCase().includes('network') ||
                 errorMsg.toLowerCase().includes('réseau') ||
                 errorMsg.toLowerCase().includes('connexion')) {
        errors.general = 'Problème de connexion internet. Vérifiez votre connexion et réessayez.'
      } else if (errorMsg.toLowerCase().includes('email') && errorMsg.toLowerCase().includes('verify')) {
        errors.general = 'Votre compte n\'est pas encore vérifié. Vérifiez votre boîte email et cliquez sur le lien de vérification.'
      } else if (errorMsg.toLowerCase().includes('blocked') || errorMsg.toLowerCase().includes('suspendu')) {
        errors.general = 'Votre compte est temporairement suspendu. Contactez le support client.'
      } else {
        errors.general = `${errorMsg}`
      }

      if (window.$toast) {
        window.$toast.error(errors.general, '🚫 Erreur de connexion')
      }

      // Focus sur le champ approprié selon l'erreur
      setTimeout(() => {
        if (errors.email) {
          document.getElementById('email')?.focus()
        } else {
          document.getElementById('email')?.focus()
        }
      }, 100)
    }
  } catch (error) {
    console.error('Erreur lors de la connexion:', error)

    // Gestion d'erreurs système avec messages clairs
    let errorMessage = 'Une erreur technique est survenue. Veuillez réessayer.'

    if (error.response?.status === 401) {
      errorMessage = '🚫 Identifiants incorrects. Vérifiez votre adresse email et votre mot de passe.'
      errors.email = 'Adresse email ou mot de passe incorrect'
      errors.password = 'Adresse email ou mot de passe incorrect'
    } else if (error.response?.status === 422) {
      // Erreurs de validation détaillées
      if (error.response.data?.errors) {
        const validationErrors = error.response.data.errors
        if (validationErrors.email) {
          errors.email = validationErrors.email[0]
        }
        if (validationErrors.password) {
          errors.password = validationErrors.password[0]
        }
        errorMessage = '⚠️ Veuillez corriger les erreurs ci-dessous.'
      } else {
        errorMessage = '⚠️ Données invalides. Vérifiez vos informations.'
      }
    } else if (error.response?.status === 429) {
      errorMessage = '⏳ Trop de tentatives de connexion. Veuillez patienter 15 minutes avant de réessayer.'
    } else if (error.response?.status === 500) {
      errorMessage = '🔧 Erreur du serveur. Nos équipes sont notifiées. Veuillez réessayer dans quelques minutes.'
    } else if (error.response?.status === 503) {
      errorMessage = '⚙️ Service temporairement indisponible. Maintenance en cours.'
    } else if (error.code === 'NETWORK_ERROR' || !error.response) {
      errorMessage = '🌐 Impossible de joindre le serveur. Vérifiez votre connexion internet.'
    } else if (error.response?.data?.message) {
      errorMessage = `${error.response.data.message}`
    } else if (error.message) {
      errorMessage = `${error.message}`
    }

    errors.general = errorMessage

    // Toast avec titre approprié
    if (window.$toast) {
      let title = '🚫 Erreur de connexion'
      if (error.response?.status === 500) {
        title = '🔧 Erreur serveur'
      } else if (error.code === 'NETWORK_ERROR') {
        title = '🌐 Problème réseau'
      }
      window.$toast.error(errorMessage, title)
    }

    // Focus sur le champ approprié
    setTimeout(() => {
      if (errors.email) {
        document.getElementById('email')?.focus()
      } else if (errors.password) {
        document.getElementById('password')?.focus()
      } else {
        document.getElementById('email')?.focus()
      }
    }, 100)
  } finally {
    loading.value = false
  }
}

// Validation en temps réel pour améliorer l'UX
watch(() => form.email, (newEmail) => {
  if (errors.email && newEmail) {
    // Révalider seulement si il y avait une erreur et que l'utilisateur tape
    if (newEmail.includes('@') && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(newEmail)) {
      errors.email = '' // Clear error si maintenant valide
    }
  }
}, { debounce: 300 })

watch(() => form.password, (newPassword) => {
  if (errors.password && newPassword) {
    // Révalider seulement si il y avait une erreur et que l'utilisateur tape
    if (newPassword.length >= 6 && newPassword.length <= 50 && !newPassword.includes(' ')) {
      errors.password = '' // Clear error si maintenant valide
    }
  }
}, { debounce: 300 })

// Clear les erreurs générales quand l'utilisateur commence à retaper
watch([() => form.email, () => form.password], () => {
  if (errors.general) {
    errors.general = ''
  }
})

// Fonction pour effacer le message d'inscription
const clearRegistrationMessage = () => {
  registrationSuccess.value = null
  sessionStorage.removeItem('registration_success')
}

// Social login handlers
const loginWithGoogle = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/auth/google/redirect')
    const data = await response.json()

    if (data.redirect_url) {
      // Rediriger vers Google OAuth
      window.location.href = data.redirect_url
    } else {
      throw new Error('Impossible de rediriger vers Google')
    }
  } catch (error) {
    console.error('Erreur connexion Google:', error)
    errors.general = 'Erreur lors de la connexion avec Google'
    if (window.$toast) {
      window.$toast.error('Erreur lors de la connexion avec Google', '🚫 Erreur')
    }
    loading.value = false
  }
}

const loginWithFacebook = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/auth/facebook/redirect')
    const data = await response.json()

    if (data.redirect_url) {
      // Rediriger vers Facebook OAuth
      window.location.href = data.redirect_url
    } else {
      throw new Error('Impossible de rediriger vers Facebook')
    }
  } catch (error) {
    console.error('Erreur connexion Facebook:', error)
    errors.general = 'Erreur lors de la connexion avec Facebook'
    if (window.$toast) {
      window.$toast.error('Erreur lors de la connexion avec Facebook', '🚫 Erreur')
    }
    loading.value = false
  }
}

// Vérifier s'il y a des données d'inscription réussie
onMounted(() => {
  // Vérifier les paramètres de requête pour la redirection depuis l'inscription
  if (route.query.registered === 'true') {
    // Récupérer les données stockées dans sessionStorage
    const storedData = sessionStorage.getItem('registration_success')
    if (storedData) {
      try {
        registrationSuccess.value = JSON.parse(storedData)
        // Pré-remplir l'email s'il est disponible
        if (registrationSuccess.value.email) {
          form.email = registrationSuccess.value.email
        }
      } catch (error) {
        console.error('Erreur lors du parsing des données d\'inscription:', error)
      }
    } else if (route.query.email) {
      // Fallback : utiliser l'email des paramètres de requête
      form.email = route.query.email
      registrationSuccess.value = {
        email: route.query.email,
        first_name: 'utilisateur',
        verification_message: 'Un email de vérification a été envoyé à votre adresse. Veuillez consulter votre boîte email pour vérifier votre compte.',
        requires_verification: true
      }
    }
  }
})
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
