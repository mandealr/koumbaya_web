<template>
  <div class="relative">
    <!-- Icône de notification avec badge -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900 transition-colors duration-200"
      :class="{ 'text-blue-600': hasUnread }"
    >
      <!-- Icône Bell -->
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        />
      </svg>
      
      <!-- Badge nombre non lues -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
      
      <!-- Point rouge pour nouvelles notifications -->
      <span
        v-else-if="hasUnread"
        class="absolute top-1 right-1 block w-2 h-2 bg-red-600 rounded-full"
      ></span>
    </button>

    <!-- Dropdown des notifications -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showDropdown"
        class="absolute right-0 z-50 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
        @click.stop
      >
        <!-- En-tête du dropdown -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-sm font-medium text-gray-900">
            Notifications
            <span v-if="unreadCount > 0" class="text-blue-600">({{ unreadCount }})</span>
          </h3>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-xs text-blue-600 hover:text-blue-800 font-medium"
            :disabled="loading"
          >
            Tout marquer lu
          </button>
        </div>

        <!-- Liste des notifications -->
        <div class="max-h-96 overflow-y-auto">
          <!-- Loading -->
          <div v-if="loading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
          </div>

          <!-- Notifications -->
          <div v-else-if="notifications.length > 0" class="divide-y divide-gray-100">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors duration-150"
              :class="{
                'bg-blue-50 border-l-4 border-l-blue-500': !notification.is_read,
                'opacity-75': notification.is_read
              }"
            >
              <div class="flex items-start space-x-3">
                <!-- Icône selon le type -->
                <div class="flex-shrink-0 mt-1">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-medium"
                    :class="getNotificationIconClass(notification.type)"
                  >
                    {{ getNotificationIcon(notification.type) }}
                  </div>
                </div>

                <!-- Contenu -->
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900" v-html="notification.title"></p>
                  <p class="text-xs text-gray-600 mt-1 line-clamp-2" v-html="notification.message"></p>
                  <p class="text-xs text-gray-400 mt-2">{{ formatTimeAgo(notification.created_at) }}</p>
                </div>

                <!-- Indicateur non lu -->
                <div v-if="!notification.is_read" class="flex-shrink-0">
                  <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Aucune notification -->
          <div v-else class="px-4 py-8 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-sm">Aucune notification</p>
          </div>
        </div>

        <!-- Pied du dropdown -->
        <div v-if="notifications.length > 0" class="px-4 py-3 border-t border-gray-100">
          <router-link
            to="/notifications"
            @click="closeDropdown"
            class="text-sm text-blue-600 hover:text-blue-800 font-medium block text-center"
          >
            Voir toutes les notifications
          </router-link>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/composables/api'

export default {
  name: 'NotificationIcon',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()

    // États réactifs
    const showDropdown = ref(false)
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)

    // Computed
    const hasUnread = computed(() => unreadCount.value > 0)

    // Méthodes
    const toggleDropdown = () => {
      showDropdown.value = !showDropdown.value
      if (showDropdown.value) {
        loadNotifications()
      }
    }

    const closeDropdown = () => {
      showDropdown.value = false
    }

    const loadNotifications = async () => {
      if (!authStore.isAuthenticated) return

      loading.value = true
      try {
        const response = await api.get('/notifications', {
          params: { limit: 10 }
        })

        if (response.data.success) {
          notifications.value = response.data.data.notifications
          unreadCount.value = response.data.data.stats.unread
        }
      } catch (error) {
        console.error('Erreur lors du chargement des notifications:', error)
      } finally {
        loading.value = false
      }
    }

    const loadUnreadCount = async () => {
      if (!authStore.isAuthenticated) return

      try {
        const response = await api.get('/notifications/unread-count')
        if (response.data.success) {
          unreadCount.value = response.data.data.unread_count
        }
      } catch (error) {
        console.error('Erreur lors du chargement du nombre de notifications:', error)
      }
    }

    const markAsRead = async (notificationId) => {
      try {
        await api.post(`/notifications/${notificationId}/read`)
        
        // Mettre à jour localement
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification && !notification.is_read) {
          notification.is_read = true
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
      } catch (error) {
        console.error('Erreur lors du marquage de la notification:', error)
      }
    }

    const markAllAsRead = async () => {
      try {
        await api.post('/notifications/mark-all-read')
        
        // Mettre à jour localement
        notifications.value.forEach(notification => {
          notification.is_read = true
        })
        unreadCount.value = 0
      } catch (error) {
        console.error('Erreur lors du marquage de toutes les notifications:', error)
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

      closeDropdown()
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
          return '/notifications'
      }
    }

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

    const formatTimeAgo = (dateString) => {
      const now = new Date()
      const date = new Date(dateString)
      const diffInSeconds = Math.floor((now - date) / 1000)

      if (diffInSeconds < 60) return 'À l\'instant'
      if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}min`
      if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h`
      if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}j`
      
      return date.toLocaleDateString('fr-FR', { 
        day: 'numeric', 
        month: 'short',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
      })
    }

    // Fermer dropdown si clic à l'extérieur
    const handleClickOutside = (event) => {
      if (showDropdown.value && !event.target.closest('.relative')) {
        closeDropdown()
      }
    }

    // Lifecycle
    onMounted(() => {
      loadUnreadCount()
      document.addEventListener('click', handleClickOutside)
      
      // Rafraîchir le nombre de notifications toutes les 30 secondes
      const interval = setInterval(loadUnreadCount, 30000)
      onUnmounted(() => {
        clearInterval(interval)
        document.removeEventListener('click', handleClickOutside)
      })
    })

    return {
      // États
      showDropdown,
      notifications,
      unreadCount,
      loading,
      
      // Computed
      hasUnread,
      
      // Méthodes
      toggleDropdown,
      closeDropdown,
      markAllAsRead,
      handleNotificationClick,
      getNotificationIcon,
      getNotificationIconClass,
      formatTimeAgo
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>