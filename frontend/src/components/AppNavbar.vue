<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { computed, ref } from 'vue'

const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)

const user = computed(() => authStore.user)
const roleDisplayNames = computed(() => {
  if (user.value?.role === 'siswa') return 'Siswa'
  return authStore.getRoleDisplayNames.join(', ') || 'User'
})

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}
</script>

<template>
  <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo & Brand -->
        <div class="flex items-center space-x-3">
          <router-link to="/dashboard" class="flex items-center space-x-2 group">
            <div class="bg-primary-500 p-2 rounded-lg group-hover:bg-primary-600 transition">
              <span class="text-2xl">🎓</span>
            </div>
            <div class="hidden sm:block">
              <h1 class="text-lg font-bold text-gray-800 group-hover:text-primary-600 transition">
                Sistem Dispensasi
              </h1>
              <p class="text-xs text-gray-500">SMA Negeri</p>
            </div>
          </router-link>
        </div>
        
        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-1">
          <router-link
            to="/dashboard"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            📊 Dashboard
          </router-link>
          
          <router-link
            to="/dispensasi"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            📄 Dispensasi
          </router-link>

          <!-- Menu Analytics (untuk admin & kesiswaan) -->
          <router-link
            v-if="authStore.canApprove"
            to="/analytics"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            📊 Analytics
          </router-link>

          <!-- Menu Users (hanya untuk admin) -->
          <router-link
            v-if="authStore.isAdmin"
            to="/users"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            👥 Users
          </router-link>

          <!-- Menu Audit Log (hanya untuk admin) -->
          <router-link
            v-if="authStore.isAdmin"
            to="/audit-logs"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            📜 Audit Log
          </router-link>

          <!-- Menu Backup (hanya untuk admin) -->
          <router-link
            v-if="authStore.isAdmin"
            to="/backups"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            💾 Backup
          </router-link>
        </div>

        <!-- User Info & Actions (Desktop) -->
        <div class="hidden md:flex items-center space-x-4">
          <div class="text-right">
            <p class="text-sm font-semibold text-gray-800">{{ user?.name }}</p>
            <p class="text-xs text-gray-500">{{ roleDisplayNames }}</p>
          </div>
          
          <button
            @click="handleLogout"
            class="bg-danger-500 hover:bg-danger-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm"
          >
            Keluar
          </button>
        </div>

        <!-- Mobile Menu Button -->
        <button 
          @click="toggleMobileMenu"
          class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="mobileMenuOpen" class="md:hidden pb-4 space-y-2">
        <router-link
          to="/dashboard"
          @click="mobileMenuOpen = false"
          class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          📊 Dashboard
        </router-link>
        
        <router-link
          to="/dispensasi"
          @click="mobileMenuOpen = false"
          class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          📄 Dispensasi
        </router-link>

        <!-- Menu Analytics (Mobile - untuk admin & kesiswaan) -->
        <router-link
          v-if="authStore.canApprove"
          to="/analytics"
          @click="mobileMenuOpen = false"
          class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          📊 Analytics
        </router-link>

        <!-- Menu Users (Mobile - hanya untuk admin) -->
        <router-link
          v-if="authStore.isAdmin"
          to="/users"
          @click="mobileMenuOpen = false"
          class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          👥 Users
        </router-link>

        <!-- Menu Audit Log (Mobile - hanya untuk admin) -->
        <router-link
          v-if="authStore.isAdmin"
          to="/audit-logs"
          @click="mobileMenuOpen = false"
          class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          📜 Audit Log
        </router-link>

        <!-- Menu Backup (Mobile - hanya untuk admin) -->
        <router-link
          v-if="authStore.isAdmin"
          to="/backups"
          @click="mobileMenuOpen = false"
          class="block px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          💾 Backup
        </router-link>

        <div class="px-4 py-3 bg-gray-50 rounded-lg">
          <p class="text-sm font-semibold text-gray-800">{{ user?.name }}</p>
          <p class="text-xs text-gray-500">{{ roleDisplayNames }}</p>
        </div>

        <button
          @click="handleLogout"
          class="w-full bg-danger-500 hover:bg-danger-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
        >
          Keluar
        </button>
      </div>
    </div>
  </nav>
</template>