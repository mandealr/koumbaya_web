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
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
              placeholder="Ex: iPhone 15 Pro Max 256GB"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Description *
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
              placeholder="Décrivez votre produit en détail..."
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Catégorie *
            </label>
            <select
              v-model="form.category_id"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
            >
              <option value="">Sélectionner une catégorie</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Condition *
            </label>
            <select
              v-model="form.condition"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
            >
              <option value="">État du produit</option>
              <option value="new">Neuf</option>
              <option value="like_new">Comme neuf</option>
              <option value="good">Bon état</option>
              <option value="fair">État correct</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Valeur du produit (FCFA) *
            </label>
            <input
              v-model="form.value"
              type="number"
              required
              min="0"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
              placeholder="Ex: 800000"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Localisation *
            </label>
            <input
              v-model="form.location"
              type="text"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
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
              accept="image/*"
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
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 3: Lottery Settings -->
      <div v-if="currentStep === 3" class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
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
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
              placeholder="Ex: 2500"
              @input="calculateLotteryMetrics"
            />
            <p class="text-sm text-gray-500 mt-1">Minimum 100 FCFA</p>
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
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
              placeholder="Ex: 400"
              @input="calculateLotteryMetrics"
            />
            <p class="text-sm text-gray-500 mt-1">Entre 10 et 10,000 tickets</p>
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
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
            />
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
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all text-black"
              placeholder="Ex: 200"
            />
            <p class="text-sm text-gray-500 mt-1">Si pas atteint, remboursement automatique</p>
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
                  <p class="text-2xl font-bold text-green-600">{{ lotteryMetrics.profitMargin }}%</p>
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
                <span class="font-medium">{{ formatAmount(form.value) }} FCFA</span>
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

          <!-- Lottery Summary -->
          <div>
            <h3 class="font-semibold text-gray-900 mb-4">Configuration tombola</h3>
            <div class="space-y-3">
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
        <div class="mt-8 p-4 bg-gray-50 rounded-xl">
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
              des frais de 5% sur les revenus de la tombola.
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
          class="inline-flex items-center px-6 py-3 bg-[#0099cc] hover:bg-[#0088bb] text-white rounded-lg transition-colors"
          :disabled="!canProceed"
        >
          Suivant
          <ArrowRightIcon class="w-4 h-4 ml-2" />
        </button>

        <button
          v-if="currentStep === steps.length"
          type="submit"
          :disabled="loading || !canProceed"
          class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white rounded-lg transition-colors"
        >
          <span v-if="loading" class="flex items-center">
            <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></div>
            Publication...
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

const categories = ref([
  { id: 1, name: 'Électronique' },
  { id: 2, name: 'Mode & Accessoires' },
  { id: 3, name: 'Maison & Jardin' },
  { id: 4, name: 'Sport & Loisirs' },
  { id: 5, name: 'Automobile' },
  { id: 6, name: 'Autres' }
])

const form = reactive({
  name: '',
  description: '',
  category_id: '',
  condition: '',
  value: '',
  location: '',
  images: [],
  ticket_price: '',
  total_tickets: '',
  min_tickets: '',
  end_date: '',
  terms_accepted: false
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
  const profitMargin = form.value ? Math.round(((netRevenue - form.value) / form.value) * 100) : 0

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
      return form.name && form.description && form.category_id && form.condition && form.value && form.location
    case 2:
      return form.images.length > 0
    case 3:
      return form.ticket_price && form.total_tickets && form.min_tickets && form.end_date
    case 4:
      return form.terms_accepted
    default:
      return false
  }
})

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
  const validFiles = files.filter(file => file.type.startsWith('image/'))

  if (form.images.length + validFiles.length > 10) {
    alert('Maximum 10 images autorisées')
    return
  }

  validFiles.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => {
      form.images.push({
        file: file,
        preview: e.target?.result,
        name: file.name
      })
    }
    reader.readAsDataURL(file)
  })
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
    'like_new': 'Comme neuf',
    'good': 'Bon état',
    'fair': 'État correct'
  }
  return labels[condition] || condition
}

const handleSubmit = async () => {
  loading.value = true

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))

    alert('Produit publié avec succès ! Votre tombola est maintenant en ligne.')
    router.push('/merchant/products')

  } catch (error) {
    console.error('Error creating product:', error)
    alert('Erreur lors de la publication. Veuillez réessayer.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  console.log('Create product page loaded')
})
</script>
