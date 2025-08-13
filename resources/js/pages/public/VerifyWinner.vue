<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-600 text-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
          <h1 class="text-4xl font-bold mb-4">🔍 Vérification de Gain</h1>
          <p class="text-xl opacity-90 mb-8">
            Vérifiez l'authenticité d'un résultat de tombola
          </p>
        </div>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Main Verification Tool -->
      <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
          <span class="text-blue-600">🏆</span> Outil de Vérification Officiel
        </h2>
        
        <div class="max-w-md mx-auto">
          <div class="mb-6">
            <label for="verification-code" class="block text-sm font-medium text-gray-700 mb-2">
              Code de Vérification
            </label>
            <input
              id="verification-code"
              v-model="verificationCode"
              type="text"
              placeholder="Ex: A1B2C3D4"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center text-lg font-mono uppercase"
              maxlength="8"
              @input="verificationCode = $event.target.value.toUpperCase()"
              @keyup.enter="verifyCode"
            />
            <p class="mt-2 text-sm text-gray-600">
              Entrez le code de 8 caractères fourni avec votre notification de gain
            </p>
          </div>

          <button
            @click="verifyCode"
            :disabled="!verificationCode || verifying || verificationCode.length !== 8"
            class="w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200"
          >
            <span v-if="verifying" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Vérification en cours...
            </span>
            <span v-else>
              🔍 Vérifier le Gain
            </span>
          </button>
        </div>
      </div>

      <!-- Verification Result -->
      <div v-if="verificationResult" class="mb-8">
        <!-- Valid Result -->
        <div v-if="verificationResult.valid" class="bg-white rounded-lg shadow-lg border-2 border-blue-200 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-emerald-500 text-white p-6">
            <div class="flex items-center justify-center">
              <CheckCircleIcon class="w-12 h-12 mr-4" />
              <div>
                <h3 class="text-2xl font-bold">✅ GAIN VÉRIFIÉ</h3>
                <p class="text-blue-100">Ce résultat est authentique et officiel</p>
              </div>
            </div>
          </div>
          
          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Lottery Info -->
              <div class="space-y-4">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">📋 Informations de la Tombola</h4>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Numéro :</span>
                    <span class="font-medium">{{ verificationResult.lottery_number }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Produit :</span>
                    <span class="font-medium">{{ verificationResult.product_title }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Date du tirage :</span>
                    <span class="font-medium">{{ formatDate(verificationResult.draw_date) }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Winner Info -->
              <div class="space-y-4">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">🏆 Gagnant</h4>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Identité :</span>
                    <span class="font-medium">{{ verificationResult.winner_initial }}****</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Code :</span>
                    <span class="font-mono font-medium text-blue-600">{{ verificationCode }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Vérifié le :</span>
                    <span class="font-medium">{{ formatDate(verificationResult.verification_date) }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
              <div class="flex items-start">
                <InformationCircleIcon class="w-5 h-5 text-blue-500 mt-0.5 mr-3" />
                <div class="text-sm text-blue-800">
                  <p class="font-medium mb-1">Certificat de Vérification</p>
                  <p>Ce résultat a été vérifié dans notre système officiel. La tombola {{ verificationResult.lottery_number }} s'est terminée le {{ formatDate(verificationResult.draw_date) }} avec un tirage transparent et équitable.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Invalid Result -->
        <div v-else class="bg-white rounded-lg shadow-lg border-2 border-red-200">
          <div class="bg-gradient-to-r from-red-500 to-red-600 text-white p-6">
            <div class="flex items-center justify-center">
              <XCircleIcon class="w-12 h-12 mr-4" />
              <div>
                <h3 class="text-2xl font-bold">❌ CODE INVALIDE</h3>
                <p class="text-red-100">Ce code de vérification n'existe pas</p>
              </div>
            </div>
          </div>
          
          <div class="p-8">
            <div class="text-center">
              <p class="text-gray-700 mb-4">
                Le code <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ verificationCode }}</span> ne correspond à aucun résultat officiel dans notre système.
              </p>
              
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                  <ExclamationTriangleIcon class="w-5 h-5 text-yellow-500 mt-0.5 mr-3" />
                  <div class="text-sm text-yellow-800">
                    <p class="font-medium mb-1">Vérifications possibles :</p>
                    <ul class="list-disc list-inside space-y-1">
                      <li>Assurez-vous que le code est correct (8 caractères)</li>
                      <li>Vérifiez que le code provient d'une source officielle</li>
                      <li>Contactez notre support si vous pensez qu'il y a une erreur</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Information Panel -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">ℹ️ Comment ça marche ?</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
              <span class="text-xl">1️⃣</span>
            </div>
            <h4 class="font-medium text-gray-900 mb-2">Recevez votre code</h4>
            <p class="text-sm text-gray-600">
              Après un tirage, le gagnant reçoit un code de vérification unique par email et SMS
            </p>
          </div>
          
          <div class="text-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
              <span class="text-xl">2️⃣</span>
            </div>
            <h4 class="font-medium text-gray-900 mb-2">Vérifiez en ligne</h4>
            <p class="text-sm text-gray-600">
              Entrez le code sur cette page pour vérifier l'authenticité du résultat
            </p>
          </div>
          
          <div class="text-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
              <span class="text-xl">3️⃣</span>
            </div>
            <h4 class="font-medium text-gray-900 mb-2">Confirmez le gain</h4>
            <p class="text-sm text-gray-600">
              Un résultat valide confirme officiellement le gain et ses détails
            </p>
          </div>
        </div>
      </div>

      <!-- Recent Verifications -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Vérifications Récentes</h3>
        
        <div v-if="recentVerifications.length === 0" class="text-center text-gray-500 py-8">
          <MagnifyingGlassIcon class="w-12 h-12 mx-auto text-gray-300 mb-3" />
          <p>Aucune vérification récente</p>
        </div>
        
        <div v-else class="space-y-3">
          <div
            v-for="verification in recentVerifications"
            :key="verification.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div>
              <span class="font-mono text-sm text-gray-600">{{ verification.code }}</span>
              <span class="mx-2">→</span>
              <span class="text-sm" :class="verification.valid ? 'text-blue-600' : 'text-red-600'">
                {{ verification.valid ? '✅ Valide' : '❌ Invalide' }}
              </span>
            </div>
            <span class="text-xs text-gray-500">
              {{ formatTimeAgo(verification.verified_at) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useApi } from '@/composables/api'
import {
  CheckCircleIcon,
  XCircleIcon,
  InformationCircleIcon,
  ExclamationTriangleIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

const { get } = useApi()

// Data
const verificationCode = ref('')
const verifying = ref(false)
const verificationResult = ref(null)
const recentVerifications = ref([])

// Methods
const verifyCode = async () => {
  if (!verificationCode.value.trim() || verificationCode.value.length !== 8) return
  
  verifying.value = true
  verificationResult.value = null
  
  try {
    const response = await get(`/public/results/verify/${verificationCode.value.trim()}`)
    verificationResult.value = response.data
    verificationResult.value.valid = true
    
    // Add to recent verifications
    addToRecentVerifications(verificationCode.value, true)
  } catch (error) {
    verificationResult.value = { 
      valid: false,
      code: verificationCode.value.trim()
    }
    
    // Add to recent verifications
    addToRecentVerifications(verificationCode.value, false)
  } finally {
    verifying.value = false
  }
}

const addToRecentVerifications = (code, valid) => {
  const verification = {
    id: Date.now(),
    code: code,
    valid: valid,
    verified_at: new Date()
  }
  
  recentVerifications.value.unshift(verification)
  
  // Keep only last 5 verifications
  if (recentVerifications.value.length > 5) {
    recentVerifications.value = recentVerifications.value.slice(0, 5)
  }
  
  // Store in localStorage
  localStorage.setItem('recent_verifications', JSON.stringify(recentVerifications.value))
}

const loadRecentVerifications = () => {
  try {
    const stored = localStorage.getItem('recent_verifications')
    if (stored) {
      recentVerifications.value = JSON.parse(stored)
        .map(v => ({
          ...v,
          verified_at: new Date(v.verified_at)
        }))
        .slice(0, 5) // Keep only 5 most recent
    }
  } catch (error) {
    console.error('Failed to load recent verifications', error)
  }
}

// Utility functions
const formatDate = (dateString) => {
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(dateString))
}

const formatTimeAgo = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  
  if (minutes < 1) return 'À l\'instant'
  if (minutes < 60) return `${minutes}min`
  
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h`
  
  const days = Math.floor(hours / 24)
  return `${days}j`
}

// Lifecycle
onMounted(() => {
  loadRecentVerifications()
})
</script>