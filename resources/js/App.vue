<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

onMounted(() => {
  // Vérifier si l'utilisateur est connecté au démarrage
  authStore.checkAuth()
})
</script>

<style>
/* Global styles */
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}
</style>