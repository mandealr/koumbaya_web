<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <TransitionGroup
        name="toast"
        tag="div"
        class="space-y-2"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :class="toastClasses(toast.type)"
          class="flex items-start p-4 rounded-xl shadow-lg border backdrop-blur-sm max-w-sm transition-all duration-300"
        >
          <!-- Icône -->
          <div class="flex-shrink-0">
            <component 
              :is="getIcon(toast.type)"
              :class="iconClasses(toast.type)"
              class="w-5 h-5"
            />
          </div>

          <!-- Contenu -->
          <div class="ml-3 flex-1">
            <h4 v-if="toast.title" class="text-sm font-semibold mb-1">
              {{ toast.title }}
            </h4>
            <p class="text-sm">
              {{ toast.message }}
            </p>
          </div>

          <!-- Bouton fermer -->
          <button
            @click="removeToast(toast.id)"
            class="flex-shrink-0 ml-2 p-1 rounded-lg hover:bg-black/5 transition-colors"
          >
            <XMarkIcon class="w-4 h-4 text-gray-400 hover:text-gray-600" />
          </button>

          <!-- Barre de progression -->
          <div 
            v-if="toast.duration"
            :class="progressBarClasses(toast.type)"
            class="absolute bottom-0 left-0 h-1 rounded-b-xl transition-all duration-linear"
            :style="{ 
              width: '100%',
              animationDuration: toast.duration + 'ms',
              animationName: 'toast-progress'
            }"
          ></div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import {
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const toasts = ref([])
let toastId = 0

const iconMap = {
  success: CheckCircleIcon,
  error: XCircleIcon,
  warning: ExclamationTriangleIcon,
  info: InformationCircleIcon
}

const getIcon = (type) => iconMap[type] || InformationCircleIcon

const toastClasses = (type) => {
  const baseClasses = 'bg-white/90 border-l-4'
  const typeClasses = {
    success: 'border-green-500 text-green-900',
    error: 'border-red-500 text-red-900', 
    warning: 'border-orange-500 text-orange-900',
    info: 'border-blue-500 text-blue-900'
  }
  return `${baseClasses} ${typeClasses[type] || typeClasses.info}`
}

const iconClasses = (type) => {
  const classes = {
    success: 'text-green-600',
    error: 'text-red-600',
    warning: 'text-orange-600', 
    info: 'text-blue-600'
  }
  return classes[type] || classes.info
}

const progressBarClasses = (type) => {
  const classes = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-orange-500',
    info: 'bg-blue-500'
  }
  return classes[type] || classes.info
}

const addToast = ({ title, message, type = 'info', duration = 5000 }) => {
  const id = ++toastId
  const toast = {
    id,
    title,
    message,
    type,
    duration
  }
  
  toasts.value.push(toast)
  
  if (duration > 0) {
    setTimeout(() => {
      removeToast(id)
    }, duration)
  }
  
  return id
}

const removeToast = (id) => {
  const index = toasts.value.findIndex(toast => toast.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
}

const clearAll = () => {
  toasts.value = []
}

// API globale
const showSuccess = (message, title = 'Succès') => {
  return addToast({ title, message, type: 'success' })
}

const showError = (message, title = 'Erreur') => {
  return addToast({ title, message, type: 'error' })
}

const showWarning = (message, title = 'Attention') => {
  return addToast({ title, message, type: 'warning' })
}

const showInfo = (message, title = 'Information') => {
  return addToast({ title, message, type: 'info' })
}

// Exposer les méthodes pour utilisation externe
defineExpose({
  addToast,
  removeToast,
  clearAll,
  showSuccess,
  showError,
  showWarning,
  showInfo
})

// API globale sur window pour utilisation partout
onMounted(() => {
  window.$toast = {
    success: showSuccess,
    error: showError,
    warning: showWarning,
    info: showInfo,
    add: addToast,
    remove: removeToast,
    clear: clearAll
  }
})

onUnmounted(() => {
  delete window.$toast
})
</script>

<style scoped>
/* Animations pour les toasts */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.toast-move {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Animation de la barre de progression */
@keyframes toast-progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}
</style>