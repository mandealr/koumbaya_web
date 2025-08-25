<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Paramètres système</h1>
        <p class="mt-2 text-gray-600">Configuration et paramètres généraux de la plateforme</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="refreshSettings"
          class="admin-btn-secondary"
        >
          <ArrowPathIcon class="w-4 h-4 mr-2" />
          Actualiser
        </button>
        <button
          @click="backupSettings"
          class="admin-btn-accent"
        >
          <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
          Sauvegarder
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Navigation des paramètres -->
      <div class="lg:col-span-1">
        <nav class="admin-card space-y-1">
          <button
            v-for="section in settingSections"
            :key="section.id"
            @click="activeSection = section.id"
            :class="[
              'w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-colors flex items-center',
              activeSection === section.id 
                ? 'bg-[#0099cc]/10 text-[#0099cc] border border-[#0099cc]/20' 
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
          >
            <component :is="section.icon" class="w-5 h-5 mr-3" />
            {{ section.name }}
          </button>
        </nav>
      </div>

      <!-- Contenu des paramètres -->
      <div class="lg:col-span-3">
        <div class="admin-card">
          <!-- Paramètres généraux -->
          <div v-if="activeSection === 'general'" class="p-6">
            <div class="admin-card-header mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Paramètres généraux</h3>
            </div>
            
            <form @submit.prevent="updateGeneralSettings" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nom de la plateforme</label>
                  <input 
                    v-model="generalSettings.app_name"
                    type="text" 
                    class="admin-input"
                    placeholder="Koumbaya"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">URL de base</label>
                  <input 
                    v-model="generalSettings.app_url"
                    type="url" 
                    class="admin-input"
                    placeholder="https://koumbaya.com"
                  >
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description de la plateforme</label>
                <textarea 
                  v-model="generalSettings.app_description"
                  rows="3"
                  class="admin-input"
                  placeholder="Description de votre plateforme..."
                ></textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email de contact</label>
                  <input 
                    v-model="generalSettings.contact_email"
                    type="email" 
                    class="admin-input"
                    placeholder="contact@koumbaya.com"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone de contact</label>
                  <input 
                    v-model="generalSettings.contact_phone"
                    type="tel" 
                    class="admin-input"
                    placeholder="+225 XX XX XX XX"
                  >
                </div>
              </div>

              <div class="flex justify-end">
                <button 
                  type="submit"
                  :disabled="loading"
                  class="admin-btn-primary disabled:opacity-50"
                >
                  {{ loading ? 'Sauvegarde...' : 'Sauvegarder' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Paramètres de paiement -->
          <div v-if="activeSection === 'payment'" class="p-6">
            <div class="admin-card-header mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Paramètres de paiement</h3>
            </div>
            
            <form @submit.prevent="updatePaymentSettings" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Devise par défaut</label>
                  <select v-model="paymentSettings.default_currency" class="admin-input">
                    <option value="XOF">Franc CFA (XOF)</option>
                    <option value="EUR">Euro (EUR)</option>
                    <option value="USD">Dollar US (USD)</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Commission plateforme (%)</label>
                  <input 
                    v-model.number="paymentSettings.platform_commission"
                    type="number" 
                    min="0"
                    max="100"
                    step="0.1"
                    class="admin-input"
                    placeholder="5.0"
                  >
                </div>
              </div>

              <div class="space-y-4">
                <h4 class="font-medium text-gray-900">Méthodes de paiement actives</h4>
                
                <div class="space-y-3">
                  <div v-for="method in paymentMethods" :key="method.id" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                      <component :is="method.icon" class="w-6 h-6 mr-3 text-gray-500" />
                      <div>
                        <p class="font-medium text-gray-900">{{ method.name }}</p>
                        <p class="text-sm text-gray-600">{{ method.description }}</p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-3">
                      <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                          v-model="method.active" 
                          type="checkbox" 
                          class="sr-only peer"
                        >
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#0099cc]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0099cc]"></div>
                      </label>
                      <button
                        @click="configurePaymentMethod(method)"
                        class="text-[#0099cc] hover:text-[#0088bb] text-sm"
                      >
                        <CogIcon class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end">
                <button 
                  type="submit"
                  :disabled="loading"
                  class="admin-btn-primary disabled:opacity-50"
                >
                  {{ loading ? 'Sauvegarde...' : 'Sauvegarder' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Paramètres des tombolas -->
          <div v-if="activeSection === 'lottery'" class="p-6">
            <div class="admin-card-header mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Paramètres des tombolas</h3>
            </div>
            
            <form @submit.prevent="updateLotterySettings" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Prix minimum ticket (FCFA)</label>
                  <input 
                    v-model.number="lotterySettings.min_ticket_price"
                    type="number" 
                    min="100"
                    class="admin-input"
                    placeholder="500"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Prix maximum ticket (FCFA)</label>
                  <input 
                    v-model.number="lotterySettings.max_ticket_price"
                    type="number" 
                    min="1000"
                    class="admin-input"
                    placeholder="50000"
                  >
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Participants minimum par tombola</label>
                  <input 
                    v-model.number="lotterySettings.min_participants"
                    type="number" 
                    min="2"
                    class="admin-input"
                    placeholder="10"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Durée maximum (jours)</label>
                  <input 
                    v-model.number="lotterySettings.max_duration_days"
                    type="number" 
                    min="1"
                    class="admin-input"
                    placeholder="30"
                  >
                </div>
              </div>

              <div class="space-y-4">
                <h4 class="font-medium text-gray-900">Options automatiques</h4>
                
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <div>
                      <h5 class="font-medium text-gray-900">Remboursement automatique</h5>
                      <p class="text-sm text-gray-600">Rembourser automatiquement si pas assez de participants</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input 
                        v-model="lotterySettings.auto_refund" 
                        type="checkbox" 
                        class="sr-only peer"
                      >
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#0099cc]/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0099cc]"></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between">
                    <div>
                      <h5 class="font-medium text-gray-900">Tirage automatique</h5>
                      <p class="text-sm text-gray-600">Effectuer le tirage automatiquement à la date de fin</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input 
                        v-model="lotterySettings.auto_draw" 
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
                  type="submit"
                  :disabled="loading"
                  class="admin-btn-primary disabled:opacity-50"
                >
                  {{ loading ? 'Sauvegarde...' : 'Sauvegarder' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Paramètres de notifications -->
          <div v-if="activeSection === 'notifications'" class="p-6">
            <div class="admin-card-header mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Paramètres de notifications</h3>
            </div>
            
            <form @submit.prevent="updateNotificationSettings" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email expéditeur</label>
                  <input 
                    v-model="notificationSettings.from_email"
                    type="email" 
                    class="admin-input"
                    placeholder="noreply@koumbaya.com"
                  >
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nom expéditeur</label>
                  <input 
                    v-model="notificationSettings.from_name"
                    type="text" 
                    class="admin-input"
                    placeholder="Koumbaya"
                  >
                </div>
              </div>

              <div class="space-y-4">
                <h4 class="font-medium text-gray-900">Types de notifications automatiques</h4>
                
                <div class="space-y-3">
                  <div v-for="notification in notificationTypes" :key="notification.id" class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
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
                  type="submit"
                  :disabled="loading"
                  class="admin-btn-primary disabled:opacity-50"
                >
                  {{ loading ? 'Sauvegarde...' : 'Sauvegarder' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Maintenance -->
          <div v-if="activeSection === 'maintenance'" class="p-6">
            <div class="admin-card-header mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Maintenance et sécurité</h3>
            </div>
            
            <div class="space-y-6">
              <!-- Mode maintenance -->
              <div class="p-4 border border-yellow-200 bg-yellow-50 rounded-lg">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="font-medium text-yellow-800">Mode maintenance</h4>
                    <p class="text-sm text-yellow-700">Désactiver temporairement l'accès public à la plateforme</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input 
                      v-model="maintenanceSettings.maintenance_mode" 
                      type="checkbox" 
                      class="sr-only peer"
                    >
                    <div class="w-11 h-6 bg-yellow-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-yellow-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                  </label>
                </div>
                
                <div v-if="maintenanceSettings.maintenance_mode" class="mt-4">
                  <label class="block text-sm font-medium text-yellow-800 mb-2">Message de maintenance</label>
                  <textarea 
                    v-model="maintenanceSettings.maintenance_message"
                    rows="2"
                    class="w-full border border-yellow-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-yellow-50"
                    placeholder="La plateforme est temporairement en maintenance..."
                  ></textarea>
                </div>
              </div>

              <!-- Actions de maintenance -->
              <div class="space-y-4">
                <h4 class="font-medium text-gray-900">Actions de maintenance</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <button
                    @click="clearCache"
                    :disabled="loading"
                    class="admin-btn-secondary justify-center disabled:opacity-50"
                  >
                    <TrashIcon class="w-4 h-4 mr-2" />
                    Vider le cache
                  </button>
                  
                  <button
                    @click="optimizeDatabase"
                    :disabled="loading"
                    class="admin-btn-secondary justify-center disabled:opacity-50"
                  >
                    <CogIcon class="w-4 h-4 mr-2" />
                    Optimiser la BDD
                  </button>
                  
                  <button
                    @click="generateSitemap"
                    :disabled="loading"
                    class="admin-btn-secondary justify-center disabled:opacity-50"
                  >
                    <DocumentIcon class="w-4 h-4 mr-2" />
                    Générer sitemap
                  </button>
                  
                  <button
                    @click="testEmailConfig"
                    :disabled="loading"
                    class="admin-btn-secondary justify-center disabled:opacity-50"
                  >
                    <EnvelopeIcon class="w-4 h-4 mr-2" />
                    Tester les emails
                  </button>
                </div>
              </div>

              <div class="flex justify-end">
                <button 
                  @click="updateMaintenanceSettings"
                  :disabled="loading"
                  class="admin-btn-primary disabled:opacity-50"
                >
                  {{ loading ? 'Sauvegarde...' : 'Sauvegarder' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import {
  CogIcon,
  CurrencyDollarIcon,
  GiftIcon,
  BellIcon,
  WrenchScrewdriverIcon,
  ArrowPathIcon,
  DocumentArrowDownIcon,
  CreditCardIcon,
  BanknotesIcon,
  DevicePhoneMobileIcon,
  TrashIcon,
  DocumentIcon,
  EnvelopeIcon
} from '@heroicons/vue/24/outline'

const { get, post, put } = useApi()

// State
const loading = ref(false)
const activeSection = ref('general')

const settingSections = [
  { id: 'general', name: 'Général', icon: CogIcon },
  { id: 'payment', name: 'Paiements', icon: CurrencyDollarIcon },
  { id: 'lottery', name: 'Tombolas', icon: GiftIcon },
  { id: 'notifications', name: 'Notifications', icon: BellIcon },
  { id: 'maintenance', name: 'Maintenance', icon: WrenchScrewdriverIcon }
]

// Settings forms
const generalSettings = reactive({
  app_name: 'Koumbaya',
  app_url: 'https://koumbaya.com',
  app_description: 'Plateforme de tombolas en ligne',
  contact_email: 'contact@koumbaya.com',
  contact_phone: '+225 XX XX XX XX'
})

const paymentSettings = reactive({
  default_currency: 'XOF',
  platform_commission: 5.0
})

const paymentMethods = ref([
  {
    id: 'orange_money',
    name: 'Orange Money',
    description: 'Paiement via Orange Money',
    icon: DevicePhoneMobileIcon,
    active: true
  },
  {
    id: 'mtn_money',
    name: 'MTN Mobile Money',
    description: 'Paiement via MTN Mobile Money',
    icon: DevicePhoneMobileIcon,
    active: true
  },
  {
    id: 'credit_card',
    name: 'Carte bancaire',
    description: 'Paiement par carte Visa/Mastercard',
    icon: CreditCardIcon,
    active: false
  },
  {
    id: 'bank_transfer',
    name: 'Virement bancaire',
    description: 'Paiement par virement bancaire',
    icon: BanknotesIcon,
    active: false
  }
])

const lotterySettings = reactive({
  min_ticket_price: 500,
  max_ticket_price: 50000,
  min_participants: 10,
  max_duration_days: 30,
  auto_refund: true,
  auto_draw: false
})

const notificationSettings = reactive({
  from_email: 'noreply@koumbaya.com',
  from_name: 'Koumbaya'
})

const notificationTypes = ref([
  {
    id: 'new_user',
    name: 'Nouvel utilisateur',
    description: 'Email de bienvenue aux nouveaux utilisateurs',
    enabled: true
  },
  {
    id: 'lottery_winner',
    name: 'Gagnant tombola',
    description: 'Notification au gagnant d\'une tombola',
    enabled: true
  },
  {
    id: 'payment_success',
    name: 'Paiement réussi',
    description: 'Confirmation de paiement',
    enabled: true
  },
  {
    id: 'refund_processed',
    name: 'Remboursement traité',
    description: 'Confirmation de remboursement',
    enabled: true
  }
])

const maintenanceSettings = reactive({
  maintenance_mode: false,
  maintenance_message: 'La plateforme est temporairement en maintenance. Nous reviendrons bientôt !'
})

// Methods
const loadSettings = async () => {
  loading.value = true
  try {
    const response = await get('/admin/settings')
    if (response && response.success && response.data) {
      // Load settings data
      Object.assign(generalSettings, response.data.general || {})
      Object.assign(paymentSettings, response.data.payment || {})
      Object.assign(lotterySettings, response.data.lottery || {})
      Object.assign(notificationSettings, response.data.notifications || {})
      Object.assign(maintenanceSettings, response.data.maintenance || {})
      
      if (response.data.payment_methods) {
        paymentMethods.value.forEach(method => {
          const savedMethod = response.data.payment_methods.find(pm => pm.id === method.id)
          if (savedMethod) {
            method.active = savedMethod.active
          }
        })
      }
    }
  } catch (error) {
    console.error('Error loading settings:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du chargement des paramètres', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const updateGeneralSettings = async () => {
  loading.value = true
  try {
    const response = await put('/admin/settings/general', generalSettings)
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Paramètres généraux sauvegardés', '✅ Succès')
      }
    }
  } catch (error) {
    console.error('Error updating general settings:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la sauvegarde', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const updatePaymentSettings = async () => {
  loading.value = true
  try {
    const response = await put('/admin/settings/payment', {
      ...paymentSettings,
      payment_methods: paymentMethods.value
    })
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Paramètres de paiement sauvegardés', '✅ Succès')
      }
    }
  } catch (error) {
    console.error('Error updating payment settings:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la sauvegarde', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const updateLotterySettings = async () => {
  loading.value = true
  try {
    const response = await put('/admin/settings/lottery', lotterySettings)
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Paramètres des tombolas sauvegardés', '✅ Succès')
      }
    }
  } catch (error) {
    console.error('Error updating lottery settings:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la sauvegarde', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const updateNotificationSettings = async () => {
  loading.value = true
  try {
    const response = await put('/admin/settings/notifications', {
      ...notificationSettings,
      notification_types: notificationTypes.value
    })
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Paramètres de notifications sauvegardés', '✅ Succès')
      }
    }
  } catch (error) {
    console.error('Error updating notification settings:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la sauvegarde', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const updateMaintenanceSettings = async () => {
  loading.value = true
  try {
    const response = await put('/admin/settings/maintenance', maintenanceSettings)
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Paramètres de maintenance sauvegardés', '✅ Succès')
      }
    }
  } catch (error) {
    console.error('Error updating maintenance settings:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la sauvegarde', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

// Utility actions
const refreshSettings = async () => {
  await loadSettings()
  if (window.$toast) {
    window.$toast.info('Paramètres rechargés', 'ℹ️ Information')
  }
}

const backupSettings = async () => {
  loading.value = true
  try {
    const response = await post('/admin/settings/backup')
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Sauvegarde créée avec succès', '✅ Sauvegarde')
      }
    }
  } catch (error) {
    console.error('Error creating backup:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la sauvegarde', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const configurePaymentMethod = (method) => {
  if (window.$toast) {
    window.$toast.info(`Configuration de ${method.name}`, 'ℹ️ Configuration')
  }
  // TODO: Open configuration modal for payment method
}

const clearCache = async () => {
  if (!confirm('Vider le cache de l\'application ?')) return
  
  loading.value = true
  try {
    const response = await post('/admin/maintenance/clear-cache')
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Cache vidé avec succès', '✅ Maintenance')
      }
    }
  } catch (error) {
    console.error('Error clearing cache:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du vidage du cache', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const optimizeDatabase = async () => {
  if (!confirm('Optimiser la base de données ? Cette opération peut prendre du temps.')) return
  
  loading.value = true
  try {
    const response = await post('/admin/maintenance/optimize-database')
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Base de données optimisée', '✅ Maintenance')
      }
    }
  } catch (error) {
    console.error('Error optimizing database:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de l\'optimisation', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const generateSitemap = async () => {
  loading.value = true
  try {
    const response = await post('/admin/maintenance/generate-sitemap')
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Sitemap généré avec succès', '✅ Maintenance')
      }
    }
  } catch (error) {
    console.error('Error generating sitemap:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la génération du sitemap', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const testEmailConfig = async () => {
  loading.value = true
  try {
    const response = await post('/admin/maintenance/test-email')
    if (response && response.success) {
      if (window.$toast) {
        window.$toast.success('Email de test envoyé', '✅ Test')
      }
    }
  } catch (error) {
    console.error('Error testing email:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du test email', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadSettings()
})
</script>