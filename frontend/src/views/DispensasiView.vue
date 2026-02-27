<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'
import { formatDateIndo } from '@/utils/dateFormatter'

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

  if (filterStatus.value !== 'all') {
    filtered = filtered.filter(d => d.status === filterStatus.value)
  }

  if (filterKelas.value) {
    filtered = filtered.filter(d => d.kelas_id == filterKelas.value)
  }

  if (filterTanggalMulai.value) {
    filtered = filtered.filter(d => d.tanggal >= filterTanggalMulai.value)
  }
  if (filterTanggalSelesai.value) {
    filtered = filtered.filter(d => d.tanggal <= filterTanggalSelesai.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(d => 
      d.mata_pelajaran.toLowerCase().includes(query) ||
      d.keperluan.toLowerCase().includes(query) ||
      d.siswa?.name.toLowerCase().includes(query)
    )
  }

  return filtered
})

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'bg-warning-100 text-warning-800 border-warning-300',
    approved: 'bg-success-100 text-success-800 border-success-300',
    rejected: 'bg-danger-100 text-danger-800 border-danger-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300'
}

function getStatusText(status) {
  const texts = {
    pending: 'Menunggu',
    approved: 'Disetujui',
    rejected: 'Ditolak'
  }
  return texts[status] || status
}

async function handleDelete(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus dispensasi ini?')) {
    return
  }

  try {
    await api.delete(`/dispensasi/${id}`)
    alert('Dispensasi berhasil dihapus')
    fetchDispensasi()
  } catch (error) {
    console.error('Error deleting dispensasi:', error)
    alert(error.response?.data?.message || 'Gagal menghapus dispensasi')
  }
}

async function handleExportExcel() {
  exporting.value = true
  try {
    const params = {
      status: filterStatus.value,
      kelas_id: filterKelas.value,
      tanggal_mulai: filterTanggalMulai.value,
      tanggal_selesai: filterTanggalSelesai.value,
    }

    const response = await api.get('/export/excel', {
      params,
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `Dispensasi_${new Date().toISOString().split('T')[0]}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error exporting Excel:', error)
    alert('Gagal export ke Excel')
  } finally {
    exporting.value = false
  }
}

async function handleExportCSV() {
  exporting.value = true
  try {
    const params = {
      status: filterStatus.value,
      kelas_id: filterKelas.value,
      tanggal_mulai: filterTanggalMulai.value,
      tanggal_selesai: filterTanggalSelesai.value,
    }

    const response = await api.get('/export/csv', {
      params,
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `Dispensasi_${new Date().toISOString().split('T')[0]}.csv`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error exporting CSV:', error)
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

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
              <span class="text-3xl mr-3">📋</span>
              Daftar Dispensasi
            </h1>
            <p class="text-gray-600 mt-1">Total: {{ filteredDispensasi.length }} dispensasi</p>
          </div>

          <div class="flex flex-wrap gap-2">
            <router-link
              v-if="user?.role === 'siswa'"
              to="/dispensasi/create"
              class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold transition shadow-sm"
            >
              <span class="text-xl mr-2">➕</span>
              Buat Dispensasi
            </router-link>
          </div>
        </div>
      </div>

      <!-- Export Buttons (Kesiswaan only) -->
      <div v-if="canExport" class="bg-white rounded-xl shadow-sm p-4 border border-gray-100 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h3 class="text-sm font-semibold text-gray-800 mb-1">📊 Export Data</h3>
            <p class="text-xs text-gray-600">Download data dispensasi sesuai filter</p>
          </div>
          <div class="flex gap-2">
            <button
              @click="handleExportExcel"
              :disabled="exporting"
              class="inline-flex items-center bg-success-500 hover:bg-success-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition disabled:bg-gray-400"
            >
              <span class="mr-2">📊</span>
              {{ exporting ? 'Exporting...' : 'Export Excel' }}
            </button>
            <button
              @click="handleExportCSV"
              :disabled="exporting"
              class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition disabled:bg-gray-400"
            >
              <span class="mr-2">📄</span>
              {{ exporting ? 'Exporting...' : 'Export CSV' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-800">🔍 Filter & Pencarian</h3>
          <button
            @click="resetFilters"
            class="text-sm text-primary-600 hover:text-primary-800 font-semibold"
          >
            Reset Filter
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Pencarian</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari mata pelajaran, keperluan, nama..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
            <select
              v-model="filterStatus"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="all">Semua Status</option>
              <option value="pending">Menunggu</option>
              <option value="approved">Disetujui</option>
              <option value="rejected">Ditolak</option>
            </select>
          </div>

          <div v-if="user?.role !== 'siswa'">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas</label>
            <select
              v-model="filterKelas"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">Semua Kelas</option>
              <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                {{ kelas.nama_kelas }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
            <input
              v-model="filterTanggalMulai"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai</label>
            <input
              v-model="filterTanggalSelesai"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div v-if="loading" class="p-12 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
          <p class="text-gray-500 mt-4">Memuat data...</p>
        </div>

        <div v-else-if="filteredDispensasi.length === 0" class="p-12 text-center">
          <div class="text-6xl mb-4">📭</div>
          <p class="text-gray-500 text-lg font-medium">Tidak ada data dispensasi</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                <th v-if="user?.role !== 'siswa'" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Siswa</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Jam Pelajaran</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Mata Pelajaran</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in filteredDispensasi" :key="item.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ index + 1 }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDateIndo(item.tanggal) }}
                </td>
                <td v-if="user?.role !== 'siswa'" class="px-6 py-4 whitespace-nowrap text-sm">
                  <div>
                    <p class="font-medium text-gray-900">{{ item.siswa?.name }}</p>
                    <p class="text-xs text-gray-500">{{ item.kelas?.nama_kelas }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ getJamPelajaranText(item.jam_pelajaran_mulai, item.jam_pelajaran_selesai) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">{{ item.mata_pelajaran }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(item.status)" class="px-3 py-1 text-xs font-semibold rounded-full border">
                    {{ getStatusText(item.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                  <router-link
                    :to="`/dispensasi/${item.id}`"
                    class="text-primary-600 hover:text-primary-800 font-semibold"
                  >
                    Detail
                  </router-link>
                  
                  <button
                    v-if="user?.role === 'siswa' && item.status === 'pending'"
                    @click="handleDelete(item.id)"
                    class="text-danger-600 hover:text-danger-800 font-semibold"
                  >
                    Hapus
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