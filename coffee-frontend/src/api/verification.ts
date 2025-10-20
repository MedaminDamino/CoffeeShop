import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

// Create axios instance with default config
const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Add token to requests if available
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

export interface VerificationResponse {
  message: string
  success: boolean
}

export interface VerifyCodeResponse {
  valid: boolean
  message: string
}

/**
 * Send verification code to email
 */
export const sendVerificationCodeAPI = async (email: string): Promise<VerificationResponse> => {
  const response = await apiClient.post('/verification/send-code', { email })
  return response.data
}

/**
 * Verify email code
 */
export const verifyEmailCodeAPI = async (email: string, code: string): Promise<VerifyCodeResponse> => {
  const response = await apiClient.post('/verification/verify-code', { email, code })
  return response.data
}