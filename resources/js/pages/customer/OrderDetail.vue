<template>
  <div class="order-detail">
    <!-- Loading State -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-4 text-lg">Chargement de la commande...</p>
        <p class="text-gray-400 text-sm mt-2">Veuillez patienter</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="min-h-screen flex items-center justify-center">
      <div class="text-center max-w-md mx-auto px-4">
        <div class="mb-6">
          <svg class="w-20 h-20 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
          <h2 class="text-3xl font-bold text-gray-900 mb-2">Commande introuvable</h2>
          <p class="text-gray-600 mb-6">{{ error }}</p>
        </div>
        
        <div class="space-y-3">
          <router-link 
            to="/customer/orders" 
            class="block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors"
          >
            <ArrowLeftIcon class="w-5 h-5 inline mr-2" />
            Retour aux commandes
          </router-link>
          <button 
            @click="retryLoadOrder" 
            class="block w-full bg-gray-200 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-300 transition-colors"
          >
            <ArrowPathIcon class="w-5 h-5 inline mr-2" />
            Réessayer
          </button>
        </div>
      </div>
    </div>

    <!-- Order Details -->
    <div v-else-if="order">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Commande #{{ order.order_number }}</h1>
            <div class="flex items-center space-x-4 mt-2">
              <p class="text-gray-600">{{ formatDate(order.created_at) }}</p>
              <span class="text-gray-400">•</span>
              <span class="text-sm text-gray-600">Type: {{ getTypeText(order.type) }}</span>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span :class="getStatusBadgeClass(order.status)">{{ getStatusText(order.status) }}</span>
            <button
              v-if="['paid', 'fulfilled'].includes(order.status)"
              @click="printInvoice"
              :disabled="printingInvoice"
              :class="[
                'px-4 py-2 rounded-md transition-colors flex items-center',
                printingInvoice 
                  ? 'bg-green-400 text-white cursor-not-allowed' 
                  : 'bg-green-600 text-white hover:bg-green-700'
              ]"
            >
              <div v-if="printingInvoice" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              <PrinterIcon v-else class="w-4 h-4 mr-2" />
              {{ printingInvoice ? 'Génération...' : 'Imprimer facture' }}
            </button>
            <button
              @click="$router.go(-1)"
              class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors"
            >
              <ArrowLeftIcon class="w-4 h-4 inline mr-2" />
              Retour
            </button>
          </div>
        </div>
      </div>

      <!-- Client Information -->
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations client</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-600">Nom complet</dt>
                <dd class="text-sm text-gray-900">{{ order.client?.full_name || 'Non renseigné' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-600">Email</dt>
                <dd class="text-sm text-gray-900">{{ order.client?.email || 'Non renseigné' }}</dd>
              </div>
            </dl>
          </div>
          <div>
            <dl class="space-y-3">
              <div>
                <dt class="text-sm font-medium text-gray-600">Téléphone</dt>
                <dd class="text-sm text-gray-900">{{ order.client?.phone || 'Non renseigné' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-600">Date d'inscription</dt>
                <dd class="text-sm text-gray-900">{{ order.client?.created_at ? formatDate(order.client.created_at) : 'Non disponible' }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Order Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Order Status Timeline -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Statut de la commande</h2>
            <div class="space-y-4">
              <div class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Commande créée</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                </div>
              </div>
              
              <div v-if="order.paid_at" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Paiement confirmé</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.paid_at) }}</p>
                </div>
              </div>
              
              <div v-if="order.fulfilled_at" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <CheckIcon class="w-5 h-5 text-green-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">Commande livrée</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.fulfilled_at) }}</p>
                </div>
              </div>

              <div v-if="['pending', 'awaiting_payment'].includes(order.status)" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                  <ClockIcon class="w-5 h-5 text-yellow-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">{{ order.status === 'pending' ? 'En attente' : 'En attente de paiement' }}</p>
                  <p class="text-sm text-gray-500">Créé le {{ formatDate(order.created_at) }}</p>
                </div>
              </div>

              <div v-if="['failed', 'cancelled'].includes(order.status)" class="flex items-center">
                <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                  <XMarkIcon class="w-5 h-5 text-red-600" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-900">{{ order.status === 'failed' ? 'Commande échouée' : 'Commande annulée' }}</p>
                  <p class="text-sm text-gray-500">{{ order.notes || 'Aucune raison spécifiée' }}</p>
                  <p v-if="order.cancelled_at" class="text-sm text-gray-500">{{ formatDate(order.cancelled_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Product/Lottery Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails de l'achat</h2>
            
            <!-- Product Info -->
            <div v-if="order.product" class="flex items-start space-x-4 mb-6">
              <div class="flex-shrink-0 w-16 h-16 bg-gray-200 rounded-lg overflow-hidden">
                <img 
                  v-if="order.product.image"
                  :src="order.product.image"
                  :alt="order.product.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <ShoppingBagIcon class="w-8 h-8 text-gray-400" />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-medium text-gray-900">{{ order.product.name }}</h3>
                <p class="text-sm text-gray-600 mt-1">{{ order.product.description }}</p>
                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                  <span>Mode: {{ getSaleModeText(order.product.sale_mode) }}</span>
                  <span>Type: {{ getTypeText(order.type) }}</span>
                </div>
              </div>
            </div>

            <!-- Lottery Info -->
            <div v-if="order.lottery" class="border rounded-lg p-4 bg-blue-50">
              <div class="flex items-center mb-3">
                <TicketIcon class="w-5 h-5 text-blue-600 mr-2" />
                <h3 class="text-lg font-medium text-gray-900">{{ order.lottery.title }}</h3>
              </div>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Numéro de tombola:</span>
                  <span class="font-medium ml-2">{{ order.lottery.lottery_number }}</span>
                </div>
                <div>
                  <span class="text-gray-600">Prix du billet:</span>
                  <span class="font-medium ml-2">{{ formatPrice(order.lottery.ticket_price) }}</span>
                </div>
                <div>
                  <span class="text-gray-600">Billets vendus:</span>
                  <span class="font-medium ml-2">{{ order.lottery.sold_tickets }}/{{ order.lottery.max_tickets }}</span>
                </div>
                <div v-if="order.lottery.draw_date">
                  <span class="text-gray-600">Date du tirage:</span>
                  <span class="font-medium ml-2">{{ formatDate(order.lottery.draw_date) }}</span>
                </div>
              </div>
              
              <!-- Winner Info -->
              <div v-if="order.lottery.winner_ticket_number" class="mt-3 p-3 bg-green-100 rounded-lg">
                <div class="flex items-center">
                  <TrophyIcon class="w-5 h-5 text-green-600 mr-2" />
                  <span class="text-sm font-medium text-green-800">
                    Billet gagnant: {{ order.lottery.winner_ticket_number }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Line Items -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails de la commande</h2>
            
            <!-- Product Line Item -->
            <div class="border-b border-gray-200 pb-4 mb-4">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <h3 class="font-medium text-gray-900">{{ order.product?.name || 'Produit non disponible' }}</h3>
                  <p class="text-sm text-gray-600 mt-1">{{ order.product?.description || '' }}</p>
                  <div class="flex items-center space-x-2 mt-2">
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ getTypeText(order.type) }}</span>
                    <span v-if="order.lottery" class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">{{ order.lottery.title }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900">{{ formatPrice(order.total_amount) }}</p>
                  <p class="text-sm text-gray-500">{{ order.currency }}</p>
                </div>
              </div>
            </div>
            
            <!-- Tickets Details -->
            <div v-if="order.tickets && order.tickets.length > 0">
              <h3 class="font-medium text-gray-900 mb-3">Billets de tombola ({{ order.tickets.length }})</h3>
              <div class="space-y-3 max-h-64 overflow-y-auto">
                <div 
                  v-for="ticket in order.tickets" 
                  :key="ticket.id"
                  :class="[
                    'border rounded-lg p-3 transition-colors',
                    ticket.is_winner 
                      ? 'bg-green-50 border-green-200 ring-1 ring-green-300' 
                      : 'bg-gray-50 border-gray-200'
                  ]"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <p class="font-medium text-gray-900 text-sm">{{ ticket.ticket_number }}</p>
                      <p class="text-xs text-gray-600">{{ formatPrice(ticket.price_paid) }}</p>
                      <p v-if="ticket.purchased_at" class="text-xs text-gray-500 mt-1">
                        Acheté le {{ formatDate(ticket.purchased_at) }}
                      </p>
                    </div>
                    <div class="text-right">
                      <span :class="getStatusBadgeClass(ticket.status)" class="text-xs">{{ getStatusText(ticket.status) }}</span>
                      <div v-if="ticket.is_winner" class="mt-1">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          <TrophyIcon class="w-3 h-3 mr-1" />
                          Gagnant!
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Order Total -->
            <div class="border-t border-gray-200 pt-4 mt-4">
              <div class="flex justify-between items-center">
                <span class="text-base font-medium text-gray-900">Total de la commande</span>
                <span class="text-xl font-bold text-gray-900">{{ formatPrice(order.total_amount) }}</span>
              </div>
            </div>
          </div>
          
          <!-- Payments Section -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Paiements liés</h2>
            
            <div v-if="order.payments && order.payments.length > 0" class="space-y-4">
              <div 
                v-for="payment in order.payments" 
                :key="payment.id"
                class="border border-gray-200 rounded-lg p-4"
              >
                <div class="flex justify-between items-start mb-3">
                  <div>
                    <p class="font-medium text-gray-900 text-sm">{{ payment.reference }}</p>
                    <p class="text-xs text-gray-500 font-mono">{{ payment.ebilling_id || 'ID non disponible' }}</p>
                  </div>
                  <span :class="getStatusBadgeClass(payment.status)" class="text-xs">{{ getStatusText(payment.status) }}</span>
                </div>
                
                <dl class="grid grid-cols-2 gap-3 text-xs">
                  <div>
                    <dt class="text-gray-600">Montant</dt>
                    <dd class="font-medium">{{ formatPrice(payment.amount) }}</dd>
                  </div>
                  <div>
                    <dt class="text-gray-600">Méthode</dt>
                    <dd class="font-medium">{{ getPaymentMethodText(payment.payment_method) }}</dd>
                  </div>
                  <div>
                    <dt class="text-gray-600">Créé le</dt>
                    <dd>{{ formatDate(payment.created_at) }}</dd>
                  </div>
                  <div v-if="payment.paid_at">
                    <dt class="text-gray-600">Payé le</dt>
                    <dd>{{ formatDate(payment.paid_at) }}</dd>
                  </div>
                  <div v-if="payment.external_transaction_id" class="col-span-2">
                    <dt class="text-gray-600">ID Transaction externe</dt>
                    <dd class="font-mono text-xs">{{ payment.external_transaction_id }}</dd>
                  </div>
                </dl>
              </div>
            </div>
            
            <div v-else class="text-center py-8">
              <CreditCardIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
              <p class="text-gray-500">Aucun paiement associé</p>
            </div>
          </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="space-y-6">
          <!-- Financial Summary -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Résumé financier</h2>
            <dl class="space-y-3">
              <div class="flex justify-between text-sm">
                <dt class="text-gray-600">Sous-total:</dt>
                <dd class="font-medium">{{ formatPrice(order.total_amount) }}</dd>
              </div>
              <div class="border-t pt-3">
                <div class="flex justify-between">
                  <dt class="text-base font-medium text-gray-900">Total:</dt>
                  <dd class="text-base font-medium text-gray-900">{{ formatPrice(order.total_amount) }}</dd>
                </div>
              </div>
            </dl>
          </div>

          <!-- Payment Info -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informations de paiement</h2>
            <dl class="space-y-3 text-sm">
              <div>
                <dt class="text-gray-600">Numéro de commande:</dt>
                <dd class="font-medium font-mono">{{ order.order_number }}</dd>
              </div>
              <div v-if="order.payments && order.payments.length > 0">
                <dt class="text-gray-600">Paiements:</dt>
                <dd>
                  <div v-for="payment in order.payments" :key="payment.id" class="mt-1">
                    <div class="text-xs text-gray-500">{{ payment.reference }}</div>
                    <div class="font-medium">{{ getPaymentMethodText(payment.payment_method) }}</div>
                    <span :class="getStatusBadgeClass(payment.status)" class="text-xs">{{ getStatusText(payment.status) }}</span>
                  </div>
                </dd>
              </div>
            </dl>
          </div>

          <!-- Actions -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions disponibles</h2>
            <div class="space-y-3">
              <!-- Retry Payment -->
              <button
                v-if="order.status === 'failed'"
                @click="retryOrder"
                class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors flex items-center justify-center"
              >
                <ArrowPathIcon class="w-4 h-4 mr-2" />
                Réessayer le paiement
              </button>
              
              <!-- Download Invoice -->
              <button
                v-if="['paid', 'fulfilled'].includes(order.status)"
                @click="downloadInvoice"
                :disabled="downloadingInvoice"
                :class="[
                  'w-full px-4 py-2 rounded-md transition-colors flex items-center justify-center whitespace-nowrap',
                  downloadingInvoice 
                    ? 'bg-green-400 text-white cursor-not-allowed' 
                    : 'bg-green-600 text-white hover:bg-green-700'
                ]"
              >
                <div v-if="downloadingInvoice" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 flex-shrink-0"></div>
                <DocumentArrowDownIcon v-else class="w-4 h-4 mr-2 flex-shrink-0" />
                {{ downloadingInvoice ? 'Téléchargement...' : 'Télécharger la facture' }}
              </button>
              
              <!-- Print Invoice -->
              <button
                v-if="['paid', 'fulfilled'].includes(order.status)"
                @click="printInvoice"
                :disabled="printingInvoice"
                :class="[
                  'w-full px-4 py-2 rounded-md transition-colors flex items-center justify-center',
                  printingInvoice 
                    ? 'bg-blue-400 text-white cursor-not-allowed' 
                    : 'bg-blue-600 text-white hover:bg-blue-700'
                ]"
              >
                <div v-if="printingInvoice" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                <PrinterIcon v-else class="w-4 h-4 mr-2" />
                {{ printingInvoice ? 'Génération...' : 'Imprimer la facture' }}
              </button>
              
              <!-- Unavailable Actions -->
              <div v-if="!['paid', 'fulfilled'].includes(order.status)" class="text-center p-3 bg-gray-50 rounded-md">
                <p class="text-sm text-gray-500 mb-2">Actions disponibles après paiement:</p>
                <div class="flex items-center justify-center space-x-2 text-xs text-gray-400">
                  <DocumentArrowDownIcon class="w-4 h-4" />
                  <span>Téléchargement facture</span>
                  <span>•</span>
                  <PrinterIcon class="w-4 h-4" />
                  <span>Impression facture</span>
                </div>
              </div>
              
              <!-- Back to Orders -->
              <router-link
                to="/customer/orders"
                class="w-full block text-center bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors"
              >
                <ArrowLeftIcon class="w-4 h-4 inline mr-2" />
                Retour aux commandes
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/composables/api'
import { useToast } from '@/composables/useToast'
import {
  ArrowLeftIcon,
  CheckIcon,
  ClockIcon,
  XMarkIcon,
  ShoppingBagIcon,
  TicketIcon,
  TrophyIcon,
  ArrowPathIcon,
  DocumentArrowDownIcon,
  PrinterIcon,
  CreditCardIcon
} from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()
const { get: apiGet } = useApi()
const { showError, showSuccess } = useToast()

// État
const order = ref(null)
const loading = ref(true)
const error = ref(null)
const downloadingInvoice = ref(false)
const printingInvoice = ref(false)

// Charger les détails de la commande
const loadOrder = async () => {
  try {
    loading.value = true
    error.value = null
    const orderNumber = route.params.id // Now expects order_number instead of transaction_id
    
    if (!orderNumber) {
      error.value = 'Numéro de commande manquant'
      return
    }
    
    const response = await apiGet(`/orders/${orderNumber}`)
    if (response.success) {
      order.value = response.data
    } else {
      error.value = response.message || 'Commande non trouvée'
    }
  } catch (err) {
    console.error('Erreur lors du chargement de la commande:', err)
    if (err.response?.status === 404) {
      error.value = 'Commande introuvable. Vérifiez le numéro de commande.'
    } else if (err.response?.status === 403) {
      error.value = 'Vous n\'êtes pas autorisé à voir cette commande.'
    } else {
      error.value = 'Erreur lors du chargement de la commande. Veuillez réessayer.'
    }
  } finally {
    loading.value = false
  }
}

// Réessayer le chargement
const retryLoadOrder = () => {
  loadOrder()
}

// Réessayer une commande échouée
const retryOrder = () => {
  router.push({
    name: 'payment.phone',
    query: {
      order_number: order.value.order_number,
      retry: 'true'
    }
  })
}

// Télécharger la facture PDF
const downloadInvoice = async () => {
  if (!['paid', 'fulfilled'].includes(order.value.status)) {
    showError('La facture n\'est disponible que pour les commandes payées')
    return
  }
  
  try {
    downloadingInvoice.value = true
    const response = await fetch(`/api/orders/${order.value.order_number}/invoice`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/pdf'
      }
    })
    
    if (!response.ok) {
      throw new Error('Erreur lors du téléchargement')
    }
    
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `facture-${order.value.order_number}.pdf`
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
    
    showSuccess('Facture téléchargée avec succès')
  } catch (error) {
    showError('Erreur lors du téléchargement de la facture')
    console.error('Erreur:', error)
  } finally {
    downloadingInvoice.value = false
  }
}

// Imprimer la facture PDF
const printInvoice = async () => {
  if (!['paid', 'fulfilled'].includes(order.value.status)) {
    showError('L\'impression n\'est disponible que pour les commandes payées')
    return
  }
  
  try {
    printingInvoice.value = true
    const response = await fetch(`/api/orders/${order.value.order_number}/invoice`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/pdf'
      }
    })
    
    if (!response.ok) {
      throw new Error('Erreur lors de la génération de la facture')
    }
    
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    
    // Ouvrir dans une nouvelle fenêtre pour impression
    const printWindow = window.open(url, '_blank')
    if (printWindow) {
      printWindow.addEventListener('load', () => {
        printWindow.print()
      })
      showSuccess('Facture ouverte pour impression')
    } else {
      // Fallback: télécharger si le popup est bloqué
      const a = document.createElement('a')
      a.href = url
      a.download = `facture-${order.value.order_number}.pdf`
      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
      showError('Popup bloqué. La facture a été téléchargée à la place.')
    }
    
    window.URL.revokeObjectURL(url)
  } catch (error) {
    showError('Erreur lors de l\'impression de la facture')
    console.error('Erreur:', error)
  } finally {
    printingInvoice.value = false
  }
}

// Fonctions utilitaires
const getStatusText = (status) => {
  const statusMap = {
    'pending': 'En attente',
    'awaiting_payment': 'En attente de paiement',
    'paid': 'Payé',
    'failed': 'Échoué',
    'cancelled': 'Annulé',
    'fulfilled': 'Livré',
    'refunded': 'Remboursé',
    'expired': 'Expiré',
    'completed': 'Complété', // Legacy support
    'payment_initiated': 'Paiement initié', // Legacy support
    'active': 'Actif' // For tickets
  }
  return statusMap[status] || status
}

const getTypeText = (type) => {
  const typeMap = {
    'lottery': 'Tombola',
    'direct': 'Achat direct',
    'ticket_purchase': 'Achat de billet', // Legacy support
    'direct_purchase': 'Achat direct' // Legacy support
  }
  return typeMap[type] || type
}

const getSaleModeText = (mode) => {
  const modeMap = {
    'lottery': 'Tombola',
    'direct': 'Achat direct'
  }
  return modeMap[mode] || mode
}

const getPaymentMethodText = (method) => {
  const methodMap = {
    'airtel_money': 'Airtel Money',
    'moov_money': 'Moov Money'
  }
  return methodMap[method] || method || 'Non spécifié'
}

const getStatusBadgeClass = (status) => {
  const baseClass = 'px-2 py-1 text-xs rounded-full font-medium'
  const statusClasses = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'awaiting_payment': 'bg-blue-100 text-blue-800',
    'paid': 'bg-green-100 text-green-800',
    'failed': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800',
    'fulfilled': 'bg-green-100 text-green-800',
    'refunded': 'bg-purple-100 text-purple-800',
    'expired': 'bg-orange-100 text-orange-800',
    'completed': 'bg-green-100 text-green-800', // Legacy
    'payment_initiated': 'bg-blue-100 text-blue-800', // Legacy
    'active': 'bg-blue-100 text-blue-800' // For tickets
  }
  return `${baseClass} ${statusClasses[status] || 'bg-gray-100 text-gray-800'}`
}

const formatDate = (dateString) => {
  if (!dateString) return 'Non défini'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF',
    minimumFractionDigits: 0
  }).format(price || 0).replace('XAF', 'FCFA')
}

// Lifecycle
onMounted(() => {
  loadOrder()
})
</script>

<style scoped>
.order-detail {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>