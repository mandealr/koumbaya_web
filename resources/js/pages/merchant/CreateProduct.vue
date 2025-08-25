<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Publier un Nouveau Produit</h1>
        <p class="mt-2 text-gray-600">Créez votre tombola et mettez votre produit en vente</p>
      </div>
      <router-link
        to="/merchant/products"
        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
      >
        <ArrowLeftIcon class="w-4 h-4 mr-2" />
        Retour aux produits
      </router-link>
    </div>

    <!-- Progress Steps -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
      <div class="flex items-center justify-between mb-8">
        <div
          v-for="(step, index) in steps"
          :key="step.id"
          class="flex items-center"
          :class="{ 'flex-1': index < steps.length - 1 }"
        >
          <div class="flex items-center">
            <div :class="[
              'flex items-center justify-center w-10 h-10 rounded-full border-2 font-medium text-sm transition-all',
              currentStep >= step.id
                ? 'border-[#0099cc] bg-[#0099cc] text-white'
                : 'border-gray-300 bg-white text-gray-400'
            ]">
              <component :is="step.icon" v-if="currentStep >= step.id" class="w-5 h-5" />
              <span v-else>{{ step.id }}</span>
            </div>
            <div class="ml-3">
              <p :class="[
                'text-sm font-medium',
                currentStep >= step.id ? 'text-[#0099cc]' : 'text-gray-400'
              ]">
                {{ step.title }}
              </p>
              <p class="text-xs text-gray-500">{{ step.description }}</p>
            </div>
          </div>
          <div
            v-if="index < steps.length - 1"
            :class="[
              'flex-1 h-0.5 mx-4 transition-all',
              currentStep > step.id ? 'bg-[#0099cc]' : 'bg-gray-200'
            ]"
          ></div>
        </div>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Step 1: Basic Information -->
      <div v-if="currentStep === 1" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Informations de base</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nom du produit *
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
              placeholder="Ex: iPhone 15 Pro Max 256GB"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            <p v-else-if="form.name && form.name.length < 3" class="mt-1 text-sm text-orange-600">Le nom doit contenir au moins 3 caractères</p>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Description *
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
              placeholder="Décrivez votre produit en détail..."
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            <p v-else-if="form.description && form.description.length < 10" class="mt-1 text-sm text-orange-600">La description doit contenir au moins 10 caractères</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Catégorie *
            </label>
            <select
              v-model="form.category_id"
              required
              :disabled="apiLoading"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all disabled:opacity-50" style="color: #5f5f5f"
            >
              <option value="">Sélectionner une catégorie</option>
              <option v-if="apiLoading" disabled>Chargement des catégories...</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Condition *
            </label>
            <select
              v-model="form.condition"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
            >
              <option value="">État du produit</option>
              <option value="new">Neuf</option>
              <option value="used">Occasion</option>
              <option value="refurbished">Reconditionné</option>
            </select>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Mode de vente *
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="flex items-center p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors"
                     :class="{ 'border-[#0099cc] bg-blue-50': form.sale_mode === 'direct' }">
                <input type="radio" v-model="form.sale_mode" value="direct" class="sr-only">
                <div class="flex items-center space-x-3">
                  <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                       :class="form.sale_mode === 'direct' ? 'border-[#0099cc]' : 'border-gray-300'">
                    <div v-if="form.sale_mode === 'direct'" class="w-2.5 h-2.5 bg-[#0099cc] rounded-full"></div>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Vente directe</p>
                    <p class="text-sm text-gray-500">Les clients achètent directement</p>
                  </div>
                </div>
              </label>
              
              <label class="flex items-center p-4 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors"
                     :class="{ 'border-purple-500 bg-purple-50': form.sale_mode === 'lottery' }">
                <input type="radio" v-model="form.sale_mode" value="lottery" class="sr-only">
                <div class="flex items-center space-x-3">
                  <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                       :class="form.sale_mode === 'lottery' ? 'border-purple-500' : 'border-gray-300'">
                    <div v-if="form.sale_mode === 'lottery'" class="w-2.5 h-2.5 bg-purple-500 rounded-full"></div>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Tombola</p>
                    <p class="text-sm text-gray-500">Les clients achètent des tickets</p>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Valeur du produit (FCFA) *
            </label>
            <input
              v-model="form.price"
              type="number"
              required
              min="0"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
              placeholder="Ex: 800000"
            />
            <p v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
            <p v-else-if="form.price && parseFloat(form.price) < 1000" class="mt-1 text-sm text-orange-600">La valeur minimum est de 1000 FCFA</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Localisation *
            </label>
            <input
              v-model="form.location"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
              placeholder="Ex: Libreville, Gabon"
            />
          </div>
        </div>
      </div>

      <!-- Step 2: Images -->
      <div v-if="currentStep === 2" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Photos du produit</h2>

        <div class="space-y-6">
          <div class="text-center">
            <div
              @click="triggerFileInput"
              @dragover.prevent
              @drop.prevent="handleFileDrop"
              class="border-2 border-dashed border-gray-300 rounded-xl p-8 hover:border-[#0099cc] transition-colors cursor-pointer"
            >
              <CameraIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p class="text-lg font-medium text-gray-700 mb-2">Ajoutez des photos</p>
              <p class="text-sm text-gray-500 mb-4">Glissez-déposez ou cliquez pour sélectionner</p>
              <div class="flex justify-center">
                <span class="bg-[#0099cc] text-white px-6 py-2 rounded-lg font-medium hover:bg-[#0088bb] transition-colors">
                  Choisir des fichiers
                </span>
              </div>
            </div>
            <input
              ref="fileInput"
              type="file"
              multiple
              accept="image/jpeg,image/jpg,image/png,image/webp"
              @change="handleFileSelect"
              class="hidden"
            />
          </div>

          <!-- Image Preview -->
          <div v-if="form.images.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
              v-for="(image, index) in form.images"
              :key="index"
              class="relative group"
            >
              <img
                :src="image.preview"
                :alt="`Produit ${index + 1}`"
                class="w-full h-32 object-cover rounded-xl border border-gray-200"
              />
              <button
                @click="removeImage(index)"
                type="button"
                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
              <div v-if="index === 0" class="absolute bottom-2 left-2 bg-[#0099cc] text-white text-xs px-2 py-1 rounded-md">
                Photo principale
              </div>
            </div>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex items-start space-x-3">
              <InformationCircleIcon class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" />
              <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">Conseils pour de bonnes photos :</p>
                <ul class="space-y-1 text-sm">
                  <li>• Utilisez un bon éclairage naturel</li>
                  <li>• Prenez plusieurs angles du produit</li>
                  <li>• La première image sera la photo principale</li>
                  <li>• Maximum 10 images par produit</li>
                  <li>• Formats acceptés : JPG, PNG, WebP</li>
                  <li>• Taille maximum : 5MB par image</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 3: Sale Configuration -->
      <div v-if="currentStep === 3" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <!-- Direct Sale Mode -->
        <div v-if="form.sale_mode === 'direct'">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuration de vente directe</h2>
          
          <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
              <div class="w-12 h-12 bg-[#0099cc] rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Vente directe configurée</h3>
                <p class="text-gray-600">Votre produit sera disponible à l'achat immédiat</p>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div class="bg-white rounded-lg p-3">
                <p class="text-gray-600">Prix de vente</p>
                <p class="font-semibold text-[#0099cc]">{{ formatAmount(form.price) }} FCFA</p>
              </div>
              <div class="bg-white rounded-lg p-3">
                <p class="text-gray-600">Commission plateforme</p>
                <p class="font-semibold text-gray-800">{{ formatAmount(form.price * 0.05) }} FCFA (5%)</p>
              </div>
            </div>
            
            <p class="text-sm text-gray-500 mt-4">
              Les clients pourront acheter votre produit directement au prix affiché.
            </p>
          </div>
        </div>

        <!-- Lottery Mode -->
        <div v-else-if="form.sale_mode === 'lottery'">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuration de la tombola</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Prix par ticket (FCFA) *
            </label>
            <input
              v-model="form.ticket_price"
              type="number"
              required
              min="100"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
              placeholder="Ex: 2500"
              @input="calculateLotteryMetrics"
              @blur="validateTicketPrice"
            />
            <p v-if="errors.ticket_price" class="mt-1 text-sm text-red-600">{{ errors.ticket_price }}</p>
            <p v-else-if="form.ticket_price && parseFloat(form.ticket_price) < 100" class="mt-1 text-sm text-orange-600">Le prix minimum est de 100 FCFA</p>
            <p v-else class="text-sm text-gray-500 mt-1">Minimum 100 FCFA</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nombre de tickets *
            </label>
            <input
              v-model="form.total_tickets"
              type="number"
              required
              min="10"
              max="10000"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
              placeholder="Ex: 400"
              @input="calculateLotteryMetrics"
              @blur="validateTotalTickets"
            />
            <p v-if="errors.total_tickets" class="mt-1 text-sm text-red-600">{{ errors.total_tickets }}</p>
            <p v-else-if="form.total_tickets && (parseInt(form.total_tickets) < 10 || parseInt(form.total_tickets) > 10000)" class="mt-1 text-sm text-orange-600">Entre 10 et 10,000 tickets</p>
            <p v-else class="text-sm text-gray-500 mt-1">Entre 10 et 10,000 tickets</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Date de fin de vente *
            </label>
            <input
              v-model="form.end_date"
              type="datetime-local"
              required
              :min="minDate"
              @change="validateEndDate"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
            />
            <p v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</p>
            <p v-else-if="form.end_date && new Date(form.end_date) <= new Date()" class="mt-1 text-sm text-orange-600">La date de fin doit être dans le futur</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tickets minimum pour valider *
            </label>
            <input
              v-model="form.min_tickets"
              type="number"
              required
              :max="form.total_tickets"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
              placeholder="Ex: 200"
            />
            <p v-if="errors.min_tickets" class="mt-1 text-sm text-red-600">{{ errors.min_tickets }}</p>
            <p v-else-if="form.min_tickets && form.total_tickets && parseInt(form.min_tickets) > parseInt(form.total_tickets)" class="mt-1 text-sm text-orange-600">Ne peut pas dépasser le nombre total de tickets</p>
            <p v-else class="text-sm text-gray-500 mt-1">Si pas atteint, remboursement automatique</p>
          </div>

          <!-- Lottery Metrics -->
          <div class="lg:col-span-2">
            <div class="bg-gradient-to-br from-[#0099cc]/10 to-cyan-100 rounded-xl p-6">
              <h3 class="font-semibold text-gray-900 mb-4">Aperçu de la tombola</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                  <p class="text-2xl font-bold text-[#0099cc]">{{ formatAmount(lotteryMetrics.totalRevenue) }}</p>
                  <p class="text-sm text-gray-600">Revenus potentiels</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-blue-600">{{ lotteryMetrics.profitMargin }}%</p>
                  <p class="text-sm text-gray-600">Marge bénéficiaire</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-purple-600">{{ formatAmount(lotteryMetrics.platformFee) }}</p>
                  <p class="text-sm text-gray-600">Frais plateforme (5%)</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>

      <!-- Step 4: Review -->
      <div v-if="currentStep === 4" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Vérification et publication</h2>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Product Summary -->
          <div>
            <h3 class="font-semibold text-gray-900 mb-4">Résumé du produit</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Nom :</span>
                <span class="font-medium">{{ form.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Valeur :</span>
                <span class="font-medium">{{ formatAmount(form.price) }} FCFA</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Condition :</span>
                <span class="font-medium">{{ getConditionLabel(form.condition) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Localisation :</span>
                <span class="font-medium">{{ form.location }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Photos :</span>
                <span class="font-medium">{{ form.images.length }} image(s)</span>
              </div>
            </div>
          </div>

          <!-- Sale Configuration Summary -->
          <div>
            <h3 class="font-semibold text-gray-900 mb-4">
              {{ form.sale_mode === 'lottery' ? 'Configuration tombola' : 'Configuration vente' }}
            </h3>
            
            <!-- Direct Sale Summary -->
            <div v-if="form.sale_mode === 'direct'" class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Mode de vente :</span>
                <span class="font-medium text-[#0099cc]">Vente directe</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Prix de vente :</span>
                <span class="font-medium">{{ formatAmount(form.price) }} FCFA</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Commission (5%) :</span>
                <span class="font-medium">{{ formatAmount(form.price * 0.05) }} FCFA</span>
              </div>
              <div class="flex justify-between border-t pt-3">
                <span class="text-gray-600">Vous recevrez :</span>
                <span class="font-bold text-[#0099cc]">{{ formatAmount(form.price * 0.95) }} FCFA</span>
              </div>
            </div>
            
            <!-- Lottery Summary -->
            <div v-else-if="form.sale_mode === 'lottery'" class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Mode de vente :</span>
                <span class="font-medium text-purple-600">Tombola</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Prix/ticket :</span>
                <span class="font-medium">{{ formatAmount(form.ticket_price) }} FCFA</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total tickets :</span>
                <span class="font-medium">{{ form.total_tickets }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Minimum requis :</span>
                <span class="font-medium">{{ form.min_tickets }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Date de fin :</span>
                <span class="font-medium">{{ formatDate(form.end_date) }}</span>
              </div>
              <div class="flex justify-between border-t pt-3">
                <span class="text-gray-600">Revenus max :</span>
                <span class="font-bold text-[#0099cc]">{{ formatAmount(lotteryMetrics.totalRevenue) }} FCFA</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Terms -->
        <div class="mt-8 p-4 bg-gray-100 rounded-xl">
          <label class="flex items-start space-x-3">
            <input
              v-model="form.terms_accepted"
              type="checkbox"
              required
              class="mt-1 h-4 w-4 text-[#0099cc] focus:ring-[#0099cc] border-gray-300 rounded"
            />
            <span class="text-sm text-gray-700">
              J'accepte les <a href="#" class="text-[#0099cc] hover:underline">conditions de vente</a>
              et confirme que toutes les informations sont exactes. Je comprends que Koumbaya prélèvera
              des frais de 5% sur les revenus {{ form.sale_mode === 'lottery' ? 'de la tombola' : 'de la vente' }}.
            </span>
          </label>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="flex justify-between items-center bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        <button
          v-if="currentStep > 1"
          @click="previousStep"
          type="button"
          class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-2" />
          Précédent
        </button>
        <div v-else></div>

        <div class="text-sm text-gray-500">
          Étape {{ currentStep }} sur {{ steps.length }}
        </div>

        <button
          v-if="currentStep < steps.length"
          @click="nextStep"
          type="button"
          class="inline-flex items-center px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!canProceed || apiLoading"
        >
          Suivant
          <ArrowRightIcon class="w-4 h-4 ml-2" />
        </button>

        <button
          v-if="currentStep === steps.length"
          type="submit"
          :disabled="loading || !canProceed || apiLoading"
          class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
        >
          <span v-if="loading || apiLoading" class="flex items-center">
            <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></div>
            {{ loading ? 'Publication...' : 'Chargement...' }}
          </span>
          <span v-else class="flex items-center">
            <RocketLaunchIcon class="w-4 h-4 mr-2" />
            Publier le produit
          </span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../../composables/api'
import {
  ArrowLeftIcon,
  ArrowRightIcon,
  CameraIcon,
  XMarkIcon,
  InformationCircleIcon,
  CheckIcon,
  RocketLaunchIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const { get, post, loading: apiLoading, error: apiError } = useApi()

// State
const currentStep = ref(1)
const loading = ref(false)
const fileInput = ref(null)

const steps = [
  { id: 1, title: 'Informations', description: 'Détails du produit', icon: InformationCircleIcon },
  { id: 2, title: 'Photos', description: 'Images du produit', icon: CameraIcon },
  { id: 3, title: 'Tombola', description: 'Configuration', icon: CheckIcon },
  { id: 4, title: 'Publication', description: 'Vérification', icon: RocketLaunchIcon }
]

const categories = ref([])

const form = reactive({
  name: '',
  description: '',
  category_id: '',
  condition: '',
  sale_mode: 'direct', // 'direct' or 'lottery'
  price: '', // Changed from 'value' to 'price' to match API
  location: '',
  images: [],
  ticket_price: '',
  total_tickets: '',
  min_tickets: '', // This will be 'min_participants' in the API
  end_date: '',
  terms_accepted: false,
  duration_days: 7 // Pour la création de la tombola
})

const errors = reactive({
  name: '',
  description: '',
  category_id: '',
  condition: '',
  value: '',
  location: '',
  images: '',
  ticket_price: '',
  total_tickets: '',
  min_tickets: '',
  end_date: ''
})

// Computed
const minDate = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().slice(0, 16)
})

const lotteryMetrics = computed(() => {
  const totalRevenue = (form.ticket_price || 0) * (form.total_tickets || 0)
  const platformFee = totalRevenue * 0.05
  const netRevenue = totalRevenue - platformFee
  const profitMargin = form.price ? Math.round(((netRevenue - form.price) / form.price) * 100) : 0

  return {
    totalRevenue,
    platformFee,
    netRevenue,
    profitMargin
  }
})

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1:
      return validateStep1()
    case 2:
      return true // Images are optional according to API
    case 3:
      return validateStep3()
    case 4:
      return form.terms_accepted
    default:
      return false
  }
})

// Validation functions
const validateStep1 = () => {
  const isValid = form.name && 
                  form.description && 
                  form.category_id && 
                  form.condition && 
                  form.sale_mode &&
                  form.price && 
                  form.location &&
                  parseFloat(form.price) >= 1000 // Minimum price according to API
  
  // Clear step 1 errors if valid
  if (isValid) {
    errors.name = ''
    errors.description = ''
    errors.category_id = ''
    errors.condition = ''
    errors.price = ''
    errors.location = ''
  }
  
  return isValid
}

const validateStep3 = () => {
  // Direct sale mode - always valid (no extra fields required)
  if (form.sale_mode === 'direct') {
    return true
  }
  
  // Lottery mode - validate lottery fields
  if (form.sale_mode === 'lottery') {
    const isValid = form.ticket_price && 
                    form.total_tickets && 
                    form.min_tickets && 
                    form.end_date &&
                    parseFloat(form.ticket_price) >= 100 && // Minimum ticket price
                    parseInt(form.total_tickets) >= 10 && // Minimum tickets
                    parseInt(form.min_tickets) >= 10 &&
                    parseInt(form.min_tickets) <= parseInt(form.total_tickets) &&
                    new Date(form.end_date) > new Date() // End date in future
    
    // Clear step 3 errors if valid
    if (isValid) {
      errors.ticket_price = ''
      errors.total_tickets = ''
      errors.min_tickets = ''
      errors.end_date = ''
    }
    
    return isValid
  }
  
  return false
}

// Methods
const nextStep = () => {
  if (canProceed.value && currentStep.value < steps.length) {
    currentStep.value++
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files || [])
  handleFiles(files)
}

const handleFileDrop = (event) => {
  const files = Array.from(event.dataTransfer.files || [])
  handleFiles(files)
}

const handleFiles = (files) => {
  const validFiles = files.filter(file => {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      showErrorToast(`Le fichier ${file.name} n'est pas une image valide`)
      return false
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      showErrorToast(`Le fichier ${file.name} est trop volumineux (max 5MB)`)
      return false
    }
    
    return true
  })

  if (form.images.length + validFiles.length > 10) {
    showErrorToast('Maximum 10 images autorisées')
    return
  }

  validFiles.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => {
      form.images.push({
        file: file,
        preview: e.target?.result,
        name: file.name,
        size: file.size,
        type: file.type
      })
    }
    reader.onerror = () => {
      showErrorToast(`Erreur lors de la lecture du fichier ${file.name}`)
    }
    reader.readAsDataURL(file)
  })

  // Clear the file input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeImage = (index) => {
  form.images.splice(index, 1)
}

const calculateLotteryMetrics = () => {
  // Trigger reactivity for computed properties
}

const formatAmount = (amount) => {
  return new Intl.NumberFormat('fr-FR').format(amount || 0)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getConditionLabel = (condition) => {
  const labels = {
    'new': 'Neuf',
    'used': 'Occasion',
    'refurbished': 'Reconditionné'
  }
  return labels[condition] || condition
}

const handleSubmit = async () => {
  loading.value = true
  clearErrors()

  try {
    // Calculate total tickets based on price and ticket price
    const calculatedTotalTickets = Math.ceil(form.price / form.ticket_price)
    
    // Prepare product data
    const productData = {
      name: form.name,
      description: form.description,
      category_id: parseInt(form.category_id),
      price: parseFloat(form.price),
      sale_mode: form.sale_mode,
      images: form.images.map(img => img.preview) // For now, use base64 images
    }

    // Add lottery-specific fields only if lottery mode
    if (form.sale_mode === 'lottery') {
      productData.ticket_price = parseFloat(form.ticket_price)
      productData.min_participants = parseInt(form.min_tickets)
    }

    // Create product
    const productResponse = await post('/products', productData)
    
    if (productResponse.product) {
      // Create lottery only if lottery mode is selected
      if (form.sale_mode === 'lottery') {
        const lotteryData = {
          duration_days: calculateDurationDays()
        }
        
        try {
          await post(`/products/${productResponse.product.id}/create-lottery`, lotteryData)
          showSuccessToast('Produit et tombola créés avec succès !')
        } catch (lotteryError) {
          console.error('Error creating lottery:', lotteryError)
          showWarningToast('Produit créé mais erreur lors de la création de la tombola')
        }
      } else {
        showSuccessToast('Produit créé avec succès !')
      }
      
      // Redirect to products list
      setTimeout(() => {
        router.push('/merchant/products')
      }, 1500)
    }

  } catch (error) {
    console.error('Error creating product:', error)
    handleApiError(error)
  } finally {
    loading.value = false
  }
}

// Helper functions
const clearErrors = () => {
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}

const handleApiError = (error) => {
  if (error.response?.data?.errors) {
    // Validation errors
    const validationErrors = error.response.data.errors
    Object.keys(validationErrors).forEach(key => {
      if (errors[key] !== undefined) {
        errors[key] = validationErrors[key][0]
      }
    })
    showErrorToast('Veuillez corriger les erreurs dans le formulaire')
  } else if (error.response?.data?.message) {
    showErrorToast(error.response.data.message)
  } else {
    showErrorToast('Une erreur est survenue. Veuillez réessayer.')
  }
}

const calculateDurationDays = () => {
  if (!form.end_date) return 7
  const endDate = new Date(form.end_date)
  const now = new Date()
  const diffTime = Math.abs(endDate - now)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.min(Math.max(diffDays, 1), 30) // Between 1 and 30 days
}

const showSuccessToast = (message) => {
  if (window.$toast) {
    window.$toast.success(message, '✅ Succès')
  }
}

const showErrorToast = (message) => {
  if (window.$toast) {
    window.$toast.error(message, '❌ Erreur')
  }
}

const showWarningToast = (message) => {
  if (window.$toast) {
    window.$toast.warning(message, '⚠️ Attention')
  }
}

// Validation helper
const validateEndDate = () => {
  if (form.end_date) {
    const selectedDate = new Date(form.end_date)
    const now = new Date()
    const maxDate = new Date()
    maxDate.setDate(maxDate.getDate() + 30)
    
    if (selectedDate <= now) {
      errors.end_date = 'La date de fin doit être dans le futur'
    } else if (selectedDate > maxDate) {
      errors.end_date = 'La date de fin ne peut pas dépasser 30 jours'
    } else {
      errors.end_date = ''
    }
  }
}

// Additional validation helpers
const validateTicketPrice = () => {
  if (form.ticket_price) {
    const price = parseFloat(form.ticket_price)
    if (price < 100) {
      errors.ticket_price = 'Le prix minimum est de 100 FCFA'
    } else {
      errors.ticket_price = ''
    }
  }
}

const validateTotalTickets = () => {
  if (form.total_tickets) {
    const tickets = parseInt(form.total_tickets)
    if (tickets < 10) {
      errors.total_tickets = 'Minimum 10 tickets requis'
    } else if (tickets > 10000) {
      errors.total_tickets = 'Maximum 10,000 tickets autorisés'
    } else {
      errors.total_tickets = ''
    }
  }
}

// Load categories on mount
const loadCategories = async () => {
  try {
    const response = await get('/categories')
    if (response.categories) {
      categories.value = response.categories
    }
  } catch (error) {
    console.error('Error loading categories:', error)
    showErrorToast('Erreur lors du chargement des catégories')
  }
}

onMounted(() => {
  loadCategories()
  console.log('CreateProduct page loaded')
})
</script>
