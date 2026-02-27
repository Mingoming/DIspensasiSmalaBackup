/**
 * Utility functions for date formatting
 */

/**
 * Format date to Indonesian locale (dd/mm/yyyy)
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date
 */
export function formatDate(date) {
  return new Date(date).toLocaleDateString('id-ID')
}

/**
 * Format date with full details (e.g., "Senin, 11 Februari 2026")
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date with full details
 */
export function formatDateFull(date) {
  return new Date(date).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

/**
 * Format date and time to Indonesian locale
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date and time
 */
export function formatDateTime(date) {
  return new Date(date).toLocaleString('id-ID')
}

/**
 * Format date to short format (e.g., "11 Feb")
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted short date
 */
export function formatDateShort(date) {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short'
  })
}

/**
 * Get today's date in YYYY-MM-DD format (for input[type="date"])
 * @returns {string} Today's date in ISO format
 */
export function getTodayISO() {
  return new Date().toISOString().split('T')[0]
}
