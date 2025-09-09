<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Configuration système</h1>
      <p class="mt-2 text-gray-600">Gérez les paramètres globaux de l'application</p>
    </div>

    <!-- Tabs -->
    <div class="mb-8">
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.key
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <component :is="tab.icon" class="w-5 h-5 inline mr-2" />
            {{ tab.label }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Countries Tab -->
    <div v-if="activeTab === 'countries'">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Gestion des pays</h3>
            <p class="text-sm text-gray-600">Configurez les pays disponibles sur la plateforme</p>
          </div>
          <button
            @click="showCountryModal = true"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Ajouter un pays
          </button>
        </div>

        <div class="p-6">
          <!-- Search -->
          <div class="mb-6">
            <input
              v-model="countryFilters.search"
              type="text"
              placeholder="Rechercher un pays..."
              class="w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Countries Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pays
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Code
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Devise
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="country in filteredCountries"
                  :key="country.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <img
                        v-if="country.flag"
                        :src="country.flag"
                        :alt="country.name"
                        class="w-6 h-4 rounded object-cover mr-3"
                      />
                      <div class="text-sm font-medium text-gray-900">{{ country.name }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ country.code }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ country.currency }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      country.is_active ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
                    ]">
                      {{ country.is_active ? 'Actif' : 'Inactif' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button
                        @click="editCountry(country)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        <PencilIcon class="w-4 h-4" />
                      </button>
                      <button
                        @click="toggleCountryStatus(country)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        {{ country.is_active ? 'Désactiver' : 'Activer' }}
                      </button>
                      <button
                        @click="deleteCountry(country.id)"
                        class="text-red-600 hover:text-red-900"
                      >
                        <TrashIcon class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Languages Tab -->
    <div v-if="activeTab === 'languages'">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Gestion des langues</h3>
            <p class="text-sm text-gray-600">Configurez les langues disponibles sur la plateforme</p>
          </div>
          <button
            @click="showLanguageModal = true"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Ajouter une langue
          </button>
        </div>

        <div class="p-6">
          <!-- Search -->
          <div class="mb-6">
            <input
              v-model="languageFilters.search"
              type="text"
              placeholder="Rechercher une langue..."
              class="w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Languages Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Langue
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Code
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Direction
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="language in filteredLanguages"
                  :key="language.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <span class="text-2xl mr-3">{{ language.flag }}</span>
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ language.name }}</div>
                        <div class="text-sm text-gray-500">{{ language.native_name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ language.code }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ language.direction === 'rtl' ? 'Droite à gauche' : 'Gauche à droite' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      language.is_active ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'
                    ]">
                      {{ language.is_active ? 'Actif' : 'Inactif' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button
                        @click="editLanguage(language)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        <PencilIcon class="w-4 h-4" />
                      </button>
                      <button
                        @click="toggleLanguageStatus(language)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        {{ language.is_active ? 'Désactiver' : 'Activer' }}
                      </button>
                      <button
                        @click="deleteLanguage(language.id)"
                        class="text-red-600 hover:text-red-900"
                      >
                        <TrashIcon class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings Tab -->
    <div v-if="activeTab === 'settings'">
      <div class="space-y-6">
        <!-- General Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Paramètres généraux</h3>
          </div>
          <div class="p-6">
            <form @submit.prevent="updateGeneralSettings">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nom de l'application</label>
                  <input
                    v-model="generalSettings.app_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">URL de l'application</label>
                  <input
                    v-model="generalSettings.app_url"
                    type="url"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Langue par défaut</label>
                  <select
                    v-model="generalSettings.default_language"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option v-for="language in activeLanguages" :key="language.id" :value="language.code">
                      {{ language.name }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Pays par défaut</label>
                  <select
                    v-model="generalSettings.default_country"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option v-for="country in activeCountries" :key="country.id" :value="country.code">
                      {{ country.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description de l'application</label>
                <textarea
                  v-model="generalSettings.app_description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
              </div>
              <div class="mt-6 flex justify-end">
                <button
                  type="submit"
                  :disabled="updatingSettings"
                  class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
                >
                  <span v-if="updatingSettings">Mise à jour...</span>
                  <span v-else>Sauvegarder</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Email Settings -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Paramètres email</h3>
          </div>
          <div class="p-6">
            <form @submit.prevent="updateEmailSettings">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email expéditeur</label>
                  <input
                    v-model="emailSettings.from_email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nom expéditeur</label>
                  <input
                    v-model="emailSettings.from_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email support</label>
                  <input
                    v-model="emailSettings.support_email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email admin</label>
                  <input
                    v-model="emailSettings.admin_email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
              <div class="mt-6 flex justify-end">
                <button
                  type="submit"
                  :disabled="updatingSettings"
                  class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
                >
                  <span v-if="updatingSettings">Mise à jour...</span>
                  <span v-else>Sauvegarder</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Country Modal -->
    <div v-if="showCountryModal || showEditCountryModal" class="fixed inset-0 bg-gray-600 bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ showCountryModal ? 'Ajouter un pays' : 'Modifier le pays' }}
          </h3>
        </div>

        <form @submit.prevent="showCountryModal ? createCountry() : updateCountry()">
          <div class="px-6 py-4 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
              <input
                v-model="countryForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Code *</label>
              <input
                v-model="countryForm.code"
                type="text"
                required
                maxlength="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Devise</label>
              <input
                v-model="countryForm.currency"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">URL du drapeau</label>
              <input
                v-model="countryForm.flag"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div class="flex items-center">
              <input
                v-model="countryForm.is_active"
                type="checkbox"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label class="ml-2 text-sm text-gray-700">Actif</label>
            </div>
          </div>

          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <button
              type="button"
              @click="closeCountryModal"
              class="px-4 py-2 text-gray-600 hover:text-gray-900"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
            >
              <span v-if="submitting">Enregistrement...</span>
              <span v-else>{{ showCountryModal ? 'Créer' : 'Modifier' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Language Modal -->
    <div v-if="showLanguageModal || showEditLanguageModal" class="fixed inset-0 bg-gray-600 bg-opacity-40 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ showLanguageModal ? 'Ajouter une langue' : 'Modifier la langue' }}
          </h3>
        </div>

        <form @submit.prevent="showLanguageModal ? createLanguage() : updateLanguage()">
          <div class="px-6 py-4 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
              <input
                v-model="languageForm.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nom natif</label>
              <input
                v-model="languageForm.native_name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Code *</label>
              <input
                v-model="languageForm.code"
                type="text"
                required
                maxlength="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Drapeau (emoji)</label>
              <input
                v-model="languageForm.flag"
                type="text"
                maxlength="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Direction</label>
              <select
                v-model="languageForm.direction"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="ltr">Gauche à droite</option>
                <option value="rtl">Droite à gauche</option>
              </select>
            </div>
            <div class="flex items-center">
              <input
                v-model="languageForm.is_active"
                type="checkbox"
                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label class="ml-2 text-sm text-gray-700">Actif</label>
            </div>
          </div>

          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <button
              type="button"
              @click="closeLanguageModal"
              class="px-4 py-2 text-gray-600 hover:text-gray-900"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400"
            >
              <span v-if="submitting">Enregistrement...</span>
              <span v-else>{{ showLanguageModal ? 'Créer' : 'Modifier' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import {
  GlobeAltIcon,
  LanguageIcon,
  CogIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'
import api from '@/composables/api'

const activeTab = ref('countries')
const submitting = ref(false)
const updatingSettings = ref(false)
const showCountryModal = ref(false)
const showEditCountryModal = ref(false)
const showLanguageModal = ref(false)
const showEditLanguageModal = ref(false)
const editingCountry = ref(null)
const editingLanguage = ref(null)

const tabs = [
  { key: 'countries', label: 'Pays', icon: GlobeAltIcon },
  { key: 'languages', label: 'Langues', icon: LanguageIcon },
  { key: 'settings', label: 'Paramètres', icon: CogIcon }
]

const countryFilters = reactive({
  search: ''
})

const languageFilters = reactive({
  search: ''
})

const countries = ref([
  {
    id: 1,
    name: 'France',
    code: 'FR',
    currency: 'EUR',
    flag: 'https://flagcdn.com/w40/fr.png',
    is_active: true
  },
  {
    id: 2,
    name: 'Sénégal',
    code: 'SN',
    currency: 'XOF',
    flag: 'https://flagcdn.com/w40/sn.png',
    is_active: true
  },
  {
    id: 3,
    name: 'Côte d\'Ivoire',
    code: 'CI',
    currency: 'XOF',
    flag: 'https://flagcdn.com/w40/ci.png',
    is_active: true
  },
  {
    id: 4,
    name: 'Mali',
    code: 'ML',
    currency: 'XOF',
    flag: 'https://flagcdn.com/w40/ml.png',
    is_active: false
  }
])

const languages = ref([
  {
    id: 1,
    name: 'Français',
    native_name: 'Français',
    code: 'fr',
    flag: '🇫🇷',
    direction: 'ltr',
    is_active: true
  },
  {
    id: 2,
    name: 'English',
    native_name: 'English',
    code: 'en',
    flag: '🇺🇸',
    direction: 'ltr',
    is_active: true
  },
  {
    id: 3,
    name: 'العربية',
    native_name: 'العربية',
    code: 'ar',
    flag: '🇸🇦',
    direction: 'rtl',
    is_active: false
  },
  {
    id: 4,
    name: 'Español',
    native_name: 'Español',
    code: 'es',
    flag: '🇪🇸',
    direction: 'ltr',
    is_active: false
  }
])

const countryForm = reactive({
  name: '',
  code: '',
  currency: '',
  flag: '',
  is_active: true
})

const languageForm = reactive({
  name: '',
  native_name: '',
  code: '',
  flag: '',
  direction: 'ltr',
  is_active: true
})

const generalSettings = reactive({
  app_name: 'Koumbaya',
  app_url: 'https://koumbaya.com',
  app_description: 'Plateforme de tombolas en ligne pour gagner des produits exceptionnels',
  default_language: 'fr',
  default_country: 'SN'
})

const emailSettings = reactive({
  from_email: 'noreply@koumbaya.com',
  from_name: 'Koumbaya',
  support_email: 'support@koumbaya.com',
  admin_email: 'admin@koumbaya.com'
})

const filteredCountries = computed(() => {
  let filtered = countries.value

  if (countryFilters.search) {
    const search = countryFilters.search.toLowerCase()
    filtered = filtered.filter(country =>
      country.name.toLowerCase().includes(search) ||
      country.code.toLowerCase().includes(search)
    )
  }

  return filtered
})

const filteredLanguages = computed(() => {
  let filtered = languages.value

  if (languageFilters.search) {
    const search = languageFilters.search.toLowerCase()
    filtered = filtered.filter(language =>
      language.name.toLowerCase().includes(search) ||
      language.native_name.toLowerCase().includes(search) ||
      language.code.toLowerCase().includes(search)
    )
  }

  return filtered
})

const activeCountries = computed(() => {
  return countries.value.filter(country => country.is_active)
})

const activeLanguages = computed(() => {
  return languages.value.filter(language => language.is_active)
})

const editCountry = (country) => {
  editingCountry.value = country
  Object.assign(countryForm, {
    name: country.name,
    code: country.code,
    currency: country.currency,
    flag: country.flag,
    is_active: country.is_active
  })
  showEditCountryModal.value = true
}

const editLanguage = (language) => {
  editingLanguage.value = language
  Object.assign(languageForm, {
    name: language.name,
    native_name: language.native_name,
    code: language.code,
    flag: language.flag,
    direction: language.direction,
    is_active: language.is_active
  })
  showEditLanguageModal.value = true
}

const toggleCountryStatus = async (country) => {
  try {
    country.is_active = !country.is_active
    console.log('Country status toggled:', country)
  } catch (error) {
    console.error('Error toggling country status:', error)
  }
}

const toggleLanguageStatus = async (language) => {
  try {
    language.is_active = !language.is_active
    console.log('Language status toggled:', language)
  } catch (error) {
    console.error('Error toggling language status:', error)
  }
}

const deleteCountry = async (countryId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce pays ?')) {
    try {
      countries.value = countries.value.filter(c => c.id !== countryId)
      console.log('Country deleted:', countryId)
    } catch (error) {
      console.error('Error deleting country:', error)
    }
  }
}

const deleteLanguage = async (languageId) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette langue ?')) {
    try {
      languages.value = languages.value.filter(l => l.id !== languageId)
      console.log('Language deleted:', languageId)
    } catch (error) {
      console.error('Error deleting language:', error)
    }
  }
}

const createCountry = async () => {
  submitting.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))

    const newCountry = {
      id: Date.now(),
      ...countryForm
    }

    countries.value.unshift(newCountry)
    closeCountryModal()
    console.log('Country created')
  } catch (error) {
    console.error('Error creating country:', error)
  } finally {
    submitting.value = false
  }
}

const updateCountry = async () => {
  submitting.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))

    const index = countries.value.findIndex(c => c.id === editingCountry.value.id)
    if (index !== -1) {
      countries.value[index] = {
        ...countries.value[index],
        ...countryForm
      }
    }

    closeCountryModal()
    console.log('Country updated')
  } catch (error) {
    console.error('Error updating country:', error)
  } finally {
    submitting.value = false
  }
}

const createLanguage = async () => {
  submitting.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))

    const newLanguage = {
      id: Date.now(),
      ...languageForm
    }

    languages.value.unshift(newLanguage)
    closeLanguageModal()
    console.log('Language created')
  } catch (error) {
    console.error('Error creating language:', error)
  } finally {
    submitting.value = false
  }
}

const updateLanguage = async () => {
  submitting.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))

    const index = languages.value.findIndex(l => l.id === editingLanguage.value.id)
    if (index !== -1) {
      languages.value[index] = {
        ...languages.value[index],
        ...languageForm
      }
    }

    closeLanguageModal()
    console.log('Language updated')
  } catch (error) {
    console.error('Error updating language:', error)
  } finally {
    submitting.value = false
  }
}

const updateGeneralSettings = async () => {
  updatingSettings.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    console.log('General settings updated')
  } catch (error) {
    console.error('Error updating general settings:', error)
  } finally {
    updatingSettings.value = false
  }
}

const updateEmailSettings = async () => {
  updatingSettings.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    console.log('Email settings updated')
  } catch (error) {
    console.error('Error updating email settings:', error)
  } finally {
    updatingSettings.value = false
  }
}

const closeCountryModal = () => {
  showCountryModal.value = false
  showEditCountryModal.value = false
  editingCountry.value = null
  Object.assign(countryForm, {
    name: '',
    code: '',
    currency: '',
    flag: '',
    is_active: true
  })
}

const closeLanguageModal = () => {
  showLanguageModal.value = false
  showEditLanguageModal.value = false
  editingLanguage.value = null
  Object.assign(languageForm, {
    name: '',
    native_name: '',
    code: '',
    flag: '',
    direction: 'ltr',
    is_active: true
  })
}

onMounted(() => {
  // Load settings data
  console.log('Settings page mounted')
})
</script>
