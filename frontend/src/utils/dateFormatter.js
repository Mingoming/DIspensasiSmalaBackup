/**
 * Format date to Indonesian format (DD/MM/YYYY)
 */
export function formatDateIndo(dateString) {
  if (!dateString) return '-'
  
  const date = new Date(dateString)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  
  return `${day}/${month}/${year}`
}

/**
 * Format date to input format (YYYY-MM-DD)
 */
export function formatDateForInput(dateString) {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  
  return `${year}-${month}-${day}`
}

/**
 * Format date to Indonesian long format (Senin, 14 Februari 2026)
 */
export function formatDateLongIndo(dateString) {
  if (!dateString) return '-'
  
  const date = new Date(dateString)
  const options = { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  }
  
  return date.toLocaleDateString('id-ID', options)
}

/**
 * Get today's date in YYYY-MM-DD format
 */
export function getTodayForInput() {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  
  return `${year}-${month}-${day}`
}