<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const backups = ref([])
const loading = ref(true)
const creating = ref(false)
const totalSize = ref('')

// Redirect if not admin
if (!authStore.isAdmin) {
  router.push('/dashboard')
}

async function fetchBackups() {
  loading.value = true
  try {
    const response = await api.get('/backups')
    backups.value = response.data.data
    totalSize.value = response.data.total_size
  } catch (error) {
    console.error('Error fetching backups:', error)
  } finally {
    loading.value = false
  }
}

async function createBackup() {
  if (!confirm('Apakah Anda yakin ingin membuat backup baru? Proses ini mungkin memakan waktu beberapa menit.')) {
    return
  }

  creating.value = true
  try {
    await api.post('/backups')
    alert('Backup berhasil dibuat!')
    fetchBackups()
  } catch (error) {
    console.error('Error creating backup:', error)
    alert('Gagal membuat backup: ' + (error.response?.data?.message || 'Unknown error'))
  } finally {
    creating.value = false
  }
}

async function downloadBackup(filename) {
  try {
    const response = await api.get(`/backups/${filename}/download`, {
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error downloading backup:', error)
    alert('Gagal download backup')
  }
}

async function deleteBackup(filename) {
  if (!confirm(`Apakah Anda yakin ingin menghapus backup "${filename}"? Tindakan ini tidak dapat dibatalkan.`)) {
    return
  }

  try {
    await api.delete(`/backups/${filename}`)
    alert('Backup berhasil dihapus')
    fetchBackups()
  } catch (error) {
    console.error('Error deleting backup:', error)
    alert('Gagal menghapus backup')
  }
}

onMounted(() => {
  fetchBackups()
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
              <span class="text-3xl mr-3">💾</span>
              Backup & Recovery
            </h1>
            <p class="text-gray-600 mt-1">Kelola backup database sistem</p>
          </div>

          <button
            @click="createBackup"
            :disabled="creating"
            class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold transition shadow-sm disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            <span v-if="creating">Creating...</span>
            <span v-else>➕ Buat Backup Baru</span>
          </button>
        </div>
      </div>

      <!-- Info Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <p class="text-gray-500 text-sm">Total Backups</p>
            <span class="text-2xl">📦</span>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-800">{{ backups.length }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <p class="text-gray-500 text-sm">Total Size</p>
            <span class="text-2xl">💽</span>
          </div>
          <p class="text-2xl sm:text-3xl font-bold text-gray-800">{{ totalSize }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <p class="text-gray-500 text-sm">Auto Backup</p>
            <span class="text-2xl">⏰</span>
          </div>
          <p class="text-sm font-semibold text-success-600">Setiap 6 Bulan</p>
          <p class="text-xs text-gray-500 mt-1">1 Jan & 1 Jul jam 02:00</p>
        </div>
      </div>

      <!-- Info Alert -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
          <span class="text-2xl mr-3">ℹ️</span>
          <div>
            <p class="text-sm font-semibold text-blue-800 mb-2">Informasi Backup</p>
            <ul class="text-xs text-blue-700 space-y-1 list-disc ml-5">
              <li>Backup otomatis berjalan setiap 6 bulan (1 Januari & 1 Juli)</li>
              <li>Backup mingguan sebagai safety net setiap hari Minggu</li>
              <li>Backup lama akan otomatis dihapus sesuai retention policy</li>
              <li>Lokasi backup: <code class="bg-blue-100 px-1 py-0.5 rounded">storage/app/backups</code></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Backups List -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-800">📋 Daftar Backup</h2>
        </div>

        <div v-if="loading" class="p-12 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
          <p class="text-gray-500 mt-4">Memuat data...</p>
        </div>

        <div v-else-if="backups.length === 0" class="p-12 text-center">
          <div class="text-6xl mb-4">📭</div>
          <p class="text-gray-500 text-lg font-medium">Belum ada backup</p>
          <p class="text-gray-400 text-sm mt-2">Klik tombol "Buat Backup Baru" untuk membuat backup pertama</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama File</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Size</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(backup, index) in backups" :key="backup.name" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ index + 1 }}
                </td>
                <td class="px-6 py-4 text-sm">
                  <div class="flex items-center">
                    <span class="text-xl mr-2">📦</span>
                    <div>
                      <p class="font-medium text-gray-900">{{ backup.name }}</p>
                      <p class="text-xs text-gray-500">{{ backup.path }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ backup.size_human }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ backup.created_at_human }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                  <button
                    @click="downloadBackup(backup.name)"
                    class="text-primary-600 hover:text-primary-800 font-semibold"
                  >
                    ⬇️ Download
                  </button>
                  <button
                    @click="deleteBackup(backup.name)"
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

      <!-- Retention Policy Info -->
      <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start">
          <span class="text-2xl mr-3">⚠️</span>
          <div>
            <p class="text-sm font-semibold text-yellow-800 mb-2">Retention Policy</p>
            <ul class="text-xs text-yellow-700 space-y-1 list-disc ml-5">
              <li>Semua backup disimpan selama 7 hari</li>
              <li>Backup harian disimpan selama 30 hari</li>
              <li>Backup mingguan disimpan selama 8 minggu</li>
              <li>Backup bulanan disimpan selama 6 bulan</li>
              <li>Backup tahunan disimpan selama 2 tahun</li>
            </ul>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>