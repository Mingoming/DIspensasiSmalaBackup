<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import LineChart from '@/components/LineChart.vue'
import BarChart from '@/components/BarChart.vue'
import DoughnutChart from '@/components/DoughnutChart.vue'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

const selectedYear = ref(new Date().getFullYear())

// Pisah loading state per section → UI muncul bertahap
const loadingOverview   = ref(true)
const loadingCharts     = ref(true)
const loadingBarChart   = ref(true)
const loadingTables     = ref(true)

const overview          = ref({})
const dispensasiByMonth = ref({})
const dispensasiByKelas = ref([])
const topSiswa          = ref([])
const dispensasiByMapel = ref([])
const approvalRate      = ref({})

if (!authStore.canApprove) {
  router.push('/dashboard')
}

// Step 1: overview dulu (paling cepat, tampil pertama)
async function fetchOverview() {
  loadingOverview.value = true
  try {
    const res = await api.get('/analytics/overview')
    overview.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingOverview.value = false
  }
}

// Step 2: charts utama (line + doughnut — paralel)
async function fetchCharts() {
  loadingCharts.value = true
  try {
    const [byMonthRes, approvalRateRes] = await Promise.all([
      api.get('/analytics/dispensasi-by-month', { params: { year: selectedYear.value } }),
      api.get('/analytics/approval-rate')
    ])
    dispensasiByMonth.value = byMonthRes.data
    approvalRate.value      = approvalRateRes.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingCharts.value = false
  }
}

// Step 3: bar chart per kelas
async function fetchBarChart() {
  loadingBarChart.value = true
  try {
    const res = await api.get('/analytics/dispensasi-by-kelas')
    dispensasiByKelas.value = res.data.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingBarChart.value = false
  }
}

// Step 4: tabel (paling berat, terakhir)
async function fetchTables() {
  loadingTables.value = true
  try {
    const [topSiswaRes, byMapelRes] = await Promise.all([
      api.get('/analytics/top-siswa', { params: { limit: 10 } }),
      api.get('/analytics/dispensasi-by-mapel', { params: { limit: 5 } })
    ])
    topSiswa.value          = topSiswaRes.data.data
    dispensasiByMapel.value = byMapelRes.data.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingTables.value = false
  }
}

async function changeYear() {
  loadingCharts.value = true
  try {
    const res = await api.get('/analytics/dispensasi-by-month', { params: { year: selectedYear.value } })
    dispensasiByMonth.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loadingCharts.value = false
  }
}

onMounted(() => {
  // Jalankan bertahap: overview → charts → bar → tabel
  fetchOverview()
  fetchCharts()
  fetchBarChart()
  fetchTables()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
          <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          Analytics & Statistik
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">Dashboard analitik sistem dispensasi</p>
      </div>

      <!-- ── Overview Stats ── muncul pertama -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Skeleton atau data -->
        <template v-if="loadingOverview">
          <div v-for="i in 4" :key="i" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 animate-pulse">
            <div class="h-3 bg-gray-200 rounded w-2/3 mb-3"></div>
            <div class="h-8 bg-gray-200 rounded w-1/2"></div>
          </div>
        </template>

        <template v-else>
          <!-- Total -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium mb-0.5">Total Dispensasi</p>
              <p class="text-2xl font-bold text-gray-800">{{ overview.total_dispensasi || 0 }}</p>
            </div>
          </div>

          <!-- Menunggu -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium mb-0.5">Menunggu</p>
              <p class="text-2xl font-bold text-amber-500">{{ overview.pending || 0 }}</p>
            </div>
          </div>

          <!-- Disetujui -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium mb-0.5">Disetujui</p>
              <p class="text-2xl font-bold text-green-600">{{ overview.approved || 0 }}</p>
            </div>
          </div>

          <!-- Ditolak -->
          <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-medium mb-0.5">Ditolak</p>
              <p class="text-2xl font-bold text-red-500">{{ overview.rejected || 0 }}</p>
            </div>
          </div>
        </template>

      </div>

      <!-- ── Charts Row: Line + Doughnut ── -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Line Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
              </svg>
              Dispensasi per Bulan
            </h3>
            <select
              v-model="selectedYear"
              @change="changeYear"
              class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-primary-400 outline-none transition bg-white"
            >
              <option :value="new Date().getFullYear() - 1">{{ new Date().getFullYear() - 1 }}</option>
              <option :value="new Date().getFullYear()">{{ new Date().getFullYear() }}</option>
            </select>
          </div>
          <div v-if="loadingCharts" class="h-48 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-400"></div>
          </div>
          <LineChart v-else :data="dispensasiByMonth" />
        </div>

        <!-- Doughnut Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
            Tingkat Persetujuan
          </h3>
          <div v-if="loadingCharts" class="h-48 flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-400"></div>
          </div>
          <template v-else>
            <DoughnutChart :data="approvalRate" />
            <div class="mt-4 grid grid-cols-2 gap-3 text-center">
              <div class="bg-green-50 rounded-xl p-3">
                <p class="text-xs text-green-600 font-medium">Approval Rate</p>
                <p class="text-2xl font-bold text-green-700">{{ approvalRate.approval_rate || 0 }}%</p>
              </div>
              <div class="bg-red-50 rounded-xl p-3">
                <p class="text-xs text-red-600 font-medium">Rejection Rate</p>
                <p class="text-2xl font-bold text-red-600">{{ approvalRate.rejection_rate || 0 }}%</p>
              </div>
            </div>
          </template>
        </div>

      </div>

      <!-- ── Bar Chart ── -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          Dispensasi per Kelas
        </h3>
        <div v-if="loadingBarChart" class="h-48 flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-400"></div>
        </div>
        <BarChart v-else :data="dispensasiByKelas" />
      </div>

      <!-- ── Tables Row ── -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top Siswa -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Top 10 Siswa</h3>
              <p class="text-xs text-gray-500">Siswa dengan dispensasi terbanyak</p>
            </div>
          </div>

          <!-- Skeleton tabel -->
          <div v-if="loadingTables" class="p-6 space-y-3">
            <div v-for="i in 5" :key="i" class="flex items-center gap-3 animate-pulse">
              <div class="w-8 h-8 bg-gray-200 rounded-full flex-shrink-0"></div>
              <div class="flex-1">
                <div class="h-3 bg-gray-200 rounded w-3/4 mb-1.5"></div>
                <div class="h-2 bg-gray-100 rounded w-1/2"></div>
              </div>
              <div class="w-10 h-6 bg-gray-200 rounded-full"></div>
            </div>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-gray-50/70">
                <tr>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">Rank</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(item, index) in topSiswa" :key="index" class="hover:bg-gray-50/60 transition-colors">
                  <td class="px-5 py-3.5">
                    <!-- Rank 1-3: warna berbeda; sisanya angka biasa -->
                    <div
                      class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                      :class="{
                        'bg-amber-100 text-amber-700': index === 0,
                        'bg-gray-200 text-gray-600':   index === 1,
                        'bg-orange-100 text-orange-600': index === 2,
                        'bg-gray-100 text-gray-500':   index > 2
                      }"
                    >
                      {{ index + 1 }}
                    </div>
                  </td>
                  <td class="px-5 py-3.5">
                    <p class="text-sm font-medium text-gray-900">{{ item.nama }}</p>
                    <p class="text-xs text-gray-400">{{ item.nisn }}</p>
                  </td>
                  <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">{{ item.kelas }}</td>
                  <td class="px-5 py-3.5 whitespace-nowrap">
                    <span class="px-2.5 py-1 text-xs font-bold text-primary-700 bg-primary-50 rounded-full">
                      {{ item.total }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mata Pelajaran Populer -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Mata Pelajaran Populer</h3>
              <p class="text-xs text-gray-500">Mapel yang paling sering ditinggalkan</p>
            </div>
          </div>

          <!-- Skeleton bar -->
          <div v-if="loadingTables" class="p-6 space-y-5">
            <div v-for="i in 5" :key="i" class="animate-pulse">
              <div class="flex justify-between mb-1.5">
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                <div class="h-3 bg-gray-200 rounded w-8"></div>
              </div>
              <div class="h-2 bg-gray-200 rounded-full" :style="{ width: `${100 - i * 12}%` }"></div>
            </div>
          </div>

          <div v-else class="p-6 space-y-4">
            <div
              v-for="(item, index) in dispensasiByMapel"
              :key="index"
              class="flex items-center gap-3"
            >
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1.5">
                  <p class="text-sm font-medium text-gray-800 truncate">{{ item.mata_pelajaran }}</p>
                  <span class="ml-3 text-sm font-bold text-primary-600 flex-shrink-0">{{ item.total }}</span>
                </div>
                <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                  <div
                    class="bg-primary-500 h-2 rounded-full transition-all duration-500"
                    :style="{ width: `${(item.total / (dispensasiByMapel[0]?.total || 1)) * 100}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </main>
  </div>
</template>