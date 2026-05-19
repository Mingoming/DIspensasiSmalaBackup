<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/AppNavbar.vue'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

if (!authStore.isAdmin) {
  router.push('/dashboard')
}

const activeTab = ref('kelas')
const loading = ref(true)
const saving = ref(false)
const error = ref('')

const kelasList = ref([])
const mapelList = ref([])
const jadwalList = ref([])
const guruList = ref([])

const kelasForm = ref({ id: null, nama_kelas: '', tingkat: '', jurusan: '' })
const mapelForm = ref({ id: null, nama: '', aktif: true })
const jadwalForm = ref({
  id: null,
  user_id: '',
  kelas_id: '',
  mata_pelajaran_id: '',
  hari: 'senin',
  jam_pelajaran_mulai: 1,
  jam_pelajaran_selesai: 2
})

const tabs = [
  { id: 'kelas', label: 'Kelas' },
  { id: 'mapel', label: 'Mata Pelajaran' },
  { id: 'jadwal', label: 'Jadwal Mengajar' }
]

const hariOptions = [
  ['senin', 'Senin'],
  ['selasa', 'Selasa'],
  ['rabu', 'Rabu'],
  ['kamis', 'Kamis'],
  ['jumat', 'Jumat'],
  ['sabtu', 'Sabtu']
]

const jamOptions = computed(() => Array.from({ length: 8 }, (_, index) => index + 1))

function setError(err, fallback) {
  error.value = err.response?.data?.message || fallback
  if (err.response?.data?.errors) {
    error.value = Object.values(err.response.data.errors).flat().join(', ')
  }
}

async function fetchMasterData() {
  loading.value = true
  error.value = ''
  try {
    const [kelasRes, mapelRes, jadwalRes, guruRes] = await Promise.all([
      api.get('/kelas'),
      api.get('/mata-pelajaran'),
      api.get('/jadwal-mengajar'),
      api.get('/users', { params: { role: 'guru', per_page: 100 } })
    ])

    kelasList.value = kelasRes.data.data
    mapelList.value = mapelRes.data.data
    jadwalList.value = jadwalRes.data.data
    guruList.value = guruRes.data.data
  } catch (err) {
    setError(err, 'Gagal memuat master data')
  } finally {
    loading.value = false
  }
}

function resetKelasForm() {
  kelasForm.value = { id: null, nama_kelas: '', tingkat: '', jurusan: '' }
}

function editKelas(kelas) {
  kelasForm.value = { ...kelas }
}

async function saveKelas() {
  saving.value = true
  error.value = ''
  try {
    if (kelasForm.value.id) {
      await api.put(`/kelas/${kelasForm.value.id}`, kelasForm.value)
    } else {
      await api.post('/kelas', kelasForm.value)
    }
    resetKelasForm()
    await fetchMasterData()
  } catch (err) {
    setError(err, 'Gagal menyimpan kelas')
  } finally {
    saving.value = false
  }
}

async function deleteKelas(kelas) {
  if (!confirm(`Hapus kelas "${kelas.nama_kelas}"?`)) return
  try {
    await api.delete(`/kelas/${kelas.id}`)
    await fetchMasterData()
  } catch (err) {
    setError(err, 'Gagal menghapus kelas')
  }
}

function resetMapelForm() {
  mapelForm.value = { id: null, nama: '', aktif: true }
}

function editMapel(mapel) {
  mapelForm.value = { id: mapel.id, nama: mapel.nama, aktif: Boolean(mapel.aktif) }
}

async function saveMapel() {
  saving.value = true
  error.value = ''
  try {
    if (mapelForm.value.id) {
      await api.put(`/mata-pelajaran/${mapelForm.value.id}`, mapelForm.value)
    } else {
      await api.post('/mata-pelajaran', mapelForm.value)
    }
    resetMapelForm()
    await fetchMasterData()
  } catch (err) {
    setError(err, 'Gagal menyimpan mata pelajaran')
  } finally {
    saving.value = false
  }
}

async function deleteMapel(mapel) {
  if (!confirm(`Hapus mata pelajaran "${mapel.nama}"?`)) return
  try {
    await api.delete(`/mata-pelajaran/${mapel.id}`)
    await fetchMasterData()
  } catch (err) {
    setError(err, 'Gagal menghapus mata pelajaran')
  }
}

function resetJadwalForm() {
  jadwalForm.value = {
    id: null,
    user_id: '',
    kelas_id: '',
    mata_pelajaran_id: '',
    hari: 'senin',
    jam_pelajaran_mulai: 1,
    jam_pelajaran_selesai: 2
  }
}

function editJadwal(jadwal) {
  jadwalForm.value = {
    id: jadwal.id,
    user_id: jadwal.user_id,
    kelas_id: jadwal.kelas_id,
    mata_pelajaran_id: jadwal.mata_pelajaran_id,
    hari: jadwal.hari,
    jam_pelajaran_mulai: jadwal.jam_pelajaran_mulai,
    jam_pelajaran_selesai: jadwal.jam_pelajaran_selesai
  }
}

async function saveJadwal() {
  saving.value = true
  error.value = ''
  try {
    const payload = {
      ...jadwalForm.value,
      jam_pelajaran_mulai: Number(jadwalForm.value.jam_pelajaran_mulai),
      jam_pelajaran_selesai: Number(jadwalForm.value.jam_pelajaran_selesai)
    }

    if (jadwalForm.value.id) {
      await api.put(`/jadwal-mengajar/${jadwalForm.value.id}`, payload)
    } else {
      await api.post('/jadwal-mengajar', payload)
    }
    resetJadwalForm()
    await fetchMasterData()
  } catch (err) {
    setError(err, 'Gagal menyimpan jadwal mengajar')
  } finally {
    saving.value = false
  }
}

async function deleteJadwal(jadwal) {
  if (!confirm('Hapus jadwal mengajar ini?')) return
  try {
    await api.delete(`/jadwal-mengajar/${jadwal.id}`)
    await fetchMasterData()
  } catch (err) {
    setError(err, 'Gagal menghapus jadwal mengajar')
  }
}

function hariLabel(value) {
  return hariOptions.find(([id]) => id === value)?.[1] || value
}

onMounted(fetchMasterData)
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-5">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Master Data Admin</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola kelas, mata pelajaran, dan jadwal mengajar</p>
      </div>

      <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-2 flex flex-wrap gap-2">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          type="button"
          @click="activeTab = tab.id"
          class="px-4 py-2 rounded-lg text-sm font-semibold transition"
          :class="activeTab === tab.id ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-100'"
        >
          {{ tab.label }}
        </button>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        {{ error }}
      </div>

      <div v-if="loading" class="bg-white rounded-xl border border-gray-100 shadow-sm py-16 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-500 mb-4"></div>
        <p class="text-sm text-gray-400">Memuat master data...</p>
      </div>

      <section v-else-if="activeTab === 'kelas'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <form @submit.prevent="saveKelas" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
          <h2 class="text-sm font-bold text-gray-800">{{ kelasForm.id ? 'Edit Kelas' : 'Tambah Kelas' }}</h2>
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Nama Kelas</label>
            <input v-model="kelasForm.nama_kelas" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 outline-none" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Tingkat</label>
            <input v-model="kelasForm.tingkat" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 outline-none" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Jurusan</label>
            <input v-model="kelasForm.jurusan" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 outline-none" />
          </div>
          <div class="flex gap-2">
            <button :disabled="saving" class="flex-1 bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 text-white py-2 rounded-lg text-sm font-semibold">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button type="button" @click="resetKelasForm" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold">Reset</button>
          </div>
        </form>

        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tingkat</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jurusan</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="kelas in kelasList" :key="kelas.id">
                <td class="px-5 py-4 text-sm font-semibold text-gray-800">{{ kelas.nama_kelas }}</td>
                <td class="px-5 py-4 text-sm text-gray-600">{{ kelas.tingkat }}</td>
                <td class="px-5 py-4 text-sm text-gray-600">{{ kelas.jurusan || '-' }}</td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <button @click="editKelas(kelas)" class="text-xs font-semibold text-primary-600 bg-primary-50 px-3 py-1.5 rounded-lg mr-2">Edit</button>
                  <button @click="deleteKelas(kelas)" class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg">Hapus</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-else-if="activeTab === 'mapel'" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <form @submit.prevent="saveMapel" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
          <h2 class="text-sm font-bold text-gray-800">{{ mapelForm.id ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h2>
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Nama</label>
            <input v-model="mapelForm.nama" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-400 outline-none" />
          </div>
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input v-model="mapelForm.aktif" type="checkbox" class="rounded border-gray-300 text-primary-500 focus:ring-primary-400" />
            Aktif
          </label>
          <div class="flex gap-2">
            <button :disabled="saving" class="flex-1 bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 text-white py-2 rounded-lg text-sm font-semibold">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button type="button" @click="resetMapelForm" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold">Reset</button>
          </div>
        </form>

        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="mapel in mapelList" :key="mapel.id">
                <td class="px-5 py-4 text-sm font-semibold text-gray-800">{{ mapel.nama }}</td>
                <td class="px-5 py-4">
                  <span class="px-2.5 py-1 text-xs font-semibold rounded-full border" :class="mapel.aktif ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-200'">
                    {{ mapel.aktif ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <button @click="editMapel(mapel)" class="text-xs font-semibold text-primary-600 bg-primary-50 px-3 py-1.5 rounded-lg mr-2">Edit</button>
                  <button @click="deleteMapel(mapel)" class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg">Hapus</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-else class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <form @submit.prevent="saveJadwal" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-4">
          <h2 class="text-sm font-bold text-gray-800">{{ jadwalForm.id ? 'Edit Jadwal' : 'Tambah Jadwal' }}</h2>
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Guru</label>
            <select v-model="jadwalForm.user_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary-400 outline-none">
              <option value="">Pilih Guru</option>
              <option v-for="guru in guruList" :key="guru.id" :value="guru.id">{{ guru.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Kelas</label>
            <select v-model="jadwalForm.kelas_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary-400 outline-none">
              <option value="">Pilih Kelas</option>
              <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">{{ kelas.nama_kelas }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Mata Pelajaran</label>
            <select v-model="jadwalForm.mata_pelajaran_id" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary-400 outline-none">
              <option value="">Pilih Mata Pelajaran</option>
              <option v-for="mapel in mapelList" :key="mapel.id" :value="mapel.id">{{ mapel.nama }}</option>
            </select>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Hari</label>
              <select v-model="jadwalForm.hari" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary-400 outline-none">
                <option v-for="[value, label] in hariOptions" :key="value" :value="value">{{ label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Mulai</label>
              <select v-model="jadwalForm.jam_pelajaran_mulai" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary-400 outline-none">
                <option v-for="jam in jamOptions" :key="jam" :value="jam">{{ jam }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 uppercase mb-1.5">Selesai</label>
              <select v-model="jadwalForm.jam_pelajaran_selesai" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white focus:ring-2 focus:ring-primary-400 outline-none">
                <option v-for="jam in jamOptions" :key="jam" :value="jam">{{ jam }}</option>
              </select>
            </div>
          </div>
          <div class="flex gap-2">
            <button :disabled="saving" class="flex-1 bg-primary-500 hover:bg-primary-600 disabled:bg-gray-300 text-white py-2 rounded-lg text-sm font-semibold">
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <button type="button" @click="resetJadwalForm" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold">Reset</button>
          </div>
        </form>

        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hari</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jam</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Guru</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mapel</th>
                  <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="jadwal in jadwalList" :key="jadwal.id">
                  <td class="px-5 py-4 text-sm font-semibold text-gray-800">{{ hariLabel(jadwal.hari) }}</td>
                  <td class="px-5 py-4 text-sm text-gray-600">Jam {{ jadwal.jam_pelajaran_mulai }}-{{ jadwal.jam_pelajaran_selesai }}</td>
                  <td class="px-5 py-4 text-sm text-gray-600">{{ jadwal.guru?.name || '-' }}</td>
                  <td class="px-5 py-4 text-sm text-gray-600">{{ jadwal.kelas?.nama_kelas || '-' }}</td>
                  <td class="px-5 py-4 text-sm text-gray-600">{{ jadwal.mata_pelajaran?.nama || '-' }}</td>
                  <td class="px-5 py-4 whitespace-nowrap">
                    <button @click="editJadwal(jadwal)" class="text-xs font-semibold text-primary-600 bg-primary-50 px-3 py-1.5 rounded-lg mr-2">Edit</button>
                    <button @click="deleteJadwal(jadwal)" class="text-xs font-semibold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
