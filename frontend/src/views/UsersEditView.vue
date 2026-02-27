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

// Redirect if not admin
if (!authStore.isAdmin) {
  router.push('/dashboard')
}

watch(() => form.value.role, (newRole) => {
  if (newRole !== 'guru' && newRole !== 'admin') {
    form.value.roles = []
  }
})

async function fetchUser() {
  loading.value = true
  try {
    const response = await api.get(`/users/${route.params.id}`)
    const userData = response.data.data
    
    form.value = {
      name: userData.name,
      email: userData.email,
      password: '', // Don't populate password
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
    
    // Remove password if empty (don't update password)
    if (!payload.password) {
      delete payload.password
    }

    await api.put(`/users/${route.params.id}`, payload)
    alert('User berhasil diperbarui!')
    router.push('/users')
  } catch (err) {
    console.error('Update user error:', err)
    error.value = err.response?.data?.message || 'Gagal memperbarui user'
    
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
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

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <router-link
        to="/users"
        class="inline-flex items-center text-primary-600 hover:text-primary-800 mb-6"
      >
        ← Kembali ke Daftar User
      </router-link>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-12 text-center border border-gray-100">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500"></div>
        <p class="text-gray-500 mt-4">Memuat data...</p>
      </div>

      <!-- Form -->
      <div v-else class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
          <span class="text-3xl mr-3">✏️</span>
          Edit User
        </h1>

        <div v-if="error" class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg">
          <p class="text-sm">{{ error }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Name -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Nama Lengkap <span class="text-danger-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="Nama lengkap"
            />
          </div>

          <!-- Email & Role -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Email <span class="text-danger-500">*</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="email@example.com"
              />
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Role Utama <span class="text-danger-500">*</span>
              </label>
              <select
                v-model="form.role"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="siswa">Siswa</option>
                <option value="guru">Guru</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>

          <!-- Password (Optional) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Password Baru (opsional)
            </label>
            <input
              v-model="form.password"
              type="password"
              minlength="8"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="Kosongkan jika tidak ingin mengubah password"
            />
            <p class="text-xs text-gray-500 mt-1">
              💡 Kosongkan field ini jika tidak ingin mengubah password
            </p>
          </div>

          <!-- Multiple Roles (jika guru/admin) -->
          <div v-if="form.role === 'guru' || form.role === 'admin'" class="bg-primary-50 border border-primary-200 rounded-lg p-4">
            <label class="block text-sm font-semibold text-gray-700 mb-3">
              📋 Role Tambahan (opsional)
            </label>
            <div class="space-y-2">
              <div v-for="role in rolesList" :key="role.id" class="flex items-center">
                <input
                  :id="`role-${role.id}`"
                  type="checkbox"
                  :value="role.name"
                  v-model="form.roles"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label :for="`role-${role.id}`" class="ml-3 block text-sm text-gray-700">
                  <span class="font-semibold">{{ role.display_name }}</span>
                  <span class="text-gray-500 text-xs block">{{ role.description }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Fields untuk Siswa -->
          <div v-if="form.role === 'siswa'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                NISN <span class="text-danger-500">*</span>
              </label>
              <input
                v-model="form.nisn"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="0051234567"
              />
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Kelas <span class="text-danger-500">*</span>
              </label>
              <select
                v-model="form.kelas_id"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">Pilih Kelas</option>
                <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                  {{ kelas.nama_kelas }}
                </option>
              </select>
            </div>
          </div>

          <!-- Fields untuk Guru/Admin -->
          <div v-if="form.role === 'guru' || form.role === 'admin'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                NIP <span class="text-danger-500">*</span>
              </label>
              <input
                v-model="form.nip"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="198501012010011001"
              />
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Mata Pelajaran
              </label>
              <input
                v-model="form.mata_pelajaran"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Contoh: Matematika"
              />
            </div>
          </div>

          <!-- No Telepon -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              No. Telepon (opsional)
            </label>
            <input
              v-model="form.no_telepon"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="081234567890"
            />
          </div>

          <!-- Buttons -->
          <div class="flex gap-4 pt-4">
            <button
              type="submit"
              :disabled="submitting"
              class="flex-1 bg-primary-500 hover:bg-primary-600 text-white py-3 rounded-lg font-semibold transition disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              <span v-if="submitting">Menyimpan...</span>
              <span v-else>💾 Simpan Perubahan</span>
            </button>

            <router-link
              to="/users"
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