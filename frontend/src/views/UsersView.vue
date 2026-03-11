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
const filters = ref({ role: 'all', search: '' })

const user = computed(() => authStore.user)

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
    if (filters.value.role !== 'all') params.role = filters.value.role
    if (filters.value.search) params.search = filters.value.search
    const response = await api.get('/users', { params })
    users.value = response.data.data
  } catch (error) {
    console.error('Error fetching users:', error)
  } finally {
    loading.value = false
  }
}

async function handleDelete(userId, userName) {
  if (!confirm(`Apakah Anda yakin ingin menghapus user "${userName}"?`)) return
  try {
    await api.delete(`/users/${userId}`)
    alert('User berhasil dihapus')
    fetchUsers()
    fetchStats()
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menghapus user')
  }
}

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
}

function getAvatarColor(role) {
  const colors = {
    siswa: 'bg-blue-100 text-blue-600',
    guru:  'bg-green-100 text-green-600',
    admin: 'bg-amber-100 text-amber-600'
  }
  return colors[role] || 'bg-gray-100 text-gray-600'
}

const hasActiveFilter = computed(() =>
  filters.value.role !== 'all' || filters.value.search !== ''
)

function resetFilters() {
  filters.value.role = 'all'
  filters.value.search = ''
  fetchUsers()
}

onMounted(() => {
  fetchStats()
  fetchUsers()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
           
            Manajemen User
          </h1>
          <p class="text-sm text-gray-500 mt-0.5">Kelola data pengguna sistem</p>
        </div>
        <router-link
          to="/users/create"
          class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm hover:shadow-md self-start sm:self-auto"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah User
        </router-link>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        <!-- Total -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ stats.total_users || 0 }}</p>
          </div>
        </div>

        <!-- Siswa -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">Siswa</p>
            <p class="text-2xl font-bold text-blue-600">{{ stats.total_siswa || 0 }}</p>
          </div>
        </div>

        <!-- Guru -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">Guru</p>
            <p class="text-2xl font-bold text-green-600">{{ stats.total_guru || 0 }}</p>
          </div>
        </div>

        <!-- Admin -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">Admin</p>
            <p class="text-2xl font-bold text-amber-600">{{ stats.total_admin || 0 }}</p>
          </div>
        </div>

        <!-- Bulan Ini -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium">Bulan Ini</p>
            <p class="text-2xl font-bold text-purple-600">{{ stats.users_this_month || 0 }}</p>
          </div>
        </div>

      </div>

      <!-- Filter -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
            </svg>
            <h3 class="text-sm font-semibold text-gray-700">Filter & Pencarian</h3>
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
            Reset
          </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Pencarian</label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                v-model="filters.search"
                @input="fetchUsers"
                type="text"
                placeholder="Cari nama, email, NISN, NIP..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
              />
            </div>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Filter Role</label>
            <select
              v-model="filters.role"
              @change="fetchUsers"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white"
            >
              <option value="all">Semua Role</option>
              <option value="siswa">Siswa</option>
              <option value="guru">Guru</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabel -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        <!-- Loading -->
        <div v-if="loading" class="py-16 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mb-4"></div>
          <p class="text-sm text-gray-400">Memuat data...</p>
        </div>

        <!-- Empty -->
        <div v-else-if="users.length === 0" class="py-16 text-center">
          <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <p class="text-gray-600 font-medium">Tidak ada user ditemukan</p>
          <p class="text-gray-400 text-sm mt-1">
            {{ hasActiveFilter ? 'Coba ubah filter pencarian' : 'Belum ada user yang terdaftar' }}
          </p>
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

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/70">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">No</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="hidden lg:table-cell px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="(item, index) in users"
                :key="item.id"
                class="hover:bg-gray-50/60 transition-colors"
              >
                <td class="px-5 py-4 text-sm text-gray-400 font-medium">{{ index + 1 }}</td>

                <!-- Nama + Avatar -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div
                      :class="getAvatarColor(item.role)"
                      class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0"
                    >
                      {{ getInitials(item.name) }}
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ item.name }}</p>
                  </div>
                </td>

                <!-- Email -->
                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.email }}</td>

                <!-- Role -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="flex flex-wrap gap-1">
                    <span
                      class="px-2.5 py-1 text-xs font-semibold rounded-full border capitalize"
                      :class="{
                        'bg-blue-100 text-blue-800 border-blue-200':   item.role === 'siswa',
                        'bg-green-100 text-green-800 border-green-200': item.role === 'guru',
                        'bg-amber-100 text-amber-800 border-amber-200': item.role === 'admin'
                      }"
                    >
                      {{ item.role }}
                    </span>
                    <span
                      v-for="role in item.roles"
                      :key="role.id"
                      class="px-2.5 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700 border border-purple-200"
                    >
                      {{ role.display_name }}
                    </span>
                  </div>
                </td>

                <!-- Info -->
                <td class="hidden lg:table-cell px-5 py-4 text-sm text-gray-500">
                  <div v-if="item.role === 'siswa'" class="space-y-0.5">
                    <p class="text-xs flex items-center gap-1.5">
                      
                      {{ item.kelas?.nama_kelas || '-' }}
                    </p>
                    <p class="text-xs flex items-center gap-1.5">
                      
                      NISN: {{ item.nisn || '-' }}
                    </p>
                  </div>
                  <div v-else class="space-y-0.5">
                    <p class="text-xs flex items-center gap-1.5">
                     
                      NIP: {{ item.nip || '-' }}
                    </p>
                    <p class="text-xs flex items-center gap-1.5">
                    
                      {{ item.mata_pelajaran || '-' }}
                    </p>
                  </div>
                </td>

                <!-- Aksi -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <router-link
                      :to="`/users/${item.id}/edit`"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Edit
                    </router-link>
                    <button
                      @click="handleDelete(item.id, item.name)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-100">
            <p class="text-xs text-gray-400">
              Menampilkan {{ users.length }} user
              <span v-if="hasActiveFilter"> (difilter)</span>
            </p>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>