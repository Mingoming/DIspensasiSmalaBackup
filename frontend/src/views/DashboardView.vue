<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDispensasi } from '@/composables/useDispensasi'
import { getStatusBadgeClass, getStatusText } from '@/utils/status'
import { formatDateFull, formatDateShort } from '@/utils/date'
import AppNavbar from '@/components/AppNavbar.vue'

const router = useRouter()
const authStore = useAuthStore()

// Use composable
const { dispensasiList, loading, fetchDispensasi } = useDispensasi()

const stats = ref({
  total: 0,
  pending: 0,
  approved: 0,
  rejected: 0
})

const user = computed(() => authStore.user)
const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 11) return 'Selamat Pagi'
  if (hour < 15) return 'Selamat Siang'
  if (hour < 18) return 'Selamat Sore'
  return 'Selamat Malam'
})

onMounted(async () => {
  if (!authStore.user) {
    await authStore.fetchProfile()
  }
  await fetchDispensasi()
  
  // Calculate stats
  stats.value.total = dispensasiList.value.length
  stats.value.pending = dispensasiList.value.filter(d => d.status === 'pending').length
  stats.value.approved = dispensasiList.value.filter(d => d.status === 'approved').length
  stats.value.rejected = dispensasiList.value.filter(d => d.status === 'rejected').length
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Welcome Hero -->
      <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl shadow-soft p-6 sm:p-8 mb-6 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm opacity-90 mb-1">{{ greeting }},</p>
            <h1 class="text-2xl sm:text-3xl font-bold mb-2">{{ user?.name }}! 👋</h1>
            
            <!-- User Info -->
            <div v-if="user?.role === 'siswa'" class="flex flex-wrap gap-x-4 gap-y-1 text-sm opacity-90">
              <span>📚 Kelas {{ user?.kelas?.nama_kelas || '-' }}</span>
              <span>🆔 NISN: {{ user?.nisn }}</span>
            </div>
            <div v-else class="space-y-1 text-sm opacity-90">
              <p>🆔 NIP: {{ user?.nip }}</p>
              <p v-if="user?.mata_pelajaran">📖 {{ user?.mata_pelajaran }}</p>
              <div class="flex flex-wrap gap-2 mt-2">
                <span 
                  v-for="role in user?.roles" 
                  :key="role.id"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/20 backdrop-blur"
                >
                  {{ role.display_name }}
                </span>
              </div>
            </div>
          </div>
          
          <div class="mt-4 sm:mt-0">
            <div class="text-right text-sm opacity-90">
              {{ formatDateFull(new Date()) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total -->
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
          <div class="flex items-center justify-between mb-3">
            <div class="bg-primary-100 p-3 rounded-lg">
              <span class="text-2xl">📋</span>
            </div>
          </div>
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Total Dispensasi</p>
          <p class="text-2xl sm:text-3xl font-bold text-gray-800">{{ stats.total }}</p>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
          <div class="flex items-center justify-between mb-3">
            <div class="bg-warning-100 p-3 rounded-lg">
              <span class="text-2xl">⏳</span>
            </div>
          </div>
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Menunggu</p>
          <p class="text-2xl sm:text-3xl font-bold text-warning-600">{{ stats.pending }}</p>
        </div>

        <!-- Approved -->
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
          <div class="flex items-center justify-between mb-3">
            <div class="bg-success-100 p-3 rounded-lg">
              <span class="text-2xl">✅</span>
            </div>
          </div>
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Disetujui</p>
          <p class="text-2xl sm:text-3xl font-bold text-success-600">{{ stats.approved }}</p>
        </div>

        <!-- Rejected -->
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
          <div class="flex items-center justify-between mb-3">
            <div class="bg-danger-100 p-3 rounded-lg">
              <span class="text-2xl">❌</span>
            </div>
          </div>
          <p class="text-gray-500 text-xs sm:text-sm mb-1">Ditolak</p>
          <p class="text-2xl sm:text-3xl font-bold text-danger-600">{{ stats.rejected }}</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <span class="text-xl mr-2">⚡</span>
          Aksi Cepat
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <router-link
            v-if="user?.role === 'siswa'"
            to="/dispensasi/create"
            class="flex items-center justify-center space-x-2 bg-primary-500 hover:bg-primary-600 text-white px-6 py-4 rounded-lg font-semibold transition shadow-sm hover:shadow-md"
          >
            <span class="text-xl">➕</span>
            <span>Ajukan Dispensasi</span>
          </router-link>
          
          <router-link
            to="/dispensasi"
            class="flex items-center justify-center space-x-2 bg-success-500 hover:bg-success-600 text-white px-6 py-4 rounded-lg font-semibold transition shadow-sm hover:shadow-md"
          >
            <span class="text-xl">📄</span>
            <span>Lihat Semua</span>
          </router-link>
        </div>
      </div>

      <!-- Recent Dispensasi Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            <span class="text-xl mr-2">📊</span>
            Dispensasi Terbaru
          </h2>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-12 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
          <p class="text-gray-500 mt-4">Memuat data...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="dispensasiList.length === 0" class="p-12 text-center">
          <div class="text-6xl mb-4">📭</div>
          <p class="text-gray-500 text-lg font-medium">Belum ada dispensasi</p>
          <p class="text-gray-400 text-sm mt-2">Data dispensasi akan muncul di sini</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Tanggal
                </th>
                <th v-if="user?.role !== 'siswa'" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Siswa
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Mata Pelajaran
                </th>
                <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Keperluan
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in dispensasiList.slice(0, 5)" :key="item.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDateShort(item.tanggal) }}
                </td>
                <td v-if="user?.role !== 'siswa'" class="px-6 py-4 whitespace-nowrap text-sm">
                  <div>
                    <p class="font-medium text-gray-900">{{ item.siswa?.name }}</p>
                    <p class="text-xs text-gray-500">{{ item.kelas?.nama_kelas }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.mata_pelajaran }}
                </td>
                <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                  {{ item.keperluan }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(item.status)" class="px-3 py-1 text-xs font-semibold rounded-full border">
                    {{ getStatusText(item.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <router-link
                    :to="`/dispensasi/${item.id}`"
                    class="text-primary-600 hover:text-primary-800 font-semibold hover:underline"
                  >
                    Detail →
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- View All Link -->
        <div v-if="dispensasiList.length > 5" class="p-4 bg-gray-50 border-t border-gray-100 text-center">
          <router-link
            to="/dispensasi"
            class="text-primary-600 hover:text-primary-800 font-semibold text-sm inline-flex items-center space-x-1"
          >
            <span>Lihat Semua Dispensasi</span>
            <span>→</span>
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>
