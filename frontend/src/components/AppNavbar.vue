<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { computed, ref } from 'vue'

const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)
const profileDropdownOpen = ref(false)

const user = computed(() => authStore.user)
const roleDisplayNames = computed(() => {
  if (user.value?.role === 'siswa') return 'Siswa'
  return authStore.getRoleDisplayNames.join(', ') || 'User'
})

function getInitials(name) {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
}

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function toggleProfileDropdown() {
  profileDropdownOpen.value = !profileDropdownOpen.value
}

function closeProfileDropdown() {
  profileDropdownOpen.value = false
}
</script>

<template>
  <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">

        <!-- Logo & Brand -->
        <div class="flex items-center space-x-3">
          <router-link to="/dashboard" class="flex items-center space-x-3 group">
            <div class="flex-shrink-0">
              <img
                :src="`/images/logo-smala.png.PNG`"
                alt="Logo SMALA"
                class="h-10 w-10 object-contain"
                @error="$event.target.style.display='none'; $event.target.nextElementSibling.style.display='flex'"
              />
              <div
                class="h-10 w-10 bg-primary-500 rounded-lg items-center justify-center group-hover:bg-primary-600 transition hidden"
                style="display:none"
              >
                <span class="text-xl">🎓</span>
              </div>
            </div>
            <div class="hidden sm:block leading-tight">
              <h1 class="text-xs font-bold text-gray-800 group-hover:text-primary-600 transition leading-none whitespace-nowrap">
                SMA Negeri 5 Mataram
              </h1>
              <p class="text-[11px] text-primary-500 font-medium mt-0.5 whitespace-nowrap">Sistem Dispensasi</p>
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

          <router-link
            v-if="authStore.canApprove"
            to="/analytics"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            📊 Analytics
          </router-link>

          <router-link
            v-if="authStore.isAdmin"
            to="/users"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            👥 Users
          </router-link>

          <router-link
            v-if="authStore.isAdmin"
            to="/audit-logs"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            📜 Audit Log
          </router-link>

          <router-link
            v-if="authStore.isAdmin"
            to="/backups"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
            active-class="bg-primary-50 text-primary-600"
          >
            💾 Backup
          </router-link>
        </div>

        <!-- User Dropdown (Desktop) -->
        <div class="hidden md:flex items-center space-x-3">
          <!-- Profile Dropdown -->
          <div class="relative" v-click-outside="closeProfileDropdown">
            <button
              @click="toggleProfileDropdown"
              class="flex items-center space-x-2.5 px-3 py-1.5 rounded-xl hover:bg-gray-100 transition group"
            >
              <!-- Avatar -->
              <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                <span class="text-primary-600 text-xs font-bold">{{ getInitials(user?.name) }}</span>
              </div>
              <!-- Info -->
              <div class="text-left">
                <p class="text-sm font-semibold text-gray-800 leading-none">{{ user?.name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ roleDisplayNames }}</p>
              </div>
              <!-- Chevron -->
              <svg
                class="w-4 h-4 text-gray-400 transition-transform"
                :class="profileDropdownOpen ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <div
              v-if="profileDropdownOpen"
              class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50"
            >
              <!-- User info header -->
              <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-800">{{ user?.name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ user?.email }}</p>
              </div>

              <router-link
                to="/profile"
                @click="closeProfileDropdown"
                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
              >
                <span>👤</span>
                <span>Pengaturan Profile</span>
              </router-link>

              <div class="border-t border-gray-100 mt-1 pt-1">
                <button
                  @click="handleLogout"
                  class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  <span>Keluar</span>
                </button>
              </div>
            </div>
          </div>
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
      <div v-if="mobileMenuOpen" class="md:hidden pb-4 space-y-1 border-t border-gray-100 pt-3 mt-1">

        <!-- User info mobile -->
        <router-link
          to="/profile"
          @click="mobileMenuOpen = false"
          class="px-4 py-3 bg-primary-50 rounded-xl flex items-center space-x-3 mb-3 hover:bg-primary-100 transition"
        >
          <div class="h-10 w-10 rounded-full bg-primary-200 flex items-center justify-center flex-shrink-0">
            <span class="text-primary-700 text-sm font-bold">{{ getInitials(user?.name) }}</span>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-gray-800">{{ user?.name }}</p>
            <p class="text-xs text-gray-500">{{ roleDisplayNames }}</p>
          </div>
          <span class="text-xs text-primary-500 font-medium">Edit Profil →</span>
        </router-link>

        <router-link
          to="/dashboard"
          @click="mobileMenuOpen = false"
          class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          📊 Dashboard
        </router-link>

        <router-link
          to="/dispensasi"
          @click="mobileMenuOpen = false"
          class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          📄 Dispensasi
        </router-link>

        <router-link
          v-if="authStore.canApprove"
          to="/analytics"
          @click="mobileMenuOpen = false"
          class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          📊 Analytics
        </router-link>

        <router-link
          v-if="authStore.isAdmin"
          to="/users"
          @click="mobileMenuOpen = false"
          class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          👥 Users
        </router-link>

        <router-link
          v-if="authStore.isAdmin"
          to="/audit-logs"
          @click="mobileMenuOpen = false"
          class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          📜 Audit Log
        </router-link>

        <router-link
          v-if="authStore.isAdmin"
          to="/backups"
          @click="mobileMenuOpen = false"
          class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition"
          active-class="bg-primary-50 text-primary-600"
        >
          💾 Backup
        </router-link>

        <button
          @click="handleLogout"
          class="w-full bg-danger-500 hover:bg-danger-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition flex items-center justify-center space-x-2 mt-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span>Keluar</span>
        </button>
      </div>
    </div>
  </nav>
</template>