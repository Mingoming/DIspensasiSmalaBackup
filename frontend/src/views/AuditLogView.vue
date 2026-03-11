<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const logs = ref([])
const loading = ref(true)
const error = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const totalLogs = ref(0)

const filters = ref({
  action: '', user_id: '', model: '', date_from: '', date_to: '', search: ''
})

const users = ref([])

if (!authStore.isAdmin) {
  router.push('/dashboard')
}

async function fetchUsers() {
  try {
    const response = await api.get('/users')
    users.value = response.data.data
  } catch (err) {
    console.error('Error fetching users:', err)
  }
}

async function fetchLogs(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const params = { page, per_page: 20 }
    if (filters.value.action)    params.action    = filters.value.action
    if (filters.value.user_id)   params.user_id   = filters.value.user_id
    if (filters.value.model)     params.model     = filters.value.model
    if (filters.value.date_from) params.date_from = filters.value.date_from
    if (filters.value.date_to)   params.date_to   = filters.value.date_to
    if (filters.value.search)    params.search    = filters.value.search

    const response = await api.get('/audit-logs', { params })

    if (Array.isArray(response.data)) {
      logs.value = response.data
      totalLogs.value = response.data.length
    } else {
      logs.value = response.data.data || []
      currentPage.value = response.data.current_page || 1
      totalPages.value = response.data.last_page || 1
      totalLogs.value = response.data.total || 0
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal memuat audit logs'
  } finally {
    loading.value = false
  }
}

// Semua action badge pakai type untuk SVG icon
function getActionBadge(action) {
  const badges = {
    login:           { class: 'bg-blue-100 text-blue-800 border-blue-200',       icon: 'login',    text: 'Login' },
    login_success:   { class: 'bg-blue-100 text-blue-800 border-blue-200',       icon: 'login',    text: 'Login' },
    logout:          { class: 'bg-gray-100 text-gray-700 border-gray-200',       icon: 'logout',   text: 'Logout' },
    login_failed:    { class: 'bg-red-100 text-red-800 border-red-200',          icon: 'warning',  text: 'Login Failed' },
    create:          { class: 'bg-green-100 text-green-800 border-green-200',    icon: 'create',   text: 'Create' },
    update:          { class: 'bg-amber-100 text-amber-800 border-amber-200',    icon: 'edit',     text: 'Update' },
    delete:          { class: 'bg-red-100 text-red-800 border-red-200',          icon: 'trash',    text: 'Delete' },
    approve:         { class: 'bg-green-100 text-green-800 border-green-200',    icon: 'check',    text: 'Approve' },
    reject:          { class: 'bg-red-100 text-red-800 border-red-200',          icon: 'x',        text: 'Reject' },
    backup_create:   { class: 'bg-purple-100 text-purple-800 border-purple-200', icon: 'backup',   text: 'Backup' },
    backup_download: { class: 'bg-indigo-100 text-indigo-800 border-indigo-200', icon: 'download', text: 'Download' },
    backup_delete:   { class: 'bg-red-100 text-red-800 border-red-200',          icon: 'trash',    text: 'Del Backup' },
    update_profile:  { class: 'bg-blue-100 text-blue-800 border-blue-200',       icon: 'user',     text: 'Update Profile' },
    update_password: { class: 'bg-orange-100 text-orange-800 border-orange-200', icon: 'key',      text: 'Ubah Password' },
  }
  return badges[action] || { class: 'bg-gray-100 text-gray-700 border-gray-200', icon: 'default', text: action }
}

function formatDateTime(date) {
  if (!date) return '-'
  const d = new Date(date)
  if (isNaN(d.getTime())) return '-'
  return d.toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
}

const hasActiveFilter = computed(() =>
  filters.value.action || filters.value.user_id || filters.value.search ||
  filters.value.date_from || filters.value.date_to
)

function resetFilters() {
  filters.value = { action: '', user_id: '', model: '', date_from: '', date_to: '', search: '' }
  fetchLogs()
}

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) fetchLogs(page)
}

onMounted(() => {
  fetchUsers()
  fetchLogs()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
          <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          Audit Log
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">
          Riwayat aktivitas sistem —
          <span class="font-semibold text-gray-700">{{ totalLogs }}</span> log tercatat
        </p>
      </div>

      <!-- Error -->
      <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ error }}
      </div>

      <!-- Filter -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
            <h3 class="text-sm font-semibold text-gray-700">Filter</h3>
            <span
              v-if="hasActiveFilter"
              class="text-xs bg-primary-100 text-primary-700 font-semibold px-2 py-0.5 rounded-full"
            >Aktif</span>
          </div>
          <button
            v-if="hasActiveFilter"
            @click="resetFilters"
            class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-red-500 font-semibold transition border border-gray-200 hover:border-red-200 hover:bg-red-50 px-2.5 py-1.5 rounded-lg"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">

          <!-- Search -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Pencarian</label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Cari deskripsi..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
              />
            </div>
          </div>

          <!-- Action -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Action</label>
            <select
              v-model="filters.action"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white"
            >
              <option value="">Semua Action</option>
              <option value="login">Login</option>
              <option value="logout">Logout</option>
              <option value="create">Create</option>
              <option value="update">Update</option>
              <option value="delete">Delete</option>
              <option value="approve">Approve</option>
              <option value="reject">Reject</option>
              <option value="backup_create">Backup</option>
            </select>
          </div>

          <!-- User -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">User</label>
            <select
              v-model="filters.user_id"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white"
            >
              <option value="">Semua User</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>

          <!-- Date From -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Dari Tanggal</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
            />
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Sampai Tanggal</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
            />
          </div>

        </div>

        <button
          @click="fetchLogs(1)"
          class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white px-5 py-2 rounded-lg text-sm font-semibold transition shadow-sm"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Cari
        </button>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        <!-- Loading -->
        <div v-if="loading" class="py-16 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mb-4"></div>
          <p class="text-sm text-gray-400">Memuat data...</p>
        </div>

        <!-- Empty -->
        <div v-else-if="logs.length === 0" class="py-16 text-center">
          <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <p class="text-gray-600 font-medium">Tidak ada log ditemukan</p>
          <p class="text-gray-400 text-sm mt-1">Coba reset filter atau ubah kriteria pencarian</p>
          <button
            v-if="hasActiveFilter"
            @click="resetFilters"
            class="mt-4 inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800 font-semibold border border-primary-200 hover:border-primary-400 px-4 py-2 rounded-lg transition"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Reset Filter
          </button>
        </div>

        <div v-else>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-gray-50/70">
                <tr>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP Address</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50/60 transition-colors">

                  <!-- Waktu -->
                  <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ formatDateTime(log.created_at) }}
                  </td>

                  <!-- User -->
                  <td class="px-5 py-4 whitespace-nowrap">
                    <div v-if="log.user" class="flex items-center gap-2.5">
                      <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ getInitials(log.user.name) }}
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ log.user.name }}</p>
                        <p class="text-xs text-gray-400">{{ log.user.email }}</p>
                      </div>
                    </div>
                    <span v-else class="text-xs text-gray-400 italic">System</span>
                  </td>

                  <!-- Action badge dengan SVG icon -->
                  <td class="px-5 py-4 whitespace-nowrap">
                    <span
                      :class="getActionBadge(log.action).class"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full border"
                    >
                      <!-- Login -->
                      <svg v-if="getActionBadge(log.action).icon === 'login'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                      </svg>
                      <!-- Logout -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'logout'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      <!-- Warning -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'warning'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                      </svg>
                      <!-- Create -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'create'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      <!-- Edit -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'edit'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      <!-- Trash -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'trash'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      <!-- Check -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'check'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      <!-- X -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'x'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      <!-- Backup -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'backup'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 3.582 3 8 3s8-1 8-3V7M4 7c0 2 3.582 3 8 3s8-1 8-3M4 7c0-2 3.582-3 8-3s8 1 8 3" />
                      </svg>
                      <!-- Download -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'download'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                      </svg>
                      <!-- User -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'user'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <!-- Key -->
                      <svg v-else-if="getActionBadge(log.action).icon === 'key'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                      </svg>
                      <!-- Default -->
                      <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      {{ getActionBadge(log.action).text }}
                    </span>
                  </td>

                  <!-- Deskripsi -->
                  <td class="px-5 py-4 text-sm text-gray-600 max-w-sm">
                    {{ log.description }}
                  </td>

                  <!-- IP -->
                  <td class="px-5 py-4 whitespace-nowrap">
                    <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded">
                      {{ log.ip_address || '-' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-gray-500">
              Halaman <span class="font-semibold text-gray-700">{{ currentPage }}</span>
              dari <span class="font-semibold text-gray-700">{{ totalPages }}</span>
              &nbsp;·&nbsp; Total <span class="font-semibold text-gray-700">{{ totalLogs }}</span> log
            </p>
            <div class="flex items-center gap-1.5">
              <!-- Previous -->
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold border border-gray-200 rounded-lg text-gray-600 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Prev
              </button>

              <!-- Page numbers -->
              <template v-for="p in totalPages" :key="p">
                <button
                  v-if="p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)"
                  @click="goToPage(p)"
                  :class="p === currentPage
                    ? 'bg-primary-500 text-white border-primary-500'
                    : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                  class="w-8 h-8 text-xs font-semibold border rounded-lg transition"
                >
                  {{ p }}
                </button>
                <span
                  v-else-if="p === currentPage - 2 || p === currentPage + 2"
                  class="w-8 h-8 flex items-center justify-center text-xs text-gray-400"
                >…</span>
              </template>

              <!-- Next -->
              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold border border-gray-200 rounded-lg text-gray-600 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
              >
                Next
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>