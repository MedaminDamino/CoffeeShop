import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { Modal } from 'bootstrap'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../pages/user/HomePage.vue'),
    },
    { path: '/about', name: 'about', component: () => import('../pages/user/AboutPage.vue') },
    { path: '/services', name: 'services', component: () => import('../pages/user/ServicesPage.vue') },
    { path: '/menu', name: 'menu', component: () => import('../pages/user/ProductsPage.vue') },
    { path: '/reserve', name: 'reserve', component: () => import('../pages/user/ReservePage.vue') },
    { path: '/checkout', name: 'checkout', component: () => import('../pages/user/CheckoutPage.vue') },
    { path: '/checkout/online', name: 'online-checkout', component: () => import('../pages/user/OnlineCheckoutPape.vue') },
    { path: '/about', name: 'about', component: () => import('../pages/user/AboutPage.vue') },
    { path: '/contact', name: 'contact', component: () => import('../pages/user/ContactPage.vue') },
    { path: '/complete-profile', name: 'complete-profile', component: () => import('../components/CompleteProfile.vue') },

    {
      path: '/google/callback',
      name: 'GoogleCallback',
      component: () => import('../components/GoogleCallback.vue')
    },


    {
      path: '/admin',
      component: () => import('../pages/admin/AdminLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', redirect: '/admin/users' },
        { path: 'users', component: () => import('../pages/admin/UsersAdmin.vue') },
        { path: 'branches', component: () => import('../pages/admin/BranchesAdmin.vue') },
        { path: 'tables', component: () => import('../pages/admin/TablesAdmin.vue') },
        { path: 'categories', component: () => import('../pages/admin/CategoriesAdmin.vue') },
        { path: 'products', component: () => import('../pages/admin/ProductsAdmin.vue') },
        { path: 'reservations', component: () => import('../pages/admin/ReservationsAdmin.vue') },
        { path: 'orders', component: () => import('../pages/admin/OrdersAdmin.vue') },
        { path: 'promotions', component: () => import('../pages/admin/PromotionsAdmin.vue') },
      ],
    },
  ],
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!authStore.isAuthenticated) {
      // Redirect to home with login modal
      next('/')
      // Trigger login modal
      setTimeout(() => {
        const authModalElement = document.getElementById('authModal')
        if (authModalElement) {
          const modal = new Modal(authModalElement)
          modal.show()
        }
      }, 100)
    } else if (authStore.user?.role !== 'admin' && authStore.user?.role !== 'super_admin') {
      // User is authenticated but not admin/super_admin
      next('/')
    } else {
      next()
    }
  } else {
    next()
  }
})

// Scroll to top on route change
router.afterEach(() => {
  window.scrollTo(0, 0)
})

export default router
