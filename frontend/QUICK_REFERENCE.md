# Quick Reference Guide - New Utilities

## 📅 Date Utilities

```javascript
import { 
  formatDate, 
  formatDateFull, 
  formatDateTime, 
  formatDateShort,
  getTodayISO 
} from '@/utils/date'

// Format: dd/mm/yyyy (e.g., "11/02/2026")
{{ formatDate(item.tanggal) }}

// Format: Senin, 11 Februari 2026
{{ formatDateFull(item.tanggal) }}

// Format: 11/02/2026, 14:30:00
{{ formatDateTime(item.created_at) }}

// Format: 11 Feb
{{ formatDateShort(item.tanggal) }}

// Get today in ISO format for input[type="date"]
const today = getTodayISO() // "2026-02-11"
```

---

## 🎨 Status Utilities

```javascript
import { 
  getStatusBadgeClass, 
  getStatusText,
  getStatusTextDetailed 
} from '@/utils/status'

// Get CSS classes for badge
:class="getStatusBadgeClass(item.status)"

// Get status text
{{ getStatusText(item.status) }}
// Output: "Menunggu", "Disetujui", or "Ditolak"

// Get detailed status text
{{ getStatusTextDetailed(item.status) }}
// Output: "Menunggu Persetujuan", "Disetujui", or "Ditolak"
```

---

## 📁 File Utilities

```javascript
import { 
  validateFile, 
  formatFileSize,
  downloadBlob 
} from '@/utils/file'

// Validate file
const { valid, error } = validateFile(file)
if (!valid) {
  console.error(error)
}

// Format file size
const size = formatFileSize(1500000) // "1.43 MB"

// Download blob
downloadBlob(blob, 'filename.pdf')
```

---

## 🔄 Dispensasi Composable

```javascript
import { useDispensasi } from '@/composables/useDispensasi'

const { 
  dispensasiList,    // ref([])
  loading,           // ref(false)
  error,             // ref('')
  fetchDispensasi,   // async function
  fetchDispensasiById,
  deleteDispensasi,
  updateStatus
} = useDispensasi()

// Fetch all dispensasi
await fetchDispensasi()

// Fetch single dispensasi
const dispensasi = await fetchDispensasiById(id)

// Delete dispensasi
await deleteDispensasi(id)

// Update status
await updateStatus(id, 'approved', 'Catatan opsional')
```

---

## 🏫 Kelas Composable

```javascript
import { useKelas } from '@/composables/useKelas'

const {
  kelasList,    // ref([])
  loading,      // ref(false)
  error,        // ref('')
  fetchKelas    // async function
} = useKelas()

// Fetch all kelas
await fetchKelas()
```

---

## 🌍 Environment Variables

```javascript
// .env file
VITE_API_BASE_URL=http://127.0.0.1:8000/api
VITE_APP_NAME=Sistem Dispensasi SMALA

// Access in code
const apiUrl = import.meta.env.VITE_API_BASE_URL
const appName = import.meta.env.VITE_APP_NAME
```

---

## ✨ Complete Example

### Before (Old Way)
```vue
<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const dispensasiList = ref([])
const loading = ref(true)

async function fetchDispensasi() {
  loading.value = true
  try {
    const response = await api.get('/dispensasi')
    dispensasiList.value = response.data.data
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

function getStatusBadgeClass(status) {
  const classes = {
    pending: 'bg-warning-100 text-warning-800 border-warning-300',
    approved: 'bg-success-100 text-success-800 border-success-300',
    rejected: 'bg-danger-100 text-danger-800 border-danger-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300'
}

onMounted(() => {
  fetchDispensasi()
})
</script>

<template>
  <div v-for="item in dispensasiList" :key="item.id">
    <span :class="getStatusBadgeClass(item.status)">
      {{ item.status }}
    </span>
    <p>{{ new Date(item.tanggal).toLocaleDateString('id-ID') }}</p>
  </div>
</template>
```

### After (New Way)
```vue
<script setup>
import { onMounted } from 'vue'
import { useDispensasi } from '@/composables/useDispensasi'
import { getStatusBadgeClass, getStatusText } from '@/utils/status'
import { formatDate } from '@/utils/date'

const { dispensasiList, loading, fetchDispensasi } = useDispensasi()

onMounted(async () => {
  await fetchDispensasi()
})
</script>

<template>
  <div v-for="item in dispensasiList" :key="item.id">
    <span :class="getStatusBadgeClass(item.status)">
      {{ getStatusText(item.status) }}
    </span>
    <p>{{ formatDate(item.tanggal) }}</p>
  </div>
</template>
```

**Benefits:**
- ✅ 50% less code
- ✅ No duplicate logic
- ✅ Easier to test
- ✅ Consistent across app
- ✅ Single source of truth

---

## 🎯 Common Patterns

### Pattern 1: Fetch Data on Mount
```javascript
import { onMounted } from 'vue'
import { useDispensasi } from '@/composables/useDispensasi'

const { dispensasiList, loading, fetchDispensasi } = useDispensasi()

onMounted(async () => {
  await fetchDispensasi()
})
```

### Pattern 2: Delete with Confirmation
```javascript
import { useDispensasi } from '@/composables/useDispensasi'

const { deleteDispensasi } = useDispensasi()

async function handleDelete(id) {
  if (!confirm('Apakah Anda yakin?')) return
  
  try {
    await deleteDispensasi(id)
    alert('Berhasil dihapus')
  } catch (error) {
    alert('Gagal menghapus')
  }
}
```

### Pattern 3: File Upload Validation
```javascript
import { ref } from 'vue'
import { validateFile } from '@/utils/file'

const error = ref('')

function handleFileChange(event) {
  const file = event.target.files[0]
  const { valid, error: validationError } = validateFile(file)
  
  if (!valid) {
    error.value = validationError
    event.target.value = ''
    return
  }
  
  // Process valid file
  form.value.file = file
}
```

### Pattern 4: Export with Download
```javascript
import { downloadBlob, getTodayISO } from '@/utils/file'
import api from '@/services/api'

async function handleExport() {
  const response = await api.get('/export/excel', {
    responseType: 'blob'
  })
  
  downloadBlob(
    new Blob([response.data]),
    `Export_${getTodayISO()}.xlsx`
  )
}
```

---

## 📚 Tips

1. **Always import utilities** instead of writing inline logic
2. **Use composables** for data fetching and mutations
3. **Keep components clean** - move logic to composables/utilities
4. **Consistent naming** - use Indonesian for user-facing text
5. **Error handling** - always handle errors in try-catch blocks
