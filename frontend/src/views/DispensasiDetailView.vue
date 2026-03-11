<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const dispensasi = ref(null)
const loading = ref(true)
const error = ref('')

const approvalForm = ref({ status: '', catatan: '' })
const submittingApproval = ref(false)

const user = computed(() => authStore.user)
const canApprove = computed(() =>
  authStore.hasRole && authStore.hasRole('kesiswaan') && dispensasi.value?.status === 'pending'
)
const canEdit = computed(() =>
  user.value?.role === 'siswa' &&
  dispensasi.value?.user_id === user.value?.id &&
  dispensasi.value?.status === 'pending'
)
const canDelete = computed(() => canEdit.value)

function formatDate(date) {
  if (!date) return '-'
  const d = new Date(date)
  if (isNaN(d.getTime())) return '-'
  return d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
}
function formatDateTime(date) {
  if (!date) return '-'
  const d = new Date(date)
  if (isNaN(d.getTime())) return '-'
  return d.toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function fetchDispensasi() {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get(`/dispensasi/${route.params.id}`)
    dispensasi.value = response.data.data
  } catch (err) {
    error.value = 'Gagal memuat data dispensasi'
  } finally {
    loading.value = false
  }
}

async function handleApproval(status) {
  if (!confirm(`Apakah Anda yakin ingin ${status === 'approved' ? 'menyetujui' : 'menolak'} dispensasi ini?`)) return
  submittingApproval.value = true
  error.value = ''
  try {
    await api.put(`/dispensasi/${route.params.id}/status`, { status, catatan: approvalForm.value.catatan })
    alert(`Dispensasi berhasil ${status === 'approved' ? 'disetujui' : 'ditolak'}`)
    fetchDispensasi()
    approvalForm.value.catatan = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal mengubah status dispensasi'
  } finally {
    submittingApproval.value = false
  }
}

async function handleDelete() {
  if (!confirm('Apakah Anda yakin ingin menghapus dispensasi ini?')) return
  try {
    await api.delete(`/dispensasi/${route.params.id}`)
    alert('Dispensasi berhasil dihapus')
    router.push('/dashboard')
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal menghapus dispensasi'
  }
}

function getStatusBadgeClass(status) {
  const classes = {
    pending:  'bg-amber-100 text-amber-800 border-amber-300',
    approved: 'bg-green-100 text-green-800 border-green-300',
    rejected: 'bg-red-100 text-red-800 border-red-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300'
}

function getStatusText(status) {
  const texts = { pending: 'Menunggu Persetujuan', approved: 'Disetujui', rejected: 'Ditolak' }
  return texts[status] || status
}

onMounted(() => fetchDispensasi())
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">

      <!-- Back & Actions -->
      <div class="flex items-center justify-between">
        <router-link
          to="/dispensasi"
          class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800 font-semibold transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Kembali ke Daftar
        </router-link>

        <div v-if="dispensasi && !loading" class="flex gap-2 no-print">
          <router-link
            v-if="canEdit"
            :to="`/dispensasi/${dispensasi.id}/edit`"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </router-link>
          <button
            v-if="canDelete"
            @click="handleDelete"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Hapus
          </button>
        </div>
      </div>

      <!-- Error -->
      <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ error }}
      </div>

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mb-4"></div>
        <p class="text-sm text-gray-400">Memuat data...</p>
      </div>

      <div v-else-if="dispensasi" class="space-y-5">

        <!-- Status Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
              <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Detail Dispensasi</p>
              <h1 class="text-xl font-bold text-gray-800 mb-1">#{{ dispensasi.id }}</h1>
              <p class="text-sm text-gray-500">Diajukan pada {{ formatDateTime(dispensasi.created_at) }}</p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
              <!-- Status icon -->
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                :class="{
                  'bg-amber-100': dispensasi.status === 'pending',
                  'bg-green-100': dispensasi.status === 'approved',
                  'bg-red-100':   dispensasi.status === 'rejected'
                }"
              >
                <svg v-if="dispensasi.status === 'pending'" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else-if="dispensasi.status === 'approved'" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span :class="getStatusBadgeClass(dispensasi.status)" class="px-4 py-1.5 text-sm font-semibold rounded-full border">
                {{ getStatusText(dispensasi.status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Data Siswa -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 bg-blue-50/50 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h2 class="text-sm font-semibold text-gray-700">Data Siswa</h2>
          </div>
          <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Nama Siswa</p>
              <p class="text-sm font-semibold text-gray-800">{{ dispensasi.siswa?.name || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Kelas</p>
              <p class="text-sm font-semibold text-gray-800">{{ dispensasi.kelas?.nama_kelas || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">NISN</p>
              <p class="text-sm font-semibold text-gray-800">{{ dispensasi.siswa?.nisn || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">No. Telepon</p>
              <p class="text-sm font-semibold text-gray-800">{{ dispensasi.siswa?.no_telepon || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Detail Dispensasi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 bg-primary-50/50 flex items-center gap-2">
            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <h2 class="text-sm font-semibold text-gray-700">Detail Dispensasi</h2>
          </div>
          <div class="p-6 space-y-5">
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Tanggal</p>
              <p class="text-sm font-semibold text-gray-800">{{ formatDate(dispensasi.tanggal) }}</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <div>
                <p class="text-xs text-gray-400 font-medium mb-1">Jam Mulai</p>
                <p class="text-sm font-semibold text-gray-800">{{ dispensasi.jam_mulai || '-' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400 font-medium mb-1">Jam Selesai</p>
                <p class="text-sm font-semibold text-gray-800">{{ dispensasi.jam_selesai || '-' }}</p>
              </div>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Mata Pelajaran yang Ditinggalkan</p>
              <p class="text-sm font-semibold text-gray-800">{{ dispensasi.mata_pelajaran || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Keperluan / Alasan</p>
              <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed bg-gray-50 rounded-lg px-4 py-3">
                {{ dispensasi.keperluan || '-' }}
              </p>
            </div>
            <div v-if="dispensasi.surat_dispensasi">
              <p class="text-xs text-gray-400 font-medium mb-2">Surat Dispensasi</p>
              <a
                :href="`http://127.0.0.1:8000/storage/${dispensasi.surat_dispensasi}`"
                target="_blank"
                class="inline-flex items-center gap-2 bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                Lihat File
              </a>
            </div>
          </div>
        </div>

        <!-- Informasi Persetujuan / Penolakan -->
        <div
          v-if="dispensasi.status !== 'pending'"
          class="bg-white rounded-xl shadow-sm border overflow-hidden"
          :class="dispensasi.status === 'approved' ? 'border-green-200' : 'border-red-200'"
        >
          <div
            class="px-6 py-4 border-b flex items-center gap-2"
            :class="dispensasi.status === 'approved' ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100'"
          >
            <svg v-if="dispensasi.status === 'approved'" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="text-sm font-semibold text-gray-700">
              {{ dispensasi.status === 'approved' ? 'Informasi Persetujuan' : 'Informasi Penolakan' }}
            </h2>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Diproses oleh</p>
              <p class="text-sm font-semibold text-gray-800">{{ dispensasi.approver?.name || '-' }}</p>
            </div>
            <div v-if="dispensasi.catatan">
              <p class="text-xs text-gray-400 font-medium mb-1">Catatan</p>
              <p class="text-sm text-gray-700 bg-gray-50 rounded-lg px-4 py-3">{{ dispensasi.catatan }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-400 font-medium mb-1">Waktu Proses</p>
              <p class="text-sm text-gray-800">{{ formatDateTime(dispensasi.updated_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Approval Form -->
        <div v-if="canApprove" class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-amber-100 bg-amber-50 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <h2 class="text-sm font-semibold text-gray-700">Aksi Persetujuan</h2>
          </div>
          <div class="p-6">
            <p class="text-sm text-gray-500 mb-4">
              Anda memiliki hak untuk approve/reject sebagai
              <span class="font-semibold text-gray-700">{{ authStore.getRoleDisplayNames?.join(', ') }}</span>
            </p>
            <div class="mb-4">
              <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                Catatan <span class="text-gray-400 normal-case font-normal">(opsional)</span>
              </label>
              <textarea
                v-model="approvalForm.catatan"
                rows="3"
                placeholder="Tambahkan catatan jika diperlukan..."
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition resize-none"
              ></textarea>
            </div>
            <div class="flex gap-3">
              <button
                @click="handleApproval('approved')"
                :disabled="submittingApproval"
                class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 text-white py-2.5 rounded-lg text-sm font-semibold transition"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ submittingApproval ? 'Memproses...' : 'Setujui' }}
              </button>
              <button
                @click="handleApproval('rejected')"
                :disabled="submittingApproval"
                class="flex-1 inline-flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 disabled:bg-gray-300 text-white py-2.5 rounded-lg text-sm font-semibold transition"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ submittingApproval ? 'Memproses...' : 'Tolak' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="text-sm font-semibold text-gray-700">Timeline</h2>
          </div>
          <div class="p-6">
            <div class="relative">
              <!-- Garis vertikal -->
              <div class="absolute left-4 top-5 bottom-5 w-0.5 bg-gray-200"></div>

              <div class="space-y-6">
                <!-- Diajukan -->
                <div class="flex items-start gap-4 relative">
                  <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center z-10">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </div>
                  <div class="pt-0.5">
                    <p class="text-sm font-semibold text-gray-800">Dispensasi Diajukan</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ formatDateTime(dispensasi.created_at) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">oleh {{ dispensasi.siswa?.name }}</p>
                  </div>
                </div>

                <!-- Menunggu -->
                <div v-if="dispensasi.status === 'pending'" class="flex items-start gap-4 relative">
                  <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center z-10">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="pt-0.5">
                    <p class="text-sm font-semibold text-amber-700">Menunggu Persetujuan</p>
                    <p class="text-xs text-gray-400 mt-0.5">Belum diproses</p>
                  </div>
                </div>

                <!-- Disetujui / Ditolak -->
                <div v-if="dispensasi.status !== 'pending'" class="flex items-start gap-4 relative">
                  <div
                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center z-10"
                    :class="dispensasi.status === 'approved' ? 'bg-green-100' : 'bg-red-100'"
                  >
                    <svg v-if="dispensasi.status === 'approved'" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </div>
                  <div class="pt-0.5">
                    <p class="text-sm font-semibold text-gray-800">
                      {{ dispensasi.status === 'approved' ? 'Dispensasi Disetujui' : 'Dispensasi Ditolak' }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ formatDateTime(dispensasi.updated_at) }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">oleh {{ dispensasi.approver?.name }}</p>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>