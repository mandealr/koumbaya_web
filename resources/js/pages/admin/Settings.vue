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

          <!-- Gestion des pays -->
          <div v-if="activeSection === 'countries'" class="p-6">
            <div class="admin-card-header mb-6">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Gestion des pays</h3>
                  <p class="text-sm text-gray-600">Configurez les pays disponibles sur la plateforme</p>
                </div>
                <button
                  @click="showCountryModal = true"
                  class="admin-btn-primary flex items-center"
                >
                  <PlusIcon class="w-4 h-4 mr-2" />
                  Ajouter un pays
                </button>
              </div>
            </div>
            
            <!-- Search -->
            <div class="mb-6">
              <input
                v-model="countryFilters.search"
                type="text"
                placeholder="Rechercher un pays..."
                class="admin-input w-full md:w-1/3"
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
                          class="text-blue-600 hover:text-blue-900 text-xs"
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

          <!-- Gestion des langues -->
          <div v-if="activeSection === 'languages'" class="p-6">
            <div class="admin-card-header mb-6">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Gestion des langues</h3>
                  <p class="text-sm text-gray-600">Configurez les langues disponibles sur la plateforme</p>
                </div>
                <button
                  @click="showLanguageModal = true"
                  class="admin-btn-primary flex items-center"
                >
                  <PlusIcon class="w-4 h-4 mr-2" />
                  Ajouter une langue
                </button>
              </div>
            </div>
            
            <!-- Search -->
            <div class="mb-6">
              <input
                v-model="languageFilters.search"
                type="text"
                placeholder="Rechercher une langue..."
                class="admin-input w-full md:w-1/3"
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
                          class="text-blue-600 hover:text-blue-900 text-xs"
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

    <!-- Modales pour les pays et langues -->
    
    <!-- Country Modal -->
    <div v-if="showCountryModal || showEditCountryModal" class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
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
                class="admin-input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Code *</label>
              <input
                v-model="countryForm.code"
                type="text"
                required
                maxlength="2"
                class="admin-input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Devise</label>
              <input
                v-model="countryForm.currency"
                type="text"
                class="admin-input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">URL du drapeau</label>
              <input
                v-model="countryForm.flag"
                type="url"
                class="admin-input"
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
              class="admin-btn-secondary"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="admin-btn-primary disabled:opacity-50"
            >
              <span v-if="loading">Enregistrement...</span>
              <span v-else>{{ showCountryModal ? 'Créer' : 'Modifier' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Language Modal -->
    <div v-if="showLanguageModal || showEditLanguageModal" class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
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
                class="admin-input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nom natif</label>
              <input
                v-model="languageForm.native_name"
                type="text"
                class="admin-input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Code *</label>
              <input
                v-model="languageForm.code"
                type="text"
                required
                maxlength="2"
                class="admin-input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Drapeau (emoji)</label>
              <input
                v-model="languageForm.flag"
                type="text"
                maxlength="2"
                class="admin-input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Direction</label>
              <select
                v-model="languageForm.direction"
                class="admin-input"
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
              class="admin-btn-secondary"
            >
              Annuler
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="admin-btn-primary disabled:opacity-50"
            >
              <span v-if="loading">Enregistrement...</span>
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
  EnvelopeIcon,
  GlobeAltIcon,
  LanguageIcon,
  PlusIcon,
  PencilIcon
} from '@heroicons/vue/24/outline'

const { get, post, put } = useApi()

// State
const loading = ref(false)
const activeSection = ref('general')

const settingSections = [
  { id: 'general', name: 'Général', icon: CogIcon },
  { id: 'countries', name: 'Pays', icon: GlobeAltIcon },
  { id: 'languages', name: 'Langues', icon: LanguageIcon },
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

// Countries and Languages data
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

// Modal states
const showCountryModal = ref(false)
const showEditCountryModal = ref(false)
const showLanguageModal = ref(false)
const showEditLanguageModal = ref(false)
const editingCountry = ref(null)
const editingLanguage = ref(null)

// Forms
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

// Filters
const countryFilters = reactive({
  search: ''
})

const languageFilters = reactive({
  search: ''
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
      
      // Load countries and languages
      if (response.data.countries) {
        countries.value = response.data.countries
      }
      
      if (response.data.languages) {
        languages.value = response.data.languages
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

// Countries and Languages methods

// Computed properties for filters
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

// Country methods
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

const createCountry = async () => {
  loading.value = true
  try {
    const response = await post('/admin/countries', countryForm)
    if (response && response.success) {
      const newCountry = {
        id: Date.now(),
        ...countryForm
      }
      countries.value.unshift(newCountry)
      closeCountryModal()
      if (window.$toast) {
        window.$toast.success('Pays créé avec succès', '✅ Création')
      }
    }
  } catch (error) {
    console.error('Error creating country:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la création du pays', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const updateCountry = async () => {
  loading.value = true
  try {
    const response = await put(`/admin/countries/${editingCountry.value.id}`, countryForm)
    if (response && response.success) {
      const index = countries.value.findIndex(c => c.id === editingCountry.value.id)
      if (index !== -1) {
        countries.value[index] = {
          ...countries.value[index],
          ...countryForm
        }
      }
      closeCountryModal()
      if (window.$toast) {
        window.$toast.success('Pays modifié avec succès', '✅ Modification')
      }
    }
  } catch (error) {
    console.error('Error updating country:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la modification du pays', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const toggleCountryStatus = async (country) => {
  try {
    const response = await put(`/admin/countries/${country.id}/toggle-status`)
    if (response && response.success) {
      country.is_active = !country.is_active
      if (window.$toast) {
        window.$toast.success(`Pays ${country.is_active ? 'activé' : 'désactivé'}`, '✅ Statut')
      }
    }
  } catch (error) {
    console.error('Error toggling country status:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du changement de statut', '✗ Erreur')
    }
  }
}

const deleteCountry = async (countryId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce pays ?')) return
  
  try {
    const response = await post(`/admin/countries/${countryId}/delete`)
    if (response && response.success) {
      countries.value = countries.value.filter(c => c.id !== countryId)
      if (window.$toast) {
        window.$toast.success('Pays supprimé avec succès', '✅ Suppression')
      }
    }
  } catch (error) {
    console.error('Error deleting country:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la suppression du pays', '✗ Erreur')
    }
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

// Language methods
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

const createLanguage = async () => {
  loading.value = true
  try {
    const response = await post('/admin/languages', languageForm)
    if (response && response.success) {
      const newLanguage = {
        id: Date.now(),
        ...languageForm
      }
      languages.value.unshift(newLanguage)
      closeLanguageModal()
      if (window.$toast) {
        window.$toast.success('Langue créée avec succès', '✅ Création')
      }
    }
  } catch (error) {
    console.error('Error creating language:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la création de la langue', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const updateLanguage = async () => {
  loading.value = true
  try {
    const response = await put(`/admin/languages/${editingLanguage.value.id}`, languageForm)
    if (response && response.success) {
      const index = languages.value.findIndex(l => l.id === editingLanguage.value.id)
      if (index !== -1) {
        languages.value[index] = {
          ...languages.value[index],
          ...languageForm
        }
      }
      closeLanguageModal()
      if (window.$toast) {
        window.$toast.success('Langue modifiée avec succès', '✅ Modification')
      }
    }
  } catch (error) {
    console.error('Error updating language:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la modification de la langue', '✗ Erreur')
    }
  } finally {
    loading.value = false
  }
}

const toggleLanguageStatus = async (language) => {
  try {
    const response = await put(`/admin/languages/${language.id}/toggle-status`)
    if (response && response.success) {
      language.is_active = !language.is_active
      if (window.$toast) {
        window.$toast.success(`Langue ${language.is_active ? 'activée' : 'désactivée'}`, '✅ Statut')
      }
    }
  } catch (error) {
    console.error('Error toggling language status:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors du changement de statut', '✗ Erreur')
    }
  }
}

const deleteLanguage = async (languageId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette langue ?')) return
  
  try {
    const response = await post(`/admin/languages/${languageId}/delete`)
    if (response && response.success) {
      languages.value = languages.value.filter(l => l.id !== languageId)
      if (window.$toast) {
        window.$toast.success('Langue supprimée avec succès', '✅ Suppression')
      }
    }
  } catch (error) {
    console.error('Error deleting language:', error)
    if (window.$toast) {
      window.$toast.error('Erreur lors de la suppression de la langue', '✗ Erreur')
    }
  }
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

// Lifecycle
onMounted(() => {
  loadSettings()
})
</script>