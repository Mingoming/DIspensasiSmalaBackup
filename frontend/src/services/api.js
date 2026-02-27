import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Interceptor untuk menangani error
api.interceptors.response.use(
  response => response,
  error => {
    console.error('API Error:', error)
    
    // Handle common errors
    if (error.response) {
      switch (error.response.status) {
        case 401:
          // Unauthorized - could trigger logout
          console.warn('Unauthorized access')
          break
        case 403:
          console.warn('Forbidden access')
          break
        case 404:
          console.warn('Resource not found')
          break
        case 500:
          console.error('Server error')
          break
      }
    } else if (error.request) {
      console.error('Network error - no response received')
    }
    
    return Promise.reject(error)
  }
)

export default api