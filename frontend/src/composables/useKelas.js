/**
 * Composable for kelas management
 */
import { ref } from 'vue'
import api from '@/services/api'

export function useKelas() {
  const kelasList = ref([])
  const loading = ref(false)
  const error = ref('')

  /**
   * Fetch all kelas
   */
  async function fetchKelas() {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.get('/kelas')
      kelasList.value = response.data.data
      return response.data.data
    } catch (err) {
      console.error('Error fetching kelas:', err)
      error.value = err.response?.data?.message || 'Gagal memuat data kelas'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    kelasList,
    loading,
    error,
    fetchKelas
  }
}
