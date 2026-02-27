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

const loading = ref(true)
const selectedYear = ref(new Date().getFullYear())

const overview = ref({})
const dispensasiByMonth = ref({})
const dispensasiByKelas = ref([])
const topSiswa = ref([])
const dispensasiByMapel = ref([])
const approvalRate = ref({})

// Redirect if not admin/kesiswaan
if (!authStore.canApprove) {
  router.push('/dashboard')
}

async function fetchAnalytics() {
  loading.value = true
  try {
    const [
      overviewRes,
      byMonthRes,
      byKelasRes,
      topSiswaRes,
      byMapelRes,
      approvalRateRes
    ] = await Promise.all([
      api.get('/analytics/overview'),
      api.get('/analytics/dispensasi-by-month', { params: { year: selectedYear.value } }),
      api.get('/analytics/dispensasi-by-kelas'),
      api.get('/analytics/top-siswa', { params: { limit: 10 } }),
      api.get('/analytics/dispensasi-by-mapel', { params: { limit: 5 } }),
      api.get('/analytics/approval-rate')
    ])

    overview.value = overviewRes.data
    dispensasiByMonth.value = byMonthRes.data
    dispensasiByKelas.value = byKelasRes.data.data
    topSiswa.value = topSiswaRes.data.data
    dispensasiByMapel.value = byMapelRes.data.data
    approvalRate.value = approvalRateRes.data
  } catch (error) {
    console.error('Error fetching analytics:', error)
  } finally {
    loading.value = false
  }
}

async function changeYear() {
  const res = await api.get('/analytics/dispensasi-by-month', { 
    params: { year: selectedYear.value } 
  })
  dispensasiByMonth.value = res.data
}

onMounted(() => {
  fetchAnalytics()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
          <span class="text-3xl mr-3">📊</span>
          Analytics & Statistik
        </h1>
        <p class="text-gray-600 mt-1">Dashboard analitik sistem dispensasi</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      </div>

      <!-- Content -->
      <div v-else class="space-y-6">
        <!-- Overview Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <p class="text-gray-500 text-xs sm:text-sm">Total Dispensasi</p>
              <span class="text-2xl">📋</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-gray-800">{{ overview.total_dispensasi || 0 }}</p>
          </div>

          <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <p class="text-gray-500 text-xs sm:text-sm">Menunggu</p>
              <span class="text-2xl">⏳</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-warning-600">{{ overview.pending || 0 }}</p>
          </div>

          <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <p class="text-gray-500 text-xs sm:text-sm">Disetujui</p>
              <span class="text-2xl">✅</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-success-600">{{ overview.approved || 0 }}</p>
          </div>

          <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <p class="text-gray-500 text-xs sm:text-sm">Ditolak</p>
              <span class="text-2xl">❌</span>
            </div>
            <p class="text-2xl sm:text-3xl font-bold text-danger-600">{{ overview.rejected || 0 }}</p>
          </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Line Chart: Dispensasi per Bulan -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-800">📈 Dispensasi per Bulan</h3>
              <select 
                v-model="selectedYear" 
                @change="changeYear"
                class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
              >
                <option :value="new Date().getFullYear() - 1">{{ new Date().getFullYear() - 1 }}</option>
                <option :value="new Date().getFullYear()">{{ new Date().getFullYear() }}</option>
              </select>
            </div>
            <LineChart :data="dispensasiByMonth" />
          </div>

          <!-- Doughnut Chart: Approval Rate -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">🎯 Tingkat Persetujuan</h3>
            <DoughnutChart :data="approvalRate" />
            <div class="mt-4 grid grid-cols-2 gap-4 text-center">
              <div class="bg-success-50 rounded-lg p-3">
                <p class="text-xs text-success-700">Approval Rate</p>
                <p class="text-2xl font-bold text-success-700">{{ approvalRate.approval_rate || 0 }}%</p>
              </div>
              <div class="bg-danger-50 rounded-lg p-3">
                <p class="text-xs text-danger-700">Rejection Rate</p>
                <p class="text-2xl font-bold text-danger-700">{{ approvalRate.rejection_rate || 0 }}%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bar Chart: Dispensasi per Kelas -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">📚 Dispensasi per Kelas</h3>
          <BarChart :data="dispensasiByKelas" />
        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Siswa -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800">🏆 Top 10 Siswa</h3>
              <p class="text-sm text-gray-600">Siswa dengan dispensasi terbanyak</p>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Total</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="(item, index) in topSiswa" :key="index" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="text-2xl">{{ index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `${index + 1}.` }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                      <p class="font-medium text-gray-900">{{ item.nama }}</p>
                      <p class="text-xs text-gray-500">{{ item.nisn }}</p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ item.kelas }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-3 py-1 text-sm font-bold text-primary-700 bg-primary-100 rounded-full">
                        {{ item.total }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Top Mata Pelajaran -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <h3 class="text-lg font-semibold text-gray-800">📖 Mata Pelajaran Populer</h3>
              <p class="text-sm text-gray-600">Mapel yang paling sering ditinggalkan</p>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div v-for="(item, index) in dispensasiByMapel" :key="index" class="flex items-center justify-between">
                  <div class="flex-1">
                    <p class="font-medium text-gray-800">{{ item.mata_pelajaran }}</p>
                    <div class="mt-1 bg-gray-200 rounded-full h-2">
                      <div 
                        class="bg-primary-500 h-2 rounded-full" 
                        :style="{ width: `${(item.total / dispensasiByMapel[0]?.total * 100) || 0}%` }"
                      ></div>
                    </div>
                  </div>
                  <span class="ml-4 text-lg font-bold text-primary-700">{{ item.total }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>