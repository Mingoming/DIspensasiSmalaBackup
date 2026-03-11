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

const { dispensasiList, loading, fetchDispensasi } = useDispensasi()

const stats = ref({ total: 0, pending: 0, approved: 0, rejected: 0 })

const user = computed(() => authStore.user)

const greeting = computed(() => {
  const hour = new Date().getHours()
  if (hour < 11) return 'Selamat Pagi'
  if (hour < 15) return 'Selamat Siang'
  if (hour < 18) return 'Selamat Sore'
  return 'Selamat Malam'
})

// Menentukan ikon greeting berdasar waktu (SVG path berbeda)
const greetingIconType = computed(() => {
  const hour = new Date().getHours()
  if (hour < 11) return 'morning'   // matahari + awan
  if (hour < 15) return 'day'       // matahari penuh
  if (hour < 18) return 'evening'   // sunset
  return 'night'                    // bulan
})

onMounted(async () => {
  if (!authStore.user) await authStore.fetchProfile()
  await fetchDispensasi()
  stats.value.total    = dispensasiList.value.length
  stats.value.pending  = dispensasiList.value.filter(d => d.status === 'pending').length
  stats.value.approved = dispensasiList.value.filter(d => d.status === 'approved').length
  stats.value.rejected = dispensasiList.value.filter(d => d.status === 'rejected').length
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- Welcome Hero -->
      <div class="relative bg-gradient-to-br from-primary-600 via-primary-500 to-primary-400 rounded-2xl shadow-lg overflow-hidden">
        <!-- Dekorasi lingkaran -->
        <div class="absolute -top-10 -right-10 w-56 h-56 bg-white/10 rounded-full pointer-events-none"></div>
        <div class="absolute bottom-0 right-24 w-32 h-32 bg-white/10 rounded-full translate-y-1/2 pointer-events-none"></div>
        <div class="absolute top-4 right-48 w-16 h-16 bg-white/5 rounded-full pointer-events-none"></div>

        <div class="relative z-10 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <!-- Kiri -->
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
              <!-- Greeting icon -->
              <div class="w-5 h-5 text-white/80">
                <!-- Morning: cloud + sun -->
                <svg v-if="greetingIconType === 'morning'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                </svg>
                <!-- Day: sun -->
                <svg v-else-if="greetingIconType === 'day'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
                <!-- Evening: sunset -->
                <svg v-else-if="greetingIconType === 'evening'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8zM3 20h18" />
                </svg>
                <!-- Night: moon -->
                <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
              </div>
              <p class="text-sm text-white/80 font-medium">{{ greeting }},</p>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white mb-3">{{ user?.name }}!</h1>

            <!-- Siswa -->
            <div v-if="user?.role === 'siswa'" class="flex flex-wrap gap-2">
              <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full text-xs font-medium text-white">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Kelas {{ user?.kelas?.nama_kelas || '-' }}
              </span>
              <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full text-xs font-medium text-white">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" />
                </svg>
                NISN: {{ user?.nisn }}
              </span>
            </div>

            <!-- Guru / Staff -->
            <div v-else class="flex flex-wrap gap-2">
              <span class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full text-xs font-medium text-white">
                
                NIP: {{ user?.nip }}
              </span>
              
              <span
                v-for="role in user?.roles" :key="role.id"
                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-white/30 text-white"
              >{{ role.display_name }}</span>
            </div>
          </div>

          <!-- Kanan: Tanggal -->
          <div class="flex-shrink-0">
            <div class="bg-white/20 backdrop-blur-sm rounded-xl px-5 py-3 text-center sm:text-right">
              <p class="text-[10px] text-white/70 font-semibold uppercase tracking-widest mb-1">Hari Ini</p>
              <p class="text-sm font-bold text-white leading-snug">{{ formatDateFull(new Date()) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium mb-0.5">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ stats.total }}</p>
          </div>
        </div>

        <!-- Menunggu -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium mb-0.5">Menunggu</p>
            <p class="text-2xl font-bold text-amber-500">{{ stats.pending }}</p>
          </div>
        </div>

        <!-- Disetujui -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium mb-0.5">Disetujui</p>
            <p class="text-2xl font-bold text-green-600">{{ stats.approved }}</p>
          </div>
        </div>

        <!-- Ditolak -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-medium mb-0.5">Ditolak</p>
            <p class="text-2xl font-bold text-red-500">{{ stats.rejected }}</p>
          </div>
        </div>

      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          Aksi Cepat
        </h2>
        <div class="flex flex-wrap gap-3">
          <router-link
            v-if="user?.role === 'siswa'"
            to="/dispensasi/create"
            class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm hover:shadow-md"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajukan Dispensasi
          </router-link>

          <router-link
            to="/dispensasi"
            class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm hover:shadow-md"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Lihat Semua Dispensasi
          </router-link>
        </div>
      </div>

      <!-- Recent Dispensasi -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Dispensasi Terbaru
          </h2>
          <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">5 Terbaru</span>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="py-16 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mb-4"></div>
          <p class="text-sm text-gray-400">Memuat data...</p>
        </div>

        <!-- Empty -->
        <div v-else-if="dispensasiList.length === 0" class="py-16 text-center">
          <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <p class="text-gray-600 font-medium">Belum ada dispensasi</p>
          <p class="text-gray-400 text-sm mt-1">Data dispensasi akan muncul di sini</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/70">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                <th v-if="user?.role !== 'siswa'" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                <th class="hidden lg:table-cell px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keperluan</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="item in dispensasiList.slice(0, 5)"
                :key="item.id"
                class="hover:bg-gray-50/60 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ formatDateShort(item.tanggal) }}
                </td>
                <td v-if="user?.role !== 'siswa'" class="px-6 py-4 whitespace-nowrap">
                  <p class="text-sm font-medium text-gray-900">{{ item.siswa?.name }}</p>
                  <p class="text-xs text-gray-400">{{ item.kelas?.nama_kelas }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ item.mata_pelajaran }}</td>
                <td class="hidden lg:table-cell px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ item.keperluan }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(item.status)" class="px-2.5 py-1 text-xs font-semibold rounded-full border">
                    {{ getStatusText(item.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <router-link
                    :to="`/dispensasi/${item.id}`"
                    class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-800 transition-colors"
                  >
                    Detail
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- View All -->
        <div v-if="dispensasiList.length > 5" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 text-center">
          <router-link
            to="/dispensasi"
            class="text-sm font-semibold text-primary-600 hover:text-primary-800 inline-flex items-center gap-1 transition-colors"
          >
            Lihat Semua Dispensasi
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </router-link>
        </div>
      </div>

    </main>
  </div>
</template>