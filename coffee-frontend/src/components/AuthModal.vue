<template>
  <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="authModalLabel">{{ isLogin ? 'Login' : 'Sign Up' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Tab Navigation -->
          <ul class="nav nav-tabs mb-3" id="authTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                :class="{ active: isLogin }"
                id="login-tab"
                type="button"
                @click="switchToLogin"
                role="tab"
              >
                Login
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                :class="{ active: !isLogin }"
                id="signup-tab"
                type="button"
                @click="switchToSignup"
                role="tab"
              >
                Sign Up
              </button>
            </li>
          </ul>

          <!-- Login Form -->
          <Form v-if="isLogin" @submit="handleLogin" :validation-schema="loginSchema" class="auth-form">
            <div class="mb-3">
              <label for="loginUsername" class="form-label">Username or Email</label>
              <Field
                id="loginUsername"
                name="username"
                type="text"
                class="form-control"
                placeholder="Enter your username or email"
              />
              <ErrorMessage name="username" class="invalid-feedback" />
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <div class="input-group">
                <Field
                  id="loginPassword"
                  name="password"
                  :type="showLoginPassword ? 'text' : 'password'"
                  class="form-control"
                  placeholder="Enter your password"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="showLoginPassword = !showLoginPassword"
                >
                  <i :class="showLoginPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <ErrorMessage name="password" class="invalid-feedback" />
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary" :disabled="authStore.isLoading">
                <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Login
              </button>
            </div>
            <div class="text-center mt-3">
              <button type="button" class="btn btn-link p-0" @click="showForgotPassword">Forgot Password?</button>
            </div>
          </Form>

          <!-- Signup Form -->
          <Form v-else @submit="handleSignup" :validation-schema="signupSchema" class="auth-form">
            <div class="mb-3">
              <label for="signupUsername" class="form-label">Username</label>
              <Field
                id="signupUsername"
                name="username"
                type="text"
                class="form-control"
                placeholder="Choose a username"
              />
              <ErrorMessage name="username" class="invalid-feedback" />
            </div>
            <div class="mb-3">
              <label for="signupEmail" class="form-label">Email address</label>
              <Field
                id="signupEmail"
                name="email"
                type="email"
                class="form-control"
                
                placeholder="Enter your email"
              />
              <ErrorMessage name="email" class="invalid-feedback" />
            </div>
            <div class="mb-3">
              <label for="signupBirthday" class="form-label">Birthday</label>
              <Field
                id="signupBirthday"
                name="birthday"
                type="date"
                class="form-control"
                
              />
              <ErrorMessage name="birthday" class="invalid-feedback" />
            </div>
            <div class="mb-3">
              <label for="signupPassword" class="form-label">Password</label>
              <div class="input-group">
                <Field
                  id="signupPassword"
                  name="password"
                  :type="showSignupPassword ? 'text' : 'password'"
                  class="form-control"
                  placeholder="Create a password"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="showSignupPassword = !showSignupPassword"
                >
                  <i :class="showSignupPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <ErrorMessage name="password" class="invalid-feedback" />
            </div>
            <div class="mb-3">
              <label for="signupConfirmPassword" class="form-label">Confirm Password</label>
              <div class="input-group">
                <Field
                  id="signupConfirmPassword"
                  name="password_confirmation"
                  :type="showSignupConfirmPassword ? 'text' : 'password'"
                  class="form-control"
                  placeholder="Confirm your password"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="showSignupConfirmPassword = !showSignupConfirmPassword"
                >
                  <i :class="showSignupConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
              <ErrorMessage name="password_confirmation" class="invalid-feedback" />
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary" :disabled="authStore.isLoading">
                <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Sign Up
              </button>
            </div>
          </Form>

          <!-- Error Alert -->
          <div v-if="authStore.error" class="alert alert-danger mt-3" role="alert">
            {{ authStore.error }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Forgot Password Modal -->
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <Form @submit="handleForgotPassword" :validation-schema="forgotPasswordSchema" class="auth-form">
            <div class="mb-3">
              <label for="forgotEmail" class="form-label">Email address</label>
              <Field
                id="forgotEmail"
                name="email"
                type="email"
                class="form-control"
                
                placeholder="Enter your email"
              />
              <ErrorMessage name="email" class="invalid-feedback" />
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary" :disabled="authStore.isLoading">
                <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Send Reset Email
              </button>
            </div>
          </Form>
          <div v-if="authStore.error" class="alert alert-danger mt-3" role="alert">
            {{ authStore.error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Form, Field, ErrorMessage } from 'vee-validate'
import { object, string, date, ref as yupRef } from 'yup'
import { useAuthStore } from '@/stores/auth'
import { Modal } from 'bootstrap'

const authStore = useAuthStore()
const isLogin = ref(true)
const showLoginPassword = ref(false)
const showSignupPassword = ref(false)
const showSignupConfirmPassword = ref(false)

// Validation schemas
const loginSchema = object({
  username: string().required('Username or email is required'),
  password: string().required('Password is required')
})

const signupSchema = object({
  username: string().required('Username is required').min(3, 'Username must be at least 3 characters'),
  email: string().required('Email is required').email('Invalid email format'),
  birthday: date()
    .required('Birthday is required')
    .max(new Date(), 'Birthday cannot be in the future')
    .test('age', 'You must be at least 18 years old', (value) => {
      if (!value) return false
      const today = new Date()
      const birthDate = new Date(value)
      let age = today.getFullYear() - birthDate.getFullYear()
      const monthDiff = today.getMonth() - birthDate.getMonth()
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--
      }
      return age >= 18
    }),
  password: string()
    .required('Password is required')
    .min(8, 'Password must be at least 8 characters')
    .matches(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/,
      'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character'),
  password_confirmation: string()
    .required('Please confirm your password')
    .oneOf([yupRef('password')], 'Passwords must match')
})

const forgotPasswordSchema = object({
  email: string().required('Email is required').email('Invalid email format')
})

// Define proper types for form data
interface LoginFormData {
  username: string
  password: string
}

interface SignupFormData {
  username: string
  email: string
  birthday: string
  password: string
  password_confirmation: string
}

interface ForgotPasswordFormData {
  email: string
}

// Form handlers with proper typing for VeeValidate compatibility
const handleLogin = async (values: Record<string, unknown>) => {
  const loginData = values as unknown as LoginFormData
  try {
    await authStore.loginUser(loginData)
    // Close modal on success
    const modal = Modal.getInstance(document.getElementById('authModal')!)
    modal?.hide()

    // Role-based redirect
    const role = authStore.user?.role
    if (role === 'admin' || role === 'super_admin') {
      // Import router here to avoid circular dependency
      const router = (await import('vue-router')).useRouter()
      router.push('/admin')
    }
  } catch (error: unknown) {
    console.error('Login failed:', error)
    // Error is handled by the store
  }
}

const handleSignup = async (values: Record<string, unknown>) => {
  const signupData = values as unknown as SignupFormData
  try {
    await authStore.registerUser(signupData)
    // Close modal on success
    const modal = Modal.getInstance(document.getElementById('authModal')!)
    modal?.hide()
  } catch (error: unknown) {
    console.error('Signup failed:', error)
    // Error is handled by the store
  }
}

const handleForgotPassword = async (values: Record<string, unknown>) => {
  const forgotData = values as unknown as ForgotPasswordFormData
  try {
    await authStore.sendForgotPassword(forgotData.email)
    // Close modal on success
    const modal = Modal.getInstance(document.getElementById('forgotPasswordModal')!)
    modal?.hide()
    // Show success message
    alert('Password reset email sent successfully!')
  } catch (error: unknown) {
    console.error('Forgot password failed:', error)
    // Error is handled by the store
  }
}

// Tab switching
const switchToLogin = () => {
  isLogin.value = true
}

const switchToSignup = () => {
  isLogin.value = false
}

const showForgotPassword = () => {
  const authModal = Modal.getInstance(document.getElementById('authModal')!)
  authModal?.hide()
  const forgotModal = new Modal(document.getElementById('forgotPasswordModal')!)
  forgotModal.show()
}
</script>

<style scoped>
.auth-form {
  max-width: 100%;
}
</style>