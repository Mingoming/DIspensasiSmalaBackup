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
  if (!confirm('Apakah Anda yakin ingin membuat backup baru? Proses ini mungkin memakan waktu beberapa menit.')) return
  creating.value = true
  try {
    await api.post('/backups')
    alert('Backup berhasil dibuat!')
    fetchBackups()
  } catch (error) {
    alert('Gagal membuat backup: ' + (error.response?.data?.message || 'Unknown error'))
  } finally {
    creating.value = false
  }
}

async function downloadBackup(filename) {
  try {
    const response = await api.get(`/backups/${filename}/download`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    alert('Gagal download backup')
  }
}

async function deleteBackup(filename) {
  if (!confirm(`Apakah Anda yakin ingin menghapus backup "${filename}"? Tindakan ini tidak dapat dibatalkan.`)) return
  try {
    await api.delete(`/backups/${filename}`)
    alert('Backup berhasil dihapus')
    fetchBackups()
  } catch (error) {
    alert('Gagal menghapus backup')
  }
}

onMounted(() => fetchBackups())
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <!-- Database icon -->
            <svg class="w-7 h-7 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 3.582 3 8 3s8-1 8-3V7M4 7c0 2 3.582 3 8 3s8-1 8-3M4 7c0-2 3.582-3 8-3s8 1 8 3M4 12c0 2 3.582 3 8 3s8-1 8-3" />
            </svg>
            Backup & Recovery
          </h1>
          <p class="text-sm text-gray-500 mt-0.5">Kelola backup database sistem</p>
        </div>

        <button
          @click="createBackup"
          :disabled="creating"
          class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm hover:shadow-md self-start sm:self-auto"
        >
          <svg v-if="!creating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <svg v-else class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          {{ creating ? 'Membuat Backup...' : 'Buat Backup Baru' }}
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Total Backups -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium mb-0.5">Total Backups</p>
            <p class="text-2xl font-bold text-gray-800">{{ backups.length }}</p>
          </div>
        </div>

        <!-- Total Size -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 3.582 3 8 3s8-1 8-3V7M4 7c0 2 3.582 3 8 3s8-1 8-3M4 7c0-2 3.582-3 8-3s8 1 8 3" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium mb-0.5">Total Size</p>
            <p class="text-2xl font-bold text-gray-800">{{ totalSize || '-' }}</p>
          </div>
        </div>

        <!-- Auto Backup -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium mb-0.5">Auto Backup</p>
            <p class="text-sm font-bold text-green-600">Setiap 6 Bulan</p>
            <p class="text-xs text-gray-400 mt-0.5">1 Jan & 1 Jul jam 02:00</p>
          </div>
        </div>

      </div>

      <!-- Info Backup -->
      <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-blue-800 mb-2">Informasi Backup</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1">
              <div v-for="info in [
                'Backup otomatis berjalan setiap 6 bulan (1 Januari & 1 Juli)',
                'Backup mingguan sebagai safety net setiap hari Minggu',
                'Backup lama akan otomatis dihapus sesuai retention policy',
              ]" :key="info" class="flex items-start gap-2 text-xs text-blue-700">
                <svg class="w-3.5 h-3.5 text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                {{ info }}
              </div>
              <div class="flex items-start gap-2 text-xs text-blue-700">
                <svg class="w-3.5 h-3.5 text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Lokasi: <code class="bg-blue-100 px-1 py-0.5 rounded font-mono ml-1">storage/app/backups</code>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Daftar Backup -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
          </svg>
          <h2 class="text-sm font-semibold text-gray-700">Daftar Backup</h2>
          <span class="ml-auto text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full font-medium">
            {{ backups.length }} file
          </span>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="py-16 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mb-4"></div>
          <p class="text-sm text-gray-400">Memuat data...</p>
        </div>

        <!-- Empty -->
        <div v-else-if="backups.length === 0" class="py-16 text-center">
          <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
          </div>
          <p class="text-gray-600 font-medium">Belum ada backup</p>
          <p class="text-gray-400 text-sm mt-1">Klik "Buat Backup Baru" untuk membuat backup pertama</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/70">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">No</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama File</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Size</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="(backup, index) in backups"
                :key="backup.name"
                class="hover:bg-gray-50/60 transition-colors"
              >
                <td class="px-5 py-4 text-sm text-gray-400 font-medium">{{ index + 1 }}</td>

                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0">
                      <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ backup.name }}</p>
                      <p class="text-xs text-gray-400 font-mono">{{ backup.path }}</p>
                    </div>
                  </div>
                </td>

                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium text-gray-700 bg-gray-100 px-2.5 py-1 rounded-lg">
                    {{ backup.size_human }}
                  </span>
                </td>

                <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ backup.created_at_human }}
                </td>

                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2">
                    <button
                      @click="downloadBackup(backup.name)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                      </svg>
                      Download
                    </button>
                    <button
                      @click="deleteBackup(backup.name)"
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
            <p class="text-xs text-gray-400">{{ backups.length }} file backup tersimpan</p>
          </div>
        </div>
      </div>

      <!-- Retention Policy -->
      <div class="bg-amber-50 border border-amber-100 rounded-xl p-5">
        <div class="flex items-start gap-3">
          <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-semibold text-amber-800 mb-3">Retention Policy</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
              <div v-for="policy in [
                { label: 'Semua backup', value: '7 hari' },
                { label: 'Backup harian', value: '30 hari' },
                { label: 'Backup mingguan', value: '8 minggu' },
                { label: 'Backup bulanan', value: '6 bulan' },
                { label: 'Backup tahunan', value: '2 tahun' },
              ]" :key="policy.label"
                class="flex items-center justify-between bg-amber-100/60 rounded-lg px-3 py-2"
              >
                <span class="text-xs text-amber-700">{{ policy.label }}</span>
                <span class="text-xs font-bold text-amber-800 ml-2">{{ policy.value }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>