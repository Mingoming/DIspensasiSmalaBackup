<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const dispensasi = ref(null)
const loading = ref(true)
const error = ref('')

// Form untuk approval (guru/admin)
const approvalForm = ref({
  status: '',
  catatan: ''
})
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

async function fetchDispensasi() {
  loading.value = true
  error.value = ''
  
  try {
    const response = await api.get(`/dispensasi/${route.params.id}`)
    dispensasi.value = response.data.data
  } catch (err) {
    console.error('Error fetching dispensasi:', err)
    error.value = 'Gagal memuat data dispensasi'
  } finally {
    loading.value = false
  }
}

async function handleApproval(status) {
  if (!confirm(`Apakah Anda yakin ingin ${status === 'approved' ? 'menyetujui' : 'menolak'} dispensasi ini?`)) {
    return
  }

  submittingApproval.value = true
  error.value = ''

  try {
    await api.put(`/dispensasi/${route.params.id}/status`, {
      status: status,
      catatan: approvalForm.value.catatan
    })

    alert(`Dispensasi berhasil ${status === 'approved' ? 'disetujui' : 'ditolak'}`)
    fetchDispensasi()
    approvalForm.value.catatan = ''
  } catch (err) {
    console.error('Error updating status:', err)
    error.value = err.response?.data?.message || 'Gagal mengubah status dispensasi'
  } finally {
    submittingApproval.value = false
  }
}

async function handleDelete() {
  if (!confirm('Apakah Anda yakin ingin menghapus dispensasi ini?')) {
    return
  }

  try {
    await api.delete(`/dispensasi/${route.params.id}`)
    alert('Dispensasi berhasil dihapus')
    router.push('/dashboard')
  } catch (err) {
    console.error('Error deleting dispensasi:', err)
    error.value = err.response?.data?.message || 'Gagal menghapus dispensasi'
  }
}

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-300',
    approved: 'bg-green-100 text-green-800 border-green-300',
    rejected: 'bg-red-100 text-red-800 border-red-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300'
}

function getStatusText(status) {
  const texts = {
    pending: 'Menunggu Persetujuan',
    approved: 'Disetujui',
    rejected: 'Ditolak'
  }
  return texts[status] || status
}

function getStatusIcon(status) {
  const icons = {
    pending: '⏳',
    approved: '✅',
    rejected: '❌'
  }
  return icons[status] || '📋'
}

onMounted(() => {
  fetchDispensasi()
})

function printDispensasi() {
  window.print()
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <router-link to="/dashboard" class="text-xl font-bold text-gray-800">
              🎓 Sistem Dispensasi
            </router-link>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">{{ user?.name }}</span>
          </div>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Back Button -->
      <div class="mb-6 flex justify-between items-center">
        <router-link
          to="/dispensasi"
          class="inline-flex items-center text-blue-600 hover:text-blue-800"
        >
          ← Kembali ke Daftar
        </router-link>

        <!-- Action Buttons -->
        <div v-if="dispensasi && !loading" class="flex gap-2 no-print">
          <router-link
            v-if="canEdit"
            :to="`/dispensasi/${dispensasi.id}/edit`"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            ✏️ Edit
          </router-link>
          
          <button
            v-if="canDelete"
            @click="handleDelete"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            🗑️ Hapus
          </button>
        </div>
      </div>

      <!-- Error Alert -->
      <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <p>❌ {{ error }}</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-lg shadow-md p-12 text-center">
        <p class="text-gray-500">Loading...</p>
      </div>

      <!-- Detail Dispensasi -->
      <div v-else-if="dispensasi" class="space-y-6">
        <!-- Status Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-800 mb-2">
                Detail Dispensasi #{{ dispensasi.id }}
              </h1>
              <p class="text-gray-600 text-sm">
                Diajukan pada {{ new Date(dispensasi.created_at).toLocaleString('id-ID') }}
              </p>
            </div>
            <div class="text-center">
              <div class="text-4xl mb-2">{{ getStatusIcon(dispensasi.status) }}</div>
              <span :class="getStatusBadgeClass(dispensasi.status)" class="px-4 py-2 text-sm font-semibold rounded-full border">
                {{ getStatusText(dispensasi.status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Data Siswa -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">👤 Data Siswa</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-500">Nama Siswa</p>
              <p class="font-semibold text-gray-800">{{ dispensasi.siswa?.name }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Kelas</p>
              <p class="font-semibold text-gray-800">{{ dispensasi.kelas?.nama_kelas }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">NISN</p>
              <p class="font-semibold text-gray-800">{{ dispensasi.siswa?.nisn || '-' }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">No. Telepon</p>
              <p class="font-semibold text-gray-800">{{ dispensasi.siswa?.no_telepon || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Detail Dispensasi -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">📋 Detail Dispensasi</h2>
          <div class="space-y-4">
            <div>
              <p class="text-sm text-gray-500">Tanggal</p>
              <p class="font-semibold text-gray-800">
                {{ new Date(dispensasi.tanggal).toLocaleDateString('id-ID', { 
                  weekday: 'long', 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                }) }}
              </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Jam Mulai</p>
                <p class="font-semibold text-gray-800">{{ dispensasi.jam_mulai }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Jam Selesai</p>
                <p class="font-semibold text-gray-800">{{ dispensasi.jam_selesai }}</p>
              </div>
            </div>

            <div>
              <p class="text-sm text-gray-500">Mata Pelajaran yang Ditinggalkan</p>
              <p class="font-semibold text-gray-800">{{ dispensasi.mata_pelajaran }}</p>
            </div>

            <div>
              <p class="text-sm text-gray-500">Keperluan/Alasan</p>
              <p class="text-gray-800 whitespace-pre-line">{{ dispensasi.keperluan }}</p>
            </div>

            <div v-if="dispensasi.surat_dispensasi">
              <p class="text-sm text-gray-500 mb-2">Surat Dispensasi</p>
              <a 
                :href="`http://127.0.0.1:8000/storage/${dispensasi.surat_dispensasi}`"
                target="_blank"
                class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
              >
                📎 Lihat File
              </a>
            </div>
          </div>
        </div>

        <!-- Approval Info (jika sudah di-approve/reject) -->
        <div v-if="dispensasi.status !== 'pending'" class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">
            {{ dispensasi.status === 'approved' ? '✅ Informasi Persetujuan' : '❌ Informasi Penolakan' }}
          </h2>
          <div class="space-y-3">
            <div>
              <p class="text-sm text-gray-500">Diproses oleh</p>
              <p class="font-semibold text-gray-800">{{ dispensasi.approver?.name }}</p>
            </div>
            <div v-if="dispensasi.catatan">
              <p class="text-sm text-gray-500">Catatan</p>
              <p class="text-gray-800">{{ dispensasi.catatan }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Waktu Proses</p>
              <p class="text-gray-800">{{ new Date(dispensasi.updated_at).toLocaleString('id-ID') }}</p>
            </div>
          </div>
        </div>

        <!-- Approval Form (untuk guru/admin jika status pending) -->
        <div v-if="canApprove" class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">⚡ Aksi Persetujuan</h2>
          <p class="text-sm text-gray-600 mb-4">
            Anda memiliki hak untuk approve/reject karena memiliki role: 
            <span class="font-semibold">{{ authStore.getRoleDisplayNames.join(', ') }}</span>
          </p>

          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Catatan (Opsional)
            </label>
            <textarea
              v-model="approvalForm.catatan"
              rows="3"
              placeholder="Tambahkan catatan jika diperlukan..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            ></textarea>
          </div>

          <div class="flex gap-4">
            <button
              @click="handleApproval('approved')"
              :disabled="submittingApproval"
              class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              <span v-if="submittingApproval">Processing...</span>
              <span v-else>✅ Setujui</span>
            </button>

            <button
              @click="handleApproval('rejected')"
              :disabled="submittingApproval"
              class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-semibold transition disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              <span v-if="submittingApproval">Processing...</span>
              <span v-else>❌ Tolak</span>
            </button>
          </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">🕐 Timeline</h2>
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-lg">📝</span>
              </div>
              <div class="ml-4">
                <p class="font-semibold text-gray-800">Dispensasi Diajukan</p>
                <p class="text-sm text-gray-500">{{ new Date(dispensasi.created_at).toLocaleString('id-ID') }}</p>
              </div>
            </div>

            <div v-if="dispensasi.status !== 'pending'" class="flex items-start">
              <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" 
                   :class="dispensasi.status === 'approved' ? 'bg-green-100' : 'bg-red-100'">
                <span class="text-lg">{{ dispensasi.status === 'approved' ? '✅' : '❌' }}</span>
              </div>
              <div class="ml-4">
                <p class="font-semibold text-gray-800">
                  {{ dispensasi.status === 'approved' ? 'Dispensasi Disetujui' : 'Dispensasi Ditolak' }}
                </p>
                <p class="text-sm text-gray-500">{{ new Date(dispensasi.updated_at).toLocaleString('id-ID') }}</p>
                <p class="text-sm text-gray-600 mt-1">oleh {{ dispensasi.approver?.name }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Update Action Buttons -->
  <div v-if="dispensasi && !loading" class="flex gap-2 no-print">
    no-print <button
      @click="printDispensasi"
      class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
    >
      🖨️ Print
    </button>
    
    <router-link
      v-if="canEdit"
      :to="`/dispensasi/${dispensasi.id}/edit`"
      class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
    >
      ✏️ Edit
    </router-link>
    
    <button
      v-if="canDelete"
      @click="handleDelete"
      class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
    >
      🗑️ Hapus
    </button>
  </div>
</template>

<!-- Tambahkan style untuk print -->
<style>
@media print {
  nav, .no-print {
    display: none !important;
  }
  
  body {
    background: white !important;
  }
  
  .max-w-5xl {
    max-width: 100% !important;
  }
}
</style>