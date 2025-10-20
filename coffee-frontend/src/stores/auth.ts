import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login, register, forgotPassword, getUser, type User } from '@/api/auth'
import { api } from '@/api/client'
import { useCartStore } from './cart'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const user = ref<User | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  // Set auth token in axios and localStorage
  const setToken = (newToken: string) => {
    token.value = newToken
    localStorage.setItem('token', newToken)
    api.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  // Clear auth
  const clearAuth = () => {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    delete api.defaults.headers.common['Authorization']
  }

  // Login
  const loginUser = async (credentials: { username: string; password: string }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await login(credentials)
      setToken(response.token)
      user.value = response.user

      // Role-based redirect
      const role = response.user.role
      if (role === 'admin' || role === 'super_admin') {
        // Use router.push for programmatic navigation
        // This will be handled in the component that calls this method
      }

      return response
    } catch (err: unknown) {
      error.value = 'Login failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Register
  const registerUser = async (data: { username: string; email: string; birthday: string; password: string; password_confirmation: string }) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await register(data)
      setToken(response.token)
      user.value = response.user
      return response
    } catch (err: unknown) {
      error.value = 'Registration failed'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Logout
  const logout = async () => {
    try {
      await api.post('/logout')
    } catch (err) {
      // Ignore logout errors
    } finally {
      // Clear cart when user logs out
      const cartStore = useCartStore()
      cartStore.clearCart()
      clearAuth()
    }
  }

  // Forgot password
  const sendForgotPassword = async (email: string) => {
    isLoading.value = true
    error.value = null
    try {
      const response = await forgotPassword({ email })
      return response
    } catch (err: unknown) {
      error.value = 'Failed to send reset email'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Initialize auth on app start
  const initializeAuth = async () => {
    if (token.value) {
      api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      try {
        user.value = await getUser()
      } catch (error) {
        // Token might be invalid, clear auth
        clearAuth()
      }
    }
  }

  return {
    token,
    user,
    isLoading,
    error,
    isAuthenticated,
    loginUser,
    registerUser,
    logout,
    sendForgotPassword,
    initializeAuth,
    clearAuth
  }
})