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
  action: '',
  user_id: '',
  model: '',
  date_from: '',
  date_to: '',
  search: ''
})

const users = ref([])

// Redirect if not admin
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
    const params = {
      page,
      per_page: 20,
    }

    // ✅ HANYA tambahkan filter jika ada value
    if (filters.value.action) params.action = filters.value.action
    if (filters.value.user_id) params.user_id = filters.value.user_id
    if (filters.value.model) params.model = filters.value.model
    if (filters.value.date_from) params.date_from = filters.value.date_from
    if (filters.value.date_to) params.date_to = filters.value.date_to
    if (filters.value.search) params.search = filters.value.search

    console.log('Fetching logs with params:', params)

    const response = await api.get('/audit-logs', { params })
    
    console.log('API Response:', response.data)

    // ✅ HANDLE BOTH FORMATS (data array atau pagination object)
    if (Array.isArray(response.data)) {
      // Jika response langsung array
      logs.value = response.data
      totalLogs.value = response.data.length
    } else {
      // Jika response pagination object
      logs.value = response.data.data || []
      currentPage.value = response.data.current_page || 1
      totalPages.value = response.data.last_page || 1
      totalLogs.value = response.data.total || 0
    }

    console.log('Logs loaded:', logs.value.length)

  } catch (err) {
    console.error('Error fetching logs:', err)
    error.value = err.response?.data?.message || 'Gagal memuat audit logs'
    
    // ✅ Show detailed error in console
    if (err.response) {
      console.error('Response status:', err.response.status)
      console.error('Response data:', err.response.data)
    }
  } finally {
    loading.value = false
  }
}

function getActionBadge(action) {
  const badges = {
    login: { class: 'bg-blue-100 text-blue-800', icon: '🔐', text: 'Login' },
    logout: { class: 'bg-gray-100 text-gray-800', icon: '🚪', text: 'Logout' },
    login_failed: { class: 'bg-red-100 text-red-800', icon: '⚠️', text: 'Login Failed' },
    create: { class: 'bg-green-100 text-green-800', icon: '➕', text: 'Create' },
    update: { class: 'bg-yellow-100 text-yellow-800', icon: '✏️', text: 'Update' },
    delete: { class: 'bg-red-100 text-red-800', icon: '🗑️', text: 'Delete' },
    approve: { class: 'bg-success-100 text-success-800', icon: '✅', text: 'Approve' },
    reject: { class: 'bg-danger-100 text-danger-800', icon: '❌', text: 'Reject' },
    backup_create: { class: 'bg-purple-100 text-purple-800', icon: '💾', text: 'Backup' },
    backup_download: { class: 'bg-indigo-100 text-indigo-800', icon: '⬇️', text: 'Download Backup' },
    backup_delete: { class: 'bg-red-100 text-red-800', icon: '🗑️', text: 'Delete Backup' },
    update_profile: { class: 'bg-blue-100 text-blue-800', icon: '👤', text: 'Update Profile' },
    update_password: { class: 'bg-orange-100 text-orange-800', icon: '🔑', text: 'Change Password' },
  }
  
  return badges[action] || { class: 'bg-gray-100 text-gray-800', icon: '📝', text: action }
}

function resetFilters() {
  filters.value = {
    action: '',
    user_id: '',
    model: '',
    date_from: '',
    date_to: '',
    search: ''
  }
  fetchLogs()
}

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    fetchLogs(page)
  }
}

onMounted(() => {
  console.log('AuditLogView mounted')
  console.log('User is admin:', authStore.isAdmin)
  fetchUsers()
  fetchLogs()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
          <span class="text-3xl mr-3">📜</span>
          Audit Log
        </h1>
        <p class="text-gray-600 mt-1">Riwayat aktivitas sistem</p>
        <p class="text-sm text-gray-500 mt-1">Total: {{ totalLogs }} logs</p>
      </div>

      <!-- ✅ DEBUG INFO (Hapus setelah fix) -->
      <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800 font-semibold">Error:</p>
        <p class="text-red-700 text-sm">{{ error }}</p>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-800">🔍 Filter</h3>
          <button
            @click="resetFilters"
            class="text-sm text-primary-600 hover:text-primary-800 font-semibold"
          >
            Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Pencarian</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari deskripsi..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <!-- Action -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Action</label>
            <select
              v-model="filters.action"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
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
            <label class="block text-sm font-semibold text-gray-700 mb-2">User</label>
            <select
              v-model="filters.user_id"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">Semua User</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>

          <!-- Date From -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Dari Tanggal</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Sampai Tanggal</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>
        </div>

        <button
          @click="fetchLogs(1)"
          class="w-full sm:w-auto bg-primary-500 hover:bg-primary-600 text-white px-6 py-2 rounded-lg font-semibold transition"
        >
          🔍 Cari
        </button>
      </div>

      <!-- Logs Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div v-if="loading" class="p-12 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
          <p class="text-gray-500 mt-4">Memuat data...</p>
        </div>

        <div v-else-if="logs.length === 0" class="p-12 text-center">
          <div class="text-6xl mb-4">📭</div>
          <p class="text-gray-500 text-lg font-medium">Tidak ada log ditemukan</p>
          <p class="text-gray-400 text-sm mt-2">Coba reset filter atau ubah kriteria pencarian</p>
        </div>

        <div v-else>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">User</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Action</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Deskripsi</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">IP Address</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ new Date(log.created_at).toLocaleString('id-ID', { 
                      day: '2-digit', 
                      month: 'short', 
                      year: 'numeric',
                      hour: '2-digit', 
                      minute: '2-digit' 
                    }) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div v-if="log.user">
                      <p class="font-medium text-gray-900">{{ log.user.name }}</p>
                      <p class="text-xs text-gray-500">{{ log.user.email }}</p>
                    </div>
                    <span v-else class="text-gray-400 italic">System</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="getActionBadge(log.action).class" 
                      class="px-3 py-1 text-xs font-semibold rounded-full inline-flex items-center"
                    >
                      <span class="mr-1">{{ getActionBadge(log.action).icon }}</span>
                      {{ getActionBadge(log.action).text }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700 max-w-md">
                    {{ log.description }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ log.ip_address }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
            <div class="text-sm text-gray-700">
              Halaman {{ currentPage }} dari {{ totalPages }} (Total: {{ totalLogs }} logs)
            </div>
            <div class="flex gap-2">
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                ← Previous
              </button>
              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next →
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>