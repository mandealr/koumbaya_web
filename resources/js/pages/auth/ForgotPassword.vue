<template>
  <div class="min-h-screen bg-gradient-to-br from-koumbaya-primary/5 via-white to-koumbaya-primary/10 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center mb-8">
        <img class="h-20 w-auto" :src="logoUrl" alt="Koumbaya" />
      </div>
      <h2 class="text-center text-4xl font-bold text-gray-900 mb-3">
        Mot de passe oublié ?
      </h2>
      <p class="text-center text-lg text-gray-600 mb-8">
        Entrez votre adresse email ou votre numéro de téléphone
      </p>
    </div>

    <div class="mt-12 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="koumbaya-card bg-white/80 backdrop-blur-sm border-0 shadow-2xl">
        <div class="koumbaya-card-body p-8">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Toggle entre email et téléphone -->
            <div class="flex justify-center space-x-2 p-1 bg-gray-100 rounded-lg mb-6">
              <button
                type="button"
                @click="resetMethod = 'email'"
                :class="[
                  'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200',
                  resetMethod === 'email'
                    ? 'bg-white text-koumbaya-primary shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                <EnvelopeIcon class="w-4 h-4 inline-block mr-2" />
                Email
              </button>
              <button
                type="button"
                @click="resetMethod = 'phone'"
                :class="[
                  'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200',
                  resetMethod === 'phone'
                    ? 'bg-white text-koumbaya-primary shadow-sm'
                    : 'text-gray-600 hover:text-gray-900'
                ]"
              >
                <PhoneIcon class="w-4 h-4 inline-block mr-2" />
                Téléphone
              </button>
            </div>

            <!-- Email input -->
            <div v-if="resetMethod === 'email'" class="koumbaya-form-group">
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
                :class="{
                  'border-red-300 focus:border-red-500 focus:ring-red-500/10': errors.email
                }"
                placeholder="exemple@koumbaya.com"
              />
              <p v-if="errors.email" class="mt-2 text-sm text-red-600 flex items-center">
                <ExclamationCircleIcon class="w-4 h-4 mr-1" />
                {{ errors.email }}
              </p>
            </div>

            <!-- Phone input -->
            <div v-if="resetMethod === 'phone'" class="koumbaya-form-group">
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
                  <h3 class="text-sm font-semibold text-green-800">Code envoyé !</h3>
                  <div class="mt-1 text-sm text-green-700">
                    Un code de réinitialisation a été envoyé à {{ resetMethod === 'email' ? form.email : form.phone }}
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
                <span>Envoi en cours...</span>
              </span>
              <span v-else>Envoyer le code</span>
            </button>

            <!-- Back to login -->
            <div class="text-center">
              <router-link
                to="/login"
                class="text-sm font-semibold text-koumbaya-primary hover:text-koumbaya-primary-dark transition-colors"
              >
                ← Retour à la connexion
              </router-link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import logoUrl from '@/assets/logo.png'
import {
  EnvelopeIcon,
  PhoneIcon,
  ExclamationCircleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'
import PhoneInput from '@/components/PhoneInput.vue'
import { useApi } from '@/composables/api'

const router = useRouter()
const { post } = useApi()

const resetMethod = ref('email')
const phoneValid = ref(false)
const loading = ref(false)
const success = ref(false)

const form = reactive({
  email: '',
  phone: ''
})

const errors = reactive({
  email: '',
  phone: '',
  general: ''
})

const onPhoneChange = (phoneData) => {
  phoneValid.value = phoneData.isValid
  form.phone = phoneData.fullNumber
  
  if (phoneData.isValid) {
    errors.phone = ''
  }
}

const validateForm = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  let isValid = true

  if (resetMethod.value === 'email') {
    if (!form.email) {
      errors.email = 'L\'adresse email est obligatoire'
      isValid = false
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
      errors.email = 'Format d\'email invalide'
      isValid = false
    }
  } else {
    if (!form.phone) {
      errors.phone = 'Le numéro de téléphone est obligatoire'
      isValid = false
    } else if (!phoneValid.value) {
      errors.phone = 'Format de téléphone invalide'
      isValid = false
    }
  }

  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) return

  loading.value = true
  errors.general = ''
  success.value = false

  try {
    const identifier = resetMethod.value === 'email' ? form.email : form.phone
    
    const response = await post('/otp/send', {
      identifier: identifier,
      purpose: 'password_reset'
    })

    if (response.success) {
      success.value = true
      
      // Redirection vers la page de vérification OTP après 2 secondes
      setTimeout(() => {
        router.push({
          name: 'reset-password-verify',
          query: {
            identifier: identifier,
            method: resetMethod.value
          }
        })
      }, 2000)
    } else {
      errors.general = response.message || 'Une erreur est survenue'
    }
  } catch (error) {
    console.error('Erreur lors de l\'envoi du code:', error)
    errors.general = 'Une erreur est survenue lors de l\'envoi du code'
  } finally {
    loading.value = false
  }
}
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
</style>