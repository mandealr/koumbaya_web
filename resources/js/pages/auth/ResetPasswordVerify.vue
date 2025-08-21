<template>
  <div class="min-h-screen bg-gradient-to-br from-koumbaya-primary/5 via-white to-koumbaya-primary/10 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center mb-8">
        <img class="h-20 w-auto" :src="logoUrl" alt="Koumbaya" />
      </div>
      <h2 class="text-center text-4xl font-bold text-gray-900 mb-3">
        Vérification du code
      </h2>
      <p class="text-center text-lg text-gray-600 mb-8">
        Entrez le code reçu {{ method === 'email' ? 'par email' : 'par SMS' }}
      </p>
    </div>

    <div class="mt-12 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="koumbaya-card bg-white/80 backdrop-blur-sm border-0 shadow-2xl">
        <div class="koumbaya-card-body p-8">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Code OTP -->
            <div class="koumbaya-form-group">
              <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                Code de vérification
              </label>
              <input
                id="otp"
                name="otp"
                type="text"
                v-model="form.otp"
                required
                maxlength="6"
                class="koumbaya-input bg-white/50 border-gray-200 focus:border-koumbaya-primary focus:ring-4 focus:ring-koumbaya-primary/10 rounded-xl transition-all duration-200 text-black text-center text-2xl letter-spacing-wide"
                :class="{
                  'border-red-300 focus:border-red-500 focus:ring-red-500/10': errors.otp
                }"
                placeholder="123456"
              />
              <p v-if="errors.otp" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                {{ errors.otp }}
              </p>
            </div>

            <!-- Nouveau mot de passe -->
            <div class="koumbaya-form-group">
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Nouveau mot de passe
              </label>
              <div class="relative">
                <input
                  id="password"
                  name="password"
                  :type="showPassword ? 'text' : 'password'"
                  v-model="form.password"
                  required
                  class="koumbaya-input bg-white/50 border-gray-200 focus:border-koumbaya-primary focus:ring-4 focus:ring-koumbaya-primary/10 rounded-xl transition-all duration-200 pr-12 text-black"
                  :class="{
                    'border-red-300 focus:border-red-500 focus:ring-red-500/10': errors.password
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
            </div>

            <!-- Confirmation mot de passe -->
            <div class="koumbaya-form-group">
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirmer le mot de passe
              </label>
              <div class="relative">
                <input
                  id="password_confirmation"
                  name="password_confirmation"
                  :type="showPasswordConfirmation ? 'text' : 'password'"
                  v-model="form.password_confirmation"
                  required
                  class="koumbaya-input bg-white/50 border-gray-200 focus:border-koumbaya-primary focus:ring-4 focus:ring-koumbaya-primary/10 rounded-xl transition-all duration-200 pr-12 text-black"
                  :class="{
                    'border-red-300 focus:border-red-500 focus:ring-red-500/10': errors.password_confirmation
                  }"
                  placeholder="••••••••"
                />
                <button
                  type="button"
                  @click="showPasswordConfirmation = !showPasswordConfirmation"
                  class="absolute inset-y-0 right-0 pr-4 flex items-center hover:bg-gray-100 rounded-r-xl transition-colors"
                >
                  <EyeIcon v-if="!showPasswordConfirmation" class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                  <EyeSlashIcon v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                </button>
              </div>
              <p v-if="errors.password_confirmation" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                {{ errors.password_confirmation }}
              </p>
            </div>

            <!-- Error Message -->
            <div v-if="errors.general" class="rounded-xl bg-red-50 p-4 border border-red-200">
              <div class="flex">
                <ExclamationCircleIcon class="h-5 w-5 text-red-400 mt-0.5" />
                <div class="ml-3">
                  <h3 class="text-sm font-semibold text-red-800">Erreur</h3>
                  <div class="mt-1 text-sm text-red-700">{{ errors.general }}</div>
                </div>
              </div>
            </div>

            <!-- Success Message -->
            <div v-if="success" class="rounded-xl bg-green-50 p-4 border border-green-200">
              <div class="flex">
                <CheckCircleIcon class="h-5 w-5 text-green-400 mt-0.5" />
                <div class="ml-3">
                  <h3 class="text-sm font-semibold text-green-800">Mot de passe réinitialisé !</h3>
                  <div class="mt-1 text-sm text-green-700">
                    Redirection vers la page de connexion...
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-[#0099cc] hover:bg-[#0088bb] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
            >
              <span v-if="loading" class="flex items-center space-x-2">
                <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                <span>Réinitialisation...</span>
              </span>
              <span v-else>Réinitialiser le mot de passe</span>
            </button>

            <!-- Renvoyer le code -->
            <div class="text-center">
              <button
                type="button"
                @click="resendCode"
                :disabled="resendLoading || cooldownTime > 0"
                class="text-sm font-semibold text-koumbaya-primary hover:text-koumbaya-primary-dark transition-colors disabled:opacity-50"
              >
                <span v-if="cooldownTime > 0">
                  Renvoyer le code ({{ cooldownTime }}s)
                </span>
                <span v-else-if="resendLoading">Envoi...</span>
                <span v-else>Renvoyer le code</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import logoUrl from '@/assets/logo.png'
import {
  ExclamationCircleIcon,
  CheckCircleIcon,
  EyeIcon,
  EyeSlashIcon
} from '@heroicons/vue/24/outline'
import { useApi } from '@/composables/api'

const router = useRouter()
const route = useRoute()
const { post } = useApi()

const identifier = ref(route.query.identifier || '')
const method = ref(route.query.method || 'email')
const loading = ref(false)
const success = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const resendLoading = ref(false)
const cooldownTime = ref(0)
const cooldownTimer = ref(null)

const form = reactive({
  otp: '',
  password: '',
  password_confirmation: ''
})

const errors = reactive({
  otp: '',
  password: '',
  password_confirmation: '',
  general: ''
})

const validateForm = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  let isValid = true

  if (!form.otp) {
    errors.otp = 'Le code est obligatoire'
    isValid = false
  } else if (form.otp.length !== 6) {
    errors.otp = 'Le code doit contenir 6 chiffres'
    isValid = false
  }

  if (!form.password) {
    errors.password = 'Le mot de passe est obligatoire'
    isValid = false
  } else if (form.password.length < 8) {
    errors.password = 'Le mot de passe doit contenir au moins 8 caractères'
    isValid = false
  }

  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Les mots de passe ne correspondent pas'
    isValid = false
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) return

  loading.value = true
  errors.general = ''
  success.value = false

  try {
    // D'abord vérifier le code OTP
    const otpResponse = await post('/otp/verify', {
      identifier: identifier.value,
      otp: form.otp,
      purpose: 'password_reset'
    })

    if (!otpResponse.success) {
      errors.general = otpResponse.message || 'Code invalide'
      loading.value = false
      return
    }

    // Ensuite réinitialiser le mot de passe
    const resetResponse = await post('/reset-password', {
      identifier: identifier.value,
      otp: form.otp,
      password: form.password,
      password_confirmation: form.password_confirmation
    })

    if (resetResponse.success) {
      success.value = true
      
      // Redirection vers la page de connexion après 2 secondes
      setTimeout(() => {
        router.push({ name: 'login' })
      }, 2000)
    } else {
      errors.general = resetResponse.message || 'Une erreur est survenue'
    }
  } catch (error) {
    console.error('Erreur lors de la réinitialisation:', error)
    errors.general = 'Une erreur est survenue lors de la réinitialisation'
  } finally {
    loading.value = false
  }
}

const resendCode = async () => {
  resendLoading.value = true
  
  try {
    const response = await post('/otp/resend', {
      identifier: identifier.value,
      purpose: 'password_reset'
    })

    if (response.success) {
      startCooldown()
    } else {
      errors.general = response.message || 'Erreur lors du renvoi du code'
    }
  } catch (error) {
    console.error('Erreur lors du renvoi:', error)
    errors.general = 'Erreur lors du renvoi du code'
  } finally {
    resendLoading.value = false
  }
}

const startCooldown = () => {
  cooldownTime.value = 60
  cooldownTimer.value = setInterval(() => {
    cooldownTime.value--
    if (cooldownTime.value <= 0) {
      clearInterval(cooldownTimer.value)
    }
  }, 1000)
}

onMounted(() => {
  if (!identifier.value) {
    router.push({ name: 'forgot-password' })
  }
})

onUnmounted(() => {
  if (cooldownTimer.value) {
    clearInterval(cooldownTimer.value)
  }
})
</script>

<style scoped>
.koumbaya-card {
  border-radius: 24px;
}

.koumbaya-input {
  height: 56px;
  padding: 16px 20px;
}

.koumbaya-form-group {
  margin-bottom: 24px;
}

.letter-spacing-wide {
  letter-spacing: 0.2em;
}
</style>