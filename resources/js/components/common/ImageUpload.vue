<template>
  <div class="image-upload-container">
    <!-- Zone de drop pour drag & drop -->
    <div
      @drop="handleDrop"
      @dragover.prevent
      @dragenter.prevent
      :class="[
        'border-2 border-dashed rounded-xl p-6 text-center transition-all duration-300',
        isDragging ? 'border-[#0099cc] bg-[#0099cc]/5' : 'border-gray-300 hover:border-[#0099cc]/50'
      ]"
      @dragenter="isDragging = true"
      @dragleave="isDragging = false"
    >
      <!-- Input file caché -->
      <input
        ref="fileInput"
        type="file"
        multiple
        accept="image/*"
        @change="handleFileSelect"
        class="hidden"
      />

      <!-- Zone de drop ou click -->
      <div v-if="!images.length" @click="openFileDialog" class="cursor-pointer">
        <PhotoIcon class="w-12 h-12 text-gray-400 mx-auto mb-2" />
        <p class="text-gray-600 font-medium">Glissez vos images ici</p>
        <p class="text-sm text-gray-500">ou cliquez pour sélectionner</p>
        <p class="text-xs text-gray-400 mt-2">
          {{ helpText || `JPG, PNG, WebP - Max ${maxSizeMB}MB chacune` }}
        </p>
      </div>

      <!-- Bouton d'ajout quand il y a déjà des images -->
      <button
        v-else
        @click="openFileDialog"
        type="button"
        class="inline-flex items-center px-4 py-2 border-2 border-dashed border-[#0099cc] text-[#0099cc] rounded-lg hover:bg-[#0099cc]/5 transition-colors"
      >
        <PlusIcon class="w-4 h-4 mr-2" />
        Ajouter des images
      </button>
    </div>

    <!-- Prévisualisation des images sélectionnées -->
    <div v-if="images.length" class="mt-4">
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-700">
          {{ images.length }} image(s) sélectionnée(s)
        </h4>
        <button
          @click="clearAll"
          type="button"
          class="text-xs text-red-600 hover:text-red-700 transition-colors"
        >
          Tout supprimer
        </button>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        <div
          v-for="(image, index) in images"
          :key="index"
          class="relative group"
        >
          <!-- Image preview -->
          <div class="aspect-square rounded-lg overflow-hidden border border-gray-200">
            <img
              :src="image.preview"
              :alt="`Image ${index + 1}`"
              class="w-full h-full object-cover"
              @error="handleImageError(index)"
            />
          </div>

          <!-- Boutons d'action -->
          <div class="absolute top-1 right-1 flex space-x-1">
            <!-- Supprimer -->
            <button
              @click="removeImage(index)"
              type="button"
              class="bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
              title="Supprimer"
            >
              <XMarkIcon class="w-3 h-3" />
            </button>
          </div>

          <!-- Indicateur image principale -->
          <div
            v-if="index === 0"
            class="absolute bottom-1 left-1 bg-[#0099cc] text-white text-xs px-2 py-1 rounded"
          >
            Principale
          </div>
          
          <!-- Indicateur image existante -->
          <div
            v-if="image.existing"
            class="absolute top-1 left-1 bg-green-500 text-white text-xs px-2 py-1 rounded"
            title="Image existante"
          >
            Existante
          </div>

          <!-- Statut upload -->
          <div v-if="image.uploading" class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-lg">
            <div class="bg-white rounded-lg p-2">
              <div class="w-6 h-6 border-2 border-[#0099cc] border-t-transparent rounded-full animate-spin"></div>
            </div>
          </div>

          <div v-if="image.error" class="absolute inset-0 bg-red-500/80 flex items-center justify-center rounded-lg">
            <div class="text-white text-xs text-center p-2">
              <ExclamationTriangleIcon class="w-4 h-4 mx-auto mb-1" />
              Erreur upload
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Messages d'erreur -->
    <div v-if="errors.length" class="mt-3">
      <div
        v-for="(error, index) in errors"
        :key="index"
        class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-2 mb-2"
      >
        {{ error }}
      </div>
    </div>

    <!-- Indicateur de progression globale -->
    <div v-if="isUploading" class="mt-4">
      <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
        <span>Upload en cours...</span>
        <span>{{ uploadProgress }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div 
          class="bg-[#0099cc] h-2 rounded-full transition-all duration-300"
          :style="{ width: `${uploadProgress}%` }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { PhotoIcon, PlusIcon, XMarkIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useApi } from '@/composables/api'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  uploadEndpoint: {
    type: String,
    default: '/products/images'
  },
  maxFiles: {
    type: Number,
    default: 10
  },
  maxSizeMB: {
    type: Number,
    default: 5
  },
  helpText: {
    type: String,
    default: null
  },
  autoUpload: {
    type: Boolean,
    default: true
  },
  existingImages: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue', 'success', 'error', 'progress', 'remove-existing'])

const { post } = useApi()

// Reactive state
const fileInput = ref(null)
const images = ref([])
const errors = ref([])
const isDragging = ref(false)
const isUploading = ref(false)
const uploadProgress = ref(0)

// Computed
const remainingSlots = computed(() => props.maxFiles - images.value.length)

// Variable pour éviter les boucles infinies
let isUpdatingFromProps = false
let isEmittingUpdate = false

// Watchers
watch(() => props.modelValue, (newValue, oldValue) => {
  // Éviter la boucle infinie
  if (isEmittingUpdate) return
  
  // Vérifier si la valeur a réellement changé
  const oldUrls = oldValue ? oldValue.sort().join(',') : ''
  const newUrls = newValue ? newValue.sort().join(',') : ''
  if (oldUrls === newUrls) return
  
  isUpdatingFromProps = true
  if (newValue && newValue.length > 0) {
    // Ne recréer les objets images que si nécessaire
    const currentUrls = images.value.filter(img => img.uploaded).map(img => img.url).sort().join(',')
    if (currentUrls !== newUrls) {
      images.value = newValue.map(url => ({
        preview: url,
        url: url,
        uploaded: true
      }))
    }
  } else if (!newValue || newValue.length === 0) {
    images.value = []
  }
  isUpdatingFromProps = false
}, { immediate: true })

// Watcher pour les images existantes
watch(() => props.existingImages, (newExistingImages) => {
  if (newExistingImages && newExistingImages.length > 0) {
    const existingImageObjects = newExistingImages.map(url => ({
      preview: url,
      url: url,
      uploaded: true,
      existing: true // Marquer comme image existante
    }))
    
    // Fusionner avec les images déjà présentes (nouvellement uploadées)
    const newImages = images.value.filter(img => !img.existing)
    images.value = [...existingImageObjects, ...newImages]
  }
}, { immediate: true })

watch(images, (newImages) => {
  // Éviter la boucle infinie
  if (isUpdatingFromProps) return
  
  isEmittingUpdate = true
  const uploadedImages = newImages.filter(img => img.uploaded && img.url).map(img => img.url)
  emit('update:modelValue', uploadedImages)
  
  // Utiliser nextTick pour s'assurer que l'émission est terminée
  nextTick(() => {
    isEmittingUpdate = false
  })
}, { deep: true })

// Methods
const openFileDialog = () => {
  fileInput.value?.click()
}

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  processFiles(files)
  
  // Reset input pour permettre de sélectionner le même fichier
  event.target.value = ''
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragging.value = false
  
  const files = Array.from(event.dataTransfer.files).filter(file => file.type.startsWith('image/'))
  processFiles(files)
}

const processFiles = (files) => {
  errors.value = []
  
  // Vérifier le nombre de fichiers
  if (files.length > remainingSlots.value) {
    errors.value.push(`Maximum ${props.maxFiles} images autorisées. ${remainingSlots.value} emplacements restants.`)
    files = files.slice(0, remainingSlots.value)
  }
  
  files.forEach(file => {
    // Vérifier la taille
    if (file.size > props.maxSizeMB * 1024 * 1024) {
      errors.value.push(`${file.name} est trop volumineux (max ${props.maxSizeMB}MB)`)
      return
    }
    
    // Vérifier le type
    if (!file.type.startsWith('image/')) {
      errors.value.push(`${file.name} n'est pas une image valide`)
      return
    }
    
    // Créer la prévisualisation
    const reader = new FileReader()
    reader.onload = (e) => {
      const imageData = {
        file,
        preview: e.target.result,
        name: file.name,
        size: file.size,
        uploading: false,
        uploaded: false,
        error: false,
        url: null
      }
      
      images.value.push(imageData)
      
      // Auto upload si activé
      if (props.autoUpload) {
        uploadImage(images.value.length - 1)
      }
    }
    
    reader.readAsDataURL(file)
  })
}

const uploadImage = async (index) => {
  const image = images.value[index]
  if (!image || image.uploaded || image.uploading) return
  
  console.log('Starting image upload:', { index, fileName: image.name, fileSize: image.size })
  
  image.uploading = true
  image.error = false
  isUploading.value = true
  
  try {
    const formData = new FormData()
    formData.append('image', image.file)
    formData.append('index', index)
    
    const response = await post(props.uploadEndpoint, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        updateUploadProgress()
        emit('progress', { index, progress })
      }
    })
    
    if (response.success && response.data.url) {
      image.uploaded = true
      image.url = response.data.url
      image.uploading = false
      
      console.log('Image upload completed:', { 
        index, 
        url: response.data.url,
        totalImages: images.value.length,
        uploadedCount: images.value.filter(img => img.uploaded).length
      })
      
      emit('success', { index, url: response.data.url, response })
    } else {
      throw new Error(response.message || 'Erreur lors de l\'upload')
    }
  } catch (error) {
    image.error = true
    image.uploading = false
    errors.value.push(`Erreur upload ${image.name}: ${error.message}`)
    emit('error', { index, error })
  }
  
  updateUploadProgress()
}

const updateUploadProgress = () => {
  const totalImages = images.value.length
  const uploadedImages = images.value.filter(img => img.uploaded).length
  const uploadingImages = images.value.filter(img => img.uploading).length
  
  if (totalImages === 0) {
    uploadProgress.value = 0
    isUploading.value = false
  } else if (uploadingImages === 0) {
    uploadProgress.value = 100
    isUploading.value = false
  } else {
    uploadProgress.value = Math.round((uploadedImages / totalImages) * 100)
    isUploading.value = true
  }
}

const removeImage = (index) => {
  const image = images.value[index]
  
  // Si c'est une image existante, émettre un événement spécial
  if (image && image.existing) {
    emit('remove-existing', { index, url: image.url })
  }
  
  images.value.splice(index, 1)
}

const clearAll = () => {
  images.value = []
  errors.value = []
}

const handleImageError = (index) => {
  const image = images.value[index]
  if (image) {
    image.error = true
  }
}

// Méthodes exposées
defineExpose({
  uploadAll: () => {
    images.value.forEach((image, index) => {
      if (!image.uploaded && !image.uploading) {
        uploadImage(index)
      }
    })
  },
  clearAll,
  getUploadedUrls: () => images.value.filter(img => img.uploaded).map(img => img.url)
})
</script>

<style scoped>
.image-upload-container {
  @apply w-full;
}
</style>