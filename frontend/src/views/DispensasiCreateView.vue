<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'
import { getTodayForInput } from '@/utils/dateFormatter'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  tanggal: '',
  jam_pelajaran_mulai: '',
  jam_pelajaran_selesai: '',
  mata_pelajaran: '',
  keperluan: '',
  surat_dispensasi: null
})

const mataPelajaranList = ref([])
const jamPelajaranList = ref({})
const loading = ref(false)
const error = ref('')
const success = ref('')

async function fetchConfig() {
  try {
    const [mapelRes, jamRes] = await Promise.all([
      api.get('/config/mata-pelajaran'),
      api.get('/config/jam-pelajaran')
    ])
    
    mataPelajaranList.value = mapelRes.data.data
    jamPelajaranList.value = jamRes.data.data
  } catch (err) {
    console.error('Error fetching config:', err)
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    // Validasi jam pelajaran
    if (parseInt(form.value.jam_pelajaran_mulai) > parseInt(form.value.jam_pelajaran_selesai)) {
      error.value = 'Jam pelajaran selesai harus lebih besar atau sama dengan jam mulai'
      loading.value = false
      return
    }

    const formData = new FormData()
    formData.append('tanggal', form.value.tanggal)
    formData.append('jam_pelajaran_mulai', form.value.jam_pelajaran_mulai)
    formData.append('jam_pelajaran_selesai', form.value.jam_pelajaran_selesai)
    formData.append('mata_pelajaran', form.value.mata_pelajaran)
    formData.append('keperluan', form.value.keperluan)
    
    if (form.value.surat_dispensasi) {
      formData.append('surat_dispensasi', form.value.surat_dispensasi)
    }

    await api.post('/dispensasi', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    success.value = 'Dispensasi berhasil diajukan!'
    
    setTimeout(() => {
      router.push('/dashboard')
    }, 2000)
  } catch (err) {
    console.error('Error creating dispensasi:', err)
    error.value = err.response?.data?.message || 'Gagal mengajukan dispensasi'
    
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    }
  } finally {
    loading.value = false
  }
}

function handleFileChange(event) {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2048000) {
      error.value = 'Ukuran file maksimal 2MB'
      event.target.value = ''
      return
    }
    
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png']
    if (!allowedTypes.includes(file.type)) {
      error.value = 'Format file harus PDF, JPG, JPEG, atau PNG'
      event.target.value = ''
      return
    }
    
    form.value.surat_dispensasi = file
    error.value = ''
  }
}

onMounted(() => {
  fetchConfig()
  form.value.tanggal = getTodayForInput()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <router-link
        to="/dashboard"
        class="inline-flex items-center text-primary-600 hover:text-primary-800 mb-6"
      >
        ← Kembali ke Dashboard
      </router-link>

      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
          <span class="text-3xl mr-3">📝</span>
          Ajukan Dispensasi Baru
        </h1>

        <div v-if="success" class="mb-6 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg">
          <p class="font-semibold">✅ {{ success }}</p>
          <p class="text-sm">Mengalihkan ke dashboard...</p>
        </div>

        <div v-if="error" class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg">
          <p class="text-sm">❌ {{ error }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Tanggal -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Tanggal Dispensasi <span class="text-danger-500">*</span>
            </label>
            <input
              v-model="form.tanggal"
              type="date"
              required
              :min="getTodayForInput()"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
            <p class="text-xs text-gray-500 mt-1">Format: Tanggal/Bulan/Tahun</p>
          </div>

          <!-- Jam Pelajaran -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Jam Pelajaran Mulai <span class="text-danger-500">*</span>
              </label>
              <select
                v-model="form.jam_pelajaran_mulai"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">Pilih Jam Pelajaran</option>
                <option v-for="(waktu, jam) in jamPelajaranList" :key="jam" :value="jam">
                  Jam ke-{{ jam }} ({{ waktu }})
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Jam Pelajaran Selesai <span class="text-danger-500">*</span>
              </label>
              <select
                v-model="form.jam_pelajaran_selesai"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">Pilih Jam Pelajaran</option>
                <option v-for="(waktu, jam) in jamPelajaranList" :key="jam" :value="jam">
                  Jam ke-{{ jam }} ({{ waktu }})
                </option>
              </select>
            </div>
          </div>

          <!-- Mata Pelajaran Dropdown -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Mata Pelajaran yang Ditinggalkan <span class="text-danger-500">*</span>
            </label>
            <select
              v-model="form.mata_pelajaran"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">Pilih Mata Pelajaran</option>
              <option v-for="mapel in mataPelajaranList" :key="mapel" :value="mapel">
                {{ mapel }}
              </option>
            </select>
          </div>

          <!-- Keperluan -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Keperluan/Alasan Dispensasi <span class="text-danger-500">*</span>
            </label>
            <textarea
              v-model="form.keperluan"
              required
              rows="4"
              placeholder="Jelaskan keperluan Anda secara detail..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            ></textarea>
          </div>

          <!-- Upload Surat -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Surat Dispensasi (Opsional)
            </label>
            <input
              type="file"
              @change="handleFileChange"
              accept=".pdf,.jpg,.jpeg,.png"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
            <p class="text-xs text-gray-500 mt-1">
              Format: PDF, JPG, JPEG, PNG. Maksimal 2MB
            </p>
          </div>

          <!-- Submit Button -->
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 bg-primary-500 text-white py-3 rounded-lg font-semibold hover:bg-primary-600 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              <span v-if="loading">Mengirim...</span>
              <span v-else>📤 Ajukan Dispensasi</span>
            </button>

            <router-link
              to="/dashboard"
              class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center"
            >
              Batal
            </router-link>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>