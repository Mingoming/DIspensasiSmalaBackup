<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const authStore = useAuthStore()

const activeTab = ref('profile')
const kelasList = ref([])

// Profile Form
const profileForm = ref({
  name: '',
  email: '',
  no_telepon: '',
  nisn: '',
  nip: '',
  mata_pelajaran: '',
  kelas_id: ''
})

// Password Form
const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const loadingProfile = ref(false)
const loadingPassword = ref(false)
const profileError = ref('')
const profileSuccess = ref('')
const passwordError = ref('')
const passwordSuccess = ref('')

async function fetchKelas() {
  try {
    const response = await api.get('/kelas')
    kelasList.value = response.data.data
  } catch (err) {
    console.error('Error fetching kelas:', err)
  }
}

async function fetchProfile() {
  try {
    const response = await api.get('/profile/me')
    const user = response.data.user
    
    profileForm.value = {
      name: user.name || '',
      email: user.email || '',
      no_telepon: user.no_telepon || '',
      nisn: user.nisn || '',
      nip: user.nip || '',
      mata_pelajaran: user.mata_pelajaran || '',
      kelas_id: user.kelas_id || ''
    }
  } catch (err) {
    console.error('Error fetching profile:', err)
  }
}

async function handleUpdateProfile() {
  profileError.value = ''
  profileSuccess.value = ''
  loadingProfile.value = true
  
  try {
    const response = await api.put('/profile/update', profileForm.value)
    profileSuccess.value = 'Profile berhasil diperbarui!'
    
    // Update auth store
    authStore.user = response.data.user
    
    setTimeout(() => {
      profileSuccess.value = ''
    }, 3000)
  } catch (err) {
    console.error('Update profile error:', err)
    profileError.value = err.response?.data?.message || 'Gagal memperbarui profile'
    
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      profileError.value = errors.join(', ')
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
    
    // Reset form
    passwordForm.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    }
    
    setTimeout(() => {
      passwordSuccess.value = ''
    }, 3000)
  } catch (err) {
    console.error('Update password error:', err)
    passwordError.value = err.response?.data?.message || 'Gagal mengubah password'
  } finally {
    loadingPassword.value = false
  }
}

onMounted(() => {
  fetchKelas()
  fetchProfile()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 flex items-center">
          <span class="text-3xl mr-3">👤</span>
          Pengaturan Profile
        </h1>
        <p class="text-gray-600 mt-1">Kelola informasi akun Anda</p>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-t-xl shadow-sm border border-gray-100 border-b-0">
        <div class="flex border-b border-gray-200">
          <button
            @click="activeTab = 'profile'"
            :class="[
              'flex-1 px-6 py-4 text-sm font-semibold transition',
              activeTab === 'profile'
                ? 'text-primary-600 border-b-2 border-primary-600'
                : 'text-gray-600 hover:text-gray-800'
            ]"
          >
            📝 Informasi Profile
          </button>
          <button
            @click="activeTab = 'password'"
            :class="[
              'flex-1 px-6 py-4 text-sm font-semibold transition',
              activeTab === 'password'
                ? 'text-primary-600 border-b-2 border-primary-600'
                : 'text-gray-600 hover:text-gray-800'
            ]"
          >
            🔐 Ubah Password
          </button>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="bg-white rounded-b-xl shadow-sm p-6 border border-gray-100">
        <!-- Profile Tab -->
        <div v-if="activeTab === 'profile'">
          <div v-if="profileSuccess" class="mb-6 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg">
            <p class="text-sm">✅ {{ profileSuccess }}</p>
          </div>

          <div v-if="profileError" class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg">
            <p class="text-sm">❌ {{ profileError }}</p>
          </div>

          <form @submit.prevent="handleUpdateProfile" class="space-y-5">
            <!-- Name -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Nama Lengkap <span class="text-danger-500">*</span>
              </label>
              <input
                v-model="profileForm.name"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Email <span class="text-danger-500">*</span>
              </label>
              <input
                v-model="profileForm.email"
                type="email"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
            </div>

            <!-- No Telepon -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                No. Telepon
              </label>
              <input
                v-model="profileForm.no_telepon"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
            </div>

            <!-- Fields untuk Siswa -->
            <div v-if="authStore.user?.role === 'siswa'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">NISN</label>
                <input
                  v-model="profileForm.nisn"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
              </div>
              
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas</label>
                <select
                  v-model="profileForm.kelas_id"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
                  <option value="">Pilih Kelas</option>
                  <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                    {{ kelas.nama_kelas }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Fields untuk Guru -->
            <div v-if="authStore.user?.role === 'guru'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">NIP</label>
                <input
                  v-model="profileForm.nip"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
              </div>
              
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Mata Pelajaran</label>
                <input
                  v-model="profileForm.mata_pelajaran"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
              </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
              <button
                type="submit"
                :disabled="loadingProfile"
                class="w-full bg-primary-500 hover:bg-primary-600 text-white py-3 rounded-lg font-semibold transition disabled:bg-gray-400 disabled:cursor-not-allowed"
              >
                <span v-if="loadingProfile">Menyimpan...</span>
                <span v-else>💾 Simpan Perubahan</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Password Tab -->
        <div v-if="activeTab === 'password'">
          <div v-if="passwordSuccess" class="mb-6 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg">
            <p class="text-sm">✅ {{ passwordSuccess }}</p>
          </div>

          <div v-if="passwordError" class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg">
            <p class="text-sm">❌ {{ passwordError }}</p>
          </div>

          <form @submit.prevent="handleUpdatePassword" class="space-y-5">
            <!-- Current Password -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Password Saat Ini <span class="text-danger-500">*</span>
              </label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Masukkan password saat ini"
              />
            </div>

            <!-- New Password -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Password Baru <span class="text-danger-500">*</span>
              </label>
              <input
                v-model="passwordForm.new_password"
                type="password"
                required
                minlength="8"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Minimal 8 karakter"
              />
            </div>

            <!-- Confirm New Password -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Konfirmasi Password Baru <span class="text-danger-500">*</span>
              </label>
              <input
                v-model="passwordForm.new_password_confirmation"
                type="password"
                required
                minlength="8"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Ulangi password baru"
              />
            </div>

            <!-- Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-blue-800">
                💡 <strong>Tips Keamanan:</strong>
              </p>
              <ul class="text-xs text-blue-700 mt-2 space-y-1 ml-5 list-disc">
                <li>Gunakan minimal 8 karakter</li>
                <li>Kombinasikan huruf besar, kecil, angka, dan simbol</li>
                <li>Jangan gunakan password yang mudah ditebak</li>
              </ul>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
              <button
                type="submit"
                :disabled="loadingPassword"
                class="w-full bg-primary-500 hover:bg-primary-600 text-white py-3 rounded-lg font-semibold transition disabled:bg-gray-400 disabled:cursor-not-allowed"
              >
                <span v-if="loadingPassword">Memproses...</span>
                <span v-else">🔐 Ubah Password</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>