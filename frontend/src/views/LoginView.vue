<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

async function handleLogin() {
  error.value = ''
  loading.value = true
  
  try {
    await authStore.login(form.value)
    router.push('/dashboard')
  } catch (err) {
    console.error('Login error:', err)
    error.value = err.response?.data?.message || 'Login gagal. Periksa email dan password Anda.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 flex items-center justify-center p-4">
    <!-- Decorative Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-0 w-96 h-96 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/2 translate-y-1/2"></div>
    </div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
      <!-- Logo & Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-primary-500 rounded-2xl mb-4">
          <span class="text-4xl">🎓</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Selamat Datang</h1>
        <p class="text-gray-600">Sistem Dispensasi SMA Negeri</p>
      </div>

      <!-- Error Alert -->
      <div v-if="error" class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg">
        <div class="flex items-start">
          <span class="text-xl mr-2">⚠️</span>
          <p class="text-sm">{{ error }}</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-5">
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
            Email
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-400">📧</span>
            </div>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
              placeholder="email@sma.com"
            />
          </div>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
            Password
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-400">🔒</span>
            </div>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
              placeholder="••••••••"
            />
          </div>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-primary-500 hover:bg-primary-600 text-white py-3 rounded-lg font-semibold transition shadow-lg hover:shadow-xl disabled:bg-gray-400 disabled:cursor-not-allowed disabled:shadow-none"
        >
          <span v-if="loading" class="flex items-center justify-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Memproses...
          </span>
          <span v-else>Masuk</span>
        </button>
      </form>

      <!-- Register Link -->
      <div class="mt-6 text-center">
        <p class="text-gray-600 text-sm">
          Belum punya akun?
          <router-link to="/register" class="text-primary-600 font-semibold hover:text-primary-700 hover:underline">
            Daftar di sini
          </router-link>
        </p>
      </div>

      <!-- Divider -->
      <div class="my-6 flex items-center">
        <div class="flex-1 border-t border-gray-200"></div>
        <span class="px-4 text-xs text-gray-500">Akun Demo</span>
        <div class="flex-1 border-t border-gray-200"></div>
      </div>

      <!-- Test Accounts -->
      <div class="space-y-2 bg-gray-50 rounded-lg p-4">
        <p class="text-xs font-semibold text-gray-600 mb-3">Akun untuk testing:</p>
        <div class="grid grid-cols-1 gap-2 text-xs">
          <div class="bg-white p-2 rounded border border-gray-200">
            <p class="font-semibold text-gray-700">👨‍💼 Admin</p>
            <p class="text-gray-600">admin@sma.com / password</p>
          </div>
          <div class="bg-white p-2 rounded border border-gray-200">
            <p class="font-semibold text-gray-700">👨‍🏫 Guru</p>
            <p class="text-gray-600">budi@sma.com / password</p>
          </div>
          <div class="bg-white p-2 rounded border border-gray-200">
            <p class="font-semibold text-gray-700">👨‍🎓 Siswa</p>
            <p class="text-gray-600">siswa@sma.com / password</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
