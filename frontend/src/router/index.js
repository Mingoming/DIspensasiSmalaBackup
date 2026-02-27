import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/login'
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { guest: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/dispensasi',
      name: 'dispensasi',
      component: () => import('../views/DispensasiView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/dispensasi/create',
      name: 'dispensasi-create',
      component: () => import('../views/DispensasiCreateView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/dispensasi/:id',
      name: 'dispensasi-detail',
      component: () => import('../views/DispensasiDetailView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/dispensasi/:id/edit',
      name: 'dispensasi-edit',
      component: () => import('../views/DispensasiEditView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('../views/UsersView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/users/create',
      name: 'users-create',
      component: () => import('../views/UsersCreateView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/users/:id/edit',
      name: 'users-edit',
      component: () => import('../views/UsersEditView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/analytics',
      name: 'analytics',
      component: () => import('../views/AnalyticsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/ProfileView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/audit-logs',
      name: 'audit-logs',
      component: () => import('../views/AuditLogView.vue'),
      meta: { requiresAuth: true, adminOnly: true }
    },
    {
      path: '/backups',
      name: 'backups',
      component: () => import('../views/BackupView.vue'),
      meta: { requiresAuth: true, adminOnly: true }
    }
  ]
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/dashboard')
  } else if (to.meta.adminOnly && !authStore.isAdmin) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router