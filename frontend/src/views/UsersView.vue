<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const users = ref([])
const loading = ref(true)
const stats = ref({})
const filters = ref({
  role: 'all',
  search: ''
})

const user = computed(() => authStore.user)

// Redirect if not admin
if (!authStore.isAdmin) {
  router.push('/dashboard')
}

async function fetchStats() {
  try {
    const response = await api.get('/users/statistics')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

async function fetchUsers() {
  loading.value = true
  try {
    const params = {}
    if (filters.value.role !== 'all') {
      params.role = filters.value.role
    }
    if (filters.value.search) {
      params.search = filters.value.search
    }

    const response = await api.get('/users', { params })
    users.value = response.data.data
  } catch (error) {
    console.error('Error fetching users:', error)
  } finally {
    loading.value = false
  }
}

async function handleDelete(userId, userName) {
  if (!confirm(`Apakah Anda yakin ingin menghapus user "${userName}"?`)) {
    return
  }

  try {
    await api.delete(`/users/${userId}`)
    alert('User berhasil dihapus')
    fetchUsers()
    fetchStats()
  } catch (error) {
    console.error('Error deleting user:', error)
    alert(error.response?.data?.message || 'Gagal menghapus user')
  }
}

onMounted(() => {
  fetchStats()
  fetchUsers()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
              <span class="text-3xl mr-3">👥</span>
              Manajemen User
            </h1>
            <p class="text-gray-600 mt-1">Kelola data pengguna sistem</p>
          </div>
          
          <router-link
            to="/users/create"
            class="inline-flex items-center justify-center bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold transition shadow-sm"
          >
            <span class="text-xl mr-2">➕</span>
            Tambah User
          </router-link>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Total User</p>
          <p class="text-2xl sm:text-3xl font-bold text-gray-800">{{ stats.total_users || 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Siswa</p>
          <p class="text-2xl sm:text-3xl font-bold text-primary-600">{{ stats.total_siswa || 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Guru</p>
          <p class="text-2xl sm:text-3xl font-bold text-success-600">{{ stats.total_guru || 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Admin</p>
          <p class="text-2xl sm:text-3xl font-bold text-warning-600">{{ stats.total_admin || 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Bulan Ini</p>
          <p class="text-2xl sm:text-3xl font-bold text-purple-600">{{ stats.users_this_month || 0 }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              🔍 Pencarian
            </label>
            <input
              v-model="filters.search"
              @input="fetchUsers"
              type="text"
              placeholder="Cari nama, email, NISN, NIP..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <!-- Filter Role -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              🎯 Filter Role
            </label>
            <select
              v-model="filters.role"
              @change="fetchUsers"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="all">Semua Role</option>
              <option value="siswa">Siswa</option>
              <option value="guru">Guru</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div v-if="loading" class="p-12 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
          <p class="text-gray-500 mt-4">Memuat data...</p>
        </div>

        <div v-else-if="users.length === 0" class="p-12 text-center">
          <div class="text-6xl mb-4">👤</div>
          <p class="text-gray-500 text-lg font-medium">Tidak ada user ditemukan</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Role</th>
                <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Info</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in users" :key="item.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ index + 1 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-primary-100 rounded-full flex items-center justify-center">
                      <span class="text-lg">
                        {{ item.role === 'siswa' ? '👨‍🎓' : item.role === 'guru' ? '👨‍🏫' : '👨‍💼' }}
                      </span>
                    </div>
                    <div class="ml-3">
                      <p class="font-medium text-gray-900">{{ item.name }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ item.email }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-3 py-1 text-xs font-semibold rounded-full capitalize"
                    :class="{
                      'bg-primary-100 text-primary-800': item.role === 'siswa',
                      'bg-success-100 text-success-800': item.role === 'guru',
                      'bg-warning-100 text-warning-800': item.role === 'admin'
                    }"
                  >
                    {{ item.role }}
                  </span>
                  <div v-if="item.roles && item.roles.length > 0" class="flex flex-wrap gap-1 mt-1">
                    <span 
                      v-for="role in item.roles" 
                      :key="role.id"
                      class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800"
                    >
                      {{ role.display_name }}
                    </span>
                  </div>
                </td>
                <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-600">
                  <div v-if="item.role === 'siswa'">
                    <p class="text-xs">Kelas: {{ item.kelas?.nama_kelas || '-' }}</p>
                    <p class="text-xs">NISN: {{ item.nisn }}</p>
                  </div>
                  <div v-else>
                    <p class="text-xs">NIP: {{ item.nip || '-' }}</p>
                    <p class="text-xs">Mapel: {{ item.mata_pelajaran || '-' }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                  <router-link
                    :to="`/users/${item.id}/edit`"
                    class="text-primary-600 hover:text-primary-800 font-semibold"
                  >
                    ✏️ Edit
                  </router-link>
                  <button
                    @click="handleDelete(item.id, item.name)"
                    class="text-danger-600 hover:text-danger-800 font-semibold"
                  >
                    🗑️ Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>