<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()



const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'siswa',
  roles: [], // Array untuk multiple roles (jika guru)
  nisn: '',
  nip: '',
  mata_pelajaran: '',
  kelas_id: '',
  no_telepon: ''
})

const kelasList = ref([])
const rolesList = ref([])
const loading = ref(false)
const error = ref('')

// Fetch kelas untuk dropdown
async function fetchKelas() {
  try {
    const response = await api.get('/kelas')
    kelasList.value = response.data.data
  } catch (err) {
    console.error('Error fetching kelas:', err)
  }
}

// Fetch roles untuk dropdown
async function fetchRoles() {
  try {
    const response = await api.get('/roles')
    rolesList.value = response.data.data
  } catch (err) {
    console.error('Error fetching roles:', err)
  }
}

// Watch role changes - reset roles array jika bukan guru
watch(() => form.value.role, (newRole) => {
  if (newRole !== 'guru') {
    form.value.roles = []
  }
})

async function handleRegister() {
  error.value = ''
  loading.value = true
  
  try {
    await authStore.register(form.value)
    router.push('/dashboard')
  } catch (err) {
    console.error('Register error:', err)
    error.value = err.response?.data?.message || 'Registrasi gagal. Periksa data Anda.'
    
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchKelas()
  fetchRoles()
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-8 my-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Daftar Akun Baru</h1>
        <p class="text-gray-600 mt-2">Sistem Dispensasi SMA</p>
      </div>

      <!-- Error Alert -->
      <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <p class="text-sm">{{ error }}</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Nama lengkap"
          />
        </div>

        <!-- Email & Role Utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="email@example.com"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role Utama</label>
            <select
              v-model="form.role"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="siswa">Siswa</option>
              <option value="guru">Guru</option>
            </select>
          </div>
        </div>

        <!-- Multiple Roles (hanya untuk guru) -->
        <div v-if="form.role === 'guru'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <label class="block text-sm font-medium text-gray-700 mb-3">
            📋 Pilih Role Tambahan (boleh lebih dari satu)
          </label>
          <div class="space-y-2">
            <div v-for="role in rolesList" :key="role.id" class="flex items-center">
              <input
                :id="`role-${role.id}`"
                type="checkbox"
                :value="role.name"
                v-model="form.roles"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label :for="`role-${role.id}`" class="ml-3 block text-sm text-gray-700">
                <span class="font-semibold">{{ role.display_name }}</span>
                <span class="text-gray-500 text-xs block">{{ role.description }}</span>
              </label>
            </div>
          </div>
          <p class="text-xs text-blue-600 mt-3">
            💡 Contoh: Pilih "Guru Mata Pelajaran" + "Kesiswaan" jika Anda mengajar sekaligus mengelola kesiswaan
          </p>
        </div>

        <!-- NISN & Kelas (jika siswa) -->
        <div v-if="form.role === 'siswa'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">NISN</label>
            <input
              v-model="form.nisn"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="0051234567"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
            <select
              v-model="form.kelas_id"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Pilih Kelas</option>
              <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                {{ kelas.nama_kelas }}
              </option>
            </select>
          </div>
        </div>

        <!-- NIP & Mata Pelajaran (jika guru) -->
        <div v-if="form.role === 'guru'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
            <input
              v-model="form.nip"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="198501012010011001"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Mata Pelajaran
              <span v-if="form.roles.includes('guru_mapel')" class="text-red-500">*</span>
            </label>
            <input
              v-model="form.mata_pelajaran"
              type="text"
              :required="form.roles.includes('guru_mapel')"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Contoh: Matematika"
            />
          </div>
        </div>

        <!-- No Telepon -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon (Opsional)</label>
          <input
            v-model="form.no_telepon"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="081234567890"
          />
        </div>

        <!-- Password -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              minlength="8"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Min. 8 karakter"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              required
              minlength="8"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Ulangi password"
            />
          </div>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
          <span v-if="loading">Loading...</span>
          <span v-else>Daftar</span>
        </button>
      </form>

      <!-- Login Link -->
      <div class="mt-6 text-center">
        <p class="text-gray-600 text-sm">
          Sudah punya akun?
          <router-link to="/login" class="text-blue-600 font-semibold hover:underline">
            Login di sini
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>