import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Layouts
import GuestLayout from '@/components/common/GuestLayout.vue'
import CustomerLayout from '@/components/common/CustomerLayout.vue'
import AdminLayout from '@/components/common/AdminLayout.vue'

// Guest Pages
import Login from '@/pages/auth/Login.vue'
import Register from '@/pages/auth/Register.vue'
import Home from '@/pages/guest/Home.vue'
import Results from '@/pages/public/Results.vue'
import VerifyWinner from '@/pages/public/VerifyWinner.vue'
import History from '@/pages/public/History.vue'

// Customer Pages
import CustomerDashboard from '@/pages/customer/Dashboard.vue'
import Products from '@/pages/customer/Products.vue'
import ProductDetail from '@/pages/customer/ProductDetail.vue'
import Profile from '@/pages/customer/Profile.vue'
import MyTickets from '@/pages/customer/MyTickets.vue'
import Refunds from '@/pages/customer/Refunds.vue'

// Admin Pages
import AdminDashboard from '@/pages/admin/Dashboard.vue'
import UserManagement from '@/pages/admin/UserManagement.vue'
import ProductManagement from '@/pages/admin/ProductManagement.vue'
import LotteryManagement from '@/pages/admin/LotteryManagement.vue'
import CountryLanguageManagement from '@/pages/admin/CountryLanguageManagement.vue'
import RefundManagement from '@/pages/admin/RefundManagement.vue'

const routes = [
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
        path: 'register',
        name: 'register',
        component: Register,
        meta: { requiresGuest: true }
      }
    ]
  },

  // Customer Routes
  {
    path: '/customer',
    component: CustomerLayout,
    meta: { requiresAuth: true, role: 'CUSTOMER' },
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
      }
    ]
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
        path: 'lotteries',
        name: 'admin.lotteries',
        component: LotteryManagement
      },
      {
        path: 'settings',
        name: 'admin.settings',
        component: CountryLanguageManagement
      },
      {
        path: 'refunds',
        name: 'admin.refunds',
        component: RefundManagement
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login' })
    return
  }
  
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    // Redirect based on user role
    if (authStore.user?.role === 'ADMIN') {
      next({ name: 'admin.dashboard' })
    } else {
      next({ name: 'customer.dashboard' })
    }
    return
  }
  
  if (to.meta.role && authStore.user?.role !== to.meta.role) {
    // Redirect to appropriate dashboard if wrong role
    if (authStore.user?.role === 'ADMIN') {
      next({ name: 'admin.dashboard' })
    } else {
      next({ name: 'customer.dashboard' })
    }
    return
  }
  
  next()
})

export default router