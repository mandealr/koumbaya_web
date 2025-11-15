<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Publier un Nouveau Produit</h1>
        <p class="mt-2 text-gray-600">
          {{ form.sale_mode === 'lottery' ? 'Créez votre tombola et mettez votre produit en jeu' : 'Mettez votre produit en vente directe' }}
        </p>
        <p class="mt-1 text-sm text-blue-600 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Sauvegarde automatique activée
        </p>
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
                : 'border-gray-300 bg-white text-gray-600'
            ]">
              <component :is="step.icon" v-if="currentStep >= step.id" class="w-5 h-5" />
              <span v-else>{{ step.id }}</span>
            </div>
            <div class="ml-3">
              <p :class="[
                'text-sm font-medium',
                currentStep >= step.id ? 'text-[#0099cc]' : 'text-gray-600'
              ]">
                {{ step.title }}
              </p>
              <p class="text-xs text-gray-700">{{ step.description }}</p>
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
              <option v-if="categories.length === 0 && !apiLoading" disabled>Aucune catégorie disponible</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <!-- Debug info -->
            <p class="text-xs text-gray-500 mt-1">
              Debug: {{ categories.length }} catégories chargées
            </p>
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
                    <p class="text-sm text-gray-700">Les clients achètent directement</p>
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
                    <p class="text-sm text-gray-700">Les clients achètent des tickets</p>
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
            <!-- Message d'avertissement pour vendeur individuel -->
            <div v-if="isIndividualSeller && form.sale_mode === 'lottery' && form.price && parseFloat(form.price) < 100000" class="mt-2 bg-blue-50 border border-blue-200 rounded-lg p-2">
              <p class="text-xs text-blue-800">
                <strong>Recommandation:</strong> Pour un vendeur individuel avec 500 tickets fixes, un prix produit minimum de 100,000 FCFA est recommandé pour garantir un prix de ticket d'au moins 200 FCFA.
              </p>
            </div>
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
          <!-- Nouveau composant ImageUpload -->
          <ImageUpload
            v-model="form.imageUrls"
            upload-endpoint="/products/images"
            :max-files="10"
            :max-size-m-b="5"
            help-text="JPG, PNG, WebP - Max 5MB chacune - La première image sera la photo principale"
            @success="handleImageUploadSuccess"
            @error="handleImageUploadError"
            @progress="handleImageUploadProgress"
            @start="handleImageUploadStart"
          />

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
            
            <p class="text-sm text-gray-700 mt-4">
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
              Nombre de tickets *
            </label>
            <!-- BLOQUÉ À 500 TICKETS POUR TOUS -->
            <div>
              <input
                v-model="form.total_tickets"
                type="number"
                required
                readonly
                disabled
                class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 cursor-not-allowed text-gray-600"
              />
              <div class="mt-2 bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1 a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-blue-800">
                      <strong>Nombre fixe :</strong> Le nombre de tickets est automatiquement fixé à 500 pour garantir l'équité et maintenir des prix accessibles.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Prix par ticket (FCFA) *
            </label>
            <!-- Prix du ticket automatiquement calculé (lecture seule) -->
            <input
              v-model="calculatedTicketPrice"
              type="number"
              required
              readonly
              disabled
              class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-100 cursor-not-allowed text-gray-600"
            />
            <div class="mt-2 bg-green-50 border border-green-200 rounded-lg p-3">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm text-green-800">
                    <strong>Calcul automatique :</strong> Le prix du ticket est calculé automatiquement pour couvrir le prix du produit et les frais.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ lotteryDurationConstraints?.can_customize ? 'Durée de la tombola *' : 'Date de fin de vente *' }}
            </label>
            
            <!-- Durée pour vendeurs business -->
            <div v-if="lotteryDurationConstraints?.can_customize">
              <div class="relative">
                <input
                  v-model="form.lottery_duration"
                  type="number"
                  required
                  :min="lotteryDurationConstraints.min_days"
                  :max="lotteryDurationConstraints.max_days"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all" style="color: #5f5f5f"
                  placeholder="30"
                  @input="validateLotteryDuration"
                />
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 pointer-events-none">jours</span>
              </div>
              <p v-if="errors.lottery_duration" class="mt-1 text-sm text-red-600">{{ errors.lottery_duration }}</p>
              <p v-else class="text-sm text-gray-700 mt-1">
                Entre {{ lotteryDurationConstraints.min_days }} et {{ lotteryDurationConstraints.max_days }} jours
              </p>
            </div>
            
            <!-- Durée fixe pour vendeurs individuels -->
            <div v-else-if="lotteryDurationConstraints && !lotteryDurationConstraints.can_customize">
              <div class="relative">
                <input
                  :value="lotteryDurationConstraints.fixed_duration + ' jours'"
                  type="text"
                  readonly
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-100 cursor-not-allowed" style="color: #5f5f5f"
                />
              </div>
              <div class="mt-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-yellow-800">
                      <strong>Profil Vendeur Individuel:</strong> La durée est fixée à {{ lotteryDurationConstraints.fixed_duration }} jours.
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Fallback: date si pas de contraintes chargées -->
            <div v-else>
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
            <p v-else class="text-sm text-gray-700 mt-1">Si pas atteint, remboursement automatique</p>
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
                <span class="text-gray-800">Nom :</span>
                <span class="font-medium">{{ form.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Valeur :</span>
                <span class="font-medium">{{ formatAmount(form.price) }} FCFA</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Condition :</span>
                <span class="font-medium">{{ getConditionLabel(form.condition) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Localisation :</span>
                <span class="font-medium">{{ form.location }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Photos :</span>
                <span class="font-medium">{{ (form.imageUrls?.length || form.images?.length || 0) }} image(s)</span>
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
                <span class="text-gray-800">Mode de vente :</span>
                <span class="font-medium text-[#0099cc]">Vente directe</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Prix de vente :</span>
                <span class="font-medium">{{ formatAmount(form.price) }} FCFA</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Commission (5%) :</span>
                <span class="font-medium">{{ formatAmount(form.price * 0.05) }} FCFA</span>
              </div>
              <div class="flex justify-between border-t pt-3">
                <span class="text-gray-800">Vous recevrez :</span>
                <span class="font-bold text-[#0099cc]">{{ formatAmount(form.price * 0.95) }} FCFA</span>
              </div>
            </div>
            
            <!-- Lottery Summary -->
            <div v-else-if="form.sale_mode === 'lottery'" class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-800">Mode de vente :</span>
                <span class="font-medium text-purple-600">Tombola</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Total tickets :</span>
                <span class="font-medium">{{ form.total_tickets }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Prix/ticket :</span>
                <span class="font-medium">{{ formatAmount(calculatedTicketPrice) }} FCFA</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Minimum requis :</span>
                <span class="font-medium">{{ form.min_tickets }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-800">Durée :</span>
                <span class="font-medium">
                  {{ lotteryDurationConstraints && !lotteryDurationConstraints.can_customize ? 
                     lotteryDurationConstraints.fixed_duration : 
                     (form.lottery_duration || calculateDurationDays()) 
                  }} jours
                </span>
              </div>
              <div class="flex justify-between border-t pt-3">
                <span class="text-gray-800">Revenus max :</span>
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
            <span class="text-sm text-gray-900">
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

        <div class="text-sm text-gray-700">
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
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useApi } from '../../composables/api'
import { useAuthStore } from '../../stores/auth'
import ImageUpload from '../../components/common/ImageUpload.vue'
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
const { user, isIndividualSeller, isBusinessSeller } = useAuthStore()

// State
const currentStep = ref(1)
const loading = ref(false)
const fileInput = ref(null)

// Steps computed dynamically based on sale_mode
const steps = computed(() => [
  { id: 1, title: 'Informations', description: 'Détails du produit', icon: InformationCircleIcon },
  { id: 2, title: 'Photos', description: 'Images du produit', icon: CameraIcon },
  {
    id: 3,
    title: form.sale_mode === 'lottery' ? 'Tombola' : 'Configuration',
    description: form.sale_mode === 'lottery' ? 'Paramètres tombola' : 'Vente directe',
    icon: CheckIcon
  },
  { id: 4, title: 'Publication', description: 'Vérification', icon: RocketLaunchIcon }
])

const categories = ref([])
const lotteryDurationConstraints = ref(null)

const form = reactive({
  name: '',
  description: '',
  category_id: '',
  condition: '',
  sale_mode: 'direct', // 'direct' or 'lottery'
  price: '', // Changed from 'value' to 'price' to match API
  location: '',
  images: [], // Ancien système pour compatibilité
  imageUrls: [], // Nouveau système avec URLs des images uploadées
  ticket_price: '',
  total_tickets: 500, // BLOQUÉ À 500 TICKETS MAX
  min_tickets: '', // This will be 'min_participants' in the API
  end_date: '',
  lottery_duration: '', // Durée en jours pour les vendeurs business
  terms_accepted: false,
  duration_days: 7 // Pour la création de la tombola
})

// Watcher pour gérer le changement de mode de vente
watch(() => form.sale_mode, (newMode, oldMode) => {
  console.log('=== CHANGEMENT MODE DE VENTE ===')
  console.log('Old mode:', oldMode)
  console.log('New mode:', newMode)
  console.log('Is individual seller:', isIndividualSeller.value)
  
  if (newMode === 'lottery') {
    // TOUJOURS forcer 500 tickets en mode tombola (restriction globale)
    console.log('Forcing 500 tickets for lottery mode')
    form.total_tickets = 500
    form.min_tickets = 500 // Minimum = maximum = 500
  }

  // Réinitialiser les champs spécifiques à la tombola si on passe en vente directe
  if (newMode === 'direct') {
    console.log('Switching to direct sale - clearing lottery fields')
    form.ticket_price = ''
    form.total_tickets = 500
    form.min_tickets = ''
    form.end_date = ''
  }
  
  console.log('Updated form state:', {
    sale_mode: form.sale_mode,
    ticket_price: form.ticket_price,
    total_tickets: form.total_tickets
  })
  console.log('================================')
})

const errors = reactive({
  name: '',
  description: '',
  category_id: '',
  condition: '',
  price: '', // Changé de 'value' à 'price'
  location: '',
  images: '',
  ticket_price: '',
  total_tickets: '',
  min_tickets: '',
  end_date: '',
  lottery_duration: ''
})

// Computed
const minDate = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().slice(0, 16)
})

// Computed property for automatic ticket price calculation
const calculatedTicketPrice = computed(() => {
  if (form.sale_mode !== 'lottery' || !form.price || !form.total_tickets) {
    return 0
  }
  
  const productPrice = parseFloat(form.price)
  const ticketCount = parseInt(form.total_tickets)
  
  if (productPrice <= 0 || ticketCount <= 0) {
    return 0
  }
  
  // Configuration des taux (à terme depuis les paramètres admin)
  const commissionRate = 0.10 // 10%
  const marginRate = 0.15     // 15%
  
  // Formule: Prix ticket = (Prix Article + Commission + Marge) ÷ Nombre de Tickets
  const commission = productPrice * commissionRate
  const margin = productPrice * marginRate
  const totalAmount = productPrice + commission + margin
  const ticketPrice = totalAmount / ticketCount
  
  console.log('=== CALCUL AUTOMATIQUE PRIX TICKET ===')
  console.log('Product price:', productPrice)
  console.log('Ticket count:', ticketCount)
  console.log('Commission (10%):', commission)
  console.log('Margin (15%):', margin)
  console.log('Total amount:', totalAmount)
  console.log('Calculated ticket price:', ticketPrice)
  console.log('=====================================')
  
  return Math.round(ticketPrice) // Arrondir à l'entier le plus proche
})

const lotteryMetrics = computed(() => {
  const totalRevenue = (calculatedTicketPrice.value || 0) * (form.total_tickets || 0)
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

// Watcher to update form.ticket_price when calculated value changes
watch(calculatedTicketPrice, (newValue) => {
  if (form.sale_mode === 'lottery' && newValue !== parseFloat(form.ticket_price)) {
    form.ticket_price = newValue.toString()
    console.log('Updated form.ticket_price to calculated value:', newValue)
  }
}, { flush: 'post' })

// État pour l'upload d'images
const isUploadingImages = ref(false)

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1:
      return validateStep1()
    case 2:
      // Vérifier qu'au moins une image est uploadée en utilisant l'état interne
      const hasImages = (form.imageUrls && form.imageUrls.length > 0) || (form.images && form.images.length > 0)
      const uploadInProgress = isUploadingImages.value
      
      console.log('=== VALIDATION ÉTAPE 2 ===')
      console.log('Total images (new system):', form.imageUrls?.length || 0)
      console.log('Total images (old system):', form.images?.length || 0)
      console.log('Has images:', hasImages)
      console.log('Upload in progress:', uploadInProgress)
      console.log('Can proceed:', hasImages && !uploadInProgress)
      console.log('=========================')
      
      return hasImages && !uploadInProgress // Au moins une image uploadée ET aucun upload en cours
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
  console.log('=== VALIDATION ÉTAPE 3 ===')
  console.log('Sale mode:', form.sale_mode)

  // Direct sale mode - always valid (no extra fields required)
  if (form.sale_mode === 'direct') {
    console.log('Direct sale mode - always valid')
    console.log('==========================')
    return true
  }

  // Lottery mode - validate lottery fields
  if (form.sale_mode === 'lottery') {
    console.log('Lottery mode - validating fields...')
    console.log('Form values:', {
      ticket_price: form.ticket_price,
      total_tickets: form.total_tickets,
      min_tickets: form.min_tickets,
      end_date: form.end_date,
      lottery_duration: form.lottery_duration,
      has_duration_constraints: !!lotteryDurationConstraints.value
    })

    // Vérifier les champs de base (toujours requis)
    const basicChecks = {
      hasTicketPrice: !!form.ticket_price,
      hasTotalTickets: !!form.total_tickets,
      hasMinTickets: !!form.min_tickets,
      ticketPriceValid: parseFloat(form.ticket_price) >= 100,
      totalTicketsValid: parseInt(form.total_tickets) >= 10,
      minTicketsValid: parseInt(form.min_tickets) >= 10,
      minTicketsNotExceedTotal: parseInt(form.min_tickets) <= parseInt(form.total_tickets)
    }

    // Vérifier la durée (soit via end_date, soit via lottery_duration selon le profil)
    let durationCheck = false
    if (lotteryDurationConstraints.value && !lotteryDurationConstraints.value.can_customize) {
      // Vendeur individuel : pas besoin de validation de durée (elle est fixe)
      console.log('Individual seller - duration is fixed, no validation needed')
      durationCheck = true
    } else if (lotteryDurationConstraints.value?.can_customize && form.lottery_duration) {
      // Vendeur business : vérifier lottery_duration
      console.log('Business seller - validating lottery_duration')
      durationCheck = parseInt(form.lottery_duration) >= 1
    } else if (form.end_date) {
      // Fallback : vérifier end_date
      console.log('Using end_date fallback')
      durationCheck = new Date(form.end_date) > new Date()
    }

    const checks = { ...basicChecks, durationCheck }
    console.log('Validation checks:', checks)

    // Validation globale
    const isValid = form.ticket_price &&
                    form.total_tickets &&
                    form.min_tickets &&
                    parseFloat(form.ticket_price) >= 100 &&
                    parseInt(form.total_tickets) >= 10 &&
                    parseInt(form.min_tickets) >= 10 &&
                    parseInt(form.min_tickets) <= parseInt(form.total_tickets) &&
                    durationCheck // Durée valide selon le profil

    console.log('Overall validation result:', isValid)

    // Clear step 3 errors if valid
    if (isValid) {
      errors.ticket_price = ''
      errors.total_tickets = ''
      errors.min_tickets = ''
      errors.end_date = ''
      errors.lottery_duration = ''
      console.log('Cleared all step 3 errors')
    } else {
      console.log('Validation failed - checking individual fields...')
      if (!form.ticket_price || parseFloat(form.ticket_price) < 100) {
        console.log('Ticket price invalid')
      }
      if (!form.total_tickets || parseInt(form.total_tickets) < 10) {
        console.log('Total tickets invalid')
      }
      if (!form.min_tickets || parseInt(form.min_tickets) < 10) {
        console.log('Min tickets invalid')
      }
      if (parseInt(form.min_tickets) > parseInt(form.total_tickets)) {
        console.log('Min tickets exceeds total tickets')
      }
      if (!durationCheck) {
        console.log('Duration validation failed')
      }
    }

    console.log('==========================')
    return isValid
  }

  console.log('Unknown sale mode - returning false')
  console.log('==========================')
  return false
}

// Methods
const nextStep = () => {
  console.log('=== NAVIGATION: NEXT STEP ===')
  console.log('Current step:', currentStep.value)
  console.log('Can proceed:', canProceed.value)
  console.log('Form state:', {
    name: form.name,
    description: form.description?.substring(0, 50) + '...',
    category_id: form.category_id,
    condition: form.condition,
    sale_mode: form.sale_mode,
    price: form.price,
    location: form.location,
    images: form.images.length,
    imageUrls: form.imageUrls.length,
    ticket_price: form.ticket_price,
    total_tickets: form.total_tickets,
    min_tickets: form.min_tickets,
    end_date: form.end_date,
    lottery_duration: form.lottery_duration
  })
  
  if (canProceed.value && currentStep.value < steps.value.length) {
    console.log(`Moving from step ${currentStep.value} to step ${currentStep.value + 1}`)
    currentStep.value++
    
    // Log spécifique quand on arrive à l'étape 3 (tombola)
    if (currentStep.value === 3) {
      console.log('=== ARRIVÉE À L\'ÉTAPE 3: CONFIGURATION TOMBOLA ===')
      console.log('Sale mode:', form.sale_mode)
      console.log('Lottery duration constraints:', lotteryDurationConstraints.value)
      console.log('Is individual seller:', isIndividualSeller.value)
      console.log('Form lottery values:', {
        ticket_price: form.ticket_price,
        total_tickets: form.total_tickets,
        min_tickets: form.min_tickets,
        lottery_duration: form.lottery_duration
      })
    }
  } else {
    console.log('Cannot proceed - validation failed')
    console.log('Validation details:', {
      step1Valid: currentStep.value === 1 ? validateStep1() : 'N/A',
      step2Valid: currentStep.value === 2 ? true : 'N/A', // Images optional
      step3Valid: currentStep.value === 3 ? validateStep3() : 'N/A'
    })
  }
  console.log('=============================')
}

const previousStep = () => {
  console.log('=== NAVIGATION: PREVIOUS STEP ===')
  console.log(`Moving from step ${currentStep.value} to step ${currentStep.value - 1}`)
  if (currentStep.value > 1) {
    currentStep.value--
  }
  console.log('=================================')
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

// Handlers pour le nouveau système ImageUpload
const handleImageUploadStart = ({ index }) => {
  console.log('=== ÉTAPE 2: IMAGE UPLOAD START ===')
  console.log('Starting upload for image index:', index)
  isUploadingImages.value = true
  console.log('Upload state set to:', isUploadingImages.value)
  console.log('====================================')
}

const handleImageUploadSuccess = ({ index, url, response }) => {
  console.log('=== ÉTAPE 2: IMAGE UPLOAD SUCCESS ===')
  console.log('Index:', index)
  console.log('URL:', url)
  console.log('Response:', response)
  console.log('Total images après upload:', form.imageUrls?.length || 0)
  
  // Réinitialiser l'état d'upload après succès
  isUploadingImages.value = false
  
  showSuccessToast(`Image ${index + 1} uploadée avec succès`)
  
  // Le v-model du composant ImageUpload gère automatiquement la mise à jour de form.imageUrls
  // Pas besoin de modifier manuellement form.imageUrls ici
  console.log('=====================================')
}

const handleImageUploadError = ({ index, error }) => {
  console.log('=== ÉTAPE 2: IMAGE UPLOAD ERROR ===')
  console.log('Index:', index)
  console.log('Error:', error)
  console.log('Error message:', error.message)
  console.log('Error stack:', error.stack)
  
  // Réinitialiser l'état d'upload après erreur
  isUploadingImages.value = false
  
  console.error('Erreur upload image:', { index, error })
  showErrorToast(`Erreur lors de l'upload de l'image ${index + 1}: ${error.message}`)
  console.log('===================================')
}

const handleImageUploadProgress = ({ index, progress }) => {
  console.log(`=== ÉTAPE 2: Upload image ${index + 1}: ${progress}% ===`)
  
  // Marquer qu'un upload est en cours
  if (progress > 0 && progress < 100) {
    isUploadingImages.value = true
  } else if (progress === 100) {
    // Attendre un peu avant de réinitialiser, car le traitement serveur peut prendre du temps
    setTimeout(() => {
      if (isUploadingImages.value) {
        isUploadingImages.value = false
      }
    }, 1000)
  }
}

// Ancien système pour compatibilité (sera supprimé plus tard)
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
  console.log('=== ÉTAPE 3: CALCUL DES MÉTRIQUES TOMBOLA ===')
  console.log('Sale mode:', form.sale_mode)
  console.log('Inputs:', {
    price: form.price,
    total_tickets: form.total_tickets,
    calculated_ticket_price: calculatedTicketPrice.value
  })
  
  // Update form.ticket_price with calculated value
  form.ticket_price = calculatedTicketPrice.value.toString()
  
  // Log computed metrics
  console.log('Computed metrics:', {
    totalRevenue: lotteryMetrics.value.totalRevenue,
    platformFee: lotteryMetrics.value.platformFee,
    netRevenue: lotteryMetrics.value.netRevenue,
    profitMargin: lotteryMetrics.value.profitMargin
  })
  console.log('==============================================')
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
  console.log('=== SOUMISSION FINALE DU FORMULAIRE ===')
  console.log('Form state before submission:', {
    name: form.name,
    description: form.description?.substring(0, 50) + '...',
    category_id: form.category_id,
    condition: form.condition,
    sale_mode: form.sale_mode,
    price: form.price,
    location: form.location,
    images: form.images.length,
    imageUrls: form.imageUrls,
    ticket_price: form.ticket_price,
    total_tickets: form.total_tickets,
    min_tickets: form.min_tickets,
    end_date: form.end_date,
    lottery_duration: form.lottery_duration,
    terms_accepted: form.terms_accepted
  })
  
  loading.value = true
  clearErrors()

  try {
    // Prepare product data with reversed lottery logic (user inputs tickets, system calculates price)
    const productData = {
      name: form.name,
      description: form.description,
      category_id: parseInt(form.category_id),
      price: parseFloat(form.price),
      sale_mode: form.sale_mode,
      images: form.imageUrls.length > 0 ? form.imageUrls : form.images.map(img => img.preview) // Utiliser les URLs uploadées ou fallback vers base64
    }
    
    console.log('Product data to submit:', productData)

    // Add lottery-specific fields only if lottery mode
    if (form.sale_mode === 'lottery') {
      productData.ticket_price = calculatedTicketPrice.value
      productData.total_tickets = 500 // TOUJOURS 500
      productData.min_participants = 500 // TOUJOURS 500 (même valeur que total_tickets)

      console.log('Adding lottery fields:', {
        ticket_price: productData.ticket_price,
        total_tickets: productData.total_tickets,
        min_participants: productData.min_participants
      })

      // Ajouter la durée de tombola si pertinent
      if (lotteryDurationConstraints.value?.can_customize) {
        productData.lottery_duration = parseInt(form.lottery_duration)
        console.log('Added custom lottery duration:', productData.lottery_duration)
      }
    }

    console.log('Final product data:', productData)

    // Create product
    console.log('Creating product...')
    const productResponse = await post('/products', productData)
    console.log('Product creation response:', productResponse)
    
    if (productResponse.product) {
      console.log('Product created successfully, ID:', productResponse.product.id)

      // Effacer le brouillon après une publication réussie
      clearFormFromLocalStorage()

      // Product creation automatically creates lottery if sale_mode is 'lottery'
      // No need to make separate lottery creation call
      if (form.sale_mode === 'lottery') {
        console.log('Lottery was automatically created with the product')
        showSuccessToast('Produit et tombola créés avec succès !')
      } else {
        showSuccessToast('Produit créé avec succès !')
      }

      // Redirect to products list
      console.log('Redirecting to products list...')
      setTimeout(() => {
        router.push('/merchant/products')
      }, 1500)
    }

  } catch (error) {
    console.error('Error creating product:', error)
    console.error('Error response:', error.response?.data)
    handleApiError(error)
  } finally {
    loading.value = false
    console.log('========================================')
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
  // Pour les vendeurs avec durée personnalisable
  if (lotteryDurationConstraints.value?.can_customize && form.lottery_duration) {
    return parseInt(form.lottery_duration)
  }
  
  // Pour les vendeurs avec durée fixe
  if (lotteryDurationConstraints.value && !lotteryDurationConstraints.value.can_customize) {
    return lotteryDurationConstraints.value.fixed_duration
  }
  
  // Fallback: calculer depuis la date de fin si présente
  if (form.end_date) {
    const endDate = new Date(form.end_date)
    const now = new Date()
    const diffTime = Math.abs(endDate - now)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    // Respecter les contraintes si elles existent
    if (lotteryDurationConstraints.value) {
      const minDays = lotteryDurationConstraints.value.min_days || 1
      const maxDays = lotteryDurationConstraints.value.max_days || 60
      return Math.min(Math.max(diffDays, minDays), maxDays)
    }
    
    return Math.min(Math.max(diffDays, 1), 30)
  }
  
  // Valeur par défaut
  return lotteryDurationConstraints.value?.default_duration || 30
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
  console.log('=== ÉTAPE 3: VALIDATION PRIX TICKET ===')
  console.log('Ticket price:', form.ticket_price)
  
  if (form.ticket_price) {
    const price = parseFloat(form.ticket_price)
    console.log('Parsed price:', price)
    
    if (price < 100) {
      errors.ticket_price = 'Le prix minimum est de 100 FCFA'
      console.log('Validation failed: price too low')
    } else {
      errors.ticket_price = ''
      console.log('Validation passed')
    }
    
    // Log du calcul du prix minimum recommandé
    if (isIndividualSeller.value && form.price) {
      const minRecommendedPrice = parseFloat(form.price) / 500 // 500 tickets fixes
      console.log('Recommended minimum ticket price for individual seller:', minRecommendedPrice)
      console.log('Current ticket price meets recommendation:', price >= minRecommendedPrice)
    }
  }
  console.log('=======================================')
}

const validateTotalTickets = () => {
  console.log('=== ÉTAPE 3: VALIDATION NOMBRE TICKETS ===')
  console.log('Total tickets:', form.total_tickets)
  console.log('Is individual seller:', isIndividualSeller.value)
  
  if (form.total_tickets) {
    const tickets = parseInt(form.total_tickets)
    console.log('Parsed tickets:', tickets)
    
    // Pour vendeur individuel, toujours 500 tickets
    if (isIndividualSeller.value) {
      console.log('Forcing 500 tickets for individual seller')
      form.total_tickets = '500'
      errors.total_tickets = ''
      return
    }
    
    if (tickets < 10) {
      errors.total_tickets = 'Minimum 10 tickets requis'
      console.log('Validation failed: too few tickets')
    } else if (tickets > 10000) {
      errors.total_tickets = 'Maximum 10,000 tickets autorisés'
      console.log('Validation failed: too many tickets')
    } else {
      errors.total_tickets = ''
      console.log('Validation passed')
    }
  }
  console.log('==========================================')
}

// Validation de la durée de tombola
const validateLotteryDuration = () => {
  if (!lotteryDurationConstraints.value || !lotteryDurationConstraints.value.can_customize) {
    errors.lottery_duration = ''
    return
  }
  
  if (form.lottery_duration) {
    const duration = parseInt(form.lottery_duration)
    const minDays = lotteryDurationConstraints.value.min_days
    const maxDays = lotteryDurationConstraints.value.max_days
    
    if (isNaN(duration)) {
      errors.lottery_duration = 'Veuillez entrer un nombre valide'
    } else if (duration < minDays) {
      errors.lottery_duration = `Minimum ${minDays} jour${minDays > 1 ? 's' : ''} requis`
    } else if (duration > maxDays) {
      errors.lottery_duration = `Maximum ${maxDays} jours autorisés`
    } else {
      errors.lottery_duration = ''
    }
  }
}

// Sauvegarde automatique dans localStorage
const STORAGE_KEY = 'koumbaya_create_product_draft'

const saveFormToLocalStorage = () => {
  try {
    const draftData = {
      form: {
        name: form.name,
        description: form.description,
        category_id: form.category_id,
        condition: form.condition,
        sale_mode: form.sale_mode,
        price: form.price,
        location: form.location,
        imageUrls: form.imageUrls,
        ticket_price: form.ticket_price,
        total_tickets: form.total_tickets,
        min_tickets: form.min_tickets,
        end_date: form.end_date,
        lottery_duration: form.lottery_duration
      },
      currentStep: currentStep.value,
      timestamp: new Date().toISOString()
    }
    localStorage.setItem(STORAGE_KEY, JSON.stringify(draftData))
    console.log('✓ Brouillon sauvegardé automatiquement')
  } catch (error) {
    console.error('Erreur lors de la sauvegarde du brouillon:', error)
  }
}

const loadFormFromLocalStorage = () => {
  try {
    const savedData = localStorage.getItem(STORAGE_KEY)
    if (savedData) {
      const draftData = JSON.parse(savedData)
      console.log('=== RESTAURATION DU BROUILLON ===')
      console.log('Date de sauvegarde:', draftData.timestamp)

      // Demander confirmation à l'utilisateur
      if (confirm('Un brouillon de produit a été trouvé. Voulez-vous le restaurer ?')) {
        // Restaurer les données du formulaire
        Object.assign(form, draftData.form)
        currentStep.value = draftData.currentStep || 1

        showSuccessToast('Brouillon restauré avec succès')
        console.log('Brouillon restauré')
      } else {
        // Effacer le brouillon si l'utilisateur refuse
        clearFormFromLocalStorage()
        console.log('Brouillon refusé et effacé')
      }
      console.log('=================================')
    }
  } catch (error) {
    console.error('Erreur lors de la restauration du brouillon:', error)
  }
}

const clearFormFromLocalStorage = () => {
  try {
    localStorage.removeItem(STORAGE_KEY)
    console.log('✓ Brouillon effacé')
  } catch (error) {
    console.error('Erreur lors de l\'effacement du brouillon:', error)
  }
}

// Watcher pour sauvegarder automatiquement à chaque modification
watch(() => ({ ...form }), () => {
  saveFormToLocalStorage()
}, { deep: true, debounce: 500 })

// Watcher pour sauvegarder l'étape actuelle
watch(currentStep, () => {
  saveFormToLocalStorage()
})

// Load categories on mount
const loadCategories = async () => {
  try {
    console.log('Loading categories...')
    const response = await get('/categories')
    console.log('Categories response:', response)
    console.log('Response keys:', Object.keys(response))
    if (response && response.categories) {
      categories.value = response.categories
      console.log('Categories loaded:', categories.value.length, 'items')
    } else if (response && response.data) {
      // Fallback si categories n'existe pas mais data existe
      categories.value = response.data
      console.log('Categories loaded from data:', categories.value.length, 'items')
    } else {
      console.log('No categories in response or response is null')
    }
  } catch (error) {
    console.error('Error loading categories:', error)
    showErrorToast('Erreur lors du chargement des catégories')
  }
}

// Charger les contraintes de durée de tombola
const loadLotteryDurationConstraints = async () => {
  console.log('=== CHARGEMENT CONTRAINTES DURÉE TOMBOLA ===')
  console.log('User type:', user.value?.seller_type)
  console.log('Is individual seller:', isIndividualSeller.value)
  console.log('Is business seller:', isBusinessSeller.value)
  
  try {
    const response = await get('/products/lottery-duration-constraints')
    console.log('API Response:', response)
    
    if (response && response.data) {
      lotteryDurationConstraints.value = response.data.constraints
      console.log('Lottery duration constraints loaded:', lotteryDurationConstraints.value)
      console.log('Can customize:', lotteryDurationConstraints.value?.can_customize)
      console.log('Fixed duration:', lotteryDurationConstraints.value?.fixed_duration)
      console.log('Min days:', lotteryDurationConstraints.value?.min_days)
      console.log('Max days:', lotteryDurationConstraints.value?.max_days)
      console.log('Default duration:', lotteryDurationConstraints.value?.default_duration)
      
      // Si vendeur individuel, définir la durée fixe
      if (lotteryDurationConstraints.value && !lotteryDurationConstraints.value.can_customize) {
        form.lottery_duration = lotteryDurationConstraints.value.fixed_duration
        console.log('Set fixed duration for individual seller:', form.lottery_duration)
      } else {
        // Pour les vendeurs business, utiliser la durée par défaut
        form.lottery_duration = lotteryDurationConstraints.value?.default_duration || 30
        console.log('Set default duration for business seller:', form.lottery_duration)
      }
    }
  } catch (error) {
    console.error('Error loading lottery duration constraints:', error)
    console.error('Error details:', error.response?.data)
    // En cas d'erreur, utiliser les valeurs par défaut
    form.lottery_duration = 30
    console.log('Using fallback duration:', form.lottery_duration)
  }
  console.log('============================================')
}

onMounted(() => {
  console.log('=== INITIALISATION CREATE PRODUCT PAGE ===')
  console.log('User:', user.value)
  console.log('User seller type:', user.value?.seller_type)
  console.log('Is individual seller:', isIndividualSeller.value)
  console.log('Is business seller:', isBusinessSeller.value)

  // Charger les données avant de restaurer le brouillon
  loadCategories()
  loadLotteryDurationConstraints()

  // Vérifier et restaurer un brouillon sauvegardé
  loadFormFromLocalStorage()

  // Forcer le nombre de tickets à 500 pour les vendeurs individuels (si pas de brouillon)
  if (isIndividualSeller.value && !form.total_tickets) {
    console.log('Setting default 500 tickets for individual seller')
    form.total_tickets = '500'
  }

  // Set default end date (tomorrow) si pas déjà défini
  if (!form.end_date) {
    const tomorrow = new Date()
    tomorrow.setDate(tomorrow.getDate() + 1)
    tomorrow.setHours(12, 0, 0, 0)
    form.end_date = tomorrow.toISOString().slice(0, 16)
    console.log('Set default end date:', form.end_date)
  }

  console.log('Initial form state:', {
    sale_mode: form.sale_mode,
    total_tickets: form.total_tickets,
    end_date: form.end_date,
    lottery_duration: form.lottery_duration
  })
  console.log('=========================================')
})
</script>
