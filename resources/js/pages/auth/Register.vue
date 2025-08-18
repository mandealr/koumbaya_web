<template>
  <div class="min-h-screen bg-white flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full">
      <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2">
          <!-- Left Side - Branding -->
          <div class="bg-gradient-to-br from-[#0099cc] to-[#0088bb] p-8 lg:p-12 flex flex-col justify-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
              <div class="absolute -top-10 -right-10 w-40 h-40 bg-white rounded-full"></div>
              <div class="absolute -bottom-16 -left-16 w-60 h-60 bg-white rounded-full"></div>
            </div>

            <div class="relative z-10">
              <div class="flex items-center mb-8">
                <img
                  :src="logoWhiteUrl"
                  alt="Logo Koumbaya"
                  class="h-16 w-auto mr-4"
                />
              </div>

              <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                Rejoignez notre marketplace !
              </h2>

              <p class="text-blue-100 text-lg leading-relaxed mb-8">
                Créez votre compte hybride et profitez d'une expérience unique :
                <strong class="text-white">achetez malin</strong> et
                <strong class="text-white">vendez facilement</strong> sur la même plateforme.
              </p>

              <div class="space-y-4">
                <div class="flex items-center text-blue-100">
                  <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-sm">✓</span>
                  </div>
                  <span>Compte hybride acheteur-vendeur</span>
                </div>
                <div class="flex items-center text-blue-100">
                  <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-sm">✓</span>
                  </div>
                  <span>Produits authentiques à prix réduits</span>
                </div>
                <div class="flex items-center text-blue-100">
                  <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center mr-3">
                    <span class="text-white text-sm">✓</span>
                  </div>
                  <span>Système de notation communautaire</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Side - Registration Form -->
          <div class="p-8 lg:p-12">
            <div class="max-w-md mx-auto">
              <h3 class="text-3xl font-bold text-black mb-2">Créer un compte</h3>
              <p class="text-gray-600 mb-8">
                Déjà membre ?
                <router-link to="/login" class="text-[#0099cc] hover:text-[#0088bb] font-semibold">
                  Se connecter
                </router-link>
              </p>

              <!-- Social Login Buttons -->
              <div class="space-y-3 mb-8">
                <button
                  @click="loginWithFacebook"
                  class="w-full flex items-center justify-center px-4 py-3 border border-gray-200 rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 group"
                >
                  <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="#1877F2">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
                  Continuer avec Facebook
                </button>

                <button
                  @click="loginWithGoogle"
                  class="w-full flex items-center justify-center px-4 py-3 border border-gray-200 rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 group"
                >
                  <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                  </svg>
                  Continuer avec Google
                </button>

                <button
                  @click="loginWithApple"
                  class="w-full flex items-center justify-center px-4 py-3 border border-gray-200 rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 group"
                >
                  <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.017 0C8.396 0 8.025.044 8.025 2.551c0 1.983 1.396 3.329 3.396 3.329 3.329 0 3.329-2.551 3.329-2.551S14.75.044 12.017 0zm2.076 24c-1.825 0-2.631-1.078-4.076-1.078-1.513 0-2.513 1.078-4.025 1.078C3.759 24 0 18.444 0 12.861c0-5.516 3.759-9.861 8.944-9.861 1.825 0 2.631 1.078 4.076 1.078 1.513 0 2.513-1.078 4.025-1.078C19.278 3 24 7.861 24 12.861 24 18.444 19.278 24 14.093 24z"/>
                  </svg>
                  Continuer avec Apple
                </button>
              </div>

              <!-- Divider -->
              <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                  <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                  <span class="px-4 bg-white text-gray-500">ou créer avec email</span>
                </div>
              </div>

              <!-- Account Type Selection -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-900 mb-3">Type de compte</label>
                <div class="grid grid-cols-2 gap-3">
                  <div
                    @click="form.account_type = 'personal'"
                    class="relative cursor-pointer p-4 border-2 rounded-xl transition-all duration-200 hover:border-[#0099cc]/50"
                    :class="form.account_type === 'personal' ? 'border-[#0099cc] bg-[#0099cc]/5' : 'border-gray-200'"
                  >
                    <div class="flex items-center">
                      <div class="w-4 h-4 rounded-full border-2 mr-3 transition-colors"
                           :class="form.account_type === 'personal' ? 'border-[#0099cc] bg-[#0099cc]' : 'border-gray-300'">
                        <div v-if="form.account_type === 'personal'" class="w-full h-full rounded-full bg-white scale-50"></div>
                      </div>
                      <div>
                        <h4 class="font-semibold text-gray-900">Personnel</h4>
                        <p class="text-xs text-gray-600">Acheteur particulier</p>
                      </div>
                    </div>
                  </div>

                  <div
                    @click="form.account_type = 'business'"
                    class="relative cursor-pointer p-4 border-2 rounded-xl transition-all duration-200 hover:border-[#0099cc]/50"
                    :class="form.account_type === 'business' ? 'border-[#0099cc] bg-[#0099cc]/5' : 'border-gray-200'"
                  >
                    <div class="flex items-center">
                      <div class="w-4 h-4 rounded-full border-2 mr-3 transition-colors"
                           :class="form.account_type === 'business' ? 'border-[#0099cc] bg-[#0099cc]' : 'border-gray-300'">
                        <div v-if="form.account_type === 'business'" class="w-full h-full rounded-full bg-white scale-50"></div>
                      </div>
                      <div>
                        <h4 class="font-semibold text-gray-900">Business</h4>
                        <p class="text-xs text-gray-600">Vendeur professionnel</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Registration Form -->
              <form @submit.prevent="handleSubmit" class="space-y-5">
                <!-- Business Name (shown only for business accounts) -->
                <div v-if="form.account_type === 'business'" class="transition-all duration-300">
                  <label for="business_name" class="block text-sm font-semibold text-gray-900 mb-2">
                    Nom de l'entreprise *
                  </label>
                  <input
                    id="business_name"
                    v-model="form.business_name"
                    type="text"
                    required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                    :class="{ 'border-red-300 bg-red-50': errors.business_name }"
                    placeholder="Ma Super Entreprise"
                  />
                  <p v-if="errors.business_name" class="mt-2 text-sm text-red-600">{{ errors.business_name }}</p>
                </div>

                <!-- Name Fields -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label for="first_name" class="block text-sm font-semibold text-gray-900 mb-2">
                      Prénom *
                    </label>
                    <input
                      id="first_name"
                      v-model="form.first_name"
                      type="text"
                      required
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                      :class="{ 'border-red-300 bg-red-50': errors.first_name }"
                      placeholder="Jean"
                    />
                    <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name }}</p>
                  </div>

                  <div>
                    <label for="last_name" class="block text-sm font-semibold text-gray-900 mb-2">
                      Nom *
                    </label>
                    <input
                      id="last_name"
                      v-model="form.last_name"
                      type="text"
                      required
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                      :class="{ 'border-red-300 bg-red-50': errors.last_name }"
                      placeholder="Dupont"
                    />
                    <p v-if="errors.last_name" class="mt-1 text-sm text-red-600">{{ errors.last_name }}</p>
                  </div>
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">
                    Adresse email *
                  </label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                    :class="{ 'border-red-300 bg-red-50': errors.email }"
                    placeholder="jean@exemple.com"
                  />
                  <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                </div>

                <!-- Phone -->
                <div>
                  <label for="phone" class="block text-sm font-semibold text-gray-900 mb-2">
                    Numéro de téléphone *
                  </label>
                  <div class="phone-input-container" :class="{ 'has-error': errors.phone }">
                    <div class="flex">
                      <!-- Country Selector -->
                      <div class="relative">
                        <select
                          v-model="selectedCountryCode"
                          @change="onCountryChanged"
                          class="appearance-none bg-white border border-gray-200 rounded-l-xl px-3 py-3 pr-8 focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-sm font-medium"
                          :class="{ 'border-red-300 bg-red-50': errors.phone }"
                        >
                          <option value="GA">🇬🇦 +241</option>
                          <option value="FR">🇫🇷 +33</option>
                          <option value="CA">🇨🇦 +1</option>
                          <option value="CM">🇨🇲 +237</option>
                          <option value="CI">🇨🇮 +225</option>
                          <option value="SN">🇸🇳 +221</option>
                          <option value="BF">🇧🇫 +226</option>
                          <option value="ML">🇲🇱 +223</option>
                          <option value="NE">🇳🇪 +227</option>
                          <option value="TG">🇹🇬 +228</option>
                          <option value="BJ">🇧🇯 +229</option>
                          <option value="GN">🇬🇳 +224</option>
                          <option value="MR">🇲🇷 +222</option>
                          <option value="TD">🇹🇩 +235</option>
                          <option value="CF">🇨🇫 +236</option>
                          <option value="CG">🇨🇬 +242</option>
                          <option value="CD">🇨🇩 +243</option>
                          <option value="AO">🇦🇴 +244</option>
                          <option value="GQ">🇬🇶 +240</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                          <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                        </div>
                      </div>

                      <!-- Phone Number Input -->
                      <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        :placeholder="getPhonePlaceholder()"
                        required
                        class="flex-1 px-4 py-3 border-l-0 border border-gray-200 rounded-r-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                        :class="{ 'border-red-300 bg-red-50': errors.phone }"
                        @input="onPhoneInput"
                        @blur="validatePhoneNumber"
                      />
                    </div>
                  </div>
                  <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                </div>

                <!-- Country Selection -->
                <div>
                  <label for="country" class="block text-sm font-semibold text-gray-900 mb-2">
                    Pays *
                  </label>
                  <div class="relative">
                    <select
                      id="country"
                      v-model="form.country_id"
                      required
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all appearance-none bg-white text-black"
                      :class="{ 'border-red-300 bg-red-50': errors.country_id }"
                      :disabled="countriesLoading"
                    >
                      <option value="" disabled>Sélectionnez votre pays</option>
                      <option
                        v-for="country in countries"
                        :key="country.id"
                        :value="country.id"
                      >
                        {{ country.name }}
                      </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                      </svg>
                    </div>
                  </div>
                  <p v-if="errors.country_id" class="mt-1 text-sm text-red-600">{{ errors.country_id }}</p>
                  <p v-if="countriesLoading" class="mt-1 text-sm text-gray-500">Chargement des pays...</p>
                </div>

                <!-- Location Fields - only for business accounts -->
                <div v-if="form.account_type == 'business'" class="grid grid-cols-2 gap-4 transition-all duration-300">
                  <div>
                    <label for="city" class="block text-sm font-semibold text-gray-900 mb-2">
                      Ville *
                    </label>
                    <input
                      id="city"
                      v-model="form.city"
                      type="text"
                      :required="form.account_type === 'business'"
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                      :class="{ 'border-red-300 bg-red-50': errors.city }"
                      placeholder="Libreville"
                    />
                    <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city }}</p>
                  </div>

                  <div>
                    <label for="address" class="block text-sm font-semibold text-gray-900 mb-2">
                      Adresse
                    </label>
                    <input
                      id="address"
                      v-model="form.address"
                      type="text"
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                      :class="{ 'border-red-300 bg-red-50': errors.address }"
                      placeholder="123 Rue Example"
                    />
                    <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
                  </div>
                </div>

                <!-- Password Fields -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">
                      Mot de passe *
                    </label>
                    <div class="relative">
                      <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                        :class="{ 'border-red-300 bg-red-50': errors.password }"
                        placeholder="••••••••"
                      />
                      <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                        <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                        <EyeSlashIcon v-else class="h-5 w-5" />
                      </button>
                    </div>
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
                  </div>

                  <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-900 mb-2">
                      Confirmer *
                    </label>
                    <div class="relative">
                      <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        required
                        class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
                        :class="{ 'border-red-300 bg-red-50': errors.password_confirmation }"
                        placeholder="••••••••"
                      />
                      <button
                        type="button"
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                        <EyeIcon v-if="!showPasswordConfirmation" class="h-5 w-5" />
                        <EyeSlashIcon v-else class="h-5 w-5" />
                      </button>
                    </div>
                    <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
                  </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start space-x-3">
                  <div class="flex items-center h-6">
                    <input
                      id="terms"
                      v-model="form.terms"
                      type="checkbox"
                      class="h-4 w-4 text-[#0099cc] focus:ring-[#0099cc] border-gray-300 rounded"
                      :class="{ 'border-red-500': errors.terms }"
                    />
                  </div>
                  <label for="terms" class="text-sm text-gray-600 leading-6">
                    J'accepte les
                    <a href="#" class="text-[#0099cc] hover:text-[#0088bb] font-semibold">conditions d'utilisation</a>
                    et la
                    <a href="#" class="text-[#0099cc] hover:text-[#0088bb] font-semibold">politique de confidentialité</a>
                  </label>
                </div>
                <p v-if="errors.terms" class="text-sm text-red-600">{{ errors.terms }}</p>

                <!-- Error Message -->
                <div v-if="authStore.error" class="rounded-xl bg-red-50 p-4 border border-red-200">
                  <div class="flex">
                    <ExclamationTriangleIcon class="h-5 w-5 text-red-400 mt-0.5" />
                    <div class="ml-3">
                      <h3 class="text-sm font-semibold text-red-800">
                        Erreur d'inscription
                      </h3>
                      <div class="mt-1 text-sm text-red-700">
                        {{ authStore.error }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <button
                  type="submit"
                  :disabled="authStore.loading"
                  class="w-full bg-[#0099cc] hover:bg-[#0088bb] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
                >
                  <span v-if="authStore.loading" class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                    <span>Création en cours...</span>
                  </span>
                  <span v-else>Créer mon compte Koumbaya</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { EyeIcon, EyeSlashIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import socialAuth from '@/services/socialAuth'
const logoWhiteUrl = '/logo_white.png'
import { useApi } from '@/composables/api'

const router = useRouter()
const authStore = useAuthStore()
const { get } = useApi()


const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const countries = ref([])
const countriesLoading = ref(false)
const phoneValid = ref(false)
const selectedCountryCode = ref('GA') // Gabon par défaut

const form = reactive({
  account_type: 'personal',
  business_name: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  city: '',
  address: '',
  country_id: '',
  password: '',
  password_confirmation: '',
  terms: false
})

const errors = reactive({
  account_type: '',
  business_name: '',
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  city: '',
  address: '',
  country_id: '',
  password: '',
  password_confirmation: '',
  terms: ''
})

const validateForm = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })

  let isValid = true

  if (form.account_type === 'business' && !form.business_name.trim()) {
    errors.business_name = 'Le nom de l\'entreprise est requis pour un compte business'
    isValid = false
  }

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

  if (!form.phone) {
    errors.phone = 'Le numéro de téléphone est requis'
    isValid = false
  } else if (!phoneValid.value) {
    errors.phone = 'Format de téléphone invalide'
    isValid = false
  }

  if (form.account_type === 'business' && !form.city.trim()) {
    errors.city = 'La ville est requise pour un compte business'
    isValid = false
  }

  if (!form.country_id) {
    errors.country_id = 'Le pays est requis'
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

// Mapping des codes pays vers leurs indicatifs
const countryDialCodes = {
  'GA': '+241', 'FR': '+33', 'CA': '+1', 'CM': '+237', 'CI': '+225',
  'SN': '+221', 'BF': '+226', 'ML': '+223', 'NE': '+227', 'TG': '+228',
  'BJ': '+229', 'GN': '+224', 'MR': '+222', 'TD': '+235', 'CF': '+236',
  'CG': '+242', 'CD': '+243', 'AO': '+244', 'GQ': '+240'
}

// Placeholders spécifiques par pays
const getPhonePlaceholder = () => {
  const placeholders = {
    'GA': '01 23 45 67',
    'FR': '01 23 45 67 89',
    'CA': '(123) 456-7890',
    'CM': '6 77 12 34 56',
    'CI': '07 12 34 56 78',
    'SN': '77 123 45 67'
  }
  return placeholders[selectedCountryCode.value] || '12 34 56 78'
}

// Gestion de la saisie téléphone
const onPhoneInput = (event) => {
  const value = event.target.value
  form.phone = value
  validatePhoneNumber()
}

// Validation du numéro de téléphone
const validatePhoneNumber = () => {
  const phone = form.phone.trim()

  if (!phone) {
    phoneValid.value = false
    errors.phone = ''
    return
  }

  // Validation basique selon le pays sélectionné
  const dialCode = countryDialCodes[selectedCountryCode.value]
  let isValid = false

  // Si le numéro commence par l'indicatif du pays
  if (phone.startsWith(dialCode)) {
    isValid = phone.length >= dialCode.length + 8 // Indicatif + au moins 8 chiffres
  } else {
    // Numéro local (sans indicatif)
    isValid = phone.length >= 8 && phone.length <= 12
  }

  phoneValid.value = isValid

  if (isValid) {
    errors.phone = ''
  } else if (phone.length > 0) {
    errors.phone = `Format de téléphone invalide pour ${selectedCountryCode.value}`
  }
}

// Changement de pays
const onCountryChanged = () => {
  console.log('Pays sélectionné:', selectedCountryCode.value)
  validatePhoneNumber() // Re-valider avec le nouveau pays
}

const handleSubmit = async () => {
  if (!validateForm()) return

  authStore.clearError()

  const registrationData = {
    account_type: form.account_type,
    first_name: form.first_name.trim(),
    last_name: form.last_name.trim(),
    email: form.email.trim(),
    phone: form.phone.trim(),
    country_id: parseInt(form.country_id),
    password: form.password,
    password_confirmation: form.password_confirmation,
    // Logique correcte selon le type de compte
    can_sell: form.account_type === 'business',
    can_buy: true // Tous les comptes peuvent acheter
  }

  // Ajouter ville et adresse uniquement pour les comptes business
  if (form.account_type === 'business') {
    registrationData.city = form.city.trim()
    registrationData.address = form.address.trim() || null
  }

  if (form.account_type === 'business' && form.business_name.trim()) {
    registrationData.business_name = form.business_name.trim()
  }

  const result = await authStore.register(registrationData)

  if (result.success) {
    console.log('Inscription réussie, données utilisateur:', authStore.user)

    // Déconnecter immédiatement l'utilisateur pour forcer la vérification
    authStore.logout()

    // Préparer les données pour la page de connexion
    const accountType = form.account_type === 'business' ? 'marchand' : 'client'
    const verificationMessage = result.verification_type === 'email_link'
      ? `Un email de vérification a été envoyé à ${result.verification_sent_to}. Veuillez cliquer sur le lien dans votre email, puis vous connecter ci-dessous.`
      : `Un code de vérification a été envoyé à ${result.verification_sent_to}. Veuillez vérifier votre compte, puis vous connecter ci-dessous.`

    // Sauvegarder les informations dans sessionStorage pour la page de login
    sessionStorage.setItem('registration_success', JSON.stringify({
      email: form.email.trim(),
      first_name: result.user?.first_name || form.first_name,
      account_type: accountType,
      verification_message: verificationMessage,
      requires_verification: result.requires_verification || false
    }))

    console.log('Redirection vers la page de connexion après inscription')

    // Rediriger vers la page de connexion
    router.push({
      name: 'login',
      query: {
        registered: 'true',
        email: form.email.trim()
      }
    })
  } else {
    console.error('Erreur lors de l\'inscription:', result)

    // Afficher l'erreur dans un toast
    if (window.$toast) {
      const errorMessage = result.message || 'Une erreur est survenue lors de l\'inscription'
      window.$toast.error(errorMessage, 'Erreur d\'inscription')
    }
  }
}

// Social login methods
const loginWithFacebook = async () => {
  try {
    authStore.clearError()
    await socialAuth.loginWithFacebook()
  } catch (error) {
    authStore.setError(error.message || 'Erreur lors de la connexion avec Facebook')
  }
}

const loginWithGoogle = async () => {
  try {
    authStore.clearError()
    await socialAuth.loginWithGoogle()
  } catch (error) {
    authStore.setError(error.message || 'Erreur lors de la connexion avec Google')
  }
}

const loginWithApple = async () => {
  try {
    authStore.clearError()
    await socialAuth.loginWithApple()
  } catch (error) {
    authStore.setError(error.message || 'Erreur lors de la connexion avec Apple')
  }
}

// Load countries on component mount
const loadCountries = async () => {
  try {
    countriesLoading.value = true
    const response = await get('/countries')
    countries.value = response.countries || []

    // Set Gabon as default country
    const gabon = countries.value.find(country => country.name === 'Gabon')
    if (gabon && !form.country_id) {
      form.country_id = gabon.id
    }
  } catch (error) {
    console.error('Erreur lors du chargement des pays:', error)
  } finally {
    countriesLoading.value = false
  }
}

onMounted(() => {
  loadCountries()
})
</script>

<style>
/* Styles personnalisés pour le composant téléphone Koumbaya */
.phone-input-container {
  width: 100%;
}

.phone-input-container.has-error select,
.phone-input-container.has-error input {
  border-color: #fca5a5 !important;
  background-color: #fef2f2 !important;
}

.phone-input-container select:focus,
.phone-input-container input:focus {
  outline: none;
  border-color: #0099cc;
  box-shadow: 0 0 0 2px rgba(0, 153, 204, 0.2);
}

/* Styles personnalisés pour vue-tel-input avec le thème Koumbaya (si nécessaire plus tard) */
.vue-tel-input-koumbaya {
  border-radius: 0.75rem !important;
  border: 1px solid #e5e7eb !important;
  box-shadow: none !important;
}

.vue-tel-input-koumbaya:focus-within {
  border-color: #0099cc !important;
  box-shadow: 0 0 0 2px rgba(0, 153, 204, 0.2) !important;
}

.vue-tel-input-koumbaya.has-error {
  border-color: #fca5a5 !important;
  background-color: #fef2f2 !important;
}

.vue-tel-input-koumbaya .vti__dropdown {
  background-color: white !important;
  border: none !important;
  border-radius: 0.75rem 0 0 0.75rem !important;
  padding: 0.75rem 0.5rem !important;
}

.vue-tel-input-koumbaya .vti__dropdown:hover {
  background-color: #f9fafb !important;
}

.vue-tel-input-koumbaya .vti__dropdown-list {
  border-radius: 0.5rem !important;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
  border: 1px solid #e5e7eb !important;
  max-height: 200px !important;
  overflow-y: auto !important;
}

.vue-tel-input-koumbaya .vti__dropdown-item {
  padding: 0.5rem 0.75rem !important;
  transition: background-color 0.15s ease-in-out !important;
}

.vue-tel-input-koumbaya .vti__dropdown-item:hover,
.vue-tel-input-koumbaya .vti__dropdown-item.highlighted {
  background-color: #0099cc !important;
  color: white !important;
}

.vue-tel-input-koumbaya .vti__input {
  background-color: transparent !important;
  border: none !important;
  padding: 0.75rem 1rem !important;
  font-size: 1rem !important;
  color: #000000 !important;
  border-radius: 0 0.75rem 0.75rem 0 !important;
}

.vue-tel-input-koumbaya .vti__input:focus {
  outline: none !important;
  box-shadow: none !important;
}

.vue-tel-input-koumbaya .vti__input::placeholder {
  color: #9ca3af !important;
}

.vue-tel-input-koumbaya .vti__flag {
  margin-right: 0.5rem !important;
}

.vue-tel-input-koumbaya .vti__dropdown-arrow {
  color: #6b7280 !important;
  transform: scale(0.8) !important;
}

/* Style pour le mode erreur */
.vue-tel-input-koumbaya.has-error .vti__input {
  background-color: #fef2f2 !important;
}

.vue-tel-input-koumbaya.has-error .vti__dropdown {
  background-color: #fef2f2 !important;
  border-color: #fca5a5 !important;
}

/* Responsive design */
@media (max-width: 640px) {
  .vue-tel-input-koumbaya .vti__dropdown-list {
    max-height: 160px !important;
  }

  .vue-tel-input-koumbaya .vti__dropdown-item {
    padding: 0.375rem 0.5rem !important;
    font-size: 0.875rem !important;
  }
}
</style>
