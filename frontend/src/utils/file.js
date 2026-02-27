/**
 * Utility functions for file handling
 */

const MAX_FILE_SIZE = 2048000 // 2MB in bytes
const ALLOWED_FILE_TYPES = {
  'application/pdf': 'PDF',
  'image/jpeg': 'JPEG',
  'image/jpg': 'JPG',
  'image/png': 'PNG'
}

/**
 * Validate file size and type
 * @param {File} file - File to validate
 * @returns {{ valid: boolean, error: string }}
 */
export function validateFile(file) {
  if (!file) {
    return { valid: false, error: 'Tidak ada file yang dipilih' }
  }

  // Check file size
  if (file.size > MAX_FILE_SIZE) {
    return { valid: false, error: 'Ukuran file maksimal 2MB' }
  }

  // Check file type
  if (!ALLOWED_FILE_TYPES[file.type]) {
    return { valid: false, error: 'Format file harus PDF, JPG, JPEG, atau PNG' }
  }

  return { valid: true, error: '' }
}

/**
 * Format file size to human-readable format
 * @param {number} bytes - File size in bytes
 * @returns {string} Formatted file size
 */
export function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

/**
 * Download blob as file
 * @param {Blob} blob - Blob data
 * @param {string} filename - Filename for download
 */
export function downloadBlob(blob, filename) {
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', filename)
  document.body.appendChild(link)
  link.click()
  link.remove()
  window.URL.revokeObjectURL(url)
}
