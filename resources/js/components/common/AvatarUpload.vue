<template>
  <div class="avatar-upload-container">
    <!-- Avatar actuel et prévisualisation -->
    <div class="relative group">
      <div 
        class="w-24 h-24 rounded-full overflow-hidden border-4 border-gray-200 bg-gray-100 flex items-center justify-center cursor-pointer hover:border-blue-400 transition-colors duration-200"
        @click="triggerFileInput"
      >
        <!-- Image actuelle ou prévisualisée -->
        <img
          v-if="previewUrl || currentAvatarUrl"
          :src="previewUrl || currentAvatarUrl"
          :alt="altText"
          class="w-full h-full object-cover"
          @error="onImageError"
        />
        <!-- Placeholder si pas d'image -->
        <div v-else class="flex flex-col items-center justify-center text-gray-400">
          <UserIcon class="h-8 w-8 mb-1" />
          <span class="text-xs">Photo</span>
        </div>
      </div>

      <!-- Overlay d'édition au survol -->
      <div class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 cursor-pointer" @click="triggerFileInput">
        <div class="text-white text-center">
          <CameraIcon class="h-6 w-6 mx-auto mb-1" />
          <span class="text-xs">Modifier</span>
        </div>
      </div>

      <!-- Badge de chargement -->
      <div v-if="isUploading" class="absolute inset-0 bg-black bg-opacity-60 rounded-full flex items-center justify-center">
        <div class="text-white text-center">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-white mx-auto mb-1"></div>
          <span class="text-xs">Upload...</span>
        </div>
      </div>
    </div>

    <!-- Input file caché -->
    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      class="hidden"
      @change="onFileSelected"
    />

    <!-- Informations et boutons -->
    <div class="mt-4 text-center">
      <p class="text-sm text-gray-600 mb-2">
        {{ helpText || 'Cliquez sur la photo pour la modifier' }}
      </p>
      <p class="text-xs text-gray-500">
        Formats acceptés: JPG, PNG, GIF (max {{ maxSizeMB }}MB)
      </p>
      
      <!-- Boutons d'action -->
      <div class="flex justify-center space-x-2 mt-3" v-if="previewUrl">
        <button
          @click="uploadAvatar"
          :disabled="isUploading"
          class="px-4 py-2 bg-blue-500 text-white text-sm rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
        >
          <CloudArrowUpIcon class="h-4 w-4" />
          {{ isUploading ? 'Upload...' : 'Sauvegarder' }}
        </button>
        <button
          @click="cancelPreview"
          :disabled="isUploading"
          class="px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-400 disabled:opacity-50"
        >
          Annuler
        </button>
      </div>
    </div>

    <!-- Messages d'erreur -->
    <div v-if="errorMessage" class="mt-2 text-center">
      <p class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-md px-3 py-2">
        {{ errorMessage }}
      </p>
    </div>

    <!-- Messages de succès -->
    <div v-if="successMessage" class="mt-2 text-center">
      <p class="text-sm text-green-600 bg-green-50 border border-green-200 rounded-md px-3 py-2">
        {{ successMessage }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useApi } from '@/composables/api'
import { UserIcon, CameraIcon, CloudArrowUpIcon } from '@heroicons/vue/24/outline'
import { handleImageError, PLACEHOLDERS } from '@/utils/imageUtils'

// Props
const props = defineProps({
  currentAvatarUrl: {
    type: String,
    default: null
  },
  uploadEndpoint: {
    type: String,
    default: '/user/avatar'
  },
  fieldName: {
    type: String,
    default: 'avatar'
  },
  altText: {
    type: String,
    default: 'Photo de profil'
  },
  helpText: {
    type: String,
    default: null
  },
  maxSizeMB: {
    type: Number,
    default: 5
  }
})

// Emits
const emit = defineEmits(['success', 'error', 'upload-start', 'upload-end'])

// Composables
const { post, loading } = useApi()

// State
const fileInput = ref(null)
const selectedFile = ref(null)
const previewUrl = ref(null)
const isUploading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Computed
const maxSizeBytes = computed(() => props.maxSizeMB * 1024 * 1024)

// Methods
const triggerFileInput = () => {
  if (isUploading.value) return
  fileInput.value?.click()
}

const onFileSelected = (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  // Reset messages
  errorMessage.value = ''
  successMessage.value = ''

  // Validate file type
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
  if (!validTypes.includes(file.type)) {
    errorMessage.value = 'Veuillez sélectionner un fichier image valide (JPG, PNG, GIF ou WEBP).'
    return
  }

  // Validate file size
  if (file.size > maxSizeBytes.value) {
    errorMessage.value = `La taille du fichier ne doit pas dépasser ${props.maxSizeMB}MB.`
    return
  }

  // Store file and create preview
  selectedFile.value = file
  
  const reader = new FileReader()
  reader.onload = (e) => {
    previewUrl.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const cancelPreview = () => {
  selectedFile.value = null
  previewUrl.value = null
  errorMessage.value = ''
  successMessage.value = ''
  
  // Reset file input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const uploadAvatar = async () => {
  if (!selectedFile.value) return

  try {
    isUploading.value = true
    errorMessage.value = ''
    successMessage.value = ''
    
    emit('upload-start')

    // Create FormData
    const formData = new FormData()
    formData.append(props.fieldName, selectedFile.value)
    
    console.log('Uploading avatar to:', props.uploadEndpoint)
    console.log('File details:', {
      name: selectedFile.value.name,
      size: selectedFile.value.size,
      type: selectedFile.value.type,
      fieldName: props.fieldName
    })
    
    // Upload - Ne pas définir Content-Type manuellement avec FormData
    const response = await post(props.uploadEndpoint, formData)

    console.log('Upload successful:', response)

    if (response && (response.success || response.data)) {
      const avatarUrl = response.data?.avatar_url || response.avatar_url
      successMessage.value = 'Photo de profil mise à jour avec succès !'
      
      // Clear preview after successful upload
      setTimeout(() => {
        selectedFile.value = null
        previewUrl.value = null
        successMessage.value = ''
      }, 2000)
      
      emit('success', { 
        avatar_url: avatarUrl, 
        response: response 
      })
    } else {
      throw new Error(response?.message || 'Upload failed')
    }

  } catch (error) {
    console.error('Error uploading avatar:', error)
    
    let message = 'Erreur lors de l\'upload de l\'image.'
    
    // Gestion spécifique des erreurs
    if (error.response) {
      console.error('Response error data:', error.response.data)
      console.error('Response status:', error.response.status)
      
      if (error.response.status === 422 && error.response.data?.errors) {
        // Erreurs de validation
        const errors = error.response.data.errors
        const firstError = Object.values(errors)[0]
        message = Array.isArray(firstError) ? firstError[0] : firstError
      } else if (error.response.status === 413) {
        message = 'Le fichier est trop volumineux. Veuillez réduire sa taille.'
      } else if (error.response.status === 401) {
        message = 'Session expirée. Veuillez vous reconnecter.'
      } else if (error.response.data?.message) {
        message = error.response.data.message
      }
    } else if (error.request) {
      console.error('Request error:', error.request)
      message = 'Erreur de connexion au serveur. Vérifiez votre connexion internet.'
    }
    
    if (error.response?.data?.message) {
      message = error.response.data.message
    } else if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      message = Object.values(errors).flat().join(', ')
    } else if (error.message) {
      message = error.message
    }
    
    errorMessage.value = message
    emit('error', error)
  } finally {
    isUploading.value = false
    emit('upload-end')
  }
}

// Gestion d'erreur pour les images
const onImageError = (event) => {
  console.log('Avatar image failed to load, using placeholder')
  handleImageError(event, 'Avatar')
}
</script>

<style scoped>
.avatar-upload-container {
  @apply flex flex-col items-center;
}

/* Animation pour le loader */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>