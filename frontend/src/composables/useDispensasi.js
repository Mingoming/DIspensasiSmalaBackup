/**
 * Composable for dispensasi management
 */
import { ref } from 'vue'
import api from '@/services/api'

export function useDispensasi() {
  const dispensasiList = ref([])
  const loading = ref(false)
  const error = ref('')

  /**
   * Fetch all dispensasi
   */
  async function fetchDispensasi() {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.get('/dispensasi')
      dispensasiList.value = response.data.data
      return response.data.data
    } catch (err) {
      console.error('Error fetching dispensasi:', err)
      error.value = err.response?.data?.message || 'Gagal memuat data dispensasi'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch single dispensasi by ID
   */
  async function fetchDispensasiById(id) {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.get(`/dispensasi/${id}`)
      return response.data.data
    } catch (err) {
      console.error('Error fetching dispensasi:', err)
      error.value = err.response?.data?.message || 'Gagal memuat data dispensasi'
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Delete dispensasi
   */
  async function deleteDispensasi(id) {
    try {
      await api.delete(`/dispensasi/${id}`)
      // Remove from list if exists
      const index = dispensasiList.value.findIndex(d => d.id === id)
      if (index > -1) {
        dispensasiList.value.splice(index, 1)
      }
      return true
    } catch (err) {
      console.error('Error deleting dispensasi:', err)
      error.value = err.response?.data?.message || 'Gagal menghapus dispensasi'
      throw err
    }
  }

  /**
   * Update dispensasi status
   */
  async function updateStatus(id, status, catatan = '') {
    try {
      await api.put(`/dispensasi/${id}/status`, { status, catatan })
      // Update in list if exists
      const item = dispensasiList.value.find(d => d.id === id)
      if (item) {
        item.status = status
      }
      return true
    } catch (err) {
      console.error('Error updating status:', err)
      error.value = err.response?.data?.message || 'Gagal mengubah status'
      throw err
    }
  }

  return {
    dispensasiList,
    loading,
    error,
    fetchDispensasi,
    fetchDispensasiById,
    deleteDispensasi,
    updateStatus
  }
}
