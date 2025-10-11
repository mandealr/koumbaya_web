<template>
  <div class="p-8">
    <h1 class="text-4xl font-bold text-red-600">TEST DASHBOARD CUSTOMER</h1>
    <p class="text-2xl mt-4">Si vous voyez ce texte, le routing fonctionne!</p>
    <div class="mt-8 p-4 bg-blue-100 rounded">
      <p>User: {{ user?.first_name }} {{ user?.last_name }}</p>
      <p>Email: {{ user?.email }}</p>
      <p>isCustomer: {{ isCustomer }}</p>
      <p>isMerchant: {{ isMerchant }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

console.log('✅ DashboardSimple: Component is loading!')

const authStore = useAuthStore()

const user = computed(() => authStore.user)
const isCustomer = computed(() => authStore.isCustomer)
const isMerchant = computed(() => authStore.isMerchant)

console.log('✅ DashboardSimple: User:', user.value)
console.log('✅ DashboardSimple: isCustomer:', isCustomer.value)
console.log('✅ DashboardSimple: isMerchant:', isMerchant.value)
</script>
