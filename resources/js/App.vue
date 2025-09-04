<template>
  <div id="app">
    <router-view />
    <!-- Composant Toast global -->
    <Toast ref="toastRef" />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Toast from '@/components/common/Toast.vue'

const authStore = useAuthStore()

onMounted(async () => {
  // Vérifier si l'utilisateur est connecté au démarrage
  await authStore.checkAuth()
})
</script>

<style>
/* Global styles */
body {
  font-family: var(--koumbaya-font-family);
  background-color: var(--koumbaya-gray-50);
  color: var(--koumbaya-gray-800);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Classes Tailwind personnalisées pour Koumbaya */
.text-koumbaya-primary {
  color: var(--koumbaya-primary);
}

.text-koumbaya-primary-dark {
  color: var(--koumbaya-primary-dark);
}

.bg-koumbaya-primary {
  background-color: var(--koumbaya-primary);
}

.bg-koumbaya-primary-dark {
  background-color: var(--koumbaya-primary-dark);
}

.border-koumbaya-primary {
  border-color: var(--koumbaya-primary);
}

.focus\:border-koumbaya-primary:focus {
  border-color: var(--koumbaya-primary);
}

.focus\:ring-koumbaya-primary:focus {
  --tw-ring-color: var(--koumbaya-primary);
}

.hover\:bg-koumbaya-primary:hover {
  background-color: var(--koumbaya-primary);
}

.hover\:text-koumbaya-primary:hover {
  color: var(--koumbaya-primary);
}

.hover\:text-koumbaya-primary-dark:hover {
  color: var(--koumbaya-primary-dark);
}

/* Scrollbar personnalisée */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--koumbaya-gray-100);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: var(--koumbaya-gray-300);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--koumbaya-primary);
}
</style>