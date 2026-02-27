<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const route = useRoute()
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
const existingFile = ref(null)
const loading = ref(true)
const submitting = ref(false)
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

async function fetchDispensasi() {
  loading.value = true
  try {
    const response = await api.get(`/dispensasi/${route.params.id}`)
    const data = response.data.data
    
    // Check ownership
    if (data.user_id !== authStore.user?.id) {
      error.value = 'Anda tidak memiliki akses untuk mengedit dispensasi ini'
      return
    }
    
    // Check status
    if (data.status !== 'pending') {
      error.value = 'Hanya dispensasi dengan status pending yang bisa diedit'
      return
    }
    
    // Populate form
    form.value.tanggal = data.tanggal
    form.value.jam_pelajaran_mulai = data.jam_pelajaran_mulai
    form.value.jam_pelajaran_selesai = data.jam_pelajaran_selesai
    form.value.mata_pelajaran = data.mata_pelajaran
    form.value.keperluan = data.keperluan
    existingFile.value = data.surat_dispensasi
  } catch (err) {
    console.error('Error fetching dispensasi:', err)
    error.value = 'Gagal memuat data dispensasi'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''
  submitting.value = true

  try {
    // Validasi jam pelajaran
    if (parseInt(form.value.jam_pelajaran_mulai) > parseInt(form.value.jam_pelajaran_selesai)) {
      error.value = 'Jam pelajaran selesai harus lebih besar atau sama dengan jam mulai'
      submitting.value = false
      return
    }

    const formData = new FormData()
    formData.append('tanggal', form.value.tanggal)
    formData.append('jam_pelajaran_mulai', form.value.jam_pelajaran_mulai)
    formData.append('jam_pelajaran_selesai', form.value.jam_pelajaran_selesai)
    formData.append('mata_pelajaran', form.value.mata_pelajaran)
    formData.append('keperluan', form.value.keperluan)
    formData.append('_method', 'PUT')
    
    if (form.value.surat_dispensasi) {
      formData.append('surat_dispensasi', form.value.surat_dispensasi)
    }

    await api.post(`/dispensasi/${route.params.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    success.value = 'Dispensasi berhasil diperbarui!'
    
    setTimeout(() => {
      router.push(`/dispensasi/${route.params.id}`)
    }, 2000)
  } catch (err) {
    console.error('Error updating dispensasi:', err)
    error.value = err.response?.data?.message || 'Gagal memperbarui dispensasi'
    
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    }
  } finally {
    submitting.value = false
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
  fetchDispensasi()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <router-link
        :to="`/dispensasi/${route.params.id}`"
        class="inline-flex items-center text-primary-600 hover:text-primary-800 mb-6"
      >
        ← Kembali ke Detail
      </router-link>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
        <p class="text-gray-500 mt-4">Memuat data...</p>
      </div>

      <div v-else class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
          <span class="text-3xl mr-3">✏️</span>
          Edit Dispensasi
        </h1>

        <div v-if="success" class="mb-6 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg">
          <p class="font-semibold">✅ {{ success }}</p>
          <p class="text-sm">Mengalihkan ke detail...</p>
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
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
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

          <!-- Mata Pelajaran -->
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
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            ></textarea>
          </div>

          <!-- Upload Surat -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Surat Dispensasi (Opsional)
            </label>
            
            <div v-if="existingFile" class="mb-2 text-sm text-gray-600">
              File saat ini: 
              <a 
                :href="`http://127.0.0.1:8000/storage/${existingFile}`"
                target="_blank"
                class="text-primary-600 hover:underline"
              >
                Lihat file
              </a>
            </div>
            
            <input
              type="file"
              @change="handleFileChange"
              accept=".pdf,.jpg,.jpeg,.png"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
            <p class="text-xs text-gray-500 mt-1">
              Format: PDF, JPG, JPEG, PNG. Maksimal 2MB. Kosongkan jika tidak ingin mengubah file.
            </p>
          </div>

          <!-- Submit Buttons -->
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="submitting"
              class="flex-1 bg-primary-500 text-white py-3 rounded-lg font-semibold hover:bg-primary-600 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              <span v-if="submitting">Menyimpan...</span>
              <span v-else>💾 Simpan Perubahan</span>
            </button>

            <router-link
              :to="`/dispensasi/${route.params.id}`"
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