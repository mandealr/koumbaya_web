<template>
  <div class="max-w-4xl mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 mb-2">Notifications</h1>
      <div class="flex items-center justify-between">
        <p class="text-gray-600">
          {{ stats.total }} notification{{ stats.total > 1 ? 's' : '' }}
          <span v-if="stats.unread > 0" class="text-blue-600 font-medium">
            ({{ stats.unread }} non lue{{ stats.unread > 1 ? 's' : '' }})
          </span>
        </p>
        
        <!-- Actions -->
        <div class="flex items-center space-x-4">
          <button
            v-if="stats.unread > 0"
            @click="markAllAsRead"
            :disabled="loading"
            class="text-sm text-blue-600 hover:text-blue-800 font-medium disabled:opacity-50"
          >
            Tout marquer comme lu
          </button>
          
          <!-- Filtres -->
          <select
            v-model="selectedFilter"
            @change="loadNotifications"
            class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="all">Toutes</option>
            <option value="unread">Non lues</option>
            <option value="order_status_changed">Commandes</option>
            <option value="lottery_winner">Gains</option>
            <option value="ticket_purchase">Achats</option>
            <option value="reminder">Rappels</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading initial -->
    <div v-if="initialLoading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Liste des notifications -->
    <div v-else-if="notifications.length > 0" class="space-y-4">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        @click="handleNotificationClick(notification)"
        class="bg-white rounded-lg border shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer overflow-hidden"
        :class="{
          'border-l-4 border-l-blue-500 bg-blue-50': !notification.is_read,
          'opacity-75': notification.is_read
        }"
      >
        <div class="p-6">
          <div class="flex items-start space-x-4">
            <!-- Icône -->
            <div class="flex-shrink-0">
              <div
                class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-medium"
                :class="getNotificationIconClass(notification.type)"
              >
                {{ getNotificationIcon(notification.type) }}
              </div>
            </div>

            <!-- Contenu -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between mb-2">
                <h3 
                  class="text-lg font-medium text-gray-900"
                  :class="{ 'font-semibold': !notification.is_read }"
                  v-html="notification.title"
                ></h3>
                
                <!-- Actions -->
                <div class="flex items-center space-x-2">
                  <span class="text-sm text-gray-400">
                    {{ formatTimeAgo(notification.created_at) }}
                  </span>
                  
                  <!-- Bouton supprimer -->
                  <button
                    @click.stop="deleteNotification(notification.id)"
                    class="text-gray-400 hover:text-red-500 transition-colors duration-200"
                    title="Supprimer"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>

              <p 
                class="text-gray-600 text-sm mb-3"
                v-html="notification.message"
              ></p>

              <!-- Tags/Métadonnées -->
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <!-- Type -->
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getNotificationTypeClass(notification.type)"
                  >
                    {{ getNotificationTypeLabel(notification.type) }}
                  </span>
                  
                  <!-- Statut -->
                  <span
                    v-if="!notification.is_read"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    Nouveau
                  </span>
                </div>

                <!-- Indicateur non lu -->
                <div v-if="!notification.is_read" class="w-3 h-3 bg-blue-600 rounded-full"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bouton charger plus -->
      <div v-if="hasMoreNotifications" class="text-center py-6">
        <button
          @click="loadMoreNotifications"
          :disabled="loading"
          class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <span v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25"/>
              <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"/>
            </svg>
          </span>
          {{ loading ? 'Chargement...' : 'Charger plus' }}
        </button>
      </div>
    </div>

    <!-- Aucune notification -->
    <div v-else class="text-center py-12">
      <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ selectedFilter === 'unread' ? 'Aucune notification non lue' : 'Aucune notification' }}
      </h3>
      <p class="text-gray-500">
        {{ selectedFilter === 'unread' 
          ? 'Toutes vos notifications ont été lues.' 
          : 'Vous n\'avez encore reçu aucune notification.' 
        }}
      </p>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/composables/api'

export default {
  name: 'Notifications',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    // États réactifs
    const notifications = ref([])
    const stats = reactive({
      total: 0,
      unread: 0,
      recent: 0,
      by_type: {}
    })
    const selectedFilter = ref('all')
    const initialLoading = ref(true)
    const loading = ref(false)
    const currentPage = ref(1)
    const perPage = 20

    // Computed
    const hasMoreNotifications = computed(() => {
      return notifications.value.length >= currentPage.value * perPage
    })

    // Méthodes
    const loadNotifications = async (append = false) => {
      if (!authStore.isAuthenticated) return

      if (!append) {
        initialLoading.value = true
        currentPage.value = 1
      } else {
        loading.value = true
      }

      try {
        const params = {
          limit: perPage,
          offset: append ? notifications.value.length : 0
        }

        // Filtre par type
        if (selectedFilter.value !== 'all') {
          if (selectedFilter.value === 'unread') {
            params.unread_only = true
          } else {
            params.type = selectedFilter.value
          }
        }

        const response = await api.get('/notifications', { params })

        if (response.data.success) {
          const newNotifications = response.data.data.notifications

          if (append) {
            notifications.value.push(...newNotifications)
          } else {
            notifications.value = newNotifications
          }

          // Mettre à jour les stats
          Object.assign(stats, response.data.data.stats)
        }
      } catch (error) {
        console.error('Erreur lors du chargement des notifications:', error)
      } finally {
        initialLoading.value = false
        loading.value = false
      }
    }

    const loadMoreNotifications = () => {
      currentPage.value++
      loadNotifications(true)
    }

    const markAsRead = async (notificationId) => {
      try {
        await api.post(`/notifications/${notificationId}/read`)
        
        // Mettre à jour localement
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification && !notification.is_read) {
          notification.is_read = true
          stats.unread = Math.max(0, stats.unread - 1)
        }
      } catch (error) {
        console.error('Erreur lors du marquage de la notification:', error)
      }
    }

    const markAllAsRead = async () => {
      loading.value = true
      try {
        await api.post('/notifications/mark-all-read')
        
        // Mettre à jour localement
        notifications.value.forEach(notification => {
          notification.is_read = true
        })
        stats.unread = 0
      } catch (error) {
        console.error('Erreur lors du marquage de toutes les notifications:', error)
      } finally {
        loading.value = false
      }
    }

    const deleteNotification = async (notificationId) => {
      try {
        await api.delete(`/notifications/${notificationId}`)
        
        // Supprimer localement
        const index = notifications.value.findIndex(n => n.id === notificationId)
        if (index > -1) {
          const notification = notifications.value[index]
          notifications.value.splice(index, 1)
          
          // Mettre à jour les stats
          stats.total = Math.max(0, stats.total - 1)
          if (!notification.is_read) {
            stats.unread = Math.max(0, stats.unread - 1)
          }
        }
      } catch (error) {
        console.error('Erreur lors de la suppression de la notification:', error)
      }
    }

    const handleNotificationClick = async (notification) => {
      // Marquer comme lue si pas encore lu
      if (!notification.is_read) {
        await markAsRead(notification.id)
      }

      // Redirection selon le type de notification
      const redirectPath = getNotificationRedirectPath(notification)
      if (redirectPath) {
        router.push(redirectPath)
      }
    }

    const getNotificationRedirectPath = (notification) => {
      switch (notification.type) {
        case 'order_status_changed':
          return `/customer/orders/${notification.data?.order_number || ''}`
        case 'lottery_winner':
        case 'ticket_purchase':
          return '/customer/tickets'
        case 'lottery_draw_result':
          return `/lotteries/${notification.data?.lottery_id || ''}`
        case 'merchant_payment':
          return '/merchant/orders'
        case 'admin_alert':
          return '/admin/dashboard'
        default:
          return null
      }
    }

    // Méthodes UI
    const getNotificationIcon = (type) => {
      const icons = {
        order_status_changed: '📦',
        lottery_winner: '🎉',
        ticket_purchase: '🎫',
        lottery_draw_result: '📊',
        merchant_payment: '💰',
        admin_alert: '⚠️',
        welcome: '👋',
        reminder: '⏰',
        system: '🔔'
      }
      return icons[type] || '🔔'
    }

    const getNotificationIconClass = (type) => {
      const classes = {
        order_status_changed: 'bg-blue-500',
        lottery_winner: 'bg-green-500',
        ticket_purchase: 'bg-purple-500',
        lottery_draw_result: 'bg-orange-500',
        merchant_payment: 'bg-yellow-500',
        admin_alert: 'bg-red-500',
        welcome: 'bg-blue-500',
        reminder: 'bg-orange-500',
        system: 'bg-gray-500'
      }
      return classes[type] || 'bg-gray-500'
    }

    const getNotificationTypeLabel = (type) => {
      const labels = {
        order_status_changed: 'Commande',
        lottery_winner: 'Gain',
        ticket_purchase: 'Achat',
        lottery_draw_result: 'Tirage',
        merchant_payment: 'Paiement',
        admin_alert: 'Alerte',
        welcome: 'Bienvenue',
        reminder: 'Rappel',
        system: 'Système'
      }
      return labels[type] || 'Notification'
    }

    const getNotificationTypeClass = (type) => {
      const classes = {
        order_status_changed: 'bg-blue-100 text-blue-800',
        lottery_winner: 'bg-green-100 text-green-800',
        ticket_purchase: 'bg-purple-100 text-purple-800',
        lottery_draw_result: 'bg-orange-100 text-orange-800',
        merchant_payment: 'bg-yellow-100 text-yellow-800',
        admin_alert: 'bg-red-100 text-red-800',
        welcome: 'bg-blue-100 text-blue-800',
        reminder: 'bg-orange-100 text-orange-800',
        system: 'bg-gray-100 text-gray-800'
      }
      return classes[type] || 'bg-gray-100 text-gray-800'
    }

    const formatTimeAgo = (dateString) => {
      const now = new Date()
      const date = new Date(dateString)
      const diffInSeconds = Math.floor((now - date) / 1000)

      if (diffInSeconds < 60) return 'À l\'instant'
      if (diffInSeconds < 3600) return `Il y a ${Math.floor(diffInSeconds / 60)} min`
      if (diffInSeconds < 86400) return `Il y a ${Math.floor(diffInSeconds / 3600)}h`
      if (diffInSeconds < 604800) return `Il y a ${Math.floor(diffInSeconds / 86400)} jour${Math.floor(diffInSeconds / 86400) > 1 ? 's' : ''}`
      
      return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
      })
    }

    // Lifecycle
    onMounted(() => {
      loadNotifications()
    })

    return {
      // États
      notifications,
      stats,
      selectedFilter,
      initialLoading,
      loading,
      
      // Computed
      hasMoreNotifications,
      
      // Méthodes
      loadNotifications,
      loadMoreNotifications,
      markAllAsRead,
      deleteNotification,
      handleNotificationClick,
      
      // Méthodes UI
      getNotificationIcon,
      getNotificationIconClass,
      getNotificationTypeLabel,
      getNotificationTypeClass,
      formatTimeAgo
    }
  }
}
</script>