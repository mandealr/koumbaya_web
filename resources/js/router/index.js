import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Layouts
import GuestLayout from '@/components/common/GuestLayout.vue'
import CustomerLayout from '@/components/common/CustomerLayout.vue'
import AdminLayout from '@/components/common/AdminLayout.vue'

// Guest Pages
import Login from '@/pages/auth/Login.vue'
import Register from '@/pages/auth/Register.vue'
import VerifyEmail from '@/pages/auth/VerifyEmail.vue'
import ForgotPassword from '@/pages/auth/ForgotPassword.vue'
import ResetPasswordVerify from '@/pages/auth/ResetPasswordVerify.vue'
import Home from '@/pages/guest/Home.vue'
import Results from '@/pages/public/Results.vue'
import VerifyWinner from '@/pages/public/VerifyWinner.vue'
import History from '@/pages/public/History.vue'

// Public Pages (accessible without authentication)
import PublicProducts from '@/pages/public/Products.vue'
import PublicProductDetail from '@/pages/public/ProductDetail.vue'
import HowItWorks from '@/pages/public/HowItWorks.vue'
import Contact from '@/pages/public/Contact.vue'

// Customer Pages
import CustomerDashboard from '@/pages/customer/Dashboard.vue'
import Products from '@/pages/customer/Products.vue'
import ProductDetail from '@/pages/customer/ProductDetail.vue'
import Profile from '@/pages/customer/Profile.vue'
import MyTickets from '@/pages/customer/MyTickets.vue'
import Refunds from '@/pages/customer/Refunds.vue'
import OrderTracking from '@/pages/customer/OrderTracking.vue'
import Payments from '@/pages/customer/Payments.vue'
import OrderDetail from '@/pages/customer/OrderDetail.vue'
import PaymentDetail from '@/pages/customer/PaymentDetail.vue'

// Notifications
import Notifications from '@/pages/common/Notifications.vue'

// Payment Pages
import PaymentMethod from '@/pages/payment/PaymentMethod.vue'
import PaymentPhone from '@/pages/payment/PhoneInput.vue'
import PaymentConfirmation from '@/pages/payment/PaymentConfirmation.vue'

// Admin Pages
import AdminDashboard from '@/pages/admin/Dashboard.vue'
import UserManagement from '@/pages/admin/UserManagement.vue'
import ProductManagement from '@/pages/admin/ProductManagement.vue'
import LotteryManagement from '@/pages/admin/LotteryManagement.vue'
import AdminSettings from '@/pages/admin/Settings.vue'
import AdminProfile from '@/pages/admin/Profile.vue'
import RefundManagement from '@/pages/admin/RefundManagement.vue'
import OrderManagement from '@/pages/admin/OrderManagement.vue'
import PaymentManagement from '@/pages/admin/PaymentManagement.vue'

const routes = [
  // Standalone Register Route (no layout)
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { requiresGuest: true }
  },
  
  // Email Verification Route (no layout)
  {
    path: '/verify-email',
    name: 'verify-email',
    component: VerifyEmail
  },
  

  // Password Reset Routes (no layout)
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { requiresGuest: true }
  },
  {
    path: '/reset-password-verify',
    name: 'reset-password-verify',
    component: ResetPasswordVerify,
    meta: { requiresGuest: true }
  },

  // Guest Routes
  {
    path: '/',
    component: GuestLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: Home
      },
      {
        path: 'results',
        name: 'public.results',
        component: Results
      },
      {
        path: 'verify',
        name: 'public.verify',
        component: VerifyWinner
      },
      {
        path: 'history',
        name: 'public.history',
        component: History
      },
      {
        path: 'login',
        name: 'login',
        component: Login,
        meta: { requiresGuest: true }
      },
      {
        path: 'products',
        name: 'public.products',
        component: PublicProducts
      },
      {
        path: 'products/:id',
        name: 'public.product.detail',
        component: PublicProductDetail,
        props: true
      },
      {
        path: 'how-it-works',
        name: 'public.how-it-works',
        component: HowItWorks
      },
      {
        path: 'contact',
        name: 'public.contact',
        component: Contact
      },
      // Pages statiques du footer
      {
        path: 'about',
        name: 'public.about',
        component: () => import('@/pages/public/static/AboutPage.vue')
      },
      {
        path: 'affiliates',
        name: 'public.affiliates',
        component: () => import('@/pages/public/static/AffiliatesPage.vue')
      },
      {
        path: 'earn-gombos',
        name: 'public.earn-gombos',
        component: () => import('@/pages/public/static/EarnGombosPage.vue')
      },
      {
        path: 'careers',
        name: 'public.careers',
        component: () => import('@/pages/public/static/CareersPage.vue')
      },
      {
        path: 'media-press',
        name: 'public.media-press',
        component: () => import('@/pages/public/static/MediaPressPage.vue')
      },
      {
        path: 'lottery-participation',
        name: 'public.lottery-participation',
        component: () => import('@/pages/public/StaticPage.vue')
      },
      {
        path: 'intellectual-property',
        name: 'public.intellectual-property',
        component: () => import('@/pages/public/StaticPage.vue')
      },
      {
        path: 'order-delivery',
        name: 'public.order-delivery',
        component: () => import('@/pages/public/StaticPage.vue')
      },
      {
        path: 'report-suspicious',
        name: 'public.report-suspicious',
        component: () => import('@/pages/public/static/ReportSuspiciousPage.vue')
      },
      {
        path: 'support-center',
        name: 'public.support-center',
        component: () => import('@/pages/public/static/SupportCenterPage.vue')
      },
      {
        path: 'security-center',
        name: 'public.security-center',
        component: () => import('@/pages/public/static/SecurityCenterPage.vue')
      },
      {
        path: 'peace-on-koumbaya',
        name: 'public.peace-on-koumbaya',
        component: () => import('@/pages/public/StaticPage.vue')
      },
      {
        path: 'sitemap',
        name: 'public.sitemap',
        component: () => import('@/pages/public/static/SitemapPage.vue')
      },
      {
        path: 'sell-on-koumbaya',
        name: 'public.sell-on-koumbaya',
        component: () => import('@/pages/public/static/SellOnKoumbayaPage.vue')
      },
      {
        path: 'terms',
        name: 'public.terms',
        component: () => import('@/pages/public/StaticPage.vue')
      },
      {
        path: 'privacy',
        name: 'public.privacy',
        component: () => import('@/pages/public/StaticPage.vue')
      },
      {
        path: 'legal',
        name: 'public.legal',
        component: () => import('@/pages/public/StaticPage.vue')
      },
      {
        path: 'cookies',
        name: 'public.cookies',
        component: () => import('@/pages/public/StaticPage.vue')
      }
    ]
  },


  // Customer Routes
  {
    path: '/customer',
    component: CustomerLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'customer.dashboard',
        component: CustomerDashboard
      },
      {
        path: 'products',
        name: 'customer.products',
        component: Products
      },
      {
        path: 'products/:id',
        name: 'customer.product.detail',
        component: ProductDetail,
        props: true
      },
      {
        path: 'profile',
        name: 'customer.profile',
        component: Profile
      },
      {
        path: 'tickets',
        name: 'customer.tickets',
        component: MyTickets
      },
      {
        path: 'refunds',
        name: 'customer.refunds',
        component: Refunds
      },
      {
        path: 'orders',
        name: 'customer.orders',
        component: OrderTracking
      },
      {
        path: 'payments',
        name: 'customer.payments',
        component: Payments
      },
      {
        path: 'orders/:id',
        name: 'customer.order.detail',
        component: OrderDetail,
        props: true
      },
      {
        path: 'payments/:id',
        name: 'customer.payment.detail',
        component: PaymentDetail,
        props: true
      },
      {
        path: 'notifications',
        name: 'customer.notifications',
        component: Notifications
      }
    ]
  },

  // Payment Routes (standalone, no layout)
  {
    path: '/payment/method',
    name: 'payment.method',
    component: PaymentMethod,
    meta: { requiresAuth: true }
  },
  {
    path: '/payment/phone',
    name: 'payment.phone',
    component: PaymentPhone,
    meta: { requiresAuth: true }
  },
  {
    path: '/payment/confirmation',
    name: 'payment.confirmation',
    component: PaymentConfirmation,
    meta: { requiresAuth: false } // Permettre l'accès sans authentification pour éviter la redirection lors de l'actualisation
  },

  // Admin Routes  
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'ADMIN' },
    children: [
      {
        path: '',
        name: 'admin.dashboard',
        component: AdminDashboard
      },
      {
        path: 'users',
        name: 'admin.users',
        component: UserManagement
      },
      {
        path: 'products',
        name: 'admin.products',
        component: ProductManagement
      },
      {
        path: 'products/:id',
        name: 'admin.product.detail',
        component: () => import('@/pages/admin/ProductDetail.vue')
      },
      {
        path: 'lotteries',
        name: 'admin.lotteries',
        component: LotteryManagement
      },
      {
        path: 'settings',
        name: 'admin.settings',
        component: AdminSettings
      },
      {
        path: 'profile',
        name: 'admin.profile',
        component: AdminProfile
      },
      {
        path: 'orders',
        name: 'admin.orders',
        component: OrderManagement
      },
      {
        path: 'refunds',
        name: 'admin.refunds',
        component: RefundManagement
      },
      {
        path: 'payments',
        name: 'admin.payments',
        component: PaymentManagement
      },
      {
        path: 'notifications',
        name: 'admin.notifications',
        component: Notifications
      }
    ]
  },

  // Merchant Routes
  {
    path: '/merchant',
    component: () => import('@/components/common/MerchantLayout.vue'),
    meta: { requiresAuth: true, role: 'MERCHANT' },
    children: [
      {
        path: '',
        redirect: 'dashboard'
      },
      {
        path: 'dashboard',
        name: 'merchant.dashboard',
        component: () => import('@/pages/merchant/Dashboard.vue')
      },
      {
        path: 'simple-dashboard',
        name: 'merchant.simple-dashboard',
        component: () => import('@/pages/merchant/SimpleMerchantDashboard.vue')
      },
      {
        path: 'products',
        name: 'merchant.products',
        component: () => import('@/pages/merchant/Products.vue')
      },
      {
        path: 'products/create',
        name: 'merchant.products.create',
        component: () => import('@/pages/merchant/CreateProduct.vue')
      },
      {
        path: 'products/:id/edit',
        name: 'merchant.products.edit',
        component: () => import('@/pages/merchant/EditProduct.vue'),
        props: true
      },
      {
        path: 'orders',
        name: 'merchant.orders',
        component: () => import('@/pages/merchant/Orders.vue')
      },
      {
        path: 'lotteries',
        name: 'merchant.lotteries',
        component: () => import('@/pages/merchant/LotteriesSimple.vue')
      },
      {
        path: 'lotteries/:id',
        name: 'merchant.lottery.view',
        component: () => import('@/pages/merchant/LotteryView.vue'),
        props: true
      },
      {
        path: 'analytics',
        name: 'merchant.analytics',
        component: () => import('@/pages/merchant/Analytics.vue')
      },
      {
        path: 'settings',
        name: 'merchant.settings',
        component: () => import('@/pages/merchant/Settings.vue')
      },
      {
        path: 'profile',
        name: 'merchant.profile',
        component: () => import('@/pages/merchant/Profile.vue')
      },
      {
        path: 'notifications',
        name: 'merchant.notifications',
        component: Notifications
      }
    ]
  },

  // Generic notifications route - redirects to appropriate profile
  {
    path: '/notifications',
    name: 'notifications',
    redirect: (to) => {
      const authStore = useAuthStore()
      if (!authStore.isAuthenticated) {
        return { name: 'login' }
      }
      
      const userRole = authStore.user?.role?.name?.toLowerCase()
      
      switch (userRole) {
        case 'admin':
        case 'super_admin':
          return { name: 'admin.notifications' }
        case 'merchant':
          return { name: 'merchant.notifications' }
        case 'customer':
        default:
          return { name: 'customer.notifications' }
      }
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Attendre la fin de l'initialisation de l'auth si nécessaire
  if (authStore.initializing) {
    let attempts = 0
    while (authStore.initializing && attempts < 100) { // Max 5 secondes (50ms * 100)
      await new Promise(resolve => setTimeout(resolve, 50))
      attempts++
    }
  }
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    // Préserver l'URL de destination pour rediriger après connexion
    const redirectPath = to.fullPath
    next({ 
      name: 'login',
      query: { redirect: encodeURIComponent(redirectPath) }
    })
    return
  }
  
  // Redirect authenticated users away from guest pages
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    const redirectTo = authStore.getDefaultRedirect()
    next({ name: redirectTo })
    return
  }
  
  // Role-based access control
  if (to.meta.role && authStore.isAuthenticated) {
    const hasAccess = checkRoleAccess(to.meta.role, authStore)
    
    if (!hasAccess) {
      const redirectTo = authStore.getDefaultRedirect()
      next({ name: redirectTo })
      return
    }
  }
  
  next()
})

// Helper function to check role access
function checkRoleAccess(requiredRole, authStore) {
  switch (requiredRole) {
    case 'ADMIN':
      return authStore.isAdmin
    case 'MERCHANT':
      return authStore.isMerchant
    case 'CUSTOMER':
      return authStore.isCustomer
    default:
      return false
  }
}

export default router