/**
 * Utility functions for status handling
 */

/**
 * Get CSS class for status badge
 * @param {string} status - Status value (pending, approved, rejected)
 * @returns {string} CSS classes for badge
 */
export function getStatusBadgeClass(status) {
  const classes = {
    pending: 'bg-warning-100 text-warning-800 border-warning-300',
    approved: 'bg-success-100 text-success-800 border-success-300',
    rejected: 'bg-danger-100 text-danger-800 border-danger-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-300'
}

/**
 * Get Indonesian text for status
 * @param {string} status - Status value (pending, approved, rejected)
 * @returns {string} Indonesian status text
 */
export function getStatusText(status) {
  const texts = {
    pending: 'Menunggu',
    approved: 'Disetujui',
    rejected: 'Ditolak'
  }
  return texts[status] || status
}

/**
 * Get detailed status text
 * @param {string} status - Status value (pending, approved, rejected)
 * @returns {string} Detailed Indonesian status text
 */
export function getStatusTextDetailed(status) {
  const texts = {
    pending: 'Menunggu Persetujuan',
    approved: 'Disetujui',
    rejected: 'Ditolak'
  }
  return texts[status] || status
}
