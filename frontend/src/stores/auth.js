import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || null)
  const isAuthenticated = ref(!!token.value)

  // Set token ke axios headers
  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  // Computed: Check roles
  const isSiswa = computed(() => user.value?.role === 'siswa')
  const isGuru = computed(() => user.value?.role === 'guru')
  const hasRole = (roleName) => {
    return user.value?.roles?.some(role => role.name === roleName) || false
  }
  const hasAnyRole = (roleNames) => {
    return roleNames.some(roleName => hasRole(roleName))
  }
  const isAdmin = computed(() => hasRole('admin'))
  const isKesiswaan = computed(() => hasRole('kesiswaan'))
  const isGuruMapel = computed(() => hasRole('guru_mapel'))
  const canApprove = computed(() => hasAnyRole(['admin', 'kesiswaan']))

  // Get role display names
  const getRoleDisplayNames = computed(() => {
    if (!user.value?.roles) return []
    return user.value.roles.map(role => role.display_name)
  })

  // Login
  async function login(credentials) {
    try {
      const response = await api.post('/login', credentials)
      
      token.value = response.data.token
      user.value = response.data.user
      isAuthenticated.value = true
      
      localStorage.setItem('token', response.data.token)
      api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      return response.data
    } catch (error) {
      throw error
    }
  }

  // Register
  async function register(userData) {
    try {
      const response = await api.post('/register', userData)
      
      token.value = response.data.token
      user.value = response.data.user
      isAuthenticated.value = true
      
      localStorage.setItem('token', response.data.token)
      api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      return response.data
    } catch (error) {
      throw error
    }
  }

  // Logout
  async function logout() {
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      token.value = null
      user.value = null
      isAuthenticated.value = false
      
      localStorage.removeItem('token')
      delete api.defaults.headers.common['Authorization']
    }
  }

  // Get Profile
  async function fetchProfile() {
    try {
      const response = await api.get('/profile')
      user.value = response.data.user
    } catch (error) {
      console.error('Fetch profile error:', error)
      logout()
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isSiswa,
    isGuru,
    isAdmin,
    isKesiswaan,
    isGuruMapel,
    canApprove,
    hasRole,
    hasAnyRole,
    getRoleDisplayNames,
    login,
    register,
    logout,
    fetchProfile
  }
})