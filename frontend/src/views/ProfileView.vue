<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const authStore = useAuthStore()

const activeTab = ref('profile')
const kelasList = ref([])
const avatarPreview = ref(null)
const avatarFile = ref(null)
const loadingAvatar = ref(false)

const profileForm = ref({
  name: '', email: '', no_telepon: '',
  nisn: '', nip: '', mata_pelajaran: '', kelas_id: ''
})

const passwordForm = ref({
  current_password: '', new_password: '', new_password_confirmation: ''
})

const loadingProfile = ref(false)
const loadingPassword = ref(false)
const profileError = ref('')
const profileSuccess = ref('')
const passwordError = ref('')
const passwordSuccess = ref('')
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const user = computed(() => authStore.user)

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
}

function getAvatarColor(role) {
  const colors = { siswa: 'bg-blue-100 text-blue-600', guru: 'bg-green-100 text-green-600', admin: 'bg-amber-100 text-amber-600' }
  return colors[role] || 'bg-primary-100 text-primary-600'
}

function getRoleBadge(role) {
  const badges = { siswa: 'bg-blue-100 text-blue-700', guru: 'bg-green-100 text-green-700', admin: 'bg-amber-100 text-amber-700' }
  return badges[role] || 'bg-gray-100 text-gray-700'
}

function handleAvatarChange(event) {
  const file = event.target.files[0]
  if (!file) return
  if (!file.type.startsWith('image/')) { alert('File harus berupa gambar'); return }
  if (file.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2MB'); return }
  avatarFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => { avatarPreview.value = e.target.result }
  reader.readAsDataURL(file)
}

async function handleUploadAvatar() {
  if (!avatarFile.value) return
  loadingAvatar.value = true
  try {
    const formData = new FormData()
    formData.append('avatar', avatarFile.value)
    const response = await api.post('/profile/avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    authStore.user = response.data.user
    avatarFile.value = null
    profileSuccess.value = 'Foto profil berhasil diperbarui!'
    setTimeout(() => { profileSuccess.value = '' }, 3000)
  } catch (err) {
    profileError.value = err.response?.data?.message || 'Gagal mengupload foto'
  } finally {
    loadingAvatar.value = false
  }
}

function cancelAvatarChange() {
  avatarPreview.value = null
  avatarFile.value = null
}

async function fetchKelas() {
  try {
    const response = await api.get('/kelas')
    kelasList.value = response.data.data
  } catch (err) { console.error(err) }
}

async function fetchProfile() {
  try {
    const response = await api.get('/profile/me')
    const u = response.data.user
    profileForm.value = {
      name: u.name || '', email: u.email || '', no_telepon: u.no_telepon || '',
      nisn: u.nisn || '', nip: u.nip || '', mata_pelajaran: u.mata_pelajaran || '', kelas_id: u.kelas_id || ''
    }
  } catch (err) { console.error(err) }
}

async function handleUpdateProfile() {
  profileError.value = ''
  profileSuccess.value = ''
  loadingProfile.value = true
  try {
    const response = await api.put('/profile/update', profileForm.value)
    profileSuccess.value = 'Profile berhasil diperbarui!'
    authStore.user = response.data.user
    setTimeout(() => { profileSuccess.value = '' }, 3000)
  } catch (err) {
    profileError.value = err.response?.data?.message || 'Gagal memperbarui profile'
    if (err.response?.data?.errors) {
      profileError.value = Object.values(err.response.data.errors).flat().join(', ')
    }
  } finally {
    loadingProfile.value = false
  }
}

async function handleUpdatePassword() {
  passwordError.value = ''
  passwordSuccess.value = ''
  loadingPassword.value = true
  try {
    await api.put('/profile/update-password', passwordForm.value)
    passwordSuccess.value = 'Password berhasil diubah!'
    passwordForm.value = { current_password: '', new_password: '', new_password_confirmation: '' }
    setTimeout(() => { passwordSuccess.value = '' }, 3000)
  } catch (err) {
    passwordError.value = err.response?.data?.message || 'Gagal mengubah password'
  } finally {
    loadingPassword.value = false
  }
}

const passwordMismatch = computed(() =>
  passwordForm.value.new_password_confirmation &&
  passwordForm.value.new_password !== passwordForm.value.new_password_confirmation
)

onMounted(() => {
  fetchKelas()
  fetchProfile()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
          <!-- User icon -->
          
          Pengaturan Profile
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola informasi dan keamanan akun Anda</p>
      </div>

      <!-- Profile Card -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">

          <!-- Avatar -->
          <div class="flex flex-col items-center gap-2 flex-shrink-0">
            <div class="relative">
              <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-gray-100">
                <img
                  v-if="avatarPreview || user?.avatar"
                  :src="avatarPreview || `/storage/${user?.avatar}`"
                  alt="Foto Profil"
                  class="w-full h-full object-cover"
                />
                <div
                  v-else
                  :class="getAvatarColor(user?.role)"
                  class="w-full h-full flex items-center justify-center text-2xl font-bold"
                >
                  {{ getInitials(user?.name) }}
                </div>
              </div>
              <label
                for="avatar-upload"
                class="absolute bottom-0 right-0 w-8 h-8 bg-primary-500 hover:bg-primary-600 text-white rounded-full flex items-center justify-center cursor-pointer shadow-md transition"
                title="Ganti foto profil"
              >
                <!-- Camera icon -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </label>
              <input id="avatar-upload" type="file" accept="image/*" class="hidden" @change="handleAvatarChange" />
            </div>

            <div v-if="avatarFile" class="flex gap-1.5">
              <button
                @click="handleUploadAvatar"
                :disabled="loadingAvatar"
                class="px-3 py-1.5 text-xs font-semibold bg-primary-500 hover:bg-primary-600 text-white rounded-lg transition disabled:bg-gray-300"
              >{{ loadingAvatar ? 'Uploading...' : 'Simpan' }}</button>
              <button
                @click="cancelAvatarChange"
                class="px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition"
              >Batal</button>
            </div>
            <p class="text-[11px] text-gray-400">Maks. 2MB · JPG, PNG</p>
          </div>

          <!-- Info -->
          <div class="flex-1 text-center sm:text-left">
            <h2 class="text-xl font-bold text-gray-800">{{ user?.name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ user?.email }}</p>
            <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
              <span :class="getRoleBadge(user?.role)" class="px-3 py-1 text-xs font-semibold rounded-full capitalize">
                {{ user?.role }}
              </span>
              <span
                v-for="role in user?.roles" :key="role.id"
                class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-700"
              >{{ role.display_name }}</span>
            </div>
            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-gray-500 justify-center sm:justify-start">
              <span v-if="user?.role === 'siswa' && user?.kelas" class="flex items-center gap-1">
                <!-- Building icon -->
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ user?.kelas?.nama_kelas }}
              </span>
              <span v-if="user?.role === 'siswa' && user?.nisn" class="flex items-center gap-1">
                <!-- ID card icon -->
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" />
                </svg>
                NISN: {{ user?.nisn }}
              </span>
              <span v-if="user?.role !== 'siswa' && user?.nip" class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" />
                </svg>
                NIP: {{ user?.nip }}
              </span>
              <span v-if="user?.mata_pelajaran" class="flex items-center gap-1">
                <!-- Book icon -->
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                {{ user?.mata_pelajaran }}
              </span>
              <span v-if="user?.no_telepon" class="flex items-center gap-1">
                <!-- Phone icon -->
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                {{ user?.no_telepon }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Alert global -->
      <div v-if="profileSuccess" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ profileSuccess }}
      </div>
      <div v-if="profileError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ profileError }}
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-100">
          <button
            @click="activeTab = 'profile'"
            :class="activeTab === 'profile'
              ? 'text-primary-600 border-b-2 border-primary-500 bg-primary-50/40'
              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
            class="flex-1 px-6 py-3.5 text-sm font-semibold transition flex items-center justify-center gap-2"
          >
            <!-- Pencil icon -->
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Informasi Profile
          </button>
          <button
            @click="activeTab = 'password'"
            :class="activeTab === 'password'
              ? 'text-primary-600 border-b-2 border-primary-500 bg-primary-50/40'
              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
            class="flex-1 px-6 py-3.5 text-sm font-semibold transition flex items-center justify-center gap-2"
          >
            <!-- Lock icon -->
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Ubah Password
          </button>
        </div>

        <div class="p-6">

          <!-- Profile Tab -->
          <form v-if="activeTab === 'profile'" @submit.prevent="handleUpdateProfile" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
              <input v-model="profileForm.name" type="text" required
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Email <span class="text-red-400">*</span></label>
              <input v-model="profileForm.email" type="email" required
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">No. Telepon</label>
              <input v-model="profileForm.no_telepon" type="text" placeholder="08xxxxxxxxxx"
                class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition" />
            </div>

            <!-- Siswa -->
            <div v-if="user?.role === 'siswa'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">NISN</label>
                <input v-model="profileForm.nisn" type="text"
                  class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Kelas</label>
                <select v-model="profileForm.kelas_id"
                  class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white">
                  <option value="">Pilih Kelas</option>
                  <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">{{ kelas.nama_kelas }}</option>
                </select>
              </div>
            </div>

            <!-- Guru -->
            <div v-if="user?.role === 'guru'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">NIP</label>
                <input v-model="profileForm.nip" type="text"
                  class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Mata Pelajaran</label>
                <input v-model="profileForm.mata_pelajaran" type="text"
                  class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition" />
              </div>
            </div>

            <div class="pt-2">
              <button type="submit" :disabled="loadingProfile"
                class="w-full bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2.5 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2">
                <svg v-if="loadingProfile" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                {{ loadingProfile ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </form>

          <!-- Password Tab -->
          <div v-if="activeTab === 'password'">
            <div v-if="passwordSuccess" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ passwordSuccess }}
            </div>
            <div v-if="passwordError" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ passwordError }}
            </div>

            <form @submit.prevent="handleUpdatePassword" class="space-y-4">

              <!-- Current password -->
              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Password Saat Ini <span class="text-red-400">*</span></label>
                <div class="relative">
                  <input v-model="passwordForm.current_password" :type="showCurrentPassword ? 'text' : 'password'" required
                    placeholder="Masukkan password saat ini"
                    class="w-full px-4 py-2.5 pr-11 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition" />
                  <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                    <svg v-if="showCurrentPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- New password -->
              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Password Baru <span class="text-red-400">*</span></label>
                <div class="relative">
                  <input v-model="passwordForm.new_password" :type="showNewPassword ? 'text' : 'password'" required minlength="8"
                    placeholder="Minimal 8 karakter"
                    class="w-full px-4 py-2.5 pr-11 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition" />
                  <button type="button" @click="showNewPassword = !showNewPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                    <svg v-if="showNewPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Confirm password -->
              <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Konfirmasi Password Baru <span class="text-red-400">*</span></label>
                <div class="relative">
                  <input v-model="passwordForm.new_password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required minlength="8"
                    placeholder="Ulangi password baru"
                    :class="passwordMismatch ? 'border-red-300 focus:ring-red-300' : 'border-gray-200 focus:ring-primary-400'"
                    class="w-full px-4 py-2.5 pr-11 text-sm border rounded-lg focus:ring-2 focus:border-transparent outline-none transition" />
                  <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                    <svg v-if="showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
                <p v-if="passwordMismatch" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  Password tidak cocok
                </p>
              </div>

              <!-- Tips -->
              <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                  </svg>
                  <p class="text-xs font-semibold text-blue-700">Tips Keamanan</p>
                </div>
                <ul class="text-xs text-blue-600 space-y-1.5">
                  <li v-for="tip in ['Gunakan minimal 8 karakter', 'Kombinasikan huruf besar, kecil, angka, dan simbol', 'Jangan gunakan password yang mudah ditebak']"
                    :key="tip" class="flex items-start gap-2">
                    <svg class="w-3.5 h-3.5 text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ tip }}
                  </li>
                </ul>
              </div>

              <div class="pt-2">
                <button type="submit" :disabled="loadingPassword || passwordMismatch"
                  class="w-full bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2.5 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-2">
                  <svg v-if="loadingPassword" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  {{ loadingPassword ? 'Memproses...' : 'Ubah Password' }}
                </button>
              </div>
            </form>
          </div>

        </div>
      </div>

    </main>
  </div>
</template>