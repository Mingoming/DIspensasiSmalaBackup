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
const fileName = ref('')

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
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    success.value = 'Dispensasi berhasil diajukan!'
    setTimeout(() => { router.push('/dashboard') }, 2000)
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal mengajukan dispensasi'
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors).flat().join(', ')
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
      fileName.value = ''
      return
    }
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png']
    if (!allowedTypes.includes(file.type)) {
      error.value = 'Format file harus PDF, JPG, JPEG, atau PNG'
      event.target.value = ''
      fileName.value = ''
      return
    }
    form.value.surat_dispensasi = file
    fileName.value = file.name
    error.value = ''
  }
}

function clearFile() {
  form.value.surat_dispensasi = null
  fileName.value = ''
}

onMounted(() => {
  fetchConfig()
  form.value.tanggal = getTodayForInput()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

      <!-- Back -->
      <router-link
        to="/dashboard"
        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-600 font-medium mb-6 transition group"
      >
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Dashboard
      </router-link>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Card Header -->
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h1 class="text-lg font-bold text-white">Ajukan Dispensasi Baru</h1>
              <p class="text-sm text-white/70">Isi formulir dengan lengkap dan benar</p>
            </div>
          </div>
        </div>

        <!-- Form Body -->
        <div class="p-6 space-y-6">

          <!-- Success Alert -->
          <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-start gap-2">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="font-semibold text-sm">{{ success }}</p>
              <p class="text-xs mt-0.5 text-green-600">Mengalihkan ke dashboard...</p>
            </div>
          </div>

          <!-- Error Alert -->
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-2">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm">{{ error }}</p>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-5">

            <!-- ── SECTION: Waktu ── -->
            <div>
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Waktu Dispensasi
              </h2>
              <div class="space-y-4">

                <!-- Tanggal -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Tanggal <span class="text-red-500 normal-case font-normal">*</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <input
                      v-model="form.tanggal"
                      type="date"
                      required
                      :min="getTodayForInput()"
                      class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
                    />
                  </div>
                </div>

                <!-- Jam Mulai & Selesai -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                      Jam Mulai <span class="text-red-500 normal-case font-normal">*</span>
                    </label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                      <select
                        v-model="form.jam_pelajaran_mulai"
                        required
                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white appearance-none"
                      >
                        <option value="">Pilih Jam</option>
                        <option v-for="(waktu, jam) in jamPelajaranList" :key="jam" :value="jam">
                          Jam ke-{{ jam }} ({{ waktu }})
                        </option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                      Jam Selesai <span class="text-red-500 normal-case font-normal">*</span>
                    </label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                      <select
                        v-model="form.jam_pelajaran_selesai"
                        required
                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white appearance-none"
                      >
                        <option value="">Pilih Jam</option>
                        <option v-for="(waktu, jam) in jamPelajaranList" :key="jam" :value="jam">
                          Jam ke-{{ jam }} ({{ waktu }})
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            <!-- ── SECTION: Detail Dispensasi ── -->
            <div class="border-t border-gray-100 pt-5">
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Detail Dispensasi
              </h2>
              <div class="space-y-4">

                <!-- Mata Pelajaran -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Mata Pelajaran yang Ditinggalkan <span class="text-red-500 normal-case font-normal">*</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                    </div>
                    <select
                      v-model="form.mata_pelajaran"
                      required
                      class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white appearance-none"
                    >
                      <option value="">Pilih Mata Pelajaran</option>
                      <option v-for="mapel in mataPelajaranList" :key="mapel" :value="mapel">
                        {{ mapel }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Keperluan -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Keperluan / Alasan <span class="text-red-500 normal-case font-normal">*</span>
                  </label>
                  <textarea
                    v-model="form.keperluan"
                    required
                    rows="4"
                    placeholder="Jelaskan keperluan Anda secara detail..."
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition resize-none"
                  ></textarea>
                </div>

              </div>
            </div>

            <!-- ── SECTION: Surat ── -->
            <div class="border-t border-gray-100 pt-5">
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                </svg>
                Lampiran
              </h2>

              <!-- File upload area -->
              <div v-if="!fileName" class="relative">
                <label
                  class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-primary-300 hover:bg-primary-50/30 transition group"
                >
                  <div class="flex flex-col items-center gap-2 text-center">
                    <svg class="w-8 h-8 text-gray-300 group-hover:text-primary-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-sm text-gray-500 group-hover:text-primary-600 transition">
                      Klik untuk upload surat
                    </p>
                    <p class="text-xs text-gray-400">PDF, JPG, JPEG, PNG — maks. 2MB</p>
                  </div>
                  <input
                    type="file"
                    @change="handleFileChange"
                    accept=".pdf,.jpg,.jpeg,.png"
                    class="hidden"
                  />
                </label>
                <p class="text-xs text-gray-400 mt-1.5 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Surat dispensasi bersifat opsional
                </p>
              </div>

              <!-- File selected preview -->
              <div v-else class="flex items-center gap-3 p-3 bg-primary-50 border border-primary-200 rounded-xl">
                <div class="w-9 h-9 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-700 truncate">{{ fileName }}</p>
                  <p class="text-xs text-gray-400">Siap diunggah</p>
                </div>
                <button
                  type="button"
                  @click="clearFile"
                  class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- ── Buttons ── -->
            <div class="flex gap-3 pt-2 border-t border-gray-100">
              <button
                type="submit"
                :disabled="loading"
                class="flex-1 inline-flex items-center justify-center gap-2 bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2.5 rounded-lg text-sm font-semibold transition shadow-sm hover:shadow-md"
              >
                <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                {{ loading ? 'Mengirim...' : 'Ajukan Dispensasi' }}
              </button>

              <router-link
                to="/dashboard"
                class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm font-semibold transition"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Batal
              </router-link>
            </div>

          </form>
        </div>
      </div>
    </main>
  </div>
</template>