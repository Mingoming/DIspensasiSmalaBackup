<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'siswa',
  roles: [],
  nisn: '',
  nip: '',
  mata_pelajaran: '',
  kelas_id: '',
  no_telepon: ''
})

const kelasList = ref([])
const rolesList = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const showPassword = ref(false)

if (!authStore.isAdmin) {
  router.push('/dashboard')
}

watch(() => form.value.role, (newRole) => {
  if (newRole !== 'guru' && newRole !== 'admin') form.value.roles = []
})

async function fetchUser() {
  loading.value = true
  try {
    const response = await api.get(`/users/${route.params.id}`)
    const userData = response.data.data
    form.value = {
      name: userData.name,
      email: userData.email,
      password: '',
      role: userData.role,
      roles: userData.roles ? userData.roles.map(r => r.name) : [],
      nisn: userData.nisn || '',
      nip: userData.nip || '',
      mata_pelajaran: userData.mata_pelajaran || '',
      kelas_id: userData.kelas_id || '',
      no_telepon: userData.no_telepon || ''
    }
  } catch (err) {
    console.error('Error fetching user:', err)
    error.value = 'Gagal memuat data user'
  } finally {
    loading.value = false
  }
}

async function fetchKelas() {
  try {
    const response = await api.get('/kelas')
    kelasList.value = response.data.data
  } catch (err) {
    console.error('Error fetching kelas:', err)
  }
}

async function fetchRoles() {
  try {
    const response = await api.get('/roles')
    rolesList.value = response.data.data
  } catch (err) {
    console.error('Error fetching roles:', err)
  }
}

async function handleSubmit() {
  error.value = ''
  submitting.value = true
  try {
    const payload = { ...form.value }
    if (!payload.password) delete payload.password
    await api.put(`/users/${route.params.id}`, payload)
    alert('User berhasil diperbarui!')
    router.push('/users')
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal memperbarui user'
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors).flat().join(', ')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchKelas()
  fetchRoles()
  fetchUser()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

      <!-- Back -->
      <router-link
        to="/users"
        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-600 font-medium mb-6 transition group"
      >
        <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Daftar User
      </router-link>

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mb-4"></div>
        <p class="text-sm text-gray-400">Memuat data user...</p>
      </div>

      <!-- Card -->
      <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Card Header -->
        <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <div>
              <h1 class="text-lg font-bold text-white">Edit User</h1>
              <p class="text-sm text-white/70">{{ form.name || 'Memuat...' }}</p>
            </div>
          </div>
        </div>

        <!-- Form Body -->
        <div class="p-6 space-y-6">

          <!-- Error Alert -->
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm">{{ error }}</p>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-5">

            <!-- ── SECTION: Info Dasar ── -->
            <div>
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Informasi Dasar
              </h2>
              <div class="space-y-4">

                <!-- Nama -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Nama Lengkap <span class="text-red-500 normal-case font-normal">*</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
                      placeholder="Nama lengkap"
                    />
                  </div>
                </div>

                <!-- Email & Role -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                      Email <span class="text-red-500 normal-case font-normal">*</span>
                    </label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
                        placeholder="email@example.com"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                      Role Utama <span class="text-red-500 normal-case font-normal">*</span>
                    </label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                      </div>
                      <select
                        v-model="form.role"
                        class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white appearance-none"
                      >
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                        <option value="admin">Admin</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Password Baru -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Password Baru <span class="text-gray-400 normal-case font-normal text-xs">(opsional)</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                    </div>
                    <input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      minlength="8"
                      class="w-full pl-10 pr-11 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
                      placeholder="Kosongkan jika tidak ingin mengubah"
                    />
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition"
                    >
                      <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                  </div>
                  <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Kosongkan jika tidak ingin mengubah password
                  </p>
                </div>

                <!-- No Telepon -->
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    No. Telepon <span class="text-gray-400 normal-case font-normal text-xs">(opsional)</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                      </svg>
                    </div>
                    <input
                      v-model="form.no_telepon"
                      type="text"
                      class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
                      placeholder="081234567890"
                    />
                  </div>
                </div>

              </div>
            </div>

            <!-- ── SECTION: Data Siswa ── -->
            <div v-if="form.role === 'siswa'" class="border-t border-gray-100 pt-5">
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                Data Siswa
              </h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    NISN <span class="text-red-500 normal-case font-normal">*</span>
                  </label>
                  <input
                    v-model="form.nisn"
                    type="text"
                    required
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
                    placeholder="0051234567"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Kelas <span class="text-red-500 normal-case font-normal">*</span>
                  </label>
                  <select
                    v-model="form.kelas_id"
                    required
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition bg-white"
                  >
                    <option value="">Pilih Kelas</option>
                    <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                      {{ kelas.nama_kelas }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- ── SECTION: Data Guru/Admin ── -->
            <div v-if="form.role === 'guru' || form.role === 'admin'" class="border-t border-gray-100 pt-5">
              <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Data {{ form.role === 'guru' ? 'Guru' : 'Admin' }}
              </h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    NIP <span class="text-red-500 normal-case font-normal">*</span>
                  </label>
                  <input
                    v-model="form.nip"
                    type="text"
                    required
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
                    placeholder="198501012010011001"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Mata Pelajaran <span class="text-gray-400 normal-case font-normal text-xs">(opsional)</span>
                  </label>
                  <input
                    v-model="form.mata_pelajaran"
                    type="text"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 focus:border-transparent outline-none transition"
                    placeholder="Contoh: Matematika"
                  />
                </div>
              </div>
            </div>

            <!-- ── SECTION: Role Tambahan ── -->
            <div v-if="(form.role === 'guru' || form.role === 'admin') && rolesList.length > 0" class="bg-primary-50 border border-primary-100 rounded-xl p-4">
              <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Role Tambahan <span class="text-gray-400 normal-case font-normal text-xs">(opsional)</span>
              </h2>
              <div class="space-y-2">
                <label
                  v-for="role in rolesList"
                  :key="role.id"
                  :for="`role-${role.id}`"
                  class="flex items-start gap-3 p-3 bg-white rounded-lg border border-primary-100 hover:border-primary-300 cursor-pointer transition"
                >
                  <input
                    :id="`role-${role.id}`"
                    type="checkbox"
                    :value="role.name"
                    v-model="form.roles"
                    class="mt-0.5 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                  />
                  <div>
                    <p class="text-sm font-semibold text-gray-700">{{ role.display_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ role.description }}</p>
                  </div>
                </label>
              </div>
            </div>

            <!-- ── Buttons ── -->
            <div class="flex gap-3 pt-2 border-t border-gray-100">
              <button
                type="submit"
                :disabled="submitting"
                class="flex-1 inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white py-2.5 rounded-lg text-sm font-semibold transition shadow-sm hover:shadow-md"
              >
                <svg v-if="submitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>

              <router-link
                to="/users"
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