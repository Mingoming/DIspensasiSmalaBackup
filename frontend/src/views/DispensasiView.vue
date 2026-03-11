<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const dispensasiList = ref([])
const kelasList = ref([])
const loading = ref(true)
const filterStatus = ref('all')
const filterKelas = ref('')
const filterTanggalMulai = ref('')
const filterTanggalSelesai = ref('')
const searchQuery = ref('')
const exporting = ref(false)

const user = computed(() => authStore.user)
const canExport = computed(() => authStore.hasRole && authStore.hasRole('kesiswaan'))

function formatDateIndo(date) {
  if (!date) return '-'
  const d = new Date(date)
  if (isNaN(d.getTime())) return '-'
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

async function fetchKelas() {
  try {
    const response = await api.get('/kelas')
    kelasList.value = response.data.data
  } catch (error) {
    console.error('Error fetching kelas:', error)
  }
}

async function fetchDispensasi() {
  loading.value = true
  try {
    const response = await api.get('/dispensasi')
    dispensasiList.value = response.data.data
  } catch (error) {
    console.error('Error fetching dispensasi:', error)
  } finally {
    loading.value = false
  }
}

const filteredDispensasi = computed(() => {
  let filtered = dispensasiList.value
  if (filterStatus.value !== 'all') filtered = filtered.filter(d => d.status === filterStatus.value)
  if (filterKelas.value) filtered = filtered.filter(d => d.kelas_id == filterKelas.value)
  if (filterTanggalMulai.value) filtered = filtered.filter(d => d.tanggal >= filterTanggalMulai.value)
  if (filterTanggalSelesai.value) filtered = filtered.filter(d => d.tanggal <= filterTanggalSelesai.value)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(d =>
      d.mata_pelajaran?.toLowerCase().includes(query) ||
      d.keperluan?.toLowerCase().includes(query) ||
      d.siswa?.name?.toLowerCase().includes(query)
    )
  }
  return filtered
})

const hasActiveFilter = computed(() =>
  filterStatus.value !== 'all' ||
  filterKelas.value !== '' ||
  filterTanggalMulai.value !== '' ||
  filterTanggalSelesai.value !== '' ||
  searchQuery.value !== ''
)

function getStatusBadgeClass(status) {
  const classes = {
    pending:  'bg-amber-100 text-amber-800 border-amber-300',
    approved: 'bg-green-100 text-green-800 border-green-300',
    rejected: 'bg-red-100 text-red-800 border-red-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300'
}

function getStatusText(status) {
  const texts = { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' }
  return texts[status] || status
}

async function handleDelete(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus dispensasi ini?')) return
  try {
    await api.delete(`/dispensasi/${id}`)
    alert('Dispensasi berhasil dihapus')
    fetchDispensasi()
  } catch (error) {
    alert(error.response?.data?.message || 'Gagal menghapus dispensasi')
  }
}

async function handleExportExcel() {
  exporting.value = true
  try {
    const params = { status: filterStatus.value, kelas_id: filterKelas.value, tanggal_mulai: filterTanggalMulai.value, tanggal_selesai: filterTanggalSelesai.value }
    const response = await api.get('/export/excel', { params, responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `Dispensasi_${new Date().toISOString().split('T')[0]}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    alert('Gagal export ke Excel')
  } finally {
    exporting.value = false
  }
}

async function handleExportCSV() {
  exporting.value = true
  try {
    const params = { status: filterStatus.value, kelas_id: filterKelas.value, tanggal_mulai: filterTanggalMulai.value, tanggal_selesai: filterTanggalSelesai.value }
    const response = await api.get('/export/csv', { params, responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `Dispensasi_${new Date().toISOString().split('T')[0]}.csv`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    alert('Gagal export ke CSV')
  } finally {
    exporting.value = false
  }
}

function resetFilters() {
  filterStatus.value = 'all'
  filterKelas.value = ''
  filterTanggalMulai.value = ''
  filterTanggalSelesai.value = ''
  searchQuery.value = ''
}

function getJamPelajaranText(jamMulai, jamSelesai) {
  return `Jam ${jamMulai} - ${jamSelesai}`
}

onMounted(() => {
  fetchKelas()
  fetchDispensasi()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">

      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <!-- Clipboard list icon -->
            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Daftar Dispensasi
          </h1>
          <p class="text-sm text-gray-500 mt-0.5">
            Menampilkan
            <span class="font-semibold text-gray-700">{{ filteredDispensasi.length }}</span>
            dari
            <span class="font-semibold text-gray-700">{{ dispensasiList.length }}</span>
            data
          </p>
        </div>

        <router-link
          v-if="user?.role === 'siswa'"
          to="/dispensasi/create"
          class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm hover:shadow-md self-start sm:self-auto"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Buat Dispensasi
        </router-link>
      </div>

      <!-- Export Bar -->
      <div v-if="canExport" class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800">Export Data</p>
            <p class="text-xs text-gray-500">Download data dispensasi sesuai filter aktif</p>
          </div>
        </div>
        <div class="flex gap-2 flex-shrink-0">
          <button
            @click="handleExportExcel"
            :disabled="exporting"
            class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ exporting ? 'Exporting...' : 'Excel' }}
          </button>
          <button
            @click="handleExportCSV"
            :disabled="exporting"
            class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ exporting ? 'Exporting...' : 'CSV' }}
          </button>
        </div>
      </div>

      <!-- Filter & Pencarian -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <!-- Filter icon -->
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
            Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">

          <!-- Pencarian -->
          <div class="lg:col-span-1">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Pencarian</label>
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari nama, mata pelajaran..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
              />
            </div>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Status</label>
            <select
              v-model="filterStatus"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white"
            >
              <option value="all">Semua Status</option>
              <option value="pending">Menunggu</option>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>

          <!-- Kelas -->
          <div v-if="user?.role !== 'siswa'">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Kelas</label>
            <select
              v-model="filterKelas"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white"
            >
              <option value="">Semua Kelas</option>
              <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">{{ kelas.nama_kelas }}</option>
            </select>
          </div>

          <!-- Tanggal Mulai -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Tanggal Mulai</label>
            <input
              v-model="filterTanggalMulai"
              type="date"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
            />
          </div>

          <!-- Tanggal Selesai -->
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Tanggal Selesai</label>
            <input
              v-model="filterTanggalSelesai"
              type="date"
              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
            />
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
        <div v-else-if="filteredDispensasi.length === 0" class="py-16 text-center">
          <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <p class="text-gray-600 font-medium">Tidak ada data dispensasi</p>
          <p class="text-gray-400 text-sm mt-1">
            {{ hasActiveFilter ? 'Coba ubah atau reset filter' : 'Belum ada dispensasi yang diajukan' }}
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
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th v-if="user?.role !== 'siswa'" class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jam Pelajaran</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="(item, index) in filteredDispensasi"
                :key="item.id"
                class="hover:bg-gray-50/60 transition-colors"
              >
                <td class="px-5 py-4 text-sm text-gray-400 font-medium">{{ index + 1 }}</td>
                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDateIndo(item.tanggal) }}</td>
                <td v-if="user?.role !== 'siswa'" class="px-5 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-gray-900">{{ item.siswa?.name }}</p>
                  <p class="text-xs text-gray-400">{{ item.kelas?.nama_kelas }}</p>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ getJamPelajaranText(item.jam_pelajaran_mulai, item.jam_pelajaran_selesai) }}
                </td>
                <td class="px-5 py-4 text-sm text-gray-700">{{ item.mata_pelajaran }}</td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(item.status)" class="px-2.5 py-1 text-xs font-semibold rounded-full border">
                    {{ getStatusText(item.status) }}
                  </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <router-link
                      :to="`/dispensasi/${item.id}`"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition"
                    >
                      Detail
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </router-link>
                    <button
                      v-if="user?.role === 'siswa' && item.status === 'pending'"
                      @click="handleDelete(item.id)"
                      class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              Menampilkan {{ filteredDispensasi.length }} data
              <span v-if="hasActiveFilter"> (difilter dari {{ dispensasiList.length }} total)</span>
            </p>
          </div>
        </div>

      </div>
    </main>
  </div>
</template>